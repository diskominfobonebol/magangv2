<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StorePegawaiRequest;
use App\Http\Requests\UpdatePegawaiRequest;
use App\Models\Pegawai;
use App\Models\User; 
use App\Models\KenpaBerkala;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class KenaikanPangkatController extends Controller
{
   public function index(Request $request)
    {
        $search = $request->input('search');
        $jenis = $request->input('jenis');
        $status = $request->input('status');
        $acc = $request->input('acc');

        // Query dasar untuk Pegawai ASN (kecuali akun admin dan saring khusus ASN)
        $queryPegawaiAsli = Pegawai::with('kenpaBerkalas')
            ->where('kategori_pegawai', 'ASN')
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai Biasa', 'Pegawai']);

        // Filter Pencarian Nama atau NIP
        if ($search) {
            $queryPegawaiAsli->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan relasi KenpaBerkala (Jenis, Status Jadwal, Status ACC)
        if ($jenis || $status || $acc) {
            $queryPegawaiAsli->whereHas('kenpaBerkalas', function($q) use ($jenis, $status, $acc) {
                if ($jenis) {
                    if ($jenis == 'Kenaikan Pangkat') {
                        $q->where('jenis', 'Kenaikan Pangkat');
                    } elseif ($jenis == 'Gaji Berkala') {
                        $q->where('jenis', 'Berkala');
                    } elseif ($jenis == 'Keduanya') {
                        $q->where('jenis', 'Keduanya');
                    }
                }
                if ($status) {
                    $q->where('status', $status);
                }
                if ($acc) {
                    $q->where('status_acc', $acc);
                }
            });
        }

        // Hitung Metrik Kotak Atas secara Dinamis dari data KenpaBerkala khusus Pegawai ASN
        $allKenpa = KenpaBerkala::whereHas('pegawai', function($q) {
            $q->where('kategori_pegawai', 'ASN');
        })->get();
        $now = Carbon::now();

        $mendekatiJt = 0;
        $lewatJt = 0;
        $belumLengkap = 0;
        $berkasLengkap = 0;

        foreach ($allKenpa as $kb) {
            if ($kb->tgl_jatuh_tempo) {
                $jt = Carbon::parse($kb->tgl_jatuh_tempo);
                $diffDays = $now->diffInDays($jt, false);
                if ($diffDays < 0) {
                    $lewatJt++;
                } elseif ($diffDays <= 30) {
                    $mendekatiJt++;
                }
            }
            if (($kb->progres_berkas ?? 0) < 100) {
                $belumLengkap++;
            } else {
                $berkasLengkap++;
            }
        }

        $totalPegawai = Pegawai::where('kategori_pegawai', 'ASN')
            ->whereNotIn('nama', ['Admin Master', 'Admin Kasubag', 'Pegawai Biasa', 'Pegawai'])
            ->count();

        $metrics = [
            'total' => $totalPegawai,
            'mendekati_jt' => $mendekatiJt, 
            'lewat_jt' => $lewatJt,
            'belum_lengkap' => $belumLengkap,
            'berkas_lengkap' => $berkasLengkap,
        ];

       // Hitung Distribusi Status Pengajuan Khusus Pegawai ASN
        $progress = [
            'menunggu' => KenpaBerkala::whereHas('pegawai', fn($q) => $q->where('kategori_pegawai', 'ASN'))->where('status_acc', 'Menunggu')->count(),
            'disetujui' => KenpaBerkala::whereHas('pegawai', fn($q) => $q->where('kategori_pegawai', 'ASN'))->whereIn('status_acc', ['Disetujui', 'ACC'])->count(),
            'ditolak' => KenpaBerkala::whereHas('pegawai', fn($q) => $q->where('kategori_pegawai', 'ASN'))->whereIn('status_acc', ['Ditolak', 'Kembalikan'])->count(),
        ];
        
        $total_progress = array_sum($progress);
        if ($total_progress == 0) {
            $total_progress = 1; // Mencegah division by zero
        }

        $pegawai = $queryPegawaiAsli->paginate(5)->withQueryString();

        return view('kenaikan-pangkat.index', compact('metrics', 'progress', 'total_progress', 'pegawai'));
    }

    public function store(StorePegawaiRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->nama,
                'email' => $request->nip, 
                'password' => Hash::make($request->password),
                'role_id' => 3, 
            ]);

            $pegawai = Pegawai::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'kategori_pegawai' => 'ASN', // Jalur Kenpa/Berkala dikhususkan untuk ASN
                'no_wa' => $request->no_wa,
                'pangkat_golongan' => '-', 
            ]);

            $tglTerakhir = Carbon::now();
            $jenis = $request->jenis; // <-- SUDAH DIPERBAIKI
            
            $jatuhTempo = ($jenis == 'Berkala') 
                ? $tglTerakhir->copy()->addYears(2) 
                : $tglTerakhir->copy()->addYears(4);

            KenpaBerkala::create([
                'pegawai_id' => $pegawai->id,
                'jenis' => $jenis,
                'tgl_terakhir' => $tglTerakhir, 
                'tgl_jatuh_tempo' => $jatuhTempo, 
                'status' => 'Aktif',
                'progres_berkas' => 0,
                'status_acc' => 'Menunggu'
            ]);

            return redirect()->back()->with('success', 'Pegawai dan Jadwal berhasil ditambahkan!');

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function edit($id)
    {
        $pegawai = Pegawai::where('kategori_pegawai', 'ASN')->with('kenpaBerkalas')->findOrFail($id);
        $kenpa = $pegawai->kenpaBerkalas->first();

        return view('kenaikan-pangkat.edit', compact('pegawai', 'kenpa'));
    }

    public function update(UpdatePegawaiRequest $request, $id)
    {
        $pegawai = Pegawai::where('kategori_pegawai', 'ASN')->findOrFail($id);
        $kenaikan = $pegawai->kenpaBerkalas->first();

        try {
            $pegawai->update([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'kategori_pegawai' => $request->kategori_pegawai,
                'no_wa' => $request->no_wa,
                'pangkat_golongan' => $request->pangkat_golongan,
            ]);
            
            if ($pegawai->user_id) {
                User::where('id', $pegawai->user_id)->update([
                    'name' => $request->nama,
                    'email' => $request->nip,
                ]);
            }

            $tglTerakhir = $request->tgl_terakhir ? Carbon::parse($request->tgl_terakhir) : now();
            $jenis = $request->jenis; // <-- SUDAH DIPERBAIKI
            $jatuhTempo = ($jenis == 'Berkala') ? $tglTerakhir->copy()->addYears(2) : $tglTerakhir->copy()->addYears(4);

            if ($kenaikan) {
                $kenaikan->update([
                    'jenis' => $jenis,
                    'tgl_terakhir' => $tglTerakhir,
                    'tgl_jatuh_tempo' => $jatuhTempo,
                    'status' => $request->status,
                    'progres_berkas' => $request->progres_berkas ?? $kenaikan->progres_berkas, // <-- Ambil nilai lama jika request kosong
                    'status_acc' => $request->status_acc,
                    'keterangan' => $request->keterangan,
                ]);
            } 
            else {
                KenpaBerkala::create([
                    'pegawai_id' => $pegawai->id,
                    'jenis' => $jenis,
                    'tgl_terakhir' => $tglTerakhir,
                    'tgl_jatuh_tempo' => $jatuhTempo,
                    'status' => $request->status,
                    'progres_berkas' => $request->progres_berkas ?? 0,
                    'status_acc' => $request->status_acc,
                    'keterangan' => $request->keterangan,
                ]);
            }

            return redirect()->route('kenaikan-pangkat.index')->with('success', 'Data pegawai berhasil diperbarui!');

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function show($id)
    {
        $pegawai = Pegawai::where('kategori_pegawai', 'ASN')->with([
            'kenpaBerkalas',
            'dokumenPegawais',
            'surats' => function($q) {
                $q->with(['jenisSurat', 'pegawais'])
                  ->latest('tgl_surat')
                  ->latest('id');
            }
        ])->findOrFail($id);
        $kenpa = $pegawai->kenpaBerkalas->first();

        return view('kenaikan-pangkat.show', compact('pegawai', 'kenpa'));
    }

   public function kirimWhatsApp($tujuan, $pesan)
    {
        // Ambil token dan device dari database Setting
        $token = \App\Models\Setting::where('key', 'wa_token')->value('value') ?? env('WHATSAPP_API_TOKEN');
        $device = \App\Models\Setting::where('key', 'wa_device')->value('value');
        
        // Menggunakan API Fonnte 
        try {
            $payload = [
                'target' => $tujuan,
                'message' => $pesan,
            ];

            if ($device) {
                $payload['device'] = $device;
            }

            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', $payload);

        } catch (\Exception $e) {
            // Tangkap error jika gagal kirim agar aplikasi tidak crash
        }
    }

    public function verifikasiDokumen(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:disetujui,ditolak',
            'keterangan_admin' => 'nullable|string'
        ]);

        $dokumen = \App\Models\DokumenPegawai::findOrFail($id);
        $dokumen->update([
            'status_verifikasi' => $request->status_verifikasi,
            'keterangan_admin' => $request->keterangan_admin,
        ]);

        return redirect()->back()->with('success', 'Status dokumen berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $pegawai = Pegawai::where('kategori_pegawai', 'ASN')->with(['kenpaBerkalas', 'dokumenPegawais'])->findOrFail($id);

            // 1. Hapus data relasi pengajuan kenaikan pangkat / berkala jika ada
            if ($pegawai->kenpaBerkalas()->exists()) {
                $pegawai->kenpaBerkalas()->delete();
            }

            // 2. Hapus data dokumen pegawai jika ada
            if ($pegawai->dokumenPegawais()->exists()) {
                $pegawai->dokumenPegawais()->delete();
            }

            // 3. Hapus akun user terkait di tabel users (jika terhubung via user_id)
            if ($pegawai->user_id) {
                User::where('id', $pegawai->user_id)->delete();
            }

            // 4. Hapus data pegawai utama
            $pegawai->delete();

            return redirect()->route('kenaikan-pangkat.index')->with('success', 'Data pegawai beserta riwayatnya berhasil dihapus!');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}