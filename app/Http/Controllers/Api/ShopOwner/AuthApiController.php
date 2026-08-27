<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Carbon\Carbon;

class AuthApiController extends Controller
{
    /**
     * Register a new shopowner.
     */
    public function register(Request $request)
    {
        $existingUser = User::where('email', $request->email)->first();

        // If email belongs to an already verified account, reject registration
        if ($existingUser && $existingUser->email_verified_at !== null) {
            return response()->json([
                'errors' => [
                    'email' => ['The email has already been taken.']
                ]
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otpCode = (string) rand(100000, 999999);
        $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();

        if ($existingUser && $existingUser->email_verified_at === null) {
            // Update unverified user account details with fresh OTP code
            $existingUser->update([
                'name' => $request->first_name . ' ' . $request->last_name,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'status' => 'active',
                'active_plan_id' => $freePlan ? $freePlan->id : $existingUser->active_plan_id,
            ]);
            $user = $existingUser;
        } else {
            // Create new user account
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'status' => 'active',
                'active_plan_id' => $freePlan ? $freePlan->id : null,
            ]);
        }

        try {
            Mail::send('shopowner.emails.otp', ['user' => $user, 'otp_code' => $otpCode], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email - DukanHisab');
            });
        } catch (\Exception $e) {
            \Log::error('OTP email failed to send: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. A verification OTP code has been sent to your email.',
            'email' => $user->email,
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

        if ($user->otpAttemptsExceeded()) {
            return response()->json([
                'message' => 'Too many incorrect attempts. Please request a new OTP code.',
                'otp_locked' => true,
            ], 429);
        }

        if ($user->otp_code !== $request->otp_code || Carbon::now()->isAfter($user->otp_expires_at)) {
            $user->registerFailedOtpAttempt();
            $remaining = max(0, User::MAX_OTP_ATTEMPTS - $user->otp_attempts);
            return response()->json([
                'message' => 'Invalid or expired OTP code.',
                'attempts_remaining' => $remaining,
            ], 400);
        }

        // Verify user email
        $user->email_verified_at = Carbon::now();
        $user->clearOtp();
        $user->save();

        $token = $user->issueDeviceToken('shopowner-auth-token');
        $user->load(['shops', 'activePlan', 'currentSubscription']);
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

        $otpCode = $user->issueNewOtp();

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

        $token = $user->issueDeviceToken('shopowner-auth-token');
        $user->load(['shops', 'activePlan', 'currentSubscription']);
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
            $otpCode = $user->issueNewOtp();

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

        if ($user->otpAttemptsExceeded()) {
            return response()->json([
                'message' => 'Too many incorrect attempts. Please request a new reset code.',
                'otp_locked' => true,
            ], 429);
        }

        if ($user->otp_code !== $request->otp_code || Carbon::now()->isAfter($user->otp_expires_at)) {
            $user->registerFailedOtpAttempt();
            $remaining = max(0, User::MAX_OTP_ATTEMPTS - $user->otp_attempts);
            return response()->json([
                'message' => 'Invalid or expired reset code.',
                'attempts_remaining' => $remaining,
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->clearOtp();
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
        $user->load(['shops', 'activePlan', 'currentSubscription']);
        
        $shop = null;
        $shopId = $request->header('X-Shop-ID');
        if ($shopId) {
            $shop = $user->shops()->where('id', $shopId)->first();
        }
        if (!$shop) {
            $shop = $user->shops()->first();
        }

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
            'shop_id' => 'nullable|integer|exists:shops,id',
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
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
            'shop_image' => 'nullable|image|max:4096', // max 4MB
            'website_settings' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Update User's name if changed
        $user->name = $request->owner_name;
        $user->save();

        $shopId = $request->input('shop_id') ?: $request->header('X-Shop-ID');
        if ($shopId === 'none') {
            $shopId = null;
        }
        $shop = null;
        if ($shopId) {
            $shop = $user->shops()->where('id', $shopId)->first();
            if (!$shop) {
                return response()->json(['message' => 'Shop not found or access denied.'], 403);
            }
        }

        if (!$shop) {
            if (!$user->canAddShop()) {
                return response()->json([
                    'message' => "Your active subscription plan allows maximum {$user->maxShops()} shop(s). Please upgrade your subscription plan."
                ], 403);
            }
        }

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

        // Handle Shop Image Upload
        $shopImagePath = null;
        if ($request->hasFile('shop_image')) {
            $shopImagePath = $request->file('shop_image')->store('shop_images', 'public');
        }

        // Parse website settings JSON string if sent
        $websiteSettings = null;
        if ($request->has('website_settings')) {
            $websiteSettings = json_decode($request->website_settings, true);
            if (is_array($websiteSettings)) {
                $websiteSettings['subdomain'] = \Illuminate\Support\Str::slug($request->name);
                if ($shopImagePath) {
                    $websiteSettings['shop_image'] = $shopImagePath;
                } else {
                    $websiteSettings['shop_image'] = $shop?->website_settings['shop_image'] ?? null;
                }

                // Enforce active plan limits for Free plan
                if ($user->activePlan && $user->activePlan->slug === 'free') {
                    $websiteSettings['theme_color'] = '#0F766E';
                    $websiteSettings['seo_title'] = '';
                    $websiteSettings['seo_description'] = '';
                    $websiteSettings['social_facebook'] = '';
                    $websiteSettings['social_instagram'] = '';
                    $websiteSettings['social_twitter'] = '';
                    $websiteSettings['social_whatsapp'] = '';
                    $websiteSettings['shop_image'] = null;
                    $shopImagePath = null;
                }
            }
        }

        // Create or update Shop
        $shopData = [
            'owner_id' => $user->id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number,
            'invoice_prefix' => $request->invoice_prefix,
            'currency' => $request->currency ?: ($shop?->currency ?: 'INR'),
            'upi_id' => $request->upi_id,
            'bank_details' => $request->bank_details,
            'invoice_footer' => $request->invoice_footer,
            'logo' => $logoPath ?: ($shop?->logo),
            'signature' => $signaturePath ?: ($shop?->signature),
            'website_settings' => $request->has('website_settings') ? $websiteSettings : ($shop?->website_settings),
        ];

        if ($shop) {
            $shop->update($shopData);
        } else {
            $shop = \App\Models\Shop::create($shopData);
            // Initialize default invoice config for the new shop
            \App\Models\InvoiceConfig::create(['shop_id' => $shop->id]);
        }

        $user->load(['shops', 'activePlan', 'currentSubscription']);

        return response()->json([
            'message' => 'Shop setup successfully completed.',
            'user' => $user,
            'has_shop' => true,
            'shop' => $shop
        ]);
    }

    /**
     * Get postal address details by pincode.
     */
    public function getPincodeDetails($pincode)
    {
        $validator = Validator::make(['pincode' => $pincode], [
            'pincode' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $response = Http::timeout(8)->get("https://api.postalpincode.in/pincode/{$pincode}");

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data) && isset($data[0]) && $data[0]['Status'] === 'Success') {
                    $postOffices = $data[0]['PostOffice'];
                    $firstOffice = $postOffices[0] ?? null;

                    return response()->json([
                        'success' => true,
                        'status' => 'Success',
                        'message' => $data[0]['Message'] ?? 'Details retrieved successfully.',
                        'data' => [
                            'pincode' => $pincode,
                            'city' => $firstOffice ? $firstOffice['District'] : null,
                            'state' => $firstOffice ? $firstOffice['State'] : null,
                            'post_offices' => array_map(function ($office) {
                                return [
                                    'name' => $office['Name'],
                                    'branch_type' => $office['BranchType'],
                                    'delivery_status' => $office['DeliveryStatus'],
                                    'district' => $office['District'],
                                    'state' => $office['State'],
                                ];
                            }, $postOffices)
                        ]
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'status' => 'Error',
                    'message' => $data[0]['Message'] ?? 'No records found.'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pincode details from provider.'
            ], 502);

        } catch (\Exception $e) {
            \Log::error('Pincode fetch error [' . get_class($e) . ']: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching pincode details.'
            ], 500);
        }
    }
}

