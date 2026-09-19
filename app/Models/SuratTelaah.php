<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratTelaah extends Model
{
    use HasFactory;

    protected $table = 'surat_telaahs';

    protected $fillable = [
        'nomor_telaah',
        'tanggal_telaah',
        'spt_id',
        'sppd_id',
        'uraian',
        'tujuan',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_telaah' => 'date',
    ];

    public function spt()
    {
        return $this->belongsTo(Surat::class, 'spt_id');
    }

    public function sppd()
    {
        return $this->belongsTo(Surat::class, 'sppd_id');
    }

    public function pegawais()
    {
        return $this->belongsToMany(Pegawai::class, 'pegawai_surat_telaah', 'surat_telaah_id', 'pegawai_id')
                    ->withTimestamps();
    }

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
                    $sq->where('nomor_telaah', 'like', "%{$search}%")
                       ->orWhere('uraian', 'like', "%{$search}%")
                       ->orWhere('tujuan', 'like', "%{$search}%")
                       ->orWhere('keterangan', 'like', "%{$search}%")
                       ->orWhereHas('spt', function ($sptQ) use ($search) {
                           $sptQ->where('nomor_surat', 'like', "%{$search}%");
                       })
                       ->orWhereHas('pegawais', function ($pq) use ($search) {
                           $pq->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");
                       });
                });
            })
            ->when($request->filled('year'), function ($q) use ($request) {
                $q->whereYear('tanggal_telaah', $request->year);
            })
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereBetween('tanggal_telaah', [$request->start_date, $request->end_date]);
            })
            ->when($request->filled('start_date') && !$request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_telaah', '>=', $request->start_date);
            })
            ->when(!$request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_telaah', '<=', $request->end_date);
            });
    }

    /**
     * Format resmi Nomor Surat Telaah:
     * 555/KEP/Telaah.staff/{nomor_urut}/{bulan_romawi}/{tahun}
     * Contoh: 555/KEP/Telaah.staff/01/IX/2026
     */
    public static function formatNomor(string|int $noUrut, $tgl = null): string
    {
        $bln = Surat::formatBulanRomawi($tgl);
        $thn = Surat::formatTahun($tgl);
        $cleanUrut = str_pad(preg_replace('/\D/', '', (string)$noUrut) ?: '1', 2, '0', STR_PAD_LEFT);
        return "555/KEP/Telaah.staff/{$cleanUrut}/{$bln}/{$thn}";
    }

    /**
     * Ekstrak nomor urut dari format nomor surat telaah.
     */
    public static function extractSequence(?string $nomor): int
    {
        if (empty($nomor)) return 0;
        if (preg_match('/555\/KEP\/Telaah\.staff\/(\d+)\//i', $nomor, $matches)) {
            return (int) $matches[1];
        }
        if (preg_match('/\/(\d+)\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/\d{4}/i', $nomor, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    /**
     * Dapatkan nomor urut berikutnya untuk Surat Telaah di tahun tertentu (reset tiap tahun baru).
     */
    public static function getNextSequence($year = null): int
    {
        $year = $year ?: date('Y');
        $maxSeq = self::whereYear('tanggal_telaah', $year)
            ->get()
            ->map(function ($item) {
                return self::extractSequence($item->nomor_telaah);
            })
            ->max() ?? 0;

        return $maxSeq + 1;
    }

    /**
     * Generate nomor surat telaah unik otomatis.
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
            $exists = self::where('nomor_telaah', $candidate)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $candidate;
    }
}
