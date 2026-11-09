<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori', 
        'nama_dokumen', 
        'is_wajib',
        'kategori_dokumen_id',
        'nama_jenis',
        'urutan',
    ];

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriDokumen::class, 'kategori_dokumen_id');
    }

    public function kategoriDokumen()
    {
        return $this->belongsTo(KategoriDokumen::class, 'kategori_dokumen_id');
    }

    public function arsipDokumens()
    {
        return $this->hasMany(ArsipDokumen::class, 'jenis_dokumen_id');
    }
}
