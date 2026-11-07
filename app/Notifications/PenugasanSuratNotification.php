<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PenugasanSuratNotification extends Notification
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
            'title' => $this->payload['title'] ?? 'Penugasan Surat',
            'message' => $this->payload['message'] ?? '',
            'url' => $this->payload['url'] ?? '/dashboard/pegawai',
            'icon' => $this->payload['icon'] ?? 'file-text',
            'type' => 'penugasan_surat',
            'dedup_key' => $this->payload['dedup_key'] ?? null,
        ];
    }
}

