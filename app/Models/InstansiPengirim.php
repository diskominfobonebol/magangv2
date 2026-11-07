<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstansiPengirim extends Model
{
    use HasFactory;

    protected $table = 'instansi_pengirim';

    protected $fillable = [
        'nama',
        'nama_normal',
    ];

    /**
     * Helper untuk menormalisasi nama instansi:
     * - Menghapus spasi di awal/akhir (trim).
     * - Mengubah spasi berlebih menjadi 1 spasi tunggal.
     * - Mengubah seluruh huruf menjadi huruf kecil (lowercase).
     */
    public static function normalizeName(?string $name): string
    {
        if ($name === null) {
            return '';
        }

        $trimmed = trim($name);
        $collapsed = preg_replace('/\s+/', ' ', $trimmed);

        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * Helper untuk merapikan nama tampilan (tanpa mengubah kapitalisasi):
     * - Menghapus spasi di awal/akhir (trim).
     * - Mengubah spasi ganda/berlebih menjadi 1 spasi tunggal.
     */
    public static function cleanDisplayName(?string $name): string
    {
        if ($name === null) {
            return '';
        }

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Cari entri instansi pengirim atau daftarkan otomatis:
     * - Jika nama yang diketik cocok secara nama_normal (case-insensitive & spasi berlebih dirapikan),
     *   kembalikan nama resmi dari master (agar ejaan seragam).
     * - Jika belum ada di master, buat entri baru dan kembalikan namanya.
     */
    public static function findOrRegister(string $rawName): string
    {
        $cleanName = self::cleanDisplayName($rawName);
        $normal = self::normalizeName($cleanName);

        if (empty($normal)) {
            return $cleanName;
        }

        $existing = self::where('nama_normal', $normal)->first();

        if ($existing) {
            // Gunakan ejaan resmi yang sudah tersimpan di master
            return $existing->nama;
        }

        // Daftarkan sebagai instansi baru di master
        $newInstansi = self::create([
            'nama' => $cleanName,
            'nama_normal' => $normal,
        ]);

        return $newInstansi->nama;
    }

    /**
     * Hitung berapa banyak Surat Masuk yang menggunakan nama instansi ini.
     */
    public function getSuratMasukCountAttribute(): int
    {
        return SuratMasuk::where('asal_surat', $this->nama)->count();
    }
}

