<?php

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Http\Controllers\Api\InvoiceApiController;
use Illuminate\Http\Request;

try {
    $sale = Sale::first();
    if (!$sale) {
        echo "No sales found in database!\n";
        exit;
    }
    
    // Set locale to gu
    app()->setLocale('gu');
    echo "App Locale set to: " . app()->getLocale() . "\n";

    // Replicate generatePDF
    $controller = new InvoiceApiController();
    
    // Construct request with attributes
    $request = Request::create('/api/v1/sales/' . $sale->id . '/invoice', 'GET', ['locale' => 'gu']);
    $request->attributes->set('shop_id', $sale->shop_id);
    $app->instance('request', $request);

    // Call private buildSaleInvoiceHtml using reflection
    $reflector = new \ReflectionClass(InvoiceApiController::class);
    $method = $reflector->getMethod('buildSaleInvoiceHtml');
    $method->setAccessible(true);
    
    $html = $method->invoke($controller, $sale);
    
    // Save to test_output.html
    file_put_contents('test_output.html', $html);
    echo "Saved to test_output.html\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
