<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function log(string $action, ?array $payload = null, ?int $userId = null, ?int $adminId = null): void
    {
        self::create([
            'user_id' => $userId ?? (auth()->check() ? auth()->id() : null),
            'admin_id' => $adminId ?? (auth('admin')->check() ? auth('admin')->id() : null),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
        ]);
    }
}
