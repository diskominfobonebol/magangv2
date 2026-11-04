<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StatusBerkasNotification extends Notification
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
            'title' => $this->payload['title'] ?? 'Status Berkas Kenaikan Pangkat/KGB',
            'message' => $this->payload['message'] ?? '',
            'url' => $this->payload['url'] ?? '/dashboard/pegawai',
            'icon' => $this->payload['icon'] ?? 'check-circle',
            'type' => 'status_berkas',
            'dedup_key' => $this->payload['dedup_key'] ?? null,
        ];
    }
}

