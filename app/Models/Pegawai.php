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

    public function suratTelaahs()
    {
        return $this->belongsToMany(SuratTelaah::class, 'pegawai_surat_telaah', 'pegawai_id', 'surat_telaah_id')->withTimestamps();
    }

    public function getAccStatusAttribute()
    {
        return app(\App\Http\Controllers\KenaikanPangkatController::class)->calculateAccStatus($this);
    }

    /**
     * Scope untuk mengurutkan pegawai sesuai hierarki jabatan organisasi:
     * 1. Kepala Dinas
     * 2. Sekretaris
     * 3. Kepala Bidang (Informatika, Komunikasi, dst)
     * 4. Kasubag & Subkor
     * 5. Staff & Pegawai lainnya
     */
    public function scopeOrderByHierarki($query)
    {
        return $query->orderByRaw("
            CASE
                WHEN jabatan LIKE '%Kepala Dinas%' THEN 1
                WHEN jabatan LIKE '%Sekretaris%' THEN 2
                WHEN jabatan LIKE '%Kepala Bidang Informatika%' THEN 3
                WHEN jabatan LIKE '%Kepala Bidang Komunikasi%' THEN 4
                WHEN jabatan LIKE '%Kepala Bidang%' OR jabatan LIKE '%Kabid%' THEN 5
                WHEN jabatan LIKE '%Kasubag Kepegawaian%' THEN 6
                WHEN jabatan LIKE '%Kasubag Keuangan%' THEN 7
                WHEN jabatan LIKE '%Kasubag%' OR jabatan LIKE '%Kasubbag%' OR jabatan LIKE '%Kepala Sub Bagian%' THEN 8
                WHEN jabatan LIKE '%Subkor%' OR jabatan LIKE '%Sub Koordinator%' THEN 9
                ELSE 10
            END ASC
        ")->orderBy('id', 'asc');
    }
}
