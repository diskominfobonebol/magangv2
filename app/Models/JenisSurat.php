<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $table = 'jenis_surats';

    protected $fillable = [
        'nama',
        'nama_jenis',
        'kode',
        'mode_nomor_default',
        'punya_langkah_sppd',
    ];

    protected $casts = [
        'punya_langkah_sppd' => 'boolean',
    ];

    public function getNamaAttribute($value)
    {
        return $value ?: $this->nama_jenis;
    }

    public function surats()
    {
        return $this->hasMany(Surat::class);
    }
}
