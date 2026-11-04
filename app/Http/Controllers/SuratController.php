<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Models\JenisSurat;
use App\Models\SuratTelaah;
use App\Models\SuratSk;
use App\Models\NomorSuratTerpakai;
use App\Http\Requests\StorePegawaiP3kRequest;
use App\Services\SppdNumberingService;
use App\Services\GoogleDriveService;
use App\Services\NomorSuratService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratController extends Controller
{
    public function storePegawaiP3kApi(StorePegawaiP3kRequest $request)
    {
        $pegawai = Pegawai::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'kategori_pegawai' => 'P3K',
            'no_wa' => '-',
            'pangkat_golongan' => '-',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pegawai P3K berhasil ditambahkan ke master data!',
            'pegawai' => [
                'id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan,
                'kategori_pegawai' => 'P3K',
            ]
        ]);
    }
    public function getLetterSuffix(int $index): string
    {
        return Surat::getLetterSuffix($index);
    }

    public function extractSptSequence($sptNomor)
    {
        return Surat::extractSptSequence($sptNomor);
    }

    public function formatBulanRomawi($date = null): string
    {
        return Surat::formatBulanRomawi($date);
    }

    public function formatTahun($date = null): string
    {
        return Surat::formatTahun($date);
    }

    public function formatNomorSpt(string $noUrut, $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSpt($noUrut, $tglSurat, $jenisPenugasan);
    }

    public function formatNomorSppd(string $sptUrut, string $letter, $tglSurat = null, string $jenisPenugasan = 'DD'): string
    {
        return Surat::formatNomorSppd($sptUrut, $letter, $tglSurat, $jenisPenugasan);
    }

    public function determineJenisPenugasan(?string $tujuan = null): string
    {
        return Surat::determineJenisPenugasan($tujuan);
    }

    public function validateManualNomorSurat($jenisSuratId, string $nomor): void
    {
        if ($jenisSuratId == 1) {
            SppdNumberingService::validateManualNomorSppd($nomor);
        } else {
            SppdNumberingService::validateManualNomorSpt($nomor);
        }
    }

    public function generateNextSppdNumber($parentSptNomor = null, $parentSptId = null, $tglSurat = null, $manualNomor = null, array &$usedInCurrentBatch = [], string $jenisPenugasan = 'DD', $excludeId = null)
    {
        return SppdNumberingService::generateNextSppdNumber($parentSptNomor, $parentSptId, $tglSurat, $manualNomor, $usedInCurrentBatch, $jenisPenugasan, $excludeId);
    }

    public function getNextSppdCounter($year = null, bool $lock = false)
    {
        return SppdNumberingService::getNextSppdSequence($year, $lock);
    }

    protected function getNextSptCounter($year = null, bool $lock = false)
    {
        return SppdNumberingService::getNextSptSequence($year, $lock);
    }

    protected function generateUniqueNomorSurat($jenisSuratId, $tglSurat, $manualNomor = null, $excludeId = null, $parentSptNomor = null, $parentId = null, array &$usedBatch = [], $tujuan = null)
    {
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        if ($jenisSuratId == 1) {
            return SppdNumberingService::generateNextSppdNumber($parentSptNomor, $parentId, $tglSurat, $manualNomor, $usedBatch, $jenisPenugasan, $excludeId);
        }

        return SppdNumberingService::generateNextSptNumber($tglSurat, $manualNomor, $usedBatch, $jenisPenugasan, $excludeId);
    }

    public function index(Request $request)
    {
        $totalSpt = Surat::where(function($q) {
            $q->where('jenis_surat_id', 2)
              ->orWhereNull('jenis_surat_id')
              ->orWhere('nomor_surat', 'like', '555/%');
        })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '')->count();

        $totalSppd = DB::table('surat_pegawai')->whereNotNull('nomor_sppd')->where('nomor_sppd', '!=', '')->count();
        
        $totalBulanIni = Surat::whereMonth('tgl_surat', now()->month)
                            ->whereYear('tgl_surat', now()->year)
                            ->count()
                        + SuratTelaah::whereMonth('tanggal_telaah', now()->month)
                            ->whereYear('tanggal_telaah', now()->year)
                            ->count()
                        + SuratSk::whereMonth('tanggal_sk', now()->month)
                            ->whereYear('tanggal_sk', now()->year)
                            ->count();

        $unifiedCollection = $this->getUnifiedSuratKeluar($request);

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $currentPageItems = $unifiedCollection->slice(($page - 1) * $perPage, $perPage)->values();

        $surats = new LengthAwarePaginator(
            $currentPageItems,
            $unifiedCollection->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        $jenisSurats = JenisSurat::all();
        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->orderByHierarki()->get();
        $nextSppdCounter = $this->getNextSppdCounter();

        // Ambil daftar SPT aktif beserta metadata penomoran SPPD
        $sptList = SppdNumberingService::getSptMetadataList();
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        return view('surat.index', compact('surats', 'totalSpt', 'totalSppd', 'totalBulanIni', 'allPegawais', 'nextSppdCounter', 'sptList', 'seriesSummary', 'jenisSurats'));
    }

    /**
     * Helper untuk menyusun baris stdClass dengan set properti seragam untuk semua kategori (Tugas 1 & 4).
     */
    protected function formatUnifiedItem(array $data): object
    {
        $defaults = [
            'id' => null,
            'source_type' => 'spt',
            'kategori' => 'SPT',
            'kategori_badge' => 'SPT',
            'kategori_color' => 'bg-blue-100 text-primary border border-blue-200',
            'jenis_surat_id' => null,
            'nomor_surat' => '-',
            'tgl_surat' => null,
            'perihal' => null,
            'uraian' => null,
            'tujuan' => '-',
            'pegawais' => collect([]),
            'children' => collect([]),
            'has_sppd' => 0,
            'direct_sppds' => [],
            'child_sppds' => [],
            'parent' => null,
            'parent_id' => null,
            'spt_induk_manual' => null,
            'keterangan' => null,
            'status' => 'Terbit',
            'link_google_drive' => null,
            'google_drive_url' => null,
            'file_name' => null,
            'file_path' => null,
            'google_drive_file_id' => null,
            'drive_upload_status' => 'none',
            'show_route' => null,
            'edit_route' => null,
            'delete_route' => null,
            'model' => null,
        ];

        return (object) array_merge($defaults, $data);
    }

    /**
     * Menyatukan seluruh data Surat Keluar (SPT, SPPD, Surat Telaah, Surat SK, Lainnya)
     * ke dalam satu koleksi standar untuk tabel dan ekspor rekap PDF (Tugas 1 & Tugas 4).
     */
    public function getUnifiedSuratKeluar(Request $request): \Illuminate\Support\Collection
    {
        $search = trim($request->get('search', ''));
        $filterJenis = trim($request->get('filter_jenis', $request->get('jenis', '')));
        $year = $request->get('year');
        $month = $request->get('month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $lainnyaId = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->value('id');

        $includeSurats = empty($filterJenis) || in_array($filterJenis, ['SPT', 'SPPD', 'Lainnya']);
        $includeTelaah = empty($filterJenis) || $filterJenis === 'Surat Telaah';
        $includeSk = empty($filterJenis) || $filterJenis === 'Surat SK';

        $unified = collect();

        // 1. Ambil dari tabel surats
        if ($includeSurats) {
            $suratQuery = Surat::with(['jenisSurat', 'pegawais', 'parent', 'children.pegawais'])
                ->whereNull('parent_id')
                ->when($search, function($query) use ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('nomor_surat', 'like', "%{$search}%")
                            ->orWhere('tujuan', 'like', "%{$search}%")
                            ->orWhere('uraian', 'like', "%{$search}%")
                            ->orWhere('keterangan', 'like', "%{$search}%")
                            ->orWhereHas('pegawais', function($pq) use ($search) {
                                $pq->where('nama', 'like', "%{$search}%")
                                   ->orWhere('nip', 'like', "%{$search}%")
                                   ->orWhere('surat_pegawai.nomor_sppd', 'like', "%{$search}%");
                            })
                            ->orWhereHas('children', function($cq) use ($search) {
                                $cq->where('nomor_surat', 'like', "%{$search}%")
                                   ->orWhere('uraian', 'like', "%{$search}%")
                                   ->orWhere('tujuan', 'like', "%{$search}%")
                                   ->orWhere('keterangan', 'like', "%{$search}%")
                                   ->orWhereHas('pegawais', function($cpq) use ($search) {
                                       $cpq->where('nama', 'like', "%{$search}%")
                                          ->orWhere('nip', 'like', "%{$search}%")
                                          ->orWhere('surat_pegawai.nomor_sppd', 'like', "%{$search}%");
                                   });
                            });
                    });
                })
                ->when($filterJenis, function($query) use ($filterJenis, $lainnyaId) {
                    if ($filterJenis === 'SPT') {
                        $query->where(function($q) use ($lainnyaId) {
                            $q->where('jenis_surat_id', 2)
                              ->orWhereNull('jenis_surat_id')
                              ->orWhere('nomor_surat', 'like', '555/%');
                        })->where('jenis_surat_id', '!=', $lainnyaId ?? -1)
                          ->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
                    } elseif ($filterJenis === 'SPPD') {
                        $query->where(function($q) {
                            $q->where(function($sq) {
                                $sq->where('has_sppd', 1)
                                   ->whereHas('pegawais', function($pq) {
                                       $pq->whereNotNull('surat_pegawai.nomor_sppd')
                                          ->where('surat_pegawai.nomor_sppd', '!=', '');
                                   });
                            })->orWhereHas('children', function($cq) {
                                $cq->whereHas('pegawais', function($cpq) {
                                    $cpq->whereNotNull('surat_pegawai.nomor_sppd')
                                       ->where('surat_pegawai.nomor_sppd', '!=', '');
                                });
                            })->orWhere('jenis_surat_id', 1)
                              ->orWhere('nomor_surat', 'like', '090/%');
                        });
                    } elseif ($filterJenis === 'Lainnya') {
                        $query->where('jenis_surat_id', $lainnyaId);
                    }
                })
                ->when($year, function($query) use ($year) {
                    $query->whereYear('tgl_surat', $year);
                })
                ->when($month, function($query) use ($month) {
                    $query->whereMonth('tgl_surat', $month);
                })
                ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('tgl_surat', [$startDate, $endDate]);
                })
                ->when($startDate && !$endDate, function($query) use ($startDate) {
                    $query->whereDate('tgl_surat', '>=', $startDate);
                })
                ->when(!$startDate && $endDate, function($query) use ($endDate) {
                    $query->whereDate('tgl_surat', '<=', $endDate);
                });

            foreach ($suratQuery->get() as $s) {
                $isSppd = ($s->jenis_surat_id == 1 || str_starts_with($s->nomor_surat, '090/'));
                $isLainnya = ($lainnyaId && $s->jenis_surat_id == $lainnyaId);
                $kat = $isLainnya ? 'Lainnya' : ($isSppd ? 'SPPD' : 'SPT');

                $directSppds = [];
                if ($s->has_sppd && $s->pegawais) {
                    foreach ($s->pegawais as $p) {
                        if (!empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-') {
                            $directSppds[] = [
                                'nomor' => $p->pivot->nomor_sppd,
                                'nama' => $p->nama,
                                'url' => route('surat.show', $s->id),
                                'is_child' => false,
                            ];
                        }
                    }
                }

                $childSppds = [];
                if ($s->children && $s->children->count() > 0) {
                    foreach ($s->children as $child) {
                        if ($child->pegawais && $child->pegawais->count() > 0) {
                            foreach ($child->pegawais as $cp) {
                                $cNomor = $cp->pivot->nomor_sppd ?: $child->nomor_surat;
                                if (!empty($cNomor) && $cNomor !== '-') {
                                    $childSppds[] = [
                                        'nomor' => $cNomor,
                                        'nama' => $cp->nama,
                                        'url' => route('surat.show', $child->id),
                                        'is_child' => true,
                                    ];
                                }
                            }
                        } else {
                            $childSppds[] = [
                                'nomor' => $child->nomor_surat,
                                'nama' => '-',
                                'url' => route('surat.show', $child->id),
                                'is_child' => true,
                            ];
                        }
                    }
                }

                $sourceType = $isLainnya ? 'lainnya' : ($isSppd ? 'sppd' : 'spt');
                $kategoriBadge = $kat;
                $kategoriColor = $isLainnya 
                    ? 'bg-purple-100 text-purple-800 border border-purple-200' 
                    : ($isSppd ? 'bg-pink-100 text-pink-800 border border-pink-200' : 'bg-blue-100 text-primary border border-blue-200');

                // Kolom Google Drive di surats: link_google_drive & google_drive_url
                $driveUrl = $s->google_drive_url ?: $s->link_google_drive;

                $unified->push($this->formatUnifiedItem([
                    'id' => $s->id,
                    'source_type' => $sourceType,
                    'kategori' => $kat,
                    'kategori_badge' => $kategoriBadge,
                    'kategori_color' => $kategoriColor,
                    'jenis_surat_id' => $s->jenis_surat_id,
                    'nomor_surat' => $s->nomor_surat,
                    'tgl_surat' => $s->tgl_surat,
                    'perihal' => $s->perihal,
                    'uraian' => $s->uraian ?: $s->perihal,
                    'tujuan' => $s->tujuan,
                    'pegawais' => $s->pegawais,
                    'children' => $s->children,
                    'has_sppd' => $s->has_sppd,
                    'direct_sppds' => $directSppds,
                    'child_sppds' => $childSppds,
                    'parent' => $s->parent,
                    'parent_id' => $s->parent_id,
                    'spt_induk_manual' => $s->spt_induk_manual,
                    'keterangan' => $s->keterangan,
                    'status' => $s->status ?? 'Terbit',
                    'link_google_drive' => $s->link_google_drive,
                    'google_drive_url' => $driveUrl,
                    'file_name' => $s->file_name,
                    'file_path' => $s->file_path,
                    'google_drive_file_id' => $s->google_drive_file_id,
                    'drive_upload_status' => $s->drive_upload_status ?? 'none',
                    'show_route' => route('surat.show', $s->id),
                    'edit_route' => route('surat.edit', $s->id),
                    'delete_route' => route('surat.destroy', $s->id),
                    'model' => $s,
                ]));
            }
        }

        // 2. Ambil dari tabel surat_telaahs
        if ($includeTelaah) {
            $telaahQuery = SuratTelaah::with(['pegawais', 'spt'])
                ->when($search, function($query) use ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('nomor_telaah', 'like', "%{$search}%")
                            ->orWhere('uraian', 'like', "%{$search}%")
                            ->orWhere('tujuan', 'like', "%{$search}%")
                            ->orWhere('keterangan', 'like', "%{$search}%")
                            ->orWhereHas('pegawais', function($pq) use ($search) {
                                $pq->where('nama', 'like', "%{$search}%")
                                   ->orWhere('nip', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($year, function($query) use ($year) {
                    $query->whereYear('tanggal_telaah', $year);
                })
                ->when($month, function($query) use ($month) {
                    $query->whereMonth('tanggal_telaah', $month);
                })
                ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_telaah', [$startDate, $endDate]);
                })
                ->when($startDate && !$endDate, function($query) use ($startDate) {
                    $query->whereDate('tanggal_telaah', '>=', $startDate);
                })
                ->when(!$startDate && $endDate, function($query) use ($endDate) {
                    $query->whereDate('tanggal_telaah', '<=', $endDate);
                });

            foreach ($telaahQuery->get() as $st) {
                // Kolom Google Drive di surat_telaahs: link_google_drive
                $driveUrl = $st->link_google_drive;

                $unified->push($this->formatUnifiedItem([
                    'id' => $st->id,
                    'source_type' => 'telaah',
                    'kategori' => 'Surat Telaah',
                    'kategori_badge' => 'Surat Telaah',
                    'kategori_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                    'jenis_surat_id' => null,
                    'nomor_surat' => $st->nomor_telaah,
                    'tgl_surat' => $st->tanggal_telaah ? $st->tanggal_telaah->format('Y-m-d') : null,
                    'perihal' => null,
                    'uraian' => $st->uraian,
                    'tujuan' => $st->tujuan,
                    'pegawais' => $st->pegawais,
                    'children' => collect([]),
                    'has_sppd' => 0,
                    'direct_sppds' => [],
                    'child_sppds' => [],
                    'parent' => $st->spt,
                    'parent_id' => $st->spt_id,
                    'spt_induk_manual' => null,
                    'keterangan' => $st->keterangan,
                    'status' => 'Terbit',
                    'link_google_drive' => $st->link_google_drive,
                    'google_drive_url' => $driveUrl,
                    'file_name' => null,
                    'file_path' => null,
                    'google_drive_file_id' => null,
                    'drive_upload_status' => 'none',
                    'show_route' => route('surat.telaah.show', $st->id),
                    'edit_route' => route('surat.telaah.edit', $st->id),
                    'delete_route' => route('surat.telaah.destroy', $st->id),
                    'model' => $st,
                ]));
            }
        }

        // 3. Ambil dari tabel surat_sks
        if ($includeSk) {
            $skQuery = SuratSk::query()
                ->when($search, function($query) use ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('nomor_sk', 'like', "%{$search}%")
                            ->orWhere('tentang', 'like', "%{$search}%")
                            ->orWhere('keterangan', 'like', "%{$search}%");
                    });
                })
                ->when($year, function($query) use ($year) {
                    $query->whereYear('tanggal_sk', $year);
                })
                ->when($month, function($query) use ($month) {
                    $query->whereMonth('tanggal_sk', $month);
                })
                ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_sk', [$startDate, $endDate]);
                })
                ->when($startDate && !$endDate, function($query) use ($startDate) {
                    $query->whereDate('tanggal_sk', '>=', $startDate);
                })
                ->when(!$startDate && $endDate, function($query) use ($endDate) {
                    $query->whereDate('tanggal_sk', '<=', $endDate);
                });

            foreach ($skQuery->get() as $sk) {
                // Kolom Google Drive di surat_sks: link_google_drive
                $driveUrl = $sk->link_google_drive;

                $unified->push($this->formatUnifiedItem([
                    'id' => $sk->id,
                    'source_type' => 'sk',
                    'kategori' => 'Surat SK',
                    'kategori_badge' => 'Surat SK',
                    'kategori_color' => 'bg-violet-100 text-violet-800 border border-violet-200',
                    'jenis_surat_id' => null,
                    'nomor_surat' => $sk->nomor_sk,
                    'tgl_surat' => $sk->tanggal_sk ? $sk->tanggal_sk->format('Y-m-d') : null,
                    'perihal' => null,
                    'uraian' => $sk->tentang,
                    'tujuan' => '-',
                    'pegawais' => collect([]),
                    'children' => collect([]),
                    'has_sppd' => 0,
                    'direct_sppds' => [],
                    'child_sppds' => [],
                    'parent' => null,
                    'parent_id' => null,
                    'spt_induk_manual' => null,
                    'keterangan' => $sk->keterangan,
                    'status' => 'Terbit',
                    'link_google_drive' => $sk->link_google_drive,
                    'google_drive_url' => $driveUrl,
                    'file_name' => $sk->file_sk,
                    'file_path' => $sk->file_sk ? ('storage/' . $sk->file_sk) : null,
                    'google_drive_file_id' => null,
                    'drive_upload_status' => 'none',
                    'show_route' => route('surat.sk.show', $sk->id),
                    'edit_route' => route('surat.sk.edit', $sk->id),
                    'delete_route' => route('surat.sk.destroy', $sk->id),
                    'model' => $sk,
                ]));
            }
        }

        // Urutkan berdasarkan tanggal surat descending, kemudian id descending
        return $unified->sortByDesc(function($item) {
            return ($item->tgl_surat ?? '1970-01-01') . sprintf('%08d', $item->id);
        })->values();
    }

    public function getNextSppdCounterApi()
    {
        return response()->json([
            'next_counter' => $this->getNextSppdCounter()
        ]);
    }

    public function checkBackdateApi(Request $request)
    {
        $series = $request->get('series', 'SPT');
        if ($request->filled('jenis_surat_id')) {
            $series = ((int)$request->get('jenis_surat_id') === 1) ? 'SPPD' : 'SPT';
        }
        $tanggal = $request->get('tgl_surat', $request->get('tanggal', date('Y-m-d')));
        $jenisPenugasan = $request->get('jenis_penugasan', 'DD');
        $excludeId = $request->get('exclude_id');

        $eval = SppdNumberingService::checkBackdate($series, $tanggal, $jenisPenugasan, [], $excludeId);

        return response()->json($eval);
    }

    /**
     * Endpoint API cek backdate untuk kategori dinamis (SPT, SPPD, Surat Telaah, Surat SK, Lainnya) (Tugas 2.2).
     */
    public function checkBackdateKategoriApi(Request $request)
    {
        $kategori = $request->get('kategori', 'SPT');
        $tglSurat = $request->get('tgl_surat', date('Y-m-d'));
        $excludeId = $request->filled('exclude_id') ? (int)$request->get('exclude_id') : null;

        $result = NomorSuratService::isBackdate($kategori, $tglSurat, $excludeId);

        $year = (int)date('Y', strtotime($tglSurat));
        $nextSeq = NomorSuratService::getNextSequenceNumber($kategori, $year, $excludeId);
        $padded = str_pad($nextSeq, ($kategori === 'Surat Telaah' || $kategori === 'Surat SK' ? 2 : 3), '0', STR_PAD_LEFT);
        $result['next_seq'] = $padded;
        $result['preview_nomor'] = NomorSuratService::formatCandidateNomor($kategori, $nextSeq, $tglSurat);

        return response()->json($result);
    }

    /**
     * Endpoint API cek duplikat nomor surat lintas seluruh kategori surat keluar (Tugas 3).
     */
    public function checkNomorDuplikatApi(Request $request)
    {
        $nomor = $request->get('nomor');
        $excludeTable = $request->get('exclude_table');
        $excludeId = $request->filled('exclude_id') ? (int)$request->get('exclude_id') : null;

        $duplicate = NomorSuratService::checkDuplicate($nomor, $excludeTable, $excludeId);

        if ($duplicate) {
            $namaKat = $duplicate['kategori'];
            $tgl = $duplicate['tgl_surat'] ? date('d/m/Y', strtotime($duplicate['tgl_surat'])) : '-';
            return response()->json([
                'is_duplicate' => true,
                'message' => "Nomor surat ini sudah terdaftar pada {$namaKat} tanggal {$tgl}.",
                'existing' => $duplicate,
            ]);
        }

        return response()->json([
            'is_duplicate' => false,
            'message' => 'Nomor surat tersedia.',
        ]);
    }

    /**
     * Halaman form pembuatan Surat Keluar kategori 'Lainnya' (Tugas 1 poin 5).
     */
    public function createLainnya(Request $request)
    {
        $jenisSurat = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->first();
        $kodeTersedia = $jenisSurat && !empty($jenisSurat->kode);

        return view('surat.create-lainnya', compact('jenisSurat', 'kodeTersedia'));
    }

    /**
     * Simpan Surat Keluar kategori 'Lainnya' (Tugas 1 poin 5).
     */
    public function storeLainnya(Request $request, GoogleDriveService $driveService)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:150',
            'tgl_surat' => 'required|date',
            'uraian' => 'required|string',
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
        ], [
            'nomor_surat.required' => 'Nomor Surat wajib diisi.',
            'nomor_surat.max' => 'Nomor Surat maksimal 150 karakter.',
            'tgl_surat.required' => 'Tanggal Surat wajib diisi.',
            'uraian.required' => 'Uraian / Isi Ringkas Surat wajib diisi.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid.',
        ]);

        $nomor = trim($request->nomor_surat);

        // Cek duplikat lintas kategori
        $duplicate = NomorSuratService::checkDuplicate($nomor);
        if ($duplicate) {
            return back()->withInput()->withErrors([
                'nomor_surat' => "Nomor surat '{$nomor}' sudah digunakan pada {$duplicate['kategori']} tanggal " . ($duplicate['tgl_surat'] ? date('d/m/Y', strtotime($duplicate['tgl_surat'])) : '-') . "."
            ]);
        }

        $lainnyaJenis = JenisSurat::where('nama', 'Lainnya')->orWhere('nama_jenis', 'Lainnya')->first();
        $jenisSuratId = $lainnyaJenis ? $lainnyaJenis->id : null;

        $surat = DB::transaction(function() use ($request, $nomor, $jenisSuratId) {
            $surat = Surat::create([
                'jenis_surat_id' => $jenisSuratId,
                'nomor_surat' => $nomor,
                'tgl_surat' => $request->tgl_surat,
                'uraian' => $request->uraian,
                'tujuan' => '-',
                'keterangan' => $request->keterangan,
                'link_google_drive' => $request->link_google_drive,
                'has_sppd' => 0,
                'status' => 'Terbit',
                'created_by' => auth()->id() ?? 1,
            ]);

            NomorSuratService::registerNomor(
                $nomor,
                'Lainnya',
                'surats',
                $surat->id,
                $surat->tgl_surat
            );

            return $surat;
        });

        return redirect()->route('surat.index')->with('success', "Surat Keluar Lainnya ({$nomor}) berhasil diterbitkan!");
    }

    public function create(Request $request)
    {
        // Bersihkan session jika bukan dari tombol kembali
        if (!$request->hasHeader('referer') || !str_contains(url()->previous(), 'step-2')) {
            session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_uraian', 's_keterangan', 's_pegawai_id', 's_nomor_surat', 's_mode_nomor', 's_buat_sppd', 's_parent_id', 's_spt_induk_manual']);
        }

        $jenisSuratId = 2; 
        $jenisSurats = \App\Models\JenisSurat::where('id', 2)->get(); 

        $tglSurat = session('s_tgl_surat', date('Y-m-d'));
        $eval = SppdNumberingService::checkBackdate('SPT', $tglSurat);
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        $nomorUrutSpt = $eval['next_seq'];
        $nomorUrut = $nomorUrutSpt;

        return view('surat.create', compact('nomorUrut', 'nomorUrutSpt', 'jenisSuratId', 'jenisSurats', 'eval', 'seriesSummary'));
    }

    public function createStep2(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'tgl_surat' => 'required|date',
            ], [
                'tgl_surat.required' => 'Tanggal Nomor Surat wajib diisi sebelum melanjutkan ke tahap berikutnya.',
                'tgl_surat.date' => 'Format Tanggal Nomor Surat tidak valid.',
            ]);

            $modeNomor = $request->input('mode_nomor', 'otomatis');
            session([
                's_tgl_surat' => $request->tgl_surat,
                's_jenis_surat_id' => $request->jenis_surat_id,
                's_mode_nomor' => $modeNomor,
                's_nomor_surat' => ($modeNomor === 'manual') ? ($request->nomor_surat_manual ?? $request->nomor_surat) : null,
                's_tujuan' => $request->tujuan,
            ]);
        } else {
            if (!session('s_tgl_surat')) {
                return redirect()->route('surat.create')->with('error', 'Silakan tentukan tanggal nomor surat terlebih dahulu.');
            }
        }

        // Saring pegawai agar akun Admin Master, Admin Kasubag, dan Pegawai Biasa tidak ikut terpanggil
        $pegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai', 'Bendahara Barang', 'Pegawai Biasa'])
            ->orderByHierarki()
            ->get();

        return view('surat.create-step-2', compact('pegawais'));
    }

    public function createStep3(Request $request)
    {
        if ($request->isMethod('post')) {
            // Tambahkan validasi agar pegawai_id wajib diisi (minimal 1 personel) dan batasi keterangan maksimal 150 karakter
            $request->validate([
                'pegawai_id' => 'required|array|min:1',
                'keterangan' => 'nullable|string|max:150',
            ], [
                'pegawai_id.required' => 'Pilih minimal 1 pegawai untuk melanjutkan.',
                'pegawai_id.min' => 'Pilih minimal 1 pegawai untuk melanjutkan.',
                'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            ]);

            session([
                's_uraian' => $request->uraian,
                's_keterangan' => $request->keterangan,
                's_pegawai_id' => $request->pegawai_id,
            ]);
        }

        $jenisSuratId = 2;

        $selectedPegawais = collect();
        $pegawaiIds = session('s_pegawai_id', []);
        if (!empty($pegawaiIds)) {
            $selectedPegawais = \App\Models\Pegawai::whereIn('id', $pegawaiIds)->orderByHierarki()->get();
        } else {
            return redirect()->route('surat.create.step2')->with('error', 'Pilih minimal 1 pegawai untuk melanjutkan.');
        }

        $tglSurat = session('s_tgl_surat', now()->format('Y-m-d'));
        $d = new \DateTime($tglSurat);
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        $sppdPreviews = [];
        $batch = [];
        $tujuan = session('s_tujuan');
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        foreach ($selectedPegawais as $pegawai) {
            $sppdPreviews[$pegawai->id] = SppdNumberingService::generateNextSppdNumber(null, null, $tglSurat, null, $batch, $jenisPenugasan);
        }

        $sppdEval = SppdNumberingService::checkBackdate('SPPD', $tglSurat, $jenisPenugasan);
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        return view('surat.create-step-3', compact('selectedPegawais', 'sppdPreviews', 'sppdEval', 'jenisSuratId', 'bln', 'thn', 'seriesSummary'));
    }

    /**
     * SPPD Standalone Wizard - Langkah 1: Informasi Nomor Surat (Tugas A)
     */
    public function createSppd(Request $request)
    {
        // Bersihkan session jika bukan dari tombol kembali
        if (!$request->hasHeader('referer') || !str_contains(url()->previous(), 'sppd/create/step-2')) {
            session()->forget([
                's_sppd_tgl_surat', 's_sppd_parent_id', 's_sppd_spt_induk_manual',
                's_sppd_mode_nomor', 's_sppd_nomor_surat', 's_sppd_tujuan',
                's_sppd_uraian', 's_sppd_keterangan', 's_sppd_pegawai_id'
            ]);
        }

        $jenisSurat = JenisSurat::find(1); // SPPD (kode 090, default otomatis)
        $spts = Surat::where(function($q) {
            $q->where('jenis_surat_id', 2)
              ->orWhereNull('jenis_surat_id')
              ->orWhere('nomor_surat', 'like', '555/%');
        })
        ->whereNotNull('nomor_surat')
        ->where('nomor_surat', '!=', '')
        ->where('status', '!=', 'Draft')
        ->with(['pegawais', 'children.pegawais'])
        ->orderByDesc('tgl_surat')
        ->orderByDesc('id')
        ->get();

        $tglSurat = session('s_sppd_tgl_surat', null);
        $eval = $tglSurat ? SppdNumberingService::checkBackdate('SPPD', $tglSurat) : ['is_backdate' => false, 'notice' => ''];
        $nextSeq = NomorSuratService::getNextSequenceNumber('SPPD', (int)date('Y'));
        $nomorUrutSppd = str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        return view('surat.sppd.create', compact('jenisSurat', 'spts', 'eval', 'nomorUrutSppd', 'seriesSummary'));
    }

    /**
     * SPPD Standalone Wizard - Langkah 2: Personel & Uraian (Tugas A)
     */
    public function createSppdStep2(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'tgl_surat' => 'required|date',
                'parent_id' => 'required_without:spt_induk_manual|nullable|exists:surats,id',
                'spt_induk_manual' => 'required_without:parent_id|nullable|string|max:255',
            ], [
                'tgl_surat.required' => 'Tanggal SPPD wajib diisi.',
                'parent_id.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
                'spt_induk_manual.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
            ]);

            $modeNomor = $request->input('mode_nomor', 'otomatis');
            $prevParentId = session('s_sppd_parent_id');
            if ($request->parent_id && $request->parent_id != $prevParentId) {
                session()->forget('s_sppd_pegawai_id');
            }

            session([
                's_sppd_tgl_surat' => $request->tgl_surat,
                's_sppd_parent_id' => $request->parent_id,
                's_sppd_spt_induk_manual' => $request->spt_induk_manual,
                's_sppd_mode_nomor' => $modeNomor,
                's_sppd_nomor_surat' => ($modeNomor === 'manual') ? ($request->nomor_surat_manual ?? $request->nomor_surat) : null,
                's_sppd_tujuan' => $request->tujuan,
                's_sppd_uraian' => $request->uraian,
            ]);
        } else {
            if (!session('s_sppd_tgl_surat')) {
                return redirect()->route('surat.sppd.create')->with('error', 'Silakan tentukan informasi nomor SPPD terlebih dahulu.');
            }
        }

        $pegawais = Pegawai::whereNotIn('id', [1, 2, 3])->orderByHierarki()->get();
        $parentSpt = session('s_sppd_parent_id') ? Surat::with('pegawais')->find(session('s_sppd_parent_id')) : null;
        if ($parentSpt && $parentSpt->pegawais) {
            $pegawais = $pegawais->merge($parentSpt->pegawais)->unique('id')->values();
        }

        return view('surat.sppd.create-step-2', compact('pegawais', 'parentSpt'));
    }

    /**
     * SPPD Standalone Wizard - Langkah 3: Nomor SPPD per Pegawai (Tugas A)
     */
    public function createSppdStep3(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'pegawai_id' => 'required|array|min:1',
                'tujuan' => 'required|string',
                'uraian' => 'required|string',
                'keterangan' => 'nullable|string|max:150',
            ], [
                'pegawai_id.required' => 'Pilih minimal 1 personil yang ditugaskan.',
                'pegawai_id.min' => 'Pilih minimal 1 personil yang ditugaskan.',
                'tujuan.required' => 'Tujuan perjalanan dinas wajib diisi.',
                'uraian.required' => 'Maksud / Uraian perjalanan dinas wajib diisi.',
                'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            ]);

            session([
                's_sppd_tujuan' => $request->tujuan,
                's_sppd_uraian' => $request->uraian,
                's_sppd_keterangan' => $request->keterangan,
                's_sppd_pegawai_id' => $request->pegawai_id,
            ]);
        }

        $pegawaiIds = session('s_sppd_pegawai_id', []);
        if (empty($pegawaiIds)) {
            return redirect()->route('surat.sppd.create.step2')->with('error', 'Pilih minimal 1 personil yang ditugaskan.');
        }

        $selectedPegawais = Pegawai::whereIn('id', $pegawaiIds)->orderByHierarki()->get();
        $tglSurat = session('s_sppd_tgl_surat', date('Y-m-d'));
        $tujuan = session('s_sppd_tujuan', '-');
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);
        $parentId = session('s_sppd_parent_id');
        $sptIndukManual = session('s_sppd_spt_induk_manual');
        $parentSpt = $parentId ? Surat::find($parentId) : null;
        $parentSptNomor = $parentSpt ? $parentSpt->nomor_surat : $sptIndukManual;

        $sppdPreviews = [];
        $batch = [];
        foreach ($selectedPegawais as $pegawai) {
            $sppdPreviews[$pegawai->id] = SppdNumberingService::generateNextSppdNumber($parentSptNomor, $parentId, $tglSurat, null, $batch, $jenisPenugasan);
        }

        $eval = SppdNumberingService::checkBackdate('SPPD', $tglSurat, $jenisPenugasan);
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        return view('surat.sppd.create-step-3', compact('selectedPegawais', 'sppdPreviews', 'eval', 'seriesSummary', 'parentSpt', 'parentSptNomor'));
    }

    /**
     * Simpan Draft dari Wizard SPPD
     */
    public function storeSppdDraft(Request $request)
    {
        $request->merge([
            'jenis_surat_id' => 1,
            'has_sppd' => 1,
            'tgl_surat' => $request->input('tgl_surat', session('s_sppd_tgl_surat')),
            'parent_id' => $request->input('parent_id', session('s_sppd_parent_id')),
            'spt_induk_manual' => $request->input('spt_induk_manual', session('s_sppd_spt_induk_manual')),
            'mode_nomor' => $request->input('mode_nomor', session('s_sppd_mode_nomor', 'otomatis')),
            'nomor_surat_manual' => $request->input('nomor_surat_manual', session('s_sppd_nomor_surat')),
            'tujuan' => $request->input('tujuan', session('s_sppd_tujuan', '-')),
            'uraian' => $request->input('uraian', session('s_sppd_uraian', 'Perjalanan Dinas')),
            'keterangan' => $request->input('keterangan', session('s_sppd_keterangan')),
            'pegawai_id' => $request->input('pegawai_id', session('s_sppd_pegawai_id', [])),
        ]);

        return $this->storeDraft($request);
    }

    /**
     * Terbitkan SPPD dari Wizard SPPD
     */
    public function storeSppd(Request $request, GoogleDriveService $driveService)
    {
        $request->merge([
            'jenis_surat_id' => 1,
            'has_sppd' => 1,
            'tgl_surat' => $request->input('tgl_surat', session('s_sppd_tgl_surat')),
            'parent_id' => $request->input('parent_id', session('s_sppd_parent_id')),
            'spt_induk_manual' => $request->input('spt_induk_manual', session('s_sppd_spt_induk_manual')),
            'mode_nomor' => $request->input('mode_nomor', session('s_sppd_mode_nomor', 'otomatis')),
            'nomor_surat_manual' => $request->input('nomor_surat_manual', session('s_sppd_nomor_surat')),
            'tujuan' => $request->input('tujuan', session('s_sppd_tujuan', '-')),
            'uraian' => $request->input('uraian', session('s_sppd_uraian', 'Perjalanan Dinas')),
            'keterangan' => $request->input('keterangan', session('s_sppd_keterangan')),
            'pegawai_id' => $request->input('pegawai_id', session('s_sppd_pegawai_id', [])),
        ]);

        return $this->store($request, $driveService);
    }

    public function show($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais', 'parent', 'children.pegawais'])->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }

        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->orderByHierarki()->get();
        $sptUrut = SppdNumberingService::extractSptSequence($surat->nomor_surat);
        $nextSppdCounter = SppdNumberingService::getNextSppdSequence();
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();
        $sppdEval = SppdNumberingService::checkBackdate('SPPD', date('Y-m-d'));

        return view('surat.show', compact('surat', 'allPegawais', 'sptUrut', 'nextSppdCounter', 'seriesSummary', 'sppdEval'));
    }

    public function edit($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais', 'parent'])->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }
        $pegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->orderByHierarki()->get();
        
        // Ambil daftar SPT dari database untuk opsi pilihan SPT Induk jika surat berjenis SPPD
        $sptList = Surat::where(function($q) {
                $q->where('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id')
                  ->orWhere('nomor_surat', 'like', '555/%');
            })
            ->where('id', '!=', $id)
            ->where('status', '!=', 'Draft')
            ->orderByDesc('tgl_surat')
            ->orderByDesc('id')
            ->get(['id', 'nomor_surat', 'tgl_surat', 'uraian', 'tujuan', 'jenis_penugasan']);

        $isSppd = ($surat->jenis_surat_id == 1 || str_starts_with($surat->nomor_surat ?? '', '090/'));
        $series = $isSppd ? 'SPPD' : 'SPT';
        $nomorUrutSurat = $isSppd ? SppdNumberingService::extractSppdSequence($surat->nomor_surat) : SppdNumberingService::extractSptSequence($surat->nomor_surat);
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();
        $eval = SppdNumberingService::checkBackdate($series, $surat->tgl_surat ?? date('Y-m-d'), $surat->jenis_penugasan ?? 'DD');

        return view('surat.edit', compact('surat', 'pegawais', 'sptList', 'nomorUrutSurat', 'seriesSummary', 'eval'));
    }

    public function update(Request $request, $id, ?GoogleDriveService $driveService = null)
    {
        $driveService = $driveService ?: app(GoogleDriveService::class);
        $request->validate([
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
            'file_surat.mimes' => 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_surat.max' => 'Ukuran file surat maksimal 10 MB.',
        ]);

        $surat = Surat::with('pegawais')->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }

        $hasSppd = $request->input('has_sppd');
        $buatSppd = ($hasSppd === '1' || $hasSppd === 1 || $hasSppd === 'ya') ? 1 : 0;

        $tglSurat = $request->input('tgl_surat', $surat->tgl_surat);
        $tujuan = $request->input('tujuan', $surat->tujuan);
        $uraian = $request->input('uraian', $surat->uraian);
        $keterangan = $request->input('keterangan', $surat->keterangan);
        $jenisSuratId = $surat->jenis_surat_id;
        if ($request->filled('jenis_surat')) {
            $jenisName = $request->input('jenis_surat');
            $jenisObj = \App\Models\JenisSurat::where('nama_jenis', $jenisName)->orWhere('kode', $jenisName)->first();
            if ($jenisObj) {
                $jenisSuratId = $jenisObj->id;
            }
        }

        // Tentukan status: jika sebelumnya Draft dan diedit/dilengkapi, perbarui menjadi Terbit
        $status = $request->input('status');
        if (empty($status)) {
            $status = ($surat->status === 'Draft') ? 'Terbit' : ($surat->status ?? 'Terbit');
        }

        $d = new \DateTime($tglSurat ?: date('Y-m-d'));
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        $updatedSurat = DB::transaction(function () use ($request, $surat, $hasSppd, $buatSppd, $tglSurat, $tujuan, $uraian, $keterangan, $jenisSuratId, $status, $bln, $thn) {
            $modeNomor = $request->input('mode_nomor', 'manual');
            $parentId = $surat->parent_id;
            $sptIndukManual = $surat->spt_induk_manual;
            $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

            if ($jenisSuratId == 1) {
                // Rule 4: Jika SPPD SUDAH punya SPT induk, field ini bersifat read-only / tidak dapat diubah lagi
                if (empty($surat->parent_id) && empty($surat->spt_induk_manual)) {
                    if ($request->filled('parent_id')) {
                        $parentCandidate = Surat::where('id', $request->input('parent_id'))
                            ->where(function($q) {
                                $q->where('jenis_surat_id', 2)
                                  ->orWhereNull('jenis_surat_id')
                                  ->orWhere('nomor_surat', 'like', '555/%');
                            })->first();

                        if ($parentCandidate) {
                            $parentId = $parentCandidate->id;
                            $sptIndukManual = null;

                            // Rule 5: Audit log
                            $user = auth()->user();
                            $userName = $user ? $user->name : 'Admin';
                            $userId = $user ? $user->id : 0;
                            \Illuminate\Support\Facades\Log::info("Audit Trail: User ID {$userId} ({$userName}) menghubungkan SPPD legacy #{$surat->id} ({$surat->nomor_surat}) ke SPT Induk #{$parentCandidate->id} ({$parentCandidate->nomor_surat}) pada " . now()->format('Y-m-d H:i:s'));
                        }
                    } elseif ($request->filled('spt_induk_manual')) {
                        $parentId = null;
                        $sptIndukManual = trim($request->input('spt_induk_manual'));

                        $user = auth()->user();
                        $userName = $user ? $user->name : 'Admin';
                        $userId = $user ? $user->id : 0;
                        \Illuminate\Support\Facades\Log::info("Audit Trail: User ID {$userId} ({$userName}) menghubungkan SPPD legacy #{$surat->id} ({$surat->nomor_surat}) ke SPT Manual: {$sptIndukManual} pada " . now()->format('Y-m-d H:i:s'));
                    }
                }
            }

            if ($modeNomor === 'manual' && $request->filled('nomor_surat_manual')) {
                $nomorSurat = $request->input('nomor_surat_manual');
                $this->validateManualNomorSurat($jenisSuratId, $nomorSurat);
            } elseif ($modeNomor === 'otomatis') {
                $dummyBatch = [];
                if ($jenisSuratId == 1) {
                    $parentNomor = $parentId ? (Surat::find($parentId)->nomor_surat ?? $sptIndukManual) : $sptIndukManual;
                    $nomorSurat = $this->generateUniqueNomorSurat(1, $tglSurat, null, $surat->id, $parentNomor, $parentId, $dummyBatch, $tujuan);
                } else {
                    $nomorSurat = $this->generateUniqueNomorSurat(2, $tglSurat, null, $surat->id, null, null, $dummyBatch, $tujuan);
                }
            } else {
                $nomorSurat = $request->input('nomor_surat', $surat->nomor_surat);
            }

            $surat->update([
                'tgl_surat' => $tglSurat,
                'perihal' => null,
                'tujuan' => $tujuan,
                'uraian' => $uraian,
                'keterangan' => $keterangan,
                'link_google_drive' => $request->input('link_google_drive'),
                'nomor_surat' => $nomorSurat,
                'jenis_surat_id' => $jenisSuratId,
                'jenis_penugasan' => $jenisPenugasan,
                'parent_id' => $parentId,
                'spt_induk_manual' => $sptIndukManual,
                'has_sppd' => ($jenisSuratId == 1 ? 1 : $buatSppd),
                'status' => $status,
            ]);

            $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
            $syncData = [];
            $nomorSppdInputs = (array) $request->input('nomor_sppd', []);

            if (!$buatSppd) {
                foreach ($pegawaiIds as $pId) {
                    $syncData[$pId] = [
                        'nomor_sppd' => null,
                    ];
                }
            } else {
                // Ambil nomor SPPD yang sudah ada di database untuk surat ini
                $existingSppdMap = $surat->pegawais->pluck('pivot.nomor_sppd', 'id')->toArray();
                $assignedSppd = [];
                $usedSppdSet = [];

                // 1. Pertahankan nomor SPPD lama yang sudah ada atau input manual yang valid (jika belum duplikat)
                foreach ($pegawaiIds as $pId) {
                    $manualInput = trim($nomorSppdInputs[$pId] ?? '');
                    $existingNum = trim($existingSppdMap[$pId] ?? '');

                    // Prioritaskan nomor existing jika ada, atau manual input jika valid dan bukan placeholder
                    $candidate = ($manualInput && $manualInput !== '-') ? $manualInput : (($existingNum && $existingNum !== '-') ? $existingNum : null);

                    if ($candidate && !in_array($candidate, $usedSppdSet, true)) {
                        $assignedSppd[$pId] = $candidate;
                        $usedSppdSet[] = $candidate;
                    }
                }

                // 2. Untuk pegawai baru yang ditambahkan saat edit atau pegawai yang belum memiliki nomor SPPD unik,
                // generate nomor baru berurutan dengan huruf berikutnya
                $parentNomorForPersonnel = ($jenisSuratId == 1)
                    ? ($parentId ? (Surat::find($parentId)->nomor_surat ?? $sptIndukManual) : $sptIndukManual)
                    : $surat->nomor_surat;
                $parentSuratIdForPersonnel = ($jenisSuratId == 1) ? $parentId : $surat->id;

                foreach ($pegawaiIds as $pId) {
                    if (isset($assignedSppd[$pId])) {
                        $syncData[$pId] = [
                            'nomor_sppd' => $assignedSppd[$pId],
                        ];
                    } else {
                        $newSppd = $this->generateNextSppdNumber(
                            $parentNomorForPersonnel,
                            $parentSuratIdForPersonnel,
                            $tglSurat,
                            null,
                            $usedSppdSet,
                            $jenisPenugasan
                        );

                        $syncData[$pId] = [
                            'nomor_sppd' => $newSppd,
                        ];
                    }
                }
            }

            $surat->pegawais()->sync($syncData);

            return $surat;
        });

        if ($request->hasFile('file_surat') && $updatedSurat->jenis_surat_id == 2) {
            $this->handleSuratFileUpload($request, $updatedSurat, $driveService);
        }

        $suratType = ($updatedSurat->jenis_surat_id == 1) ? 'sppd' : 'spt';
        \App\Services\NotificationService::notifyPenugasanSurat($updatedSurat, array_keys($syncData ?? []), $suratType);

        return redirect()->route('surat.index')->with('success', 'Perubahan data surat berhasil disimpan!');
    }

    public function hubungkanSpt(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'required|exists:surats,id',
        ], [
            'parent_id.required' => 'Pilih SPT Induk yang valid.',
            'parent_id.exists' => 'SPT Induk yang dipilih tidak ditemukan di database.',
        ]);

        $surat = Surat::find($id);
        if (!$surat) {
            return response()->json([
                'success' => false,
                'message' => 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.'
            ], 404);
        }

        $isSppd = ($surat->jenis_surat_id == 1 || str_starts_with($surat->nomor_surat, '090/'));
        if (!$isSppd) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokumen berjenis SPPD yang dapat dihubungkan ke SPT Induk.'
            ], 422);
        }

        // Rule 4: Jika SPPD SUDAH punya SPT induk (relasi sudah terhubung), tidak bisa diubah lagi
        if (!empty($surat->parent_id)) {
            return response()->json([
                'success' => false,
                'message' => 'SPPD ini sudah memiliki relasi SPT Induk yang terhubung dan tidak dapat diubah.'
            ], 422);
        }

        $sptInduk = Surat::where('id', $request->parent_id)
            ->where(function($q) {
                $q->where('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id')
                  ->orWhere('nomor_surat', 'like', '555/%');
            })->first();

        if (!$sptInduk) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen yang dipilih bukan merupakan Surat Perintah Tugas (SPT) yang valid.'
            ], 422);
        }

        // Simpan relasi
        $surat->parent_id = $sptInduk->id;
        $surat->spt_induk_manual = null;
        $surat->save();

        // Rule 5: Audit Trail
        $user = auth()->user();
        $userName = $user ? $user->name : 'Admin';
        $userId = $user ? $user->id : 0;
        \Illuminate\Support\Facades\Log::info("Audit Trail: User ID {$userId} ({$userName}) menghubungkan SPPD legacy #{$surat->id} ({$surat->nomor_surat}) ke SPT Induk #{$sptInduk->id} ({$sptInduk->nomor_surat}) pada " . now()->format('Y-m-d H:i:s'));

        return response()->json([
            'success' => true,
            'message' => "SPPD {$surat->nomor_surat} berhasil dihubungkan ke SPT Induk {$sptInduk->nomor_surat}.",
            'parent' => [
                'id' => $sptInduk->id,
                'nomor_surat' => $sptInduk->nomor_surat,
                'tgl_surat' => \Carbon\Carbon::parse($sptInduk->tgl_surat)->translatedFormat('d F Y'),
                'uraian' => $sptInduk->uraian ?: $sptInduk->perihal,
                'tujuan' => $sptInduk->tujuan,
            ]
        ]);
    }

    public function downloadPdf($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }
        $isPdf = true; 
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.print', compact('surat', 'isPdf'));
        return $pdf->download('Surat_'.$id.'.pdf');
    }

    public function print($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }
        $isPdf = false; 
        return view('surat.print', compact('surat', 'isPdf'));
    }

    public function storeSppdChild(Request $request, $id, GoogleDriveService $driveService)
    {
        $request->validate([
            'tgl_surat' => 'required|date',
            'pegawai_id' => 'required|array|min:1',
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
        ], [
            'tgl_surat.required' => 'Tanggal SPPD wajib diisi.',
            'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
        ]);

        $parentSpt = Surat::findOrFail($id);

        $tglSurat = $request->tgl_surat;
        $tujuan = $request->input('tujuan', $parentSpt->tujuan);
        $uraian = $request->input('uraian', $parentSpt->uraian ?: 'Perjalanan Dinas');
        $keterangan = $request->input('keterangan');
        $linkGoogleDrive = $request->input('link_google_drive');
        $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        $childSurat = DB::transaction(function () use ($parentSpt, $tglSurat, $tujuan, $uraian, $keterangan, $linkGoogleDrive, $pegawaiIds, $jenisPenugasan) {
            $usedSppdBatch = [];

            // Generate nomor SPPD untuk dokumen child
            $firstSppdNomor = SppdNumberingService::generateNextSppdNumber(
                $parentSpt->nomor_surat,
                $parentSpt->id,
                $tglSurat,
                null,
                $usedSppdBatch,
                $jenisPenugasan
            );

            $childSurat = Surat::create([
                'jenis_surat_id' => 1,
                'jenis_penugasan' => $jenisPenugasan,
                'parent_id' => $parentSpt->id,
                'spt_induk_manual' => null,
                'nomor_surat' => $firstSppdNomor,
                'perihal' => null,
                'tgl_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'uraian' => $uraian,
                'keterangan' => $keterangan,
                'link_google_drive' => $linkGoogleDrive,
                'has_sppd' => 1,
                'status' => 'Terbit',
                'created_by' => auth()->id() ?? 1,
            ]);

            $attachData = [];
            foreach ($pegawaiIds as $index => $pId) {
                if ($index === 0) {
                    $nomorSppd = $firstSppdNomor;
                } else {
                    $nomorSppd = SppdNumberingService::generateNextSppdNumber(
                        $parentSpt->nomor_surat,
                        $parentSpt->id,
                        $tglSurat,
                        null,
                        $usedSppdBatch,
                        $jenisPenugasan
                    );
                }
                $attachData[$pId] = [
                    'nomor_sppd' => $nomorSppd,
                ];
            }
            $childSurat->pegawais()->sync($attachData);

            NomorSuratService::registerNomor(
                $childSurat->nomor_surat,
                'SPPD',
                'surats',
                $childSurat->id,
                $childSurat->tgl_surat
            );

            $pivotRows = DB::table('surat_pegawai')
                ->where('surat_id', $childSurat->id)
                ->whereNotNull('nomor_sppd')
                ->where('nomor_sppd', '!=', '')
                ->where('nomor_sppd', '!=', '-')
                ->get();
            foreach ($pivotRows as $pRow) {
                NomorSuratService::registerNomor(
                    $pRow->nomor_sppd,
                    'SPPD',
                    'surat_pegawai',
                    $pRow->id,
                    $childSurat->tgl_surat
                );
            }

            // Pastikan SPT induk ter-flag has_sppd = 1
            if (!$parentSpt->has_sppd) {
                $parentSpt->has_sppd = 1;
                $parentSpt->save();
            }

            return $childSurat;
        });

        \App\Services\NotificationService::notifyPenugasanSurat($childSurat, $pegawaiIds, 'sppd');

        return redirect()->route('surat.show', $parentSpt->id)->with('success', 'SPPD baru (' . $childSurat->nomor_surat . ') berhasil ditambahkan ke SPT ini!');
    }

    public function storeSppdStandalone(Request $request, GoogleDriveService $driveService)
    {
        $request->validate([
            'parent_id' => 'required_without:spt_induk_manual|nullable|exists:surats,id',
            'spt_induk_manual' => 'required_without:parent_id|nullable|string|max:255',
            'tgl_surat' => 'required|date',
            'pegawai_id' => 'required|array|min:1',
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
        ], [
            'parent_id.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
            'spt_induk_manual.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
            'tgl_surat.required' => 'Tanggal SPPD wajib diisi.',
            'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
        ]);

        $parentId = $request->filled('parent_id') ? $request->parent_id : null;
        $sptIndukManual = $request->filled('spt_induk_manual') ? trim($request->spt_induk_manual) : null;
        $parentSpt = $parentId ? Surat::find($parentId) : null;
        $parentSptNomor = $parentSpt ? $parentSpt->nomor_surat : $sptIndukManual;

        $tglSurat = $request->tgl_surat;
        $tujuan = $request->input('tujuan', ($parentSpt ? $parentSpt->tujuan : '-'));
        $uraian = $request->input('uraian', ($parentSpt ? $parentSpt->uraian : 'Perjalanan Dinas'));
        $keterangan = $request->input('keterangan');
        $linkGoogleDrive = $request->input('link_google_drive');
        $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        $childSurat = DB::transaction(function () use ($parentId, $parentSpt, $parentSptNomor, $sptIndukManual, $tglSurat, $tujuan, $uraian, $keterangan, $linkGoogleDrive, $pegawaiIds, $jenisPenugasan) {
            $usedSppdBatch = [];

            $firstSppdNomor = SppdNumberingService::generateNextSppdNumber(
                $parentSptNomor,
                $parentId,
                $tglSurat,
                null,
                $usedSppdBatch,
                $jenisPenugasan
            );

            $childSurat = Surat::create([
                'jenis_surat_id' => 1,
                'jenis_penugasan' => $jenisPenugasan,
                'parent_id' => $parentId,
                'spt_induk_manual' => $sptIndukManual,
                'nomor_surat' => $firstSppdNomor,
                'perihal' => null,
                'tgl_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'uraian' => $uraian,
                'keterangan' => $keterangan,
                'link_google_drive' => $linkGoogleDrive,
                'has_sppd' => 1,
                'status' => 'Terbit',
                'created_by' => auth()->id() ?? 1,
            ]);

            $attachData = [];
            foreach ($pegawaiIds as $index => $pId) {
                if ($index === 0) {
                    $nomorSppd = $firstSppdNomor;
                } else {
                    $nomorSppd = SppdNumberingService::generateNextSppdNumber(
                        $parentSptNomor,
                        $parentId,
                        $tglSurat,
                        null,
                        $usedSppdBatch,
                        $jenisPenugasan
                    );
                }
                $attachData[$pId] = [
                    'nomor_sppd' => $nomorSppd,
                ];
            }
            $childSurat->pegawais()->sync($attachData);

            NomorSuratService::registerNomor(
                $childSurat->nomor_surat,
                'SPPD',
                'surats',
                $childSurat->id,
                $childSurat->tgl_surat
            );

            $pivotRows = DB::table('surat_pegawai')
                ->where('surat_id', $childSurat->id)
                ->whereNotNull('nomor_sppd')
                ->where('nomor_sppd', '!=', '')
                ->where('nomor_sppd', '!=', '-')
                ->get();
            foreach ($pivotRows as $pRow) {
                NomorSuratService::registerNomor(
                    $pRow->nomor_sppd,
                    'SPPD',
                    'surat_pegawai',
                    $pRow->id,
                    $childSurat->tgl_surat
                );
            }

            if ($parentSpt && !$parentSpt->has_sppd) {
                $parentSpt->has_sppd = 1;
                $parentSpt->save();
            }

            return $childSurat;
        });

        \App\Services\NotificationService::notifyPenugasanSurat($childSurat, $pegawaiIds, 'sppd');

        return redirect()->route('surat.index')->with('success', 'Surat Perintah Perjalanan Dinas (SPPD) ' . $childSurat->nomor_surat . ' berhasil diterbitkan!');
    }

    public function store(Request $request, GoogleDriveService $driveService)
    {
        $jenisSuratId = (int) session('s_jenis_surat_id', $request->jenis_surat_id ?? 2);

        $rules = [
            'keterangan' => 'nullable|string|max:150',
            'link_google_drive' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
            ],
            'tgl_surat' => 'nullable|date',
        ];
        $messages = [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
            'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
        ];

        if ($jenisSuratId !== 1) {
            $rules['file_surat'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $messages['file_surat.mimes'] = 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.';
            $messages['file_surat.max'] = 'Ukuran file surat maksimal 10 MB.';
        }

        if ($jenisSuratId === 1) {
            $rules['parent_id'] = 'required_without:spt_induk_manual|nullable|exists:surats,id';
            $rules['spt_induk_manual'] = 'required_without:parent_id|nullable|string|max:255';
            $messages['parent_id.required_without'] = 'SPT Induk wajib dipilih dari daftar atau diketik nomornya secara manual.';
            $messages['spt_induk_manual.required_without'] = 'SPT Induk wajib dipilih dari daftar atau diketik nomornya secara manual.';
            $messages['parent_id.exists'] = 'SPT Induk yang dipilih tidak ditemukan di database.';
        }

        $request->validate($rules, $messages);

        $tglSurat = $request->input('tgl_surat', session('s_tgl_surat', now()->format('Y-m-d')));
        $tujuan = $request->input('tujuan', session('s_tujuan', 'Kementerian Dalam Negeri, Jakarta'));
        $uraian = $request->input('uraian', session('s_uraian', ''));
        $keterangan = $request->input('keterangan', session('s_keterangan', ''));
        $linkGoogleDrive = $request->input('link_google_drive', session('s_link_google_drive'));
        
        $rawPegawaiIds = $request->input('pegawai_id') ?: session('s_pegawai_id', []);
        $pegawaiIds = array_values(array_unique(array_filter((array) $rawPegawaiIds)));

        if (empty($pegawaiIds)) {
            return redirect()->route('surat.create.step2')->with('error', 'Minimal harus memilih satu personel yang ditugaskan sebelum menerbitkan surat.');
        }
        
        $buatSppd = ($jenisSuratId === 1) ? 1 : ($request->has('has_sppd') ? $request->input('has_sppd') : 0);

        $parentId = null;
        $sptIndukManual = null;
        if ($jenisSuratId === 1) {
            if ($request->filled('parent_id')) {
                $parentId = $request->input('parent_id');
            } elseif ($request->filled('spt_induk_manual')) {
                $sptIndukManual = trim($request->input('spt_induk_manual'));
            }
        }

        $modeNomor = $request->input('mode_nomor', session('s_mode_nomor', 'otomatis'));
        $manualNumber = ($modeNomor === 'manual') ? ($request->input('nomor_surat_manual') ?? $request->input('nomor_surat') ?? session('s_nomor_surat')) : null;
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        // Validasi Backdate & Pencegahan Bypass Mode Otomatis (Tugas 2.1 & 2.2)
        $kategoriName = ($jenisSuratId === 1) ? 'SPPD' : 'SPT';
        $backdateCheck = NomorSuratService::isBackdate($kategoriName, $tglSurat);
        if ($backdateCheck['is_backdate'] && $modeNomor === 'otomatis') {
            return back()->withInput()->withErrors([
                'mode_nomor' => 'Tanggal surat sebelum hari ini (backdate). Penomoran otomatis dinonaktifkan, nomor surat wajib diisi secara manual.'
            ]);
        }

        // Validasi Duplikat untuk Nomor Manual (Tugas 3)
        if ($manualNumber) {
            $duplicate = NomorSuratService::checkDuplicate($manualNumber);
            if ($duplicate) {
                return back()->withInput()->withErrors([
                    'nomor_surat_manual' => "Nomor surat '{$manualNumber}' sudah digunakan pada {$duplicate['kategori']} tanggal " . ($duplicate['tgl_surat'] ? date('d/m/Y', strtotime($duplicate['tgl_surat'])) : '-') . "."
                ]);
            }
        }

        $surat = DB::transaction(function () use ($request, $jenisSuratId, $tglSurat, $tujuan, $uraian, $keterangan, $linkGoogleDrive, $pegawaiIds, $buatSppd, $parentId, $sptIndukManual, $manualNumber, $modeNomor, $jenisPenugasan) {
            $usedSppdBatch = [];

            if ($jenisSuratId === 1) {
                $parentSpt = $parentId ? Surat::find($parentId) : null;
                $parentSptNomor = $parentSpt ? $parentSpt->nomor_surat : $sptIndukManual;

                $nomorSurat = $this->generateUniqueNomorSurat(1, $tglSurat, $manualNumber, null, $parentSptNomor, $parentId, $usedSppdBatch, $tujuan);
            } else {
                $nomorSurat = $this->generateUniqueNomorSurat(2, $tglSurat, $manualNumber, null, null, null, $usedSppdBatch, $tujuan);
            }

            $surat = Surat::create([
                'jenis_surat_id' => $jenisSuratId,
                'jenis_penugasan' => $jenisPenugasan,
                'parent_id' => $parentId,
                'spt_induk_manual' => $sptIndukManual,
                'nomor_surat' => $nomorSurat,
                'perihal' => null,
                'tgl_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'uraian' => $uraian,
                'keterangan' => $keterangan,
                'link_google_drive' => $linkGoogleDrive,
                'has_sppd' => (int)$buatSppd,
                'status' => 'Terbit',
                'created_by' => auth()->id() ?? 1,
            ]);

            NomorSuratService::registerNomor(
                $surat->nomor_surat,
                $jenisSuratId === 1 ? 'SPPD' : 'SPT',
                'surats',
                $surat->id,
                $surat->tgl_surat
            );

            if (!empty($pegawaiIds)) {
                $attachData = [];
                $nomorSppdInputs = (array) $request->input('nomor_sppd', []);

                $parentNomorForPersonnel = ($jenisSuratId === 1) 
                    ? ($parentId ? (Surat::find($parentId)->nomor_surat ?? $sptIndukManual) : $sptIndukManual)
                    : $surat->nomor_surat;
                $parentSuratIdForPersonnel = ($jenisSuratId === 1) ? $parentId : $surat->id;

                foreach ($pegawaiIds as $index => $pId) {
                    $nomorSppd = null;
                    if ((int)$buatSppd === 1) {
                        $manualSppd = trim($nomorSppdInputs[$pId] ?? '');
                        if ($modeNomor === 'manual' && $manualSppd && $manualSppd !== '-' && !in_array($manualSppd, $usedSppdBatch, true)) {
                            $this->validateManualNomorSurat(1, $manualSppd);
                            $nomorSppd = $manualSppd;
                            $usedSppdBatch[] = $nomorSppd;
                        } else {
                            if ($jenisSuratId === 1 && $index === 0) {
                                $nomorSppd = $nomorSurat;
                                if (!in_array($nomorSurat, $usedSppdBatch, true)) {
                                    $usedSppdBatch[] = $nomorSurat;
                                }
                            } else {
                                $nomorSppd = $this->generateNextSppdNumber(
                                    $parentNomorForPersonnel, 
                                    $parentSuratIdForPersonnel, 
                                    $tglSurat, 
                                    null, 
                                    $usedSppdBatch, 
                                    $jenisPenugasan
                                );
                            }
                        }
                    }
                    $attachData[$pId] = [
                        'nomor_sppd' => $nomorSppd,
                    ];
                }
                $surat->pegawais()->sync($attachData);

                $pivotRows = DB::table('surat_pegawai')
                    ->where('surat_id', $surat->id)
                    ->whereNotNull('nomor_sppd')
                    ->where('nomor_sppd', '!=', '')
                    ->where('nomor_sppd', '!=', '-')
                    ->get();
                foreach ($pivotRows as $pRow) {
                    NomorSuratService::registerNomor(
                        $pRow->nomor_sppd,
                        'SPPD',
                        'surat_pegawai',
                        $pRow->id,
                        $surat->tgl_surat
                    );
                }
            }

            return $surat;
        });

        if ($request->hasFile('file_surat') && $surat->jenis_surat_id !== 1) {
            $this->handleSuratFileUpload($request, $surat, $driveService);
        }

        session()->forget([
            's_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_mode_nomor', 's_nomor_surat', 's_uraian', 's_keterangan', 's_link_google_drive', 's_pegawai_id', 's_buat_sppd', 's_parent_id', 's_spt_induk_manual',
            's_sppd_tgl_surat', 's_sppd_parent_id', 's_sppd_spt_induk_manual', 's_sppd_mode_nomor', 's_sppd_nomor_surat', 's_sppd_tujuan', 's_sppd_uraian', 's_sppd_keterangan', 's_sppd_pegawai_id'
        ]);

        $suratType = ($surat->jenis_surat_id == 1) ? 'sppd' : 'spt';
        \App\Services\NotificationService::notifyPenugasanSurat($surat, $pegawaiIds, $suratType);

        return redirect()->route('surat.index')->with('success', 'Surat berhasil diterbitkan!');
    }

    public function storeDraft(Request $request)
    {
        try {
            $request->validate([
                'keterangan' => 'nullable|string|max:150',
                'link_google_drive' => [
                    'nullable',
                    'url',
                    'regex:/^(https?:\/\/)?([\w-]+\.)*drive\.google\.com\/.+$/i'
                ],
            ], [
                'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
                'link_google_drive.url' => 'Link Google Drive harus berupa format URL yang valid (diawali https:// atau http://).',
                'link_google_drive.regex' => 'Link harus berupa tautan Google Drive yang valid (contoh: https://drive.google.com/...).',
            ]);

            $tglSurat = $request->input('tgl_surat', session('s_tgl_surat'));
            $jenisSuratId = (int) $request->input('jenis_surat_id', session('s_jenis_surat_id', 2));

            // Validasi minimal: pastikan field yang wajib tidak kosong
            if (empty($tglSurat)) {
                return back()->withInput()->with('error_tgl_surat', 'Tanggal Surat wajib diisi sebelum menyimpan draft.')->with('error', 'Gagal menyimpan draft: Tanggal Surat belum diisi.');
            }

            if (empty($jenisSuratId)) {
                return back()->withInput()->with('error_jenis_surat', 'Jenis Surat wajib dipilih sebelum menyimpan draft.')->with('error', 'Gagal menyimpan draft: Jenis Surat belum dipilih.');
            }

            $tujuan = $request->filled('tujuan') ? $request->input('tujuan') : (session('s_tujuan') ?: '-');
            $uraian = $request->filled('uraian') ? $request->input('uraian') : (session('s_uraian') ?: null);
            $keterangan = $request->filled('keterangan') ? $request->input('keterangan') : (session('s_keterangan') ?: null);
            $linkGoogleDrive = $request->input('link_google_drive', session('s_link_google_drive'));
            $pegawaiIds = $request->filled('pegawai_id') ? (array)$request->input('pegawai_id') : (session('s_pegawai_id') ?: []);

            $parentId = null;
            $sptIndukManual = null;
            if ($jenisSuratId == 1) {
                if ($request->filled('parent_id')) {
                    $parentId = $request->input('parent_id');
                } elseif ($request->filled('spt_induk_manual')) {
                    $sptIndukManual = trim($request->input('spt_induk_manual'));
                }
            }

            $modeNomor = $request->input('mode_nomor', session('s_mode_nomor', 'otomatis'));
            $manualNumber = ($modeNomor === 'manual') ? ($request->input('nomor_surat_manual') ?? $request->input('nomor_surat') ?? session('s_nomor_surat')) : null;
            $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

            // Validasi Backdate & Pencegahan Bypass Mode Otomatis
            $kategoriName = ($jenisSuratId == 1) ? 'SPPD' : 'SPT';
            $backdateCheck = NomorSuratService::isBackdate($kategoriName, $tglSurat);
            if ($backdateCheck['is_backdate'] && $modeNomor === 'otomatis') {
                return back()->withInput()->withErrors([
                    'mode_nomor' => 'Tanggal surat sebelum hari ini (backdate). Penomoran otomatis dinonaktifkan, nomor surat wajib diisi secara manual.'
                ]);
            }

            // Validasi duplikat pada nomor manual jika draft menggunakan nomor manual
            if ($manualNumber) {
                $duplicate = NomorSuratService::checkDuplicate($manualNumber);
                if ($duplicate) {
                    return back()->withInput()->withErrors([
                        'nomor_surat_manual' => "Nomor surat '{$manualNumber}' sudah digunakan pada {$duplicate['kategori']} tanggal " . ($duplicate['tgl_surat'] ? date('d/m/Y', strtotime($duplicate['tgl_surat'])) : '-') . "."
                    ]);
                }
            }

            return DB::transaction(function () use ($request, $jenisSuratId, $tglSurat, $tujuan, $uraian, $keterangan, $linkGoogleDrive, $pegawaiIds, $parentId, $sptIndukManual, $manualNumber, $modeNomor, $jenisPenugasan) {
                $usedSppdBatch = [];

                if ($jenisSuratId === 1) {
                    $parentSpt = $parentId ? Surat::find($parentId) : null;
                    $parentSptNomor = $parentSpt ? $parentSpt->nomor_surat : $sptIndukManual;

                    $nomorSurat = $this->generateUniqueNomorSurat(1, $tglSurat, $manualNumber, null, $parentSptNomor, $parentId, $usedSppdBatch, $tujuan);
                } else {
                    $nomorSurat = $this->generateUniqueNomorSurat(2, $tglSurat, $manualNumber, null, null, null, $usedSppdBatch, $tujuan);
                }

                $surat = Surat::create([
                    'jenis_surat_id' => $jenisSuratId,
                    'jenis_penugasan' => $jenisPenugasan,
                    'parent_id' => $parentId,
                    'spt_induk_manual' => $sptIndukManual,
                    'nomor_surat' => $nomorSurat,
                    'perihal' => null,
                    'tgl_surat' => $tglSurat,
                    'tujuan' => $tujuan,
                    'uraian' => $uraian,
                    'keterangan' => $keterangan,
                    'link_google_drive' => $linkGoogleDrive,
                    'has_sppd' => ($jenisSuratId == 1 ? 1 : 0),
                    'status' => 'Draft',
                    'created_by' => auth()->id() ?? 1,
                ]);

                NomorSuratService::registerNomor(
                    $surat->nomor_surat,
                    $jenisSuratId === 1 ? 'SPPD' : 'SPT',
                    'surats',
                    $surat->id,
                    $surat->tgl_surat
                );

                if (!empty($pegawaiIds)) {
                    $filteredPegawaiIds = array_values(array_unique(array_filter($pegawaiIds)));
                    $attachData = [];
                    $parentNomorForPersonnel = ($jenisSuratId === 1) 
                        ? ($parentId ? (Surat::find($parentId)->nomor_surat ?? $sptIndukManual) : $sptIndukManual)
                        : $surat->nomor_surat;
                    $parentSuratIdForPersonnel = ($jenisSuratId === 1) ? $parentId : $surat->id;

                    foreach ($filteredPegawaiIds as $index => $pId) {
                        $nomorSppd = null;
                        if ($jenisSuratId === 1) {
                            if ($index === 0) {
                                $nomorSppd = $nomorSurat;
                                if (!in_array($nomorSurat, $usedSppdBatch, true)) {
                                    $usedSppdBatch[] = $nomorSurat;
                                }
                            } else {
                                $nomorSppd = $this->generateNextSppdNumber(
                                    $parentNomorForPersonnel,
                                    $parentSuratIdForPersonnel,
                                    $tglSurat,
                                    null,
                                    $usedSppdBatch,
                                    $jenisPenugasan
                                );
                            }
                        }
                        $attachData[$pId] = [
                            'nomor_sppd' => $nomorSppd,
                        ];
                    }
                    $surat->pegawais()->sync($attachData);

                    $pivotRows = DB::table('surat_pegawai')
                        ->where('surat_id', $surat->id)
                        ->whereNotNull('nomor_sppd')
                        ->where('nomor_sppd', '!=', '')
                        ->where('nomor_sppd', '!=', '-')
                        ->get();
                    foreach ($pivotRows as $pRow) {
                        NomorSuratService::registerNomor(
                            $pRow->nomor_sppd,
                            'SPPD',
                            'surat_pegawai',
                            $pRow->id,
                            $surat->tgl_surat
                        );
                    }
                }

                session()->forget([
                    's_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_mode_nomor', 's_nomor_surat', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd', 's_parent_id', 's_spt_induk_manual',
                    's_sppd_tgl_surat', 's_sppd_parent_id', 's_sppd_spt_induk_manual', 's_sppd_mode_nomor', 's_sppd_nomor_surat', 's_sppd_tujuan', 's_sppd_uraian', 's_sppd_keterangan', 's_sppd_pegawai_id'
                ]);

                return redirect()->route('surat.index')->with('success', 'Draft surat berhasil disimpan!');
            });
        } catch (\Throwable $e) {
            \Log::error('Gagal simpan draft surat: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan draft ke database: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $surat = Surat::with('children')->find($id);

        if (!$surat) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.'
                ], 404);
            }

            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }

        // Validasi: Cek apakah surat ini memiliki SPPD turunan (child)
        if ($surat->children->isNotEmpty()) {
            $count = $surat->children->count();
            $nomorList = $surat->children->pluck('nomor_surat')->filter()->values()->all();
            $nomorStr = !empty($nomorList) ? implode(', ', $nomorList) : '-';
            $nomorSurat = $surat->nomor_surat ?: "#{$surat->id}";

            $errorMessage = "Dokumen SPT {$nomorSurat} tidak dapat dihapus karena masih memiliki {$count} dokumen SPPD terkait ({$nomorStr}). Hapus atau hubungkan ulang SPPD tersebut ke SPT lain terlebih dahulu.";

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'child_count' => $count,
                    'children' => $nomorList,
                ], 422);
            }

            return redirect()->route('surat.index')->with('error', $errorMessage);
        }

        // Lepaskan nomor surat dan nomor SPPD pegawai dari registri nomor_surat_terpakai (Tugas 3)
        NomorSuratService::releaseNomor('surats', $surat->id);
        $pivotIds = DB::table('surat_pegawai')->where('surat_id', $surat->id)->pluck('id');
        foreach ($pivotIds as $pId) {
            NomorSuratService::releaseNomor('surat_pegawai', $pId);
        }

        foreach ($surat->children as $child) {
            NomorSuratService::releaseNomor('surats', $child->id);
            $childPivotIds = DB::table('surat_pegawai')->where('surat_id', $child->id)->pluck('id');
            foreach ($childPivotIds as $cpId) {
                NomorSuratService::releaseNomor('surat_pegawai', $cpId);
            }
            $child->pegawais()->detach();
            $child->delete();
        }

        $surat->pegawais()->detach();
        $surat->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil dihapus!'
            ]);
        }

        return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus!');
    }

    public function rekapIndex(Request $request)
    {
        return $this->index($request);
    }

    public function exportRekapPdf(Request $request)
    {
        $surats = $this->getUnifiedSuratKeluar($request);

        // Judul Periode Dinamis
        $periodeText = 'Rekapitulasi Surat Keluar Keseluruhan';
        if ($request->filled('month') && $request->filled('year')) {
            $monthName = \Carbon\Carbon::createFromDate($request->year, $request->month, 1)->translatedFormat('F');
            $periodeText = "Rekapitulasi Surat Keluar — Bulan {$monthName} {$request->year}";
        } elseif ($request->filled('year')) {
            $periodeText = 'Rekapitulasi Surat Keluar — Tahun ' . $request->year;
        } elseif ($request->filled('month')) {
            $monthName = \Carbon\Carbon::createFromDate(date('Y'), $request->month, 1)->translatedFormat('F');
            $periodeText = "Rekapitulasi Surat Keluar — Bulan {$monthName}";
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keluar — {$startDateFormatted} s/d {$endDateFormatted}";
        } elseif ($request->filled('start_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keluar — Mulai {$startDateFormatted}";
        } elseif ($request->filled('end_date')) {
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keluar — Sampai {$endDateFormatted}";
        }

        // Metrik Ringkasan Rekap
        $totalSpt = $surats->filter(fn($item) => $item->kategori === 'SPT')->count();

        $totalSppd = 0;
        foreach ($surats as $item) {
            if (!empty($item->direct_sppds)) {
                $totalSppd += count($item->direct_sppds);
            }
            if (!empty($item->child_sppds)) {
                $totalSppd += count($item->child_sppds);
            }
        }

        $totalBulanIni = $surats->filter(function($item) {
            return $item->tgl_surat && \Carbon\Carbon::parse($item->tgl_surat)->isCurrentMonth() && \Carbon\Carbon::parse($item->tgl_surat)->isCurrentYear();
        })->count();

        $logoKominfoPath = file_exists(public_path('images/logo-kominfo.png')) 
            ? public_path('images/logo-kominfo.png') 
            : (file_exists(public_path('image/logo-kominfo.png')) ? public_path('image/logo-kominfo.png') : null);
        $logoKominfoBase64 = ($logoKominfoPath && file_exists($logoKominfoPath)) ? base64_encode(file_get_contents($logoKominfoPath)) : null;
        $logoBase64 = $logoKominfoBase64;

        $logoBonePath = file_exists(public_path('images/bonebolango.png')) 
            ? public_path('images/bonebolango.png') 
            : (file_exists(public_path('image/bonebolango.png')) ? public_path('image/bonebolango.png') : null);
        $logoBoneBase64 = ($logoBonePath && file_exists($logoBonePath)) ? base64_encode(file_get_contents($logoBonePath)) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.rekap-pdf', compact(
            'surats', 
            'totalSpt', 
            'totalSppd', 
            'totalBulanIni', 
            'logoBase64', 
            'logoKominfoBase64', 
            'logoBoneBase64',
            'periodeText'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Surat_Bone_Bolango_'.date('Ymd_His').'.pdf');
    }

    /**
     * Endpoint untuk mengunggah file scan fisik surat keluar belakangan / revisi.
     */
    public function uploadFileSurat(Request $request, $id, ?GoogleDriveService $driveService = null)
    {
        $driveService = $driveService ?: app(GoogleDriveService::class);

        $request->validate([
            'file_surat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'file_surat.required' => 'Pilih file hasil scan surat terlebih dahulu.',
            'file_surat.mimes' => 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_surat.max' => 'Ukuran file surat maksimal 10 MB.',
        ]);

        $surat = Surat::findOrFail($id);
        $this->handleSuratFileUpload($request, $surat, $driveService);

        if ($surat->drive_upload_status === 'success') {
            $msg = "File scan surat {$surat->nomor_surat} berhasil diunggah dan tersinkronisasi ke Google Drive!";
        } else {
            $msg = "File scan tersimpan secara lokal, namun sinkronisasi Google Drive mengalami kendala. Anda dapat mencoba klik 'Coba Upload Ulang ke Drive'.";
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'drive_url' => $surat->google_drive_url,
                'drive_status' => $surat->drive_upload_status,
                'file_name' => $surat->file_name,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Endpoint untuk mencoba ulang sinkronisasi file lokal ke Google Drive jika status failed.
     */
    public function retryDriveUpload($id, ?GoogleDriveService $driveService = null)
    {
        $driveService = $driveService ?: app(GoogleDriveService::class);

        $surat = Surat::findOrFail($id);

        if (empty($surat->file_path) || !file_exists(storage_path('app/' . $surat->file_path))) {
            return back()->with('error', 'File fisik lokal tidak ditemukan pada server. Silakan upload ulang file surat.');
        }

        $localFullPath = storage_path('app/' . $surat->file_path);
        $year = Surat::formatTahun($surat->tgl_surat);
        $jenisName = ($surat->jenis_surat_id == 1 || str_starts_with($surat->nomor_surat, '090/')) ? 'SPPD' : 'SPT';

        $uploadResult = $driveService->uploadSuratFile($localFullPath, $surat->nomor_surat, $jenisName, $year);

        if ($uploadResult['success']) {
            $surat->google_drive_file_id = $uploadResult['file_id'];
            $surat->google_drive_url = $uploadResult['web_view_link'];
            $surat->drive_upload_status = 'success';
            $surat->save();

            return back()->with('success', "Sinkronisasi Google Drive untuk surat {$surat->nomor_surat} berhasil!");
        } else {
            $surat->drive_upload_status = 'failed';
            $surat->save();

            return back()->with('error', "Gagal sinkronisasi Google Drive: " . ($uploadResult['error'] ?? 'Terjadi kesalahan.'));
        }
    }

    /**
     * Helper internal untuk memproses upload file lokal dan sinkronisasi ke Google Drive.
     */
    protected function handleSuratFileUpload(Request $request, Surat $surat, ?GoogleDriveService $driveService = null, ?UploadedFile $uploadedFile = null): void
    {
        $driveService = $driveService ?: app(GoogleDriveService::class);
        $file = $uploadedFile ?: $request->file('file_surat');
        if (!$file || !$file->isValid()) {
            return;
        }

        $year = Surat::formatTahun($surat->tgl_surat);
        $jenisName = ($surat->jenis_surat_id == 1 || str_starts_with($surat->nomor_surat, '090/')) ? 'SPPD' : 'SPT';

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: 'pdf';
        $cleanNomor = preg_replace('/[\/\\\\]+/', '-', $surat->nomor_surat);
        $localFileName = "{$surat->id}_{$cleanNomor}.{$extension}";
        $localPath = $file->storeAs("public/surat-keluar/{$year}", $localFileName);

        $surat->file_name = $originalName;
        $surat->file_path = $localPath;
        $surat->drive_upload_status = 'pending';
        $surat->save();

        // Hapus file lama di Drive jika ada
        if (!empty($surat->google_drive_file_id)) {
            $driveService->deleteFile($surat->google_drive_file_id);
        }

        $uploadResult = $driveService->uploadSuratFile($file, $surat->nomor_surat, $jenisName, $year);

        if ($uploadResult['success']) {
            $surat->google_drive_file_id = $uploadResult['file_id'];
            $surat->google_drive_url = $uploadResult['web_view_link'];
            $surat->drive_upload_status = 'success';
        } else {
            $surat->drive_upload_status = 'failed';
            Log::warning("Sinkronisasi Google Drive gagal untuk surat #{$surat->id} ({$surat->nomor_surat}): " . ($uploadResult['error'] ?? 'Unknown error'));
        }

        $surat->save();
    }
}