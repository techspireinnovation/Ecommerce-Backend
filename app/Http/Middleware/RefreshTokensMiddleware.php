<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class RefreshTokensMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            // Try authenticating normally
            JWTAuth::parseToken()->authenticate();
            return $next($request);

        } catch (TokenExpiredException $e) {

            // Decode token without authenticating
            $payload = JWTAuth::getJWTProvider()->decode($token);
            $userId = $payload['sub'] ?? null;

            if (!$userId) {
                return response()->json(['error' => 'Invalid token payload'], 401);
            }

            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $refreshToken = RefreshToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$refreshToken) {
                return response()->json(['error' => 'Session expired. Please login again.'], 401);
            }

            // Check token age: only refresh if last refresh is older than JWT_TTL (15 min)
            $tokenCreatedAt = Carbon::parse($refreshToken->created_at);
            if ($tokenCreatedAt->diffInMinutes(now()) < config('jwt.ttl')) {
                return $next($request)
                    ->header('Authorization', 'Bearer ' . (string) $token)
                    ->header('X-Refresh-Token', $refreshToken ? $refreshToken->plain_token ?? 'existing token' : null);
            }


            // Delete old refresh token and generate new ones
            $refreshToken->delete();

            $newAccessToken = JWTAuth::fromUser($user);
            $newRefreshToken = Str::random(64);

            RefreshToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $newRefreshToken),
                'expires_at' => now()->addDays(7),
            ]);

            JWTAuth::setToken($newAccessToken)->authenticate();
            $response = $next($request);

            return $response
                ->header('Authorization', 'Bearer ' . $newAccessToken)
                ->header('X-Refresh-Token', $newRefreshToken)
                ->header('X-Token-Refreshed', 'true');
        }
    }

}

