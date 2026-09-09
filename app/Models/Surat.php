<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surats';

    protected $fillable = [
        'jenis_surat_id',
        'nomor_surat',
        'perihal',
        'tgl_surat',
        'tujuan',
        'uraian',
        'keterangan',
        'has_sppd', 
        'status',
        'created_by',
    ];

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function parent()
    {
        return $this->belongsTo(Surat::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Surat::class, 'parent_id');
    }

    public function pegawais()
    {
        return $this->belongsToMany(Pegawai::class, 'surat_pegawai', 'surat_id', 'pegawai_id')
                    ->withPivot('keterangan_tugas', 'nomor_sppd')
                    ->withTimestamps();
    }
}