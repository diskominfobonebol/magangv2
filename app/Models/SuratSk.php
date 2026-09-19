<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratSk extends Model
{
    use HasFactory;

    protected $table = 'surat_sks';

    protected $fillable = [
        'nomor_sk',
        'tanggal_sk',
        'tentang',
        'keterangan',
        'file_sk',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_sk' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope query untuk filter pencarian, tahun, dan rentang tanggal.
     */
    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);
                $q->where(function ($sq) use ($search) {
                    $sq->where('nomor_sk', 'like', "%{$search}%")
                       ->orWhere('tentang', 'like', "%{$search}%")
                       ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('year'), function ($q) use ($request) {
                $q->whereYear('tanggal_sk', $request->year);
            })
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereBetween('tanggal_sk', [$request->start_date, $request->end_date]);
            })
            ->when($request->filled('start_date') && !$request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_sk', '>=', $request->start_date);
            })
            ->when(!$request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_sk', '<=', $request->end_date);
            });
    }

    /**
     * Format resmi Nomor Surat SK:
     * 555/KEP/Kominfo.BB/{nomor_urut}/{bulan_romawi}/{tahun}
     * Contoh: 555/KEP/Kominfo.BB/01/IX/2026
     */
    public static function formatNomor(string|int $noUrut, $tgl = null): string
    {
        $bln = Surat::formatBulanRomawi($tgl);
        $thn = Surat::formatTahun($tgl);
        $cleanUrut = str_pad(preg_replace('/\D/', '', (string)$noUrut) ?: '1', 2, '0', STR_PAD_LEFT);
        return "555/KEP/Kominfo.BB/{$cleanUrut}/{$bln}/{$thn}";
    }

    /**
     * Ekstrak nomor urut dari format nomor surat SK.
     */
    public static function extractSequence(?string $nomor): int
    {
        if (empty($nomor)) return 0;
        if (preg_match('/555\/KEP\/Kominfo\.BB\/(\d+)\//i', $nomor, $matches)) {
            return (int) $matches[1];
        }
        if (preg_match('/\/(\d+)\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/\d{4}/i', $nomor, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    /**
     * Dapatkan nomor urut berikutnya untuk Surat SK di tahun tertentu (reset tiap tahun baru).
     */
    public static function getNextSequence($year = null): int
    {
        $year = $year ?: date('Y');
        $maxSeq = self::whereYear('tanggal_sk', $year)
            ->get()
            ->map(function ($item) {
                return self::extractSequence($item->nomor_sk);
            })
            ->max() ?? 0;

        return $maxSeq + 1;
    }

    /**
     * Generate nomor surat SK unik otomatis.
     */
    public static function generateNomor($tgl = null, $manualNomor = null): string
    {
        if (!empty($manualNomor) && trim($manualNomor) !== '') {
            return trim($manualNomor);
        }
        $d = new \DateTime($tgl ?: date('Y-m-d'));
        $thn = $d->format('Y');
        $seq = self::getNextSequence($thn);
        
        do {
            $candidate = self::formatNomor($seq, $tgl);
            $exists = self::where('nomor_sk', $candidate)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $candidate;
    }
}
