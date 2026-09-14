<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = ['nip', 'nama', 'pangkat_golongan', 'jabatan', 'kategori_pegawai', 'no_wa', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surats()
    {
        return $this->belongsToMany(Surat::class, 'surat_pegawai')->withPivot('keterangan_tugas', 'nomor_sppd')->withTimestamps();
    }

    public function kenpaBerkalas()
    {
        return $this->hasMany(KenpaBerkala::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function dokumenPegawais()
    {
        // Artinya: Pegawai memiliki banyak Dokumen "MELALUI" perantara KenpaBerkala
        return $this->hasManyThrough(DokumenPegawai::class, KenpaBerkala::class);
    }
}
