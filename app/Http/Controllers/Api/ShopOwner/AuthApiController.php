<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;

class AuthApiController extends Controller
{
    /**
     * Register a new shopowner.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otpCode = (string) rand(100000, 999999);

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
            'status' => 'active',
        ]);

        try {
            Mail::send('shopowner.emails.otp', ['user' => $user, 'otp_code' => $otpCode], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email - DukanHisab');
            });
        } catch (\Exception $e) {
            // Log exception, but do not block user registration flow in development/local
            \Log::error('OTP email failed to send: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. A verification OTP code has been sent to your email.',
            'email' => $user->email,
            // Including OTP in response for development convenience
            'dev_otp' => app()->environment('local') ? $otpCode : null
        ], 201);
    }

    /**
     * Verify email OTP.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        if ($user->otp_code !== $request->otp_code || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP code.'], 400);
        }

        // Verify user email
        $user->email_verified_at = Carbon::now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('shopowner-auth-token')->plainTextToken;
        $user->load('shops');
        $shop = $user->shops()->first();

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user,
            'has_shop' => $shop !== null,
            'shop' => $shop
        ]);
    }

    /**
     * Resend verification OTP.
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        $otpCode = (string) rand(100000, 999999);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        try {
            Mail::send('shopowner.emails.otp', ['user' => $user, 'otp_code' => $otpCode], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email - DukanHisab');
            });
        } catch (\Exception $e) {
            \Log::error('Resend OTP email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'A new verification OTP code has been sent to your email.',
            'dev_otp' => app()->environment('local') ? $otpCode : null
        ]);
    }

    /**
     * Login shopowner.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if ($user->isSuspended()) {
            return response()->json(['message' => 'Your account has been suspended.'], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Your email has not been verified yet.',
                'email' => $user->email,
                'email_unverified' => true
            ], 403);
        }

        $user->last_login_at = Carbon::now();
        $user->save();

        $token = $user->createToken('shopowner-auth-token')->plainTextToken;
        $user->load('shops');
        $shop = $user->shops()->first();

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
            'has_shop' => $shop !== null,
            'shop' => $shop
        ]);
    }

    /**
     * Forgot password request (sends reset OTP).
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $otpCode = (string) rand(100000, 999999);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();

            try {
                Mail::send('shopowner.emails.reset', ['user' => $user, 'otp_code' => $otpCode], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Reset Your Password - DukanHisab');
                });
            } catch (\Exception $e) {
                \Log::error('Password reset email failed: ' . $e->getMessage());
            }
        }

        // Return positive response regardless of existence to prevent email enumeration
        return response()->json([
            'message' => 'If the email exists, a password reset OTP code has been sent.',
            'dev_otp' => (app()->environment('local') && isset($otpCode)) ? $otpCode : null
        ]);
    }

    /**
     * Reset password using OTP.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->otp_code !== $request->otp_code || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired reset code.'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        // Auto-verify email if it wasn't verified already when they successfully reset password
        if (!$user->email_verified_at) {
            $user->email_verified_at = Carbon::now();
        }
        $user->save();

        return response()->json([
            'message' => 'Password reset successful. You can now login with your new password.'
        ]);
    }

    /**
     * Change password while logged in.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match.'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * Get authenticated profile details.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('shops');
        $shop = $user->shops()->first();
        return response()->json([
            'user' => $user,
            'has_shop' => $shop !== null,
            'shop' => $shop
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Set up shop details for the owner.
     */
    public function shopSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Update User's name & mobile if changed
        $user->name = $request->owner_name;
        $user->mobile = $request->mobile;
        $user->save();

        // Handle Shop Logo Upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Create or update Shop (we assume one shop per owner for setup module)
        $shop = \App\Models\Shop::updateOrCreate(
            ['owner_id' => $user->id],
            [
                'name' => $request->name,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'gst_number' => $request->gst_number,
                'logo' => $logoPath ?: ($user->shops()->first()?->logo)
            ]
        );

        $user->load('shops');

        return response()->json([
            'message' => 'Shop setup successfully completed.',
            'user' => $user,
            'has_shop' => true,
            'shop' => $shop
        ]);
    }
}
