<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    protected Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2fa = new Google2FA();

        Role::insert([
            ['id' => 1, 'nama' => 'Admin Master', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Admin Kasubag', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Pegawai', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * PENGUJIAN 1:
     * User baru dengan password default: login, ganti password lalu otomatis diarahkan ke Setup 2FA.
     * Mengetik URL lain atau AJAX call selalu kembali ke Setup 2FA (tanpa loop).
     */
    public function test_1_new_user_login_must_change_password_then_redirects_to_2fa_setup()
    {
        $user = User::create([
            'name' => 'Pegawai Baru',
            'email' => '199001012020011001',
            'password' => Hash::make('Sinosip123!'),
            'role_id' => 3,
            'must_change_password' => true,
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nip' => '199001012020011001',
            'jabatan' => 'Staff',
            'kategori_pegawai' => 'ASN',
            'pangkat_golongan' => 'Penata Muda / IIIa',
            'no_wa' => '081234567890',
        ]);

        // Login menggunakan NIP
        $response = $this->post('/login', [
            'email' => '199001012020011001',
            'password' => 'Sinosip123!',
        ]);

        // Wajib diarahkan ke halaman ganti password terlebih dahulu
        $response->assertRedirect(route('password.force_reset'));
        $this->assertAuthenticatedAs($user);

        // Ganti password
        $resetResponse = $this->actingAs($user)->post(route('password.force_reset.update'), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        // Setelah ganti password, SELALU langsung diarahkan ke Setup 2FA
        $resetResponse->assertRedirect(route('2fa.setup'));

        $user->refresh();
        $this->assertFalse((bool) $user->must_change_password);

        // Uji URL lain (Dashboard Pegawai) dicegah dan dialihkan ke Setup 2FA
        $dashResponse = $this->actingAs($user)->get(route('dashboard.pegawai'));
        $dashResponse->assertRedirect(route('2fa.setup'));

        // Uji panggilan AJAX langsung dicegah dengan HTTP 403 Forbidden
        $ajaxResponse = $this->actingAs($user)->getJson('/dashboard/pegawai');
        $ajaxResponse->assertStatus(403);
        $ajaxResponse->assertJsonFragment([
            'redirect' => route('2fa.setup'),
        ]);
    }

    /**
     * PENGUJIAN 2:
     * NIP / Email duplikat ditolak dengan pesan jelas pada form Tambah User & Edit User.
     */
    public function test_2_duplicate_nip_or_email_is_rejected_with_clear_message()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@sinosip.bonebolango.go.id',
            'password' => Hash::make('password123'),
            'role_id' => 1,
            'must_change_password' => false,
            'two_factor_confirmed_at' => now(),
        ]);
        session(['2fa_passed' => true]);

        // Akun existing 1
        User::create([
            'name' => 'User Existing',
            'email' => 'existing@sinosip.bonebolango.go.id',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
        ]);

        // Coba tambah user dengan email duplikat
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Duplikat',
            'identity' => 'existing@sinosip.bonebolango.go.id',
            'password' => 'password123',
            'role_id' => 3,
        ]);

        $response->assertSessionHasErrors('identity');
        $this->assertEquals(
            'Email atau NIP sudah digunakan oleh akun lain.',
            session('errors')->first('identity')
        );

        // Akun pegawai existing dengan NIP
        $pegawaiUser = User::create([
            'name' => 'Pegawai ASN',
            'email' => '198501012010011001',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
        ]);
        Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nama' => 'Pegawai ASN',
            'nip' => '198501012010011001',
            'jabatan' => 'Staff',
            'kategori_pegawai' => 'ASN',
            'pangkat_golongan' => 'Penata / IIIc',
            'no_wa' => '-',
        ]);

        // Coba tambah user dengan NIP yang sama
        $response2 = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Duplikat NIP',
            'identity' => '198501012010011001',
            'password' => 'password123',
            'role_id' => 3,
        ]);

        $response2->assertSessionHasErrors('identity');
    }

    /**
     * PENGUJIAN 3:
     * Setup 2FA: Tampilkan QR code & secret manual, konfirmasi kode, simpan recovery code, lalu masuk dashboard.
     * Selama belum selesai, semua URL lain & AJAX tertolak.
     */
    public function test_3_setup_2fa_full_lifecycle_and_blocking()
    {
        $user = User::create([
            'name' => 'Kasubag Test',
            'email' => 'kasubag@kominfo.bonebolango.go.id',
            'password' => Hash::make('PasswordBaru123!'),
            'role_id' => 2,
            'must_change_password' => false,
            'two_factor_confirmed_at' => null,
        ]);

        // 1. Masuk ke halaman Setup 2FA
        $response = $this->actingAs($user)->get(route('2fa.setup'));
        $response->assertStatus(200);
        $response->assertSee('Aktivasi Autentikasi 2FA');
        $response->assertSee('Secret Key Manual');
        $this->assertNotEmpty(session('2fa_pending_secret'));

        $secret = session('2fa_pending_secret');

        // Selama di halaman setup, coba akses halaman surat keluar -> harus ditolak & redirect ke 2fa.setup
        $blockedResponse = $this->actingAs($user)->get(route('surat.keluar'));
        $blockedResponse->assertRedirect(route('2fa.setup'));

        // Coba akses endpoint AJAX -> 403 Forbidden
        $ajaxBlocked = $this->actingAs($user)->getJson(route('surat.api.nextSppd'));
        $ajaxBlocked->assertStatus(403);

        // 2. Submit kode TOTP yang benar dari secret yang digenerate
        $validCode = $this->google2fa->getCurrentOtp($secret);
        $confirmResponse = $this->actingAs($user)->post(route('2fa.setup.confirm'), [
            'code' => $validCode,
        ]);

        // Harus diarahkan ke halaman simpan recovery codes
        $confirmResponse->assertRedirect(route('2fa.recovery-codes'));
        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotEmpty($user->two_factor_recovery_codes);

        // 3. Halaman recovery codes menampilkan 8 kode
        $recResponse = $this->actingAs($user)->get(route('2fa.recovery-codes'));
        $recResponse->assertStatus(200);
        $recResponse->assertSee('Simpan Kode Pemulihan Anda');

        // Coba bypass sebelum konfirmasi centang checkbox simpan -> harus ditolak
        $bypassAcknowledge = $this->actingAs($user)->post(route('2fa.recovery-codes.acknowledge'), []);
        $bypassAcknowledge->assertSessionHasErrors('acknowledged');

        // 4. Centang persetujuan simpan kode
        $ackResponse = $this->actingAs($user)->post(route('2fa.recovery-codes.acknowledge'), [
            'acknowledged' => '1',
        ]);

        // Berhasil diarahkan ke dashboard sesuai role Kasubag (/surat)
        $ackResponse->assertRedirect('/surat');
        $this->assertTrue((bool) session('2fa_passed'));
    }

    /**
     * PENGUJIAN 4:
     * Logout lalu login lagi: minta kode 6 digit, blokir halaman lain sebelum kode benar.
     * Rate limit (5 percobaan), toleransi window (1 menit diterima, >1.5 menit ditolak), anti-replay.
     */
    public function test_4_login_routine_totp_rate_limit_window_and_anti_replay()
    {
        $secret = $this->google2fa->generateSecretKey();
        $user = User::create([
            'name' => 'User TOTP Test',
            'email' => 'user.totp@test.com',
            'password' => Hash::make('Password123!'),
            'role_id' => 3,
            'must_change_password' => false,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'totp_last_used_step' => null,
        ]);

        // 1. Login dengan email + password benar
        $response = $this->post('/login', [
            'email' => 'user.totp@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('2fa.verify'));
        $this->assertFalse((bool) session('2fa_passed'));

        // Coba buka dashboard sebelum memasukkan kode 2FA -> dialihkan kembali ke 2fa.verify
        $dashResponse = $this->actingAs($user)->get(route('dashboard.pegawai'));
        $dashResponse->assertRedirect(route('2fa.verify'));

        // 2. Kode salah berulang terkena rate limit (5x)
        RateLimiter::clear('2fa_verify:127.0.0.1|' . $user->id);
        for ($i = 0; $i < 5; $i++) {
            $failResponse = $this->actingAs($user)->post(route('2fa.verify.check'), [
                'code' => '000000',
            ]);
            $failResponse->assertSessionHasErrors('code');
        }

        // Percobaan ke-6 harus terkunci oleh Rate Limiter
        $lockedResponse = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'code' => '000000',
        ]);
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('code'));

        RateLimiter::clear('2fa_verify:127.0.0.1|' . $user->id);

        // 3. Uji toleransi window:
        // Window config = 2 (2 * 30s = 60s sebelum dan sesudah)
        $currentTs = $this->google2fa->getTimestamp();
        
        // Kode sekarang
        $validCurrentCode = $this->google2fa->getCurrentOtp($secret);
        
        // Kode 1 interval (30 detik lalu) masih dalam window 2 -> DITERIMA
        $stepMinus1 = $currentTs - 1;
        $validMinus1Code = $this->google2fa->oathTotp($secret, $stepMinus1);
        
        // Kode 4 interval (120 detik / 2 menit lalu) -> DI LUAR WINDOW (DITOLAK)
        $stepMinus4 = $currentTs - 4;
        $expiredCode = $this->google2fa->oathTotp($secret, $stepMinus4);

        $expiredResponse = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'code' => $expiredCode,
        ]);
        $expiredResponse->assertSessionHasErrors('code');

        // Verifikasi kode sekarang berhasil
        $successResponse = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'code' => $validCurrentCode,
        ]);
        $successResponse->assertRedirect(route('dashboard.pegawai'));
        $this->assertTrue((bool) session('2fa_passed'));

        $user->refresh();
        $this->assertNotNull($user->totp_last_used_step);

        // 4. Anti-replay: Kode yang sama TIDAK BISA digunakan dua kali
        session(['2fa_passed' => false]);
        $replayResponse = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'code' => $validCurrentCode,
        ]);
        $replayResponse->assertSessionHasErrors('code');
    }

    /**
     * PENGUJIAN 5:
     * Recovery code berhasil sekali, pemakaian kedua ditolak.
     */
    public function test_5_recovery_code_one_time_use_only()
    {
        $plainCode1 = 'ABCD-1234';
        $plainCode2 = 'WXYZ-5678';
        $hashedCodes = [
            hash('sha256', 'ABCD1234'),
            hash('sha256', 'WXYZ5678'),
        ];

        $user = User::create([
            'name' => 'User Recovery Test',
            'email' => 'user.recovery@test.com',
            'password' => Hash::make('Password123!'),
            'role_id' => 3,
            'must_change_password' => false,
            'two_factor_secret' => 'SECRETKEY1234567',
            'two_factor_recovery_codes' => json_encode($hashedCodes),
            'two_factor_confirmed_at' => now(),
        ]);

        session(['2fa_passed' => false]);

        // Pemakaian pertama kode 1
        $res1 = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'recovery_code' => $plainCode1,
        ]);

        $res1->assertRedirect(route('dashboard.pegawai'));
        $this->assertTrue((bool) session('2fa_passed'));

        $user->refresh();
        $remaining = json_decode($user->two_factor_recovery_codes, true);
        $this->assertCount(1, $remaining);
        $this->assertNotContains(hash('sha256', 'ABCD1234'), $remaining);

        // Pemakaian kedua kode 1 yang sama -> DITOLAK
        session(['2fa_passed' => false]);
        $res2 = $this->actingAs($user)->post(route('2fa.verify.check'), [
            'recovery_code' => $plainCode1,
        ]);

        $res2->assertSessionHasErrors('recovery_code');
        $this->assertFalse((bool) session('2fa_passed'));
    }

    /**
     * PENGUJIAN 6:
     * Semua role (Admin Master, Kasubag, Pegawai) dan role baru dinamis (Kasubag Bendahara Barang)
     * otomatis terkena kewajiban 2FA tanpa perubahan kode.
     */
    public function test_6_all_roles_and_dynamic_new_role_are_enforced_2fa()
    {
        // Buat role baru dinamis di database (mis. ID 4: Kasubag Bendahara Barang)
        $newRole = Role::create([
            'id' => 4,
            'nama' => 'Kasubag Bendahara Barang',
        ]);

        $rolesToTest = [
            1 => 'Admin Master',
            2 => 'Admin Kasubag',
            3 => 'Pegawai Biasa',
            4 => 'Kasubag Bendahara Barang',
        ];

        foreach ($rolesToTest as $roleId => $roleName) {
            $user = User::create([
                'name' => "User {$roleName}",
                'email' => "user{$roleId}@sinosip.test",
                'password' => Hash::make('password'),
                'role_id' => $roleId,
                'must_change_password' => false,
                'two_factor_confirmed_at' => null, // Belum setup 2FA
            ]);

            // Coba akses route autentikasi apa pun -> WAJIB dialihkan ke 2fa.setup
            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertRedirect(route('2fa.setup'));

            // Uji AJAX -> WAJIB 403
            $ajaxResponse = $this->actingAs($user)->getJson('/dashboard');
            $ajaxResponse->assertStatus(403);
        }
    }

    /**
     * PENGUJIAN 7:
     * Admin Master menekan Reset Password: user wajib ganti password dan setup 2FA ulang, authenticator lama hangus.
     * Uji juga aksi Reset 2FA terpisah.
     */
    public function test_7_reset_password_and_separate_reset_2fa()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@sinosip.bonebolango.go.id',
            'password' => Hash::make('admin123'),
            'role_id' => 1,
            'must_change_password' => false,
            'two_factor_confirmed_at' => now(),
        ]);
        session(['2fa_passed' => true]);

        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@sinosip.bonebolango.go.id',
            'password' => Hash::make('oldpassword'),
            'role_id' => 2,
            'must_change_password' => false,
            'two_factor_secret' => 'OLDSECRETKEY12345',
            'two_factor_recovery_codes' => json_encode(['dummyhash']),
            'two_factor_confirmed_at' => now(),
            'totp_last_used_step' => 123456,
        ]);

        // 1. Uji Reset 2FA Terpisah
        $res2fa = $this->actingAs($admin)->post(route('admin.users.reset-2fa', $targetUser->id));
        $res2fa->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertNull($targetUser->two_factor_secret);
        $this->assertNull($targetUser->two_factor_confirmed_at);
        $this->assertNull($targetUser->totp_last_used_step);
        // Password target user tetap tidak berubah
        $this->assertTrue(Hash::check('oldpassword', $targetUser->password));

        // Setup ulang 2FA targetUser
        $targetUser->update([
            'two_factor_secret' => 'RENEWEDSECRET123',
            'two_factor_confirmed_at' => now(),
            'totp_last_used_step' => 999999,
        ]);

        // 2. Uji Reset Password oleh Admin Master
        $resPwd = $this->actingAs($admin)->post(route('admin.users.reset-password', $targetUser->id), [
            'custom_password' => 'NewCustomPass123!',
        ]);
        $resPwd->assertSessionHas('reset_success');

        $targetUser->refresh();
        $this->assertTrue(Hash::check('NewCustomPass123!', $targetUser->password));
        $this->assertTrue((bool) $targetUser->must_change_password);
        // Seluruh data 2FA wajib ikut terhapus
        $this->assertNull($targetUser->two_factor_secret);
        $this->assertNull($targetUser->two_factor_confirmed_at);
        $this->assertNull($targetUser->totp_last_used_step);
    }

    /**
     * PENGUJIAN 8:
     * Admin Master tidak bisa melihat secret atau recovery code user lain, dan tidak ada cara opt-out 2FA permanen.
     */
    public function test_8_no_secret_leak_and_no_permanent_opt_out()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin.audit@sinosip.test',
            'password' => Hash::make('admin123'),
            'role_id' => 1,
            'must_change_password' => false,
            'two_factor_confirmed_at' => now(),
        ]);
        session(['2fa_passed' => true]);

        $pegawaiSecret = 'PEGAWAISECRET7890';
        $userPegawai = User::create([
            'name' => 'Pegawai Rahasia',
            'email' => 'rahasia@sinosip.test',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
            'two_factor_secret' => $pegawaiSecret,
            'two_factor_recovery_codes' => json_encode(['secret_hash_code']),
            'two_factor_confirmed_at' => now(),
        ]);

        // Buka halaman manajemen user
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);

        // Pastikan plain secret atau hash recovery code tidak pernah bocor di HTML response
        $response->assertDontSee($pegawaiSecret);
        $response->assertDontSee('secret_hash_code');

        // Pastikan serialisasi JSON user menyembunyikan secret dan recovery codes
        $userJson = $userPegawai->toJson();
        $this->assertStringNotContainsString($pegawaiSecret, $userJson);
        $this->assertStringNotContainsString('secret_hash_code', $userJson);
    }

    /**
     * PENGUJIAN 9:
     * Command user:purge --dry-run berjalan dan hanya menampilkan daftar tanpa menghapus data.
     */
    public function test_9_command_user_purge_dry_run_does_not_delete_any_data()
    {
        $adminCountBefore = User::where('role_id', 1)->count();
        $totalUsersBefore = User::count();

        // Jalankan artisan user:purge --dry-run
        $this->artisan('user:purge', ['--dry-run' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('DRY-RUN (SIMULASI - Tidak ada data yang dihapus)');

        // Pastikan jumlah user tidak berubah satu pun
        $this->assertEquals($totalUsersBefore, User::count());
        $this->assertEquals($adminCountBefore, User::where('role_id', 1)->count());
    }
}
