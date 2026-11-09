<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsipDokumen extends Model
{
    use HasFactory;

    protected $table = 'arsip_dokumen';

    protected $fillable = [
        'user_id',
        'kategori_dokumen_id',
        'jenis_dokumen_id',
        'kategori',
        'jenis_dokumen',
        'tmt_tahun',
        'nomor_dokumen',
        'keterangan',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
        'last_action_by',
        'last_action_role',
        'last_action_type',
    ];

    /**
     * Pegawai pemilik dokumen
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Master Kategori
     */
    public function kategoriDokumen()
    {
        return $this->belongsTo(KategoriDokumen::class, 'kategori_dokumen_id');
    }

    /**
     * Master Jenis Dokumen (Sub-Kategori)
     */
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    /**
     * User pertama yang mengunggah dokumen
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * User terakhir yang melakukan aksi (upload/edit)
     */
    public function lastActionUser()
    {
        return $this->belongsTo(User::class, 'last_action_by');
    }

    /**
     * Format Update Terakhir: "Berkas diupload/diubah oleh [Role/Nama] — 15 Sep 2026, 21:30"
     */
    public function getUpdateTerakhirTextAttribute(): string
    {
        $action = ($this->last_action_type === 'update' || $this->updated_at > $this->created_at->addMinutes(1))
            ? 'diubah'
            : 'diupload';

        $roleOrName = $this->last_action_role 
            ?: ($this->lastActionUser->nama ?? ($this->uploader->nama ?? 'Kasubag'));

        $timestamp = ($this->updated_at ?? $this->created_at);
        $formattedDate = $timestamp ? $timestamp->translatedFormat('d M Y, H:i') : '-';

        return "Berkas {$action} oleh {$roleOrName} — {$formattedDate}";
    }

    /**
     * Format ukuran file yang mudah dibaca (KB / MB)
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }
        if ($this->file_size >= 1048576) {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
        return round($this->file_size / 1024, 1) . ' KB';
    }
}
