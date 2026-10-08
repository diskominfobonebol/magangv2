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

        // Flatten roles if passed as comma-separated strings (e.g. "role:bendahara_barang,admin,4,1")
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', (string) $role) as $r) {
                $trimmed = trim($r);
                $allowedRoles[] = $trimmed;
                if (is_numeric($trimmed)) {
                    $allowedRoles[] = (int) $trimmed;
                }
            }
        }

        // Map role aliases
        $roleNameMap = [
            1 => ['1', 1, 'admin', 'admin_master', 'master'],
            2 => ['2', 2, 'kasubag', 'admin_kasubag'],
            3 => ['3', 3, 'pegawai'],
            4 => ['4', 4, 'bendahara_barang', 'bendahara'],
            5 => ['5', 5, 'mahasiswa'],
        ];

        $userMatches = $roleNameMap[$userRoleId] ?? [$userRoleId, (string) $userRoleId];

        foreach ($userMatches as $match) {
            if (in_array($match, $allowedRoles, true)) {
                return $next($request);
            }
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melakukan aksi atau mengakses halaman ini.');
    }
}
