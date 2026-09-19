<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan form minta link reset password (Forgot Password)
     */
    public function showForgotPasswordForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Kirim link reset password ke email mahasiswa
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Validasi khusus: Mahasiswa mandiri, Pegawai/Admin tetap via Administrator
        if ($user && $user->role_id != 5) {
            return back()->withErrors([
                'email' => 'Fitur reset kata sandi mandiri khusus untuk akun Mahasiswa Magang. Untuk akun Pegawai / Administrator, silakan hubungi Administrator Diskominfo.',
            ])->onlyInput('email');
        }

        // Jalankan broker password bawaan Laravel
        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Tautan reset kata sandi berhasil dikirim! Silakan periksa kotak masuk atau folder spam email Anda.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }

    /**
     * Tampilkan form pembuatan password baru berdasarkan token
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Proses reset password baru
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        // Pastikan pengguna berstatus mahasiswa
        $user = User::where('email', $request->email)->first();
        if ($user && $user->role_id != 5) {
            return redirect()->route('login')->with('error', 'Akses reset kata sandi ini hanya berlaku untuk mahasiswa magang.');
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login.mahasiswa')->with('success', 'Kata sandi berhasil diperbarui! Silakan login menggunakan kata sandi baru Anda.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }
}
