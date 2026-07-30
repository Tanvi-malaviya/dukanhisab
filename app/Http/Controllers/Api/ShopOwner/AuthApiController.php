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
     * Update the authenticated user's account settings (basic info + preferences).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|in:12h,24h',
            'theme' => 'nullable|string|in:light,dark,system',
            'notification_preferences' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
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
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:50',
            'invoice_prefix' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'upi_id' => 'nullable|string|max:100',
            'bank_details' => 'nullable|string',
            'invoice_footer' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // max 2MB
            'signature' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Update User's name & mobile if changed
        $user->name = $request->owner_name;
        $user->mobile = $request->mobile;
        $user->save();

        $existingShop = $user->shops()->first();

        // Handle Shop Logo Upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Handle Shop Signature Upload
        $signaturePath = null;
        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('signatures', 'public');
        }

        // Create or update Shop (we assume one shop per owner for setup module)
        $shop = \App\Models\Shop::updateOrCreate(
            ['owner_id' => $user->id],
            [
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'gst_number' => $request->gst_number,
                'invoice_prefix' => $request->invoice_prefix,
                'currency' => $request->currency,
                'upi_id' => $request->upi_id,
                'bank_details' => $request->bank_details,
                'invoice_footer' => $request->invoice_footer,
                'logo' => $logoPath ?: ($existingShop?->logo),
                'signature' => $signaturePath ?: ($existingShop?->signature),
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
