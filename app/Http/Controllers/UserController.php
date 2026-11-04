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
            default => 3,
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
            'identity.unique' => 'Email atau NIP sudah digunakan oleh akun lain.',
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->identity,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'instansi_bidang' => $instansi,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        if ($roleId == 3) {
            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $request->name,
                'nip' => $request->identity,
                'jabatan' => 'Staff / Pegawai',
                'kategori_pegawai' => $request->kategori_pegawai ?? 'ASN',
                'pangkat_golongan' => 'Belum diatur',
                'no_wa' => '-',
            ]);
        }

        return back()->with('success', 'User / Pegawai baru berhasil ditambahkan dan disinkronkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'identity' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id),
                function ($attribute, $value, $fail) use ($user) {
                    $existingPegawai = Pegawai::where('nip', $value)->first();
                    if ($existingPegawai && $existingPegawai->user_id !== $user->id) {
                        $fail('NIP sudah digunakan oleh pegawai lain.');
                    }
                },
            ],
            'role_id' => 'required|integer|in:1,2,3,4,5',
            'instansi_bidang' => 'nullable|string|max:150',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'identity.required' => 'Email / NIP wajib diisi.',
            'identity.unique' => 'Email / NIP login sudah digunakan oleh akun lain.',
            'role_id.required' => 'Role / Hak Akses wajib dipilih.',
            'role_id.in' => 'Role yang dipilih tidak valid di sistem.',
        ]);

        if ($user->id === auth()->id() && (int) $request->role_id !== 1) {
            return back()->with('error', 'Anda tidak dapat menurunkan atau mengubah role akun Admin Master Anda sendiri.');
        }

        if ((int) $user->role_id === 1 && (int) $request->role_id !== 1) {
            $adminMasterCount = User::where('role_id', 1)->count();
            if ($adminMasterCount <= 1) {
                return back()->with('error', 'Operasi dibatalkan. Sistem harus memiliki minimal satu Admin Master aktif.');
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->identity,
            'role_id' => (int)$request->role_id,
        ];
        if ($request->filled('instansi_bidang')) {
            $updateData['instansi_bidang'] = $request->instansi_bidang;
        }

        $user->update($updateData);

        if ($user->pegawai) {
            $user->pegawai->update([
                'nama' => $request->name,
                'nip' => $request->identity,
            ]);
        } elseif ((int) $request->role_id === 3) {
            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $request->name,
                'nip' => $request->identity,
                'jabatan' => 'Staff / Pegawai',
                'kategori_pegawai' => 'ASN',
                'pangkat_golongan' => 'Belum diatur',
                'no_wa' => '-',
            ]);
        }

        return back()->with('success', 'Data user berhasil diperbarui!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

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
            $newPassword = Str::random(8);
        }

        $user->password = Hash::make($newPassword);
        $user->must_change_password = true;
        $user->resetTwoFactor();
        $user->save();

        return back()->with('reset_success', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $newPassword,
        ]);
    }

    public function resetTwoFactor($id)
    {
        $user = User::findOrFail($id);
        $user->resetTwoFactor();

        return back()->with('success', "Autentikasi 2FA untuk user '{$user->name}' berhasil direset. User wajib melakukan setup 2FA baru saat login berikutnya.");
    }
}
