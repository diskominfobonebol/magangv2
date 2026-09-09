<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPegawai extends Model
{
    protected $fillable = ['kenpa_berkala_id', 'jenis_dokumen_id', 'file_path', 'status_verifikasi', 'uploaded_at', 'keterangan_admin'];

    public function kenpaBerkala()
    {
        return $this->belongsTo(KenpaBerkala::class);
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class);
    }
}
