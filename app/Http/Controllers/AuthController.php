<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\NotificationService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->must_change_password) {
                return redirect()->route('password.force_reset');
            }
            if (!$user->hasTwoFactorEnabled()) {
                return redirect()->route('2fa.setup');
            }
            if (!session('2fa_passed')) {
                return redirect()->route('2fa.verify');
            }
            return $this->redirectAccordingToRole($user);
        }

        return view('auth.login');
    }

    public function showLoginPegawai()
    {
        return $this->showLogin();
    }

    public function showLoginMahasiswa()
    {
        if (Auth::check()) {
            return $this->redirectAccordingToRole(Auth::user());
        }
        return view('auth.login_mahasiswa');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email atau NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('email'));
        $cleanNip = preg_replace('/[^0-9]/', '', $loginInput);
        $throttleKey = 'login:' . $request->ip() . '|' . strtolower($loginInput);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            NotificationService::notifySecurityLockout($loginInput, 'login');
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // 1. Cari user berdasarkan Email atau NIP di tabel users
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
                RateLimiter::hit($throttleKey, 60);
                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            session(['2fa_passed' => false]);
            session(['2fa_user_id' => $user->id]);

            // Alur urutan wajib:
            // a. Jika user wajib ganti sandi pada login pertama
            if ($user->must_change_password) {
                return redirect()->route('password.force_reset');
            }

            // b. Setelah ganti password (atau jika sudah), arahkan ke Setup 2FA jika 2FA belum aktif
            if (!$user->hasTwoFactorEnabled()) {
                return redirect()->route('2fa.setup');
            }

            // c. Jika 2FA sudah aktif, verifikasi kode 6 digit
            return redirect()->route('2fa.verify');
        }

        RateLimiter::hit($throttleKey, 60);

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
            'must_change_password' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        session(['2fa_passed' => true]);

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Registrasi berhasil! Silakan lengkapi pendaftaran magang Anda.');
    }

    public function showForceResetPassword()
    {
        return view('auth.force-reset-password');
    }

    public function forceResetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        // Alur wajib: Setelah ganti password berhasil, langsung diarahkan ke Setup 2FA jika belum aktif
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.setup')->with('success', 'Password Anda berhasil diperbarui! Silakan lanjutkan dengan mengaktifkan Autentikasi Dua Faktor (2FA).');
        }

        return redirect()->route('2fa.verify')->with('success', 'Password Anda berhasil diperbarui! Silakan verifikasi 2FA untuk melanjutkan.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    protected function redirectAccordingToRole($user)
    {
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
}
