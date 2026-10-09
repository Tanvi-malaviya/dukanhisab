<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountDeletionController extends Controller
{
    /**
     * Display the public Account & Data Deletion policy page and request form.
     * Required by Google Play Developer Policy and provides self-service web deletion.
     */
    public function show()
    {
        return view('auth.delete-account');
    }

    /**
     * Process direct self-service account deletion from the public web form.
     */
    public function processDeletion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|string',
            'password' => 'required|string',
            'confirm_understanding' => 'required|accepted',
            'reason' => 'nullable|string|max:255',
        ], [
            'identity.required' => 'Please enter your registered email address or mobile number.',
            'password.required' => 'Please enter your account password to verify ownership.',
            'confirm_understanding.accepted' => 'You must check the confirmation box acknowledging permanent data deletion.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        $identity = trim($request->input('identity'));
        $user = User::where('email', $identity)
            ->orWhere('mobile', $identity)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'identity' => 'Invalid credentials. Please verify your mobile/email and password.',
            ])->withInput($request->except('password'));
        }

        $userId = $user->id;
        $userName = $user->name;
        $userEmail = $user->email;
        $reason = $request->input('reason', 'Not specified');

        DB::transaction(function () use ($user, $userId, $userName, $userEmail, $reason) {
            // Set all associated shops to inactive
            foreach ($user->shops as $shop) {
                $shop->update(['status' => 'inactive']);
            }

            // Revoke all Sanctum API tokens
            $user->tokens()->delete();

            // Mark user status as deleted
            $user->update(['status' => 'deleted']);

            // Record audit log
            AuditLog::log("Account deleted via Web Form: #{$userId} ({$userName} - {$userEmail}). Reason: {$reason}", [
                'action_type' => 'account_deleted',
                'reason' => $reason,
                'user_id' => $userId,
            ], $userId);
        });

        return back()->with('success_deleted', 'Your DukanHisab account and all associated shop data have been permanently deleted.');
    }
}
