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
        $resolvedAdminId = $adminId ?? (auth('admin')->check() ? auth('admin')->id() : null);
        $resolvedUserId = $userId;
        if ($resolvedUserId === null && !$resolvedAdminId && auth()->check()) {
            $resolvedUserId = auth()->id();
        }

        // Ensure user actually exists to avoid foreign key integrity constraint violations
        if ($resolvedUserId && !\App\Models\User::where('id', $resolvedUserId)->exists()) {
            $resolvedUserId = null;
        }

        self::create([
            'user_id' => $resolvedUserId,
            'admin_id' => $resolvedAdminId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
        ]);
    }
}
