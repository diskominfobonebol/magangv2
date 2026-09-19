<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $filterRole = $request->query('role');
        $filterStatus = $request->query('status');

        $query = User::with('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('instansi_bidang', 'like', "%{$search}%");
            });
        }

        if ($filterRole && $filterRole !== 'all') {
            // Support both numeric role_id and string alias
            $roleId = match ($filterRole) {
                'admin', '1' => 1,
                'admin_kasubag', 'kasubag', '2' => 2,
                'pegawai', '3' => 3,
                'bendahara_barang', 'bendahara', '4' => 4,
                'mahasiswa', '5' => 5,
                default => is_numeric($filterRole) ? (int)$filterRole : null,
            };

            if ($roleId) {
                $query->where('role_id', $roleId);
            }
        }

        if ($filterStatus !== null && $filterStatus !== '' && $filterStatus !== 'all') {
            $query->where('is_active', $filterStatus === '1');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Ringkasan peran & status user
        $roleCounts = [
            'total'            => User::count(),
            'admin'            => User::where('role_id', 1)->count(),
            'admin_kasubag'    => User::where('role_id', 2)->count(),
            'pegawai'          => User::where('role_id', 3)->count(),
            'bendahara_barang' => User::where('role_id', 4)->count(),
            'mahasiswa'        => User::where('role_id', 5)->count(),
            'aktif'            => User::where('is_active', true)->count(),
            'nonaktif'         => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'roleCounts', 'search', 'filterRole', 'filterStatus'));
    }

    public function store(Request $request)
    {
        $roleInput = $request->input('role_id') ?? $request->input('role');
        $roleId = is_numeric($roleInput) ? (int)$roleInput : match ($roleInput) {
            'admin' => 1,
            'admin_kasubag', 'kasubag' => 2,
            'pegawai' => 3,
            'bendahara_barang', 'bendahara' => 4,
            'mahasiswa' => 5,
            default => 5,
        };

        $request->merge(['role_id' => $roleId]);

        $request->validate([
            'name' => 'required|string|max:255',
            'identity' => 'required|string|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer|in:1,2,3,4,5',
            'instansi_bidang' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'identity.required' => 'Email atau NIP wajib diisi.',
            'identity.unique' => 'Email atau NIP sudah terdaftar pada sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role_id.required' => 'Peran (role) pengguna wajib dipilih.',
        ]);

        $instansi = $request->instansi_bidang;
        if (!$instansi) {
            if ($roleId == 4) {
                $instansi = 'Bidang Pengelolaan Aset Diskominfo';
            } elseif ($roleId == 5) {
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
            'role_id' => $roleId,
            'instansi_bidang' => $instansi,
            'is_active' => true,
        ]);

        // 2. Jika role yang dibuat adalah Pegawai (Role 3), otomatis buatkan data di tabel pegawais
        if ($roleId == 3) {
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

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $roleInput = $request->input('role_id') ?? $request->input('role');
        $roleId = is_numeric($roleInput) ? (int)$roleInput : match ($roleInput) {
            'admin' => 1,
            'admin_kasubag', 'kasubag' => 2,
            'pegawai' => 3,
            'bendahara_barang', 'bendahara' => 4,
            'mahasiswa' => 5,
            default => $user->role_id,
        };

        $request->merge(['role_id' => $roleId]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $id,
            'role_id' => 'required|integer|in:1,2,3,4,5',
            'password' => 'nullable|min:6',
            'instansi_bidang' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'role_id.required' => 'Peran (role) wajib dipilih.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        // Pencegahan: User yang sedang login tidak boleh mengubah role dirinya sendiri menjadi non-admin
        if ($user->id === auth()->id() && $roleId !== 1) {
            return back()->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri menjadi selain Admin Master!');
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $roleId,
            'instansi_bidang' => $request->instansi_bidang ?: $user->instansi_bidang,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Jika role diubah menjadi Pegawai (3) dan belum ada data di pegawais, sinkronkan
        if ($roleId == 3) {
            Pegawai::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $user->name,
                    'nip' => $user->email,
                    'jabatan' => 'Staff / Pegawai',
                    'kategori_pegawai' => $request->kategori_pegawai ?? 'ASN',
                    'pangkat_golongan' => 'Belum diatur',
                ]
            );
        }

        return back()->with('success', "Data pengguna '{$user->name}' berhasil diperbarui!");
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
        return back()->with('success', "Akun '{$user->name}' berhasil {$statusText}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Mencegah admin menghapus akunnya sendiri yang sedang aktif login
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $userName = $user->name;

        // Putus atau hapus relasi data pegawai terlebih dahulu untuk menghindari integrity constraint violation
        Pegawai::where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('success', "Pengguna '{$userName}' berhasil dihapus dari sistem!");
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