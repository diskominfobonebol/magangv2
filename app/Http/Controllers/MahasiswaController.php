<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranMagang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MahasiswaController extends Controller
{
    public function index()
    {
        $pengajuanSaya = PendaftaranMagang::with('suratBalasan')
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        return view('auth.mahasiswa', compact('pengajuanSaya'));
    }

    public function storeMagang(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:50',
            'universitas' => 'required|string|max:150',
            'jurusan' => 'required|string|max:100',
            'no_hp' => 'required|string|max:30',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after:tgl_mulai',
            'surat_pengantar' => 'required|file|mimes:pdf|max:5120',
        ], [
            'nim.required'             => 'NIM wajib diisi.',
            'universitas.required'     => 'Asal Perguruan Tinggi / Sekolah wajib diisi.',
            'jurusan.required'         => 'Jurusan / Program Studi wajib diisi.',
            'no_hp.required'           => 'Nomor HP / WhatsApp wajib diisi.',
            'tgl_mulai.required'       => 'Tanggal mulai magang wajib diisi.',
            'tgl_selesai.required'     => 'Tanggal selesai magang wajib diisi.',
            'tgl_selesai.after'        => 'Tanggal selesai magang harus sesudah tanggal mulai.',
            'surat_pengantar.required' => 'Surat Pengantar wajib diunggah.',
            'surat_pengantar.mimes'    => 'Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max'      => 'Ukuran file Surat Pengantar tidak boleh melebihi 5MB (5120 KB).',
        ]);

        $filePath = null;
        if ($request->hasFile('surat_pengantar')) {
            $filePath = $request->file('surat_pengantar')->store('surat_pengantar', 'public');
        }

        PendaftaranMagang::create([
            'user_id' => Auth::id(),
            'nim' => $validated['nim'],
            'universitas' => $validated['universitas'],
            'jurusan' => $validated['jurusan'],
            'no_hp' => $validated['no_hp'],
            'tgl_mulai' => $validated['tgl_mulai'],
            'tgl_selesai' => $validated['tgl_selesai'],
            'surat_pengantar' => $filePath,
            'status' => 'Menunggu',
        ]);

        return back()->with('success', 'Berkas pendaftaran magang berhasil dikirim!');
    }

    public function downloadSuratBalasan($id)
    {
        $pengajuan = PendaftaranMagang::with('suratBalasan')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$pengajuan->suratBalasan || !$pengajuan->suratBalasan->file_surat_balasan) {
            return back()->with('error', 'Surat balasan belum tersedia.');
        }

        $filePath = storage_path('app/public/' . $pengajuan->suratBalasan->file_surat_balasan);
        if (!file_exists($filePath)) {
            return back()->with('error', 'File surat balasan tidak ditemukan.');
        }

        return response()->download($filePath);
    }
}
