<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BillingMail
{
    /**
     * Email a user about a change to their subscription plan or add-on.
     * Never throws: a mail failure must not break the admin action.
     *
     * @param array<string,string> $details label => value rows shown in the email
     */
    public static function send(?User $user, string $subject, string $headline, string $message, array $details = []): void
    {
        if (!$user || empty($user->email)) {
            return;
        }

        try {
            Mail::send('shopowner.emails.billing-update', [
                'user' => $user,
                'headline' => $headline,
                'messageText' => $message,
                'details' => $details,
            ], function ($mail) use ($user, $subject) {
                $mail->to($user->email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error('Billing email failed: ' . $e->getMessage());
        }
    }
}
