<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\SuratTelaah;
use App\Models\SuratSk;
use App\Models\SuratMasuk;
use App\Models\JenisSurat;
use App\Models\NomorSuratTerpakai;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NomorSuratService
{
    /**
     * Normalisasi nomor surat (trim, spasi ganda dirapikan, lowercase) untuk perbandingan unik.
     */
    public static function normalizeNomor(?string $nomor): string
    {
        if (empty($nomor)) {
            return '';
        }
        return strtolower(preg_replace('/\s+/', ' ', trim($nomor)));
    }

    /**
     * Helper bersama: Parse komponen nomor surat dari ujung kanan menggunakan regex resmi.
     * Mengambil nomor urut, huruf suffix opsional, bulan romawi, dan tahun 4 digit.
     * Pola: /(?:^|\/)(\d+)([a-z]?)\/([IVXLCDM]+)\/(\d{4})$/i
     *
     * @param string|null $nomor
     * @return array|null [
     *     'urut' => int,
     *     'padded_urut' => string,
     *     'letter' => string,
     *     'full_seq' => string,
     *     'bulan_romawi' => string,
     *     'tahun' => int,
     * ] atau null jika nomor kosong atau tidak cocok dengan pola penomoran resmi.
     */
    public static function parseNomorSurat(?string $nomor): ?array
    {
        if (empty($nomor) || trim($nomor) === '' || trim($nomor) === '-') {
            return null;
        }

        $trimmed = trim($nomor);
        if (preg_match('/(?:^|\/)(\d+)([a-z]*)\/([IVXLCDM]+)\/(\d{4})$/i', $trimmed, $m)) {
            $urutInt = (int)$m[1];
            $letter = strtolower($m[2] ?? '');
            $padded = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            return [
                'urut' => $urutInt,
                'padded_urut' => $padded,
                'letter' => $letter,
                'full_seq' => $padded . $letter,
                'bulan_romawi' => strtoupper($m[3]),
                'tahun' => (int)$m[4],
            ];
        }

        return null;
    }

    /**
     * Mendapatkan nama kategori baku dari ID atau String.
     */
    public static function resolveKategoriName($kategoriOrId): string
    {
        if (is_numeric($kategoriOrId)) {
            $id = (int)$kategoriOrId;
            $jenis = JenisSurat::find($id);
            if ($jenis) {
                return $jenis->nama ?: $jenis->nama_jenis;
            }
            if ($id === 1) return 'SPPD';
            if ($id === 2) return 'SPT';
            if ($id === 3) return 'Surat Telaah';
            if ($id === 4) return 'Surat SK';
            return 'Lainnya';
        }

        $str = trim((string)$kategoriOrId);
        $upper = strtoupper($str);
        if ($upper === 'SPT' || str_contains($upper, 'PERINTAH TUGAS')) return 'SPT';
        if ($upper === 'SPPD' || str_contains($upper, 'PERJALANAN DINAS')) return 'SPPD';
        if (str_contains($upper, 'TELAAH')) return 'Surat Telaah';
        if (str_contains($upper, 'SK') || str_contains($upper, 'KEPUTUSAN')) return 'Surat SK';
        return 'Lainnya';
    }

    /**
     * Definisi Backdate bersama:
     * Backdate = Tanggal Surat lebih awal dari hari ini (tanggal_surat < today, dibandingkan per tanggal tanpa jam, memakai zona waktu aplikasi).
     * Hari ini dan tanggal setelahnya BUKAN backdate, sehingga nomor otomatis tetap boleh dipakai.
     * Batasnya selalu mengikuti tanggal hari ini di server.
     *
     * @param mixed $param1 Kategori string/int ATAU langsung string tanggal jika 1 argumen
     * @param string|null $tanggalSurat
     * @param int|null $excludeId
     * @return array
     */
    public static function isBackdate($param1 = null, $tanggalSurat = null, $excludeId = null): array
    {
        if ($tanggalSurat === null && $param1 !== null && (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($param1)) || strtotime($param1))) {
            $kategori = 'SPT';
            $targetDate = date('Y-m-d', strtotime(trim($param1)));
        } else {
            $kategori = self::resolveKategoriName($param1 ?: 'SPT');
            $targetDate = $tanggalSurat ? date('Y-m-d', strtotime($tanggalSurat)) : date('Y-m-d');
        }

        $today = now()->format('Y-m-d');

        // Backdate HANYA jika targetDate < today (tanpa jam, server time)
        $isBackdate = ($targetDate < $today);

        $notice = $isBackdate 
            ? "Tanggal surat sebelum hari ini (backdate), nomor harus diisi manual."
            : null;

        return [
            'is_backdate' => $isBackdate,
            'kategori' => $kategori,
            'target_date' => $targetDate,
            'today' => $today,
            'latest_date' => null,
            'reason' => $isBackdate ? 'prior_to_today' : null,
            'notice' => $notice,
        ];
    }

    /**
     * Hitung urutan integer tertinggi yang terpakai di tahun tertentu untuk kategori tertentu.
     * Nomor urut berlanjut sepanjang tahun dan tidak direset saat ganti bulan.
     * Mengurai semua nomor dengan regex resmi dari ujung kanan (urut, huruf opsional, bulan romawi, tahun),
     * mengambil angka tertinggi + 1 (termasuk nomor manual, nomor berhuruf dihitung angkanya, dan status Draft).
     *
     * @param string|int $kategoriOrId
     * @param int|null $year
     * @param int|null $excludeId
     * @return int
     */
    public static function getMaxSequence($kategoriOrId, $year = null, $excludeId = null): int
    {
        $kategori = self::resolveKategoriName($kategoriOrId);
        $year = (int)($year ?: date('Y'));
        $allNumbers = [];

        if ($kategori === 'SPT') {
            $query = Surat::where(function($q) {
                    $q->where('jenis_surat_id', 2)
                      ->orWhereNull('jenis_surat_id')
                      ->orWhere('nomor_surat', 'like', '555/%');
                })
                ->whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->where('nomor_surat', '!=', '-')
                ->where(function($q) use ($year) {
                    $q->whereYear('tgl_surat', $year)
                      ->orWhere('nomor_surat', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $allNumbers = $query->pluck('nomor_surat')->all();
        } elseif ($kategori === 'SPPD') {
            // 1. Dari tabel surats
            $querySurat = Surat::where(function($q) {
                    $q->where('jenis_surat_id', 1)
                      ->orWhere('nomor_surat', 'like', '090/%');
                })
                ->whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->where('nomor_surat', '!=', '-')
                ->where(function($q) use ($year) {
                    $q->whereYear('tgl_surat', $year)
                      ->orWhere('nomor_surat', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $querySurat->where('id', '!=', $excludeId);
            }

            $fromSurats = $querySurat->pluck('nomor_surat')->all();

            // 2. Dari tabel surat_pegawai (pivot per pegawai)
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

            $fromPivots = $queryPivot->pluck('surat_pegawai.nomor_sppd')->all();

            $allNumbers = array_merge($fromSurats, $fromPivots);
        } elseif ($kategori === 'Surat Telaah') {
            $query = SuratTelaah::whereNotNull('nomor_telaah')
                ->where('nomor_telaah', '!=', '')
                ->where('nomor_telaah', '!=', '-')
                ->where(function($q) use ($year) {
                    $q->whereYear('tanggal_telaah', $year)
                      ->orWhere('nomor_telaah', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $allNumbers = $query->pluck('nomor_telaah')->all();
        } elseif ($kategori === 'Surat SK') {
            $query = SuratSk::whereNotNull('nomor_sk')
                ->where('nomor_sk', '!=', '')
                ->where('nomor_sk', '!=', '-')
                ->where(function($q) use ($year) {
                    $q->whereYear('tanggal_sk', $year)
                      ->orWhere('nomor_sk', 'like', "%/{$year}");
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $allNumbers = $query->pluck('nomor_sk')->all();
        } else {
            // Lainnya
            $lainnyaId = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->value('id');
            if ($lainnyaId) {
                $query = Surat::where('jenis_surat_id', $lainnyaId)
                    ->whereNotNull('nomor_surat')
                    ->where('nomor_surat', '!=', '')
                    ->where('nomor_surat', '!=', '-')
                    ->where(function($q) use ($year) {
                        $q->whereYear('tgl_surat', $year)
                          ->orWhere('nomor_surat', 'like', "%/{$year}");
                    });

                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }

                $allNumbers = $query->pluck('nomor_surat')->all();
            }
        }

        $maxUrut = 0;
        foreach (array_unique($allNumbers) as $nomor) {
            $parsed = self::parseNomorSurat($nomor);
            if ($parsed !== null) {
                if ($parsed['tahun'] === $year) {
                    if ($parsed['urut'] > $maxUrut) {
                        $maxUrut = $parsed['urut'];
                    }
                }
            } else {
                \Illuminate\Support\Facades\Log::info("Nomor surat tidak cocok pola resmi dilewati: {$nomor}");
            }
        }

        return $maxUrut;
    }

    /**
     * Dapatkan urutan integer berikutnya untuk kategori dan tahun tertentu.
     */
    public static function getNextSequenceNumber($kategoriOrId, $year = null, $excludeId = null): int
    {
        return self::getMaxSequence($kategoriOrId, $year, $excludeId) + 1;
    }

    /**
     * Verifikasi apakah dokumen sumber masih ada di database.
     */
    public static function verifySourceDocumentExists(string $sourceTable, int $sourceId): bool
    {
        if ($sourceTable === 'surats') {
            return Surat::where('id', $sourceId)->exists();
        }
        if ($sourceTable === 'surat_pegawai') {
            return DB::table('surat_pegawai')->where('id', $sourceId)->exists();
        }
        if ($sourceTable === 'surat_telaahs') {
            return SuratTelaah::where('id', $sourceId)->exists();
        }
        if ($sourceTable === 'surat_sks') {
            return SuratSk::where('id', $sourceId)->exists();
        }
        return false;
    }

    /**
     * Cari tanggal surat terbaru di database untuk grup penomoran (kategori & tahun) yang sama.
     * (Dipertahankan sebagai helper baca)
     */
    public static function getLatestDateInGroup(string $kategori, $year, $excludeId = null): ?string
    {
        $year = $year ?: date('Y');

        if ($kategori === 'SPT') {
            return Surat::where(function($q) {
                    $q->where('jenis_surat_id', 2)
                      ->orWhereNull('jenis_surat_id')
                      ->orWhere('nomor_surat', 'like', '555/%');
                })
                ->whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->whereYear('tgl_surat', $year)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('tgl_surat');
        }

        if ($kategori === 'SPPD') {
            $latestSurat = Surat::where(function($q) {
                    $q->where('jenis_surat_id', 1)
                      ->orWhere('nomor_surat', 'like', '090/%');
                })
                ->whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->whereYear('tgl_surat', $year)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('tgl_surat');

            $latestPivot = DB::table('surat_pegawai')
                ->join('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
                ->whereNotNull('surat_pegawai.nomor_sppd')
                ->where('surat_pegawai.nomor_sppd', '!=', '')
                ->where('surat_pegawai.nomor_sppd', '!=', '-')
                ->whereYear('surats.tgl_surat', $year)
                ->when($excludeId, fn($q) => $q->where('surats.id', '!=', $excludeId))
                ->max('surats.tgl_surat');

            if ($latestSurat && $latestPivot) {
                return max($latestSurat, $latestPivot);
            }
            return $latestSurat ?: $latestPivot;
        }

        if ($kategori === 'Surat Telaah') {
            return SuratTelaah::whereNotNull('nomor_telaah')
                ->where('nomor_telaah', '!=', '')
                ->whereYear('tanggal_telaah', $year)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('tanggal_telaah');
        }

        if ($kategori === 'Surat SK') {
            return SuratSk::whereNotNull('nomor_sk')
                ->where('nomor_sk', '!=', '')
                ->whereYear('tanggal_sk', $year)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('tanggal_sk');
        }

        $lainnyaId = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->value('id');
        if ($lainnyaId) {
            return Surat::where('jenis_surat_id', $lainnyaId)
                ->whereNotNull('nomor_surat')
                ->where('nomor_surat', '!=', '')
                ->whereYear('tgl_surat', $year)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->max('tgl_surat');
        }

        return null;
    }

    /**
     * Pengecekan nomor duplikat secara menyeluruh (Tugas 3).
     * Memeriksa nomor ternormalisasi pada nomor_surat_terpakai dan seluruh tabel surat keluar.
     *
     * @param string|null $nomor
     * @param string|null $excludeTable
     * @param int|null $excludeId
     * @return array|null Mengembalikan data surat pemilik nomor jika duplikat, atau null jika nomor unik.
     */
    public static function checkDuplicate(?string $nomor, ?string $excludeTable = null, ?int $excludeId = null): ?array
    {
        if (empty($nomor) || trim($nomor) === '' || trim($nomor) === '-') {
            return null;
        }

        $norm = self::normalizeNomor($nomor);

        // 1. Cek pada tabel registri nomor_surat_terpakai
        $query = DB::table('nomor_surat_terpakai')->where('nomor_normalized', $norm);
        if ($excludeTable && $excludeId) {
            $query->whereNot(function($q) use ($excludeTable, $excludeId) {
                $q->where('source_table', $excludeTable)->where('source_id', $excludeId);
            });
        }
        $existing = $query->first();

        if ($existing) {
            $sourceExists = self::verifySourceDocumentExists($existing->source_table, (int)$existing->source_id);
            if (!$sourceExists) {
                // Dokumen sumber sudah dihapus, lepaskan nomor dari registri nomor_surat_terpakai agar nomor tersedia kembali
                DB::table('nomor_surat_terpakai')->where('id', $existing->id)->delete();
                $existing = null;
            } else {
                return [
                    'is_duplicate' => true,
                    'nomor' => $existing->nomor_asli,
                    'kategori' => $existing->kategori,
                    'source_table' => $existing->source_table,
                    'source_id' => $existing->source_id,
                    'tgl_surat' => $existing->tgl_surat ? date('Y-m-d', strtotime($existing->tgl_surat)) : null,
                    'url' => self::buildSuratUrl($existing->kategori, $existing->source_table, $existing->source_id),
                ];
            }
        }

        // 2. Fallback cek langsung pada tabel surats (termasuk status Draft)
        $suratMatch = Surat::whereRaw("LOWER(TRIM(nomor_surat)) = ?", [$norm])
            ->when($excludeTable === 'surats' && $excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($suratMatch) {
            $kat = ($suratMatch->jenis_surat_id == 1 || str_starts_with($suratMatch->nomor_surat, '090/')) ? 'SPPD' : 'SPT';
            if ($suratMatch->jenisSurat && in_array($suratMatch->jenisSurat->nama, ['Lainnya', 'Surat Telaah', 'Surat SK'])) {
                $kat = $suratMatch->jenisSurat->nama;
            }
            return [
                'is_duplicate' => true,
                'nomor' => $suratMatch->nomor_surat,
                'kategori' => $kat,
                'source_table' => 'surats',
                'source_id' => $suratMatch->id,
                'tgl_surat' => $suratMatch->tgl_surat,
                'url' => route('surat.show', $suratMatch->id),
            ];
        }

        // 3. Fallback cek surat_pegawai (nomor SPPD personel)
        $pivotMatch = DB::table('surat_pegawai')
            ->join('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
            ->whereRaw("LOWER(TRIM(surat_pegawai.nomor_sppd)) = ?", [$norm])
            ->where('surat_pegawai.nomor_sppd', '!=', '-')
            ->when($excludeTable === 'surats' && $excludeId, fn($q) => $q->where('surat_pegawai.surat_id', '!=', $excludeId))
            ->when($excludeTable === 'surat_pegawai' && $excludeId, fn($q) => $q->where('surat_pegawai.id', '!=', $excludeId))
            ->select('surat_pegawai.id as pivot_id', 'surat_pegawai.surat_id', 'surat_pegawai.nomor_sppd', 'surats.tgl_surat')
            ->first();

        if ($pivotMatch) {
            return [
                'is_duplicate' => true,
                'nomor' => $pivotMatch->nomor_sppd,
                'kategori' => 'SPPD',
                'source_table' => 'surat_pegawai',
                'source_id' => $pivotMatch->pivot_id,
                'tgl_surat' => $pivotMatch->tgl_surat,
                'url' => route('surat.show', $pivotMatch->surat_id),
            ];
        }

        // 4. Fallback cek surat_telaahs
        $telaahMatch = SuratTelaah::whereRaw("LOWER(TRIM(nomor_telaah)) = ?", [$norm])
            ->when($excludeTable === 'surat_telaahs' && $excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($telaahMatch) {
            return [
                'is_duplicate' => true,
                'nomor' => $telaahMatch->nomor_telaah,
                'kategori' => 'Surat Telaah',
                'source_table' => 'surat_telaahs',
                'source_id' => $telaahMatch->id,
                'tgl_surat' => $telaahMatch->tanggal_telaah ? $telaahMatch->tanggal_telaah->format('Y-m-d') : null,
                'url' => route('surat.telaah.show', $telaahMatch->id),
            ];
        }

        // 5. Fallback cek surat_sks
        $skMatch = SuratSk::whereRaw("LOWER(TRIM(nomor_sk)) = ?", [$norm])
            ->when($excludeTable === 'surat_sks' && $excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($skMatch) {
            return [
                'is_duplicate' => true,
                'nomor' => $skMatch->nomor_sk,
                'kategori' => 'Surat SK',
                'source_table' => 'surat_sks',
                'source_id' => $skMatch->id,
                'tgl_surat' => $skMatch->tanggal_sk ? $skMatch->tanggal_sk->format('Y-m-d') : null,
                'url' => route('surat.sk.show', $skMatch->id),
            ];
        }

        return null;
    }

    /**
     * Registrasikan nomor terpakai ke tabel nomor_surat_terpakai.
     * Menggunakan DB unique constraint pada nomor_normalized untuk menjamin tidak ada duplikat.
     */
    public static function registerNomor(string $nomor, string $kategori, string $sourceTable, int $sourceId, $tglSurat = null): void
    {
        if (empty($nomor) || trim($nomor) === '' || trim($nomor) === '-') {
            return;
        }

        $norm = self::normalizeNomor($nomor);
        $asli = trim($nomor);

        // Hapus registrasi lama untuk dokumen yang sama jika nomornya diubah
        DB::table('nomor_surat_terpakai')
            ->where('source_table', $sourceTable)
            ->where('source_id', $sourceId)
            ->delete();

        // Cek jika nomor ini sudah terpakai oleh dokumen lain
        $existing = DB::table('nomor_surat_terpakai')
            ->where('nomor_normalized', $norm)
            ->first();

        if ($existing) {
            $isSameDocument = false;
            if ($existing->source_table === $sourceTable && (int)$existing->source_id === (int)$sourceId) {
                $isSameDocument = true;
            } elseif ($existing->source_table === 'surats' && $sourceTable === 'surat_pegawai') {
                $pivotSuratId = DB::table('surat_pegawai')->where('id', $sourceId)->value('surat_id');
                if ($pivotSuratId && (int)$pivotSuratId === (int)$existing->source_id) {
                    $isSameDocument = true;
                }
            } elseif ($existing->source_table === 'surat_pegawai' && $sourceTable === 'surats') {
                $pivotSuratId = DB::table('surat_pegawai')->where('id', $existing->source_id)->value('surat_id');
                if ($pivotSuratId && (int)$pivotSuratId === (int)$sourceId) {
                    $isSameDocument = true;
                }
            }

            if ($isSameDocument) {
                DB::table('nomor_surat_terpakai')->where('id', $existing->id)->update([
                    'nomor_asli' => $asli,
                    'tgl_surat' => $tglSurat ? date('Y-m-d', strtotime($tglSurat)) : null,
                    'updated_at' => now(),
                ]);
                return;
            }

            // Jika dokumen sumber dari existing sudah tidak ada, hapus orphan ini
            if (!self::verifySourceDocumentExists($existing->source_table, (int)$existing->source_id)) {
                DB::table('nomor_surat_terpakai')->where('id', $existing->id)->delete();
            } else {
                throw new \InvalidArgumentException("Nomor surat '{$nomor}' sudah terdaftar pada {$existing->kategori} tanggal " . ($existing->tgl_surat ? date('d/m/Y', strtotime($existing->tgl_surat)) : '-') . ".");
            }
        }

        // Simpan ke registri dengan DB INSERT. Unique constraint menjamin keamanan concurrency
        DB::table('nomor_surat_terpakai')->insert([
            'nomor_normalized' => $norm,
            'nomor_asli' => $asli,
            'kategori' => $kategori,
            'source_table' => $sourceTable,
            'source_id' => $sourceId,
            'tgl_surat' => $tglSurat ? date('Y-m-d', strtotime($tglSurat)) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Lepaskan nomor yang terdaftar dari tabel nomor_surat_terpakai saat dokumen dihapus.
     * Menerima ($sourceTable, $sourceId) ATAU ($nomor).
     */
    public static function releaseNomor($sourceTableOrNomor, $sourceId = null): void
    {
        if ($sourceId === null) {
            $norm = self::normalizeNomor((string)$sourceTableOrNomor);
            DB::table('nomor_surat_terpakai')
                ->where('nomor_normalized', $norm)
                ->delete();
        } else {
            DB::table('nomor_surat_terpakai')
                ->where('source_table', (string)$sourceTableOrNomor)
                ->where('source_id', (int)$sourceId)
                ->delete();
        }
    }

    /**
     * Generate nomor surat berurut otomatis untuk kategori tertentu.
     * Aturan:
     * - Grup urutan = jenis surat + tahun.
     * - Nomor urut berlanjut sepanjang tahun dan TIDAK direset saat ganti bulan. Reset HANYA saat ganti tahun.
     * - Nomor otomatis = nomor urut tertinggi yang terpakai di tahun itu + 1, melewati nomor yang sudah dipakai.
     * - Menolak generate otomatis jika tanggal surat mundur/backdate (tanggal_surat < today).
     *
     * @param string|int $kategoriOrId
     * @param string|null $tanggalSurat
     * @param string $jenisPenugasan 'DD' atau 'LD'
     * @param array $usedBatch Daftar nomor yang sudah digenerate dalam batch request ini
     * @param int|null $excludeId
     * @return string
     * @throws InvalidArgumentException
     */
    public static function generateNomorBerurut(
        $kategoriOrId,
        $tanggalSurat = null,
        string $jenisPenugasan = 'DD',
        array &$usedBatch = [],
        $excludeId = null
    ): string {
        $kategori = self::resolveKategoriName($kategoriOrId);
        $targetDate = $tanggalSurat ? date('Y-m-d', strtotime($tanggalSurat)) : date('Y-m-d');

        // Validasi: Surat backdate DILARANG menggunakan generate otomatis!
        $evalBackdate = self::isBackdate($kategori, $targetDate, $excludeId);
        if ($evalBackdate['is_backdate']) {
            throw new InvalidArgumentException("Tanggal surat sebelum hari ini (backdate), nomor otomatis dinonaktifkan dan harus diisi manual.");
        }

        $thn = date('Y', strtotime($targetDate));
        $bln = Surat::formatBulanRomawi($targetDate);
        $jp = strtoupper(trim($jenisPenugasan ?: 'DD'));

        // Cek kategori Lainnya: jika kodenya kosong, nomor otomatis tidak tersedia
        if ($kategori === 'Lainnya') {
            $lainnya = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->first();
            if (!$lainnya || empty($lainnya->kode)) {
                throw new InvalidArgumentException("Kode surat untuk kategori Lainnya belum diisi, nomor otomatis tidak dapat digunakan.");
            }
        }

        return DB::transaction(function() use ($kategori, $targetDate, $thn, $bln, $jp, &$usedBatch, $excludeId) {
            // Lock tabel registri agar tidak ada race condition antara dua admin
            DB::table('nomor_surat_terpakai')->lockForUpdate()->first();

            $maxUrut = self::getMaxSequence($kategori, (int)$thn, $excludeId);

            // Periksa juga nomor yang sudah ada dalam batch request saat ini
            foreach ($usedBatch as $bNomor) {
                $p = self::parseNomorSurat($bNomor);
                if ($p && $p['tahun'] === (int)$thn && $p['urut'] > $maxUrut) {
                    $maxUrut = $p['urut'];
                }
            }

            $seq = $maxUrut + 1;
            while (true) {
                $candidate = self::formatCandidateNomor($kategori, $seq, $targetDate, $jp);
                $norm = self::normalizeNomor($candidate);

                $inBatch = in_array($candidate, $usedBatch, true) || in_array($norm, array_map([self::class, 'normalizeNomor'], $usedBatch), true);
                $isDupe = self::checkDuplicate($candidate);

                if (!$inBatch && !$isDupe) {
                    $usedBatch[] = $candidate;
                    return $candidate;
                }
                $seq++;
            }
        });
    }

    /**
     * Format kandidat nomor untuk kategori tertentu berdasarkan counter urut integer.
     */
    public static function formatCandidateNomor(string $kategori, int $seq, $tglSurat, string $jp = 'DD'): string
    {
        $bln = Surat::formatBulanRomawi($tglSurat);
        $thn = Surat::formatTahun($tglSurat);

        if ($kategori === 'SPT') {
            $padded = str_pad($seq, 3, '0', STR_PAD_LEFT);
            return "555/KOMINFO-BB/SPT-{$jp}/{$padded}/{$bln}/{$thn}";
        }

        if ($kategori === 'SPPD') {
            $padded = str_pad($seq, 3, '0', STR_PAD_LEFT);
            return "090/KOMINFO-BB/SPPD/{$jp}/{$padded}/{$bln}/{$thn}";
        }

        if ($kategori === 'Surat Telaah') {
            $padded = str_pad($seq, 2, '0', STR_PAD_LEFT);
            return "555/KEP/Telaah.staff/{$padded}/{$bln}/{$thn}";
        }

        if ($kategori === 'Surat SK') {
            $padded = str_pad($seq, 2, '0', STR_PAD_LEFT);
            return "555/KEP/Kominfo.BB/{$padded}/{$bln}/{$thn}";
        }

        // Kategori Lainnya (jika kodenya terisi)
        $lainnya = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->first();
        $kode = ($lainnya && !empty($lainnya->kode)) ? trim($lainnya->kode) : '000';
        $padded = str_pad($seq, 3, '0', STR_PAD_LEFT);
        return "{$kode}/KOMINFO-BB/SURAT/{$padded}/{$bln}/{$thn}";
    }

    /**
     * Pengecekan kombinasi nomor_surat + asal_surat untuk Surat Masuk (Tugas 3.9).
     * Tidak memblokir, hanya memberikan peringatan.
     */
    public static function checkSuratMasukDuplicate(?string $nomorSurat, ?string $asalSurat, ?int $excludeId = null): ?array
    {
        if (empty($nomorSurat) || empty($asalSurat)) {
            return null;
        }

        $normNomor = self::normalizeNomor($nomorSurat);
        $normAsal = strtolower(trim($asalSurat));

        $found = SuratMasuk::whereRaw("LOWER(TRIM(nomor_surat)) = ?", [$normNomor])
            ->whereRaw("LOWER(TRIM(asal_surat)) = ?", [$normAsal])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($found) {
            return [
                'is_duplicate' => true,
                'nomor_surat' => $found->nomor_surat,
                'asal_surat' => $found->asal_surat,
                'tanggal_surat' => $found->tanggal_surat ? date('Y-m-d', strtotime($found->tanggal_surat)) : null,
                'uraian' => $found->uraian,
                'message' => "Perhatian: Surat Masuk dengan Nomor '{$found->nomor_surat}' dari instansi '{$found->asal_surat}' sudah pernah dicatat pada " . ($found->tanggal_surat ? date('d/m/Y', strtotime($found->tanggal_surat)) : '-') . ".",
            ];
        }

        return null;
    }

    /**
     * Helper URL menuju detail surat berdasarkan kategori.
     */
    protected static function buildSuratUrl(string $kategori, string $sourceTable, int $sourceId): string
    {
        if ($sourceTable === 'surat_telaahs') {
            return route('surat.telaah.show', $sourceId);
        }
        if ($sourceTable === 'surat_sks') {
            return route('surat.sk.show', $sourceId);
        }
        if ($sourceTable === 'surat_pegawai') {
            $pivot = DB::table('surat_pegawai')->where('id', $sourceId)->first();
            if ($pivot && $pivot->surat_id) {
                return route('surat.show', $pivot->surat_id);
            }
        }
        return route('surat.show', $sourceId);
    }
}
