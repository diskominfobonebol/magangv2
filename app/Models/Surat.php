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
        'parent_id',
        'spt_induk_manual',
        'nomor_surat',
        'perihal',
        'tgl_surat',
        'tujuan',
        'jenis_penugasan',
        'uraian',
        'keterangan',
        'file_path',
        'file_name',
        'google_drive_file_id',
        'google_drive_url',
        'drive_upload_status',
        'has_sppd', 
        'status',
        'created_by',
    ];

    /**
     * Tentukan jenis penugasan (DD = Dalam Daerah, LD = Luar Daerah).
     * Saat ini default 'DD', extensible untuk logic masa depan berbasis field tujuan.
     */
    public static function determineJenisPenugasan(?string $tujuan = null): string
    {
        return 'DD';
    }

    /**
     * Konversi tanggal ke format bulan romawi (I - XII).
     */
    public static function formatBulanRomawi($date = null): string
    {
        $d = ($date instanceof \DateTimeInterface) ? $date : new \DateTime($date ?: date('Y-m-d'));
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawiBulan[(int)$d->format('n') - 1] ?? 'I';
    }

    /**
     * Konversi tanggal ke tahun 4 digit.
     */
    public static function formatTahun($date = null): string
    {
        $d = ($date instanceof \DateTimeInterface) ? $date : new \DateTime($date ?: date('Y-m-d'));
        return $d->format('Y');
    }

    /**
     * Format resmi Nomor Surat SPT:
     * 555/KOMINFO-BB/SPT-{jenis_penugasan}/{nomor_urut}{huruf_opsional}/{bulan_romawi}/{tahun}
     * Contoh non-backdate: 555/KOMINFO-BB/SPT-DD/003/IX/2026
     * Contoh backdate:     555/KOMINFO-BB/SPT-DD/001a/IX/2026
     */
    public static function formatNomorSpt(string $noUrut, $letterOrTgl = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        // Handle polymorphic signature: formatNomorSpt($noUrut, $tglSurat, $jenisPenugasan) or formatNomorSpt($noUrut, $letter, $tglSurat, $jenisPenugasan)
        $letter = '';
        if ($tglSurat === null && (is_string($letterOrTgl) && preg_match('/^\d{4}-\d{2}-\d{2}/', $letterOrTgl))) {
            $tglSurat = $letterOrTgl;
        } elseif (is_string($letterOrTgl) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $letterOrTgl)) {
            $letter = $letterOrTgl;
        }

        $bln = self::formatBulanRomawi($tglSurat);
        $thn = self::formatTahun($tglSurat);

        $cleanLetter = strtolower(trim($letter));
        $cleanUrut = '001';
        if (preg_match('/^(\d+)([a-z]*)$/i', trim($noUrut), $m)) {
            $cleanUrut = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            if (!empty($m[2])) {
                $cleanLetter = strtolower($m[2]);
            }
        } else {
            $cleanUrut = str_pad(preg_replace('/\D/', '', $noUrut) ?: '1', 3, '0', STR_PAD_LEFT);
        }

        $jp = strtoupper(trim($jenisPenugasan ?: 'DD'));

        return "555/KOMINFO-BB/SPT-{$jp}/{$cleanUrut}{$cleanLetter}/{$bln}/{$thn}";
    }

    /**
     * Format resmi Nomor Surat SPPD:
     * 090/KOMINFO-BB/SPPD/{jenis_penugasan}/{nomor_urut}{huruf_opsional}/{bulan_romawi}/{tahun}
     * Contoh non-backdate: 090/KOMINFO-BB/SPPD/DD/001/IX/2026
     * Contoh backdate:     090/KOMINFO-BB/SPPD/DD/001a/IX/2026
     */
    public static function formatNomorSppd(string $sppdUrut, string $letter = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        $bln = self::formatBulanRomawi($tglSurat);
        $thn = self::formatTahun($tglSurat);

        $cleanLetter = strtolower(trim($letter ?? ''));
        $cleanUrut = '001';
        if (preg_match('/^(\d+)([a-z]*)$/i', trim($sppdUrut), $m)) {
            $cleanUrut = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            if (!empty($m[2])) {
                $cleanLetter = strtolower($m[2]);
            }
        } else {
            $cleanUrut = str_pad(preg_replace('/\D/', '', $sppdUrut) ?: '1', 3, '0', STR_PAD_LEFT);
        }

        $jp = strtoupper(trim($jenisPenugasan ?: 'DD'));

        return "090/KOMINFO-BB/SPPD/{$jp}/{$cleanUrut}{$cleanLetter}/{$bln}/{$thn}";
    }

    /**
     * Ekstrak komponen nomor urut dan suffix huruf dari nomor surat (SPT maupun SPPD).
     * Returns: ['base_seq' => int, 'padded_seq' => string, 'letter' => string, 'full_seq' => string]
     */
    public static function extractSequenceComponents(?string $nomor): array
    {
        if (empty($nomor)) {
            return ['base_seq' => 1, 'padded_seq' => '001', 'letter' => '', 'full_seq' => '001'];
        }

        $trimmed = trim($nomor);

        // 1. Format SPT: 555/KOMINFO-BB/SPT-DD/{urut}{huruf}/{bulan}/{tahun}
        if (preg_match('/^555\/[^\/]+\/SPT-[^\/]+\/(\d+)([a-z]*)/i', $trimmed, $m)) {
            $base = (int)$m[1];
            $padded = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $letter = strtolower($m[2] ?? '');
            return ['base_seq' => $base, 'padded_seq' => $padded, 'letter' => $letter, 'full_seq' => $padded . $letter];
        }

        // 2. Format SPPD: 090/KOMINFO-BB/SPPD/DD/{urut}{huruf}/{bulan}/{tahun}
        if (preg_match('/^090\/[^\/]+\/SPPD\/[^\/]+\/(\d+)([a-z]*)/i', $trimmed, $m)) {
            $base = (int)$m[1];
            $padded = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $letter = strtolower($m[2] ?? '');
            return ['base_seq' => $base, 'padded_seq' => $padded, 'letter' => $letter, 'full_seq' => $padded . $letter];
        }

        // 3. Format Legacy / Variasi lain (pecah per '/')
        $parts = explode('/', $trimmed);
        foreach ($parts as $idx => $part) {
            if ($idx > 0 && preg_match('/^\s*(\d+)([a-z]*)/i', $part, $m)) {
                $base = (int)$m[1];
                $padded = str_pad($m[1], 3, '0', STR_PAD_LEFT);
                $letter = strtolower($m[2] ?? '');
                return ['base_seq' => $base, 'padded_seq' => $padded, 'letter' => $letter, 'full_seq' => $padded . $letter];
            }
        }

        // 4. Fallback angka + huruf pertama
        if (preg_match('/(\d+)([a-z]*)/i', $trimmed, $m)) {
            $base = (int)$m[1];
            $padded = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $letter = strtolower($m[2] ?? '');
            return ['base_seq' => $base, 'padded_seq' => $padded, 'letter' => $letter, 'full_seq' => $padded . $letter];
        }

        return ['base_seq' => 1, 'padded_seq' => '001', 'letter' => '', 'full_seq' => '001'];
    }

    /**
     * Ekstrak nomor urut dari nomor SPT (baik format baru, legacy, maupun manual).
     */
    public static function extractSptSequence(?string $sptNomor): string
    {
        $comp = self::extractSequenceComponents($sptNomor);
        return $comp['full_seq'];
    }

    /**
     * Hitung suffix huruf dari index (0->a, 1->b, 25->z, 26->aa, etc.)
     */
    public static function getLetterSuffix(int $index): string
    {
        $result = '';
        $n = $index;
        while ($n >= 0) {
            $result = chr(97 + ($n % 26)) . $result;
            $n = intdiv($n, 26) - 1;
        }
        return $result;
    }

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

    public function telaahs()
    {
        return $this->hasMany(SuratTelaah::class, 'spt_id');
    }

    /**
     * Scope query untuk pengurutan tabel rekapitulasi:
     * 1. Primary: tgl_surat DESC (terbaru di atas)
     * 2. Secondary: base_seq DESC dari Nomor SPT Efektif (induk / diri sendiri)
     * 3. Tertiary: letter ASC dari Nomor SPT Efektif ('' -> 'a' -> 'b')
     * 4. Quaternary: jenis_surat_id DESC (SPT Induk tampil sebelum SPPD Anaknya)
     * 5. Tie-breaker: id DESC
     */
    public function scopeOrderByRekap($query)
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            $effectiveSptSql = "COALESCE((SELECT p.nomor_surat FROM surats p WHERE p.id = surats.parent_id LIMIT 1), surats.spt_induk_manual, surats.nomor_surat)";

            return $query->orderBy('tgl_surat', 'desc')
                ->orderByRaw("CAST(COALESCE(REGEXP_SUBSTR(REGEXP_SUBSTR({$effectiveSptSql}, '[0-9]+[a-zA-Z]*(?=/([IVXLCDMivxlcdm]+|[0-9]{1,2})/[0-9]{4})'), '^[0-9]+'), REGEXP_SUBSTR({$effectiveSptSql}, '[0-9]+'), 0) AS UNSIGNED) DESC")
                ->orderByRaw("COALESCE(REGEXP_SUBSTR(REGEXP_SUBSTR({$effectiveSptSql}, '[0-9]+[a-zA-Z]*(?=/([IVXLCDMivxlcdm]+|[0-9]{1,2})/[0-9]{4})'), '[a-zA-Z]+$'), '') ASC")
                ->orderBy('jenis_surat_id', 'desc')
                ->orderBy('id', 'desc');
        }

        return $query->orderBy('tgl_surat', 'desc')->orderBy('id', 'desc');
    }
}