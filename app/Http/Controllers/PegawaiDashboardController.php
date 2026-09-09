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
        
        // Cari data pegawai berdasarkan user_id, atau coba cocokkan dengan nip / nama jika user_id belum terhubung
        $pegawai = Pegawai::where('user_id', $user->id)->first();
        if (!$pegawai) {
            $pegawai = Pegawai::where('nip', $user->email)
                ->orWhere('nama', $user->name)
                ->first();
            if ($pegawai) {
                if (!$pegawai->user_id) {
                    $pegawai->user_id = $user->id;
                    $pegawai->save();
                }
            } else {
                $pegawai = Pegawai::create([
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'nip' => $user->email,
                    'jabatan' => 'Staff / Pegawai',
                    'pangkat_golongan' => 'Belum diatur',
                    'no_wa' => '-'
                ]);
            }
        }

        // Muat relasinya dengan relasi jenisSurat dan pegawais pada surats
        $pegawai->load([
            'surats' => function ($q) {
                $q->with(['jenisSurat', 'pegawais'])
                  ->orderBy('tgl_surat', 'desc')
                  ->orderBy('id', 'desc');
            },
            'kenpaBerkalas.dokumenPegawais'
        ]);

        $kenpa = $pegawai->kenpaBerkalas->first();
        $jenisDokumens = JenisDokumen::all();

        // Hitung otomatis progres persentase setiap kali halaman dimuat
        if ($kenpa) {
            $totalSyarat = ($kenpa->jenis == 'Kenpa') ? 10 : 7;
            $jumlahDiunggah = $kenpa->dokumenPegawais()->count();
            
            $progres = ($totalSyarat > 0) ? min(round(($jumlahDiunggah / $totalSyarat) * 100), 100) : 0;
            
            if ($kenpa->progres_berkas != $progres) {
                $kenpa->progres_berkas = $progres;
                $kenpa->save();
            }
        }

        return view('dashboard.pegawai', compact('pegawai', 'kenpa', 'jenisDokumens', 'user'));
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
            $totalSyarat = ($kenpa->jenis == 'Kenpa') ? 10 : 7;
            $jumlahDiunggah = $kenpa->dokumenPegawais()->count();
            
            $kenpa->progres_berkas = ($totalSyarat > 0) ? min(round(($jumlahDiunggah / $totalSyarat) * 100), 100) : 0;
            $kenpa->save();
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
            $totalSyarat = ($kenpa->jenis == 'Kenpa') ? 10 : 7;
            $jumlahDiunggah = $kenpa->dokumenPegawais()->count();
            $kenpa->progres_berkas = ($totalSyarat > 0) ? min(round(($jumlahDiunggah / $totalSyarat) * 100), 100) : 0;
            $kenpa->save();
        }

        return redirect()->back()->with('success', 'Berkas berhasil dihapus. Silakan unggah kembali dokumen yang benar.');
    }
}