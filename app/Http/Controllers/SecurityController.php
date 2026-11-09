<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SecurityController extends Controller
{
    /**
     * Tampilkan formulir Ganti Password Wajib
     */
    public function showChangePassword()
    {
        $user = Auth::user();

        // Pengaman: Jika bukan Pegawai atau tidak wajib ganti kata sandi, langsung arahkan ke tahap berikutnya
        if (!$user || $user->role !== 'pegawai' || !$user->must_change_password) {
            if ($user && is_null($user->two_factor_confirmed_at)) {
                return redirect()->route('2fa.setup');
            }
            if ($user && session('two_factor_authenticated') !== true) {
                return redirect()->route('2fa.verify');
            }
            return redirect('/');
        }

        return view('auth.change_password', compact('user'));
    }

    /**
     * Proses Ganti Password Wajib
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $defaultPassword = config('auth.default_pegawai_password', 'Kominfo#2026');

        $request->validate([
            'old_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-zA-Z]/', // Mengandung huruf
                'regex:/[0-9]/',     // Mengandung angka
                function ($attribute, $value, $fail) use ($request, $user, $defaultPassword) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Kata sandi baru tidak boleh sama dengan kata sandi lama.');
                    }
                    if ($value === $defaultPassword || $value === 'password123') {
                        $fail('Kata sandi baru tidak boleh menggunakan kata sandi default sistem.');
                    }
                },
            ],
        ], [
            'old_password.required' => 'Kata sandi lama wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus mengandung kombinasi huruf dan angka.',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Kata sandi lama yang Anda masukkan salah.']);
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        if (is_null($user->two_factor_confirmed_at)) {
            return redirect()->route('2fa.setup')->with('success', 'Kata sandi berhasil diperbarui! Silakan lanjutkan untuk mengaktifkan Autentikasi Dua Faktor (2FA).');
        }

        return redirect()->route('2fa.verify')->with('success', 'Kata sandi berhasil diperbarui! Silakan masukkan kode autentikasi 2FA Anda.');
    }

    /**
     * Tampilkan Halaman Setup 2FA Wajib (Enrollment)
     */
    public function showTwoFactorSetup()
    {
        $user = Auth::user();

        // Jika user sudah mengaktifkan 2FA, arahkan ke verifikasi atau halaman utama
        if ($user->hasTwoFactorEnabled()) {
            if (session('two_factor_authenticated') === true) {
                return $this->redirectToRoleDashboard($user);
            }
            return redirect()->route('2fa.verify');
        }

        $google2fa = app('pragmarx.google2fa');

        // Generate secret key baru jika belum ada
        if (empty($user->two_factor_secret)) {
            $user->two_factor_secret = $google2fa->generateSecretKey(32);
            $user->save();
        }

        // Format nama akun di aplikasi Google Authenticator:
        // SIMPATIK (NIP) untuk Pegawai, SIMPATIK (email) untuk role lain
        $accountHolder = ($user->role === 'pegawai' && !empty($user->nip))
            ? "SIMPATIK ({$user->nip})"
            : "SIMPATIK ({$user->email})";

        $company = 'SIMPATIK Bone Bolango';

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $company,
            $accountHolder,
            $user->two_factor_secret
        );

        // Generate QR code SVG
        $qrCodeSvg = QrCode::size(220)
            ->margin(1)
            ->generate($qrCodeUrl);

        // Secret key terpisah setiap 4 karakter untuk kemudahan membaca di laptop
        $secretKeyFormatted = chunk_split($user->two_factor_secret, 4, ' ');

        return view('auth.2fa_setup', [
            'user' => $user,
            'secretKey' => $user->two_factor_secret,
            'secretKeyFormatted' => trim($secretKeyFormatted),
            'qrCodeSvg' => $qrCodeSvg,
            'accountHolder' => $accountHolder,
        ]);
    }

    /**
     * Konfirmasi Setup 2FA dengan Kode 6 Digit
     */
    public function confirmTwoFactorSetup(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Kode autentikasi 6 digit wajib diisi.',
            'code.size' => 'Kode autentikasi harus terdiri dari 6 digit angka.',
        ]);

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code, 1);

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode autentikasi salah atau sudah kadaluarsa. Pastikan jam di HP/laptop Anda akurat.'])->withInput();
        }

        // Generate 8 recovery codes sekali pakai
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        session(['show_recovery_codes' => $recoveryCodes]);

        return redirect()->route('2fa.recovery_codes')->with('success', 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!');
    }

    /**
     * Tampilkan 8 Recovery Code Sekali Pakai
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();
        $recoveryCodes = session('show_recovery_codes', []);

        if (empty($recoveryCodes)) {
            // Jika user datang kembali setelah menyimpan, arahkan ke dashboard jika sudah login penuh
            if (session('two_factor_authenticated') === true) {
                return $this->redirectToRoleDashboard($user);
            }
            return redirect()->route('2fa.verify');
        }

        return view('auth.2fa_recovery_codes', compact('user', 'recoveryCodes'));
    }

    /**
     * Konfirmasi Penyimpanan Recovery Code
     */
    public function ackRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'acknowledged' => ['accepted'],
        ], [
            'acknowledged.accepted' => 'Anda wajib mencentang konfirmasi bahwa Anda telah menyimpan kode cadangan.',
        ]);

        session()->forget('show_recovery_codes');
        session(['two_factor_authenticated' => true]);

        return $this->redirectToRoleDashboard($user)->with('success', 'Selamat datang! Akun Anda kini terlindungi dengan Autentikasi Dua Faktor.');
    }

    /**
     * Halaman Verifikasi 2FA saat Setiap Login
     */
    public function showTwoFactorVerify()
    {
        $user = Auth::user();

        if (session('two_factor_authenticated') === true) {
            return $this->redirectToRoleDashboard($user);
        }

        return view('auth.2fa_verify', compact('user'));
    }

    /**
     * Proses Pengecekan Kode 2FA (TOTP atau Recovery Code)
     */
    public function checkTwoFactorVerify(Request $request)
    {
        $user = Auth::user();

        // 1. Rate Limiting: Maksimal 5 percobaan per menit per user/IP
        $rateLimitKey = '2fa_verify_' . $user->id . '_' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'code' => "Terlalu banyak percobaan yang salah. Silakan coba kembali dalam {$seconds} detik.",
            ]);
        }

        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
        ]);

        $inputCode = trim($request->code);

        // A. Cek apakah pengguna memasukkan Recovery Code (format XXXX-XXXX atau panjang > 6)
        if (strlen($inputCode) > 6 || str_contains($inputCode, '-')) {
            $formattedInput = strtoupper(str_replace(' ', '', $inputCode));
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];

            if (in_array($formattedInput, $recoveryCodes)) {
                // Recovery code valid! Hanguskan kode yang digunakan (sekali pakai)
                $updatedCodes = array_values(array_diff($recoveryCodes, [$formattedInput]));
                $user->two_factor_recovery_codes = $updatedCodes;
                $user->save();

                RateLimiter::clear($rateLimitKey);
                session(['two_factor_authenticated' => true]);

                $remainingCount = count($updatedCodes);
                return $this->redirectToRoleDashboard($user)->with('warning', "Anda berhasil masuk menggunakan Kode Cadangan sekali pakai. Sisa kode cadangan Anda: {$remainingCount}.");
            }

            RateLimiter::hit($rateLimitKey, 60);
            return back()->withErrors(['code' => 'Kode cadangan (recovery code) tidak valid atau telah digunakan.'])->withInput();
        }

        // B. Verifikasi Kode TOTP 6 Digit
        if (strlen($inputCode) !== 6 || !ctype_digit($inputCode)) {
            RateLimiter::hit($rateLimitKey, 60);
            return back()->withErrors(['code' => 'Kode autentikasi harus berupa 6 digit angka.'])->withInput();
        }

        // Pencegahan Replay Attack: Cek apakah kode TOTP ini sudah digunakan dalam window waktunya
        $replayKey = "2fa_used_code_{$user->id}_{$inputCode}";
        if (Cache::has($replayKey)) {
            return back()->withErrors(['code' => 'Kode autentikasi ini baru saja digunakan. Harap tunggu hingga kode baru muncul di aplikasi authenticator Anda (30 detik).'])->withInput();
        }

        $google2fa = app('pragmarx.google2fa');
        // Toleransi selisih waktu ±1 langkah (30 detik)
        $valid = $google2fa->verifyKey($user->two_factor_secret, $inputCode, 1);

        if (!$valid) {
            RateLimiter::hit($rateLimitKey, 60);
            return back()->withErrors(['code' => 'Kode autentikasi 6 digit tidak cocok atau sudah kadaluarsa. Pastikan jam perangkat Anda akurat.'])->withInput();
        }

        // Tandai kode ini telah digunakan selama 60 detik untuk mencegah replay
        Cache::put($replayKey, true, 60);
        RateLimiter::clear($rateLimitKey);

        session(['two_factor_authenticated' => true]);

        return $this->redirectToRoleDashboard($user)->with('success', 'Autentikasi dua faktor berhasil diverifikasi.');
    }

    /**
     * Halaman Keamanan Akun (Dari menu header profil)
     */
    public function showAccountSecurity()
    {
        $user = Auth::user();
        $recoveryCodesCount = count($user->two_factor_recovery_codes ?? []);
        return view('auth.keamanan_akun', compact('user', 'recoveryCodesCount'));
    }

    /**
     * Buat Ulang 8 Recovery Code (Wajib konfirmasi password saat ini)
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi yang Anda masukkan salah.']);
        }

        $newCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $newCodes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        $user->two_factor_recovery_codes = $newCodes;
        $user->save();

        session(['show_recovery_codes' => $newCodes]);

        return redirect()->route('2fa.recovery_codes')->with('success', '8 Kode Cadangan baru berhasil dibuat! Simpan kode ini di tempat yang aman.');
    }

    /**
     * Pindahkan 2FA ke Perangkat Baru (Ulang Setup 2FA, wajib konfirmasi password)
     */
    public function resetTwoFactorDevice(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi yang Anda masukkan salah.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        session()->forget('two_factor_authenticated');

        return redirect()->route('2fa.setup')->with('info', '2FA sebelumnya telah direset. Silakan scan QR code baru menggunakan Google Authenticator atau ekstensi authenticator browser Anda.');
    }

    /**
     * Helper redirect sesuai role pengguna
     */
    private function redirectToRoleDashboard($user)
    {
        if ($user->role === 'pegawai') {
            return redirect()->route('pegawai.dokumen');
        }
        if ($user->role === 'kasubag' || $user->role === 'admin_kasubag') {
            return redirect()->route('admin.arsip.index');
        }
        return redirect()->route('admin.aset');
    }
}
