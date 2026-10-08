<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Jika user wajib ganti password
            if ($user->must_change_password) {
                // Kecualikan rute reset password wajib (GET & POST) serta rute logout
                if (!$request->routeIs('password.force_reset*') && !$request->routeIs('logout') && !$request->is('logout') && !$request->is('reset-password-wajib*')) {
                    return redirect()->route('password.force_reset');
                }
            } else {
                // Jika user sudah tidak wajib ganti password, cegah akses ke halaman reset password wajib
                if ($request->routeIs('password.force_reset*') || $request->is('reset-password-wajib*')) {
                    $role = (int) $user->role_id;
                    if ($role === 1) {
                        return redirect()->route('dashboard.master');
                    } elseif ($role === 2) {
                        return redirect('/surat');
                    } else {
                        return redirect()->route('dashboard.pegawai');
                    }
                }
            }
        }

        return $next($request);
    }
}
