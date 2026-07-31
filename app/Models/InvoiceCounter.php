<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    protected $fillable = [
        'shop_id',
        'document_type',
        'counter_date',
        'last_number',
    ];

    protected $casts = [
        'counter_date' => 'date',
    ];

    /**
     * Atomically get the next sequential number for a shop/document type/day,
     * resetting to 1 whenever the date changes. Must be called inside a
     * DB transaction so the row lock actually protects the increment.
     */
    public static function nextNumber(int $shopId, string $documentType, \Carbon\Carbon $date): int
    {
        $counter = static::where('shop_id', $shopId)
            ->where('document_type', $documentType)
            ->where('counter_date', $date->toDateString())
            ->lockForUpdate()
            ->first();

        if ($counter) {
            $counter->increment('last_number');
            return $counter->last_number;
        }

        try {
            static::create([
                'shop_id' => $shopId,
                'document_type' => $documentType,
                'counter_date' => $date->toDateString(),
                'last_number' => 1,
            ]);
            return 1;
        } catch (\Illuminate\Database\QueryException $e) {
            // Lost the race to create the counter row; fall back to locking it and incrementing.
            $counter = static::where('shop_id', $shopId)
                ->where('document_type', $documentType)
                ->where('counter_date', $date->toDateString())
                ->lockForUpdate()
                ->firstOrFail();
            $counter->increment('last_number');
            return $counter->last_number;
        }
    }
}
