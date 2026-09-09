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
    // Halaman Manajemen Aset & Laporan Rekap
    public function aset(Request $request)
    {
        $aset = AsetPeralatanMesin::orderBy('created_at', 'desc')->paginate(10, ['*'], 'aset_page');

        // Data Laporan Rekap Bulanan
        $filterBulan = $request->query('filter_bulan', 'all');
        $filterTahun = $request->query('filter_tahun', date('Y'));
        $filterKondisi = $request->query('filter_kondisi', 'all');

        $queryLaporan = AsetPeralatanMesin::query();
        if ($filterBulan && $filterBulan !== 'all') {
            $queryLaporan->whereMonth('created_at', $filterBulan);
        }
        if ($filterTahun && $filterTahun !== 'all') {
            $queryLaporan->whereYear('created_at', $filterTahun);
        }
        $allPeriodeAset = $queryLaporan->orderBy('created_at', 'desc')->get();

        $rekapKondisi = [
            'Baik' => $allPeriodeAset->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $allPeriodeAset->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat' => $allPeriodeAset->where('kondisi', 'Rusak Berat')->count(),
            'Hilang' => $allPeriodeAset->where('kondisi', 'Hilang')->count(),
            'Total' => $allPeriodeAset->count(),
        ];

        if ($filterKondisi && $filterKondisi !== 'all') {
            $laporanAset = $allPeriodeAset->where('kondisi', $filterKondisi)->values();
        } else {
            $laporanAset = $allPeriodeAset;
        }

        return view('auth.aset', compact('aset', 'laporanAset', 'rekapKondisi', 'filterBulan', 'filterTahun', 'filterKondisi'));
    }

    // Tombol Tambah Aset
    public function storeAset(Request $request)
    {
        if ($request->has('harga_perolehan') && is_string($request->harga_perolehan)) {
            $cleanPrice = preg_replace('/[^\d]/', '', $request->harga_perolehan);
            $request->merge(['harga_perolehan' => $cleanPrice !== '' ? (int)$cleanPrice : null]);
        }

        $request->validate([
            'no_reg_pemda'           => 'nullable|string|max:100|unique:aset_peralatan_mesin,no_reg_pemda',
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

        if ($request->filled('no_reg_pemda')) {
            $dataAset['no_reg_pemda'] = trim($request->no_reg_pemda);
        }

        AsetPeralatanMesin::create($dataAset);

        return redirect()->back()->with('success', 'Aset berhasil ditambahkan!');
    }

    // Tombol Edit Aset
    public function updateAset(Request $request, $no_reg_pemda)
    {
        $id = urldecode($no_reg_pemda);
        $aset = AsetPeralatanMesin::where('no_reg_pemda', $id)->orWhere('no_reg_pemda', $no_reg_pemda)->first();

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
        $aset = AsetPeralatanMesin::where('no_reg_pemda', $id)->orWhere('no_reg_pemda', $no_reg_pemda)->first();

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

    // Halaman Kelola User (Redirect ke admin.users.index untuk menjaga integrasi)
    public function users()
    {
        return redirect()->route('admin.users.index');
    }

    // Halaman Detail Publik Aset (Scan QR Code)
    public function publicDetail($id)
    {
        $decodedId = urldecode($id);
        $aset = AsetPeralatanMesin::where('no_reg_pemda', $decodedId)
            ->orWhere('no_reg_pemda', $id)
            ->orWhere('no_reg_pemda', str_replace('%2F', '/', $id))
            ->first();

        if (!$aset && is_numeric($id)) {
            $aset = AsetPeralatanMesin::find($id);
        }

        if (!$aset) {
            abort(404, 'Data aset tidak ditemukan.');
        }

        return view('aset.public_detail', compact('aset'));
    }

    // Export Laporan Rekap Bulanan ke PDF
    public function exportLaporanPdf(Request $request)
    {
        $filterBulan = $request->query('filter_bulan', 'all');
        $filterTahun = $request->query('filter_tahun', date('Y'));
        $filterKondisi = $request->query('filter_kondisi', 'all');

        $queryLaporan = AsetPeralatanMesin::query();
        if ($filterBulan && $filterBulan !== 'all') {
            $queryLaporan->whereMonth('created_at', $filterBulan);
        }
        if ($filterTahun && $filterTahun !== 'all') {
            $queryLaporan->whereYear('created_at', $filterTahun);
        }
        $allAset = $queryLaporan->orderBy('created_at', 'desc')->get();

        $rekap = [
            'Baik' => $allAset->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $allAset->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat' => $allAset->where('kondisi', 'Rusak Berat')->count(),
            'Hilang' => $allAset->where('kondisi', 'Hilang')->count(),
            'Total' => $allAset->count(),
        ];

        if ($filterKondisi && $filterKondisi !== 'all') {
            $aset = $allAset->where('kondisi', $filterKondisi)->values();
        } else {
            $aset = $allAset;
        }

        $namaBulanList = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            'all' => 'Semua Bulan'
        ];

        $namaBulan = $namaBulanList[$filterBulan] ?? 'Semua Bulan';
        $tahun = $filterTahun === 'all' ? 'Semua Tahun' : $filterTahun;
        $kondisiLabel = ($filterKondisi && $filterKondisi !== 'all') ? $filterKondisi : 'Semua Kondisi';

        $fileName = 'laporan-rekap-kondisi-aset-' . $filterBulan . '-' . $filterTahun . ($filterKondisi && $filterKondisi !== 'all' ? '-' . \Illuminate\Support\Str::slug($filterKondisi) : '') . '.pdf';

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.laporan_pdf', compact('aset', 'rekap', 'namaBulan', 'tahun', 'kondisiLabel'));
            return $pdf->download($fileName);
        }

        return view('admin.laporan_pdf', compact('aset', 'rekap', 'namaBulan', 'tahun', 'kondisiLabel'));
    }
}
