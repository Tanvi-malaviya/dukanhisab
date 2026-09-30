<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'account_number',
        'bank_name',
        'ifsc_code',
        'opening_balance',
        'is_default',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function cashBookEntries()
    {
        return $this->hasMany(CashBook::class);
    }

    /**
     * opening_balance + everything ever booked against this account. Kept as a method (not an
     * appended attribute) so a listing that needs several accounts can choose to compute this in
     * one aggregate query instead of N+1'ing it — see BankAccountApiController::index().
     */
    /**
     * The account day-to-day Sale/Purchase/due-payment cashbook entries settle into when no
     * specific account is chosen — every shop always has exactly one. Created lazily so a shop
     * that predates this feature (or has never had bank/UPI activity) still gets one on first use.
     */
    public static function defaultForShop(int $shopId): self
    {
        $existing = static::where('shop_id', $shopId)->where('is_default', true)->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'shop_id' => $shopId,
            'name' => 'Primary Bank Account',
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    public function computeBalance(): float
    {
        $in = $this->cashBookEntries()->where('type', 'cash_in')->sum('amount');
        $out = $this->cashBookEntries()->where('type', 'cash_out')->sum('amount');

        return round((float) $this->opening_balance + (float) $in - (float) $out, 2);
    }
}
