<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratTelaah;
use App\Models\Surat;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;

class SuratTelaahController extends Controller
{
    /**
     * Tampilkan daftar arsip Surat Telaah.
     */
    public function index(Request $request)
    {
        $totalTelaah = SuratTelaah::count();

        $totalBulanIni = SuratTelaah::whereMonth('tanggal_telaah', now()->month)
                                    ->whereYear('tanggal_telaah', now()->year)
                                    ->count();

        // Ambil daftar tahun arsip distinct dari database + 5 tahun ke belakang
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $yearSql = $isSqlite ? "strftime('%Y', tanggal_telaah)" : "YEAR(tanggal_telaah)";
        $dbYears = SuratTelaah::selectRaw("{$yearSql} as yr")
            ->whereNotNull('tanggal_telaah')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->map(fn($y) => (int)$y)
            ->filter()
            ->toArray();

        $defaultYears = range((int)date('Y'), (int)date('Y') - 5);
        $availableYears = array_values(array_unique(array_merge($dbYears, $defaultYears)));
        rsort($availableYears);

        $suratTelaahs = SuratTelaah::with(['spt.pegawais', 'sppd', 'pegawais', 'creator'])
            ->filter($request)
            ->latest('tanggal_telaah')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('surat-telaah.index', compact(
            'suratTelaahs',
            'totalTelaah',
            'totalBulanIni',
            'availableYears'
        ));
    }

