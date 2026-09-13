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
        return $this->renderPdf($html, 'Invoice-' . $sale->sale_number . '.pdf', true);
    }

    public function emailSaleInvoice(Request $request, $id)
    {
        $user = $request->user();
        if ($user && $user->activePlan && $user->activePlan->slug === 'free') {
            return response()->json(['message' => 'Please upgrade your plan to unlock email invoice sharing.'], 403);
        }
        $shopId = $request->attributes->get('shop_id');
        $sale = Sale::where('shop_id', $shopId)->with('items.product', 'customer')->findOrFail($id);

        if (!$sale->customer || !$sale->customer->email) {
            return response()->json(['message' => 'This customer does not have an email address on file.'], 400);
        }

        $shop = Shop::findOrFail($shopId);
        $html = $this->buildSaleInvoiceHtml($sale);
        $pdfContent = $this->renderPdf($html, 'Invoice-' . $sale->sale_number . '.pdf', false);

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
        return $this->renderPdf($html, 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf', true);
    }

    public function emailPurchaseInvoice(Request $request, $id)
    {
        $user = $request->user();
        if ($user && $user->activePlan && $user->activePlan->slug === 'free') {
            return response()->json(['message' => 'Please upgrade your plan to unlock email invoice sharing.'], 403);
        }
        $shopId = $request->attributes->get('shop_id');
        $purchase = Purchase::where('shop_id', $shopId)->with('items.product', 'supplier')->findOrFail($id);

        if (!$purchase->supplier || !$purchase->supplier->email) {
            return response()->json(['message' => 'This supplier does not have an email address on file.'], 400);
        }

        $shop = Shop::findOrFail($shopId);
        $html = $this->buildPurchaseInvoiceHtml($purchase);
        $pdfContent = $this->renderPdf($html, 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf', false);

        Mail::send('shopowner.emails.purchase-invoice', ['purchase' => $purchase, 'shop' => $shop], function ($message) use ($purchase, $shop, $pdfContent) {
            $message->to($purchase->supplier->email)
                ->subject('Purchase Invoice ' . $purchase->purchase_number . ' from ' . $shop->name)
                ->attachData($pdfContent, 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf', ['mime' => 'application/pdf']);
        });

        return response()->json(['message' => 'Invoice emailed to ' . $purchase->supplier->email . ' successfully.']);
    }

    private function renderPdf(string $html, string $filename, bool $stream = true)
    {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->getOptions()->set('isFontSubsettingEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);

        if ($stream) {
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } else {
            return $pdf->output();
        }
    }

    private function buildSaleInvoiceHtml(Sale $sale): string
    {
        $shop = Shop::findOrFail($sale->shop_id);
        $invoiceConfig = InvoiceConfig::firstOrCreate(['shop_id' => $sale->shop_id]);
        $dateFormat = $invoiceConfig->date_format ?: ($shop->owner->date_format ?? 'DD/MM/YYYY');
        $timeFormat = $shop->owner->time_format ?? '12h';
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $textColor = $this->contrastTextColor($themeColor);

        // Force locale from request parameters/headers to ensure correct translations in PDF generation
        $request = request();
        $locale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language')
            ?? app()->getLocale();

        // Clean and validate locale
        $locale = strtolower(trim($locale));
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }

        // Force English locale for all PDF invoices for now to bypass font cache permissions issues
        app()->setLocale('en');
        $locale = 'en';

        $fontFaceStyles = '';
        if ($locale === 'gu') {
            $fontFaceStyles = '
                @font-face {
                    font-family: "NotoSansGujarati";
                    font-style: normal;
                    font-weight: 400;
                    src: url("' . public_path('fonts/NotoSansGujarati-Regular.ttf') . '") format("truetype");
                }
                @font-face {
                    font-family: "NotoSansGujarati";
                    font-style: normal;
                    font-weight: 700;
                    src: url("' . public_path('fonts/NotoSansGujarati-Bold.ttf') . '") format("truetype");
                }';
        } elseif ($locale === 'hi') {
            $fontFaceStyles = '
                @font-face {
                    font-family: "NotoSansDevanagari";
                    font-style: normal;
                    font-weight: 400;
                    src: url("' . public_path('fonts/NotoSansDevanagari-Regular.ttf') . '") format("truetype");
                }
                @font-face {
                    font-family: "NotoSansDevanagari";
                    font-style: normal;
                    font-weight: 700;
                    src: url("' . public_path('fonts/NotoSansDevanagari-Bold.ttf') . '") format("truetype");
                }';
        }

        $badgeHtml = '';
        if ($sale->status === 'Returned') {
            $badgeHtml = ' <span class="status-badge">' . __('returned') . '</span>';
        } elseif ($sale->status === 'Partially Returned') {
            $badgeHtml = ' <span class="status-badge">' . __('partially_returned') . '</span>';
        }

        $logoBase64 = $this->getBase64Image($shop->logo);
        $signatureBase64 = $this->getBase64Image($shop->signature);

        $qrBase64 = null;
        if ($invoiceConfig->show_upi_qr && $shop->upi_id) {
            $qrBase64 = $this->fetchQrCodeBase64($shop->upi_id, $shop->name, $sale->grand_total);
        }

        // Build premium styled HTML for PDF invoice
        $html = '
        <!DOCTYPE html>
        <html lang="' . $locale . '">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>' . __('invoice') . ' - ' . $sale->sale_number . '</title>
            <style>
                ' . $fontFaceStyles . '
                body, table, td, th, div, span, p, strong {
                    font-family: ' . ($locale === 'gu' ? 'NotoSansGujarati' : ($locale === 'hi' ? 'NotoSansDevanagari' : 'DejaVu Sans')) . ', sans-serif;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    text-rendering: geometricPrecision;
                }
                body {
                    color: #111;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }
                .font-default {
                    font-family: "DejaVu Sans", sans-serif;
                }
                .font-gujarati {
                    font-family: "NotoSansGujarati", sans-serif;
                }
                .font-devanagari {
                    font-family: "NotoSansDevanagari", sans-serif;
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
                    max-width: 65px;
                    max-height: 50px;
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
                    white-space: nowrap;
                }
                .status-badge {
                    display: inline-block;
                    font-size: 11px;
                    font-weight: bold;
                    border: 1px solid ' . $textColor . ';
                    color: ' . $textColor . ';
                    padding: 3px 12px;
                    border-radius: 12px;
                    text-transform: uppercase;
                    line-height: 1;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: ' . $textColor . ';
                    white-space: nowrap;
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
                    font-size: 11.5px;
                    text-transform: uppercase;
                    color: #475569;
                    font-weight: bold;
                    letter-spacing: 0.5px;
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
                    font-size: 11.5px;
                    color: #334155;
                    white-space: pre-line;
                    margin-top: 8px;
                    line-height: 1.4;
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
                    font-size: 12.5px;
                    color: #334155;
                    font-weight: 500;
                }
                .signature-img {
                    margin-top: 15px;
                    text-align: right;
                }
                .signature-img img {
                    max-height: 45px;
                    max-width: 120px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 11px;
                    color: #64748b;
                    border-top: 1px solid #cbd5e1;
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
                        if ($logoBase64) {
                            $html .= '<td style="vertical-align: top; max-width: 65px; padding: 0 10px 0 0; line-height: 0;"><img class="header-logo" style="vertical-align: top;" src="' . $logoBase64 . '" /></td>';
                        }
                        $html .= '
                                <td style="vertical-align: top; padding: 0;">
                                    <span class="shop-name">' . $this->renderMultilingualText($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        ' . __('mobile') . ': ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '<br>
                                        ' . ($shop->address ? $this->renderMultilingualText($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? __('gstin_label') . ': ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; float: right;">
                                <tr>
                                    <td style="font-size: 26px; font-weight: bold; color: ' . $textColor . '; vertical-align: middle; padding: 0; line-height: 1;">' . strtoupper(__('invoice')) . '</td>
                                    ' . ($badgeHtml ? '<td style="vertical-align: middle; padding: 0 0 0 8px; line-height: 1;">' . $badgeHtml . '</td>' : '') . '
                                </tr>
                            </table>
                            <div style="clear: both;"></div>
                            <div class="invoice-meta" style="margin-top: 5px;">
                                <strong>' . __('invoice_no') . ':</strong> ' . htmlspecialchars($sale->sale_number) . '<br>
                                <strong>' . __('date') . ':</strong> ' . $this->formatInvoiceDateTime($sale->sale_date, $dateFormat, $timeFormat) . '
                                ' . (($sale->status === 'Completed' && $sale->payment_type === 'Credit' && ($sale->paid_date ?? $sale->updated_at)) ? '<br><strong>' . __('paid_date') . ':</strong> ' . $this->formatInvoiceDateTime($sale->paid_date ?? $sale->updated_at, $dateFormat, $timeFormat) : '') . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="details-table">
                    <tr>
                        <td>
                            <div class="section-title">' . __('bill_to') . '</div>
                            <div class="party-name">' . $this->renderMultilingualText($sale->customer->name ?? __('walk_in_customer')) . '</div>
                            <div class="party-info">
                                ' . ($sale->customer && $sale->customer->mobile ? __('mobile') . ': ' . htmlspecialchars($sale->customer->mobile) . '<br>' : '') . '
                                ' . ($sale->customer && $sale->customer->email ? __('email') . ': ' . htmlspecialchars($sale->customer->email) : '') . '
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="section-title">' . __('payment_info') . '</div>
                            <div class="party-info">
                                <strong>' . __('payment_status') . ':</strong> ' . (
                                    $sale->status === 'Returned'
                                        ? '<span style="color:#ef4444;">' . __('returned') . '</span>'
                                        : ($sale->status === 'Partially Returned'
                                            ? '<span style="color:#f59e0b;">' . __('partially_returned') . '</span>'
                                            : ($sale->status === 'Unpaid'
                                                ? '<span style="color:#f59e0b;font-weight:bold;">' . __('unpaid') . '</span>'
                                                : ($sale->payment_type === 'Credit'
                                                    ? '<span style="color:#10b981;font-weight:bold;">' . __('paid') . '</span>'
                                                    : '<span style="color:#10b981;font-weight:bold;">' . __('paid') . '</span>')))
                                ) . '<br>
                                <strong>' . __('method') . ':</strong> ' . __(strtolower($sale->payment_type)) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">#</th>
                            <th>' . __('product_name') . '</th>
                            <th style="width: 80px; text-align: right;">' . __('price') . '</th>
                            <th style="width: 80px; text-align: center;">' . __('qty') . '</th>';

        $showDiscountCol = ($invoiceConfig->show_discount ?? true);
        if (!$showDiscountCol) {
            foreach ($sale->items as $item) {
                if ((float)($item->discount ?? 0) > 0) {
                    $showDiscountCol = true;
                    break;
                }
            }
        }

        if ($showDiscountCol) {
            $html .= '
                            <th style="width: 80px; text-align: right;">' . __('discount') . '</th>';
        }

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
                            <th style="width: 80px; text-align: center;">' . __('returned') . '</th>
                            <th style="width: 80px; text-align: center;">' . __('net_qty') . '</th>';
        }

        $html .= '
                            <th style="width: 100px; text-align: right;">' . __('total') . '</th>
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

                        $itemDiscount = (float)($item->discount ?? 0);
                        $lineTotal = max(0, ($item->selling_price * $netQty) - $itemDiscount);

                        $html .= '
                        <tr>
                            <td style="text-align: center;">' . $i++ . '</td>
                            <td>' . $this->renderMultilingualText($item->product->name ?? __('unknown_product')) . '</td>
                            <td style="text-align: right;">&#8377; ' . number_format($item->selling_price, 2) . '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>';

                        if ($showDiscountCol) {
                            $html .= '
                            <td style="text-align: right;">' . ($itemDiscount > 0 ? '&#8377; ' . number_format($itemDiscount, 2) : '-') . '</td>';
                        }

                        if ($isReturned) {
                            $html .= '
                            <td style="text-align: center; color: #ef4444; font-weight: bold;">' . $returnedQty . '</td>
                            <td style="text-align: center; font-weight: bold;">' . $netQty . '</td>';
                        }

                        $html .= '
                            <td style="text-align: right;">&#8377; ' . number_format($lineTotal, 2) . '</td>
                        </tr>';
                    }

                    $html .= '
                    </tbody>
                </table>

                <table class="footer-row-table">
                    <tr>
                        <td class="qr-cell">';
                        if ($qrBase64) {
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" /><br><span style="font-size:11px;color:#475569;font-weight:600;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td>
                            <table class="total-table">
                                <tr>
                                    <td class="total-label">' . __('subtotal') . ':</td>
                                    <td class="total-value">&#8377; ' . number_format($sale->subtotal, 2) . '</td>
                                </tr>
                                <tr>
                                    <td class="total-label">' . __('discount') . ':</td>
                                    <td class="total-value">-&#8377; ' . number_format($sale->discount, 2) . '</td>
                                </tr>
                                <tr>
                                    <td class="grand-total-label">' . __('grand_total') . ':</td>
                                    <td class="grand-total-value">&#8377; ' . number_format($sale->grand_total, 2) . '</td>
                                </tr>';
                                if ((float)($sale->store_credit ?? 0) > 0) {
                                    $netPayment = max(0, (float)$sale->grand_total - (float)$sale->store_credit);
                                    $html .= '
                                    <tr>
                                        <td class="total-label" style="color: #059669; font-weight: bold;">' . __('store_credit', [], 'Store Credit') . ':</td>
                                        <td class="total-value" style="color: #059669; font-weight: bold;">-&#8377; ' . number_format($sale->store_credit, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="total-label" style="font-weight: bold;">' . ($sale->payment_type === 'Credit' ? __('balance_due', [], 'Balance Due') : __('net_paid', [], 'Net Paid (' . $sale->payment_type . ')')) . ':</td>
                                        <td class="total-value" style="font-weight: bold;">&#8377; ' . number_format($netPayment, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="invoice-footer-text">' . $this->renderMultilingualText($shop->invoice_footer ?: __('invoice_footer_default')) . '</div>';

                if ($signatureBase64) {
                    $html .= '<div class="signature-img"><img src="' . $signatureBase64 . '" /></div>';
                }

                $html .= '
                <div class="footer">
                    ' . __('powered_by') . ' <span class="brand-highlight">DukanHisab</span>
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
        $dateFormat = $invoiceConfig->date_format ?: ($shop->owner->date_format ?? 'DD/MM/YYYY');
        $timeFormat = $shop->owner->time_format ?? '12h';
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $textColor = $this->contrastTextColor($themeColor);

        // Force locale from request parameters/headers to ensure correct translations in PDF generation
        $request = request();
        $locale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language')
            ?? app()->getLocale();

        // Clean and validate locale
        $locale = strtolower(trim($locale));
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }

        // Force English locale for all PDF invoices for now to bypass font cache permissions issues
        app()->setLocale('en');
        $locale = 'en';

        $fontFaceStyles = '';
        if ($locale === 'gu') {
            $fontFaceStyles = '
                @font-face {
                    font-family: "NotoSansGujarati";
                    font-style: normal;
                    font-weight: 400;
                    src: url("' . public_path('fonts/NotoSansGujarati-Regular.ttf') . '") format("truetype");
                }
                @font-face {
                    font-family: "NotoSansGujarati";
                    font-style: normal;
                    font-weight: 700;
                    src: url("' . public_path('fonts/NotoSansGujarati-Bold.ttf') . '") format("truetype");
                }';
        } elseif ($locale === 'hi') {
            $fontFaceStyles = '
                @font-face {
                    font-family: "NotoSansDevanagari";
                    font-style: normal;
                    font-weight: 400;
                    src: url("' . public_path('fonts/NotoSansDevanagari-Regular.ttf') . '") format("truetype");
                }
                @font-face {
                    font-family: "NotoSansDevanagari";
                    font-style: normal;
                    font-weight: 700;
                    src: url("' . public_path('fonts/NotoSansDevanagari-Bold.ttf') . '") format("truetype");
                }';
        }

        $badgeHtml = '';
        if ($purchase->status === 'Returned') {
            $badgeHtml = ' <span class="status-badge">' . __('returned') . '</span>';
        } elseif ($purchase->status === 'Partially Returned') {
            $badgeHtml = ' <span class="status-badge">' . __('partially_returned') . '</span>';
        }

        $logoBase64 = $this->getBase64Image($shop->logo);
        $signatureBase64 = $this->getBase64Image($shop->signature);

        $qrBase64 = null;
        if ($invoiceConfig->show_upi_qr && $shop->upi_id) {
            $qrBase64 = $this->fetchQrCodeBase64($shop->upi_id, $shop->name, $purchase->total_amount);
        }

        $isReturned = ($purchase->status === 'Returned' || $purchase->status === 'Partially Returned');
        $hasReturnedQty = false;
        foreach ($purchase->items as $item) {
            if (($item->returned_quantity ?? 0) > 0) {
                $hasReturnedQty = true;
                break;
            }
        }

        // Build premium styled HTML for PDF invoice
        $html = '
        <!DOCTYPE html>
        <html lang="' . $locale . '">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>' . __('purchase_invoice') . ' - ' . $purchase->purchase_number . '</title>
            <style>
                ' . $fontFaceStyles . '
                body, table, td, th, div, span, p, strong {
                    font-family: ' . ($locale === 'gu' ? 'NotoSansGujarati' : ($locale === 'hi' ? 'NotoSansDevanagari' : 'DejaVu Sans')) . ', sans-serif;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    text-rendering: geometricPrecision;
                }
                body {
                    color: #111;
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
                    max-width: 65px;
                    max-height: 50px;
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
                    white-space: nowrap;
                }
                .status-badge {
                    display: inline-block;
                    font-size: 11px;
                    font-weight: bold;
                    border: 1px solid ' . $textColor . ';
                    color: ' . $textColor . ';
                    padding: 3px 12px;
                    border-radius: 12px;
                    text-transform: uppercase;
                    line-height: 1;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 13px;
                    color: ' . $textColor . ';
                    white-space: nowrap;
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
                    font-size: 11.5px;
                    text-transform: uppercase;
                    color: #475569;
                    font-weight: bold;
                    letter-spacing: 0.5px;
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
                    font-size: 11.5px;
                    color: #334155;
                    white-space: pre-line;
                    margin-top: 8px;
                    line-height: 1.4;
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
                    font-size: 12.5px;
                    color: #334155;
                    font-weight: 500;
                }
                .signature-img {
                    margin-top: 15px;
                    text-align: right;
                }
                .signature-img img {
                    max-height: 45px;
                    max-width: 120px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 11px;
                    color: #64748b;
                    border-top: 1px solid #cbd5e1;
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
                        if ($logoBase64) {
                            $html .= '<td style="vertical-align: top; max-width: 65px; padding: 0 10px 0 0; line-height: 0;"><img class="header-logo" style="vertical-align: top;" src="' . $logoBase64 . '" /></td>';
                        }
                        $html .= '
                                <td style="vertical-align: top; padding: 0;">
                                    <span class="shop-name">' . $this->renderMultilingualText($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        ' . __('mobile') . ': ' . htmlspecialchars($shop->mobile ?? $shop->owner->mobile ?? '') . '<br>
                                        ' . ($shop->address ? $this->renderMultilingualText($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? __('gstin_label') . ': ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; float: right;">
                                <tr>
                                    <td style="font-size: 26px; font-weight: bold; color: ' . $textColor . '; vertical-align: middle; padding: 0; line-height: 1;">' . strtoupper(__('purchase_invoice')) . '</td>
                                    ' . ($badgeHtml ? '<td style="vertical-align: middle; padding: 0 0 0 8px; line-height: 1;">' . $badgeHtml . '</td>' : '') . '
                                </tr>
                            </table>
                            <div style="clear: both;"></div>
                            <div class="invoice-meta" style="margin-top: 5px;">
                                <strong>' . __('invoice_no') . ':</strong> ' . htmlspecialchars($purchase->purchase_number) . '<br>
                                <strong>' . __('date') . ':</strong> ' . $this->formatInvoiceDateTime($purchase->purchase_date, $dateFormat, $timeFormat) . '
                                ' . (($purchase->status === 'Completed' && $purchase->payment_type === 'Credit' && ($purchase->paid_date ?? $purchase->updated_at)) ? '<br><strong>' . __('paid_date') . ':</strong> ' . $this->formatInvoiceDateTime($purchase->paid_date ?? $purchase->updated_at, $dateFormat, $timeFormat) : '') . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="details-table">
                    <tr>
                        <td>
                            <div class="section-title">' . __('supplier_name') . '</div>
                            <div class="party-name">' . $this->renderMultilingualText($purchase->supplier->name ?? __('walk_in_supplier')) . '</div>
                            <div class="party-info">
                                ' . ($purchase->supplier && $purchase->supplier->mobile ? __('mobile') . ': ' . htmlspecialchars($purchase->supplier->mobile) . '<br>' : '') . '
                                ' . ($purchase->supplier && $purchase->supplier->email ? __('email') . ': ' . htmlspecialchars($purchase->supplier->email) : '') . '
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="section-title">' . __('payment_info') . '</div>
                            <div class="party-info">
                                <strong>' . __('payment_status') . ':</strong> ' . (
                                    $purchase->status === 'Returned'
                                        ? '<span class="badge badge-returned">' . strtoupper(__('returned')) . '</span>'
                                        : ($purchase->status === 'Partially Returned'
                                            ? '<span class="badge badge-warning">' . strtoupper(__('partially_returned')) . '</span>'
                                            : ($purchase->status === 'Unpaid'
                                                ? '<span class="badge badge-unpaid">' . strtoupper(__('unpaid')) . '</span>'
                                                : ($purchase->status === 'Partially Paid'
                                                    ? '<span class="badge badge-warning">' . strtoupper(__('partially_paid', [], 'Partially Paid')) . '</span>'
                                                    : ($purchase->payment_type === 'Credit'
                                                        ? '<span class="badge badge-paid">' . strtoupper(__('paid')) . '</span>'
                                                        : '<span class="badge badge-paid">' . strtoupper(__('completed')) . '</span>'
                                                    )
                                                )
                                            )
                                        )
                                ) . '
                            </div>
                            <div class="meta-row">
                                <strong>' . __('method') . ':</strong> ' . __(strtolower($purchase->payment_type)) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: ' . ($isReturned ? '40%' : '55%') . ';">' . __('item') . '</th>
                            <th style="width: ' . ($isReturned ? '12%' : '15%') . '; text-align: center;">' . ($isReturned ? __('purchased_qty') : __('quantity')) . '</th>
                            ' . ($isReturned ? '<th style="width: 12%; text-align: center;">' . __('returned_qty') . '</th><th style="width: 12%; text-align: center;">' . __('net_qty') . '</th>' : '') . '
                            <th style="width: 15%; text-align: right;">' . __('price') . '</th>
                            <th style="width: 15%; text-align: right;">' . __('total') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                    $rowNum = 1;
                    foreach ($purchase->items as $item) {
                        $retQty = (int)($item->returned_quantity ?? 0);
                        if (!$hasReturnedQty && $purchase->status === 'Partially Returned') {
                            $retQty = 0;
                        } elseif (!$hasReturnedQty && $purchase->status === 'Returned') {
                            $retQty = (int)$item->quantity;
                        }
                        $netQty = max(0, (int)$item->quantity - $retQty);
                        $lineDiscount = (float)($item->discount ?? 0);
                        $lineTotal = max(0, ($item->purchase_price * $netQty) - $lineDiscount);

                        $html .= '
                        <tr>
                            <td>' . $rowNum++ . '</td>
                            <td>
                                <strong>' . $this->renderMultilingualText($item->product->name ?? __('deleted_product')) . '</strong>' .
                                ($lineDiscount > 0 ? '<span style="font-size:11px;font-weight:600;color:#059669;display:block;margin-top:2px;">' . __('scheme_discount', [], 'Scheme Disc') . ': -&#8377; ' . number_format($lineDiscount, 2) . '</span>' : '') .
                            '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>
                            ' . ($isReturned ? '<td style="text-align: center; color: #dc2626;">' . ($retQty > 0 ? '-' . $retQty : '0') . '</td><td style="text-align: center; font-weight: bold;">' . $netQty . '</td>' : '') . '
                            <td style="text-align: right;">&#8377; ' . number_format($item->purchase_price, 2) . '</td>
                            <td style="text-align: right;">&#8377; ' . number_format($lineTotal, 2) . '</td>
                        </tr>';
                    }

                    $totalDiscount = (float)($purchase->discount ?? 0);
                    $paidAmount = (float)($purchase->paid_amount ?? 0);
                    $dueAmount = max(0, (float)$purchase->total_amount - $paidAmount);
                    $grossSubtotal = (float)$purchase->total_amount + $totalDiscount;

                    $html .= '
                    </tbody>
                </table>

                <table class="footer-row-table">
                    <tr>
                        <td class="qr-cell">';
                        if ($qrBase64) {
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" /><br><span style="font-size:11px;color:#475569;font-weight:600;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td>
                            <table class="total-table">';
                                if ($totalDiscount > 0) {
                                    $html .= '
                                    <tr>
                                        <td class="grand-total-label" style="font-size: 13px; font-weight: normal; color: #6b7280;">' . __('subtotal') . ':</td>
                                        <td style="text-align: right; font-size: 13px; color: #374151;">&#8377; ' . number_format($grossSubtotal, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="grand-total-label" style="font-size: 13px; font-weight: normal; color: #059669;">' . __('discount') . ':</td>
                                        <td style="text-align: right; font-size: 13px; color: #059669;">-&#8377; ' . number_format($totalDiscount, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                                <tr>
                                    <td class="grand-total-label">' . __('total_amount') . ':</td>
                                    <td class="grand-total-value">&#8377; ' . number_format($purchase->total_amount, 2) . '</td>
                                </tr>';
                                if ($paidAmount > 0 || $dueAmount > 0) {
                                    $html .= '
                                    <tr>
                                        <td class="grand-total-label" style="font-size: 13px; font-weight: normal; color: #16a34a;">' . __('paid_amount', [], 'Paid Amount') . ':</td>
                                        <td style="text-align: right; font-size: 13px; font-weight: bold; color: #16a34a;">&#8377; ' . number_format($paidAmount, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="grand-total-label" style="font-size: 13px; font-weight: normal; color: #dc2626;">' . __('balance_due', [], 'Balance Due') . ':</td>
                                        <td style="text-align: right; font-size: 13px; font-weight: bold; color: #dc2626;">&#8377; ' . number_format($dueAmount, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                            </table>
                        </td>
                    </tr>
                </table>

                <div class="invoice-footer-text">' . $this->renderMultilingualText($shop->invoice_footer ?: __('invoice_footer_default')) . '</div>';

                if ($signatureBase64) {
                    $html .= '<div class="signature-img"><img src="' . $signatureBase64 . '" /></div>';
                }

                $html .= '
                <div class="footer">
                    ' . __('powered_by') . ' <span class="brand-highlight">DukanHisab</span>
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

    private function renderMultilingualText(?string $text): string
    {
        $text = (string) ($text ?? '');
        if ($text === '') {
            return '';
        }
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Gujarati Unicode block: U+0A80 - U+0AFF
        if (preg_match('/[\x{0A80}-\x{0AFF}]/u', $text)) {
            return '<span class="font-gujarati">' . $escaped . '</span>';
        }

        // Devanagari Unicode block: U+0900 - U+097F
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return '<span class="font-devanagari">' . $escaped . '</span>';
        }

        return '<span class="font-default">' . $escaped . '</span>';
    }

    private function formatInvoiceDateTime($dateTime, ?string $dateFormat = 'DD/MM/YYYY', ?string $timeFormat = '12h'): string
    {
        if (!$dateTime) {
            return '-';
        }
        $carbon = is_string($dateTime) ? \Carbon\Carbon::parse($dateTime) : $dateTime->copy();
        $carbon = $carbon->timezone('Asia/Kolkata');

        $phpDateFmt = match ($dateFormat) {
            'MM/DD/YYYY' => 'm/d/Y',
            'YYYY-MM-DD' => 'Y-m-d',
            default => 'd/m/Y',
        };

        $phpTimeFmt = ($timeFormat === '24h') ? 'H:i' : 'h:i A';

        return $carbon->format($phpDateFmt . ' ' . $phpTimeFmt);
    }

    private function resolveImagePath(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        // Clean any leading slashes or redundant storage/ prefixes
        $clean = ltrim($relativePath, '/\\');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }
        if (str_starts_with($clean, 'public/')) {
            $clean = substr($clean, 7);
        }

        $candidates = [
            storage_path('app/public/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
            storage_path('app/' . $clean),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $mime = @mime_content_type($candidate);
                if ($mime && str_starts_with($mime, 'image/')) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function getBase64Image(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $localPath = $this->resolveImagePath($relativePath);

        // 1. If found on local disk and validated as a real image
        if ($localPath && file_exists($localPath) && is_file($localPath)) {
            $mime = @mime_content_type($localPath) ?: 'image/png';
            $contents = @file_get_contents($localPath);
            if ($contents) {
                return 'data:' . $mime . ';base64,' . base64_encode($contents);
            }
        }

        // 2. If it's a full remote URL
        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            try {
                $contents = @file_get_contents($relativePath);
                if ($contents) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($contents);
                    if ($mime && str_starts_with($mime, 'image/')) {
                        return 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 3. Fallback: try fetching from live server if missing locally
        try {
            $clean = ltrim($relativePath, '/\\');
            if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, 8);
            }
            $fallbackUrls = [
                'https://themejagat.com/dukanhisab/storage/' . $clean,
                'https://themejagat.com/dukanhisab/public/storage/' . $clean,
            ];
            foreach ($fallbackUrls as $fallbackUrl) {
                $contents = @file_get_contents($fallbackUrl);
                if ($contents) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($contents);
                    if ($mime && str_starts_with($mime, 'image/')) {
                        // Cache locally so subsequent generations are instant
                        $localTarget = storage_path('app/public/' . $clean);
                        @mkdir(dirname($localTarget), 0777, true);
                        @file_put_contents($localTarget, $contents);
                        return 'data:' . $mime . ';base64,' . base64_encode($contents);
                    }
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }
}