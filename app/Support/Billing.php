<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Billing
{
    /**
     * End date of one yearly billing period. Set BILLING_TEST_DAYS in .env to
     * shorten it for renewal testing (leave empty in production).
     */
    public static function yearlyPeriodEnd(): Carbon
    {
        $testDays = (int) config('services.billing_test_days');

        return $testDays > 0 ? now()->addDays($testDays) : now()->addYear();
    }
}
