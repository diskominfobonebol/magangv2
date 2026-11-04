<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengingatJatuhTempoNotification extends Notification
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
            'title' => $this->payload['title'] ?? 'Pengingat Jatuh Tempo',
            'message' => $this->payload['message'] ?? '',
            'url' => $this->payload['url'] ?? '/dashboard/pegawai',
            'icon' => $this->payload['icon'] ?? 'clock',
            'type' => 'pengingat_h30',
            'dedup_key' => $this->payload['dedup_key'] ?? null,
        ];
    }
}

