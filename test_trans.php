<?php

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$locales = ['en', 'gu', 'hi'];
$keys = [
    'invoice',
    'invoice_no',
    'mobile',
    'date',
    'paid_date',
    'bill_to',
    'walk_in_customer',
    'payment_info',
    'payment_status',
    'returned',
    'partially_returned',
    'unpaid',
    'paid',
    'method',
    'product_name',
    'price',
    'qty',
    'discount',
    'subtotal',
    'grand_total',
    'upi_payment',
    'bank_payment',
    'authorized_signatory',
];

foreach ($locales as $locale) {
    app()->setLocale($locale);
    echo "Locale: $locale\n";
    foreach ($keys as $key) {
        echo "  $key => " . __($key) . "\n";
    }
    echo "\n";
}
