<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratBalasanMagang extends Model
{
    use HasFactory;

    protected $table = 'surat_balasan_magang';

    protected $fillable = [
        'pendaftaran_id',
        'no_surat',
        'tanggal_surat',
        'file_surat_balasan',
        'catatan_admin',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }
}
