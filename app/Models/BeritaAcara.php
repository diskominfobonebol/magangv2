<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'berita_acara';

    protected $fillable = [
        'aset_id',
        'jenis',
        'sumber',
        'nomor_surat',
        'nomor_surat_normalized',
        'tanggal',
        'keterangan',
        'file_path',
        'file_nama_asli',
        'file_ukuran',
        'user_id',
        'pihak_pertama_nama',
        'pihak_pertama_nip',
        'pihak_pertama_jabatan',
        'pihak_kedua_nama',
        'pihak_kedua_nip',
        'pihak_kedua_jabatan',
        'mengetahui_nama',
        'mengetahui_nip',
        'mengetahui_jabatan',
        'jumlah_barang',
        'keperluan',
        'tempat_tanda_tangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'file_ukuran' => 'integer',
    ];

    protected $appends = [
        'display_nomor',
        'file_url',
    ];

    /**
     * Label nomor BAST yang rapi untuk tampilan tabel & modal.
     * Jika nomor kosong (misal hasil scan tanpa nomor), gunakan label cadangan: "BAST (scan) · [tanggal]"
     */
    public function getDisplayNomorAttribute(): string
    {
        if (!empty($this->nomor_surat) && trim($this->nomor_surat) !== '') {
            return trim($this->nomor_surat);
        }

        $tgl = $this->tanggal ? \Carbon\Carbon::parse($this->tanggal)->format('d/m/Y') : '-';
        return "BAST (scan) · {$tgl}";
    }

    /**
     * URL relatif file PDF Berita Acara untuk langsung dibuka di tab baru
     */
    public function getFileUrlAttribute(): string
    {
        return route('admin.berita_acara.file', $this->id, false);
    }

    /**
     * Normalisasi nomor surat: trim spasi, hilangkan spasi ganda, ubah ke huruf kapital (uppercase)
     */
    public static function normalizeNomorSurat(?string $nomor): ?string
    {
        if ($nomor === null || trim($nomor) === '') {
            return null;
        }

        return strtoupper(preg_replace('/\s+/', ' ', trim($nomor)));
    }

    protected static function booted()
    {
        static::saving(function ($ba) {
            $ba->nomor_surat_normalized = static::normalizeNomorSurat($ba->nomor_surat);
        });
    }

    /**
     * Relasi ke Master Data Aset Peralatan Mesin
     */
    public function aset()
    {
        return $this->belongsTo(AsetPeralatanMesin::class, 'aset_id');
    }

    /**
     * Relasi ke User pengunggah dokumen
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor format ukuran file dalam format KB / MB
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_ukuran) {
            return '-';
        }
        if ($this->file_ukuran >= 1048576) {
            return number_format($this->file_ukuran / 1048576, 2) . ' MB';
        }
        return number_format($this->file_ukuran / 1024, 1) . ' KB';
    }
}
