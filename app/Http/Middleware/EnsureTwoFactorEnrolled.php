<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnrolled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Pengecualian mutlak: Logout selalu diizinkan
        if ($request->routeIs('logout') || $request->is('logout')) {
            return $next($request);
        }

        // 2. Jika user masih wajib ganti password (fitur yang sudah ada),
        // serahkan ke alur ganti password terlebih dahulu.
        if ($user->must_change_password) {
            if ($request->routeIs('password.force_reset*') || $request->is('reset-password-wajib*')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Password wajib diperbarui sebelum mengakses sumber daya ini.',
                    'redirect' => route('password.force_reset'),
                ], 403);
            }

            return redirect()->route('password.force_reset');
        }

        // 3. Jika user sedang mengakses rute ganti password tapi sudah tidak wajib,
        // cegah akses dan teruskan ke alur 2FA jika belum selesai.
        if ($request->routeIs('password.force_reset*') || $request->is('reset-password-wajib*')) {
            if (!$user->hasTwoFactorEnabled()) {
                return redirect()->route('2fa.setup');
            }
            if (!session('2fa_passed')) {
                return redirect()->route('2fa.verify');
            }
            return $this->redirectToDashboard($user);
        }

        // 4. KONDISI A: User baru menyelesaikan konfirmasi 6 digit dan wajib menyimpan 8 recovery codes
        if (session()->has('2fa_new_recovery_codes')) {
            $allowedRecoveryRoutes = [
                '2fa.recovery-codes',
                '2fa.recovery-codes.acknowledge',
            ];

            foreach ($allowedRecoveryRoutes as $route) {
                if ($request->routeIs($route)) {
                    return $next($request);
                }
            }

            if ($request->is('2fa/recovery-codes*')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Anda wajib menyimpan kode pemulihan sebelum melanjutkan.',
                    'redirect' => route('2fa.recovery-codes'),
                ], 403);
            }

            return redirect()->route('2fa.recovery-codes');
        }

        // 5. KONDISI B: User BELUM mengaktifkan 2FA (two_factor_confirmed_at kosong)
        // User wajib berada di halaman Setup 2FA.
        if (!$user->hasTwoFactorEnabled() || session('2fa_re_enrolling')) {
            $allowedSetupRoutes = [
                '2fa.setup',
                '2fa.setup.confirm',
            ];

            foreach ($allowedSetupRoutes as $route) {
                if ($request->routeIs($route)) {
                    return $next($request);
                }
            }

            if ($request->is('2fa/setup*')) {
                return $next($request);
            }

            // Tangani AJAX / API endpoint
            if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Autentikasi Dua Faktor (2FA) wajib diaktifkan sebelum mengakses sumber daya ini.',
                    'redirect' => route('2fa.setup'),
                ], 403);
            }

            // Redirect paksa ke halaman Setup 2FA
            return redirect()->route('2fa.setup');
        }

        // 6. KONDISI C: User SUDAH mengaktifkan 2FA, tetapi BELUM lolos verifikasi 2FA pada sesi ini
        if (!session('2fa_passed')) {
            $allowedVerifyRoutes = [
                '2fa.verify',
                '2fa.verify.check',
            ];

            foreach ($allowedVerifyRoutes as $route) {
                if ($request->routeIs($route)) {
                    return $next($request);
                }
            }

            if ($request->is('2fa/verify*')) {
                return $next($request);
            }

            // Tangani AJAX / API endpoint
            if ($request->expectsJson() || $request->is('*/api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Verifikasi Autentikasi Dua Faktor (2FA) diperlukan.',
                    'redirect' => route('2fa.verify'),
                ], 403);
            }

            // Redirect paksa ke halaman Verifikasi 2FA
            return redirect()->route('2fa.verify');
        }

        // 6. KONDISI C: User sudah lolos verifikasi 2FA penuh (session 2fa_passed = true)
        // Cegah akses balik ke halaman verifikasi atau setup biasa
        if ($request->routeIs('2fa.verify*') || $request->routeIs('2fa.setup*')) {
            return $this->redirectToDashboard($user);
        }

        return $next($request);
    }

    protected function redirectToDashboard($user)
    {
        $role = (int) $user->role_id;
        if ($role === 1) {
            return redirect()->route('dashboard.master');
        } elseif ($role === 2) {
            return redirect('/surat');
        } elseif ($role === 4) {
            return redirect('/admin/aset');
        } elseif ($role === 5) {
            return redirect()->route('mahasiswa.dashboard');
        } else {
            return redirect()->route('dashboard.pegawai');
        }
    }
}
