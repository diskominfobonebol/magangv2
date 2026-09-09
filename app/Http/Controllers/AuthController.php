<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('email'));
        if (strtolower($loginInput) === 'admin@kominfo.bonebolango.go.id') {
            $loginInput = 'admin@kominfo.bonebolango.id';
        }

        // Deteksi: Jika input mengandung karakter '@', anggap sebagai email. Jika tidak, anggap sebagai NIP.
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'email'; 
        
        $credentialsArray = [
            $fieldType => $loginInput,
            'password' => $request->input('password')
        ];

        if (Auth::attempt($credentialsArray)) {
            $request->session()->regenerate();
            
            $role = (int) Auth::user()->role_id;
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
