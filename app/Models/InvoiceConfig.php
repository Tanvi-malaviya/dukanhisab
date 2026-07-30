<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceConfig extends Model
{
    protected $fillable = [
        'shop_id',
        'starting_invoice_number',
        'auto_increment',
        'date_format',
        'paper_size',
        'theme_color',
        'show_customer_address',
        'show_customer_gst',
        'show_hsn_code',
        'show_discount',
        'show_tax',
        'show_sku',
        'gst_enabled',
        'round_off',
        'tax_summary',
        'show_upi_qr',
        'show_bank_details',
        'auto_print',
        'whatsapp_share',
        'pdf_download',
        'email_invoice',
    ];

    protected $casts = [
        'auto_increment' => 'boolean',
        'show_customer_address' => 'boolean',
        'show_customer_gst' => 'boolean',
        'show_hsn_code' => 'boolean',
        'show_discount' => 'boolean',
        'show_tax' => 'boolean',
        'show_sku' => 'boolean',
        'gst_enabled' => 'boolean',
        'round_off' => 'boolean',
        'tax_summary' => 'boolean',
        'show_upi_qr' => 'boolean',
        'show_bank_details' => 'boolean',
        'auto_print' => 'boolean',
        'whatsapp_share' => 'boolean',
        'pdf_download' => 'boolean',
        'email_invoice' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
