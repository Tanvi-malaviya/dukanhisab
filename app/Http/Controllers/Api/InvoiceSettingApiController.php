<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceSettingApiController extends Controller
{
    public function show(Request $request)
    {
        $shopId = $request->attributes->get('shop_id');

        $settings = InvoiceConfig::firstOrCreate(['shop_id' => $shopId]);

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user && $user->activePlan && $user->activePlan->slug === 'free') {
            return response()->json(['message' => 'Please upgrade your plan to unlock invoice customization.'], 403);
        }

        $shopId = $request->attributes->get('shop_id');

        $validator = Validator::make($request->all(), [
            'starting_invoice_number' => 'nullable|integer|min:1',
            'auto_increment' => 'nullable|boolean',
            'date_format' => 'nullable|string|max:20',
            'paper_size' => 'nullable|string|in:A4,58mm,80mm',
            'theme_color' => 'nullable|string|max:10',
            'show_customer_address' => 'nullable|boolean',
            'show_customer_gst' => 'nullable|boolean',
            'show_hsn_code' => 'nullable|boolean',
            'show_discount' => 'nullable|boolean',
            'show_tax' => 'nullable|boolean',
            'show_sku' => 'nullable|boolean',
            'gst_enabled' => 'nullable|boolean',
            'round_off' => 'nullable|boolean',
            'tax_summary' => 'nullable|boolean',
            'show_upi_qr' => 'nullable|boolean',
            'show_bank_details' => 'nullable|boolean',
            'auto_print' => 'nullable|boolean',
            'whatsapp_share' => 'nullable|boolean',
            'pdf_download' => 'nullable|boolean',
            'email_invoice' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $validatedData = $validator->validated();
        if ($user && $user->activePlan && $user->activePlan->slug === 'free') {
            $validatedData['whatsapp_share'] = false;
            $validatedData['pdf_download'] = false;
            $validatedData['email_invoice'] = false;
            $validatedData['show_upi_qr'] = false;
        }

        $settings = InvoiceConfig::updateOrCreate(
            ['shop_id' => $shopId],
            $validatedData
        );

        return response()->json($settings);
    }
}
