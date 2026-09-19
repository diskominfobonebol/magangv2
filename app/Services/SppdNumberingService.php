<?php

namespace App\Services;

use App\Models\Surat;
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
     * Hitung suffix huruf dari index (0->a, 1->b, 25->z, 26->aa, etc.)
     */
    public static function getLetterSuffix(int $index): string
    {
        return Surat::getLetterSuffix($index);
    }

    /**
     * Format resmi Nomor Surat SPT:
     * 555/KOMINFO-BB/SPT-{jenis_penugasan}/{nomor_urut}{huruf_opsional}/{bulan_romawi}/{tahun}
     */
    public static function formatNomorSpt(string $noUrut, $letterOrTgl = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSpt($noUrut, $letterOrTgl, $tglSurat, $jenisPenugasan);
    }

    /**
     * Format resmi Nomor Surat SPPD:
     * 090/KOMINFO-BB/SPPD/{jenis_penugasan}/{nomor_urut}{huruf_opsional}/{bulan_romawi}/{tahun}
     */
    public static function formatNomorSppd(string $sppdUrut, string $letter = '', $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSppd($sppdUrut, $letter, $tglSurat, $jenisPenugasan);
    }

    /**
     * Validasi format nomor surat SPPD manual jika diisi.
     */
    public static function validateManualNomorSppd(string $nomor): void
    {
        $clean = trim($nomor);
        $pattern = '/^090\/KOMINFO-BB\/SPPD\/[A-Za-z0-9_-]+\/\d{3,}[a-z]*\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/\d{4}$/';
        if (!preg_match($pattern, $clean)) {
            throw new InvalidArgumentException("Format nomor SPPD manual tidak sesuai dengan format resmi. Contoh: 090/KOMINFO-BB/SPPD/DD/001a/IX/" . date('Y'));
        }
    }

    /**
     * Validasi format nomor surat SPT manual jika diisi.
     */
    public static function validateManualNomorSpt(string $nomor): void
    {
        $clean = trim($nomor);
        $pattern = '/^555\/KOMINFO-BB\/SPT-[A-Za-z0-9_-]+\/\d{3,}[a-z]*\/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)\/\d{4}$/';
        if (!preg_match($pattern, $clean)) {
            throw new InvalidArgumentException("Format nomor SPT manual tidak sesuai dengan format resmi. Contoh: 555/KOMINFO-BB/SPT-DD/001a/IX/" . date('Y'));
        }
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
                ->where('status', '!=', 'Draft')
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
            // 1. Dari tabel surats (SPPD standalone atau child document)
            $querySurat = Surat::whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->where('status', '!=', 'Draft')
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

            // 2. Dari tabel surat_pegawai (SPPD yang melekat pada SPT)
            $queryPivot = DB::table('surat_pegawai')
                ->join('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
                ->whereNotNull('surat_pegawai.nomor_sppd')
                ->where('surat_pegawai.nomor_sppd', '!=', '')
                ->where('surat_pegawai.nomor_sppd', '!=', '-')
                ->where('surat_pegawai.nomor_sppd', 'like', '090/%')
                ->where('surats.status', '!=', 'Draft')
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

        // Deduplikasi berdasarkan nomor_surat
        $unique = $items->unique('nomor_surat')->values();

        return $unique->all();
    }

    /**
     * Hitung urutan tertinggi (max sequence) di seri tertentu pada tahun tertentu.
     */
    public static function getMaxSequence(string $series, $year = null, $excludeId = null): int
    {
        $items = self::getAllSeriesItems($series, $year, $excludeId);
        if (empty($items)) {
            return 0;
        }

        $max = 0;
        foreach ($items as $item) {
            if ($item['base_seq'] > $max) {
                $max = $item['base_seq'];
            }
        }
        return $max;
    }

    /**
     * Evaluasi kondisi Backdate dan penomoran untuk seri tertentu.
     *
     * @param string $series 'SPT' atau 'SPPD'
     * @param string|null $targetDate Tanggal surat yang diinput
     * @param string $jenisPenugasan 'DD' atau 'LD'
     * @param array $usedBatch Nomor yang sudah digenerate dalam batch request yang sama
     * @param int|null $excludeId ID dokumen yang sedang diedit (jika ada)
     * @return array
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
        $year = date('Y', strtotime($targetDate));
        $bln = self::formatBulanRomawi($targetDate);
        $thn = self::formatTahun($targetDate);
        $jp = strtoupper(trim($jenisPenugasan ?: 'DD'));

        $items = collect(self::getAllSeriesItems($series, $year, $excludeId));

        // Sertakan juga nomor yang ada di batch yang cocok dengan tahun dan seri ini
        foreach ($usedBatch as $batchNum) {
            if (empty($batchNum) || $batchNum === '-') continue;
            if ($series === 'SPT' && !str_starts_with($batchNum, '555/')) continue;
            if ($series === 'SPPD' && !str_starts_with($batchNum, '090/')) continue;

            $comp = Surat::extractSequenceComponents($batchNum);
            $items->push([
                'id' => null,
                'nomor_surat' => $batchNum,
                'tgl_surat' => $targetDate,
                'base_seq' => $comp['base_seq'],
                'padded_seq' => $comp['padded_seq'],
                'letter' => $comp['letter'],
                'full_seq' => $comp['full_seq'],
            ]);
        }

        $items = $items->unique('nomor_surat')->values();

        // 1. Cari surat dengan nomor urut TERTINGGI (max base_seq) yang sudah ada di seri dan tahun yang sama
        $maxBaseSeq = 0;
        $highestSurat = null;

        if ($items->isNotEmpty()) {
            $maxBaseSeq = $items->max('base_seq');
            // Ambil surat dengan max base_seq; jika ada beberapa (misal 002, 002a), cari yang tanpa huruf atau tanggal terbaru
            $candidates = $items->where('base_seq', $maxBaseSeq)->sortByDesc('tgl_surat')->values();
            $highestSurat = $candidates->first();
        }

        $highestDate = $highestSurat ? $highestSurat['tgl_surat'] : null;

        // 2. Evaluasi Backdate:
        // Terjadi HANYA jika sudah ada surat di seri & tahun ini, dan targetDate < tanggal_surat milik surat urut tertinggi
        $isBackdate = ($highestSurat !== null && $highestDate !== null && $targetDate < $highestDate);

        if ($isBackdate) {
            // === JALUR BACKDATE ===
            // Cari surat pendahulu: surat di seri yang sama dengan tanggal_surat terakhir yang masih <= targetDate
            $predecessors = $items->filter(function($it) use ($targetDate) {
                return $it['tgl_surat'] <= $targetDate;
            })->sort(function($a, $b) {
                if ($a['tgl_surat'] !== $b['tgl_surat']) {
                    return strcmp($b['tgl_surat'], $a['tgl_surat']); // Tgl DESC
                }
                if ($a['base_seq'] !== $b['base_seq']) {
                    return $b['base_seq'] - $a['base_seq']; // Base seq DESC
                }
                return strcmp($b['letter'], $a['letter']); // Letter DESC
            })->values();

            if ($predecessors->isNotEmpty()) {
                $predecessor = $predecessors->first();
            } else {
                // Jika input date lebih awal dari surat pertama, jadikan surat urutan terendah sebagai pendahulu
                $predecessor = $items->sortBy('base_seq')->sortBy('tgl_surat')->first();
            }

            $baseSeq = $predecessor['base_seq'];
            $paddedBaseSeq = $predecessor['padded_seq'];
            $predecessorNomor = $predecessor['nomor_surat'];

            // Kumpulkan huruf suffix yang sudah dipakai untuk base_seq ini
            $usedLetters = $items->where('base_seq', $baseSeq)
                ->pluck('letter')
                ->filter(function($l) { return $l !== ''; })
                ->unique()
                ->values()
                ->all();

            $letterIndex = 0;
            while (in_array(self::getLetterSuffix($letterIndex), $usedLetters, true)) {
                $letterIndex++;
            }
            $letter = self::getLetterSuffix($letterIndex);
            $nextSeq = $paddedBaseSeq . $letter;

            if ($series === 'SPT') {
                $previewNomor = self::formatNomorSpt($paddedBaseSeq, $letter, $targetDate, $jp);
            } else {
                $previewNomor = self::formatNomorSppd($paddedBaseSeq, $letter, $targetDate, $jp);
            }

            $notice = "Terdeteksi tanggal mundur (backdate) — nomor akan disisipkan sebagai anak dari nomor {$predecessorNomor}, menjadi {$previewNomor}.";

            return [
                'is_backdate' => true,
                'series' => $series,
                'target_date' => $targetDate,
                'highest_surat' => $highestSurat['nomor_surat'],
                'highest_date' => $highestDate,
                'predecessor_nomor' => $predecessorNomor,
                'predecessor_seq' => $paddedBaseSeq,
                'base_seq' => $paddedBaseSeq,
                'letter' => $letter,
                'next_seq' => $nextSeq,
                'preview_nomor' => $previewNomor,
                'notice' => $notice,
            ];
        } else {
            // === JALUR NORMAL (NON-BACKDATE) ===
            $nextCounter = $maxBaseSeq + 1;
            $paddedSeq = str_pad($nextCounter, 3, '0', STR_PAD_LEFT);
            $letter = '';
            $nextSeq = $paddedSeq;

            if ($series === 'SPT') {
                $previewNomor = self::formatNomorSpt($paddedSeq, '', $targetDate, $jp);
            } else {
                $previewNomor = self::formatNomorSppd($paddedSeq, '', $targetDate, $jp);
            }

            return [
                'is_backdate' => false,
                'series' => $series,
                'target_date' => $targetDate,
                'highest_surat' => $highestSurat ? $highestSurat['nomor_surat'] : null,
                'highest_date' => $highestDate,
                'predecessor_nomor' => null,
                'predecessor_seq' => null,
                'base_seq' => $paddedSeq,
                'letter' => '',
                'next_seq' => $nextSeq,
                'preview_nomor' => $previewNomor,
                'notice' => null,
            ];
        }
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

        if (!empty($manualNomor) && trim($manualNomor) !== '') {
            $manualClean = trim($manualNomor);
            if ($series === 'SPT') {
                self::validateManualNomorSpt($manualClean);
            } else {
                self::validateManualNomorSppd($manualClean);
            }
            return $manualClean;
        }

        $targetDate = $targetDate ? date('Y-m-d', strtotime($targetDate)) : date('Y-m-d');
        $eval = self::checkBackdate($series, $targetDate, $jenisPenugasan, $usedBatch, $excludeId);

        // Pastikan tidak collides dengan database atau batch
        if ($eval['is_backdate']) {
            $baseSeq = $eval['base_seq'];
            $letterIndex = 0;
            while (true) {
                $letter = self::getLetterSuffix($letterIndex);
                if ($series === 'SPT') {
                    $candidate = self::formatNomorSpt($baseSeq, $letter, $targetDate, $jenisPenugasan);
                } else {
                    $candidate = self::formatNomorSppd($baseSeq, $letter, $targetDate, $jenisPenugasan);
                }

                $inBatch = in_array($candidate, $usedBatch, true);
                $existsSurat = Surat::where('nomor_surat', $candidate)
                    ->when($excludeId, function($q) use ($excludeId) { $q->where('id', '!=', $excludeId); })
                    ->exists();
                $existsPivot = ($series === 'SPPD') ? DB::table('surat_pegawai')->where('nomor_sppd', $candidate)->exists() : false;

                if (!$inBatch && !$existsSurat && !$existsPivot) {
                    $usedBatch[] = $candidate;
                    return $candidate;
                }
                $letterIndex++;
            }
        } else {
            $counter = (int)$eval['base_seq'];
            while (true) {
                $padded = str_pad($counter, 3, '0', STR_PAD_LEFT);
                if ($series === 'SPT') {
                    $candidate = self::formatNomorSpt($padded, '', $targetDate, $jenisPenugasan);
                } else {
                    $candidate = self::formatNomorSppd($padded, '', $targetDate, $jenisPenugasan);
                }

                $inBatch = in_array($candidate, $usedBatch, true);
                $existsSurat = Surat::where('nomor_surat', $candidate)
                    ->when($excludeId, function($q) use ($excludeId) { $q->where('id', '!=', $excludeId); })
                    ->exists();
                $existsPivot = ($series === 'SPPD') ? DB::table('surat_pegawai')->where('nomor_sppd', $candidate)->exists() : false;

                if (!$inBatch && !$existsSurat && !$existsPivot) {
                    $usedBatch[] = $candidate;
                    return $candidate;
                }
                $counter++;
            }
        }
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
     * Generate nomor SPPD baru (independen murni).
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

    /**
     * Hitung nomor urut SPPD berikutnya (untuk compatibility).
     */
    public static function getNextSppdSequence($year = null, bool $lock = false): int
    {
        return self::getMaxSequence('SPPD', $year) + 1;
    }

    /**
     * Hitung nomor urut SPT berikutnya (untuk compatibility).
     */
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
