<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\SuratMasuk;
use App\Models\Pegawai;
use App\Models\JenisSurat;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserEditTest extends TestCase
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
            'email' => '198501012010011001',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
        ]);

        Pegawai::create([
            'user_id' => $this->pegawai->id,
            'nama' => $this->pegawai->name,
            'nip' => $this->pegawai->email,
            'jabatan' => 'Staff / Pegawai',
            'kategori_pegawai' => 'ASN',
            'pangkat_golongan' => 'Penata Muda / IIIa',
            'no_wa' => '081234567890',
        ]);
    }

    /**
     * Verifikasi 1: Edit nama dan email user biasa, pastikan tersimpan dan tabel ter-update.
     */
    public function test_admin_master_can_edit_regular_user_name_and_email(): void
    {
        $response = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Nama Pegawai Baru',
            'identity' => '198501012010019999',
            'role_id' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->pegawai->refresh();
        $this->assertEquals('Nama Pegawai Baru', $this->pegawai->name);
        $this->assertEquals('198501012010019999', $this->pegawai->email);

        // Pastikan relasi tabel pegawais juga tersinkronisasi
        $this->assertEquals('Nama Pegawai Baru', $this->pegawai->pegawai->nama);
        $this->assertEquals('198501012010019999', $this->pegawai->pegawai->nip);
    }

    /**
     * Verifikasi 2: Edit dengan email yang sudah dipakai user lain, pastikan ditolak dengan pesan validasi yang jelas.
     */
    public function test_edit_with_duplicate_email_is_rejected_with_validation_error(): void
    {
        $response = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Pegawai Coba Ganti Email',
            'identity' => 'kasubag@test.com', // Email milik $this->kasubag
            'role_id' => 3,
        ]);

        $response->assertSessionHasErrors(['identity']);
        $this->pegawai->refresh();
        $this->assertEquals('198501012010011001', $this->pegawai->email);
    }

    /**
     * Verifikasi 3: Ubah dua user berbeda menjadi Kasubag, pastikan keduanya memegang role Kasubag bersamaan dan tidak saling menimpa, serta keduanya mendapat akses fitur Kasubag.
     */
    public function test_multiple_users_can_hold_kasubag_role_simultaneously(): void
    {
        $pegawai2 = User::create([
            'name' => 'Pegawai Kedua',
            'email' => '199001012015011002',
            'password' => Hash::make('password123'),
            'role_id' => 3,
            'must_change_password' => false,
        ]);

        // Ubah pegawai pertama jadi Kasubag
        $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => $this->pegawai->name,
            'identity' => $this->pegawai->email,
            'role_id' => 2,
        ]);

        // Ubah pegawai kedua jadi Kasubag
        $this->actingAs($this->adminMaster)->put(route('admin.users.update', $pegawai2->id), [
            'name' => $pegawai2->name,
            'identity' => $pegawai2->email,
            'role_id' => 2,
        ]);

        $this->pegawai->refresh();
        $pegawai2->refresh();
        $this->kasubag->refresh();

        // Ketiganya sekarang memegang role Kasubag bersamaan
        $this->assertEquals(2, $this->pegawai->role_id);
        $this->assertEquals(2, $pegawai2->role_id);
        $this->assertEquals(2, $this->kasubag->role_id);

        // Pastikan keduanya (pegawai 1 & pegawai 2) mendapat akses fitur mutasi Kasubag
        $this->actingAs($this->pegawai)->get(route('surat.create'))->assertStatus(200);
        $this->actingAs($pegawai2)->get(route('surat.create'))->assertStatus(200);
        $this->actingAs($this->kasubag)->get(route('surat.create'))->assertStatus(200);
    }

    /**
     * Verifikasi 4: Ubah role seorang Kasubag menjadi role lain, pastikan dia tidak lagi bisa mengakses fitur Kasubag, sementara Kasubag lain tidak terpengaruh.
     */
    public function test_demoted_kasubag_loses_access_while_other_kasubags_remain_unaffected(): void
    {
        // Buat kasubag kedua terlebih dahulu
        $kasubag2 = User::create([
            'name' => 'Kasubag Kedua',
            'email' => 'kasubag2@test.com',
            'password' => Hash::make('password123'),
            'role_id' => 2,
            'must_change_password' => false,
        ]);

        // Turunkan Kasubag pertama menjadi Pegawai (Role 3)
        $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->kasubag->id), [
            'name' => $this->kasubag->name,
            'identity' => $this->kasubag->email,
            'role_id' => 3,
        ]);

        $this->kasubag->refresh();
        $kasubag2->refresh();

        $this->assertEquals(3, $this->kasubag->role_id);
        $this->assertEquals(2, $kasubag2->role_id);

        // Kasubag 1 sekarang ditolak saat mengakses fitur Kasubag (403)
        $this->actingAs($this->kasubag)->get(route('surat.create'))->assertStatus(403);

        // Kasubag 2 tetap memiliki akses normal
        $this->actingAs($kasubag2)->get(route('surat.create'))->assertStatus(200);
    }

    /**
     * Verifikasi 5: Pastikan surat/disposisi lama tidak berubah kepemilikannya.
     */
    public function test_historical_records_ownership_remains_unchanged_when_role_changes(): void
    {
        // Buat catatan surat masuk oleh $this->kasubag
        $suratMasuk = SuratMasuk::create([
            'nomor_surat' => '001/TEST/2026',
            'tanggal_surat' => now()->toDateString(),
            'asal_surat' => 'Instansi Luar',
            'uraian' => 'Arsip Bersejarah',
            'keterangan' => 'Disposisi awal',
            'link_google_drive' => 'https://drive.google.com/test',
            'created_by' => $this->kasubag->id,
        ]);

        $this->assertEquals($this->kasubag->id, $suratMasuk->created_by);

        // Ubah role $this->kasubag menjadi Pegawai
        $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->kasubag->id), [
            'name' => 'Kasubag Berubah Jabatan',
            'identity' => $this->kasubag->email,
            'role_id' => 3,
        ]);

        $suratMasuk->refresh();
        // Rekor lama tetap merujuk ke ID user aslinya
        $this->assertEquals($this->kasubag->id, $suratMasuk->created_by);
        $this->assertEquals('Kasubag Berubah Jabatan', $suratMasuk->creator->name);
    }

    /**
     * Verifikasi 6: Coba turunkan role Admin Master sendiri, pastikan ditolak.
     */
    public function test_admin_master_cannot_demote_themselves(): void
    {
        $response = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->adminMaster->id), [
            'name' => 'Admin Master Ingin Jadi Pegawai',
            'identity' => $this->adminMaster->email,
            'role_id' => 3,
        ]);

        $response->assertSessionHas('error');
        $this->adminMaster->refresh();
        $this->assertEquals(1, $this->adminMaster->role_id);
    }

    /**
     * Pengaman tambahan: Sistem harus selalu menyisakan minimal 1 Admin Master
     */
    public function test_system_must_retain_at_least_one_admin_master(): void
    {
        // Buat admin master kedua
        $adminMaster2 = User::create([
            'name' => 'Admin Master 2',
            'email' => 'admin2@test.com',
            'password' => Hash::make('password123'),
            'role_id' => 1,
            'must_change_password' => false,
        ]);

        // Admin Master 1 menurunkan role Admin Master 2 -> Diperbolehkan karena masih ada Admin Master 1
        $response = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $adminMaster2->id), [
            'name' => $adminMaster2->name,
            'identity' => $adminMaster2->email,
            'role_id' => 2,
        ]);

        $response->assertSessionHas('success');
        $adminMaster2->refresh();
        $this->assertEquals(2, $adminMaster2->role_id);

        // Sekarang tersisa 1 Admin Master, coba turunkan akun tersisa tersebut (walau disimulasikan dari session)
        // Pengaman 1 (self-demote) dan Pengaman 2 (sole master) keduanya melindungi
        $response2 = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->adminMaster->id), [
            'name' => $this->adminMaster->name,
            'identity' => $this->adminMaster->email,
            'role_id' => 2,
        ]);

        $response2->assertSessionHas('error');
        $this->adminMaster->refresh();
        $this->assertEquals(1, $this->adminMaster->role_id);
    }

    /**
     * Verifikasi 7: Login sebagai role selain Admin Master dan coba akses route edit lewat URL langsung, pastikan ditolak.
     */
    public function test_non_admin_master_cannot_access_update_user_route(): void
    {
        // 1. Kasubag mencoba akses route edit
        $responseKasubag = $this->actingAs($this->kasubag)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Hacker Kasubag',
            'identity' => 'hacked@test.com',
            'role_id' => 1,
        ]);
        $responseKasubag->assertStatus(403);

        // 2. Pegawai mencoba akses route edit
        $responsePegawai = $this->actingAs($this->pegawai)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Hacker Pegawai',
            'identity' => 'hacked2@test.com',
            'role_id' => 1,
        ]);
        $responsePegawai->assertStatus(403);

        // 3. Guest (tanpa login) mencoba akses route edit
        auth()->logout();
        $responseGuest = $this->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Hacker Guest',
            'identity' => 'hacked3@test.com',
            'role_id' => 1,
        ]);
        $responseGuest->assertRedirect(route('login'));
    }

    /**
     * Verifikasi 8 & Validasi Role: Role tidak valid ditolak.
     */
    public function test_invalid_role_id_is_rejected(): void
    {
        $response = $this->actingAs($this->adminMaster)->put(route('admin.users.update', $this->pegawai->id), [
            'name' => 'Pegawai Test',
            'identity' => $this->pegawai->email,
            'role_id' => 9999, // ID tidak ada di tabel roles
        ]);

        $response->assertSessionHasErrors(['role_id']);
    }
}
