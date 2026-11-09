<?php

namespace App\Http\Controllers;

use App\Models\AsetPeralatanMesin;
use App\Models\BeritaAcara;
use App\Models\User;
use App\Services\BeritaAcaraService;
use App\Services\AsetImageService;
use App\Services\LaporanAsetService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $kondisi = $request->query('kondisi') ?: $request->query('status');
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

        // 7. Filter status BAST (Ada SK/BAST vs Belum)
        $statusBast = $request->query('status_bast');
        if ($statusBast === 'ada') {
            $query->whereNotNull('no_sk_bast')->where('no_sk_bast', '!=', '');
        } elseif ($statusBast === 'belum') {
            $query->where(function ($q) {
                $q->whereNull('no_sk_bast')->orWhere('no_sk_bast', '');
            });
        }

        // 8. Filter Rentang Tanggal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->query('tanggal_akhir'));
        }

        return $query;
    }

    // Halaman Manajemen Aset, Berita Acara, & Laporan Rekap
    public function aset(Request $request)
    {
        // 0. Deteksi Sub-Menu (Tab) Aktif
        $currentTab = $request->query('tab');
        if (!$currentTab) {
            $routeName = $request->route() ? $request->route()->getName() : '';
            if ($routeName === 'admin.aset.berita_acara') {
                $currentTab = 'berita-acara';
            } elseif ($routeName === 'admin.aset.laporan') {
                $currentTab = 'laporan';
            } elseif ($request->has('filter_bulan') || $request->has('filter_tahun')) {
                $currentTab = 'laporan';
            } else {
                $currentTab = 'master';
            }
        }

        // 1. Query Master Data Aset (dengan filter terpadu & eager loading Berita Acara untuk cegah N+1)
        $queryAset = AsetPeralatanMesin::query()->with(['beritaAcaras'])->withCount('beritaAcaras');
        $this->applyAsetFilters($queryAset, $request);
        $aset = $queryAset->orderBy('created_at', 'desc')->paginate(10, ['*'], 'aset_page')->withQueryString();

        // 2. Data Laporan Rekap Bulanan (dengan filter yang sama)
        $queryLaporan = AsetPeralatanMesin::query();
        $this->applyAsetFilters($queryLaporan, $request);
        $laporanAset = $queryLaporan->orderBy('created_at', 'desc')->get();

        $filterBulan = $request->query('filter_bulan', 'all');
        $filterTahun = $request->query('filter_tahun', date('Y'));

        // Query seluruh aset periode aktif (tanpa filter kondisi/pencarian tabel) untuk persentase utuh
        $queryPeriode = AsetPeralatanMesin::query();
        if ($request->filled('filter_bulan') && $request->query('filter_bulan') !== 'all') {
            $queryPeriode->whereMonth('created_at', $request->query('filter_bulan'));
        }
        if ($request->filled('filter_tahun') && $request->query('filter_tahun') !== 'all') {
            $queryPeriode->whereYear('created_at', $request->query('filter_tahun'));
        }
        if ($request->filled('tanggal_awal')) {
            $queryPeriode->whereDate('created_at', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $queryPeriode->whereDate('created_at', '<=', $request->query('tanggal_akhir'));
        }
        $asetPeriode = $queryPeriode->get();

        $laporanService = app(LaporanAsetService::class);
        $rekapData = $laporanService->calculateRekap($asetPeriode);
        $rekapKondisi = $rekapData['counts'];
        $rekapKondisi['Total'] = $rekapData['total'];

        // 3. Data & Statistik Berita Acara (Murni Upload Scan Fisik)
        $queryBa = BeritaAcara::with('aset');
        
        $searchBa = $request->query('search_ba') ?: ($currentTab === 'berita-acara' ? ($request->query('search') ?: $request->query('q')) : null);
        if ($searchBa) {
            $queryBa->where(function ($q) use ($searchBa) {
                $q->where('jenis', 'like', "%{$searchBa}%")
                  ->orWhere('nomor_surat', 'like', "%{$searchBa}%")
                  ->orWhere('keterangan', 'like', "%{$searchBa}%")
                  ->orWhere('file_nama_asli', 'like', "%{$searchBa}%")
                  ->orWhere('pihak_kedua_nama', 'like', "%{$searchBa}%")
                  ->orWhere('pihak_pertama_nama', 'like', "%{$searchBa}%")
                  ->orWhereHas('aset', function ($qa) use ($searchBa) {
                      $qa->where('no_reg_pemda', 'like', "%{$searchBa}%")
                         ->orWhere('no_reg_kominfo', 'like', "%{$searchBa}%")
                         ->orWhere('jenis_barang', 'like', "%{$searchBa}%")
                         ->orWhere('merek_tipe', 'like', "%{$searchBa}%")
                         ->orWhere('penanggung_jawab', 'like', "%{$searchBa}%");
                  });
            });
        }

        $filterJenisBa = $request->query('jenis_ba');
        if ($filterJenisBa && $filterJenisBa !== 'all') {
            $queryBa->where('jenis', $filterJenisBa);
        }

        $filterSumberBa = $request->query('sumber_ba');
        if ($filterSumberBa && $filterSumberBa !== 'all') {
            $queryBa->where('sumber', $filterSumberBa);
        }

        $beritaAcaras = $queryBa->orderBy('tanggal', 'desc')->paginate(10, ['*'], 'ba_page')->withQueryString();

        $rekapBast = [
            'total_ba'           => BeritaAcara::count(),
            'total_bast'         => BeritaAcara::where('jenis', 'like', '%BAST%')->count(),
            'total_penerimaan'   => BeritaAcara::where('jenis', 'like', '%Penerimaan%')->count(),
            'total_aset_terkait' => BeritaAcara::distinct('aset_id')->count('aset_id'),
            'total_generate'     => BeritaAcara::where('sumber', 'generate')->count(),
            'total_upload'       => BeritaAcara::where('sumber', 'upload')->count(),
        ];

        // Opsi master data aset untuk dropdown form Tambah Berita Acara
        $listAsetOption = AsetPeralatanMesin::orderBy('jenis_barang')->orderBy('no_reg_pemda')->get();

        // Data Pegawai Aktif untuk Dropdown Jabatan Berita Acara & Pengesahan Kadis
        $pegawaiList = User::where('role', 'Pegawai')
            ->where('is_active', true)
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip', 'jabatan', 'urutan']);

        // Daftar jabatan unik urut dari jabatan tertinggi (urutan teratas)
        $listJabatan = $pegawaiList->pluck('jabatan')->unique()->values();

        // Pemetaan jabatan ke daftar pegawai untuk auto-fill nama & NIP di frontend
        $jabatanPegawaiMap = [];
        foreach ($pegawaiList as $p) {
            $jabKey = trim($p->jabatan);
            if (!isset($jabatanPegawaiMap[$jabKey])) {
                $jabatanPegawaiMap[$jabKey] = [];
            }
            $jabatanPegawaiMap[$jabKey][] = [
                'nama' => $p->nama,
                'nip'  => $p->nip ?: '-',
            ];
        }

        // Cari data Plt. Kepala Dinas (otomatis untuk pengesahan & tanda tangan BAST)
        $pejabatKadis = User::where('role', 'Pegawai')
            ->where('is_active', true)
            ->where('jabatan', 'like', '%Kepala Dinas%')
            ->orderBy('urutan')
            ->first();

        if (!$pejabatKadis) {
            $pejabatKadis = User::where('role', 'Pegawai')
                ->where('is_active', true)
                ->orderBy('urutan')
                ->first();
        }

        // Opsi filter untuk dropdown di frontend Blade
        $listKategori = AsetPeralatanMesin::select('jenis_barang')->whereNotNull('jenis_barang')->where('jenis_barang', '!=', '')->distinct()->orderBy('jenis_barang')->pluck('jenis_barang');
        $listLokasi = AsetPeralatanMesin::select('keterangan_lokasi_unit')->whereNotNull('keterangan_lokasi_unit')->where('keterangan_lokasi_unit', '!=', '')->distinct()->orderBy('keterangan_lokasi_unit')->pluck('keterangan_lokasi_unit');
        $listTahun = AsetPeralatanMesin::select('tahun')->whereNotNull('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return response()->view('auth.aset', compact(
            'aset', 
            'laporanAset', 
            'rekapKondisi', 
            'rekapData',
            'rekapBast',
            'beritaAcaras',
            'listAsetOption',
            'currentTab',
            'filterBulan', 
            'filterTahun',
            'listKategori',
            'listLokasi',
            'listTahun',
            'listJabatan',
            'jabatanPegawaiMap',
            'pegawaiList',
            'pejabatKadis'
        ))->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    // Sub-halaman Berita Acara (Upload & Arsip Scan Fisik)
    public function beritaAcara(Request $request)
    {
        $request->merge(['tab' => 'berita-acara']);
        return $this->aset($request);
    }

    // Sub-halaman Laporan Rekap Bulanan
    public function laporanRekap(Request $request)
    {
        $request->merge(['tab' => 'laporan']);
        return $this->aset($request);
    }

    /**
     * Upload & Simpan File Scan Fisik Berita Acara (BAST / Penerimaan Barang)
     */
    public function storeBeritaAcara(Request $request)
    {
        $request->validate([
            'aset_id'     => 'required|exists:aset_peralatan_mesin,id',
            'jenis'       => 'required|in:Berita Acara Serah Terima (BAST),Berita Acara Penerimaan Barang',
            'nomor_surat' => 'nullable|string|max:150',
            'tanggal'     => 'required|date',
            'keterangan'  => 'nullable|string|max:1000',
            'file_scan'   => 'required|file|mimes:pdf|max:2048', // Wajib PDF, max 2MB
        ], [
            'aset_id.required'    => 'Aset terkait wajib dipilih dari Master Data Aset.',
            'aset_id.exists'      => 'Aset yang dipilih tidak valid dalam sistem.',
            'jenis.required'      => 'Jenis Berita Acara wajib dipilih.',
            'jenis.in'            => 'Jenis Berita Acara harus berupa BAST atau Penerimaan Barang.',
            'nomor_surat.max'     => 'Nomor Berita Acara maksimal 150 karakter.',
            'tanggal.required'    => 'Tanggal Berita Acara wajib diisi.',
            'tanggal.date'        => 'Format tanggal Berita Acara tidak valid.',
            'file_scan.required'  => 'File hasil scan fisik wajib diunggah.',
            'file_scan.mimes'     => 'File hasil scan harus berformat PDF.',
            'file_scan.max'       => 'Ukuran file PDF maksimal 2MB (2048 KB).',
        ]);

        $rawNomor = $request->filled('nomor_surat') && trim($request->nomor_surat) !== '' ? trim($request->nomor_surat) : null;
        $nomorNormalized = $rawNomor ? BeritaAcara::normalizeNomorSurat($rawNomor) : null;

        // Validasi keunikan nomor jika diisi
        if ($nomorNormalized) {
            $existingBa = BeritaAcara::with('aset')->where('nomor_surat_normalized', $nomorNormalized)->first();
            if ($existingBa) {
                $asetNoReg = $existingBa->aset ? ($existingBa->aset->no_reg_pemda ?: $existingBa->aset->no_reg_kominfo ?: 'ID ' . $existingBa->aset->id) : 'tidak diketahui';
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['nomor_surat' => "Nomor Berita Acara ini sudah terpakai pada {$existingBa->jenis} untuk aset {$asetNoReg}."]);
            }
        }

        $file = $request->file('file_scan');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $storedPath = $file->store('berita_acara', 'public');

        try {
            BeritaAcara::create([
                'aset_id'               => $request->aset_id,
                'jenis'                 => $request->jenis,
                'sumber'                => 'upload',
                'nomor_surat'           => $rawNomor,
                'nomor_surat_normalized'=> $nomorNormalized,
                'tanggal'               => $request->tanggal,
                'keterangan'            => $request->filled('keterangan') ? trim($request->keterangan) : null,
                'file_path'             => $storedPath,
                'file_nama_asli'        => $originalName,
                'file_ukuran'           => $fileSize,
                'user_id'               => auth()->id(),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                $existing = BeritaAcara::with('aset')->where('nomor_surat_normalized', $nomorNormalized)->first();
                $asetNoReg = $existing && $existing->aset ? ($existing->aset->no_reg_pemda ?: $existing->aset->no_reg_kominfo ?: 'ID ' . $existing->aset->id) : 'tidak diketahui';
                $jenis = $existing ? $existing->jenis : 'Berita Acara';
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['nomor_surat' => "Nomor Berita Acara ini sudah terpakai pada {$jenis} untuk aset {$asetNoReg}."]);
            }
            throw $e;
        }

        return redirect()->route('admin.aset.berita_acara')->with('success', 'File scan Berita Acara berhasil diunggah dan tersimpan permanen!');
    }

    /**
     * Buat Dokumen Resmi Berita Acara Baru (Generate Word & PDF Otomatis)
     */
    public function generateBeritaAcara(Request $request, BeritaAcaraService $service)
    {
        $isFirstManual = $request->input('pihak_pertama_user_id') === 'manual';
        $isSecondManual = $request->input('pihak_kedua_user_id') === 'manual';

        $rules = [
            'nomor_surat'            => 'required|string|max:150',
            'aset_id'                => 'required|exists:aset_peralatan_mesin,id',
            'tanggal'                => 'required|date',
            'jumlah_barang'          => 'required|string|max:150',
            'keperluan'              => 'required|string|max:255',
            'mengetahui_nama'        => 'required|string|max:150',
            'mengetahui_nip'         => 'nullable|string|max:50',
            'mengetahui_jabatan'     => 'required|string|max:150',
            'tempat_tanda_tangan'    => 'required|string|max:100',
            'keterangan'             => 'nullable|string|max:1000',
        ];

        $messages = [
            'nomor_surat.required'         => 'Nomor Berita Acara wajib diisi.',
            'aset_id.required'             => 'Aset terkait wajib dipilih dari Master Data.',
            'aset_id.exists'               => 'Aset yang dipilih tidak valid dalam sistem.',
            'tanggal.required'             => 'Tanggal Berita Acara wajib diisi.',
            'jumlah_barang.required'       => 'Jumlah & Nama Barang wajib diisi.',
            'keperluan.required'           => 'Keperluan Penggunaan wajib diisi.',
            'mengetahui_nama.required'     => 'Nama Pejabat yang Mengetahui wajib diisi.',
            'mengetahui_jabatan.required'  => 'Jabatan Pejabat yang Mengetahui wajib diisi.',
            'tempat_tanda_tangan.required' => 'Tempat penandatanganan wajib diisi.',
        ];

        // Validasi Pihak Pertama
        if ($isFirstManual) {
            $rules['pihak_pertama_nama'] = 'required|string|max:150';
            $rules['pihak_pertama_nip'] = 'nullable|string|max:50';
            $rules['pihak_pertama_jabatan'] = 'required|string|max:150';
            $messages['pihak_pertama_nama.required'] = 'Nama Pihak Pertama wajib diisi.';
            $messages['pihak_pertama_jabatan.required'] = 'Jabatan Pihak Pertama wajib diisi.';
        } else {
            $rules['pihak_pertama_user_id'] = 'required|exists:users,id';
            $messages['pihak_pertama_user_id.required'] = 'Pihak Pertama wajib dipilih dari daftar pegawai.';
            $messages['pihak_pertama_user_id.exists'] = 'Pegawai Pihak Pertama yang dipilih tidak valid.';
        }

        // Validasi Pihak Kedua
        if ($isSecondManual) {
            $rules['pihak_kedua_nama'] = 'required|string|max:150';
            $rules['pihak_kedua_nip'] = 'nullable|string|max:50';
            $rules['pihak_kedua_jabatan'] = 'required|string|max:150';
            $messages['pihak_kedua_nama.required'] = 'Nama Pihak Kedua wajib diisi.';
            $messages['pihak_kedua_jabatan.required'] = 'Jabatan Pihak Kedua wajib diisi.';
        } else {
            $rules['pihak_kedua_user_id'] = 'required|exists:users,id';
            $messages['pihak_kedua_user_id.required'] = 'Pihak Kedua wajib dipilih dari daftar pegawai.';
            $messages['pihak_kedua_user_id.exists'] = 'Pegawai Pihak Kedua yang dipilih tidak valid.';
        }

        $request->validate($rules, $messages);

        // Ambil data Pihak Pertama dari database jika memilih user id
        if ($isFirstManual) {
            $p1Nama = trim($request->pihak_pertama_nama);
            $p1Nip = $request->filled('pihak_pertama_nip') ? trim($request->pihak_pertama_nip) : '-';
            $p1Jabatan = trim($request->pihak_pertama_jabatan);
        } else {
            $p1User = User::findOrFail($request->pihak_pertama_user_id);
            $p1Nama = $p1User->nama;
            $p1Nip = $p1User->nip ?: '-';
            $p1Jabatan = $p1User->jabatan;
        }

        // Ambil data Pihak Kedua dari database jika memilih user id
        if ($isSecondManual) {
            $p2Nama = trim($request->pihak_kedua_nama);
            $p2Nip = $request->filled('pihak_kedua_nip') ? trim($request->pihak_kedua_nip) : '-';
            $p2Jabatan = trim($request->pihak_kedua_jabatan);
        } else {
            $p2User = User::findOrFail($request->pihak_kedua_user_id);
            $p2Nama = $p2User->nama;
            $p2Nip = $p2User->nip ?: '-';
            $p2Jabatan = $p2User->jabatan;
        }

        $nomorNormalized = BeritaAcara::normalizeNomorSurat($request->nomor_surat);
        $existingBa = BeritaAcara::with('aset')->where('nomor_surat_normalized', $nomorNormalized)->first();
        if ($existingBa) {
            $asetNoReg = $existingBa->aset ? ($existingBa->aset->no_reg_pemda ?: $existingBa->aset->no_reg_kominfo ?: 'ID ' . $existingBa->aset->id) : 'tidak diketahui';
            return redirect()->back()
                ->withInput()
                ->withErrors(['nomor_surat' => "Nomor Berita Acara ini sudah terpakai pada {$existingBa->jenis} untuk aset {$asetNoReg}."]);
        }

        try {
            $ba = BeritaAcara::create([
                'aset_id'               => $request->aset_id,
                'jenis'                 => 'Berita Acara Serah Terima (BAST)',
                'sumber'                => 'generate',
                'nomor_surat'           => trim($request->nomor_surat),
                'nomor_surat_normalized'=> $nomorNormalized,
                'tanggal'               => $request->tanggal,
                'keterangan'            => $request->filled('keterangan') ? trim($request->keterangan) : null,
                'file_path'             => 'pending',
                'file_nama_asli'        => 'BAST-' . Str::slug(str_replace('/', '-', $request->nomor_surat)) . '.pdf',
                'file_ukuran'           => 0,
                'user_id'               => auth()->id(),
                'pihak_pertama_nama'    => $p1Nama,
                'pihak_pertama_nip'     => $p1Nip,
                'pihak_pertama_jabatan' => $p1Jabatan,
                'pihak_kedua_nama'      => $p2Nama,
                'pihak_kedua_nip'       => $p2Nip,
                'pihak_kedua_jabatan'   => $p2Jabatan,
                'mengetahui_nama'       => trim($request->mengetahui_nama),
                'mengetahui_nip'        => $request->filled('mengetahui_nip') ? trim($request->mengetahui_nip) : '-',
                'mengetahui_jabatan'    => trim($request->mengetahui_jabatan),
                'jumlah_barang'         => trim($request->jumlah_barang),
                'keperluan'             => trim($request->keperluan),
                'tempat_tanda_tangan'   => trim($request->tempat_tanda_tangan),
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                $existing = BeritaAcara::with('aset')->where('nomor_surat_normalized', $nomorNormalized)->first();
                $asetNoReg = $existing && $existing->aset ? ($existing->aset->no_reg_pemda ?: $existing->aset->no_reg_kominfo ?: 'ID ' . $existing->aset->id) : 'tidak diketahui';
                $jenis = $existing ? $existing->jenis : 'Berita Acara';
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['nomor_surat' => "Nomor Berita Acara ini sudah terpakai pada {$jenis} untuk aset {$asetNoReg}."]);
            }
            throw $e;
        }

        $ba->load('aset');

        // Generate PDF
        $pdfPath = $service->generatePdf($ba);

        $fileSize = Storage::disk('public')->exists($pdfPath) ? Storage::disk('public')->size($pdfPath) : 0;

        $ba->update([
            'file_path'   => $pdfPath,
            'file_ukuran' => $fileSize,
        ]);

        return redirect()->route('admin.aset.berita_acara')->with('success', "Dokumen Berita Acara resmi ({$ba->nomor_surat}) berhasil dibuat dalam format PDF!");
    }

    /**
     * Sajikan Berkas PDF Berita Acara Secara Langsung (Inline Stream)
     * Hanya dapat diakses oleh Admin Master dan Bendahara Barang.
     */
    public function fileBeritaAcara($id, BeritaAcaraService $service)
    {
        if (!auth()->check()) {
            abort(403, 'Akses ditolak. Silakan masuk terlebih dahulu.');
        }

        if (!in_array(auth()->user()->role, ['admin', 'bendahara_barang'])) {
            abort(403, 'Akses ditolak. Berkas Berita Acara hanya dapat diakses oleh Admin Master dan Bendahara Barang.');
        }

        $ba = BeritaAcara::with('aset')->find($id);
        if (!$ba) {
            abort(404, 'Dokumen Berita Acara tidak ditemukan dalam sistem.');
        }

        if ($ba->sumber === 'generate') {
            $ba->file_path = $service->generatePdf($ba);
            $ba->file_ukuran = Storage::disk('public')->exists($ba->file_path) ? Storage::disk('public')->size($ba->file_path) : 0;
            $ba->save();
        } elseif (!$ba->file_path || !Storage::disk('public')->exists($ba->file_path)) {
            abort(404, 'File scan Berita Acara tidak ditemukan pada penyimpanan server.');
        }

        $fullPath = Storage::disk('public')->path($ba->file_path);
        if (!file_exists($fullPath)) {
            abort(404, 'Berkas fisik PDF Berita Acara tidak ditemukan pada direktori penyimpanan.');
        }

        $filename = $ba->file_nama_asli ?: ('BAST-' . Str::slug(str_replace('/', '-', $ba->nomor_surat ?: 'dokumen')) . '.pdf');

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /**
     * Preview File PDF Berita Acara (Alias ke fileBeritaAcara)
     */
    public function previewBeritaAcara($id, BeritaAcaraService $service)
    {
        return $this->fileBeritaAcara($id, $service);
    }

    /**
     * Download Berkas PDF Berita Acara
     */
    public function downloadPdfBeritaAcara($id, BeritaAcaraService $service)
    {
        $ba = BeritaAcara::with('aset')->findOrFail($id);

        if ($ba->sumber === 'generate') {
            $ba->file_path = $service->generatePdf($ba);
            $ba->file_ukuran = Storage::disk('public')->exists($ba->file_path) ? Storage::disk('public')->size($ba->file_path) : 0;
            $ba->save();
        } elseif (!Storage::disk('public')->exists($ba->file_path)) {
            abort(404, 'File PDF Berita Acara tidak ditemukan pada penyimpanan server.');
        }

        $fullPath = Storage::disk('public')->path($ba->file_path);
        $downloadName = $ba->file_nama_asli ?: ('BAST-' . Str::slug(str_replace('/', '-', $ba->nomor_surat ?: 'dokumen')) . '.pdf');

        return response()->download($fullPath, $downloadName);
    }

    /**
     * Endpoint AJAX untuk verifikasi keunikan nomor Berita Acara secara instan (@blur)
     */
    public function checkNomorBeritaAcara(Request $request)
    {
        $nomor = $request->query('nomor');
        if (!$nomor || !trim($nomor)) {
            return response()->json(['exists' => false]);
        }

        $normalized = BeritaAcara::normalizeNomorSurat($nomor);
        $existing = BeritaAcara::with('aset')->where('nomor_surat_normalized', $normalized)->first();

        if ($existing) {
            $asetNoReg = $existing->aset ? ($existing->aset->no_reg_pemda ?: $existing->aset->no_reg_kominfo ?: 'ID ' . $existing->aset->id) : 'tidak diketahui';
            return response()->json([
                'exists' => true,
                'message' => "Nomor Berita Acara ini sudah terpakai pada {$existing->jenis} untuk aset {$asetNoReg}."
            ]);
        }

        return response()->json(['exists' => false]);
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
            'persentase_kondisi'     => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $kondisi = $request->input('kondisi');
                    $standar = config("aset.kondisi_standar.{$kondisi}");
                    if ($standar) {
                        $val = (int)$value;
                        if ($kondisi === 'Hilang') {
                            if ($val !== 0) {
                                $fail('Persentase kondisi untuk aset Hilang harus 0%.');
                            }
                        } else {
                            if ($val < $standar['min'] || $val > $standar['max']) {
                                $fail("Persentase kondisi untuk aset {$kondisi} harus berada dalam rentang {$standar['min']}% – {$standar['max']}%.");
                            }
                        }
                    }
                },
            ],
            'keterangan_lokasi_unit' => 'required|string|max:150',
            'foto'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'persentase_kondisi.required'       => 'Persentase kondisi aset wajib diisi.',
            'persentase_kondisi.integer'        => 'Persentase kondisi harus berupa angka bulat antara 0 sampai 100.',
            'keterangan_lokasi_unit.required'   => 'Keterangan Unit Lokasi wajib diisi.',
            'foto.image'                        => 'Berkas foto harus berupa gambar.',
            'foto.mimes'                        => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto.max'                          => 'Ukuran foto aset maksimal 2MB (2048 KB).',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $imageService = app(AsetImageService::class);
            $uploadRes = $imageService->uploadAndProcess($request->file('foto'));
            $fotoPath = $uploadRes['foto'];
        }

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
            'persentase_kondisi'     => (int)$request->persentase_kondisi,
            'keterangan_lokasi_unit' => trim($request->keterangan_lokasi_unit),
            'foto'                   => $fotoPath,
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
            'persentase_kondisi'     => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $kondisi = $request->input('kondisi');
                    $standar = config("aset.kondisi_standar.{$kondisi}");
                    if ($standar) {
                        $val = (int)$value;
                        if ($kondisi === 'Hilang') {
                            if ($val !== 0) {
                                $fail('Persentase kondisi untuk aset Hilang harus 0%.');
                            }
                        } else {
                            if ($val < $standar['min'] || $val > $standar['max']) {
                                $fail("Persentase kondisi untuk aset {$kondisi} harus berada dalam rentang {$standar['min']}% – {$standar['max']}%.");
                            }
                        }
                    }
                },
            ],
            'keterangan_lokasi_unit' => 'required|string|max:150',
            'foto'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'persentase_kondisi.required'       => 'Persentase kondisi aset wajib diisi.',
            'persentase_kondisi.integer'        => 'Persentase kondisi harus berupa angka bulat antara 0 sampai 100.',
            'keterangan_lokasi_unit.required'   => 'Keterangan Unit Lokasi wajib diisi.',
            'foto.image'                        => 'Berkas foto harus berupa gambar.',
            'foto.mimes'                        => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto.max'                          => 'Ukuran foto aset maksimal 2MB (2048 KB).',
        ]);

        $imageService = app(AsetImageService::class);
        $fotoPath = $aset->foto;

        // Opsi hapus foto jika dicentang/dipilih user
        if ($request->boolean('hapus_foto') && !$request->hasFile('foto')) {
            if ($fotoPath) {
                $imageService->delete($fotoPath);
                $fotoPath = null;
            }
        }

        // Opsi ganti foto jika file baru diupload
        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                $imageService->delete($fotoPath);
            }
            $uploadRes = $imageService->uploadAndProcess($request->file('foto'));
            $fotoPath = $uploadRes['foto'];
        }

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
            'persentase_kondisi'     => (int)$request->persentase_kondisi,
            'keterangan_lokasi_unit' => trim($request->keterangan_lokasi_unit),
            'foto'                   => $fotoPath,
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



    // Halaman Kelola User
    public function users(Request $request)
    {
        $search = $request->query('search');
        $filterRole = $request->query('role');
        $filterStatus = $request->query('status');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('instansi_bidang', 'like', "%{$search}%");
            });
        }

        if ($filterRole && $filterRole !== 'all') {
            $query->where('role', $filterRole);
        }

        if ($filterStatus !== null && $filterStatus !== '' && $filterStatus !== 'all') {
            $query->where('is_active', $filterStatus === '1');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Ringkasan peran user
        $roleCounts = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'admin_kasubag' => User::where('role', 'admin_kasubag')->count(),
            'bendahara_barang' => User::where('role', 'bendahara_barang')->count(),
            'pegawai' => User::where('role', 'pegawai')->count(),
            'aktif' => User::where('is_active', true)->count(),
            'nonaktif' => User::where('is_active', false)->count(),
        ];

        return view('auth.user', compact('users', 'roleCounts', 'search', 'filterRole', 'filterStatus'));
    }

    // Tambah User Baru
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,admin_kasubag,bendahara_barang,pegawai',
            'instansi_bidang' => 'nullable|string|max:150',
        ], [
            'nama.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar pada sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.required' => 'Peran (role) pengguna wajib dipilih.',
            'role.in' => 'Peran pengguna tidak valid.',
        ]);

        User::create([
            'nama' => $request->nama,
            'nip' => $request->filled('nip') ? trim($request->nip) : null,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'golongan' => $request->golongan,
            'instansi_bidang' => $request->instansi_bidang ?: 'Diskominfo Bonebol',
            'is_active' => true,
            'must_change_password' => $request->role === 'pegawai',
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan! Pengguna dapat langsung login dan melanjutkan ke tahap Autentikasi 2FA.');
    }

    // Update Data User
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $id,
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'role' => 'required|in:admin,admin_kasubag,bendahara_barang,pegawai',
            'password' => 'nullable|min:6',
            'jabatan' => 'nullable|string|max:100',
            'golongan' => 'nullable|string|max:20',
            'instansi_bidang' => 'nullable|string|max:150',
        ], [
            'nama.required' => 'Nama pengguna wajib diisi.',
            'nip.unique' => 'NIP sudah digunakan oleh akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'role.required' => 'Peran (role) wajib dipilih.',
            'role.in' => 'Peran pengguna tidak valid.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        // Pencegahan: User yang sedang login tidak boleh mengubah role dirinya sendiri menjadi non-admin
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri menjadi selain Admin Master!');
        }

        $updateData = [
            'nama' => $request->nama,
            'email' => $request->email,
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'golongan' => $request->golongan,
            'instansi_bidang' => $request->instansi_bidang ?: $user->instansi_bidang,
        ];

        if ($request->filled('nip')) {
            $updateData['nip'] = trim($request->nip);
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            // Hanya role Pegawai yang dipaksa ganti password jika password diubah
            if ($user->role === 'pegawai') {
                $updateData['must_change_password'] = true;
            } else {
                $updateData['must_change_password'] = false;
            }
        }

        $user->update($updateData);

        return redirect()->back()->with('success', "Data pengguna '{$user->nama}' berhasil diperbarui!");
    }

    // Toggle Aktif / Nonaktifkan User
    public function toggleStatusUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun '{$user->nama}' berhasil {$statusText}.");
    }

    // Reset Password User (Pegawai -> must_change_password: true, Kasubag/Bendahara/Admin -> false)
    public function resetPasswordUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->password = Hash::make($request->password);
        if ($user->role === 'pegawai') {
            $user->must_change_password = true;
            $msg = "Kata sandi akun pegawai '{$user->nama}' berhasil direset! Pegawai wajib mengganti kata sandi saat login berikutnya.";
        } else {
            $user->must_change_password = false;
            $msg = "Kata sandi akun '{$user->nama}' berhasil direset! Pengguna dapat langsung login tanpa wajib mengganti kata sandi.";
        }
        $user->save();

        return redirect()->back()->with('success', $msg);
    }

    // Reset 2FA User (Admin Master mereset 2FA user tanpa melihat secret/recovery codes)
    public function resetTwoFactorUser($id)
    {
        $user = User::findOrFail($id);

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->back()->with('success', "Autentikasi Dua Faktor (2FA) untuk '{$user->nama}' berhasil direset! Pengguna wajib melakukan setup 2FA baru saat login berikutnya.");
    }

    // Hapus User
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $userName = $user->nama;
        $user->delete();
        return redirect()->back()->with('success', "Pengguna '{$userName}' berhasil dihapus dari sistem!");
    }

    // Halaman Detail Publik Aset (Scan QR Code)
    public function publicDetail($id)
    {
        $decodedId = urldecode($id);
        $aset = AsetPeralatanMesin::with(['beritaAcaras' => function ($q) {
                $q->orderBy('tanggal', 'desc');
            }])
            ->where('id', $decodedId)
            ->orWhere('no_reg_pemda', $decodedId)
            ->orWhere('no_reg_kominfo', $decodedId)
            ->orWhere('no_reg_pemda', $id)
            ->first();
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
        if (!$aset) {
            abort(404, 'Data aset tidak ditemukan.');
        }

        $targetUrl = $aset->public_url;

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)
                ->margin(1)
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

        if (!$aset) {
            return response()->json([
                'success' => false,
                'message' => 'Data aset dengan No. Reg "' . $decodedId . '" tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'no_reg_pemda' => $aset->no_reg_pemda,
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
        // 1. Query data aset tabel laporan (mengikuti seluruh filter aktif yang dipilih)
        $query = AsetPeralatanMesin::query();
        $this->applyAsetFilters($query, $request);
        $aset = $query->orderBy('created_at', 'desc')->get();

        // 2. Ringkasan jumlah per kondisi dari aset yang difilter
        $kondisiCounts = [
            'Baik'         => $aset->where('kondisi', 'Baik')->count(),
            'Rusak Ringan' => $aset->where('kondisi', 'Rusak Ringan')->count(),
            'Rusak Berat'  => $aset->where('kondisi', 'Rusak Berat')->count(),
            'Hilang'       => $aset->where('kondisi', 'Hilang')->count(),
            'total'        => $aset->count(),
        ];

        $filterBulan = $request->query('filter_bulan', 'all');
        $filterTahun = $request->query('filter_tahun', date('Y'));

        $filename = 'laporan-rekap-aset-' . date('Ymd-His') . '.pdf';

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('admin.laporan_pdf', compact('aset', 'kondisiCounts', 'filterBulan', 'filterTahun'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download($filename);
        }

        return view('admin.laporan_pdf', compact('aset', 'kondisiCounts', 'filterBulan', 'filterTahun'));
    }

    public function magang()
    {
        if (!class_exists(\App\Models\PendaftaranMagang::class)) {
            return redirect()->route('admin.aset');
        }
        $magang = \App\Models\PendaftaranMagang::with(['user', 'suratBalasan'])->latest()->paginate(10);
        return view('auth.magang', compact('magang'));
    }

    public function updateStatusMagang(Request $request, $id)
    {
        if (!class_exists(\App\Models\PendaftaranMagang::class)) {
            return redirect()->back();
        }
        $request->validate([
            'status' => 'required|in:Diterima,Ditolak',
            'no_surat' => 'nullable|string|max:100',
            'file_surat_balasan' => 'nullable|file|mimes:pdf|max:2048',
            'catatan_admin' => 'nullable|string',
        ]);

        $pendaftaran = \App\Models\PendaftaranMagang::findOrFail($id);

        if ($request->status === 'Ditolak') {
            if ($pendaftaran->surat_pengantar) {
                Storage::disk('public')->delete($pendaftaran->surat_pengantar);
            }
            if ($pendaftaran->surat_balasan_magang_id) {
                $surat = \App\Models\SuratBalasanMagang::find($pendaftaran->surat_balasan_magang_id);
                if ($surat && $surat->file_surat) {
                    Storage::disk('public')->delete($surat->file_surat);
                }
                $surat?->delete();
            }
            $pendaftaran->delete();
            return redirect()->back()->with('success', 'Pendaftaran magang berhasil ditolak dan data telah dibersihkan.');
        }

        $pendaftaran->update(['status' => 'Diterima']);

        if ($request->hasFile('file_surat_balasan')) {
            $filePath = $request->file('file_surat_balasan')->store('surat_balasan', 'public');
            $surat = \App\Models\SuratBalasanMagang::create([
                'pendaftaran_magang_id' => $pendaftaran->id,
                'no_surat' => $request->no_surat ?? ('B/' . date('Ymd') . '/' . $pendaftaran->id),
                'file_surat' => $filePath,
                'status' => 'Diterima',
                'catatan_admin' => $request->catatan_admin,
            ]);
            $pendaftaran->update(['surat_balasan_magang_id' => $surat->id]);
        }

        return redirect()->back()->with('success', 'Status pendaftaran magang berhasil diperbarui menjadi Diterima.');
    }
}