<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'email',
        'password',
        'mobile',
        'avatar',
        'date_of_birth',
        'gender',
        'status',
        'last_login_at',
        'otp_code',
        'otp_expires_at',
        'language',
        'currency',
        'date_format',
        'time_format',
        'theme',
        'notification_preferences',
        'active_plan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'date_of_birth' => 'date',
            'notification_preferences' => 'array',
            'password' => 'hashed',
        ];
    }

    public function shops()
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    public function activePlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'active_plan_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function maxShops(): int
    {
        if ($this->activePlan && isset($this->activePlan->features['max_shops'])) {
            return (int) $this->activePlan->features['max_shops'];
        }
        return 1;
    }

    public function canAddShop(): bool
    {
        $max = $this->maxShops();
        if ($max === -1) return true;
        return $this->shops()->count() < $max;
    }

    public function maxDevices(): int
    {
        if ($this->activePlan && isset($this->activePlan->features['max_devices'])) {
            return (int) $this->activePlan->features['max_devices'];
        }
        return 1;
    }

    public function issueDeviceToken(string $tokenName = 'auth-token'): string
    {
        $maxDevices = $this->maxDevices();
        if ($maxDevices <= 1) {
            $this->tokens()->delete();
        } else {
            while ($this->tokens()->count() >= $maxDevices) {
                $this->tokens()->oldest()->first()?->delete();
            }
        }
        return $this->createToken($tokenName)->plainTextToken;
    }
}
