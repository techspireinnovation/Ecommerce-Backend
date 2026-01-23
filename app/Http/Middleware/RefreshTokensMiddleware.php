<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
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
            return response()->json(['success' => false, 'message' => 'Token not provided'], 401);
        }

        try {
            // Try normal authentication
            $user = JWTAuth::parseToken()->authenticate();
            // Set user for Laravel guard
            auth()->setUser($user);

            return $next($request);

        } catch (TokenExpiredException $e) {
            // Decode token without authenticating
            $payload = JWTAuth::getJWTProvider()->decode($token);
            $userId = $payload['sub'] ?? null;

            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Invalid token payload'], 401);
            }

            $user = User::find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Check for a valid refresh token
            $refreshToken = RefreshToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
                ], 401);
            }

            // Optional: only refresh if last refresh is older than JWT_TTL
            $tokenCreatedAt = Carbon::parse($refreshToken->created_at);
            if ($tokenCreatedAt->diffInMinutes(now()) < config('jwt.ttl')) {
                // Token still fresh enough, pass current request
                auth()->setUser($user); // ⚡ ensure user is set
                return $next($request)
                    ->header('Authorization', 'Bearer ' . (string) $token)
                    ->header('X-Refresh-Token', $refreshToken->plain_token ?? 'existing token');
            }

            // Delete old refresh token and generate new ones
            $refreshToken->delete();

            $newAccessToken = JWTAuth::fromUser($user);
            $newRefreshToken = Str::random(64);

            RefreshToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $newRefreshToken),
                'expires_at' => now()->addDays(7),
                'plain_token' => $newRefreshToken, // optional if you want to return it
            ]);

            // ⚡ Set the user for this request so role middleware works
            auth()->setUser($user);

            $response = $next($request);

            return $response
                ->header('Authorization', 'Bearer ' . $newAccessToken)
                ->header('X-Refresh-Token', $newRefreshToken)
                ->header('X-Token-Refreshed', 'true');
        }
    }
}
