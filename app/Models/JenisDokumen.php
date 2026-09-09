<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $fillable = ['kategori', 'nama_dokumen', 'is_wajib'];

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }
}
