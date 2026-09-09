<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KenpaBerkala extends Model
{
    // Daftarkan semua nama kolom yang kita gunakan di Controller agar diizinkan masuk ke Database
    protected $fillable = [
        'pegawai_id', 
        'jenis', 
        'tgl_terakhir', 
        'tgl_jatuh_tempo', 
        'status', 
        'progres_berkas', 
        'status_acc', 
        'keterangan'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }
}