<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'gender' => 'required|in:male,female,other',
            'mobile_no' => 'required|string|size:10',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least :min characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);


        // Create the user
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'gender' => $request->gender,
            'mobile_no' => $request->mobile_no,
        ]);

        $addressData = [
            'user_id' => $user->id,
            'type' => 1,        // always 1 for user
            'label' => 'Home',  // always Home
            'street' => $request->street,
            'city' => $request->city,
            'district' => $request->district,
            'province' => $request->province,
            'zip' => $request->zip,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 0, // default active
        ];

        Address::create($addressData);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
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

    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (TokenExpiredException $e) {
                $payload = JWTAuth::getJWTProvider()->decode($token);
                $userId = $payload['sub'] ?? null;

                if (!$userId) {
                    return response()->json(['error' => 'Invalid token payload'], 401);
                }

                $user = User::find($userId);
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
            }

            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (TokenExpiredException $e) {
                // Already expired, ignore
            }

            RefreshToken::where('user_id', $user->id)->delete();

            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to logout',
                'message' => $e->getMessage() // optional debug info
            ], 500);
        }
    }



    public function me()
    {
        return response()->json(auth()->user());
    }
}