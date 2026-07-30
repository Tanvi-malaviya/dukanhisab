<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $fillable = [
        'shop_id',
        'idempotency_key',
        'request_method',
        'request_path',
        'request_hash',
        'status',
        'response_status',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
