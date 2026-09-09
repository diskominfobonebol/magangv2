<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use Illuminate\Support\Facades\DB;

class SuratController extends Controller
{
    protected function getNextSppdCounter()
    {
        $maxFromSurat = Surat::where('nomor_surat', 'like', '090/%')->get()
            ->map(function ($item) {
                $parts = explode('/', $item->nomor_surat);
                return isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;
            })->max() ?? 0;

        $maxFromPivot = DB::table('surat_pegawai')
            ->where('nomor_sppd', 'like', '090/%')->get()
            ->map(function ($item) {
                $parts = explode('/', $item->nomor_sppd);
                return isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;
            })->max() ?? 0;

        return max($maxFromSurat, $maxFromPivot) + 1;
    }

    public function getNextSppdCounterApi()
    {
        return response()->json([
            'next_counter' => $this->getNextSppdCounter()
        ]);
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

        $surats = Surat::with(['jenisSurat', 'pegawais'])
                    ->when($request->filled('search'), function($query) use ($request) {
                        $search = $request->search;
                        return $query->where(function($q) use ($search) {
                            $q->where('nomor_surat', 'like', "%{$search}%")
                                ->orWhere('perihal', 'like', "%{$search}%")
                                ->orWhere('tujuan', 'like', "%{$search}%")
                                ->orWhere('uraian', 'like', "%{$search}%")
                                ->orWhere('keterangan', 'like', "%{$search}%")
                                ->orWhereHas('pegawais', function($pq) use ($search) {
                                    $pq->where('nama', 'like', "%{$search}%")
                                       ->orWhere('nip', 'like', "%{$search}%")
                                       ->orWhere('surat_pegawai.nomor_sppd', 'like', "%{$search}%");
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
                            return $query->where('has_sppd', 1)
                                         ->whereHas('pegawais', function($pq) {
                                             $pq->whereNotNull('surat_pegawai.nomor_sppd')
                                                ->where('surat_pegawai.nomor_sppd', '!=', '');
                                         });
                        }
                    })
                    ->when($request->filled('year'), function($query) use ($request) {
                        return $query->whereYear('tgl_surat', $request->year);
                    })
                    ->when(!$request->filled('year') && $request->filled('start_date') && $request->filled('end_date'), function($query) use ($request) {
                        return $query->whereBetween('tgl_surat', [$request->start_date, $request->end_date]);
                    })
                    ->latest('tgl_surat')
                    ->latest('id')
                    ->paginate(10)
                    ->withQueryString();

        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->get();
        $nextSppdCounter = $this->getNextSppdCounter();

        return view('surat.index', compact('surats', 'totalSpt', 'totalSppd', 'totalBulanIni', 'allPegawais', 'nextSppdCounter'));
    }

   public function create(Request $request)
{
    // Bersihkan session jika bukan dari tombol kembali
    if (!$request->hasHeader('referer') || !str_contains(url()->previous(), 'step-2')) {
        session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_uraian', 's_keterangan', 's_pegawai_id']);
    }

    $jenisSuratId = $request->get('jenis_surat_id', session('s_jenis_surat_id', 2)); 

    // Ambil data pilihan jenis surat untuk looping di view
    $jenisSurats = \App\Models\JenisSurat::all(); 

    $latestSurat = \App\Models\Surat::where('jenis_surat_id', $jenisSuratId)->get()
        ->map(function ($item) {
            $parts = explode('/', $item->nomor_surat);
            return isset($parts[1]) ? (int) $parts[1] : 0;
        })->max();

    $count = ($latestSurat ?? 0) + 1;
    $nomorUrut = str_pad($count, 3, '0', STR_PAD_LEFT);

    return view('surat.create', compact('nomorUrut', 'jenisSuratId', 'jenisSurats'));
}

   public function createStep2(Request $request)
    {
        if ($request->isMethod('post') && $request->has('tgl_surat')) {
            session([
                's_tgl_surat' => $request->tgl_surat,
                's_jenis_surat_id' => $request->jenis_surat_id,
                's_nomor_surat' => $request->nomor_surat,
                's_tujuan' => $request->tujuan,
            ]);
        }

        // Saring pegawai agar "Admin", "Pegawai Biasa", atau akun sistem tidak ikut terpanggil
        $pegawais = \App\Models\Pegawai::where('nama', 'not like', '%Admin%')
            ->where('nama', 'not like', '%Pegawai Biasa%')
            ->where('jabatan', 'not like', '%Pegawai Biasa%')
            ->get();

        return view('surat.create-step-2', compact('pegawais'));
    }
    public function createStep3(Request $request)
{
    if ($request->isMethod('post')) {
        // Tambahkan validasi agar pegawai_id wajib diisi (minimal 1 personel)
        $request->validate([
            'pegawai_id' => 'required|array|min:1',
        ], [
            'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
            'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
        ]);

        session([
            's_uraian' => $request->uraian,
            's_keterangan' => $request->keterangan,
            's_pegawai_id' => $request->pegawai_id,
        ]);
    }

    $selectedPegawais = collect();
    $pegawaiIds = session('s_pegawai_id', []);
    if (!empty($pegawaiIds)) {
        $selectedPegawais = \App\Models\Pegawai::whereIn('id', $pegawaiIds)->get();
    }

    return view('surat.create-step-3', compact('selectedPegawais'));
}

    public function show($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        return view('surat.show', compact('surat'));
    }

    public function edit($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        $pegawais = \App\Models\Pegawai::all();
        return view('surat.edit', compact('surat', 'pegawais'));
    }

    public function update(Request $request, $id)
    {
        $surat = Surat::with('pegawais')->findOrFail($id);

        $hasSppd = $request->input('has_sppd');
        $buatSppd = ($hasSppd === '1' || $hasSppd === 1 || $hasSppd === 'ya') ? 1 : 0;

        $tglSurat = $request->input('tgl_surat', $surat->tgl_surat);
        $tujuan = $request->input('tujuan', $surat->tujuan);
        $uraian = $request->input('uraian', $surat->uraian);
        $keterangan = $request->input('keterangan', $surat->keterangan);
        $perihal = $request->input('perihal', $surat->perihal);
        
        $nomorSurat = $request->input('nomor_surat', $surat->nomor_surat);
        if ($request->filled('nomor_surat_manual') && $request->input('mode_nomor') === 'manual') {
            $nomorSurat = $request->input('nomor_surat_manual');
        }

        $jenisSuratId = $surat->jenis_surat_id;
        if ($request->filled('jenis_surat')) {
            $jenisName = $request->input('jenis_surat');
            $jenisObj = \App\Models\JenisSurat::where('nama_jenis', $jenisName)->orWhere('kode', $jenisName)->first();
            if ($jenisObj) {
                $jenisSuratId = $jenisObj->id;
            }
        }

        $status = $request->input('status');
        if (empty($status)) {
            $status = ($surat->status === 'Draft') ? 'Terbit' : ($surat->status ?? 'Terbit');
        }

        $surat->update([
            'tgl_surat' => $tglSurat,
            'perihal' => $perihal,
            'tujuan' => $tujuan,
            'uraian' => $uraian,
            'keterangan' => $keterangan,
            'nomor_surat' => $nomorSurat,
            'jenis_surat_id' => $jenisSuratId,
            'has_sppd' => $buatSppd,
            'status' => $status,
        ]);

        $pegawaiIds = array_values(array_unique(array_filter((array) $request->input('pegawai_id', []))));
        $syncData = [];
        $nomorSppdInputs = (array) $request->input('nomor_sppd', []);
        
        $d = new \DateTime($tglSurat ?: date('Y-m-d'));
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        if (!$buatSppd) {
            foreach ($pegawaiIds as $pId) {
                $syncData[$pId] = [
                    'nomor_sppd' => null,
                ];
            }
        } else {
            $existingSppdMap = $surat->pegawais->pluck('pivot.nomor_sppd', 'id')->toArray();
            $assignedSppd = [];
            $usedSppdSet = [];

            foreach ($pegawaiIds as $pId) {
                $manualInput = trim($nomorSppdInputs[$pId] ?? '');
                $existingNum = trim($existingSppdMap[$pId] ?? '');
                $candidate = ($manualInput && $manualInput !== '-') ? $manualInput : (($existingNum && $existingNum !== '-') ? $existingNum : null);

                if ($candidate && !in_array($candidate, $usedSppdSet, true)) {
                    $assignedSppd[$pId] = $candidate;
                    $usedSppdSet[] = $candidate;
                }
            }

            $sppdCounter = $this->getNextSppdCounter();

            foreach ($pegawaiIds as $pId) {
                if (isset($assignedSppd[$pId])) {
                    $syncData[$pId] = [
                        'nomor_sppd' => $assignedSppd[$pId],
                    ];
                } else {
                    do {
                        $sppdUrut = str_pad($sppdCounter, 3, '0', STR_PAD_LEFT);
                        $newSppd = "090/{$sppdUrut}/{$bln}/{$thn}";
                        $sppdCounter++;
                    } while (in_array($newSppd, $usedSppdSet, true));

                    $usedSppdSet[] = $newSppd;
                    $syncData[$pId] = [
                        'nomor_sppd' => $newSppd,
                    ];
                }
            }
        }

        $surat->pegawais()->sync($syncData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perubahan data surat berhasil disimpan!',
                'surat' => $surat->fresh(['jenisSurat', 'pegawais'])
            ]);
        }

        return redirect()->route('surat.index')->with('success', 'Perubahan data surat berhasil disimpan!');
    }

    public function downloadPdf($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        $isPdf = true; 
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.print', compact('surat', 'isPdf'));
        return $pdf->download('Surat_'.$id.'.pdf');
    }

    public function print($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        $isPdf = false; 
        return view('surat.print', compact('surat', 'isPdf'));
    }

    public function store(Request $request)
    {
        $tglSurat = session('s_tgl_surat', $request->tgl_surat ?? now()->format('Y-m-d'));
        $tujuan = session('s_tujuan', $request->tujuan ?? 'Kementerian Dalam Negeri, Jakarta');
        $jenisSuratId = session('s_jenis_surat_id', $request->jenis_surat_id ?? 1);
        $uraian = session('s_uraian', $request->uraian);
        $keterangan = session('s_keterangan', $request->keterangan);
        $pegawaiIds = session('s_pegawai_id', $request->pegawai_id ?? []);
        
        $buatSppd = $request->has('has_sppd') ? $request->input('has_sppd') : 0;

        $jenisSurat = \App\Models\JenisSurat::find($jenisSuratId);
        $kodeSurat = $jenisSurat ? $jenisSurat->kode : '090';

        $latestSurat = \App\Models\Surat::where('jenis_surat_id', $jenisSuratId)->get()
            ->map(function ($item) {
                $parts = explode('/', $item->nomor_surat);
                return isset($parts[1]) ? (int) $parts[1] : 0;
            })->max();

        $countUrut = ($latestSurat ?? 0) + 1;
        $noUrut = str_pad($countUrut, 3, '0', STR_PAD_LEFT);

        $d = new \DateTime($tglSurat);
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        $nomorSuratOtomatis = "{$kodeSurat}/{$noUrut}/{$bln}/{$thn}";

        $surat = Surat::create([
            'jenis_surat_id' => $jenisSuratId,
            'nomor_surat' => $request->nomor_surat ?? $nomorSuratOtomatis,
            'perihal' => $request->perihal ?? 'Surat Tugas',
            'tgl_surat' => $tglSurat,
            'tujuan' => $tujuan,
            'uraian' => $uraian,
            'keterangan' => $keterangan,
            'has_sppd' => (int)$buatSppd,
            'created_by' => auth()->id() ?? 1,
        ]);

        if (!empty($pegawaiIds)) {
            $surat->pegawais()->attach($pegawaiIds);
        }

        session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd']);

        return redirect()->route('surat.show', $surat->id)->with('success', 'Surat berhasil diterbitkan!');
    }

    public function storeDraft(Request $request)
    {
        $tglSurat = $request->tgl_surat ?? session('s_tgl_surat', now()->format('Y-m-d'));
        $tujuan = !empty($request->tujuan) ? $request->tujuan : (session('s_tujuan') ?? 'Belum ditentukan');
        $jenisSuratId = $request->jenis_surat_id ?? session('s_jenis_surat_id', 2);
        $uraian = !empty($request->uraian) ? $request->uraian : (session('s_uraian') ?? 'Draft awal surat');
        $keterangan = $request->keterangan ?? session('s_keterangan', null);
        $pegawaiIds = $request->pegawai_id ?? session('s_pegawai_id', []);

        $jenisSurat = \App\Models\JenisSurat::find($jenisSuratId);
        $kodeSurat = $jenisSurat ? $jenisSurat->kode : '090';

        $latestSurat = \App\Models\Surat::where('jenis_surat_id', $jenisSuratId)->get()
            ->map(function ($item) {
                $parts = explode('/', $item->nomor_surat);
                return isset($parts[1]) ? (int) $parts[1] : 0;
            })->max();

        $countUrut = ($latestSurat ?? 0) + 1;
        $noUrut = str_pad($countUrut, 3, '0', STR_PAD_LEFT);

        $d = new \DateTime($tglSurat);
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        $nomorSuratOtomatis = "{$kodeSurat}/{$noUrut}/{$bln}/{$thn}";

        $surat = Surat::create([
            'jenis_surat_id' => $jenisSuratId,
            'nomor_surat' => $request->nomor_surat ?? $nomorSuratOtomatis,
            'perihal' => $request->perihal ?? 'Draft Surat Tugas',
            'tgl_surat' => $tglSurat,
            'tujuan' => $tujuan,
            'uraian' => $uraian,
            'keterangan' => $keterangan,
            'has_sppd' => 0,
            'created_by' => auth()->id() ?? 1,
        ]);

        if (!empty($pegawaiIds)) {
            $surat->pegawais()->attach($pegawaiIds);
        }

        session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_uraian', 's_keterangan', 's_pegawai_id']);

        return redirect()->route('surat.index')->with('success', 'Draft surat berhasil disimpan!');
    }

    public function destroy($id)
    {
        $surat = Surat::findOrFail($id);
        $surat->pegawais()->detach();
        $surat->delete();

        return redirect()->route('surat.index')->with('success', 'Surat berhasil dihapus!');
    }

    public function rekapIndex(Request $request)
    {
        $totalSpt = Surat::where('jenis_surat_id', 2)->count();
        $totalSppd = Surat::where('has_sppd', 1)->count();
        $totalBulanIni = Surat::whereMonth('tgl_surat', now()->month)
                            ->whereYear('tgl_surat', now()->year)
                            ->count();

        // Mengambil data seluruh SPT (setiap baris adalah 1 SPT beserta relasi SPPD-nya)
        $surats = Surat::with(['jenisSurat', 'pegawais'])
                    ->where(function($q) {
                        $q->where('jenis_surat_id', 2)
                          ->orWhereNull('jenis_surat_id');
                    })
                    ->latest('tgl_surat')
                    ->latest('id')
                    ->paginate(10)
                    ->withQueryString();

        return view('surat.rekap', compact('surats', 'totalSpt', 'totalSppd', 'totalBulanIni'));
    }

    public function exportRekapPdf(Request $request)
    {
        // 1. Metrik total arsip keseluruhan (tetap dihitung sebagai referensi statistik)
        $totalSpt = Surat::where(function($q) {
            $q->where('jenis_surat_id', 2)
              ->orWhereNull('jenis_surat_id')
              ->orWhere('nomor_surat', 'like', '555/%');
        })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '')->count();

        $totalSppd = DB::table('surat_pegawai')->whereNotNull('nomor_sppd')->where('nomor_sppd', '!=', '')->count();
        
        $totalBulanIni = Surat::whereMonth('tgl_surat', now()->month)
                            ->whereYear('tgl_surat', now()->year)
                            ->count();

        // 2. Query data surat sesuai filter yang sedang aktif di halaman
        $query = Surat::with(['jenisSurat', 'pegawais']);

        $filterParts = [];
        $filterJenisLabel = null;

        // Filter Pencarian kata kunci (nomor surat, perihal, tujuan, uraian, personil)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%")
                    ->orWhere('uraian', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('pegawais', function($pq) use ($search) {
                        $pq->where('nama', 'like', "%{$search}%")
                           ->orWhere('nip', 'like', "%{$search}%")
                           ->orWhere('surat_pegawai.nomor_sppd', 'like', "%{$search}%");
                    });
            });
            $filterParts[] = 'Cari: "' . $search . '"';
        }

        // Filter Jenis Surat (SPT / SPPD)
        $filterJenis = $request->get('filter_jenis', $request->get('jenis'));
        if ($filterJenis === 'SPT') {
            $query->where(function($q) {
                $q->where('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id')
                  ->orWhere('nomor_surat', 'like', '555/%');
            })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
            $filterJenisLabel = 'SPT';
            $filterParts[] = 'Filter: Hanya SPT';
        } elseif ($filterJenis === 'SPPD') {
            $query->where('has_sppd', 1)
                  ->whereHas('pegawais', function($pq) {
                      $pq->whereNotNull('surat_pegawai.nomor_sppd')
                         ->where('surat_pegawai.nomor_sppd', '!=', '');
                  });
            $filterJenisLabel = 'SPPD';
            $filterParts[] = 'Filter: Hanya SPPD';
        }

        // Filter Tahun Arsip
        if ($request->filled('year')) {
            $query->whereYear('tgl_surat', $request->year);
            $filterParts[] = 'Tahun ' . $request->year;
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_surat', [$request->start_date, $request->end_date]);
            if ($request->start_date === now()->startOfMonth()->toDateString() && $request->end_date === now()->endOfMonth()->toDateString()) {
                $filterParts[] = 'Bulan Ini (' . \Carbon\Carbon::now()->translatedFormat('F Y') . ')';
            } else {
                $filterParts[] = 'Periode: ' . \Carbon\Carbon::parse($request->start_date)->translatedFormat('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($request->end_date)->translatedFormat('d/m/Y');
            }
        } elseif ($request->filled('start_date')) {
            $query->where('tgl_surat', '>=', $request->start_date);
            $filterParts[] = 'Mulai ' . \Carbon\Carbon::parse($request->start_date)->translatedFormat('d/m/Y');
        } elseif ($request->filled('end_date')) {
            $query->where('tgl_surat', '<=', $request->end_date);
            $filterParts[] = 'Sampai ' . \Carbon\Carbon::parse($request->end_date)->translatedFormat('d/m/Y');
        }

        $filterKeterangan = !empty($filterParts) ? implode(' | ', $filterParts) : null;

        $surats = $query->latest('tgl_surat')->latest('id')->get();

        $logoPath = public_path('images/logo-kominfo.jpg');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat.rekap-pdf', compact(
            'surats',
            'totalSpt',
            'totalSppd',
            'totalBulanIni',
            'logoBase64',
            'filterJenisLabel',
            'filterKeterangan'
        ))->setPaper('a4', 'portrait');

        $fileNameParts = ['Rekapitulasi_Surat'];
        if ($filterJenisLabel) {
            $fileNameParts[] = $filterJenisLabel;
        }
        if ($request->filled('year')) {
            $fileNameParts[] = $request->year;
        }
        $fileNameParts[] = date('Ymd_His');
        $fileName = implode('_', $fileNameParts) . '.pdf';

        return $pdf->download($fileName);
    }
}