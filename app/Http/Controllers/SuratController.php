<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Http\Requests\StorePegawaiP3kRequest;
use App\Services\SppdNumberingService;
use App\Services\GoogleDriveService;
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
                            ->count();

        $surats = Surat::with(['jenisSurat', 'pegawais', 'parent', 'children.pegawais'])
                    ->whereNull('parent_id')
                    ->when($request->filled('search'), function($query) use ($request) {
                        $search = $request->search;
                        return $query->where(function($q) use ($search) {
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
                    ->when($request->filled('filter_jenis') || $request->filled('jenis'), function($query) use ($request) {
                        $jenis = $request->get('filter_jenis', $request->get('jenis'));
                        if ($jenis === 'SPT') {
                            return $query->where(function($q) {
                                $q->where('jenis_surat_id', 2)
                                  ->orWhereNull('jenis_surat_id')
                                  ->orWhere('nomor_surat', 'like', '555/%');
                            })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
                        } elseif ($jenis === 'SPPD') {
                            return $query->where(function($q) {
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
                                });
                            });
                        }
                    })
                    ->when($request->filled('year'), function($query) use ($request) {
                        return $query->whereYear('tgl_surat', $request->year);
                    })
                    ->when(!$request->filled('year') && $request->filled('start_date') && $request->filled('end_date'), function($query) use ($request) {
                        return $query->whereBetween('tgl_surat', [$request->start_date, $request->end_date]);
                    })
                    ->orderByRekap()
                    ->paginate(5)
                    ->withQueryString();

        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3, 4])
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai', 'Bendahara Barang', 'Pegawai Biasa'])
            ->orderByHierarki()
            ->get();
        $nextSppdCounter = $this->getNextSppdCounter();

        // Ambil daftar SPT aktif beserta metadata penomoran SPPD
        $sptList = SppdNumberingService::getSptMetadataList();
        $seriesSummary = SppdNumberingService::getSeriesSummaryForFrontend();

        return view('surat.index', compact('surats', 'totalSpt', 'totalSppd', 'totalBulanIni', 'allPegawais', 'nextSppdCounter', 'sptList', 'seriesSummary'));
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
                session(['s_tgl_surat' => date('Y-m-d')]);
            }
        }

        // Saring pegawai agar akun Admin Master, Admin Kasubag, Bendahara, dan Pegawai Biasa tidak ikut terpanggil
        $pegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3, 4])
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

    public function show($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais', 'parent', 'children.pegawais'])->find($id);
        if (!$surat) {
            return redirect()->route('surat.index')->with('error', 'Data surat tidak ditemukan, mungkin sudah dihapus sebelumnya.');
        }

        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3, 4])
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai', 'Bendahara Barang', 'Pegawai Biasa'])
            ->orderByHierarki()
            ->get();
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
        $pegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3, 4])
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai', 'Bendahara Barang', 'Pegawai Biasa'])
            ->orderByHierarki()
            ->get();
        
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
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
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

        if ($request->hasFile('file_surat')) {
            $this->handleSuratFileUpload($request, $updatedSurat, $driveService);
        }

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
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'tgl_surat.required' => 'Tanggal SPPD wajib diisi.',
            'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'file_surat.mimes' => 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_surat.max' => 'Ukuran file surat maksimal 10 MB.',
        ]);

        $parentSpt = Surat::findOrFail($id);

        $tglSurat = $request->tgl_surat;
        $tujuan = $request->input('tujuan', $parentSpt->tujuan);
        $uraian = $request->input('uraian', $parentSpt->uraian ?: 'Perjalanan Dinas');
        $keterangan = $request->input('keterangan');
        $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        $childSurat = DB::transaction(function () use ($parentSpt, $tglSurat, $tujuan, $uraian, $keterangan, $pegawaiIds, $jenisPenugasan) {
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

            // Pastikan SPT induk ter-flag has_sppd = 1
            if (!$parentSpt->has_sppd) {
                $parentSpt->has_sppd = 1;
                $parentSpt->save();
            }

            return $childSurat;
        });

        if ($request->hasFile('file_surat')) {
            $this->handleSuratFileUpload($request, $childSurat, $driveService);
        }

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
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'parent_id.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
            'spt_induk_manual.required_without' => 'SPT Induk wajib dipilih dari database atau diketik nomornya secara manual.',
            'tgl_surat.required' => 'Tanggal SPPD wajib diisi.',
            'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'file_surat.mimes' => 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_surat.max' => 'Ukuran file surat maksimal 10 MB.',
        ]);

        $parentId = $request->filled('parent_id') ? $request->parent_id : null;
        $sptIndukManual = $request->filled('spt_induk_manual') ? trim($request->spt_induk_manual) : null;
        $parentSpt = $parentId ? Surat::find($parentId) : null;
        $parentSptNomor = $parentSpt ? $parentSpt->nomor_surat : $sptIndukManual;

        $tglSurat = $request->tgl_surat;
        $tujuan = $request->input('tujuan', ($parentSpt ? $parentSpt->tujuan : '-'));
        $uraian = $request->input('uraian', ($parentSpt ? $parentSpt->uraian : 'Perjalanan Dinas'));
        $keterangan = $request->input('keterangan');
        $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
        $jenisPenugasan = $this->determineJenisPenugasan($tujuan);

        $childSurat = DB::transaction(function () use ($parentId, $parentSpt, $parentSptNomor, $sptIndukManual, $tglSurat, $tujuan, $uraian, $keterangan, $pegawaiIds, $jenisPenugasan) {
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

            if ($parentSpt && !$parentSpt->has_sppd) {
                $parentSpt->has_sppd = 1;
                $parentSpt->save();
            }

            return $childSurat;
        });

        if ($request->hasFile('file_surat')) {
            $this->handleSuratFileUpload($request, $childSurat, $driveService);
        }

        return redirect()->route('surat.index')->with('success', 'Surat Perintah Perjalanan Dinas (SPPD) ' . $childSurat->nomor_surat . ' berhasil diterbitkan!');
    }

    public function store(Request $request, GoogleDriveService $driveService)
    {
        $jenisSuratId = (int) session('s_jenis_surat_id', $request->jenis_surat_id ?? 2);

        $rules = [
            'keterangan' => 'nullable|string|max:150',
            'tgl_surat' => 'nullable|date',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
        $messages = [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
            'file_surat.mimes' => 'Format file surat harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_surat.max' => 'Ukuran file surat maksimal 10 MB.',
        ];

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

        $surat = DB::transaction(function () use ($request, $jenisSuratId, $tglSurat, $tujuan, $uraian, $keterangan, $pegawaiIds, $buatSppd, $parentId, $sptIndukManual, $manualNumber, $modeNomor, $jenisPenugasan) {
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
                'has_sppd' => (int)$buatSppd,
                'status' => 'Terbit',
                'created_by' => auth()->id() ?? 1,
            ]);

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
            }

            return $surat;
        });

        if ($request->hasFile('file_surat')) {
            $this->handleSuratFileUpload($request, $surat, $driveService);
        }

        session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_mode_nomor', 's_nomor_surat', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd', 's_parent_id', 's_spt_induk_manual']);

        return redirect()->route('surat.index')->with('success', 'Surat berhasil diterbitkan!');
    }

    public function storeDraft(Request $request)
    {
        try {
            $request->validate([
                'keterangan' => 'nullable|string|max:150',
            ], [
                'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
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

            return DB::transaction(function () use ($request, $jenisSuratId, $tglSurat, $tujuan, $uraian, $keterangan, $pegawaiIds, $parentId, $sptIndukManual, $manualNumber, $modeNomor, $jenisPenugasan) {
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
                    'has_sppd' => ($jenisSuratId == 1 ? 1 : 0),
                    'status' => 'Draft',
                    'created_by' => auth()->id() ?? 1,
                ]);

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
                }

                session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_mode_nomor', 's_nomor_surat', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd', 's_parent_id', 's_spt_induk_manual']);

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
        $query = Surat::with(['jenisSurat', 'pegawais', 'parent', 'children.pegawais'])
                    ->whereNull('parent_id')
                    ->when($request->filled('search'), function($query) use ($request) {
                        $search = $request->search;
                        return $query->where(function($q) use ($search) {
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
                    ->when($request->filled('filter_jenis') || $request->filled('jenis'), function($q) use ($request) {
                        $jenis = $request->get('filter_jenis', $request->get('jenis'));
                        if ($jenis === 'SPT') {
                            return $q->where(function($sq) {
                                $sq->where('jenis_surat_id', 2)
                                  ->orWhereNull('jenis_surat_id')
                                  ->orWhere('nomor_surat', 'like', '555/%');
                            })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
                        } elseif ($jenis === 'SPPD') {
                            return $q->where(function($sqq) {
                                $sqq->where(function($sq) {
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
                                });
                            });
                        }
                    })
                    ->when($request->filled('year'), function($q) use ($request) {
                        return $q->whereYear('tgl_surat', $request->year);
                    })
                    ->when(!$request->filled('year') && $request->filled('start_date') && $request->filled('end_date'), function($q) use ($request) {
                        return $q->whereBetween('tgl_surat', [$request->start_date, $request->end_date]);
                    })
                    ->when(!$request->filled('year') && $request->filled('start_date') && !$request->filled('end_date'), function($q) use ($request) {
                        return $q->whereDate('tgl_surat', '>=', $request->start_date);
                    })
                    ->when(!$request->filled('year') && !$request->filled('start_date') && $request->filled('end_date'), function($q) use ($request) {
                        return $q->whereDate('tgl_surat', '<=', $request->end_date);
                    })
                    ->orderByRekap();

        $surats = $query->get();

        // Judul Periode Dinamis
        $periodeText = 'Rekapitulasi Surat Keseluruhan';
        if ($request->filled('year')) {
            $periodeText = 'Rekapitulasi Surat — Tahun ' . $request->year;
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat — {$startDateFormatted} s/d {$endDateFormatted}";
        } elseif ($request->filled('start_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat — Mulai {$startDateFormatted}";
        } elseif ($request->filled('end_date')) {
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat — Sampai {$endDateFormatted}";
        }

        // Metrik Ringkasan Rekap
        $totalSpt = $surats->filter(function($item) {
            return ($item->jenis_surat_id == 2 || is_null($item->jenis_surat_id) || str_starts_with($item->nomor_surat, '555/')) && !empty($item->nomor_surat);
        })->count();

        $totalSppd = 0;
        foreach ($surats as $item) {
            if ($item->has_sppd && $item->pegawais) {
                $totalSppd += $item->pegawais->filter(function($p) {
                    return !empty($p->pivot->nomor_sppd) && $p->pivot->nomor_sppd !== '-';
                })->count();
            }
            if ($item->children) {
                foreach ($item->children as $child) {
                    if ($child->pegawais) {
                        $totalSppd += $child->pegawais->filter(function($cp) {
                            return !empty($cp->pivot->nomor_sppd) && $cp->pivot->nomor_sppd !== '-';
                        })->count();
                    }
                }
            }
        }

        $totalBulanIni = $surats->filter(function($item) {
            return \Carbon\Carbon::parse($item->tgl_surat)->isCurrentMonth() && \Carbon\Carbon::parse($item->tgl_surat)->isCurrentYear();
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