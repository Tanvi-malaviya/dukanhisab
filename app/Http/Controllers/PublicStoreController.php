<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;

class PublicStoreController extends Controller
{
    public function show($subdomain)
    {
        // Search shop by website_settings->subdomain
        $shop = Shop::where('website_settings->subdomain', $subdomain)->first();

        if (!$shop) {
            $shop = Shop::all()->first(function($s) use ($subdomain) {
                return \Illuminate\Support\Str::slug($s->name) === $subdomain;
            });
        }

        // If not found or website not enabled, throw 404
        if (!$shop || !isset($shop->website_settings['enabled']) || !$shop->website_settings['enabled']) {
            abort(404, 'Store not found or is currently offline.');
        }

        $settings = $shop->website_settings;
        $products = Product::where('shop_id', $shop->id)
            ->with('category')
            ->orderBy('name')
            ->get();

        // Group products by category name for cleaner UI filtering
        $categories = $products->pluck('category.name')->unique()->filter()->values();

        return view('public_store', compact('shop', 'settings', 'products', 'categories'));
    }
}
