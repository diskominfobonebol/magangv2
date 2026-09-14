<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;
use App\Http\Requests\StoreSuratMasukRequest;
use App\Http\Requests\UpdateSuratMasukRequest;
use Carbon\Carbon;

class SuratMasukController extends Controller
{
    /**
     * Tampilkan daftar rekapitulasi Surat Masuk.
     * Dapat diakses oleh Admin Master (Role 1) dan Admin Kasubag (Role 2).
     */
    public function index(Request $request)
    {
        $totalSuratMasuk = SuratMasuk::count();

        $totalBulanIni = SuratMasuk::whereMonth('tanggal_surat', now()->month)
                                   ->whereYear('tanggal_surat', now()->year)
                                   ->count();

        $totalTahunIni = SuratMasuk::whereYear('tanggal_surat', now()->year)
                                   ->count();

        $suratMasuks = SuratMasuk::with(['creator', 'updater'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                return $query->where(function ($q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                      ->orWhere('asal_surat', 'like', "%{$search}%")
                      ->orWhere('uraian', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('year'), function ($query) use ($request) {
                return $query->whereYear('tanggal_surat', $request->year);
            })
            ->when(!$request->filled('year') && $request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                return $query->whereBetween('tanggal_surat', [$request->start_date, $request->end_date]);
            })
            ->when(!$request->filled('year') && $request->filled('start_date') && !$request->filled('end_date'), function ($query) use ($request) {
                return $query->whereDate('tanggal_surat', '>=', $request->start_date);
            })
            ->when(!$request->filled('year') && !$request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                return $query->whereDate('tanggal_surat', '<=', $request->end_date);
            })
            ->latest('tanggal_surat')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('surat-masuk.index', compact(
            'suratMasuks',
            'totalSuratMasuk',
            'totalBulanIni',
            'totalTahunIni'
        ));
    }

    /**
     * Export Rekapitulasi Surat Masuk ke format PDF (Landscape).
     * Dapat diakses oleh Admin Master (Role 1) dan Admin Kasubag (Role 2).
     */
    public function exportPdf(Request $request)
    {
        $query = SuratMasuk::with(['creator', 'updater'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                return $q->where(function ($sq) use ($search) {
                    $sq->where('nomor_surat', 'like', "%{$search}%")
                      ->orWhere('asal_surat', 'like', "%{$search}%")
                      ->orWhere('uraian', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('year'), function ($q) use ($request) {
                return $q->whereYear('tanggal_surat', $request->year);
            })
            ->when(!$request->filled('year') && $request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                return $q->whereBetween('tanggal_surat', [$request->start_date, $request->end_date]);
            })
            ->when(!$request->filled('year') && $request->filled('start_date') && !$request->filled('end_date'), function ($q) use ($request) {
                return $q->whereDate('tanggal_surat', '>=', $request->start_date);
            })
            ->when(!$request->filled('year') && !$request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                return $q->whereDate('tanggal_surat', '<=', $request->end_date);
            })
            ->latest('tanggal_surat')
            ->latest('id');

        $suratMasuks = $query->get();

        // Judul Periode Dinamis
        $periodeText = 'Rekapitulasi Surat Masuk Keseluruhan';
        if ($request->filled('year')) {
            $periodeText = 'Rekapitulasi Surat Masuk — Tahun ' . $request->year;
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            if ($startDate->format('Y-m') === $endDate->format('Y-m') && $startDate->format('d') === '01' && $endDate->format('d') === $startDate->copy()->endOfMonth()->format('d')) {
                $periodeText = 'Rekapitulasi Surat Masuk — Bulan ' . $startDate->translatedFormat('F Y');
            } else {
                $startDateFormatted = $startDate->translatedFormat('d M Y');
                $endDateFormatted = $endDate->translatedFormat('d M Y');
                $periodeText = "Rekapitulasi Surat Masuk — {$startDateFormatted} s/d {$endDateFormatted}";
            }
        } elseif ($request->filled('start_date')) {
            $startDateFormatted = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Masuk — Mulai {$startDateFormatted}";
        } elseif ($request->filled('end_date')) {
            $endDateFormatted = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d M Y');
            $periodeText = "Rekapitulasi Surat Masuk — Sampai {$endDateFormatted}";
        }

        // Metrik Ringkasan Rekap
        $totalSuratMasuk = SuratMasuk::count();
        $totalBulanIni = SuratMasuk::whereMonth('tanggal_surat', now()->month)
                                   ->whereYear('tanggal_surat', now()->year)
                                   ->count();
        $totalTahunIni = SuratMasuk::whereYear('tanggal_surat', now()->year)
                                   ->count();

        $logoBonePath = file_exists(public_path('images/bonebolango.png')) 
            ? public_path('images/bonebolango.png') 
            : (file_exists(public_path('image/bonebolango.png')) ? public_path('image/bonebolango.png') : null);
        $logoBoneBase64 = ($logoBonePath && file_exists($logoBonePath)) ? base64_encode(file_get_contents($logoBonePath)) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('surat-masuk.rekap-pdf', compact(
            'suratMasuks', 
            'totalSuratMasuk', 
            'totalBulanIni', 
            'totalTahunIni', 
            'logoBoneBase64', 
            'periodeText'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Rekapitulasi_Surat_Masuk_Bone_Bolango_'.date('Ymd_His').'.pdf');
    }

    /**
     * Form penambahan data Surat Masuk baru.
     * Khusus Admin Kasubag (Role 2).
     */
    public function create()
    {
        $this->authorizeKasubag();

        $daftarAsalSurat = SuratMasuk::select('asal_surat')
            ->whereNotNull('asal_surat')
            ->where('asal_surat', '!=', '')
            ->distinct()
            ->orderBy('asal_surat', 'asc')
            ->pluck('asal_surat');

        return view('surat-masuk.create', compact('daftarAsalSurat'));
    }

    /**
     * Simpan data Surat Masuk baru ke database.
     * Khusus Admin Kasubag (Role 2).
     */
    public function store(StoreSuratMasukRequest $request)
    {
        $this->authorizeKasubag();

        SuratMasuk::create([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'asal_surat' => $request->asal_surat,
            'uraian' => $request->uraian,
            'keterangan' => $request->keterangan,
            'link_google_drive' => $request->link_google_drive,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('surat-masuk.index')->with('success', 'Data Surat Masuk berhasil ditambahkan!');
    }

    /**
     * Tampilkan rincian Surat Masuk.
     * Dapat diakses oleh Admin Master (Role 1) dan Admin Kasubag (Role 2).
     */
    public function show($id)
    {
        $suratMasuk = SuratMasuk::with(['creator', 'updater'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $suratMasuk->id,
                    'nomor_surat' => $suratMasuk->nomor_surat,
                    'tanggal_surat' => $suratMasuk->tanggal_surat->format('Y-m-d'),
                    'tanggal_formatted' => $suratMasuk->tanggal_surat->translatedFormat('d F Y'),
                    'asal_surat' => $suratMasuk->asal_surat,
                    'uraian' => $suratMasuk->uraian,
                    'keterangan' => $suratMasuk->keterangan ?? '-',
                    'link_google_drive' => $suratMasuk->link_google_drive,
                    'created_by_name' => $suratMasuk->creator->name ?? 'Admin Kasubag',
                    'created_at_formatted' => $suratMasuk->created_at ? $suratMasuk->created_at->translatedFormat('d F Y, H:i') : '-',
                    'updated_at_formatted' => $suratMasuk->updated_at ? $suratMasuk->updated_at->translatedFormat('d F Y, H:i') : '-',
                ]
            ]);
        }

        return view('surat-masuk.show', compact('suratMasuk'));
    }

    /**
     * Form edit data Surat Masuk.
     * Khusus Admin Kasubag (Role 2).
     */
    public function edit($id)
    {
        $this->authorizeKasubag();
        $suratMasuk = SuratMasuk::findOrFail($id);

        $daftarAsalSurat = SuratMasuk::select('asal_surat')
            ->whereNotNull('asal_surat')
            ->where('asal_surat', '!=', '')
            ->distinct()
            ->orderBy('asal_surat', 'asc')
            ->pluck('asal_surat');

        return view('surat-masuk.edit', compact('suratMasuk', 'daftarAsalSurat'));
    }

    /**
     * Perbarui data Surat Masuk di database.
     * Khusus Admin Kasubag (Role 2).
     */
    public function update(UpdateSuratMasukRequest $request, $id)
    {
        $this->authorizeKasubag();
        $suratMasuk = SuratMasuk::findOrFail($id);

        $suratMasuk->update([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'asal_surat' => $request->asal_surat,
            'uraian' => $request->uraian,
            'keterangan' => $request->keterangan,
            'link_google_drive' => $request->link_google_drive,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('surat-masuk.index')->with('success', 'Data Surat Masuk berhasil diperbarui!');
    }

    /**
     * Hapus data Surat Masuk dari database.
     * Khusus Admin Kasubag (Role 2).
     */
    public function destroy($id)
    {
        $this->authorizeKasubag();
        $suratMasuk = SuratMasuk::findOrFail($id);
        $suratMasuk->delete();

        return redirect()->route('surat-masuk.index')->with('success', 'Data Surat Masuk berhasil dihapus!');
    }

    /**
     * Helper proteksi hak akses khusus Kasubag (Role 2).
     */
    protected function authorizeKasubag()
    {
        if (!auth()->check() || (int) auth()->user()->role_id !== 2) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Admin Kasubag Kepegawaian.');
        }
    }
}
