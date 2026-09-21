<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Billing
{
    /**
     * Razorpay billing period for "yearly" items. Normally 'yearly'. For real
     * renewal testing set BILLING_TEST_PERIOD=weekly (Razorpay's shortest cycle
     * is 7 days). Leave empty in production.
     */
    public static function razorpayPeriod(): string
    {
        return config('services.billing_test_period') === 'weekly' ? 'weekly' : 'yearly';
    }

    /**
     * End date of one billing period. Matches the Razorpay period above; on a
     * local machine BILLING_TEST_DAYS can shorten it further (leave empty in production).
     */
    public static function yearlyPeriodEnd(): Carbon
    {
        if (self::razorpayPeriod() === 'weekly') {
            return now()->addWeek();
        }

        $testDays = (int) config('services.billing_test_days');

        return $testDays > 0 ? now()->addDays($testDays) : now()->addYear();
    }
}
