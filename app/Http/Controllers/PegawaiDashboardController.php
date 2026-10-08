<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pegawai;
use App\Models\KenpaBerkala;
use App\Models\DokumenPegawai;
use App\Models\JenisDokumen;

class PegawaiDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Cari data pegawai berdasarkan user_id, email/nip, atau nama
        $pegawai = Pegawai::where('user_id', $user->id)
            ->orWhere('nip', $user->email)
            ->orWhere('nama', $user->name)
            ->first();

        if (!$pegawai) {
            $pegawai = Pegawai::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'nip' => $user->email,
                'jabatan' => 'Staff / Pegawai',
                'pangkat_golongan' => 'Belum diatur',
                'no_wa' => '-'
            ]);
        } elseif (!$pegawai->user_id) {
            $pegawai->user_id = $user->id;
            $pegawai->save();
        }

        // Muat relasinya dengan pengurutan terbaru
        $pegawai->load([
            'surats' => function($q) {
                $q->with(['jenisSurat', 'pegawais'])
                  ->latest('tgl_surat')
                  ->latest('id');
            }, 
            'kenpaBerkalas.dokumenPegawais.jenisDokumen'
        ]);

        // Cari record pengajuan Kenpa & Berkala
        $kenpaRecord = $pegawai->kenpaBerkalas->first(function($kb) {
            return stripos($kb->jenis, 'pangkat') !== false || stripos($kb->jenis, 'kenpa') !== false || strcasecmp($kb->jenis, 'keduanya') === 0;
        });

        $berkalaRecord = $pegawai->kenpaBerkalas->first(function($kb) {
            return (stripos($kb->jenis, 'berkala') !== false && stripos($kb->jenis, 'pangkat') === false) || strcasecmp($kb->jenis, 'keduanya') === 0;
        });

        $hasKenpa = !is_null($kenpaRecord);
        $hasBerkala = !is_null($berkalaRecord);
        $hasBoth = $hasKenpa && $hasBerkala;

        // Default tab
        if ($hasBoth) {
            $activeTab = 'kenpa';
        } elseif ($hasBerkala) {
            $activeTab = 'berkala';
        } else {
            $activeTab = 'kenpa';
        }

        // Ambil master jenis dokumen terpisah per kategori
        $jenisDokumensKenpa = JenisDokumen::where('kategori', 'kenpa')->get();
        $jenisDokumensBerkala = JenisDokumen::where('kategori', 'berkala')->get();

        // Hitung otomatis progres persentase setiap kali halaman dimuat
        if ($kenpaRecord) {
            $this->calculateAndSaveProgress($kenpaRecord);
        }
        if ($berkalaRecord && (!$kenpaRecord || $berkalaRecord->id !== $kenpaRecord->id)) {
            $this->calculateAndSaveProgress($berkalaRecord);
        }

        // Backward compatibility
        $kenpa = $kenpaRecord ?? $berkalaRecord ?? $pegawai->kenpaBerkalas->first();
        $jenisDokumens = ($activeTab === 'berkala') ? $jenisDokumensBerkala : $jenisDokumensKenpa;

        return view('dashboard.pegawai', compact(
            'pegawai',
            'kenpa',
            'kenpaRecord',
            'berkalaRecord',
            'hasKenpa',
            'hasBerkala',
            'hasBoth',
            'activeTab',
            'jenisDokumensKenpa',
            'jenisDokumensBerkala',
            'jenisDokumens',
            'user'
        ));
    }

    public function calculateAndSaveProgress(KenpaBerkala $kb)
    {
        $isKenpa = (stripos($kb->jenis, 'pangkat') !== false || stripos($kb->jenis, 'kenpa') !== false);
        $isBerkala = (stripos($kb->jenis, 'berkala') !== false && stripos($kb->jenis, 'pangkat') === false);
        $isKeduanya = (strcasecmp($kb->jenis, 'keduanya') === 0 || ($isKenpa && stripos($kb->jenis, 'berkala') !== false));

        if ($isKeduanya) {
            $totalWajib = JenisDokumen::where('is_wajib', true)->count() ?: 6;
            $uploadedCount = $kb->dokumenPegawais()->count();
        } elseif ($isBerkala) {
            $totalWajib = JenisDokumen::where('kategori', 'berkala')->where('is_wajib', true)->count() ?: 3;
            $uploadedCount = $kb->dokumenPegawais()
                ->whereHas('jenisDokumen', function($q) {
                    $q->where('kategori', 'berkala');
                })->count();
        } else {
            $totalWajib = JenisDokumen::where('kategori', 'kenpa')->where('is_wajib', true)->count() ?: 3;
            $uploadedCount = $kb->dokumenPegawais()
                ->whereHas('jenisDokumen', function($q) {
                    $q->where('kategori', 'kenpa');
                })->count();
        }

        $progres = ($totalWajib > 0) ? min(round(($uploadedCount / $totalWajib) * 100), 100) : 0;
        if ($kb->progres_berkas != $progres) {
            $kb->progres_berkas = $progres;
            $kb->save();
        }

        return $progres;
    }

    public function uploadDokumen(Request $request)
    {
        $request->validate([
            'kenpa_berkala_id' => 'required|exists:kenpa_berkalas,id',
            'jenis_dokumen_id' => 'required|exists:jenis_dokumens,id',
            'file_dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file_dokumen');
        $path = $file->store('dokumen_pegawai', 'public');

        DokumenPegawai::updateOrCreate(
            [
                'kenpa_berkala_id' => $request->kenpa_berkala_id,
                'jenis_dokumen_id' => $request->jenis_dokumen_id
            ],
            [
                'file_path' => $path,
                'status_verifikasi' => 'pending',
                'uploaded_at' => now(),
            ]
        );

        // Hitung ulang progres otomatis setelah upload
        $kenpa = KenpaBerkala::find($request->kenpa_berkala_id);
        if ($kenpa) {
            $this->calculateAndSaveProgress($kenpa);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah dan sedang menunggu verifikasi.');
    }

    public function destroyDokumen($id)
    {
        $dokumen = DokumenPegawai::findOrFail($id);
        
        // Hapus file fisik dari storage public jika ada
        if ($dokumen->file_path && \Storage::disk('public')->exists($dokumen->file_path)) {
            \Storage::disk('public')->delete($dokumen->file_path);
        }
        
        $kenpa = $dokumen->kenpaBerkala; // Simpan relasi sebelum dihapus
        $dokumen->delete();

        // Hitung ulang progres berkas setelah dihapus
        if ($kenpa) {
            $this->calculateAndSaveProgress($kenpa);
        }

        return redirect()->back()->with('success', 'Berkas berhasil dihapus. Silakan unggah kembali dokumen yang benar.');
    }
}