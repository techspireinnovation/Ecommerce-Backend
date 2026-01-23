<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Biponix\SecureOtp\Services\SecureOtpService;
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
use Illuminate\Support\Facades\DB;


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

            $user = User::query()
                ->where('email', $request->email)->first();
            if ($user->role === 'user' && !$user->email_verified_at) {
                return response()->json([
                    'error' => 'Email not verified. Please verify your email first.'
                ], 403);
            }
            $user->last_login_at = now();
            $user->save();

            Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'last_login_at' => $user->last_login_at,

            ]);

            // Delete old refresh tokens
            $deleted = RefreshToken::query()
                ->where('user_id', $user->id)->delete();

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

        $refreshToken = RefreshToken::query()
            ->where('token_hash', $tokenHash)
            ->where('expires_at', '>', Carbon::now()->toDateTimeString())
            ->first();

        if (!$refreshToken) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = User::query()
            ->find($refreshToken->user_id);

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

                $user = User::query()
                    ->find($userId);
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
            }

            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (TokenExpiredException $e) {
                // Already expired, ignore
            }

            RefreshToken::query()
                ->where('user_id', $user->id)->delete();

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

    public function sendOtp(Request $request, SecureOtpService $otp)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $otp->send($request->email, 'email');

            return response()->json([
                'message' => 'OTP sent to your email.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Too many OTP requests. Please try again later.'], 500);
        }
    }

    public function verifyOtp(Request $request, SecureOtpService $otp)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        $verified = $otp->verify($request->email, $request->code, 'email');

        if ($verified) {
            $user = User::query()->where('email', $request->email)->first();
            if ($user && !$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }

            return response()->json(['message' => 'OTP verified successfully.']);
        }

        return response()->json(['error' => 'Invalid or expired OTP.'], 422);
    }
    public function sendForgotPasswordOtp(Request $request, SecureOtpService $otp)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $otp->send($request->email, 'forgot_password');

            return response()->json([
                'message' => 'Password reset OTP sent to your email.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Too many OTP requests. Please try again later.'
            ], 500);
        }
    }



    public function verifyForgotPasswordOtp(Request $request, SecureOtpService $otp)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        $verified = $otp->verify(
            $request->email,
            $request->code,
            'forgot_password'
        );

        if (!$verified) {
            return response()->json([
                'error' => 'Invalid or expired OTP.'
            ], 422);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'OTP verified successfully.',
            'reset_token' => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|confirmed|min:8',
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Reset token missing.'
            ], 401);
        }

        $hashedToken = hash('sha256', $token);

        $record = DB::table('password_reset_tokens')
            ->where('token', $hashedToken)
            ->first();

        if (
            !$record ||
            Carbon::parse($record->created_at)->addMinutes(10)->isPast()
        ) {
            return response()->json([
                'error' => 'Invalid or expired reset token.'
            ], 403);
        }

        $user = User::query()->where('email', $record->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found.'
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $record->email)
            ->delete();

        return response()->json([
            'message' => 'Password reset successful.'
        ]);
    }


}