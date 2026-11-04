<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Tampilkan halaman Setup 2FA.
     */
    public function showSetup(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika sudah aktif dan tidak sedang re-enroll dari halaman keamanan akun
        if ($user->hasTwoFactorEnabled() && !session('2fa_re_enrolling')) {
            if (session('2fa_passed')) {
                return $this->redirectToDashboard($user);
            }
            return redirect()->route('2fa.verify');
        }

        // Generate atau ambil secret sementara dari session
        $secret = session('2fa_pending_secret');
        if (!$secret) {
            $secret = $this->google2fa->generateSecretKey();
            session(['2fa_pending_secret' => $secret]);
        }

        // Tentukan nama akun di authenticator: Sinosip (NIP) atau Sinosip (email)
        $holder = ($user->pegawai && !empty($user->pegawai->nip)) ? $user->pegawai->nip : $user->email;
        $qrCodeUrl = $this->google2fa->getQRCodeUrl('Sinosip', $holder, $secret);

        // Render QR Code dengan quiet zone standar 4 modul dan resolusi 280px
        // agar Google Authenticator di HP sangat mudah & cepat memindai dari layar web
        $renderer = new ImageRenderer(
            new RendererStyle(280, 4),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $rawSvg = $writer->writeString($qrCodeUrl);

        // Bersihkan prolog XML jika ada agar valid di dalam dokumen HTML5
        $cleanSvg = preg_replace('/<\?xml.*?\?>/i', '', $rawSvg);
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($cleanSvg);

        return view('auth.2fa-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $cleanSvg,
            'qrCodeDataUri' => $qrCodeDataUri,
            'qrCodeUrl' => $qrCodeUrl,
            'holder' => $holder,
            'user' => $user,
        ]);
    }

    /**
     * Konfirmasi 6-digit TOTP code untuk aktivasi 2FA.
     */
    public function confirmSetup(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Rate limit: 5 percobaan per menit per user/IP
        $throttleKey = '2fa_confirm:' . $request->ip() . '|' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'code' => "Terlalu banyak percobaan aktivasi yang gagal. Silakan tunggu {$seconds} detik.",
            ]);
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Kode verifikasi 6 digit wajib diisi.',
            'code.digits' => 'Kode verifikasi harus berupa 6 digit angka.',
        ]);

        $secret = session('2fa_pending_secret');
        if (!$secret) {
            return redirect()->route('2fa.setup')->withErrors([
                'code' => 'Sesi setup telah kadaluarsa. Silakan scan ulang QR code.',
            ]);
        }

        $window = (int) config('auth.totp_window', 2);
        // Anti-replay: verifyKeyNewer menolak timestep yang sama atau lebih lama
        $lastStep = $user->totp_last_used_step ?? 0;
        $validStep = $this->google2fa->verifyKeyNewer($secret, $request->code, $lastStep, $window);

        if ($validStep === false) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'code' => 'Kode verifikasi 6 digit salah atau telah kadaluarsa. Pastikan jam di HP/laptop Anda sinkron dan coba lagi.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // 2FA Sukses diaktifkan! Buat 8 recovery codes sekali pakai
        $plainCodes = [];
        $hashedCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4));
            $plainCodes[] = $code;
            // Hash menggunakan SHA-256 (satu arah, aman jika DB bocor)
            $hashedCodes[] = hash('sha256', strtoupper(str_replace('-', '', $code)));
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($hashedCodes),
            'two_factor_confirmed_at' => now(),
            'totp_last_used_step' => (int) $validStep,
        ])->save();

        session()->forget(['2fa_pending_secret', '2fa_re_enrolling']);
        session(['2fa_new_recovery_codes' => $plainCodes]);

        return redirect()->route('2fa.recovery-codes');
    }

    /**
     * Tampilkan 8 recovery codes sekali pakai (hanya tampil satu kali).
     */
    public function showRecoveryCodes()
    {
        $codes = session('2fa_new_recovery_codes');
        if (empty($codes)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            return $this->redirectToDashboard($user);
        }

        return view('auth.2fa-recovery-codes', [
            'codes' => $codes,
        ]);
    }

    /**
     * Konfirmasi bahwa user telah menyimpan recovery codes.
     */
    public function acknowledgeRecoveryCodes(Request $request)
    {
        $request->validate([
            'acknowledged' => ['accepted'],
        ], [
            'acknowledged.accepted' => 'Anda wajib mencentang persetujuan bahwa Anda telah menyimpan recovery codes sebelum melanjutkan.',
        ]);

        session()->forget('2fa_new_recovery_codes');
        session(['2fa_passed' => true]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $this->redirectToDashboard($user)->with('success', 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!');
    }

    /**
     * Tampilkan halaman Verifikasi 2FA saat login rutin.
     */
    public function showVerify()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika belum setup 2FA, wajib diarahkan ke setup
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('2fa.setup');
        }

        // Jika sudah verified untuk sesi ini, arahkan ke dashboard
        if (session('2fa_passed')) {
            return $this->redirectToDashboard($user);
        }

        return view('auth.2fa-verify', [
            'user' => $user,
        ]);
    }

    /**
     * Proses verifikasi 2FA (TOTP 6-digit atau Recovery Code).
     */
    public function verify(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $throttleKey = '2fa_verify:' . $request->ip() . '|' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $userLabel = $user ? ($user->name . ' (' . $user->email . ')') : 'User #' . ($user->id ?? 0);
            \App\Services\NotificationService::notifySecurityLockout($userLabel, '2fa');
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'code' => "Terlalu banyak percobaan verifikasi yang gagal. Akun dikunci sementara selama {$seconds} detik.",
            ]);
        }

        // Opsi 1: User memasukkan Recovery Code sekali pakai
        if ($request->filled('recovery_code')) {
            $rawCode = strtoupper(str_replace(['-', ' '], '', trim($request->recovery_code)));
            $codeHash = hash('sha256', $rawCode);

            $storedCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];
            $foundIndex = array_search($codeHash, $storedCodes, true);

            if ($foundIndex !== false) {
                // Hanguskan recovery code yang sudah dipakai
                unset($storedCodes[$foundIndex]);
                $user->forceFill([
                    'two_factor_recovery_codes' => json_encode(array_values($storedCodes)),
                ])->save();

                RateLimiter::clear($throttleKey);
                session(['2fa_passed' => true]);

                $sisa = count($storedCodes);
                return $this->redirectToDashboard($user)->with('warning', "Anda berhasil masuk menggunakan Recovery Code. Kode tersebut sekarang telah hangus (tersisa {$sisa} kode).");
            }

            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'recovery_code' => 'Kode pemulihan tidak valid atau sudah pernah digunakan sebelumnya.',
            ]);
        }

        // Opsi 2: Verifikasi TOTP 6-digit
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Kode autentikasi 6 digit wajib diisi.',
            'code.digits' => 'Kode autentikasi harus terdiri dari 6 digit angka.',
        ]);

        $window = (int) config('auth.totp_window', 2);
        $lastStep = $user->totp_last_used_step ?? 0;
        $validStep = $this->google2fa->verifyKeyNewer($user->two_factor_secret, $request->code, $lastStep, $window);

        if ($validStep === false) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'code' => 'Kode autentikasi salah atau telah kadaluarsa. Pastikan jam di HP/laptop Anda sinkron dan coba lagi.',
            ]);
        }

        // Anti-replay: Simpan timestep yang sukses digunakan
        $user->forceFill([
            'totp_last_used_step' => (int) $validStep,
        ])->save();

        RateLimiter::clear($throttleKey);
        session(['2fa_passed' => true]);

        return $this->redirectToDashboard($user);
    }

    /**
     * Halaman Keamanan Akun (Profil / Header dropdown).
     */
    public function showAccountSecurity()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $storedCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];

        return view('auth.account-security', [
            'user' => $user,
            'recoveryCodesCount' => count($storedCodes),
        ]);
    }

    /**
     * Buat ulang 8 recovery codes baru (wajib konfirmasi password).
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Konfirmasi password saat ini wajib diisi.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        $plainCodes = [];
        $hashedCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4));
            $plainCodes[] = $code;
            $hashedCodes[] = hash('sha256', strtoupper(str_replace('-', '', $code)));
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($hashedCodes),
        ])->save();

        session(['2fa_new_recovery_codes' => $plainCodes]);

        return redirect()->route('2fa.recovery-codes')->with('success', 'Recovery codes baru berhasil dibuat!');
    }

    /**
     * Pindahkan 2FA ke perangkat baru / setup ulang (wajib konfirmasi password).
     */
    public function reEnroll(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Konfirmasi password saat ini wajib diisi.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        // Reset status 2FA dan tandai sesi sedang re-enrolling
        $user->resetTwoFactor();
        session(['2fa_re_enrolling' => true]);
        session(['2fa_passed' => false]);
        session()->forget('2fa_pending_secret');

        return redirect()->route('2fa.setup')->with('info', 'Silakan pindai QR code baru pada perangkat Anda untuk memperbarui 2FA.');
    }

    /**
     * Helper redirect ke dashboard sesuai role dinamis pengguna.
     */
    protected function redirectToDashboard($user)
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
