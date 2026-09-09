<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRoleId = (int) $user->role_id;

        // Flatten roles if passed as comma-separated strings (e.g. "role:bendahara_barang,admin")
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', (string) $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // Check if numeric ID matches directly
        if (in_array((string) $userRoleId, $allowedRoles, true) || in_array($userRoleId, $allowedRoles, true)) {
            return $next($request);
        }

        // Map user's role to accepted string aliases
        $userAliases = match ($userRoleId) {
            1 => ['1', 'admin', 'admin_master', 'master', 'bendahara_barang', 'bendahara'], // Admin Master has super access
            2 => ['2', 'kasubag', 'admin_kasubag', 'admin'],
            3 => ['3', 'pegawai'],
            4 => ['4', 'bendahara_barang', 'bendahara'],
            5 => ['5', 'mahasiswa'],
            default => [(string) $userRoleId],
        };

        // If any allowed role matches any of the user's aliases, allow access
        foreach ($allowedRoles as $allowed) {
            if (in_array(strtolower($allowed), $userAliases, true)) {
                return $next($request);
            }
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
