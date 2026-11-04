<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSuratTerpakai extends Model
{
    protected $table = 'nomor_surat_terpakai';

    protected $fillable = [
        'nomor_normalized',
        'nomor_asli',
        'kategori',
        'source_table',
        'source_id',
        'tgl_surat',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
    ];

    public static function normalize(string $nomor): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($nomor)));
    }
}
