<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\NomorSuratTerpakai;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SppdNumberingService
{
    /**
     * Konversi tanggal ke format bulan romawi (I - XII).
     */
    public static function formatBulanRomawi($date = null): string
    {
        return Surat::formatBulanRomawi($date);
    }

    /**
     * Konversi tanggal ke tahun 4 digit.
     */
    public static function formatTahun($date = null): string
    {
        return Surat::formatTahun($date);
    }

    /**
     * Ekstrak nomor urut dari nomor surat (SPT maupun SPPD).
     */
    public static function extractSptSequence(?string $sptNomor): string
    {
        return Surat::extractSptSequence($sptNomor);
    }

    /**
     * Ekstrak komponen nomor urut (base_seq, padded_seq, letter, full_seq).
     */
    public static function extractSequenceComponents(?string $nomor): array
    {
        return Surat::extractSequenceComponents($nomor);
    }

    /**
     * Hitung suffix huruf dari index (dipertahankan untuk membaca format nomor lama yang berhuruf).
     */
    public static function getLetterSuffix(int $index): string
    {
        return Surat::getLetterSuffix($index);
    }

    /**
     * Format resmi Nomor Surat SPT:
     * 555/KOMINFO-BB/SPT-{jenis_penugasan}/{nomor_urut}/{bulan_romawi}/{tahun}
     */
    public static function formatNomorSpt(string $noUrut, $letterOrTgl = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSpt($noUrut, $letterOrTgl, $tglSurat, $jenisPenugasan);
    }

    /**
     * Format resmi Nomor Surat SPPD:
     * 090/KOMINFO-BB/SPPD/{jenis_penugasan}/{nomor_urut}/{bulan_romawi}/{tahun}
     */
    public static function formatNomorSppd(string $sppdUrut, string $letter = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSppd($sppdUrut, $letter, $tglSurat, $jenisPenugasan);
    }

    /**
     * Validasi nomor surat manual: diterima apa adanya (setelah trim, tanpa memaksa regex),
     * dengan batas panjang wajar (maks 255 char) dan wajib unik lintas seluruh surat keluar.
     */
    public static function validateManualNomorSurat(string $nomor, ?string $excludeTable = null, ?int $excludeId = null): void
    {
        $clean = trim($nomor);
        if ($clean === '' || $clean === '-') {
            throw new InvalidArgumentException("Nomor surat manual tidak boleh kosong.");
        }
        if (strlen($clean) > 255) {
            throw new InvalidArgumentException("Panjang nomor surat manual melebihi batas wajar (maksimal 255 karakter).");
        }

        $dupe = NomorSuratService::checkDuplicate($clean, $excludeTable, $excludeId);
        if ($dupe) {
            $tglStr = $dupe['tgl_surat'] ? " tanggal " . date('d/m/Y', strtotime($dupe['tgl_surat'])) : "";
            throw new InvalidArgumentException("Nomor surat '{$clean}' sudah digunakan oleh {$dupe['kategori']}{$tglStr}. Silakan gunakan nomor lain.");
        }
    }

    public static function validateManualNomorSppd(string $nomor, ?int $excludeId = null): void
    {
        self::validateManualNomorSurat($nomor, 'surats', $excludeId);
    }

    public static function validateManualNomorSpt(string $nomor, ?int $excludeId = null): void
    {
        self::validateManualNomorSurat($nomor, 'surats', $excludeId);
    }

    /**
     * Mengambil seluruh arsip nomor resmi yang sudah ada untuk satu seri ('SPT' atau 'SPPD') pada tahun tertentu.
     */
    public static function getAllSeriesItems(string $series, $year = null, $excludeId = null): array
    {
        $year = $year ?: date('Y');
        $series = strtoupper(trim($series));
        $items = collect();

        if ($series === 'SPT') {
            $query = Surat::whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->where(function($q) {
                    $q->where('jenis_surat_id', 2)
                      ->orWhereNull('jenis_surat_id')
                      ->orWhere('nomor_surat', 'like', '555/%');
                })
                ->where(function($q) use ($year) {
                    $q->whereYear('tgl_surat', $year)
                      ->orWhere('nomor_surat', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            foreach ($query->get(['id', 'nomor_surat', 'tgl_surat', 'status']) as $s) {
                $comp = Surat::extractSequenceComponents($s->nomor_surat);
                if ($comp['base_seq'] <= 0) {
                    continue; // Lewati nomor manual/legacy yang tidak cocok pola resmi dari perhitungan nomor urut berikutnya
                }
                $items->push([
                    'id' => $s->id,
                    'nomor_surat' => $s->nomor_surat,
                    'tgl_surat' => $s->tgl_surat ? date('Y-m-d', strtotime($s->tgl_surat)) : '1970-01-01',
                    'base_seq' => $comp['base_seq'],
                    'padded_seq' => $comp['padded_seq'],
                    'letter' => $comp['letter'],
                    'full_seq' => $comp['full_seq'],
                ]);
            }
        } elseif ($series === 'SPPD') {
            // 1. Dari tabel surats
            $querySurat = Surat::whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->where(function($q) {
                    $q->where('jenis_surat_id', 1)
                      ->orWhere('nomor_surat', 'like', '090/%');
                })
                ->where(function($q) use ($year) {
                    $q->whereYear('tgl_surat', $year)
                      ->orWhere('nomor_surat', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $querySurat->where('id', '!=', $excludeId);
            }

            foreach ($querySurat->get(['id', 'nomor_surat', 'tgl_surat', 'status']) as $s) {
                $comp = Surat::extractSequenceComponents($s->nomor_surat);
                if ($comp['base_seq'] <= 0) {
                    continue;
                }
                $items->push([
                    'id' => $s->id,
                    'nomor_surat' => $s->nomor_surat,
                    'tgl_surat' => $s->tgl_surat ? date('Y-m-d', strtotime($s->tgl_surat)) : '1970-01-01',
                    'base_seq' => $comp['base_seq'],
                    'padded_seq' => $comp['padded_seq'],
                    'letter' => $comp['letter'],
                    'full_seq' => $comp['full_seq'],
                ]);
            }

            // 2. Dari tabel surat_pegawai
            $queryPivot = DB::table('surat_pegawai')
                ->join('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
                ->whereNotNull('surat_pegawai.nomor_sppd')
                ->where('surat_pegawai.nomor_sppd', '!=', '')
                ->where('surat_pegawai.nomor_sppd', '!=', '-')
                ->where('surat_pegawai.nomor_sppd', 'like', '090/%')
                ->where(function($q) use ($year) {
                    $q->whereYear('surats.tgl_surat', $year)
                      ->orWhere('surat_pegawai.nomor_sppd', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $queryPivot->where('surats.id', '!=', $excludeId);
            }

            $pivotRows = $queryPivot->select([
                'surats.id as surat_id',
                'surat_pegawai.nomor_sppd',
                'surats.tgl_surat'
            ])->get();

            foreach ($pivotRows as $p) {
                $comp = Surat::extractSequenceComponents($p->nomor_sppd);
                if ($comp['base_seq'] <= 0) {
                    continue;
                }
                $items->push([
                    'id' => $p->surat_id,
                    'nomor_surat' => $p->nomor_sppd,
                    'tgl_surat' => $p->tgl_surat ? date('Y-m-d', strtotime($p->tgl_surat)) : '1970-01-01',
                    'base_seq' => $comp['base_seq'],
                    'padded_seq' => $comp['padded_seq'],
                    'letter' => $comp['letter'],
                    'full_seq' => $comp['full_seq'],
                ]);
            }
        }

        return $items->unique('nomor_surat')->values()->all();
    }

    public static function getMaxSequence(string $series, $year = null, $excludeId = null): int
    {
        return NomorSuratService::getMaxSequence($series, (int)($year ?: date('Y')), $excludeId);
    }

    /**
     * Evaluasi kondisi Backdate dan nomor urut berikutnya.
     * Tidak ada lagi nomor berhuruf otomatis.
     * Jika backdate (target_date < today), mode otomatis dinonaktifkan dan harus manual.
     */
    public static function checkBackdate(
        string $series,
        $targetDate = null,
        string $jenisPenugasan = 'DD',
        array $usedBatch = [],
        $excludeId = null
    ): array {
        $series = strtoupper(trim($series));
        $targetDate = $targetDate ? date('Y-m-d', strtotime($targetDate)) : date('Y-m-d');
        $jp = strtoupper(trim($jenisPenugasan ?: 'DD'));

        $eval = NomorSuratService::isBackdate($series, $targetDate, $excludeId);

        $year = (int)date('Y', strtotime($targetDate));
        $maxSeq = NomorSuratService::getMaxSequence($series, $year, $excludeId);
        $nextCounter = $maxSeq + 1;
        $paddedSeq = str_pad($nextCounter, 3, '0', STR_PAD_LEFT);

        $previewNomor = NomorSuratService::formatCandidateNomor($series, $nextCounter, $targetDate, $jp);

        return [
            'is_backdate' => $eval['is_backdate'],
            'series' => $series,
            'target_date' => $targetDate,
            'latest_date' => null,
            'notice' => $eval['notice'],
            'base_seq' => $paddedSeq,
            'next_seq' => $paddedSeq,
            'preview_nomor' => $previewNomor,
            'mode_nomor_allowed' => $eval['is_backdate'] ? ['manual'] : ['otomatis', 'manual'],
        ];
    }

    /**
     * Generate nomor surat unik baru untuk seri tertentu ('SPT' atau 'SPPD').
     */
    public static function generateNextNumber(
        string $series,
        $targetDate = null,
        ?string $manualNomor = null,
        array &$usedBatch = [],
        string $jenisPenugasan = 'DD',
        $excludeId = null
    ): string {
        $series = strtoupper(trim($series));

        // Jika nomor diisi manual oleh admin
        if (!empty($manualNomor) && trim($manualNomor) !== '' && trim($manualNomor) !== '-') {
            $clean = trim($manualNomor);
            self::validateManualNomorSurat($clean, 'surats', $excludeId);
            return $clean;
        }

        // Generate otomatis berurut murni via NomorSuratService
        return NomorSuratService::generateNomorBerurut($series, $targetDate, $jenisPenugasan, $usedBatch, $excludeId);
    }

    /**
     * Generate nomor SPT baru.
     */
    public static function generateNextSptNumber(
        $targetDate = null,
        ?string $manualNomor = null,
        array &$usedBatch = [],
        string $jenisPenugasan = 'DD',
        $excludeId = null
    ): string {
        return self::generateNextNumber('SPT', $targetDate, $manualNomor, $usedBatch, $jenisPenugasan, $excludeId);
    }

    /**
     * Generate nomor SPPD baru.
     */
    public static function generateNextSppdNumber(
        ?string $parentSptNomor = null,
        $parentSptId = null,
        $tglSurat = null,
        $manualNomor = null,
        array &$usedInCurrentBatch = [],
        string $jenisPenugasan = 'DD',
        $excludeId = null
    ): string {
        return self::generateNextNumber('SPPD', $tglSurat, $manualNomor, $usedInCurrentBatch, $jenisPenugasan, $excludeId);
    }

    public static function getNextSppdSequence($year = null, bool $lock = false): int
    {
        return self::getMaxSequence('SPPD', $year) + 1;
    }

    public static function getNextSptSequence($year = null, bool $lock = false): int
    {
        return self::getMaxSequence('SPT', $year) + 1;
    }

    /**
     * Ambil metadata SPT lengkap untuk frontend preview dan modal Standalone.
     */
    public static function getSptMetadataList()
    {
        return Surat::where(function($q) {
                $q->where('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id')
                  ->orWhere('nomor_surat', 'like', '555/%');
            })
            ->where('status', '!=', 'Draft')
            ->orderByDesc('tgl_surat')
            ->orderByDesc('id')
            ->get(['id', 'nomor_surat', 'tgl_surat', 'uraian', 'tujuan', 'jenis_penugasan'])
            ->map(function($spt) {
                $comp = Surat::extractSequenceComponents($spt->nomor_surat);
                return [
                    'id' => $spt->id,
                    'nomor_surat' => $spt->nomor_surat,
                    'tgl_surat' => $spt->tgl_surat,
                    'uraian' => $spt->uraian,
                    'tujuan' => $spt->tujuan,
                    'jenis_penugasan' => $spt->jenis_penugasan ?: 'DD',
                    'spt_urut' => $comp['full_seq'],
                ];
            });
    }

    /**
     * Summary seri untuk AlpineJS di frontend.
     */
    public static function getSeriesSummaryForFrontend($year = null): array
    {
        $year = $year ?: date('Y');
        $sptItems = self::getAllSeriesItems('SPT', $year);
        $sppdItems = self::getAllSeriesItems('SPPD', $year);

        return [
            'year' => (int)$year,
            'spt' => [
                'items' => $sptItems,
                'max_seq' => self::getMaxSequence('SPT', $year),
                'next_seq' => str_pad(self::getMaxSequence('SPT', $year) + 1, 3, '0', STR_PAD_LEFT),
            ],
            'sppd' => [
                'items' => $sppdItems,
                'max_seq' => self::getMaxSequence('SPPD', $year),
                'next_seq' => str_pad(self::getMaxSequence('SPPD', $year) + 1, 3, '0', STR_PAD_LEFT),
            ]
        ];
    }
}
