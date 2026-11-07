<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    use Queueable;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->payload['title'] ?? 'Peringatan Keamanan Sistem',
            'message' => $this->payload['message'] ?? '',
            'url' => $this->payload['url'] ?? '/admin/users',
            'icon' => $this->payload['icon'] ?? 'shield-alert',
            'type' => 'security_alert',
            'dedup_key' => $this->payload['dedup_key'] ?? null,
        ];
    }
}

