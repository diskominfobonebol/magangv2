<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratSk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratSkController extends Controller
{
    /**
     * Tampilkan daftar arsip Surat Keputusan (SK).
     */
    public function index(Request $request)
    {
        $totalSk = SuratSk::count();

        $totalBulanIni = SuratSk::whereMonth('tanggal_sk', now()->month)
                                ->whereYear('tanggal_sk', now()->year)
                                ->count();

        // Ambil daftar tahun arsip distinct dari database + 5 tahun ke belakang
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $yearSql = $isSqlite ? "strftime('%Y', tanggal_sk)" : "YEAR(tanggal_sk)";
        $dbYears = SuratSk::selectRaw("{$yearSql} as yr")
            ->whereNotNull('tanggal_sk')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->map(fn($y) => (int)$y)
            ->filter()
            ->toArray();

        $defaultYears = range((int)date('Y'), (int)date('Y') - 5);
        $availableYears = array_values(array_unique(array_merge($dbYears, $defaultYears)));
        rsort($availableYears);

        $suratSks = SuratSk::with(['creator', 'updater'])
            ->filter($request)
            ->latest('tanggal_sk')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('surat-sk.index', compact(
            'suratSks',
            'totalSk',
            'totalBulanIni',
            'availableYears'
        ));
    }

    /**
     * Export Rekapitulasi Surat SK ke format PDF (Landscape).
     */
    public function exportRekapPdf(Request $request)
    {
        $suratSks = SuratSk::with(['creator', 'updater'])
            ->filter($request)
            ->latest('tanggal_sk')
            ->latest('id')
            ->get();

        $totalSk = $suratSks->count();
        $totalBulanIni = $suratSks->filter(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_sk)->isCurrentMonth() && \Carbon\Carbon::parse($item->tanggal_sk)->isCurrentYear();
        })->count();

        // Judul Periode Dinamis
        $periodeText = 'Rekapitulasi Surat Keputusan (SK) Keseluruhan';
        if ($request->filled('year')) {
            $periodeText = 'Rekapitulasi Surat Keputusan (SK) — Tahun ' . $request->year;
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keputusan (SK) — {$startDateFormatted} s/d {$endDateFormatted}";
        } elseif ($request->filled('start_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keputusan (SK) — Mulai {$startDateFormatted}";
        } elseif ($request->filled('end_date')) {
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Keputusan (SK) — Sampai {$endDateFormatted}";
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat-sk.rekap-pdf', compact(
            'suratSks',
            'totalSk',
            'totalBulanIni',
            'logoBase64',
            'logoKominfoBase64',
            'logoBoneBase64',
            'periodeText'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Surat_SK_Bone_Bolango_'.date('Ymd_His').'.pdf');
    }

    /**
     * Tampilkan form pembuatan Surat SK baru.
     */
    public function create()
    {
        $nomorUrut = str_pad(SuratSk::getNextSequence(date('Y')), 2, '0', STR_PAD_LEFT);
        return view('surat-sk.create', compact('nomorUrut'));
    }

    /**
     * Simpan Surat SK baru ke database beserta upload file.
     */
    public function store(Request $request)
    {
        $modeNomor = $request->input('mode_nomor', 'otomatis');

        $rules = [
            'tanggal_sk' => 'required|date',
            'tentang'    => 'required|string',
            'keterangan' => 'nullable|string|max:500',
            'file_sk'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];

        if ($modeNomor === 'manual') {
            $rules['nomor_manual'] = 'required|string|max:255|unique:surat_sks,nomor_sk';
        }

        $messages = [
            'nomor_manual.required' => 'Nomor Surat SK manual wajib diisi jika memilih mode manual.',
            'nomor_manual.unique'   => 'Nomor Surat SK ini sudah terdaftar di database. Silakan gunakan nomor lain.',
            'tanggal_sk.required'   => 'Tanggal SK wajib diisi.',
            'tentang.required'      => 'Hal / Tentang SK wajib diisi.',
            'file_sk.mimes'         => 'File SK harus berformat PDF, Word (DOC/DOCX), atau Gambar (JPG/PNG).',
            'file_sk.max'           => 'Ukuran file SK maksimal 10 MB.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($modeNomor === 'manual') {
            $nomorSk = trim($validated['nomor_manual']);
        } else {
            $nomorSk = SuratSk::generateNomor($validated['tanggal_sk']);
        }

        $filePath = null;
        if ($request->hasFile('file_sk')) {
            $filePath = $request->file('file_sk')->store('surat-sk', 'public');
        }

        $sk = SuratSk::create([
            'nomor_sk'   => $nomorSk,
            'tanggal_sk' => $validated['tanggal_sk'],
            'tentang'    => $validated['tentang'],
            'keterangan' => $validated['keterangan'] ?? null,
            'file_sk'    => $filePath,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('surat.sk')->with('success', 'Surat Keputusan (SK) nomor ' . $sk->nomor_sk . ' berhasil diterbitkan dan diarsipkan.');
    }

    /**
     * Tampilkan detail Surat SK.
     */
    public function show($id)
    {
        $sk = SuratSk::with(['creator', 'updater'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'id'             => $sk->id,
                'nomor_sk'       => $sk->nomor_sk,
                'tanggal_sk'     => $sk->tanggal_sk->format('Y-m-d'),
                'tanggal_formatted' => \Carbon\Carbon::parse($sk->tanggal_sk)->translatedFormat('d F Y'),
                'tentang'        => $sk->tentang,
                'keterangan'     => $sk->keterangan ?? '-',
                'file_url'       => $sk->file_sk ? Storage::disk('public')->url($sk->file_sk) : null,
            ]);
        }

        return view('surat-sk.show', compact('sk'));
    }

    /**
     * Unduh file Surat SK.
     */
    public function download($id)
    {
        $sk = SuratSk::findOrFail($id);

        if (!$sk->file_sk || !Storage::disk('public')->exists($sk->file_sk)) {
            return back()->with('error', 'Berkas dokumen fisik SK tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($sk->file_sk, 'SK_' . str_replace(['/', '\\'], '_', $sk->nomor_sk) . '.' . pathinfo($sk->file_sk, PATHINFO_EXTENSION));
    }

    /**
     * Hapus Surat SK beserta filenya.
     */
    public function destroy($id)
    {
        $sk = SuratSk::findOrFail($id);
        $nomor = $sk->nomor_sk;

        if ($sk->file_sk && Storage::disk('public')->exists($sk->file_sk)) {
            Storage::disk('public')->delete($sk->file_sk);
        }

        $sk->delete();

        return redirect()->route('surat.sk')->with('success', "Surat Keputusan (SK) nomor {$nomor} berhasil dihapus dari arsip.");
    }
}
