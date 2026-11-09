<?php

namespace App\Http\Controllers;

use App\Models\ArsipDokumen;
use App\Models\JenisDokumen;
use App\Models\KategoriDokumen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ArsipDokumenController extends Controller
{
    /**
     * Dashboard "Arsip Dokumen" - Khusus Role Pegawai
     * Menampilkan hanya dokumen milik pegawai yang sedang login
     */
    public function myDocuments(Request $request)
    {
        $user = Auth::user();
        $query = ArsipDokumen::with(['user', 'uploader', 'lastActionUser', 'kategoriDokumen', 'jenisDokumen'])
            ->where('user_id', $user->id);

        // Filter Pencarian kata kunci
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_dokumen', 'like', "%{$search}%")
                  ->orWhere('nomor_dokumen', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('tmt_tahun', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        $kategori = $request->query('kategori');
        if ($kategori && $kategori !== 'all') {
            $query->where(function ($q) use ($kategori) {
                $q->where('kategori', $kategori)
                  ->orWhere('kategori_dokumen_id', $kategori);
            });
        }

        // Filter Sub-Kategori / Jenis Dokumen
        $jenisDokumen = $request->query('jenis_dokumen');
        if ($jenisDokumen && $jenisDokumen !== 'all') {
            $query->where(function ($q) use ($jenisDokumen) {
                $q->where('jenis_dokumen', $jenisDokumen)
                  ->orWhere('jenis_dokumen_id', $jenisDokumen);
            });
        }

        $dokumens = $query->latest('updated_at')->paginate(10)->withQueryString();

        // Master 11 Kategori Baku beserta Sub-Kategori Jenis Dokumen
        $masterKategori = KategoriDokumen::with(['jenisDokumens' => function ($q) {
            $q->orderBy('urutan');
        }])->orderBy('urutan')->get();

        // Kumpulkan jenis dokumen unik yang pernah diinput pada kategori "Dokumen Lainnya" oleh pegawai ini
        $customJenisLainnya = ArsipDokumen::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('kategori', 'Dokumen Lainnya')
                  ->orWhereHas('kategoriDokumen', function ($kq) {
                      $kq->where('nama_kategori', 'Dokumen Lainnya');
                  });
            })
            ->whereNotNull('jenis_dokumen')
            ->where('jenis_dokumen', '!=', '')
            ->distinct()
            ->pluck('jenis_dokumen')
            ->filter()
            ->values();

        // Lampirkan opsi jenis dokumen unik ke kategori Dokumen Lainnya untuk filter
        foreach ($masterKategori as $kat) {
            if ($kat->nama_kategori === 'Dokumen Lainnya' || $kat->urutan == 11) {
                $customItems = $customJenisLainnya->map(function ($val, $idx) use ($kat) {
                    return (object)[
                        'id' => $val,
                        'kategori_dokumen_id' => $kat->id,
                        'nama_jenis' => $val,
                        'urutan' => $idx + 1,
                    ];
                });
                $kat->setRelation('jenisDokumens', $customItems);
            }
        }

        // Kategori yang ada di dokumen pegawai ini untuk filter cepat
        $kategoriList = ArsipDokumen::where('user_id', $user->id)
            ->distinct()
            ->pluck('kategori')
            ->filter();

        return view('pegawai.dokumen', compact('dokumens', 'user', 'search', 'kategori', 'jenisDokumen', 'kategoriList', 'masterKategori'));
    }

    /**
     * Upload Dokumen Baru oleh Pegawai (Khusus untuk dirinya sendiri)
     */
    public function storePegawai(Request $request)
    {
        $kategoriModel = KategoriDokumen::findOrFail($request->kategori_dokumen_id);
        $isDokumenLainnya = ($kategoriModel->nama_kategori === 'Dokumen Lainnya' || $kategoriModel->urutan == 11);

        $rules = [
            'kategori_dokumen_id' => 'required|exists:kategori_dokumen,id',
            'tmt_tahun' => 'required|digits:4|integer|min:1950|max:2099',
            'nomor_dokumen' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
            'file_dokumen' => 'required|file|mimes:pdf|max:2048', // Wajib PDF, Maksimal 2MB (2048 KB)
        ];

        if ($isDokumenLainnya) {
            $rules['jenis_dokumen'] = 'required|string|max:150';
        } else {
            $rules['jenis_dokumen_id'] = 'required|exists:jenis_dokumen,id';
        }

        $messages = [
            'kategori_dokumen_id.required' => 'Kategori dokumen wajib dipilih.',
            'kategori_dokumen_id.exists' => 'Kategori dokumen yang dipilih tidak valid.',
            'jenis_dokumen.required' => 'Jenis dokumen wajib diisi.',
            'jenis_dokumen.max' => 'Jenis dokumen maksimal 150 karakter.',
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen yang dipilih tidak valid.',
            'tmt_tahun.required' => 'TMT (Tahun) dokumen wajib diisi.',
            'tmt_tahun.digits' => 'TMT (Tahun) harus berupa 4 digit tahun (contoh: 2024).',
            'file_dokumen.required' => 'File berkas dokumen wajib diunggah.',
            'file_dokumen.mimes' => 'Format berkas harus berupa file PDF.',
            'file_dokumen.max' => 'Ukuran berkas melebihi batas maksimal 2MB (2048 KB).',
        ];

        $request->validate($rules, $messages);

        if ($isDokumenLainnya) {
            $jenisDokumenId = null;
            $jenisDokumenNama = trim($request->jenis_dokumen);
        } else {
            $jenisModel = JenisDokumen::findOrFail($request->jenis_dokumen_id);
            $jenisDokumenId = $jenisModel->id;
            $jenisDokumenNama = $jenisModel->nama_jenis;
        }

        $file = $request->file('file_dokumen');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $path = $file->store('arsip_dokumen', 'public');

        $currentUser = Auth::user();

        ArsipDokumen::create([
            'user_id' => $currentUser->id, // Terkunci otomatis hanya untuk diri sendiri
            'kategori_dokumen_id' => $kategoriModel->id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'kategori' => $kategoriModel->nama_kategori,
            'jenis_dokumen' => $jenisDokumenNama,
            'tmt_tahun' => $request->tmt_tahun,
            'nomor_dokumen' => $request->nomor_dokumen,
            'keterangan' => $request->keterangan,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'uploaded_by' => $currentUser->id,
            'last_action_by' => $currentUser->id,
            'last_action_role' => $currentUser->nama,
            'last_action_type' => 'upload',
        ]);

        return redirect()->back()->with('success', 'Dokumen kepegawaian Anda berhasil diunggah!');
    }

    /**
     * Edit Dokumen oleh Pegawai (Hanya Dokumen Miliknya Sendiri)
     */
    public function updatePegawai(Request $request, $id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);
        $currentUser = Auth::user();

        // ATURAN KEAMANAN KETAT: Pegawai HANYA boleh mengubah dokumen miliknya sendiri
        if ($dokumen->user_id !== $currentUser->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengubah dokumen pegawai lain.');
        }

        $kategoriModel = KategoriDokumen::findOrFail($request->kategori_dokumen_id);
        $isDokumenLainnya = ($kategoriModel->nama_kategori === 'Dokumen Lainnya' || $kategoriModel->urutan == 11);

        $rules = [
            'kategori_dokumen_id' => 'required|exists:kategori_dokumen,id',
            'tmt_tahun' => 'required|digits:4|integer|min:1950|max:2099',
            'nomor_dokumen' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
            'file_dokumen' => 'nullable|file|mimes:pdf|max:2048', // Opsional ganti berkas, wajib PDF, max 2MB
        ];

        if ($isDokumenLainnya) {
            $rules['jenis_dokumen'] = 'required|string|max:150';
        } else {
            $rules['jenis_dokumen_id'] = 'required|exists:jenis_dokumen,id';
        }

        $messages = [
            'kategori_dokumen_id.required' => 'Kategori dokumen wajib dipilih.',
            'kategori_dokumen_id.exists' => 'Kategori dokumen yang dipilih tidak valid.',
            'jenis_dokumen.required' => 'Jenis dokumen wajib diisi.',
            'jenis_dokumen.max' => 'Jenis dokumen maksimal 150 karakter.',
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen yang dipilih tidak valid.',
            'tmt_tahun.required' => 'TMT (Tahun) dokumen wajib diisi.',
            'tmt_tahun.digits' => 'TMT (Tahun) harus berupa 4 digit tahun (contoh: 2024).',
            'file_dokumen.mimes' => 'Format berkas harus berupa file PDF.',
            'file_dokumen.max' => 'Ukuran berkas melebihi batas maksimal 2MB (2048 KB).',
        ];

        $request->validate($rules, $messages);

        if ($isDokumenLainnya) {
            $jenisDokumenId = null;
            $jenisDokumenNama = trim($request->jenis_dokumen);
        } else {
            $jenisModel = JenisDokumen::findOrFail($request->jenis_dokumen_id);
            $jenisDokumenId = $jenisModel->id;
            $jenisDokumenNama = $jenisModel->nama_jenis;
        }

        $updateData = [
            'kategori_dokumen_id' => $kategoriModel->id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'kategori' => $kategoriModel->nama_kategori,
            'jenis_dokumen' => $jenisDokumenNama,
            'tmt_tahun' => $request->tmt_tahun,
            'nomor_dokumen' => $request->nomor_dokumen,
            'keterangan' => $request->keterangan,
            'last_action_by' => $currentUser->id,
            'last_action_role' => $currentUser->nama,
            'last_action_type' => 'update',
        ];

        // Jika Pegawai mengganti file dokumen fisik
        if ($request->hasFile('file_dokumen')) {
            if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }

            $file = $request->file('file_dokumen');
            $updateData['file_path'] = $file->store('arsip_dokumen', 'public');
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
        }

        $dokumen->update($updateData);

        return redirect()->back()->with('success', 'Dokumen Anda berhasil diperbarui!');
    }

    /**
     * Hapus Dokumen oleh Pegawai (Hanya Dokumen Miliknya Sendiri)
     */
    public function destroyPegawai($id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);
        $currentUser = Auth::user();

        // ATURAN KEAMANAN KETAT: Pegawai HANYA boleh menghapus dokumen miliknya sendiri
        if ($dokumen->user_id !== $currentUser->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus dokumen pegawai lain.');
        }

        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $dokumen->delete();

        return redirect()->back()->with('success', 'Dokumen Anda berhasil dihapus permanen!');
    }

    /**
     * Kelola Seluruh Arsip Dokumen Pegawai - Khusus Kasubag & Admin Master
     */
    public function index(Request $request)
    {
        $query = ArsipDokumen::with(['user', 'uploader', 'lastActionUser', 'kategoriDokumen', 'jenisDokumen']);

        // Filter Pegawai
        $filterUser = $request->query('user_id');
        if ($filterUser && $filterUser !== 'all') {
            $query->where('user_id', $filterUser);
        }

        // Filter Kategori
        $filterKategori = $request->query('kategori');
        if ($filterKategori && $filterKategori !== 'all') {
            $query->where(function ($q) use ($filterKategori) {
                $q->where('kategori', $filterKategori)
                  ->orWhere('kategori_dokumen_id', $filterKategori);
            });
        }

        // Filter Sub-Kategori / Jenis Dokumen
        $filterJenis = $request->query('jenis_dokumen');
        if ($filterJenis && $filterJenis !== 'all') {
            $query->where(function ($q) use ($filterJenis) {
                $q->where('jenis_dokumen', $filterJenis)
                  ->orWhere('jenis_dokumen_id', $filterJenis);
            });
        }

        // Filter Tahun / TMT
        $filterTahun = $request->query('tahun');
        if ($filterTahun && $filterTahun !== 'all') {
            $query->where('tmt_tahun', $filterTahun);
        }

        // Pencarian Teks
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_dokumen', 'like', "%{$search}%")
                  ->orWhere('nomor_dokumen', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nama', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $dokumens = $query->latest('updated_at')->paginate(10)->withQueryString();

        // Master 11 Kategori beserta relasi Sub-Kategori Jenis Dokumen
        $masterKategori = KategoriDokumen::with(['jenisDokumens' => function ($q) {
            $q->orderBy('urutan');
        }])->orderBy('urutan')->get();

        // Kumpulkan jenis dokumen unik yang pernah diinput pada kategori "Dokumen Lainnya"
        $customJenisLainnya = ArsipDokumen::where(function ($q) {
            $q->where('kategori', 'Dokumen Lainnya')
              ->orWhereHas('kategoriDokumen', function ($kq) {
                  $kq->where('nama_kategori', 'Dokumen Lainnya');
              });
        })
        ->whereNotNull('jenis_dokumen')
        ->where('jenis_dokumen', '!=', '')
        ->distinct()
        ->pluck('jenis_dokumen')
        ->filter()
        ->values();

        // Lampirkan opsi jenis dokumen unik ke kategori Dokumen Lainnya untuk filter
        foreach ($masterKategori as $kat) {
            if ($kat->nama_kategori === 'Dokumen Lainnya' || $kat->urutan == 11) {
                $customItems = $customJenisLainnya->map(function ($val, $idx) use ($kat) {
                    return (object)[
                        'id' => $val,
                        'kategori_dokumen_id' => $kat->id,
                        'nama_jenis' => $val,
                        'urutan' => $idx + 1,
                    ];
                });
                $kat->setRelation('jenisDokumens', $customItems);
            }
        }

        // Daftar seluruh pegawai/ASN (termasuk Kasubag & Bendahara) untuk filter & modal upload
        $pegawaiList = User::whereIn('role', ['pegawai', 'admin_kasubag', 'kasubag', 'bendahara_barang'])
            ->withCount('arsipDokumens')
            ->orderByRaw('urutan IS NULL, urutan ASC, nama ASC')
            ->get();

        // Rekap statistik arsip dokumen
        $stats = [
            'total_dokumen' => ArsipDokumen::count(),
            'total_pegawai' => User::whereIn('role', ['pegawai', 'admin_kasubag', 'kasubag', 'bendahara_barang'])->count(),
            'total_kategori' => KategoriDokumen::count(),
            'tmt_terbaru' => ArsipDokumen::whereNotNull('tmt_tahun')->max('tmt_tahun') ?? date('Y'),
        ];

        return view('admin.arsip.index', compact('dokumens', 'pegawaiList', 'masterKategori', 'stats', 'filterUser', 'filterKategori', 'filterJenis', 'filterTahun', 'search'));
    }

    /**
     * Upload Dokumen Baru oleh Kasubag / Admin
     */
    public function store(Request $request)
    {
        $kategoriModel = KategoriDokumen::findOrFail($request->kategori_dokumen_id);
        $isDokumenLainnya = ($kategoriModel->nama_kategori === 'Dokumen Lainnya' || $kategoriModel->urutan == 11);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'kategori_dokumen_id' => 'required|exists:kategori_dokumen,id',
            'tmt_tahun' => 'required|digits:4|integer|min:1950|max:2099',
            'nomor_dokumen' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
            'file_dokumen' => 'required|file|mimes:pdf|max:2048', // Wajib PDF, Maksimal 2MB (2048 KB)
        ];

        if ($isDokumenLainnya) {
            $rules['jenis_dokumen'] = 'required|string|max:150';
        } else {
            $rules['jenis_dokumen_id'] = 'required|exists:jenis_dokumen,id';
        }

        $messages = [
            'user_id.required' => 'Pegawai terkait wajib dipilih.',
            'user_id.exists' => 'Data pegawai yang dipilih tidak valid.',
            'kategori_dokumen_id.required' => 'Kategori dokumen wajib dipilih.',
            'kategori_dokumen_id.exists' => 'Kategori dokumen yang dipilih tidak valid.',
            'jenis_dokumen.required' => 'Jenis dokumen wajib diisi.',
            'jenis_dokumen.max' => 'Jenis dokumen maksimal 150 karakter.',
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen yang dipilih tidak valid.',
            'tmt_tahun.required' => 'TMT (Tahun) dokumen wajib diisi.',
            'tmt_tahun.digits' => 'TMT (Tahun) harus berupa 4 digit tahun (contoh: 2024).',
            'file_dokumen.required' => 'File berkas dokumen wajib diunggah.',
            'file_dokumen.mimes' => 'Format berkas harus berupa file PDF.',
            'file_dokumen.max' => 'Ukuran berkas melebihi batas maksimal 2MB (2048 KB).',
        ];

        $request->validate($rules, $messages);

        if ($isDokumenLainnya) {
            $jenisDokumenId = null;
            $jenisDokumenNama = trim($request->jenis_dokumen);
        } else {
            $jenisModel = JenisDokumen::findOrFail($request->jenis_dokumen_id);
            $jenisDokumenId = $jenisModel->id;
            $jenisDokumenNama = $jenisModel->nama_jenis;
        }

        $file = $request->file('file_dokumen');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $path = $file->store('arsip_dokumen', 'public');

        $currentUser = Auth::user();
        $roleLabel = in_array($currentUser->role, ['kasubag', 'admin_kasubag']) 
            ? 'Kasubag' 
            : ($currentUser->role === 'admin' ? 'Admin Master' : $currentUser->nama);

        ArsipDokumen::create([
            'user_id' => $request->user_id,
            'kategori_dokumen_id' => $kategoriModel->id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'kategori' => $kategoriModel->nama_kategori,
            'jenis_dokumen' => $jenisDokumenNama,
            'tmt_tahun' => $request->tmt_tahun,
            'nomor_dokumen' => $request->nomor_dokumen,
            'keterangan' => $request->keterangan,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'uploaded_by' => $currentUser->id,
            'last_action_by' => $currentUser->id,
            'last_action_role' => $roleLabel,
            'last_action_type' => 'upload',
        ]);

        return redirect()->back()->with('success', 'Arsip dokumen berhasil diunggah dan disimpan dalam sistem!');
    }

    /**
     * Perbarui Data Dokumen oleh Kasubag / Admin
     */
    public function update(Request $request, $id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);

        $kategoriModel = KategoriDokumen::findOrFail($request->kategori_dokumen_id);
        $isDokumenLainnya = ($kategoriModel->nama_kategori === 'Dokumen Lainnya' || $kategoriModel->urutan == 11);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'kategori_dokumen_id' => 'required|exists:kategori_dokumen,id',
            'tmt_tahun' => 'required|digits:4|integer|min:1950|max:2099',
            'nomor_dokumen' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
            'file_dokumen' => 'nullable|file|mimes:pdf|max:2048', // Opsional ganti berkas, wajib PDF, max 2MB
        ];

        if ($isDokumenLainnya) {
            $rules['jenis_dokumen'] = 'required|string|max:150';
        } else {
            $rules['jenis_dokumen_id'] = 'required|exists:jenis_dokumen,id';
        }

        $messages = [
            'user_id.required' => 'Pegawai terkait wajib dipilih.',
            'user_id.exists' => 'Data pegawai yang dipilih tidak valid.',
            'kategori_dokumen_id.required' => 'Kategori dokumen wajib dipilih.',
            'kategori_dokumen_id.exists' => 'Kategori dokumen yang dipilih tidak valid.',
            'jenis_dokumen.required' => 'Jenis dokumen wajib diisi.',
            'jenis_dokumen.max' => 'Jenis dokumen maksimal 150 karakter.',
            'jenis_dokumen_id.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen_id.exists' => 'Jenis dokumen yang dipilih tidak valid.',
            'tmt_tahun.required' => 'TMT (Tahun) dokumen wajib diisi.',
            'tmt_tahun.digits' => 'TMT (Tahun) harus berupa 4 digit tahun (contoh: 2024).',
            'file_dokumen.mimes' => 'Format berkas harus berupa file PDF.',
            'file_dokumen.max' => 'Ukuran berkas melebihi batas maksimal 2MB (2048 KB).',
        ];

        $request->validate($rules, $messages);

        if ($isDokumenLainnya) {
            $jenisDokumenId = null;
            $jenisDokumenNama = trim($request->jenis_dokumen);
        } else {
            $jenisModel = JenisDokumen::findOrFail($request->jenis_dokumen_id);
            $jenisDokumenId = $jenisModel->id;
            $jenisDokumenNama = $jenisModel->nama_jenis;
        }

        $currentUser = Auth::user();
        $roleLabel = in_array($currentUser->role, ['kasubag', 'admin_kasubag']) 
            ? 'Kasubag' 
            : ($currentUser->role === 'admin' ? 'Admin Master' : $currentUser->nama);

        $updateData = [
            'user_id' => $request->user_id,
            'kategori_dokumen_id' => $kategoriModel->id,
            'jenis_dokumen_id' => $jenisDokumenId,
            'kategori' => $kategoriModel->nama_kategori,
            'jenis_dokumen' => $jenisDokumenNama,
            'tmt_tahun' => $request->tmt_tahun,
            'nomor_dokumen' => $request->nomor_dokumen,
            'keterangan' => $request->keterangan,
            'last_action_by' => $currentUser->id,
            'last_action_role' => $roleLabel,
            'last_action_type' => 'update',
        ];

        // Jika ada pergantian file dokumen fisik
        if ($request->hasFile('file_dokumen')) {
            if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }

            $file = $request->file('file_dokumen');
            $updateData['file_path'] = $file->store('arsip_dokumen', 'public');
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
        }

        $dokumen->update($updateData);

        return redirect()->back()->with('success', 'Data arsip dokumen berhasil diperbarui!');
    }

    /**
     * Hapus Dokumen oleh Kasubag / Admin
     */
    public function destroy($id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);

        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $dokumen->delete();

        return redirect()->back()->with('success', 'Arsip dokumen berhasil dihapus permanen dari sistem beserta file fisiknya!');
    }

    /**
     * Preview File PDF Langsung di Browser / Modal (Inline Stream)
     */
    public function preview($id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);
        $user = Auth::user();

        // ATURAN KEAMANAN: Role Pegawai HANYA boleh melihat dokumen miliknya sendiri
        if ($user->role === 'pegawai' && $dokumen->user_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat dokumen milik pegawai lain.');
        }

        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File dokumen tidak ditemukan pada penyimpanan server.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $dokumen->file_name . '"',
        ]);
    }

    /**
     * Unduh File Dokumen dengan Validasi Otorisasi Ketat (Anti-IDOR)
     */
    public function download($id)
    {
        $dokumen = ArsipDokumen::findOrFail($id);
        $user = Auth::user();

        // ATURAN KEAMANAN: Role Pegawai HANYA boleh mengunduh dokumen miliknya sendiri
        if ($user->role === 'pegawai' && $dokumen->user_id !== $user->id) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses dokumen milik pegawai lain.');
        }

        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            return redirect()->back()->with('error', 'File dokumen tidak ditemukan pada penyimpanan server.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);
        $mimeType = Storage::disk('public')->mimeType($dokumen->file_path) ?: 'application/octet-stream';

        return response()->download($fullPath, $dokumen->file_name, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Tambah Akun Pegawai Baru oleh Kasubag (Nama, NIP, Jabatan, Golongan)
     */
    public function storePegawaiAccount(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'nip' => 'required|string|max:50|unique:users,nip',
            'jabatan' => 'required|string|max:100',
            'golongan' => 'required|string|max:20',
            'urutan' => 'nullable|integer|min:1',
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nip.required' => 'NIP pegawai wajib diisi.',
            'nip.unique' => 'NIP pegawai sudah terdaftar pada sistem SIMPATIK.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
            'golongan.required' => 'Golongan pegawai wajib dipilih dari opsi baku.',
            'urutan.integer' => 'Nomor urutan harus berupa angka bulat positif.',
        ]);

        $nipClean = trim($request->nip);
        $internalEmail = $nipClean . '@pegawai.simpatik.local';

        // Jika email internal kebetulan sudah ada, gunakan unik
        if (User::where('email', $internalEmail)->exists()) {
            $internalEmail = $nipClean . '.' . time() . '@pegawai.simpatik.local';
        }

        $defaultPassword = config('auth.default_pegawai_password', 'Kominfo#2026');
        $urutanInput = $request->filled('urutan') ? (int)$request->urutan : null;

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $nipClean, $internalEmail, $defaultPassword, $urutanInput) {
            $maxUrutan = User::max('urutan') ?? 0;
            $finalUrutan = $urutanInput ?? ($maxUrutan + 1);

            if ($urutanInput && $urutanInput <= $maxUrutan) {
                // Geser urutan pegawai di bawahnya (+1)
                User::whereNotNull('urutan')
                    ->where('urutan', '>=', $finalUrutan)
                    ->increment('urutan');
            }

            return User::create([
                'nama' => trim($request->nama),
                'nip' => $nipClean,
                'email' => $internalEmail,
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
                'role' => 'pegawai',
                'jabatan' => trim($request->jabatan),
                'golongan' => trim($request->golongan),
                'urutan' => $finalUrutan,
                'instansi_bidang' => 'Dinas Kominfo Bone Bolango',
                'is_active' => true,
                'must_change_password' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ]);
        });

        return redirect()->back()->with('success', "Akun Pegawai '{$user->nama}' (NIP: {$user->nip}) berhasil dibuat pada nomor urutan {$user->urutan}! Pegawai dapat login dengan NIP dan password default, serta wajib mengganti password dan mengaktifkan 2FA saat pertama kali login.");
    }

    /**
     * Edit Akun Pegawai oleh Kasubag (Nama, Jabatan, Golongan, Urutan — NIP Permanen)
     */
    public function updatePegawaiAccount(Request $request, $id)
    {
        $user = User::where('role', 'pegawai')->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'golongan' => 'required|string|max:20',
            'urutan' => 'nullable|integer|min:1',
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
            'golongan.required' => 'Golongan pegawai wajib dipilih.',
            'urutan.integer' => 'Nomor urutan harus berupa angka bulat positif.',
        ]);

        $newUrutan = $request->filled('urutan') ? (int)$request->urutan : $user->urutan;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $newUrutan) {
            $oldUrutan = $user->urutan;

            if ($newUrutan !== null && $oldUrutan !== null && $newUrutan !== $oldUrutan) {
                if ($newUrutan < $oldUrutan) {
                    // Geser ke atas: rank di antara newUrutan dan oldUrutan - 1 bergeser ke bawah (+1)
                    User::where('id', '!=', $user->id)
                        ->whereNotNull('urutan')
                        ->whereBetween('urutan', [$newUrutan, $oldUrutan - 1])
                        ->increment('urutan');
                } else {
                    // Geser ke bawah: rank di antara oldUrutan + 1 dan newUrutan bergeser ke atas (-1)
                    User::where('id', '!=', $user->id)
                        ->whereNotNull('urutan')
                        ->whereBetween('urutan', [$oldUrutan + 1, $newUrutan])
                        ->decrement('urutan');
                }
            } elseif ($newUrutan !== null && $oldUrutan === null) {
                User::where('id', '!=', $user->id)
                    ->whereNotNull('urutan')
                    ->where('urutan', '>=', $newUrutan)
                    ->increment('urutan');
            }

            $user->nama = trim($request->nama);
            $user->jabatan = trim($request->jabatan);
            $user->golongan = trim($request->golongan);
            $user->urutan = $newUrutan;
            $user->save();
        });

        return redirect()->back()->with('success', "Data akun pegawai '{$user->nama}' (NIP: {$user->nip}) berhasil diperbarui!");
    }

    /**
     * Toggle Aktif / Nonaktifkan Akun Pegawai oleh Kasubag
     */
    public function toggleStatusPegawaiAccount($id)
    {
        $user = User::where('role', 'pegawai')->findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun pegawai '{$user->nama}' (NIP: {$user->nip}) berhasil {$statusText}.");
    }

    /**
     * Hapus Akun Pegawai oleh Kasubag / Admin Master (Permanen)
     * Menghapus akun pegawai, dokumen arsip miliknya, file PDF di storage, session, dan token
     */
    public function destroyPegawaiAccount(Request $request, $id)
    {
        $currentUser = Auth::user();

        // Validasi Wewenang: Hanya Kasubag dan Admin Master
        if (!in_array($currentUser?->role, ['kasubag', 'admin_kasubag', 'admin'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus akun pegawai.');
        }

        $user = User::findOrFail($id);

        // Validasi Backend Ketat: HANYA role pegawai yang boleh dihapus dari fitur ini
        if ($user->role !== 'pegawai') {
            abort(403, 'Akses Ditolak: Fitur ini hanya dapat digunakan untuk menghapus akun ber-role Pegawai.');
        }

        // Ambil seluruh dokumen milik pegawai
        $dokumens = ArsipDokumen::where('user_id', $user->id)->get();
        $dokumenCount = $dokumens->count();

        // 1. Hapus file fisik PDF dari storage disk public dan data record dokumen
        foreach ($dokumens as $dokumen) {
            if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }
            $dokumen->delete();
        }

        // 2. Bersihkan session aktif jika tabel sessions ada
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // 3. Bersihkan token reset kata sandi jika ada
        if (Schema::hasTable('password_reset_tokens')) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        }

        $pegawaiNama = $user->nama;
        $pegawaiNip = $user->nip;
        $pegawaiEmail = $user->email;

        // 4. Hapus akun pengguna (data 2FA di kolom user otomatis ikut terhapus)
        $user->delete();

        // 5. Catat ke log aplikasi (tanpa password atau secret 2FA)
        Log::info('Akun Pegawai berhasil dihapus permanen oleh ' . $currentUser->nama . ' (' . $currentUser->role . ' - ID: ' . $currentUser->id . ')', [
            'action' => 'delete_pegawai_account',
            'deleted_by_id' => $currentUser->id,
            'deleted_by_name' => $currentUser->nama,
            'deleted_by_role' => $currentUser->role,
            'pegawai_id' => $id,
            'pegawai_nama' => $pegawaiNama,
            'pegawai_nip' => $pegawaiNip,
            'pegawai_email' => $pegawaiEmail,
            'deleted_documents_count' => $dokumenCount,
            'timestamp' => now()->toIso8601String(),
        ]);

        return redirect()->back()->with('success', "Akun pegawai '{$pegawaiNama}' (NIP: " . ($pegawaiNip ?: '-') . ") beserta {$dokumenCount} berkas dokumen arsip fisiknya berhasil dihapus permanen.");
    }
}
