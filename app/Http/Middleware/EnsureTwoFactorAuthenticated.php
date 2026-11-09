<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Jika user masih wajib ganti password (khusus pegawai), biarkan EnsurePasswordChanged memprosesnya
            if ($user->must_change_password && $user->role === 'pegawai') {
                return $next($request);
            }

            // Jika user belum setup 2FA, biarkan EnsureTwoFactorEnrolled memprosesnya
            if (is_null($user->two_factor_confirmed_at)) {
                return $next($request);
            }

            // Jika 2FA sudah aktif, tetapi sesi login ini belum diverifikasi TOTP
            if ($request->session()->get('two_factor_authenticated') !== true) {
                $allowedRoutes = [
                    '2fa.verify',
                    '2fa.verify.check',
                    'logout',
                ];

                if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                    return redirect()->route('2fa.verify');
                }
            }
        }

        return $next($request);
    }
}