    /**
     * Export Rekapitulasi Surat Telaah ke format PDF (Landscape).
     */
    public function exportRekapPdf(Request $request)
    {
        $suratTelaahs = SuratTelaah::with(['spt.pegawais', 'sppd', 'pegawais', 'creator'])
            ->filter($request)
            ->latest('tanggal_telaah')
            ->latest('id')
            ->get();

        $totalTelaah = $suratTelaahs->count();
        $totalBulanIni = $suratTelaahs->filter(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_telaah)->isCurrentMonth() && \Carbon\Carbon::parse($item->tanggal_telaah)->isCurrentYear();
        })->count();

        // Judul Periode Dinamis
        $periodeText = 'Rekapitulasi Surat Telaah Keseluruhan';
        if ($request->filled('year')) {
            $periodeText = 'Rekapitulasi Surat Telaah — Tahun ' . $request->year;
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Telaah — {$startDateFormatted} s/d {$endDateFormatted}";
        } elseif ($request->filled('start_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Telaah — Mulai {$startDateFormatted}";
        } elseif ($request->filled('end_date')) {
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Telaah — Sampai {$endDateFormatted}";
        }

        $logoKominfoPath = file_exists(public_path('images/logo-kominfo.png')) 
            ? public_path('images/logo-kominfo.png') 
            : (file_exists(public_path('image/logo-kominfo.png')) ? public_path('image/logo-kominfo.png') : null);
        $logoKominfoBase64 = ($logoKominfoPath && file_exists($logoKominfoPath)) ? base64_encode(file_get_contents($logoKominfoPath)) : null;
        $logoBase64 = $logoKominfoBase64;

        $logoBonePath = file_exists(public_path('images/bonebolango.png')) 
            ? public_path('images/bonebolango.png') 
            : (file_exists(public_path('image/bonebolango.png')) ? public_path('image/bonebolango.png') : null);
        $logoBoneBase64 = ($logoBonePath && file_exists($logoBonePath)) ? base64_encode(file_get_contents($logoBonePath)) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat-telaah.rekap-pdf', compact(
            'suratTelaahs',
            'totalTelaah',
            'totalBulanIni',
            'logoBase64',
            'logoKominfoBase64',
            'logoBoneBase64',
            'periodeText'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Surat_Telaah_Bone_Bolango_'.date('Ymd_His').'.pdf');
    }

    /**
     * Tampilkan form pembuatan Surat Telaah baru.
     */
    public function create()
    {
        // Ambil daftar SPT resmi yang tersedia di database
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

        $pegawais = Pegawai::whereNotIn('id', [1, 2, 3])->orderByHierarki()->get();
        $nomorUrut = str_pad(SuratTelaah::getNextSequence(date('Y')), 2, '0', STR_PAD_LEFT);

        return view('surat-telaah.create', compact('spts', 'pegawais', 'nomorUrut'));
    }

    /**
     * Simpan Surat Telaah baru ke database.
     */
    public function store(Request $request)
    {
        $modeNomor = $request->input('mode_nomor', 'otomatis');

        $rules = [
            'tanggal_telaah' => 'required|date',
            'spt_id'         => 'nullable|exists:surats,id',
            'sppd_id'        => 'nullable|exists:surats,id',
            'uraian'         => 'required|string',
            'tujuan'         => 'required|string|max:255',
            'keterangan'     => 'nullable|string|max:500',
            'pegawai_id'     => 'required|array|min:1',
            'pegawai_id.*'   => 'exists:pegawais,id',
        ];

        if ($modeNomor === 'manual') {
            $rules['nomor_manual'] = 'required|string|max:255|unique:surat_telaahs,nomor_telaah';
        }

        $messages = [
            'nomor_manual.required'   => 'Nomor surat telaah manual wajib diisi jika memilih mode manual.',
            'nomor_manual.unique'     => 'Nomor surat telaah ini sudah terdaftar di database. Silakan gunakan nomor lain.',
            'tanggal_telaah.required' => 'Tanggal telaah wajib diisi.',
            'tanggal_telaah.date'     => 'Format tanggal telaah tidak valid.',
            'uraian.required'         => 'Uraian / maksud telaahan staf wajib diisi.',
            'tujuan.required'         => 'Tujuan tugas / instansi wajib diisi.',
            'tujuan.max'              => 'Tujuan tugas maksimal 255 karakter.',
            'keterangan.max'          => 'Keterangan tambahan maksimal 500 karakter.',
            'pegawai_id.required'     => 'Pilih minimal 1 personil yang ditugaskan.',
            'pegawai_id.min'          => 'Pilih minimal 1 personil yang ditugaskan.',
            'pegawai_id.*.exists'     => 'Data personil yang dipilih tidak valid.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($modeNomor === 'manual') {
            $nomorTelaah = trim($validated['nomor_manual']);
        } else {
            $nomorTelaah = SuratTelaah::generateNomor($validated['tanggal_telaah']);
        }

        $telaah = SuratTelaah::create([
            'nomor_telaah'   => $nomorTelaah,
            'tanggal_telaah' => $validated['tanggal_telaah'],
            'spt_id'         => $validated['spt_id'] ?? null,
            'sppd_id'        => $validated['sppd_id'] ?? null,
            'uraian'         => $validated['uraian'],
            'tujuan'         => $validated['tujuan'],
            'keterangan'     => $validated['keterangan'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        if (!empty($request->pegawai_id)) {
            $telaah->pegawais()->sync($request->pegawai_id);
        }

        return redirect()->route('surat.telaah')->with('success', 'Surat Telaah nomor ' . $telaah->nomor_telaah . ' berhasil diterbitkan dan diarsipkan.');
    }

    /**
     * Tampilkan detail Surat Telaah.
     */
    public function show($id)
    {
        $telaah = SuratTelaah::with(['spt.pegawais', 'sppd', 'pegawais', 'creator'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'id'             => $telaah->id,
                'nomor_telaah'   => $telaah->nomor_telaah,
                'tanggal_telaah' => $telaah->tanggal_telaah->format('Y-m-d'),
                'tanggal_formatted' => \Carbon\Carbon::parse($telaah->tanggal_telaah)->translatedFormat('d F Y'),
                'uraian'         => $telaah->uraian,
                'tujuan'         => $telaah->tujuan,
                'keterangan'     => $telaah->keterangan ?? '-',
                'spt_nomor'      => $telaah->spt ? $telaah->spt->nomor_surat : '-',
                'spt_tgl'        => $telaah->spt ? \Carbon\Carbon::parse($telaah->spt->tgl_surat)->translatedFormat('d F Y') : '-',
                'pegawais'       => $telaah->pegawais->map(function ($p) {
                    return [
                        'nama'    => $p->nama,
                        'nip'     => $p->nip,
                        'jabatan' => $p->jabatan,
                        'kategori'=> $p->kategori_pegawai ?? 'ASN',
                    ];
                }),
            ]);
        }

        return view('surat-telaah.show', compact('telaah'));
    }

    /**
     * Hapus Surat Telaah dari arsip.
     */
    public function destroy($id)
    {
        $telaah = SuratTelaah::findOrFail($id);
        $nomor = $telaah->nomor_telaah;
        $telaah->pegawais()->detach();
        $telaah->delete();

        return redirect()->route('surat.telaah')->with('success', "Surat Telaah nomor {$nomor} berhasil dihapus dari arsip.");
    }
}
