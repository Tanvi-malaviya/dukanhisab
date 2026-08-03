<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Shop;
use App\Models\InvoiceConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class InvoiceApiController extends Controller
{
    public function generatePDF(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items.product', 'customer')->findOrFail($id);
        $html = $this->buildSaleInvoiceHtml($sale);
        $pdf = Pdf::loadHTML($html);
        return $pdf->stream('Invoice-' . $sale->sale_number . '.pdf');
    }

    public function emailSaleInvoice(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items.product', 'customer')->findOrFail($id);

        if (!$sale->customer || !$sale->customer->email) {
            return response()->json(['message' => 'This customer does not have an email address on file.'], 400);
        }

        $shop = Shop::findOrFail($shopId);
        $html = $this->buildSaleInvoiceHtml($sale);
        $pdfContent = Pdf::loadHTML($html)->output();

        Mail::send('shopowner.emails.sale-invoice', ['sale' => $sale, 'shop' => $shop], function ($message) use ($sale, $shop, $pdfContent) {
            $message->to($sale->customer->email)
                ->subject('Invoice ' . $sale->sale_number . ' from ' . $shop->name)
                ->attachData($pdfContent, 'Invoice-' . $sale->sale_number . '.pdf', ['mime' => 'application/pdf']);
        });

        return response()->json(['message' => 'Invoice emailed to ' . $sale->customer->email . ' successfully.']);
    }

    public function generatePurchasePDF(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items.product', 'supplier')->findOrFail($id);
        $html = $this->buildPurchaseInvoiceHtml($purchase);
        $pdf = Pdf::loadHTML($html);
        return $pdf->stream('PurchaseInvoice-' . $purchase->purchase_number . '.pdf');
    }

    public function emailPurchaseInvoice(Request $request, $id)
    {
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items.product', 'supplier')->findOrFail($id);

        if (!$purchase->supplier || !$purchase->supplier->email) {
            return response()->json(['message' => 'This supplier does not have an email address on file.'], 400);
        }

        $shop = Shop::findOrFail($shopId);
        $html = $this->buildPurchaseInvoiceHtml($purchase);
        $pdfContent = Pdf::loadHTML($html)->output();

        Mail::send('shopowner.emails.purchase-invoice', ['purchase' => $purchase, 'shop' => $shop], function ($message) use ($purchase, $shop, $pdfContent) {
            $message->to($purchase->supplier->email)
                ->subject('Purchase Invoice ' . $purchase->purchase_number . ' from ' . $shop->name)
                ->attachData($pdfContent, 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf', ['mime' => 'application/pdf']);
        });

        return response()->json(['message' => 'Invoice emailed to ' . $purchase->supplier->email . ' successfully.']);
    }

    private function buildSaleInvoiceHtml(Sale $sale): string
    {
        $shop = Shop::findOrFail($sale->shop_id);
        $invoiceConfig = InvoiceConfig::firstOrCreate(['shop_id' => $sale->shop_id]);
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $textColor = $this->contrastTextColor($themeColor);

        $logoUrl = '';
        if ($shop->logo) {
            $logoUrl = public_path('storage/' . $shop->logo);
        }

        $signatureUrl = '';
        if ($shop->signature) {
            $signatureUrl = public_path('storage/' . $shop->signature);
        }

        $qrBase64 = null;
        if ($invoiceConfig->show_upi_qr && $shop->upi_id) {
            $qrBase64 = $this->fetchQrCodeBase64($shop->upi_id, $shop->name, $sale->grand_total);
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
                    background-color: ' . $themeColor . ';
                }
                .header-table td {
                    padding: 20px;
                }
                .header-logo {
                    width: 50px;
                    height: 50px;
                    object-fit: contain;
                    display: block;
                }
                .shop-name {
                    font-size: 22px;
                    font-weight: bold;
                    line-height: 1;
                    color: ' . $textColor . ';
                }
                .shop-details {
                    font-size: 12px;
                    line-height: 1.5;
                    color: ' . $textColor . ';
                }
                .invoice-title {
                    font-size: 26px;
                    font-weight: bold;
                    color: ' . $textColor . ';
                    text-align: right;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: ' . $textColor . ';
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
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
                    margin-bottom: 20px;
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
                .footer-row-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .footer-row-table td {
                    vertical-align: top;
                }
                .qr-cell {
                    width: 45%;
                    text-align: left;
                }
                .qr-cell img {
                    width: 80px;
                    height: 80px;
                }
                .bank-details {
                    font-size: 11px;
                    color: #6b7280;
                    white-space: pre-line;
                    margin-top: 8px;
                }
                .total-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .total-label {
                    text-align: right;
                    padding: 6px 12px;
                    font-size: 13px;
                    color: #4b5563;
                }
                .total-value {
                    text-align: right;
                    width: 120px;
                    padding: 6px 12px;
                    font-size: 13px;
                    color: #111827;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 10px 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #111827;
                    border-top: 2px solid #e5e7eb;
                }
                .grand-total-value {
                    text-align: right;
                    width: 120px;
                    padding: 10px 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: ' . $themeColor . ';
                    border-top: 2px solid #e5e7eb;
                }
                .invoice-footer-text {
                    margin-top: 25px;
                    text-align: center;
                    font-size: 12px;
                    color: #6b7280;
                }
                .signature-img {
                    margin-top: 15px;
                    text-align: right;
                }
                .signature-img img {
                    height: 40px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #9ca3af;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 15px;
                }
                .brand-highlight {
                    color: ' . $themeColor . ';
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <table class="header-table">
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;"><tr>';
                        if ($shop->logo && file_exists($logoUrl)) {
                            $html .= '<td style="vertical-align: top; width: 50px; padding: 0 10px 0 0; line-height: 0;"><img class="header-logo" style="vertical-align: top;" src="data:image/png;base64,' . base64_encode(file_get_contents($logoUrl)) . '" /></td>';
                        }
                        $html .= '
                                <td style="vertical-align: top; padding: 0;">
                                    <span class="shop-name">' . htmlspecialchars($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        Mobile: ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '<br>
                                        ' . ($shop->address ? htmlspecialchars($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? 'GSTIN: ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            INVOICE
                            <div class="invoice-meta">
                                <strong>Invoice No:</strong> ' . htmlspecialchars($sale->sale_number) . '<br>
                                <strong>Date:</strong> ' . $sale->sale_date->timezone('Asia/Kolkata')->format('d M, Y h:i A') . '
                                ' . (($sale->status === 'Completed' && $sale->payment_type === 'Credit' && $sale->updated_at) ? '<br><strong>Paid Date:</strong> ' . $sale->updated_at->timezone('Asia/Kolkata')->format('d M, Y h:i A') : '') . '
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
                                <strong>Payment Status:</strong> ' . (
                                    $sale->status === 'Returned'
                                        ? '<span style="color:#ef4444;">Returned</span>'
                                        : ($sale->status === 'Partially Returned'
                                            ? '<span style="color:#f59e0b;">Partially Returned</span>'
                                            : ($sale->status === 'Unpaid'
                                                ? '<span style="color:#f59e0b;font-weight:bold;">Unpaid</span>'
                                                : ($sale->payment_type === 'Credit'
                                                    ? '<span style="color:#10b981;font-weight:bold;">Completed</span>'
                                                    : '<span style="color:#10b981;font-weight:bold;">Paid</span>')))
                                ) . '<br>
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

                <table class="footer-row-table">
                    <tr>
                        <td class="qr-cell">';
                        if ($qrBase64) {
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" /><br><span style="font-size:10px;color:#9ca3af;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td>
                            <table class="total-table">
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
                        </td>
                    </tr>
                </table>

                <div class="invoice-footer-text">' . htmlspecialchars($shop->invoice_footer ?: 'Thank you for your business!') . '</div>';

                if ($shop->signature && file_exists($signatureUrl)) {
                    $html .= '<div class="signature-img"><img src="data:image/png;base64,' . base64_encode(file_get_contents($signatureUrl)) . '" /></div>';
                }

                $html .= '
                <div class="footer">
                    Powered by <span class="brand-highlight">DukanHisab</span>
                </div>
            </div>
        </body>
        </html>
        ';

        return $html;
    }

    private function buildPurchaseInvoiceHtml(Purchase $purchase): string
    {
        $shop = Shop::findOrFail($purchase->shop_id);
        $invoiceConfig = InvoiceConfig::firstOrCreate(['shop_id' => $purchase->shop_id]);
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $textColor = $this->contrastTextColor($themeColor);

        $logoUrl = '';
        if ($shop->logo) {
            $logoUrl = public_path('storage/' . $shop->logo);
        }

        $signatureUrl = '';
        if ($shop->signature) {
            $signatureUrl = public_path('storage/' . $shop->signature);
        }

        $qrBase64 = null;
        if ($invoiceConfig->show_upi_qr && $shop->upi_id) {
            $qrBase64 = $this->fetchQrCodeBase64($shop->upi_id, $shop->name, $purchase->total_amount);
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
                    background-color: ' . $themeColor . ';
                }
                .header-table td {
                    padding: 20px;
                }
                .header-logo {
                    width: 50px;
                    height: 50px;
                    object-fit: contain;
                    display: block;
                }
                .shop-name {
                    font-size: 22px;
                    font-weight: bold;
                    line-height: 1;
                    color: ' . $textColor . ';
                }
                .shop-details {
                    font-size: 12px;
                    line-height: 1.5;
                    color: ' . $textColor . ';
                }
                .invoice-title {
                    font-size: 26px;
                    font-weight: bold;
                    color: ' . $textColor . ';
                    text-align: right;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: ' . $textColor . ';
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
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
                    margin-bottom: 20px;
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
                .footer-row-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .footer-row-table td {
                    vertical-align: top;
                }
                .qr-cell {
                    width: 45%;
                    text-align: left;
                }
                .qr-cell img {
                    width: 80px;
                    height: 80px;
                }
                .bank-details {
                    font-size: 11px;
                    color: #6b7280;
                    white-space: pre-line;
                    margin-top: 8px;
                }
                .total-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 10px 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: #111827;
                    border-top: 2px solid #e5e7eb;
                }
                .grand-total-value {
                    text-align: right;
                    width: 120px;
                    padding: 10px 12px;
                    font-size: 16px;
                    font-weight: bold;
                    color: ' . $themeColor . ';
                    border-top: 2px solid #e5e7eb;
                }
                .invoice-footer-text {
                    margin-top: 25px;
                    text-align: center;
                    font-size: 12px;
                    color: #6b7280;
                }
                .signature-img {
                    margin-top: 15px;
                    text-align: right;
                }
                .signature-img img {
                    height: 40px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #9ca3af;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 15px;
                }
                .brand-highlight {
                    color: ' . $themeColor . ';
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <table class="header-table">
                    <tr>
                        <td style="vertical-align: top;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;"><tr>';
                        if ($shop->logo && file_exists($logoUrl)) {
                            $html .= '<td style="vertical-align: top; width: 50px; padding: 0 10px 0 0; line-height: 0;"><img class="header-logo" style="vertical-align: top;" src="data:image/png;base64,' . base64_encode(file_get_contents($logoUrl)) . '" /></td>';
                        }
                        $html .= '
                                <td style="vertical-align: top; padding: 0;">
                                    <span class="shop-name">' . htmlspecialchars($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        Mobile: ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '<br>
                                        ' . ($shop->address ? htmlspecialchars($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? 'GSTIN: ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            PURCHASE INVOICE
                            <div class="invoice-meta">
                                <strong>Invoice No:</strong> ' . htmlspecialchars($purchase->purchase_number) . '<br>
                                <strong>Date:</strong> ' . $purchase->purchase_date->timezone('Asia/Kolkata')->format('d M, Y h:i A') . '
                                ' . (($purchase->status === 'Completed' && $purchase->payment_type === 'Credit' && $purchase->updated_at) ? '<br><strong>Paid Date:</strong> ' . $purchase->updated_at->timezone('Asia/Kolkata')->format('d M, Y h:i A') : '') . '
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
                                <strong>Payment Status:</strong> ' . (
                                    $purchase->status === 'Returned'
                                        ? '<span style="color:#ef4444;">Returned</span>'
                                        : ($purchase->status === 'Partially Returned'
                                            ? '<span style="color:#f59e0b;">Partially Returned</span>'
                                            : ($purchase->status === 'Unpaid'
                                                ? '<span style="color:#f59e0b;font-weight:bold;">Unpaid</span>'
                                                : ($purchase->payment_type === 'Credit'
                                                    ? '<span style="color:#10b981;font-weight:bold;">Completed</span>'
                                                    : '<span style="color:#10b981;font-weight:bold;">Paid</span>')))
                                ) . '<br>
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

                <table class="footer-row-table">
                    <tr>
                        <td class="qr-cell">';
                        if ($qrBase64) {
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" /><br><span style="font-size:10px;color:#9ca3af;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td>
                            <table class="total-table">
                                <tr>
                                    <td class="grand-total-label">Total Amount:</td>
                                    <td class="grand-total-value">Rs. ' . number_format($purchase->total_amount, 2) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="invoice-footer-text">' . htmlspecialchars($shop->invoice_footer ?: 'Thank you for your business!') . '</div>';

                if ($shop->signature && file_exists($signatureUrl)) {
                    $html .= '<div class="signature-img"><img src="data:image/png;base64,' . base64_encode(file_get_contents($signatureUrl)) . '" /></div>';
                }

                $html .= '
                <div class="footer">
                    Powered by <span class="brand-highlight">DukanHisab</span>
                </div>
            </div>
        </body>
        </html>
        ';

        return $html;
    }

    private function contrastTextColor(?string $hex): string
    {
        $clean = ltrim($hex ?: '0F766E', '#');
        if (strlen($clean) === 3) {
            $clean = $clean[0] . $clean[0] . $clean[1] . $clean[1] . $clean[2] . $clean[2];
        }
        if (strlen($clean) !== 6) {
            return '#0f172a';
        }
        $r = hexdec(substr($clean, 0, 2));
        $g = hexdec(substr($clean, 2, 2));
        $b = hexdec(substr($clean, 4, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        // Only genuinely dark shades (black, dark chocolate/brown, navy, etc.)
        // should get white text — everything else defaults to dark text.
        return $yiq >= 60 ? '#0f172a' : '#ffffff';
    }

    private function fetchQrCodeBase64(string $upiId, ?string $shopName, $amount): ?string
    {
        try {
            $data = 'upi://pay?pa=' . $upiId . '&pn=' . ($shopName ?: 'Shop') . '&am=' . number_format((float) $amount, 2, '.', '') . '&cu=INR';
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($data);
            $contents = @file_get_contents($url);
            return $contents ? base64_encode($contents) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
