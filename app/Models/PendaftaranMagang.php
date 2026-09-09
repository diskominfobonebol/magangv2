<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranMagang extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_magang';

    protected $fillable = [
        'user_id',
        'nim',
        'universitas',
        'jurusan',
        'no_hp',
        'tgl_mulai',
        'tgl_selesai',
        'surat_pengantar',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function suratBalasan()
    {
        return $this->hasOne(SuratBalasanMagang::class, 'pendaftaran_id');
    }
}
