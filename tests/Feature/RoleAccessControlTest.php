<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratMasuk;
use App\Models\SuratTelaah;
use App\Models\SuratSk;
use App\Models\Pegawai;
use App\Models\DokumenPegawai;
use App\Models\JenisSurat;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use DatabaseMigrations;

    protected User $adminMaster;
    protected User $kasubag;
    protected User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::insert([
            ['id' => 1, 'nama' => 'Admin Master', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Admin Kasubag', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Pegawai', 'created_at' => now(), 'updated_at' => now()],
        ]);

        JenisSurat::insert([
            ['id' => 1, 'kode' => '090', 'nama_jenis' => 'SPPD', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'kode' => '555', 'nama_jenis' => 'SPT', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->adminMaster = User::create([
            'name' => 'Admin Master',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role_id' => 1,
            'must_change_password' => false,
        ]);

        $this->kasubag = User::create([
            'name' => 'Kasubag Test',
            'email' => 'kasubag@test.com',
            'password' => Hash::make('password123'),
            'role_id' => 2,
            'must_change_password' => false,
        ]);

        $this->pegawai = User::create([
            'name' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test 1: Admin Master memiliki akses eksklusif ke Pengaturan WA & Manajemen User.
     * Kasubag dan Pegawai ditolak (403).
     */
    public function test_exclusive_admin_master_routes()
    {
        // Admin Master bisa akses
        $this->actingAs($this->adminMaster)->get('/admin/settings')->assertStatus(200);
        $this->actingAs($this->adminMaster)->get('/admin/users')->assertStatus(200);

        // Kasubag ditolak (403)
        $this->actingAs($this->kasubag)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($this->kasubag)->get('/admin/users')->assertStatus(403);

        // Pegawai ditolak (403)
        $this->actingAs($this->pegawai)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($this->pegawai)->get('/admin/users')->assertStatus(403);
    }

    /**
     * Test 2: Admin Master dan Kasubag keduanya bisa melihat / membaca (index/show/export/print) di modul Surat & Kenpa.
     */
    public function test_read_only_routes_accessible_by_both_admin_and_kasubag()
    {
        foreach ([$this->adminMaster, $this->kasubag] as $user) {
            $this->actingAs($user)->get('/surat/keluar')->assertStatus(200);
            $this->actingAs($user)->get('/surat/rekap')->assertStatus(200);
            $this->actingAs($user)->get('/surat/masuk')->assertStatus(200);
            $this->actingAs($user)->get('/surat/telaah')->assertStatus(200);
            $this->actingAs($user)->get('/surat/sk')->assertStatus(200);
            $this->actingAs($user)->get('/kenaikan-pangkat')->assertStatus(200);
        }
    }

    /**
     * Test 3: Admin Master DITOLAK (403) saat mengakses SEMUA route mutasi Surat Menyurat.
     */
    public function test_admin_master_blocked_from_surat_mutation_routes()
    {
        $this->actingAs($this->adminMaster);

        // Surat Keluar (SPT & SPPD)
        $this->get('/surat/create')->assertStatus(403);
        $this->get('/surat/create/step-2')->assertStatus(403);
        $this->get('/surat/create/step-3')->assertStatus(403);
        $this->get('/surat/draft')->assertStatus(403);
        $this->post('/surat/draft')->assertStatus(403);
        $this->get('/surat/api/next-sppd-counter')->assertStatus(403);
        $this->get('/surat/api/check-backdate')->assertStatus(403);
        $this->post('/surat/api/pegawai-p3k')->assertStatus(403);
        $this->post('/surat/sppd/store-standalone')->assertStatus(403);
        $this->post('/surat/1/tambah-sppd')->assertStatus(403);
        $this->post('/surat')->assertStatus(403);
        $this->get('/surat/1/edit')->assertStatus(403);
        $this->put('/surat/1')->assertStatus(403);
        $this->post('/surat/1/upload-file')->assertStatus(403);
        $this->post('/surat/1/retry-drive')->assertStatus(403);
        $this->post('/surat/1/hubungkan-spt')->assertStatus(403);
        $this->delete('/surat/1')->assertStatus(403);

        // Surat Masuk
        $this->get('/surat/masuk/create')->assertStatus(403);
        $this->post('/surat/masuk')->assertStatus(403);
        $this->get('/surat/masuk/1/edit')->assertStatus(403);
        $this->put('/surat/masuk/1')->assertStatus(403);
        $this->delete('/surat/masuk/1')->assertStatus(403);

        // Surat Telaah
        $this->get('/surat/telaah/create')->assertStatus(403);
        $this->post('/surat/telaah')->assertStatus(403);
        $this->delete('/surat/telaah/1')->assertStatus(403);

        // Surat SK
        $this->get('/surat/sk/create')->assertStatus(403);
        $this->post('/surat/sk')->assertStatus(403);
        $this->delete('/surat/sk/1')->assertStatus(403);
    }

    /**
     * Test 4: Admin Master DITOLAK (403) saat mengakses route mutasi Kenaikan Pangkat & Berkala.
     */
    public function test_admin_master_blocked_from_kenpa_mutation_routes()
    {
        $this->actingAs($this->adminMaster);

        $this->post('/kenaikan-pangkat/store')->assertStatus(403);
        $this->get('/kenaikan-pangkat/1/edit')->assertStatus(403);
        $this->put('/kenaikan-pangkat/1')->assertStatus(403);
        $this->put('/kenaikan-pangkat/dokumen/1/verifikasi')->assertStatus(403);
        $this->delete('/kenaikan-pangkat/1')->assertStatus(403);
    }

    /**
     * Test 5: Kasubag memiliki akses normal (bukan 403) ke route pembuatan surat & kenpa.
     */
    public function test_kasubag_can_access_create_routes()
    {
        $this->actingAs($this->kasubag);

        $this->get('/surat/create')->assertStatus(200);
        $this->get('/surat/masuk/create')->assertStatus(200);
        $this->get('/surat/telaah/create')->assertStatus(200);
        $this->get('/surat/sk/create')->assertStatus(200);
    }
}
