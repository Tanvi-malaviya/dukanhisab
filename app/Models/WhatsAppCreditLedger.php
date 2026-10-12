<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppCreditLedger extends Model
{
    protected $table = 'whatsapp_credit_ledger';

    protected $fillable = [
        'shop_id',
        'type',
        'credits',
        'balance_after',
        'whatsapp_pack_purchase_id',
        'whatsapp_message_log_id',
        'admin_id',
        'note',
    ];

    protected $casts = [
        'credits' => 'integer',
        'balance_after' => 'integer',
    ];
}
