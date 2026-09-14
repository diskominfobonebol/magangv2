<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_login_with_admin_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@kominfo.bonebolango.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard/master');
        $this->assertAuthenticated();
    }

    public function test_login_with_admin_nip(): void
    {
        $response = $this->post('/login', [
            'email' => '198001012005011001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard/master');
        $this->assertAuthenticated();
    }

    public function test_login_with_kasubag_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'kasubag@kominfo.bonebolango.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/surat');
        $this->assertAuthenticated();
    }

    public function test_login_with_kasubag_nip(): void
    {
        $response = $this->post('/login', [
            'email' => '198001012005011002',
            'password' => 'password',
        ]);

        $response->assertRedirect('/surat');
        $this->assertAuthenticated();
    }

    public function test_login_with_bendahara_nip(): void
    {
        $response = $this->post('/login', [
            'email' => '198001012005011004',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/aset');
        $this->assertAuthenticated();
    }

    public function test_login_with_mahasiswa_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'mahasiswa@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_with_pegawai_nip(): void
    {
        $response = $this->post('/login', [
            'email' => '198001012005011003',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard/pegawai');
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@kominfo.bonebolango.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_unknown_account(): void
    {
        $response = $this->post('/login', [
            'email' => 'unknown@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_with_formatted_nip_with_spaces_or_dashes(): void
    {
        $response = $this->post('/login', [
            'email' => '19800101 200501 1 001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard/master');
        $this->assertAuthenticated();
    }

    public function test_login_with_admin_domain_alias(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@kominfo.bonebolango.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard/master');
        $this->assertAuthenticated();
    }

    public function test_login_fails_when_user_is_inactive(): void
    {
        $user = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => 'admin@kominfo.bonebolango.id',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_and_immediate_login(): void
    {
        $response = $this->post('/register', [
            'nama' => 'Mahasiswa Baru',
            'email' => 'mhsbaru@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nim' => '12345678',
            'universitas' => 'Universitas Negeri Gorontalo',
            'jurusan' => 'Teknik Informatika',
        ]);

        $response->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'mhsbaru@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(5, $user->role_id);
    }

    public function test_logout_works(): void
    {
        $user = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $targetUser = User::where('email', 'pegawai@kominfo.bonebolango.go.id')->first();

        // 1. Reset dengan custom password
        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/reset-password", [
            'custom_password' => 'newpassword123',
        ]);

        $response->assertSessionHas('reset_success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $targetUser->fresh()->password));

        // 2. Reset otomatis (generate random 8 chars)
        $responseAuto = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/reset-password", []);
        $responseAuto->assertSessionHas('reset_success');
        $newPass = session('reset_success')['password'];
        $this->assertEquals(8, strlen($newPass));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($newPass, $targetUser->fresh()->password));
    }
}
