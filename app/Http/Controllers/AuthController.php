<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('email'));
        $cleanNip = preg_replace('/[^0-9]/', '', $loginInput);

        // 1. Cari user langsung berdasarkan Email atau NIP di tabel users
        $user = User::where(function ($query) use ($loginInput, $cleanNip) {
            $query->whereRaw('LOWER(email) = ?', [strtolower($loginInput)]);
            
            if (!empty($cleanNip)) {
                $query->orWhere('email', $cleanNip);
            }

            // Normalisasi variasi domain kominfo bonebolango
            if (strtolower($loginInput) === 'admin@kominfo.bonebolango.go.id') {
                $query->orWhere('email', 'admin@kominfo.bonebolango.id');
            } elseif (strtolower($loginInput) === 'admin@kominfo.bonebolango.id') {
                $query->orWhere('email', 'admin@kominfo.bonebolango.go.id');
            }
        })->first();

        // 2. Jika tidak ditemukan langsung di tabel users, cari via data Pegawai (kolom nip)
        if (!$user) {
            $pegawai = Pegawai::where('nip', $loginInput)
                ->when(!empty($cleanNip), function ($q) use ($cleanNip) {
                    $q->orWhere('nip', $cleanNip);
                })
                ->first();

            if ($pegawai && $pegawai->user_id) {
                $user = User::find($pegawai->user_id);
            }
        }

        // 3. Verifikasi kecocokan password menggunakan Hash::check
        if ($user && Hash::check($request->input('password'), $user->password)) {
            // Periksa apakah akun dinonaktifkan
            if (isset($user->is_active) && !$user->is_active) {
                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.',
                ])->onlyInput('email');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            $role = (int) $user->role_id;
            if ($role === 1) {
                return redirect()->intended('/dashboard/master');
            } elseif ($role === 2) {
                return redirect()->intended('/surat');
            } elseif ($role === 4) {
                return redirect()->intended('/admin/aset');
            } elseif ($role === 5) {
                return redirect()->intended('/mahasiswa/dashboard');
            } else {
                return redirect()->intended('/dashboard/pegawai');
            }
        }

        return back()->withErrors([
            'email' => 'Kredensial tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'nim' => ['required', 'string', 'max:50'],
            'universitas' => ['required', 'string', 'max:150'],
            'jurusan' => ['required', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 5, // Mahasiswa
            'is_active' => true,
            'instansi_bidang' => trim($validated['universitas']),
        ]);

        Auth::login($user);

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Registrasi berhasil! Silakan lengkapi pendaftaran magang Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
