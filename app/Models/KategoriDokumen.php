<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriDokumen extends Model
{
    use HasFactory;

    protected $table = 'kategori_dokumen';

    protected $fillable = [
        'nama_kategori',
        'urutan',
    ];

    public function jenisDokumens()
    {
        return $this->hasMany(JenisDokumen::class, 'kategori_dokumen_id')->orderBy('urutan');
    }

    public function arsipDokumens()
    {
        return $this->hasMany(ArsipDokumen::class, 'kategori_dokumen_id');
    }
}
