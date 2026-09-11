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
        $dateStr = $date->format('Y-m-d');
        $counter = static::where('shop_id', $shopId)
            ->where('document_type', $documentType)
            ->whereDate('counter_date', $dateStr)
            ->lockForUpdate()
            ->first();

        if ($counter) {
            $counter->increment('last_number');
            return $counter->last_number;
        }

        // If no counter row yet, check for existing records today to avoid collision
        $startNumber = 1;
        if ($documentType === 'sale') {
            $prefix = 'INV-' . $date->format('Ymd') . '-';
            $existing = Sale::withTrashed()
                ->where('shop_id', $shopId)
                ->where('sale_number', 'like', $prefix . '%')
                ->pluck('sale_number');
            $maxNum = 0;
            foreach ($existing as $sn) {
                if (preg_match('/-(\d+)$/', $sn, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $maxNum) $maxNum = $num;
                }
            }
            if ($maxNum > 0) {
                $startNumber = $maxNum + 1;
            }
        } elseif ($documentType === 'purchase') {
            $prefix = 'PUR-' . $date->format('Ymd') . '-';
            $existing = Purchase::withTrashed()
                ->where('shop_id', $shopId)
                ->where('purchase_number', 'like', $prefix . '%')
                ->pluck('purchase_number');
            $maxNum = 0;
            foreach ($existing as $pn) {
                if (preg_match('/-(\d+)$/', $pn, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $maxNum) $maxNum = $num;
                }
            }
            if ($maxNum > 0) {
                $startNumber = $maxNum + 1;
            }
        } elseif ($documentType === 'credit_note') {
            $prefix = 'CN-' . $date->format('Ymd') . '-';
            $existing = CreditNote::withTrashed()
                ->where('shop_id', $shopId)
                ->where('credit_note_number', 'like', $prefix . '%')
                ->pluck('credit_note_number');
            $maxNum = 0;
            foreach ($existing as $cn) {
                if (preg_match('/-(\d+)$/', $cn, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $maxNum) $maxNum = $num;
                }
            }
            if ($maxNum > 0) {
                $startNumber = $maxNum + 1;
            }
        }

        try {
            static::create([
                'shop_id' => $shopId,
                'document_type' => $documentType,
                'counter_date' => $date->toDateString(),
                'last_number' => $startNumber,
            ]);
            return $startNumber;
        } catch (\Illuminate\Database\QueryException $e) {
            // Lost the race to create the counter row; fall back to locking it and incrementing.
            $counter = static::where('shop_id', $shopId)
                ->where('document_type', $documentType)
                ->whereDate('counter_date', $dateStr)
                ->lockForUpdate()
                ->first();

            if ($counter) {
                $counter->increment('last_number');
                return $counter->last_number;
            }
            return $startNumber;
        }
    }
}
