<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['pegawai_id', 'jenis', 'nomor_tujuan', 'pesan', 'status_kirim', 'tanggal_kirim'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
