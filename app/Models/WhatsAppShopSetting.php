<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppShopSetting extends Model
{
    protected $table = 'whatsapp_shop_settings';

    protected $fillable = [
        'shop_id',
        'event',
        'enabled',
        'schedule_days',
        'schedule_time',
        'min_due_amount',
        'last_run_at',
        'last_run_result',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'schedule_days' => 'array',
        'min_due_amount' => 'decimal:2',
        'last_run_at' => 'datetime',
        'last_run_result' => 'array',
    ];

    /** Events sent on the shop's weekly schedule; every other event is sent right after the action. */
    public const SCHEDULED_EVENTS = ['due_reminder', 'supplier_due'];

    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public const MAX_SCHEDULE_DAYS = 2;

    public const DEFAULTS = [
        'enabled' => false,
        'schedule_days' => ['mon'],
        'schedule_time' => '10:00',
        'min_due_amount' => 100,
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public static function isScheduled(string $event): bool
    {
        return in_array($event, self::SCHEDULED_EVENTS, true);
    }

    /** Whether the shop owner switched this event on (everything starts off). */
    public static function isEnabled(int $shopId, string $event): bool
    {
        return (bool) self::where('shop_id', $shopId)->where('event', $event)->value('enabled');
    }
}
