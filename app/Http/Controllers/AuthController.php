<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $accessToken = JWTAuth::fromUser($user);
        $refreshTokenPlain = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshTokenPlain),
            'expires_at' => Carbon::now()->addDays((int) 7)->toDateTimeString(),
        ]);

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenPlain,
            'token_type' => 'Bearer',
            'expires_in' => 900,
            //'expires_in' => 60, // 1 minute

        ], 201);
    }

    // ---------------------- Login ----------------------
    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            $accessToken = JWTAuth::attempt($credentials);

            if (!$accessToken) {
                Log::warning('Login failed: invalid credentials', [
                    'email' => $request->email,
                ]);

                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = User::where('email', $request->email)->first();
            $user->last_login_at = now();
            $user->save();

            Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'last_login_at' => $user->last_login_at,

            ]);

            // Delete old refresh tokens
            $deleted = RefreshToken::where('user_id', $user->id)->delete();

            Log::info('Old refresh tokens deleted', [
                'user_id' => $user->id,
                'deleted_count' => $deleted,
            ]);

            $refreshTokenPlain = Str::random(64);

            RefreshToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $refreshTokenPlain),
                'expires_at' => Carbon::now()->addDays((int) 7)->toDateTimeString(),
            ]);

            Log::info('New refresh token created', [
                'user_id' => $user->id,
                'expires_at' => Carbon::now()->addDays((int) 7)->toDateTimeString(),
            ]);

            return response()->json([
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshTokenPlain,
                'token_type' => 'Bearer',
                'expires_in' => 900,
                //'expires_in' => 60, // 1 minute
            ]);

        } catch (JWTException $e) {

            Log::error('JWT login exception', [
                'email' => $request->email,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Could not create token'], 500);
        }
    }


    // ---------------------- Refresh ----------------------
    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);

        $tokenHash = hash('sha256', $request->refresh_token);

        $refreshToken = RefreshToken::where('token_hash', $tokenHash)
            ->where('expires_at', '>', Carbon::now()->toDateTimeString())
            ->first();

        if (!$refreshToken) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = User::find($refreshToken->user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Delete old refresh token
        $refreshToken->delete();

        $accessToken = JWTAuth::fromUser($user);
        $newRefreshTokenPlain = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $newRefreshTokenPlain),
            'expires_at' => Carbon::now()->addDays((int) 7)->toDateTimeString(),
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshTokenPlain,
            'token_type' => 'Bearer',
            'expires_in' => 900,
            //'expires_in' => 60, // 1 minute
        ]);
    }

    // ---------------------- Logout ----------------------
    public function logout(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user) {
                JWTAuth::parseToken()->invalidate();
                RefreshToken::where('user_id', $user->id)->delete();
            }

            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    // ---------------------- Me ----------------------
    public function me()
    {
        return response()->json(auth()->user());
    }
}