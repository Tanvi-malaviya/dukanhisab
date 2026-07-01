<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;

class InvoiceSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'default_logo' => InvoiceSetting::get('default_logo', ''),
            'watermark' => InvoiceSetting::get('watermark', 'no'),
            'footer_text' => InvoiceSetting::get('footer_text', 'Thank you for shopping with us!'),
            'invoice_prefix' => InvoiceSetting::get('invoice_prefix', 'DH-'),
        ];

        return view('admin.settings.invoice', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'default_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'watermark' => 'required|in:yes,no',
            'footer_text' => 'nullable|string|max:500',
            'invoice_prefix' => 'required|string|max:10',
        ]);

        if ($request->hasFile('default_logo')) {
            // Delete old logo if exists
            $oldLogo = InvoiceSetting::get('default_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            $path = $request->file('default_logo')->store('invoice_logos', 'public');
            InvoiceSetting::set('default_logo', $path);
        }

        InvoiceSetting::set('watermark', $request->input('watermark'));
        InvoiceSetting::set('footer_text', $request->input('footer_text'));
        InvoiceSetting::set('invoice_prefix', $request->input('invoice_prefix'));

        AuditLog::log('Updated platform global invoice settings');

        return back()->with('success', 'Invoice settings updated successfully.');
    }
}
