<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Shop;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceApiController extends Controller
{
    public function generatePDF(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items.product', 'customer')->findOrFail($id);
        $shop = Shop::findOrFail($shopId);

        $logoUrl = '';
        if ($shop->logo) {
            $logoUrl = public_path('storage/' . $shop->logo);
        }

        // Build premium styled HTML for PDF invoice
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice - ' . $sale->sale_number . '</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    color: #333;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    padding: 30px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .header-logo {
                    width: 60px;
                    height: 60px;
                    object-fit: contain;
                    margin-right: 15px;
                }
                .shop-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #4f46e5;
                }
                .shop-details {
                    font-size: 12px;
                    color: #666;
                }
                .invoice-title {
                    font-size: 28px;
                    font-weight: bold;
                    color: #111827;
                    text-align: right;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: #4b5563;
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 40px;
                }
                .details-table td {
                    width: 50%;
                    vertical-align: top;
                }
                .section-title {
                    font-size: 11px;
                    text-transform: uppercase;
                    color: #9ca3af;
                    font-weight: bold;
                    letter-spacing: 1px;
                    margin-bottom: 5px;
                }
                .party-name {
                    font-size: 16px;
                    font-weight: bold;
                    color: #1f2937;
                }
                .party-info {
                    font-size: 13px;
                    color: #4b5563;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .items-table th {
                    background-color: #f9fafb;
                    border-bottom: 2px solid #e5e7eb;
                    text-align: left;
                    padding: 12px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #4b5563;
                    text-transform: uppercase;
                }
                .items-table td {
                    padding: 12px;
                    border-bottom: 1px solid #f3f4f6;
                    font-size: 13px;
                }
                .total-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .total-label {
                    text-align: right;
                    padding: 8px 12px;
                    font-size: 13px;
                    color: #4b5563;
                }
                .total-value {
                    text-align: right;
                    width: 120px;
                    padding: 8px 12px;
                    font-size: 13px;
                    color: #111827;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #111827;
                    border-top: 2px solid #e5e7eb;
                }
                .grand-total-value {
                    text-align: right;
                    width: 120px;
                    padding: 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #4f46e5;
                    border-top: 2px solid #e5e7eb;
                }
                .footer {
                    margin-top: 60px;
                    text-align: center;
                    font-size: 12px;
                    color: #9ca3af;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <table class="header-table">
                    <tr>
                        <td style="vertical-align: middle;">';
                        if ($shop->logo && file_exists($logoUrl)) {
                            $html .= '<img class="header-logo" src="data:image/png;base64,' . base64_encode(file_get_contents($logoUrl)) . '" align="left" />';
                        }
                        $html .= '
                            <div>
                                <span class="shop-name">' . htmlspecialchars($shop->name) . '</span><br>
                                <span class="shop-details">
                                    ' . ($shop->address ? htmlspecialchars($shop->address) . '<br>' : '') . '
                                    Mobile: ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '
                                    ' . ($shop->gst_number ? ' | GSTIN: ' . htmlspecialchars($shop->gst_number) : '') . '
                                </span>
                            </div>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            INVOICE
                            <div class="invoice-meta">
                                <strong>Invoice No:</strong> ' . htmlspecialchars($sale->sale_number) . '<br>
                                <strong>Date:</strong> ' . $sale->sale_date->format('d M, Y h:i A') . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="details-table">
                    <tr>
                        <td>
                            <div class="section-title">Billing To</div>
                            <div class="party-name">' . htmlspecialchars($sale->customer->name ?? 'Walk-In Customer') . '</div>
                            <div class="party-info">
                                ' . ($sale->customer && $sale->customer->mobile ? 'Mobile: ' . htmlspecialchars($sale->customer->mobile) . '<br>' : '') . '
                                ' . ($sale->customer && $sale->customer->email ? 'Email: ' . htmlspecialchars($sale->customer->email) : '') . '
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="section-title">Payment Info</div>
                            <div class="party-info">
                                <strong>Payment Status:</strong> ' . ($sale->status === 'Returned' ? '<span style="color:#ef4444;">Returned</span>' : 'Paid') . '<br>
                                <strong>Method:</strong> ' . htmlspecialchars($sale->payment_type) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th>Product Name</th>
                            <th style="width: 80px; text-align: right;">Price</th>
                            <th style="width: 80px; text-align: center;">Qty</th>';
        
        $isReturned = ($sale->status === 'Returned' || $sale->status === 'Partially Returned');
        $hasReturnedQty = false;
        foreach ($sale->items as $item) {
            if (($item->returned_quantity ?? 0) > 0) {
                $hasReturnedQty = true;
                break;
            }
        }
        
        if ($isReturned) {
            $html .= '
                            <th style="width: 80px; text-align: center;">Returned</th>
                            <th style="width: 80px; text-align: center;">Net Qty</th>';
        }
        
        $html .= '
                            <th style="width: 100px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
                    $i = 1;
                    foreach ($sale->items as $item) {
                        $returnedQty = 0;
                        $netQty = $item->quantity;
                        if (($item->returned_quantity ?? 0) > 0) {
                            $returnedQty = $item->returned_quantity;
                            $netQty = $item->quantity - $returnedQty;
                        } elseif (!$hasReturnedQty && $sale->status === 'Returned') {
                            $returnedQty = $item->quantity;
                            $netQty = 0;
                        }

                        $html .= '
                        <tr>
                            <td style="text-align: center;">' . $i++ . '</td>
                            <td>' . htmlspecialchars($item->product->name ?? 'Unknown Product') . '</td>
                            <td style="text-align: right;">Rs. ' . number_format($item->selling_price, 2) . '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>';
                        
                        if ($isReturned) {
                            $html .= '
                            <td style="text-align: center; color: #ef4444; font-weight: bold;">' . $returnedQty . '</td>
                            <td style="text-align: center; font-weight: bold;">' . $netQty . '</td>';
                        }

                        $html .= '
                            <td style="text-align: right;">Rs. ' . number_format($item->selling_price * $netQty, 2) . '</td>
                        </tr>';
                    }

                    $html .= '
                    </tbody>
                </table>
 
                <table class="total-table" align="right" style="width: 40%; margin-left: auto;">
                    <tr>
                        <td class="total-label">Subtotal:</td>
                        <td class="total-value">Rs. ' . number_format($sale->subtotal, 2) . '</td>
                    </tr>
                    <tr>
                        <td class="total-label">Discount:</td>
                        <td class="total-value">-Rs. ' . number_format($sale->discount, 2) . '</td>
                    </tr>
                    <tr>
                        <td class="grand-total-label">Grand Total:</td>
                        <td class="grand-total-value">Rs. ' . number_format($sale->grand_total, 2) . '</td>
                    </tr>
                </table>

                <div style="clear: both;"></div>

                <div class="footer">
                    Thank you for your business!<br>
                    Powered by DukanHisab
                </div>
            </div>
        </body>
        </html>
        ';

        $pdf = Pdf::loadHTML($html);
        return $pdf->stream('Invoice-' . $sale->sale_number . '.pdf');
    }

    public function generatePurchasePDF(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = \App\Models\Purchase::where('shop_id', $shopId)->with('items.product', 'supplier')->findOrFail($id);
        $shop = Shop::findOrFail($shopId);

        $logoUrl = '';
        if ($shop->logo) {
            $logoUrl = public_path('storage/' . $shop->logo);
        }

        // Build premium styled HTML for PDF invoice
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Purchase Invoice - ' . $purchase->purchase_number . '</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    color: #333;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    padding: 30px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .header-logo {
                    width: 60px;
                    height: 60px;
                    object-fit: contain;
                    margin-right: 15px;
                }
                .shop-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #4f46e5;
                }
                .shop-details {
                    font-size: 12px;
                    color: #666;
                }
                .invoice-title {
                    font-size: 28px;
                    font-weight: bold;
                    color: #111827;
                    text-align: right;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: #4b5563;
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 40px;
                }
                .details-table td {
                    width: 50%;
                    vertical-align: top;
                }
                .section-title {
                    font-size: 11px;
                    text-transform: uppercase;
                    color: #9ca3af;
                    font-weight: bold;
                    letter-spacing: 1px;
                    margin-bottom: 5px;
                }
                .party-name {
                    font-size: 16px;
                    font-weight: bold;
                    color: #1f2937;
                }
                .party-info {
                    font-size: 13px;
                    color: #4b5563;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .items-table th {
                    background-color: #f9fafb;
                    border-bottom: 2px solid #e5e7eb;
                    text-align: left;
                    padding: 12px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #4b5563;
                    text-transform: uppercase;
                }
                .items-table td {
                    padding: 12px;
                    border-bottom: 1px solid #f3f4f6;
                    font-size: 13px;
                }
                .total-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .total-label {
                    text-align: right;
                    padding: 8px 12px;
                    font-size: 13px;
                    color: #4b5563;
                }
                .total-value {
                    text-align: right;
                    width: 120px;
                    padding: 8px 12px;
                    font-size: 13px;
                    color: #111827;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #111827;
                    border-top: 2px solid #e5e7eb;
                }
                .grand-total-value {
                    text-align: right;
                    width: 120px;
                    padding: 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #4f46e5;
                    border-top: 2px solid #e5e7eb;
                }
                .footer {
                    margin-top: 60px;
                    text-align: center;
                    font-size: 12px;
                    color: #9ca3af;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <table class="header-table">
                    <tr>
                        <td style="vertical-align: middle;">';
                        if ($shop->logo && file_exists($logoUrl)) {
                            $html .= '<img class="header-logo" src="data:image/png;base64,' . base64_encode(file_get_contents($logoUrl)) . '" align="left" />';
                        }
                        $html .= '
                            <div>
                                <span class="shop-name">' . htmlspecialchars($shop->name) . '</span><br>
                                <span class="shop-details">
                                    ' . ($shop->address ? htmlspecialchars($shop->address) . '<br>' : '') . '
                                    Mobile: ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '
                                    ' . ($shop->gst_number ? ' | GSTIN: ' . htmlspecialchars($shop->gst_number) : '') . '
                                </span>
                            </div>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            PURCHASE INVOICE
                            <div class="invoice-meta">
                                <strong>Invoice No:</strong> ' . htmlspecialchars($purchase->purchase_number) . '<br>
                                <strong>Date:</strong> ' . $purchase->purchase_date->format('d M, Y h:i A') . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="details-table">
                    <tr>
                        <td>
                            <div class="section-title">Supplier Details</div>
                            <div class="party-name">' . htmlspecialchars($purchase->supplier->name ?? 'Walk-In Supplier') . '</div>
                            <div class="party-info">
                                ' . ($purchase->supplier && $purchase->supplier->mobile ? 'Mobile: ' . htmlspecialchars($purchase->supplier->mobile) . '<br>' : '') . '
                                ' . ($purchase->supplier && $purchase->supplier->email ? 'Email: ' . htmlspecialchars($purchase->supplier->email) : '') . '
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="section-title">Payment Info</div>
                            <div class="party-info">
                                <strong>Method:</strong> ' . htmlspecialchars($purchase->payment_type) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th>Product Name</th>
                            <th style="width: 80px; text-align: right;">Unit Price</th>
                            <th style="width: 80px; text-align: center;">Qty</th>';

        $isReturned = ($purchase->status === 'Returned' || $purchase->status === 'Partially Returned');
        $hasReturnedQty = false;
        foreach ($purchase->items as $item) {
            if (($item->returned_quantity ?? 0) > 0) {
                $hasReturnedQty = true;
                break;
            }
        }

        if ($isReturned) {
            $html .= '
                            <th style="width: 80px; text-align: center;">Returned</th>
                            <th style="width: 80px; text-align: center;">Net Qty</th>';
        }

        $html .= '
                            <th style="width: 100px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
                    $i = 1;
                    foreach ($purchase->items as $item) {
                        $returnedQty = 0;
                        $netQty = $item->quantity;
                        if (($item->returned_quantity ?? 0) > 0) {
                            $returnedQty = $item->returned_quantity;
                            $netQty = $item->quantity - $returnedQty;
                        } elseif (!$hasReturnedQty && $purchase->status === 'Returned') {
                            $returnedQty = $item->quantity;
                            $netQty = 0;
                        }

                        $html .= '
                        <tr>
                            <td style="text-align: center;">' . $i++ . '</td>
                            <td>' . htmlspecialchars($item->product->name ?? 'Deleted Product') . '</td>
                            <td style="text-align: right;">Rs. ' . number_format($item->purchase_price, 2) . '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>';

                        if ($isReturned) {
                            $html .= '
                            <td style="text-align: center; color: #ef4444; font-weight: bold;">' . $returnedQty . '</td>
                            <td style="text-align: center; font-weight: bold;">' . $netQty . '</td>';
                        }

                        $html .= '
                            <td style="text-align: right;">Rs. ' . number_format($item->purchase_price * $netQty, 2) . '</td>
                        </tr>';
                    }

                    $html .= '
                    </tbody>
                </table>
 
                <table class="total-table" align="right" style="width: 40%; margin-left: auto;">
                    <tr>
                        <td class="grand-total-label">Total Amount:</td>
                        <td class="grand-total-value">Rs. ' . number_format($purchase->total_amount, 2) . '</td>
                    </tr>
                </table>

                <div style="clear: both;"></div>

                <div class="footer">
                    Powered by DukanHisab
                </div>
            </div>
        </body>
        </html>
        ';

        $pdf = Pdf::loadHTML($html);
        return $pdf->stream('PurchaseInvoice-' . $purchase->purchase_number . '.pdf');
    }
}
