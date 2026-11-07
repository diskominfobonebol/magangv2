<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BerkasPegawaiNotification extends Notification
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
            'title' => $this->payload['title'] ?? 'Berkas Pegawai Diunggah',
            'message' => $this->payload['message'] ?? '',
            'url' => $this->payload['url'] ?? '/kenaikan-pangkat',
            'icon' => $this->payload['icon'] ?? 'upload-cloud',
            'type' => 'berkas_pegawai',
            'dedup_key' => $this->payload['dedup_key'] ?? null,
        ];
    }
}

