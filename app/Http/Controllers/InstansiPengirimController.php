<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstansiPengirim;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InstansiPengirimController extends Controller
{
    /**
     * Tampilkan halaman kelola daftar instansi pengirim.
     * Dapat diakses oleh Admin Master (Role 1 - Read Only) dan Admin Kasubag (Role 2 - Full Access).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $instansis = InstansiPengirim::query()
            ->select('instansi_pengirim.*')
            ->selectRaw('(SELECT COUNT(*) FROM surat_masuk WHERE surat_masuk.asal_surat = instansi_pengirim.nama) as surat_masuk_count')
            ->when($request->filled('search'), function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalInstansi = InstansiPengirim::count();

        return view('surat-masuk.instansi.index', compact('instansis', 'totalInstansi', 'search'));
    }

    /**
     * Endpoint API untuk menghitung berapa surat masuk yang menggunakan instansi ini.
     * Dipakai dialog konfirmasi sebelum simpan perubahan atau hapus.
     */
    public function countAffected($id)
    {
        $instansi = InstansiPengirim::findOrFail($id);
        $count = SuratMasuk::where('asal_surat', $instansi->nama)->count();

        return response()->json([
            'id' => $instansi->id,
            'nama' => $instansi->nama,
            'count' => $count,
        ]);
    }

    /**
     * Tambah instansi baru ke master.
     * Khusus Admin Kasubag (Role 2).
     */
    public function store(Request $request)
    {
        $this->authorizeKasubag();

        if ($request->wantsJson() || $request->ajax()) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
            ], [
                'nama.required' => 'Nama instansi pengirim wajib diisi.',
                'nama.max' => 'Nama instansi pengirim maksimal 255 karakter.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('nama'),
                ], 422);
            }

            $clean = InstansiPengirim::cleanDisplayName($request->nama);
            $normal = InstansiPengirim::normalizeName($clean);

            if (empty($normal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama instansi pengirim tidak boleh kosong.',
                ], 422);
            }

            if (InstansiPengirim::where('nama_normal', $normal)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Instansi '{$clean}' (atau variasi ejaannya) sudah terdaftar dalam master.",
                ], 422);
            }

            $instansi = InstansiPengirim::create([
                'nama' => $clean,
                'nama_normal' => $normal,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Instansi '{$clean}' berhasil ditambahkan ke daftar master saran.",
                'data' => [
                    'id' => $instansi->id,
                    'nama' => $instansi->nama,
                    'surat_masuk_count' => 0,
                    'created_at_formatted' => $instansi->created_at->translatedFormat('d M Y'),
                ],
                'total_count' => InstansiPengirim::count(),
            ]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Nama instansi pengirim wajib diisi.',
            'nama.max' => 'Nama instansi pengirim maksimal 255 karakter.',
        ]);

        $clean = InstansiPengirim::cleanDisplayName($request->nama);
        $normal = InstansiPengirim::normalizeName($clean);

        if (empty($normal)) {
            return back()->withInput()->with('error', 'Nama instansi pengirim tidak boleh kosong.');
        }

        if (InstansiPengirim::where('nama_normal', $normal)->exists()) {
            return back()->withInput()->with('error', "Instansi '{$clean}' (atau variasi ejaannya) sudah terdaftar dalam master.");
        }

        InstansiPengirim::create([
            'nama' => $clean,
            'nama_normal' => $normal,
        ]);

        return back()->with('success', "Instansi '{$clean}' berhasil ditambahkan ke daftar master saran.");
    }

    /**
     * Ubah nama instansi pengirim.
     * Ikut memperbarui 'asal_surat' pada surat_masuk yang memakai nama lama dalam 1 transaksi.
     * Khusus Admin Kasubag (Role 2).
     */
    public function update(Request $request, $id)
    {
        $this->authorizeKasubag();

        $instansi = InstansiPengirim::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Nama instansi pengirim wajib diisi.',
            'nama.max' => 'Nama instansi pengirim maksimal 255 karakter.',
        ]);

        $oldNama = $instansi->nama;
        $clean = InstansiPengirim::cleanDisplayName($request->nama);
        $normal = InstansiPengirim::normalizeName($clean);

        if (empty($normal)) {
            return back()->withInput()->with('error', 'Nama instansi pengirim tidak boleh kosong.');
        }

        // Cek duplikasi dengan entri lain
        $conflict = InstansiPengirim::where('nama_normal', $normal)
            ->where('id', '!=', $instansi->id)
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', "Instansi lain dengan nama '{$clean}' sudah terdaftar dalam master.");
        }

        $affectedCount = 0;
        DB::transaction(function () use ($instansi, $oldNama, $clean, $normal, &$affectedCount) {
            $instansi->update([
                'nama' => $clean,
                'nama_normal' => $normal,
            ]);

            // Perbarui asal_surat pada seluruh data surat_masuk yang menggunakan nama lama
            $affectedCount = SuratMasuk::where('asal_surat', $oldNama)->update([
                'asal_surat' => $clean,
            ]);
        });

        $msg = "Nama instansi berhasil diubah menjadi '{$clean}'.";
        if ($affectedCount > 0) {
            $msg .= " Sebanyak {$affectedCount} data Surat Masuk terkait telah otomatis diperbarui.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Hapus instansi dari daftar master saran.
     * Hanya menghapus dari master saran; data surat masuk yang sudah ada TIDAK diubah.
     * Khusus Admin Kasubag (Role 2).
     */
    public function destroy($id)
    {
        $this->authorizeKasubag();

        $instansi = InstansiPengirim::findOrFail($id);
        $nama = $instansi->nama;
        $instansi->delete();

        return back()->with('success', "Instansi '{$nama}' berhasil dihapus dari daftar saran. Data Surat Masuk yang sudah ada tetap aman menyimpan nama tersebut.");
    }

    /**
     * Proteksi pembatasan akses khusus Admin Kasubag (Role 2).
     */
    private function authorizeKasubag(): void
    {
        if (!Auth::check() || Auth::user()->role_id != 2) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengelola data instansi pengirim.');
        }
    }
}

