<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'identity' => 'required|string|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer',
            'instansi_bidang' => 'nullable|string|max:150',
        ]);

        $instansi = $request->instansi_bidang;
        if (!$instansi) {
            if ($request->role_id == 4) {
                $instansi = 'Bidang Pengelolaan Aset Diskominfo';
            } elseif ($request->role_id == 5) {
                $instansi = 'Mahasiswa Magang';
            } else {
                $instansi = 'Diskominfo Bonebol';
            }
        }

        // 1. Buat data user untuk login
        $user = User::create([
            'name' => $request->name,
            'email' => $request->identity, // Menyimpan NIP atau Email ke kolom login
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'instansi_bidang' => $instansi,
        ]);

        // 2. Jika role yang dibuat adalah Pegawai (Role 3), otomatis buatkan data di tabel pegawais
        if ($request->role_id == 3) {
            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $request->name,
                'nip' => $request->identity,
                'jabatan' => 'Staff / Pegawai',
                'kategori_pegawai' => $request->kategori_pegawai ?? 'ASN',
                'pangkat_golongan' => 'Belum diatur',
            ]);
        }

        return back()->with('success', 'User / Pegawai baru berhasil ditambahkan dan disinkronkan!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Mencegah admin menghapus akunnya sendiri yang sedang aktif login
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        // Putus atau hapus relasi data pegawai terlebih dahulu untuk menghindari integrity constraint violation
        Pegawai::where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('success', 'User berhasil dihapus!');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'custom_password' => 'nullable|string|min:6',
        ]);

        if ($request->filled('custom_password')) {
            $newPassword = $request->custom_password;
        } else {
            // Generate password acak 8 karakter kombinasi huruf dan angka
            $newPassword = Str::random(8);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('reset_success', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $newPassword,
        ]);
    }
}