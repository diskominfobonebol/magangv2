<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordResetTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        Role::insert([
            ['id' => 1, 'nama' => 'Admin Master', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Admin Kasubag', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Pegawai', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Test 1: User baru otomatis memiliki must_change_password = true
     */
    public function test_new_user_creation_has_must_change_password_true_by_default()
    {
        $user = User::create([
            'name' => 'Pegawai Baru Test',
            'email' => '199999999999999999',
            'password' => Hash::make('password123'),
            'role_id' => 3,
        ]);

        $this->assertTrue((bool)$user->must_change_password);
    }

    /**
     * Test 2: User dengan must_change_password = true tidak bisa akses dashboard dan dialihkan ke reset-password-wajib
     */
    public function test_user_with_must_change_password_is_redirected_to_force_reset_page()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'must_change_password' => true,
        ]);

        $this->actingAs($admin)
            ->get('/dashboard/master')
            ->assertRedirect(route('password.force_reset'));

        $kasubag = User::create([
            'name' => 'Kasubag Test',
            'email' => 'kasubag@test.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'must_change_password' => true,
        ]);

        $this->actingAs($kasubag)
            ->get('/surat/keluar')
            ->assertRedirect(route('password.force_reset'));

        $pegawai = User::create([
            'name' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'must_change_password' => true,
        ]);

        $this->actingAs($pegawai)
            ->get('/dashboard/pegawai')
            ->assertRedirect(route('password.force_reset'));
    }

    /**
     * Test 3: Halaman reset-password-wajib dapat dibuka oleh user yang must_change_password = true
     */
    public function test_force_reset_page_can_be_rendered()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('password.force_reset'));
        $response->assertStatus(200);
        $response->assertSee('Wajib Ganti Password');
        $response->assertSee('Password Baru');
        $response->assertSee('Konfirmasi Password Baru');
    }

    /**
     * Test 4: Validasi password minimal 8 karakter dan konfirmasi cocok
     */
    public function test_validation_rules_on_force_reset()
    {
        $user = User::create([
            'name' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'must_change_password' => true,
        ]);

        // Terlalu pendek (< 8)
        $response = $this->actingAs($user)->post(route('password.force_reset.update'), [
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);
        $response->assertSessionHasErrors(['password']);

        // Konfirmasi tidak cocok
        $response = $this->actingAs($user)->post(route('password.force_reset.update'), [
            'password' => 'passwordBaru123',
            'password_confirmation' => 'bedaPassword123',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test 5: Submit reset password berhasil mengubah password, must_change_password menjadi false, dan redirect ke dashboard
     */
    public function test_successful_force_password_reset()
    {
        $admin = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('password.force_reset.update'), [
            'password' => 'adminNewPassword123',
            'password_confirmation' => 'adminNewPassword123',
        ]);

        $response->assertRedirect(route('dashboard.master'));
        $response->assertSessionHas('success');

        $admin->refresh();
        $this->assertFalse((bool)$admin->must_change_password);
        $this->assertTrue(Hash::check('adminNewPassword123', $admin->password));

        // Setelah must_change_password = false, user bisa akses dashboard
        $this->actingAs($admin)
            ->get('/dashboard/master')
            ->assertStatus(200);

        // Jika mencoba akses /reset-password-wajib lagi, akan di-redirect ke dashboard
        $this->actingAs($admin)
            ->get(route('password.force_reset'))
            ->assertRedirect(route('dashboard.master'));
    }
}
