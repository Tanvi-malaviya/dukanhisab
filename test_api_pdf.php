<?php

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

try {
    $sale = Sale::first();
    if (!$sale) {
        echo "No sales found!\n";
        exit;
    }
    
    // Get shop and check owner/user
    $shop = $sale->shop;
    echo "Shop name: " . $shop->name . ", User ID on shop: " . ($shop->user_id ?? 'NULL') . "\n";
    
    // Find the first user in DB or shop owner
    $user = User::first();
    if (!$user) {
        echo "No users found!\n";
        exit;
    }
    Auth::login($user);
    
    echo "Simulating request for Sale ID: {$sale->id}, Shop ID: {$sale->shop_id}, User ID: {$user->id}\n";
    
    // Create GET request to /api/v1/sales/{id}/invoice?locale=gu
    $request = Request::create('/api/v1/sales/' . $sale->id . '/invoice', 'GET', ['locale' => 'gu']);
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Shop-ID', $sale->shop_id);
    
    // Bind request to container
    $app->instance('request', $request);
    
    // Handle the request through the kernel/router
    $response = $kernel->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Content type: " . $response->headers->get('Content-Type') . "\n";
    
    // Check if the response contains PDF bytes
    $content = $response->getContent();
    echo "PDF bytes length: " . strlen($content) . "\n";
    
    // Write PDF to file
    file_put_contents('test_api_output.pdf', $content);
    echo "Saved to test_api_output.pdf\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
