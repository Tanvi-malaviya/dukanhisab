<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessageLog extends Model
{
    protected $table = 'whatsapp_message_logs';

    protected $fillable = [
        'shop_id',
        'event',
        'recipient_type',
        'recipient_id',
        'phone',
        'whatsapp_template_id',
        'template_name',
        'language',
        'sale_id',
        'purchase_id',
        'payload',
        'status',
        'provider_message_id',
        'error',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /** Delivery progress order — webhook updates never move a message backwards. */
    private const PROGRESS = ['queued' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function template()
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'whatsapp_template_id');
    }

    public function markSent(string $providerMessageId): void
    {
        $this->update([
            'status' => 'sent',
            'provider_message_id' => $providerMessageId,
            'sent_at' => $this->sent_at ?? now(),
            'error' => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        if ($this->status === 'failed') {
            return;
        }

        $this->update(['status' => 'failed', 'error' => $error, 'failed_at' => now()]);
    }

    /** Applies a Meta status callback (sent / delivered / read). Out-of-order callbacks are ignored. */
    public function advanceTo(string $status, ?\DateTimeInterface $at = null): void
    {
        if (!isset(self::PROGRESS[$status]) || !isset(self::PROGRESS[$this->status])) {
            return;
        }
        if (self::PROGRESS[$status] <= self::PROGRESS[$this->status]) {
            return;
        }

        $at ??= now();
        $updates = ['status' => $status];
        foreach (['sent', 'delivered', 'read'] as $step) {
            // A "read" callback can arrive without a "delivered" one; fill the earlier stamps too.
            if (self::PROGRESS[$step] <= self::PROGRESS[$status] && !$this->{$step . '_at'}) {
                $updates[$step . '_at'] = $at;
            }
        }

        $this->update($updates);
    }
}
