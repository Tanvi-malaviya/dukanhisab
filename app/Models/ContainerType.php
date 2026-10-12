<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContainerType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'deposit_amount',
        'total_owned',
    ];

    protected $casts = [
        'deposit_amount' => 'float',
        'total_owned' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function movements()
    {
        return $this->hasMany(ContainerMovement::class);
    }
}
