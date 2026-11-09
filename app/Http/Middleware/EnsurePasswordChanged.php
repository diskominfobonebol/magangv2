<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Pengaman: Jika user belum login atau perannya bukan Pegawai, lewati pengecekan ini sepenuhnya
        if (!$user || $user->role !== 'pegawai') {
            return $next($request);
        }

        if ($user->must_change_password) {
            $allowedRoutes = [
                'password.change',
                'password.change.update',
                'logout',
            ];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
