<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Http\Requests\StorePegawaiP3kRequest;
use Illuminate\Support\Facades\DB;

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

    protected function getNextSptCounter()
    {
        $maxSpt = Surat::where(function($q) {
                $q->where('nomor_surat', 'like', '555/%')
                  ->orWhere('jenis_surat_id', 2)
                  ->orWhereNull('jenis_surat_id');
            })->get()
            ->map(function ($item) {
                $parts = explode('/', $item->nomor_surat);
                return isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;
            })->max() ?? 0;

        return $maxSpt + 1;
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
                    ->paginate(5)
                    ->withQueryString();

        $allPegawais = \App\Models\Pegawai::whereNotIn('id', [1, 2, 3])->get();
        $nextSppdCounter = $this->getNextSppdCounter();

        return view('surat.index', compact('surats', 'totalSpt', 'totalSppd', 'totalBulanIni', 'allPegawais', 'nextSppdCounter'));
    }

    public function getNextSppdCounterApi()
    {
        return response()->json([
            'next_counter' => $this->getNextSppdCounter()
        ]);
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

        $nextSpt = $this->getNextSptCounter();
        $nomorUrutSpt = str_pad($nextSpt, 3, '0', STR_PAD_LEFT);

        $nextSppd = $this->getNextSppdCounter();
        $nomorUrutSppd = str_pad($nextSppd, 3, '0', STR_PAD_LEFT);

        $nomorUrut = ($jenisSuratId == 1) ? $nomorUrutSppd : $nomorUrutSpt;

        return view('surat.create', compact('nomorUrut', 'nomorUrutSpt', 'nomorUrutSppd', 'jenisSuratId', 'jenisSurats'));
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

        // Saring pegawai agar akun Admin Master, Admin Kasubag, Pegawai Biasa, dan Bendahara Barang tidak ikut terpanggil
        $pegawais = \App\Models\Pegawai::whereNotIn('nama', [
                'Admin Master',
                'Admin Kasubag',
                'Pegawai',
                'Pegawai Biasa',
                'Bendahara Barang',
                'Bendahara',
            ])
            ->whereNotIn('id', [1, 2, 3])
            ->orderBy('id', 'asc')
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
                'pegawai_id.required' => 'Minimal harus memilih satu personel yang ditugaskan.',
                'pegawai_id.min' => 'Minimal harus memilih satu personel yang ditugaskan.',
                'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
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

        $tglSurat = session('s_tgl_surat', now()->format('Y-m-d'));
        $d = new \DateTime($tglSurat);
        $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bln = $romawiBulan[$d->format('n') - 1];
        $thn = $d->format('Y');

        $startCounter = $this->getNextSppdCounter();
        $sppdPreviews = [];
        foreach ($selectedPegawais as $pegawai) {
            $noUrut = str_pad($startCounter, 3, '0', STR_PAD_LEFT);
            $sppdPreviews[$pegawai->id] = "090/{$noUrut}/{$bln}/{$thn}";
            $startCounter++;
        }

        return view('surat.create-step-3', compact('selectedPegawais', 'sppdPreviews'));
    }

    public function show($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        return view('surat.show', compact('surat'));
    }

    public function edit($id)
    {
        $surat = Surat::with(['jenisSurat', 'pegawais'])->findOrFail($id);
        $pegawais = \App\Models\Pegawai::whereNotIn('nama', [
                'Admin Master',
                'Admin Kasubag',
                'Pegawai',
                'Pegawai Biasa',
                'Bendahara Barang',
                'Bendahara',
            ])
            ->whereNotIn('id', [1, 2, 3])
            ->orderBy('id', 'asc')
            ->get();
        return view('surat.edit', compact('surat', 'pegawais'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:150',
        ], [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
        ]);

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

        // Tentukan status: jika sebelumnya Draft dan diedit/dilengkapi, perbarui menjadi Terbit
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
            // generate nomor baru yang increment dari nomor tertinggi di database
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
        $request->validate([
            'keterangan' => 'nullable|string|max:150',
        ], [
            'keterangan.max' => 'Keterangan tambahan maksimal 150 karakter.',
        ]);

        $tglSurat = session('s_tgl_surat', $request->tgl_surat ?? now()->format('Y-m-d'));
        $tujuan = session('s_tujuan', $request->tujuan ?? 'Kementerian Dalam Negeri, Jakarta');
        $jenisSuratId = session('s_jenis_surat_id', $request->jenis_surat_id ?? 2);
        $uraian = session('s_uraian', $request->uraian);
        $keterangan = session('s_keterangan', $request->keterangan);
        $pegawaiIds = session('s_pegawai_id', $request->pegawai_id ?? []);
        
        $buatSppd = $request->has('has_sppd') ? $request->input('has_sppd') : 0;

        $jenisSurat = \App\Models\JenisSurat::find($jenisSuratId);
        $kodeSurat = $jenisSurat ? $jenisSurat->kode : (($jenisSuratId == 1) ? '090' : '555');

        if ($jenisSuratId == 1) {
            $countUrut = $this->getNextSppdCounter();
        } else {
            $countUrut = $this->getNextSptCounter();
        }
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
            'status' => 'Terbit',
            'created_by' => auth()->id() ?? 1,
        ]);

        if (!empty($pegawaiIds)) {
            $attachData = [];
            $sppdCounter = $this->getNextSppdCounter();
            $nomorSppdInputs = $request->input('nomor_sppd', []);

            foreach ($pegawaiIds as $pId) {
                $nomorSppd = null;
                if ((int)$buatSppd === 1) {
                    if (!empty($nomorSppdInputs[$pId])) {
                        $nomorSppd = $nomorSppdInputs[$pId];
                    } else {
                        $sppdUrut = str_pad($sppdCounter, 3, '0', STR_PAD_LEFT);
                        $nomorSppd = "090/{$sppdUrut}/{$bln}/{$thn}";
                        $sppdCounter++;
                    }
                }
                $attachData[$pId] = [
                    'nomor_sppd' => $nomorSppd,
                ];
            }
            $surat->pegawais()->sync($attachData);
        }

        session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd']);

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
            $jenisSuratId = $request->input('jenis_surat_id', session('s_jenis_surat_id', 2));

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

            $jenisSurat = \App\Models\JenisSurat::find($jenisSuratId);
            $kodeSurat = $jenisSurat ? $jenisSurat->kode : (($jenisSuratId == 1) ? '090' : '555');

            if ($jenisSuratId == 1) {
                $countUrut = $this->getNextSppdCounter();
            } else {
                $countUrut = $this->getNextSptCounter();
            }
            $noUrut = str_pad($countUrut, 3, '0', STR_PAD_LEFT);

            $d = new \DateTime($tglSurat);
            $romawiBulan = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $bln = $romawiBulan[$d->format('n') - 1];
            $thn = $d->format('Y');

            $nomorSuratOtomatis = "{$kodeSurat}/{$noUrut}/{$bln}/{$thn}";
            $nomorSurat = $request->filled('nomor_surat') ? $request->input('nomor_surat') : (session('s_nomor_surat') ?: $nomorSuratOtomatis);

            // Cek keunikan nomor surat di database agar tidak duplikat
            $attempt = $countUrut;
            while (Surat::where('nomor_surat', $nomorSurat)->exists()) {
                $attempt++;
                $noUrut = str_pad($attempt, 3, '0', STR_PAD_LEFT);
                $nomorSurat = "{$kodeSurat}/{$noUrut}/{$bln}/{$thn}";
            }

            $perihal = $request->filled('perihal') ? $request->input('perihal') : (session('s_perihal') ?: ('Draft ' . ($jenisSurat->nama_jenis ?? 'Surat Tugas')));

            $surat = Surat::create([
                'jenis_surat_id' => $jenisSuratId,
                'nomor_surat' => $nomorSurat,
                'perihal' => $perihal,
                'tgl_surat' => $tglSurat,
                'tujuan' => $tujuan,
                'uraian' => $uraian,
                'keterangan' => $keterangan,
                'has_sppd' => 0,
                'status' => 'Draft',
                'created_by' => auth()->id() ?? 1,
            ]);

            if (!empty($pegawaiIds)) {
                $surat->pegawais()->sync(array_filter($pegawaiIds));
            }

            session()->forget(['s_tgl_surat', 's_tujuan', 's_jenis_surat_id', 's_nomor_surat', 's_uraian', 's_keterangan', 's_pegawai_id', 's_buat_sppd']);

            return redirect()->route('surat.index')->with('success', 'Draft surat berhasil disimpan!');
        } catch (\Throwable $e) {
            \Log::error('Gagal simpan draft surat: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan draft ke database: ' . $e->getMessage());
        }
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
        return $this->index($request);
    }

    public function exportRekapPdf(Request $request)
    {
        $query = Surat::with(['jenisSurat', 'pegawais'])
                    ->when($request->filled('search'), function($q) use ($request) {
                        $search = $request->search;
                        return $q->where(function($sq) use ($search) {
                            $sq->where('nomor_surat', 'like', "%{$search}%")
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
                    ->when($request->filled('filter_jenis') || $request->filled('jenis'), function($q) use ($request) {
                        $jenis = $request->get('filter_jenis', $request->get('jenis'));
                        if ($jenis === 'SPT') {
                            return $q->where(function($sq) {
                                $sq->where('jenis_surat_id', 2)
                                  ->orWhereNull('jenis_surat_id')
                                  ->orWhere('nomor_surat', 'like', '555/%');
                            })->whereNotNull('nomor_surat')->where('nomor_surat', '!=', '');
                        } elseif ($jenis === 'SPPD') {
                            return $q->where('has_sppd', 1)
                                     ->whereHas('pegawais', function($pq) {
                                         $pq->whereNotNull('surat_pegawai.nomor_sppd')
                                            ->where('surat_pegawai.nomor_sppd', '!=', '');
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
                    ->latest('tgl_surat')
                    ->latest('id');

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
}