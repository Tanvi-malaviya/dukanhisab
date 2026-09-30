<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminBroadcastNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $type;
    public ?string $imageUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $type, ?string $imageUrl = null)
    {
        $this->title    = $title;
        $this->message  = $message;
        $this->type     = $type;
        $this->imageUrl = $imageUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'title'    => $this->title,
            'message'  => $this->message,
            'type'     => $this->type,
            'sent_at'  => now()->toDateTimeString(),
        ];

        if ($this->imageUrl) {
            $data['image_url'] = $this->imageUrl;
        }

        return $data;
    }
}
