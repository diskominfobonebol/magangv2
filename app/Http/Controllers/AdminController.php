<?php

namespace App\Http\Controllers;

use App\Models\AsetPeralatanMesin;
use App\Models\PendaftaranMagang;
use App\Models\SuratBalasanMagang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Terapkan seluruh filter aset secara seragam untuk index dan export PDF
     */
    protected function applyAsetFilters($query, Request $request)
    {
        // 1. Pencarian Teks Bebas (Kata Kunci)
        $search = $request->query('search') ?: $request->query('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_reg_pemda', 'like', "%{$search}%")
                  ->orWhere('no_reg_kominfo', 'like', "%{$search}%")
                  ->orWhere('jenis_barang', 'like', "%{$search}%")
                  ->orWhere('merek_tipe', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('keterangan_lokasi_unit', 'like', "%{$search}%")
                  ->orWhere('no_sk_bast', 'like', "%{$search}%");
            });
        }

        // 2. Filter Kategori / Jenis Barang
        $kategori = $request->query('kategori') ?: $request->query('jenis_barang');
        if ($kategori && $kategori !== 'all') {
            $query->where('jenis_barang', $kategori);
        }

        // 3. Filter Kondisi / Status Aset
        $kondisi = $request->query('kondisi') ?: ($request->query('filter_kondisi') ?: $request->query('status'));
        if ($kondisi && $kondisi !== 'all') {
            $query->where('kondisi', $kondisi);
        }

        // 4. Filter Keterangan Unit Lokasi
        $lokasi = $request->query('lokasi') ?: $request->query('keterangan_lokasi_unit');
        if ($lokasi && $lokasi !== 'all') {
            $query->where('keterangan_lokasi_unit', $lokasi);
        }

        // 5. Filter Tahun Perolehan
        $tahunPerolehan = $request->query('tahun_perolehan') ?: $request->query('tahun');
        if ($tahunPerolehan && $tahunPerolehan !== 'all') {
            $query->where('tahun', $tahunPerolehan);
        }

        // 6. Filter Bulan & Tahun Created At
        $filterBulan = $request->query('filter_bulan');
        if ($filterBulan && $filterBulan !== 'all') {
            $query->whereMonth('created_at', $filterBulan);
        }

        $filterTahun = $request->query('filter_tahun');
        if ($filterTahun && $filterTahun !== 'all') {
            $query->whereYear('created_at', $filterTahun);
        }

        // 7. Filter Rentang Tanggal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->query('tanggal_akhir'));
        }

        return $query;
    }

    // Halaman Manajemen Aset & Laporan Rekap
    public function aset(Request $request)
    {
        // 1. Query Master Data Aset (dengan filter terpadu)
        $queryAset = AsetPeralatanMesin::query();
        $this->applyAsetFilters($queryAset, $request);
        $aset = $queryAset->orderBy('created_at', 'desc')->paginate(10, ['*'], 'aset_page')->withQueryString();

        $filterBulan = $request->query('filter_bulan', 'all');
        $filterTahun = $request->query('filter_tahun', date('Y'));
        $filterKondisi = $request->query('filter_kondisi', $request->query('kondisi', 'all'));

        // 2. Data Laporan Rekap Bulanan:
        // Base query untuk periode bulan & tahun (tidak difilter kondisi agar 5 kotak statistik & grafik selalu utuh)
        $queryPeriode = AsetPeralatanMesin::query();
        if ($filterBulan && $filterBulan !== 'all') {
            $queryPeriode->whereMonth('created_at', $filterBulan);
        }
        if ($filterTahun && $filterTahun !== 'all') {
            $queryPeriode->whereYear('created_at', $filterTahun);
        }

        $allPeriodData = $queryPeriode->get();

        $rekapKondisi = [
            'Baik' => $allPeriodData->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $allPeriodData->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat' => $allPeriodData->where('kondisi', 'Rusak Berat')->count(),
            'Hilang' => $allPeriodData->where('kondisi', 'Hilang')->count(),
            'Total' => $allPeriodData->count(),
        ];

        // Tabel data laporan: saring data tabel jika ada filter kondisi aktif dengan limit 10 baris per halaman
        $queryTabelLaporan = clone $queryPeriode;
        if ($filterKondisi && $filterKondisi !== 'all') {
            $queryTabelLaporan->where('kondisi', $filterKondisi);
        }
        $laporanAset = $queryTabelLaporan->orderBy('created_at', 'desc')->paginate(10, ['*'], 'laporan_page')->withQueryString();

        // Opsi filter untuk dropdown di frontend Blade
        $listKategori = AsetPeralatanMesin::select('jenis_barang')->whereNotNull('jenis_barang')->where('jenis_barang', '!=', '')->distinct()->orderBy('jenis_barang')->pluck('jenis_barang');
        $listLokasi = AsetPeralatanMesin::select('keterangan_lokasi_unit')->whereNotNull('keterangan_lokasi_unit')->where('keterangan_lokasi_unit', '!=', '')->distinct()->orderBy('keterangan_lokasi_unit')->pluck('keterangan_lokasi_unit');
        $listTahun = AsetPeralatanMesin::select('tahun')->whereNotNull('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('auth.aset', compact(
            'aset', 
            'laporanAset', 
            'rekapKondisi', 
            'filterBulan', 
            'filterTahun',
            'filterKondisi',
            'listKategori',
            'listLokasi',
            'listTahun'
        ));
    }

    // Tombol Tambah Aset
    public function storeAset(Request $request)
    {
        if ($request->has('harga_perolehan') && is_string($request->harga_perolehan)) {
            $cleanPrice = preg_replace('/[^\d]/', '', $request->harga_perolehan);
            $request->merge(['harga_perolehan' => $cleanPrice !== '' ? (int)$cleanPrice : null]);
        }

        $request->validate([
            'no_reg_pemda'           => 'nullable|string|max:100',
            'no_reg_kominfo'         => 'nullable|string|max:100',
            'penanggung_jawab'       => 'required|string|max:100',
            'jenis_barang'           => 'required|string|max:100',
            'merek_tipe'             => 'required|string|max:100',
            'tahun'                  => 'required|digits:4',
            'harga_perolehan'        => 'required|numeric|min:0|max:999000000',
            'kondisi'                => 'required|in:Baik,Hilang,Rusak Ringan,Rusak Berat',
            'keterangan_lokasi_unit' => 'required|string|max:150',
            'no_sk_bast'             => 'nullable|string|max:150',
            'no_rangka_seri'         => 'nullable|string|max:100',
            'no_mesin'               => 'nullable|string|max:100',
            'no_polisi'              => 'nullable|string|max:50',
            'no_bpkb'                => 'nullable|string|max:50',
        ], [
            'penanggung_jawab.required'         => 'Penanggung Jawab wajib diisi.',
            'jenis_barang.required'             => 'Jenis Barang wajib diisi.',
            'merek_tipe.required'               => 'Merek / Tipe Aset wajib diisi.',
            'tahun.required'                    => 'Tahun Perolehan wajib diisi.',
            'tahun.digits'                      => 'Tahun Perolehan harus 4 digit angka valid (contoh: 2026).',
            'harga_perolehan.required'          => 'Harga Perolehan wajib diisi.',
            'harga_perolehan.numeric'           => 'Harga Perolehan harus berupa angka valid.',
            'harga_perolehan.min'               => 'Harga Perolehan tidak boleh kurang dari 0.',
            'harga_perolehan.max'               => 'Harga Perolehan tidak boleh melebihi Rp 999.000.000.',
            'kondisi.required'                  => 'Kondisi Aset wajib dipilih.',
            'keterangan_lokasi_unit.required'   => 'Keterangan Unit Lokasi wajib diisi.',
        ]);

        $dataAset = [
            'no_reg_pemda'           => $request->filled('no_reg_pemda') ? trim($request->no_reg_pemda) : null,
            'no_reg_kominfo'         => $request->filled('no_reg_kominfo') ? trim($request->no_reg_kominfo) : null,
            'penanggung_jawab'       => trim($request->penanggung_jawab),
            'jenis_barang'           => trim($request->jenis_barang),
            'merek_tipe'             => trim($request->merek_tipe),
            'tahun'                  => $request->tahun,
            'harga_perolehan'        => $request->harga_perolehan,
            'no_rangka_seri'         => $request->no_rangka_seri,
            'no_mesin'               => $request->no_mesin,
            'no_polisi'              => $request->no_polisi,
            'no_bpkb'                => $request->no_bpkb,
            'kondisi'                => $request->kondisi,
            'keterangan_lokasi_unit' => trim($request->keterangan_lokasi_unit),
            'no_sk_bast'             => $request->no_sk_bast ? trim($request->no_sk_bast) : null,
        ];

        AsetPeralatanMesin::create($dataAset);

        return redirect()->back()->with('success', 'Aset berhasil ditambahkan!');
    }

    // Tombol Edit Aset
    public function updateAset(Request $request, $no_reg_pemda)
    {
        $id = urldecode($no_reg_pemda);
        $aset = AsetPeralatanMesin::where('id', $id)
            ->orWhere('no_reg_pemda', $id)
            ->orWhere('no_reg_kominfo', $id)
            ->orWhere('no_reg_pemda', $no_reg_pemda)
            ->first();

        if (!$aset) {
            $aset = AsetPeralatanMesin::find($no_reg_pemda);
        }

        if (!$aset) {
            return redirect()->back()->with('error', 'Data aset tidak ditemukan.');
        }

        if ($request->has('harga_perolehan') && is_string($request->harga_perolehan)) {
            $cleanPrice = preg_replace('/[^\d]/', '', $request->harga_perolehan);
            $request->merge(['harga_perolehan' => $cleanPrice !== '' ? (int)$cleanPrice : null]);
        }

        $request->validate([
            'no_reg_pemda'           => 'nullable|string|max:100',
            'no_reg_kominfo'         => 'nullable|string|max:100',
            'penanggung_jawab'       => 'required|string|max:100',
            'jenis_barang'           => 'required|string|max:100',
            'merek_tipe'             => 'required|string|max:100',
            'tahun'                  => 'required|digits:4',
            'harga_perolehan'        => 'required|numeric|min:0|max:999000000',
            'kondisi'                => 'required|in:Baik,Hilang,Rusak Ringan,Rusak Berat',
            'keterangan_lokasi_unit' => 'required|string|max:150',
            'no_sk_bast'             => 'nullable|string|max:150',
            'no_rangka_seri'         => 'nullable|string|max:100',
            'no_mesin'               => 'nullable|string|max:100',
            'no_polisi'              => 'nullable|string|max:50',
            'no_bpkb'                => 'nullable|string|max:50',
        ], [
            'penanggung_jawab.required'         => 'Penanggung Jawab wajib diisi.',
            'jenis_barang.required'             => 'Jenis Barang wajib diisi.',
            'merek_tipe.required'               => 'Merek / Tipe Aset wajib diisi.',
            'tahun.required'                    => 'Tahun Perolehan wajib diisi.',
            'tahun.digits'                      => 'Tahun Perolehan harus 4 digit angka valid (contoh: 2026).',
            'harga_perolehan.required'          => 'Harga Perolehan wajib diisi.',
            'harga_perolehan.numeric'           => 'Harga Perolehan harus berupa angka valid.',
            'harga_perolehan.min'               => 'Harga Perolehan tidak boleh kurang dari 0.',
            'harga_perolehan.max'               => 'Harga Perolehan tidak boleh melebihi Rp 999.000.000.',
            'kondisi.required'                  => 'Kondisi Aset wajib dipilih.',
            'keterangan_lokasi_unit.required'   => 'Keterangan Unit Lokasi wajib diisi.',
        ]);

        $aset->update([
            'no_reg_pemda'           => $request->filled('no_reg_pemda') ? trim($request->no_reg_pemda) : null,
            'no_reg_kominfo'         => $request->filled('no_reg_kominfo') ? trim($request->no_reg_kominfo) : null,
            'penanggung_jawab'       => trim($request->penanggung_jawab),
            'jenis_barang'           => trim($request->jenis_barang),
            'merek_tipe'             => trim($request->merek_tipe),
            'tahun'                  => $request->tahun,
            'harga_perolehan'        => $request->harga_perolehan,
            'no_rangka_seri'         => $request->no_rangka_seri,
            'no_mesin'               => $request->no_mesin,
            'no_polisi'              => $request->no_polisi,
            'no_bpkb'                => $request->no_bpkb,
            'kondisi'                => $request->kondisi,
            'keterangan_lokasi_unit' => trim($request->keterangan_lokasi_unit),
            'no_sk_bast'             => $request->no_sk_bast ? trim($request->no_sk_bast) : null,
        ]);

        return redirect()->back()->with('success', 'Data aset berhasil diperbarui!');
    }

    // Tombol Hapus Aset
    public function destroyAset($no_reg_pemda)
    {
        $id = urldecode($no_reg_pemda);
        $aset = AsetPeralatanMesin::where('id', $id)
            ->orWhere('no_reg_pemda', $id)
            ->orWhere('no_reg_kominfo', $id)
            ->orWhere('no_reg_pemda', $no_reg_pemda)
            ->first();

        if (!$aset) {
            $aset = AsetPeralatanMesin::find($no_reg_pemda);
        }

        if ($aset) {
            $aset->delete();
            return redirect()->back()->with('success', 'Aset berhasil dihapus!');
        }

        return redirect()->back()->with('error', 'Data aset tidak ditemukan.');
    }

    // Halaman Kelola Magang
    public function magang()
    {
        $magang = PendaftaranMagang::with(['user', 'suratBalasan'])->latest()->paginate(10);
        return view('auth.magang', compact('magang'));
    }

    // Setujui / Tolak Magang & Upload Surat Balasan
    public function updateStatusMagang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diterima,Ditolak',
            'no_surat' => 'nullable|string|max:100',
            'file_surat_balasan' => 'nullable|file|mimes:pdf|max:2048',
            'catatan_admin' => 'nullable|string',
        ]);

        $pendaftaran = PendaftaranMagang::findOrFail($id);

        if ($request->status === 'Ditolak') {
            if ($pendaftaran->surat_pengantar) {
                Storage::disk('public')->delete($pendaftaran->surat_pengantar);
            }
            if ($pendaftaran->suratBalasan) {
                if ($pendaftaran->suratBalasan->file_surat_balasan) {
                    Storage::disk('public')->delete($pendaftaran->suratBalasan->file_surat_balasan);
                }
                $pendaftaran->suratBalasan->delete();
            }
            $pendaftaran->delete();

            return redirect()->back()->with('success', 'Data pendaftaran magang ditolak dan telah otomatis dihapus dari database.');
        }

        $pendaftaran->update(['status' => $request->status]);

        if ($request->hasFile('file_surat_balasan')) {
            $path = $request->file('file_surat_balasan')->store('surat_balasan', 'public');
            
            SuratBalasanMagang::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'no_surat' => $request->no_surat ?: ('SK/' . $pendaftaran->id . '/DISCOM/2026'),
                    'tanggal_surat' => now(),
                    'file_surat_balasan' => $path,
                    'catatan_admin' => $request->catatan_admin,
                ]
            );
        }

        return redirect()->back()->with('success', 'Status pendaftaran magang berhasil diperbarui!');
    }

    // Halaman Detail Publik Aset (Scan QR Code)
    public function publicDetail($id)
    {
        $decodedId = urldecode($id);
        $aset = AsetPeralatanMesin::where('id', $decodedId)
            ->orWhere('no_reg_pemda', $decodedId)
            ->orWhere('no_reg_kominfo', $decodedId)
            ->orWhere('no_reg_pemda', $id)
            ->first();

        if (!$aset && is_numeric($id)) {
            $aset = AsetPeralatanMesin::find($id);
        }

        if (!$aset) {
            abort(404, 'Data aset dengan nomor registrasi "' . $decodedId . '" tidak ditemukan.');
        }

        return view('aset.public_detail', compact('aset'));
    }

    // Endpoint Gambar QR Code format SVG (Offline & Tanpa Ketergantungan API Eksternal)
    public function qrImage($id)
    {
        $decodedId = urldecode($id);
        $aset = AsetPeralatanMesin::where('id', $decodedId)
            ->orWhere('no_reg_pemda', $decodedId)
            ->orWhere('no_reg_kominfo', $decodedId)
            ->orWhere('no_reg_pemda', $id)
            ->first();

        if (!$aset && is_numeric($id)) {
            $aset = AsetPeralatanMesin::find($id);
        }

        if (!$aset) {
            abort(404, 'Data aset tidak ditemukan.');
        }

        $targetUrl = $aset->public_url;

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(280)
                ->errorCorrection('M')
                ->margin(2)
                ->generate($targetUrl);

            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        return redirect("https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($targetUrl));
    }

    // Endpoint API untuk Verifikasi QR / Barcode Scanner di Frontend
    public function apiDetail($id)
    {
        $decodedId = urldecode($id);
        $aset = AsetPeralatanMesin::where('id', $decodedId)
            ->orWhere('no_reg_pemda', $decodedId)
            ->orWhere('no_reg_kominfo', $decodedId)
            ->orWhere('no_reg_pemda', $id)
            ->first();

        if (!$aset && is_numeric($id)) {
            $aset = AsetPeralatanMesin::find($id);
        }

        if (!$aset) {
            return response()->json([
                'success' => false,
                'message' => 'Data aset dengan No. Reg "' . $decodedId . '" tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $aset->id,
                'no_reg_pemda' => $aset->no_reg_pemda,
                'no_reg_kominfo' => $aset->no_reg_kominfo,
                'jenis_barang' => $aset->jenis_barang,
                'merek_tipe' => $aset->merek_tipe,
                'penanggung_jawab' => $aset->penanggung_jawab,
                'kondisi' => $aset->kondisi,
                'tahun' => $aset->tahun,
                'harga_perolehan' => $aset->harga_perolehan,
                'keterangan_lokasi_unit' => $aset->keterangan_lokasi_unit,
                'detail_url' => $aset->public_url,
            ]
        ]);
    }

    // Export Laporan Rekap Bulanan / Master Aset ke PDF Berdasarkan Filter Aktif
    public function exportLaporanPdf(Request $request)
    {
        $query = AsetPeralatanMesin::query();
        $this->applyAsetFilters($query, $request);
        $aset = $query->orderBy('created_at', 'desc')->get();

        $rekap = [
            'Baik' => $aset->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $aset->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat' => $aset->where('kondisi', 'Rusak Berat')->count(),
            'Hilang' => $aset->where('kondisi', 'Hilang')->count(),
            'Total' => $aset->count(),
        ];

        $kondisi = $request->query('kondisi') ?: ($request->query('filter_kondisi') ?: $request->query('status'));
        $tanggalCetak = date('d-m-Y');

        if ($kondisi && $kondisi !== 'all') {
            $namaKondisi = strtolower(trim($kondisi));
            $filename = "rekap aset-kondisi {$namaKondisi}-{$tanggalCetak}.pdf";
        } else {
            $filename = "rekap aset-semua kondisi-{$tanggalCetak}.pdf";
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.laporan_pdf', compact('aset', 'rekap'))
                      ->setPaper('a4', 'portrait');
            return $pdf->download($filename);
        }

        return view('admin.laporan_pdf', compact('aset', 'rekap'));
    }
}
