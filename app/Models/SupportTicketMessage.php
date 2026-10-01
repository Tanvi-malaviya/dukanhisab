<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'sender_type', // 'user' or 'admin'
        'sender_id',
        'message',
        'attachment',
    ];

    protected $appends = [
        'attachment_url',
        'sender_name',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderAdmin()
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }

    public function getSenderNameAttribute()
    {
        if ($this->sender_type === 'admin') {
            return $this->senderAdmin?->name ?? 'Support Team';
        }
        return $this->senderUser?->name ?? 'User';
    }
}
