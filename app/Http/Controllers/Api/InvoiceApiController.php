<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Shop;
use App\Models\InvoiceConfig;
use App\Models\InvoiceSetting;
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

    /**
     * Login-free PDF for WhatsApp document headers: Meta fetches the file from this URL. Access is
     * granted only by the temporary signature (the `signed` middleware), never by the id alone.
     */
    public function signedSalePDF($id)
    {
        $sale = Sale::with('items.product', 'customer')->findOrFail($id);
        return $this->renderPdf($this->buildSaleInvoiceHtml($sale), 'Invoice-' . $sale->sale_number . '.pdf', true);
    }

    public function signedPurchasePDF($id)
    {
        $purchase = Purchase::with('items.product', 'supplier')->findOrFail($id);
        return $this->renderPdf($this->buildPurchaseInvoiceHtml($purchase), 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf', true);
    }

    private function renderPdf(string $html, string $filename, bool $stream = true)
    {
        // DomPDF cannot shape Indic scripts (conjuncts/matras come out broken),
        // so any invoice containing Gujarati/Devanagari text goes through mPDF,
        // which applies OpenType layout. Everything else keeps using DomPDF.
        $output = $this->containsIndicScript($html)
            ? $this->renderWithMpdf($html)
            : $this->renderWithDompdf($html);

        if ($stream) {
            return response($output)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        }

        return $output;
    }

    private function containsIndicScript(string $html): bool
    {
        // Devanagari: U+0900-097F, Gujarati: U+0A80-0AFF
        return preg_match('/[\x{0900}-\x{097F}\x{0A80}-\x{0AFF}]/u', $html) === 1;
    }

    private function renderWithDompdf(string $html): string
    {
        $hasWatermark = str_contains($html, 'class="dh-watermark"');
        if ($hasWatermark) {
            $html = preg_replace('/<div class="dh-watermark">.*?<\/div>/s', '', $html);
        }

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->getOptions()->set('isFontSubsettingEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('defaultFont', 'DejaVu Sans');

        if ($hasWatermark) {
            $pdf->render();
            $canvas = $pdf->getDomPDF()->getCanvas();
            $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
            $font = $fontMetrics->getFont('Helvetica', 'bold');
            $canvas->set_opacity(0.06);
            $canvas->text(100, 520, 'DukanHisab', $font, 65, [0.58, 0.64, 0.72], 0, 0, -45);
            return $pdf->output();
        }

        return $pdf->output();
    }

    private function renderWithMpdf(string $html): string
    {
        $hasWatermark = str_contains($html, 'class="dh-watermark"');
        if ($hasWatermark) {
            $html = preg_replace('/<div class="dh-watermark">.*?<\/div>/s', '', $html);
        }

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => $tempDir,
            'fontDir' => array_merge($defaultConfig['fontDir'], [public_path('fonts')]),
            // Noto Sans Gujarati/Devanagari use GSUB/GPOS lookups mPDF cannot parse,
            // so use Hind Vadodara / Hind (OFL) which mPDF shapes correctly.
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'muktavaani' => [
                    'R' => 'MuktaVaani-Regular.ttf',
                    'B' => 'MuktaVaani-Bold.ttf',
                    'useOTL' => 0xFF,
                ],
                'hind' => [
                    'R' => 'Hind-Regular.ttf',
                    'B' => 'Hind-Bold.ttf',
                    'useOTL' => 0xFF,
                ],
            ],
            'default_font' => 'muktavaani',
        ]);

        if ($hasWatermark) {
            $mpdf->SetWatermarkText('DukanHisab', 0.07);
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.07;
        }

        // Point every font family the invoice HTML asks for at a face that has
        // Gujarati/Latin/₹ glyphs, so no run falls back to a font without them.
        $family = preg_match('/[\x{0A80}-\x{0AFF}]/u', $html) ? 'muktavaani' : 'hind';
        $html = preg_replace(
            '/"?(DejaVu Sans|NotoSansGujarati|NotoSansDevanagari)"?/',
            $family,
            $html
        );

        // mPDF ignores white-space:nowrap, so give the invoice title/meta column
        // enough width to keep "Invoice No" and the date on one line each.
        $html = str_replace(
            '<td class="invoice-title" style="vertical-align: middle;">',
            '<td class="invoice-title" style="vertical-align: middle; width: 38%;">',
            $html
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function buildSaleInvoiceHtml(Sale $sale): string
    {
        $shop = Shop::findOrFail($sale->shop_id);
        $invoiceConfig = InvoiceConfig::firstOrCreate(['shop_id' => $sale->shop_id]);
        $dateFormat = $invoiceConfig->date_format ?: ($shop->owner->date_format ?? 'DD/MM/YYYY');
        $timeFormat = $shop->owner->time_format ?? '12h';
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $headerTextColor = $invoiceConfig->text_color ?: '#FFFFFF';

        // Locale from request parameters/headers or shop owner to ensure correct translations in PDF generation
        $request = request();
        $rawLocale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language')
            ?? $shop->owner?->language
            ?? app()->getLocale()
            ?? 'en';

        // Clean and validate locale
        $locale = strtolower(trim((string) $rawLocale));
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }
        if (!in_array($locale, ['en', 'gu', 'hi'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

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

        $logoBase64 = $this->getSquareLogoBase64($shop->logo ?: InvoiceSetting::get('default_logo'));
        $signatureBase64 = $this->getBase64Image($shop->signature);
        $watermarkHtml = InvoiceSetting::get('watermark') === 'yes'
            ? '<div class="dh-watermark">DukanHisab</div>'
            : '';

        $qrBase64 = null;
        if ($invoiceConfig->show_upi_qr && $shop->upi_id) {
            $qrBase64 = $this->fetchQrCodeBase64($shop->upi_id, $shop->name, $sale->grand_total);
        }

        // Build unified styled HTML for PDF invoice matching on-screen modal design
        $html = '
        <!DOCTYPE html>
        <html lang="' . $locale . '">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>' . __('invoice') . ' - ' . $sale->sale_number . '</title>
            <style>
                ' . $fontFaceStyles . '
                * {
                    box-sizing: border-box;
                }
                body, table, td, th, div, span, p, strong {
                    font-family: ' . ($locale === 'gu' ? 'NotoSansGujarati' : ($locale === 'hi' ? 'NotoSansDevanagari' : 'DejaVu Sans')) . ', sans-serif;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    text-rendering: geometricPrecision;
                }
                body {
                    color: #1e293b;
                    font-size: 12px;
                    line-height: 1.5;
                    margin: 0;
                    padding: 0;
                    background-color: #ffffff;
                }
                .container {
                    padding: 24px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    background-color: ' . $themeColor . ';
                    color: ' . $headerTextColor . ';
                    border-radius: 8px;
                    overflow: hidden;
                    margin-bottom: 16px;
                }
                .header-table td {
                    padding: 14px 16px;
                }
                .shop-logo-box {
                    width: 42px;
                    height: 42px;
                    border-radius: 6px;
                    background-color: rgba(255, 255, 255, 0.2);
                    text-align: center;
                    line-height: 42px;
                    overflow: hidden;
                }
                .shop-logo-box img {
                    max-width: 42px;
                    max-height: 42px;
                    vertical-align: middle;
                }
                .shop-name {
                    font-size: 15px;
                    font-weight: bold;
                    line-height: 1.2;
                    color: ' . $headerTextColor . ';
                }
                .shop-details {
                    font-size: 10px;
                    line-height: 1.4;
                    opacity: 0.9;
                    margin-top: 2px;
                    color: ' . $headerTextColor . ';
                }
                .invoice-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: ' . $headerTextColor . ';
                    text-align: right;
                    white-space: nowrap;
                    line-height: 1.2;
                }
                .status-badge {
                    display: inline-block;
                    font-size: 9.5px;
                    font-weight: bold;
                    border: 1px solid rgba(255, 255, 255, 0.4);
                    color: ' . $headerTextColor . ';
                    padding: 2px 8px;
                    border-radius: 12px;
                    text-transform: uppercase;
                    vertical-align: middle;
                    margin-left: 6px;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 10px;
                    opacity: 0.9;
                    line-height: 1.4;
                    margin-top: 3px;
                    color: ' . $headerTextColor . ';
                    white-space: nowrap;
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 16px;
                }
                .details-table td {
                    width: 50%;
                    vertical-align: top;
                    padding: 0;
                }
                .section-title {
                    font-size: 10px;
                    text-transform: uppercase;
                    color: #94a3b8;
                    font-weight: bold;
                    letter-spacing: 0.5px;
                    margin-bottom: 2px;
                }
                .party-name {
                    font-size: 13.5px;
                    font-weight: bold;
                    color: #1e293b;
                }
                .party-info {
                    font-size: 11px;
                    color: #64748b;
                    line-height: 1.4;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 16px;
                }
                .items-table th {
                    background-color: #f8fafc;
                    border-top: 1px solid #e2e8f0;
                    border-bottom: 1px solid #e2e8f0;
                    text-align: left;
                    padding: 8px 12px;
                    font-size: 11px;
                    font-weight: bold;
                    color: #64748b;
                    text-transform: uppercase;
                }
                .items-table td {
                    padding: 9px 12px;
                    border-bottom: 1px solid #f1f5f9;
                    font-size: 12px;
                    color: #1e293b;
                }
                .footer-row-table {
                    width: 100%;
                    border-collapse: collapse;
                    border-top: 1px solid #e2e8f0;
                    margin-top: 8px;
                    padding-top: 12px;
                }
                .footer-row-table td {
                    vertical-align: top;
                    padding: 0;
                }
                .qr-cell {
                    width: 48%;
                    text-align: left;
                }
                .qr-cell img {
                    width: 75px;
                    height: 75px;
                    padding: 3px;
                    border: 1px solid #e2e8f0;
                    border-radius: 4px;
                    background: #fff;
                }
                .bank-details {
                    font-size: 9.5px;
                    color: #64748b;
                    white-space: pre-line;
                    margin-top: 6px;
                    line-height: 1.35;
                    border-top: 1px dashed #e2e8f0;
                    padding-top: 4px;
                    max-width: 260px;
                }
                .total-table {
                    width: 210px;
                    margin-left: auto;
                    border-collapse: collapse;
                }
                .total-label {
                    text-align: right;
                    padding: 3px 8px;
                    font-size: 11.5px;
                    color: #64748b;
                }
                .total-value {
                    text-align: right;
                    width: 85px;
                    padding: 3px 0 3px 8px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #1e293b;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 6px 8px 4px 8px;
                    font-size: 13px;
                    font-weight: bold;
                    color: #1e293b;
                    border-top: 1px dashed #cbd5e1;
                }
                .grand-total-value {
                    text-align: right;
                    width: 85px;
                    padding: 6px 0 4px 8px;
                    font-size: 14px;
                    font-weight: bold;
                    color: ' . $themeColor . ';
                    border-top: 1px dashed #cbd5e1;
                }
                .invoice-footer-text {
                    margin-top: 16px;
                    text-align: center;
                    font-size: 10.5px;
                    color: #94a3b8;
                    font-weight: normal;
                }
                .signature-img {
                    margin-top: 10px;
                    text-align: right;
                }
                .signature-img img {
                    max-height: 40px;
                    max-width: 120px;
                }
                .footer {
                    margin-top: 16px;
                    text-align: center;
                    font-size: 10px;
                    color: #94a3b8;
                    border-top: 1px solid #f1f5f9;
                    padding-top: 10px;
                }
                .brand-highlight {
                    color: ' . $themeColor . ';
                    font-weight: bold;
                }
                .dh-watermark {
                    position: fixed;
                    top: 40%;
                    left: 0;
                    width: 100%;
                    text-align: center;
                    transform: rotate(-35deg);
                    font-size: 70px;
                    font-weight: bold;
                    color: #94a3b8;
                    opacity: 0.15;
                    letter-spacing: 4px;
                }
            </style>
        </head>
        <body>
            ' . $watermarkHtml . '
            <div class="container">
                <table class="header-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align: middle;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;"><tr>';
                        if ($logoBase64) {
                            $html .= '<td style="vertical-align: middle; width: 44px; padding: 0 10px 0 0; line-height: 0;"><div class="shop-logo-box"><img class="header-logo" src="' . $logoBase64 . '" width="42" height="42" style="width: 42px; height: 42px; object-fit: cover;" /></div></td>';
                        }
                        $html .= '
                                <td style="vertical-align: middle; padding: 0;">
                                    <span class="shop-name">' . $this->renderMultilingualText($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        ' . ($shop->mobile ? __('mobile') . ': ' . htmlspecialchars($shop->mobile) . '<br>' : '') . '
                                        ' . ($shop->address ? $this->renderMultilingualText($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? __('gstin_label') . ': ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle;">
                            <div>' . strtoupper(__('invoice')) . ($badgeHtml ? ' ' . $badgeHtml : '') . '</div>
                            <div class="invoice-meta">
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
                                ' . (($invoiceConfig->show_customer_address ?? true) && $sale->customer && $sale->customer->mobile ? __('mobile') . ': ' . htmlspecialchars($sale->customer->mobile) : '') . '
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="section-title">' . __('payment_info') . '</div>
                            <div class="party-info">
                                <strong>' . __('payment_status') . ':</strong> ' . (
                                    $sale->status === 'Returned'
                                        ? '<span style="color:#e11d48;font-weight:bold;">' . __('returned') . '</span>'
                                        : ($sale->status === 'Partially Returned'
                                            ? '<span style="color:#d97706;font-weight:bold;">' . __('partially_returned') . '</span>'
                                            : ($sale->status === 'Unpaid'
                                                ? '<span style="color:#d97706;font-weight:bold;">' . __('unpaid') . '</span>'
                                                : ($sale->status === 'Partially Paid'
                                                    ? '<span style="color:#2563eb;font-weight:bold;">' . __('partially_paid') . '</span>'
                                                    : '<span style="color:#16a34a;font-weight:bold;">' . __('paid') . '</span>')))
                                ) . '<br>
                                <strong>' . (__('payment_type') ?: __('method')) . ':</strong> ' . __(strtolower(str_replace(' ', '_', $sale->payment_type ?? 'cash'))) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>' . __('product') . '</th>
                            <th style="width: 85px; text-align: right;">' . __('price') . '</th>
                            <th style="width: 80px; text-align: center;">' . __('quantity') . '</th>';

        $showDiscountCol = false;
        foreach ($sale->items as $item) {
            if ((float)($item->discount ?? 0) > 0) {
                $showDiscountCol = true;
                break;
            }
        }

        if ($showDiscountCol) {
            $html .= '
                            <th style="width: 80px; text-align: right; color: #d97706;">' . __('discount') . '</th>';
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
                            <th style="width: 70px; text-align: center;">' . __('returned') . '</th>
                            <th style="width: 70px; text-align: center;">' . __('net_qty') . '</th>';
        }

        $html .= '
                            <th style="width: 95px; text-align: right;">' . __('total') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

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
                            <td>' . $this->renderMultilingualText($item->product->name ?? __('unknown_product')) . '</td>
                            <td style="text-align: right;">&#8377;' . number_format($item->selling_price, 2) . '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>';

                        if ($showDiscountCol) {
                            $html .= '
                            <td style="text-align: right; color: #d97706; font-weight: bold;">' . ($itemDiscount > 0 ? '-&#8377;' . number_format($itemDiscount, 2) : '-') . '</td>';
                        }

                        if ($isReturned) {
                            $html .= '
                            <td style="text-align: center; color: #ef4444; font-weight: bold;">' . $returnedQty . '</td>
                            <td style="text-align: center; font-weight: bold;">' . $netQty . '</td>';
                        }

                        $html .= '
                            <td style="text-align: right; font-weight: bold;">&#8377;' . number_format($lineTotal, 2) . '</td>
                        </tr>';
                    }

                    $html .= '
                    </tbody>
                </table>

                <table class="footer-row-table">
                    <tr>
                        <td class="qr-cell">';
                        if ($qrBase64) {
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" width="75" height="75" style="width: 75px; height: 75px; padding: 2px; border: 1px solid #e2e8f0; border-radius: 4px; background: #fff;" /><br><span style="font-size:9px;color:#64748b;font-weight:bold;display:inline-block;margin-top:3px;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td style="vertical-align: top; text-align: right;">
                            <table class="total-table" style="width: 220px; margin-left: auto; border-collapse: collapse;">
                                <tr>
                                    <td class="total-label">' . __('subtotal') . ':</td>
                                    <td class="total-value">&#8377;' . number_format($sale->subtotal, 2) . '</td>
                                </tr>
                                <tr>
                                    <td class="total-label">' . __('discount') . ':</td>
                                    <td class="total-value">-&#8377;' . number_format($sale->discount, 2) . '</td>
                                </tr>
                                <tr>
                                    <td class="grand-total-label">' . __('grand_total') . ':</td>
                                    <td class="grand-total-value">&#8377;' . number_format($sale->grand_total, 2) . '</td>
                                </tr>';
                                if ((float)($sale->store_credit ?? 0) > 0) {
                                    $netPayment = max(0, (float)$sale->grand_total - (float)$sale->store_credit);
                                    $html .= '
                                    <tr>
                                        <td class="total-label" style="color: #059669; font-weight: bold;">' . __('store_credit') . ':</td>
                                        <td class="total-value" style="color: #059669; font-weight: bold;">-&#8377;' . number_format($sale->store_credit, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="total-label" style="font-weight: bold;">' . ($sale->payment_type === 'Credit' ? __('balance_due') : __('net_paid') . ' (' . ucfirst($sale->payment_type ?? 'Cash') . ')') . ':</td>
                                        <td class="total-value" style="font-weight: bold;">&#8377;' . number_format($netPayment, 2) . '</td>
                                    </tr>';
                                } elseif ($sale->status === 'Partially Paid' || $sale->status === 'Unpaid' || ((float)($sale->paid_amount ?? 0) < (float)$sale->grand_total && $sale->payment_type === 'Credit')) {
                                    $paidAmt = (float)($sale->paid_amount ?? 0);
                                    $dueAmt = max(0, (float)$sale->grand_total - $paidAmt);
                                    $html .= '
                                    <tr>
                                        <td class="total-label" style="color: #16a34a; font-weight: bold; padding-top: 6px;">' . __('paid_amount') . ':</td>
                                        <td class="total-value" style="color: #16a34a; font-weight: bold; padding-top: 6px;">&#8377;' . number_format($paidAmt, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="total-label" style="color: #dc2626; font-weight: bold;">' . __('balance_due') . ':</td>
                                        <td class="total-value" style="color: #dc2626; font-weight: bold;">&#8377;' . number_format($dueAmt, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                            </table>
                        </td>
                    </tr>
                </table>

                ' . $this->buildContainerDepositHtml($sale, $shop);

                $adminFooter = InvoiceSetting::get('footer_text');
                if ($adminFooter === 'Thank you for choosing DukanHisab. For assistance, contact support@dukanhisab.com.') {
                    $adminFooter = null;
                }
                $footerMessage = $shop->invoice_footer ?: ($adminFooter ?: __('invoice_footer_default'));

                $html .= '
                <div class="invoice-footer-text">' . $this->renderMultilingualText($footerMessage) . '</div>';

                if ($signatureBase64) {
                    $html .= '<div class="signature-img"><img src="' . $signatureBase64 . '" /></div>';
                }

                $html .= '
            </div>
        </body>
        </html>
        ';

        return $html;
    }

    /**
     * Returnable containers still with the customer from this sale, with the refundable deposit held.
     * Shown outside the bill totals (a deposit is not part of the sale); shrinks on partial returns
     * and disappears once every container from this sale is back.
     */
    private function buildContainerDepositHtml(Sale $sale, Shop $shop): string
    {
        if (!$shop->hasFeature('containers')) {
            return '';
        }

        $lots = $sale->containerLots()->with('containerType')->get()->filter(fn ($lot) => $lot->pending_quantity > 0);
        if ($lots->isEmpty()) {
            return '';
        }

        $rows = '';
        $totalQty = 0;
        $totalDeposit = 0.0;
        $grouped = $lots->groupBy(fn ($lot) => $lot->container_type_id . '|' . number_format($lot->deposit_per_unit, 2, '.', ''));
        foreach ($grouped as $group) {
            $first = $group->first();
            $qty = $group->sum('pending_quantity');
            $deposit = round($qty * (float) $first->deposit_per_unit, 2);
            $totalQty += $qty;
            $totalDeposit += $deposit;
            $rows .= '
                            <tr>
                                <td>' . htmlspecialchars($first->containerType->name ?? 'Container') . '</td>
                                <td style="text-align: center;">' . $qty . '</td>
                                <td style="text-align: right;">' . ((float) $first->deposit_per_unit > 0 ? '&#8377; ' . number_format($first->deposit_per_unit, 2) : __('deposit_not_collected')) . '</td>
                                <td style="text-align: right;">&#8377; ' . number_format($deposit, 2) . '</td>
                            </tr>';
        }

        return '
                <div style="margin-top: 14px; border: 1px dashed #94a3b8; border-radius: 6px; padding: 8px 10px;">
                    <div style="font-size: 12px; font-weight: bold; color: #334155; margin-bottom: 6px;">' . __('containers_with_customer') . '</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="color: #64748b;">
                                <th style="text-align: left; padding: 2px 0;">' . __('container') . '</th>
                                <th style="text-align: center; padding: 2px 0;">' . __('pending_qty') . '</th>
                                <th style="text-align: right; padding: 2px 0;">' . __('deposit_per_unit') . '</th>
                                <th style="text-align: right; padding: 2px 0;">' . __('deposit_held') . '</th>
                            </tr>
                        </thead>
                        <tbody>' . $rows . '
                            <tr style="font-weight: bold;">
                                <td style="border-top: 1px solid #cbd5e1;">' . __('total') . '</td>
                                <td style="text-align: center; border-top: 1px solid #cbd5e1;">' . $totalQty . '</td>
                                <td style="border-top: 1px solid #cbd5e1;"></td>
                                <td style="text-align: right; border-top: 1px solid #cbd5e1;">&#8377; ' . number_format($totalDeposit, 2) . '</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">' . __('container_deposit_invoice_note') . '</div>
                </div>';
    }

    private function buildPurchaseInvoiceHtml(Purchase $purchase): string
    {
        $shop = Shop::findOrFail($purchase->shop_id);
        $invoiceConfig = InvoiceConfig::firstOrCreate(['shop_id' => $purchase->shop_id]);
        $dateFormat = $invoiceConfig->date_format ?: ($shop->owner->date_format ?? 'DD/MM/YYYY');
        $timeFormat = $shop->owner->time_format ?? '12h';
        $themeColor = $invoiceConfig->theme_color ?: '#0F766E';
        $headerTextColor = $invoiceConfig->text_color ?: '#FFFFFF';

        // Locale from request parameters/headers or shop owner to ensure correct translations in PDF generation
        $request = request();
        $rawLocale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language')
            ?? $shop->owner?->language
            ?? app()->getLocale()
            ?? 'en';

        // Clean and validate locale
        $locale = strtolower(trim((string) $rawLocale));
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }
        if (!in_array($locale, ['en', 'gu', 'hi'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

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

        $logoBase64 = $this->getSquareLogoBase64($shop->logo ?: InvoiceSetting::get('default_logo'));
        $signatureBase64 = $this->getBase64Image($shop->signature);
        $watermarkHtml = InvoiceSetting::get('watermark') === 'yes'
            ? '<div class="dh-watermark">DukanHisab</div>'
            : '';

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

        // Build unified styled HTML for PDF invoice matching on-screen modal design
        $html = '
        <!DOCTYPE html>
        <html lang="' . $locale . '">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>' . __('purchase_invoice') . ' - ' . $purchase->purchase_number . '</title>
            <style>
                ' . $fontFaceStyles . '
                * {
                    box-sizing: border-box;
                }
                body, table, td, th, div, span, p, strong {
                    font-family: ' . ($locale === 'gu' ? 'NotoSansGujarati' : ($locale === 'hi' ? 'NotoSansDevanagari' : 'DejaVu Sans')) . ', sans-serif;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    text-rendering: geometricPrecision;
                }
                body {
                    color: #1e293b;
                    font-size: 12px;
                    line-height: 1.5;
                    margin: 0;
                    padding: 0;
                    background-color: #ffffff;
                }
                .container {
                    padding: 24px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    background-color: ' . $themeColor . ';
                    color: ' . $headerTextColor . ';
                    border-radius: 8px;
                    overflow: hidden;
                    margin-bottom: 16px;
                }
                .header-table td {
                    padding: 14px 16px;
                }
                .shop-logo-box {
                    width: 42px;
                    height: 42px;
                    border-radius: 6px;
                    background-color: rgba(255, 255, 255, 0.2);
                    text-align: center;
                    line-height: 42px;
                    overflow: hidden;
                }
                .shop-logo-box img {
                    max-width: 42px;
                    max-height: 42px;
                    vertical-align: middle;
                }
                .shop-name {
                    font-size: 15px;
                    font-weight: bold;
                    line-height: 1.2;
                    color: ' . $headerTextColor . ';
                }
                .shop-details {
                    font-size: 10px;
                    line-height: 1.4;
                    opacity: 0.9;
                    margin-top: 2px;
                    color: ' . $headerTextColor . ';
                }
                .invoice-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: ' . $headerTextColor . ';
                    text-align: right;
                    white-space: nowrap;
                    line-height: 1.2;
                }
                .status-badge {
                    display: inline-block;
                    font-size: 9.5px;
                    font-weight: bold;
                    border: 1px solid rgba(255, 255, 255, 0.4);
                    color: ' . $headerTextColor . ';
                    padding: 2px 8px;
                    border-radius: 12px;
                    text-transform: uppercase;
                    vertical-align: middle;
                    margin-left: 6px;
                }
                .invoice-meta {
                    text-align: right;
                    font-size: 10px;
                    opacity: 0.9;
                    line-height: 1.4;
                    margin-top: 3px;
                    color: ' . $headerTextColor . ';
                    white-space: nowrap;
                }
                .details-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 16px;
                }
                .details-table td {
                    width: 50%;
                    vertical-align: top;
                    padding: 0;
                }
                .section-title {
                    font-size: 10px;
                    text-transform: uppercase;
                    color: #94a3b8;
                    font-weight: bold;
                    letter-spacing: 0.5px;
                    margin-bottom: 2px;
                }
                .party-name {
                    font-size: 13.5px;
                    font-weight: bold;
                    color: #1e293b;
                }
                .party-info {
                    font-size: 11px;
                    color: #64748b;
                    line-height: 1.4;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 16px;
                }
                .items-table th {
                    background-color: #f8fafc;
                    border-top: 1px solid #e2e8f0;
                    border-bottom: 1px solid #e2e8f0;
                    text-align: left;
                    padding: 8px 12px;
                    font-size: 11px;
                    font-weight: bold;
                    color: #64748b;
                    text-transform: uppercase;
                }
                .items-table td {
                    padding: 9px 12px;
                    border-bottom: 1px solid #f1f5f9;
                    font-size: 12px;
                    color: #1e293b;
                }
                .footer-row-table {
                    width: 100%;
                    border-collapse: collapse;
                    border-top: 1px solid #e2e8f0;
                    margin-top: 8px;
                    padding-top: 12px;
                }
                .footer-row-table td {
                    vertical-align: top;
                    padding: 0;
                }
                .qr-cell {
                    width: 48%;
                    text-align: left;
                }
                .qr-cell img {
                    width: 75px;
                    height: 75px;
                    padding: 3px;
                    border: 1px solid #e2e8f0;
                    border-radius: 4px;
                    background: #fff;
                }
                .bank-details {
                    font-size: 9.5px;
                    color: #64748b;
                    white-space: pre-line;
                    margin-top: 6px;
                    line-height: 1.35;
                    border-top: 1px dashed #e2e8f0;
                    padding-top: 4px;
                    max-width: 260px;
                }
                .total-table {
                    width: 210px;
                    margin-left: auto;
                    border-collapse: collapse;
                }
                .total-label {
                    text-align: right;
                    padding: 3px 8px;
                    font-size: 11.5px;
                    color: #64748b;
                }
                .total-value {
                    text-align: right;
                    width: 85px;
                    padding: 3px 0 3px 8px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #1e293b;
                }
                .grand-total-label {
                    text-align: right;
                    padding: 6px 8px 4px 8px;
                    font-size: 13px;
                    font-weight: bold;
                    color: #1e293b;
                    border-top: 1px dashed #cbd5e1;
                }
                .grand-total-value {
                    text-align: right;
                    width: 85px;
                    padding: 6px 0 4px 8px;
                    font-size: 14px;
                    font-weight: bold;
                    color: ' . $themeColor . ';
                    border-top: 1px dashed #cbd5e1;
                }
                .invoice-footer-text {
                    margin-top: 16px;
                    text-align: center;
                    font-size: 10.5px;
                    color: #94a3b8;
                    font-weight: normal;
                }
                .signature-img {
                    margin-top: 10px;
                    text-align: right;
                }
                .signature-img img {
                    max-height: 40px;
                    max-width: 120px;
                }
                .footer {
                    margin-top: 16px;
                    text-align: center;
                    font-size: 10px;
                    color: #94a3b8;
                    border-top: 1px solid #f1f5f9;
                    padding-top: 10px;
                }
                .brand-highlight {
                    color: ' . $themeColor . ';
                    font-weight: bold;
                }
                .dh-watermark {
                    position: fixed;
                    top: 40%;
                    left: 0;
                    width: 100%;
                    text-align: center;
                    transform: rotate(-35deg);
                    font-size: 70px;
                    font-weight: bold;
                    color: #94a3b8;
                    opacity: 0.15;
                    letter-spacing: 4px;
                }
            </style>
        </head>
        <body>
            ' . $watermarkHtml . '
            <div class="container">
                <table class="header-table">
                    <tr>
                        <td style="vertical-align: middle; width: 55%;">
                            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;"><tr>';
                        if ($logoBase64) {
                            $html .= '<td style="vertical-align: middle; padding: 0 10px 0 0; line-height: 0;">
                                <div class="shop-logo-box"><img class="header-logo" src="' . $logoBase64 . '" width="42" height="42" style="width: 42px; height: 42px; object-fit: cover;" /></div>
                            </td>';
                        }
                        $html .= '
                                <td style="vertical-align: middle; padding: 0;">
                                    <span class="shop-name">' . $this->renderMultilingualText($shop->name) . '</span><br>
                                    <span class="shop-details">
                                        ' . ($shop->mobile ? __('mobile') . ': ' . htmlspecialchars($shop->mobile) . '<br>' : '') . '
                                        ' . ($shop->address ? $this->renderMultilingualText($shop->address) . '<br>' : '') . '
                                        ' . ($shop->gst_number ? __('gstin_label') . ': ' . htmlspecialchars($shop->gst_number) : '') . '
                                    </span>
                                </td>
                            </tr></table>
                        </td>
                        <td class="invoice-title" style="vertical-align: middle; width: 45%; text-align: right;">
                            <div style="font-size: 18px; font-weight: bold; color: ' . $headerTextColor . '; line-height: 1.2;">
                                ' . strtoupper(__('purchase_invoice')) . ($badgeHtml ? ' ' . $badgeHtml : '') . '
                            </div>
                            <div class="invoice-meta" style="margin-top: 4px;">
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
                            <div class="section-title">' . __('supplier_details') . '</div>
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
                                        ? '<span style="color:#e11d48;font-weight:bold;">' . strtoupper(__('returned')) . '</span>'
                                        : ($purchase->status === 'Partially Returned'
                                            ? '<span style="color:#d97706;font-weight:bold;">' . strtoupper(__('partially_returned')) . '</span>'
                                            : ($purchase->status === 'Unpaid'
                                                ? '<span style="color:#d97706;font-weight:bold;">' . strtoupper(__('unpaid')) . '</span>'
                                                : ($purchase->status === 'Partially Paid'
                                                    ? '<span style="color:#2563eb;font-weight:bold;">' . strtoupper(__('partially_paid')) . '</span>'
                                                    : '<span style="color:#16a34a;font-weight:bold;">' . strtoupper(__('fully_paid')) . '</span>'
                                                )
                                            )
                                        )
                                ) . '<br>
                                <strong>' . (__('payment_type') ?: __('method')) . ':</strong> ' . __(strtolower(str_replace(' ', '_', $purchase->payment_type))) . '
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: ' . ($isReturned ? '36%' : '52%') . ';">' . __('product') . '</th>
                            <th style="width: ' . ($isReturned ? '16%' : '18%') . '; text-align: right;">' . __('price') . '</th>
                            <th style="width: ' . ($isReturned ? '12%' : '14%') . '; text-align: center;">' . __('quantity') . '</th>
                            ' . ($isReturned ? '<th style="width: 12%; text-align: center;">' . __('returned') . '</th><th style="width: 10%; text-align: center;">' . __('net_qty') . '</th>' : '') . '
                            <th style="width: ' . ($isReturned ? '14%' : '16%') . '; text-align: right;">' . __('total') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

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
                            <td>
                                <strong>' . $this->renderMultilingualText($item->product->name ?? __('deleted_product')) . '</strong>' .
                                ($lineDiscount > 0 ? '<div style="font-size:10px;font-weight:bold;color:#059669;margin-top:2px;">' . __('scheme_discount') . ': -&#8377;' . number_format($lineDiscount, 2) . '</div>' : '') .
                            '</td>
                            <td style="text-align: right;">&#8377;' . number_format($item->purchase_price, 2) . '</td>
                            <td style="text-align: center;">' . $item->quantity . '</td>
                            ' . ($isReturned ? '<td style="text-align: center; color: #ef4444; font-weight: bold;">' . ($retQty > 0 ? '-' . $retQty : '0') . '</td><td style="text-align: center; font-weight: bold;">' . $netQty . '</td>' : '') . '
                            <td style="text-align: right; font-weight: bold;">&#8377;' . number_format($lineTotal, 2) . '</td>
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
                            $html .= '<img src="data:image/png;base64,' . $qrBase64 . '" width="75" height="75" style="width: 75px; height: 75px; padding: 2px; border: 1px solid #e2e8f0; border-radius: 4px; background: #fff;" /><br><span style="font-size:9px;color:#64748b;font-weight:bold;display:inline-block;margin-top:3px;">' . htmlspecialchars($shop->upi_id) . '</span>';
                        }
                        if ($invoiceConfig->show_bank_details && $shop->bank_details) {
                            $html .= '<div class="bank-details">' . nl2br(htmlspecialchars($shop->bank_details)) . '</div>';
                        }
                        $html .= '
                        </td>
                        <td style="vertical-align: top; text-align: right;">
                            <table class="total-table" style="width: 210px; margin-left: auto; border-collapse: collapse;">';
                                if ($totalDiscount > 0) {
                                    $html .= '
                                    <tr>
                                        <td class="total-label">' . __('subtotal') . ':</td>
                                        <td class="total-value">&#8377;' . number_format($grossSubtotal, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="total-label" style="color: #059669;">' . __('scheme_discount') . ':</td>
                                        <td class="total-value" style="color: #059669;">-&#8377;' . number_format($totalDiscount, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                                <tr>
                                    <td class="grand-total-label">' . __('total_amount') . ':</td>
                                    <td class="grand-total-value">&#8377;' . number_format($purchase->total_amount, 2) . '</td>
                                </tr>';
                                if ($paidAmount > 0 || $dueAmount > 0) {
                                    $html .= '
                                    <tr>
                                        <td class="total-label" style="color: #16a34a; font-weight: bold; padding-top: 6px;">' . __('paid_amount') . ':</td>
                                        <td class="total-value" style="color: #16a34a; font-weight: bold; padding-top: 6px;">&#8377;' . number_format($paidAmount, 2) . '</td>
                                    </tr>
                                    <tr>
                                        <td class="total-label" style="color: #dc2626; font-weight: bold;">' . __('balance_due') . ':</td>
                                        <td class="total-value" style="color: #dc2626; font-weight: bold;">&#8377;' . number_format($dueAmount, 2) . '</td>
                                    </tr>';
                                }
                                $html .= '
                            </table>
                        </td>
                    </tr>
                </table>';

                $adminFooter = InvoiceSetting::get('footer_text');
                if ($adminFooter === 'Thank you for choosing DukanHisab. For assistance, contact support@dukanhisab.com.') {
                    $adminFooter = null;
                }
                $footerMessage = $shop->invoice_footer ?: ($adminFooter ?: __('invoice_footer_default'));

                $html .= '
                <div class="invoice-footer-text">' . $this->renderMultilingualText($footerMessage) . '</div>';

                if ($signatureBase64) {
                    $html .= '<div class="signature-img"><img src="' . $signatureBase64 . '" /></div>';
                }

                $html .= '
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

    /// The header logo cell is a fixed CSS box (max-width/max-height), but
    /// DomPDF/mPDF's limited CSS support doesn't reliably contain a
    /// non-square source image within both bounds at once — a tall/portrait
    /// upload renders as a stretched rectangle instead of a clean square.
    /// Pre-render the logo onto a fixed square canvas server-side with GD
    /// (letterboxed, aspect ratio preserved) so the PDF only ever embeds an
    /// already-square image and there's nothing left for the renderer's CSS
    /// engine to get wrong.
    private function getSquareLogoBase64(?string $relativePath, int $size = 160): ?string
    {
        $dataUri = $this->getBase64Image($relativePath);
        if (!$dataUri || !str_contains($dataUri, ',')) {
            return $dataUri;
        }

        if (!extension_loaded('gd')) {
            return $dataUri;
        }

        try {
            $binary = base64_decode(substr($dataUri, strpos($dataUri, ',') + 1));
            if ($binary === false) {
                return $dataUri;
            }

            $source = @imagecreatefromstring($binary);
            if (!$source) {
                return $dataUri;
            }

            $srcW = imagesx($source);
            $srcH = imagesy($source);
            if ($srcW <= 0 || $srcH <= 0) {
                imagedestroy($source);
                return $dataUri;
            }

            $scale = min($size / $srcW, $size / $srcH);
            $newW = max(1, (int) round($srcW * $scale));
            $newH = max(1, (int) round($srcH * $scale));

            $canvas = imagecreatetruecolor($size, $size);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);

            imagecopyresampled(
                $canvas,
                $source,
                (int) round(($size - $newW) / 2),
                (int) round(($size - $newH) / 2),
                0,
                0,
                $newW,
                $newH,
                $srcW,
                $srcH,
            );
            imagedestroy($source);

            ob_start();
            imagepng($canvas);
            $pngBytes = ob_get_clean();
            imagedestroy($canvas);

            if (!$pngBytes) {
                return $dataUri;
            }

            return 'data:image/png;base64,' . base64_encode($pngBytes);
        } catch (\Throwable $e) {
            return $dataUri;
        }
    }
}