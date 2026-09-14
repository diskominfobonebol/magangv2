<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\SuratMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SuratMasukTest extends TestCase
{
    use RefreshDatabase;

    protected $master;
    protected $kasubag;
    protected $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'nama' => 'Admin Master']);
        Role::create(['id' => 2, 'nama' => 'Admin Kasubag']);
        Role::create(['id' => 3, 'nama' => 'Pegawai']);

        $this->master = User::create([
            'id' => 1,
            'name' => 'Admin Master',
            'email' => 'master@sinosip.test',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'is_active' => true,
        ]);

        $this->kasubag = User::create([
            'id' => 2,
            'name' => 'Admin Kasubag',
            'email' => 'kasubag@sinosip.test',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'is_active' => true,
        ]);

        $this->pegawai = User::create([
            'id' => 3,
            'name' => 'Pegawai Biasa',
            'email' => 'pegawai@sinosip.test',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);
    }

    public function test_kasubag_can_view_surat_masuk_index()
    {
        $response = $this->actingAs($this->kasubag)->get('/surat/masuk');

        $response->assertStatus(200);
        $response->assertSee('Surat Masuk');
        $response->assertSee('Tambah Surat Masuk');
    }

    public function test_master_can_view_surat_masuk_index_in_readonly_mode()
    {
        $response = $this->actingAs($this->master)->get('/surat/masuk');

        $response->assertStatus(200);
        $response->assertSee('Surat Masuk');
        // Tombol Tambah Surat Masuk tidak boleh muncul untuk Admin Master
        $response->assertDontSee('Tambah Surat Masuk');
    }

    public function test_pegawai_cannot_access_surat_masuk()
    {
        $response = $this->actingAs($this->pegawai)->get('/surat/masuk');
        $response->assertStatus(403);
    }

    public function test_kasubag_can_create_surat_masuk()
    {
        $response = $this->actingAs($this->kasubag)->post('/surat/masuk', [
            'nomor_surat' => '100/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Kementerian Keuangan RI',
            'uraian' => 'Sosialisasi Anggaran Daerah 2026',
            'keterangan' => 'Catatan internal penting',
            'link_google_drive' => 'https://drive.google.com/file/d/test100/view',
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseHas('surat_masuk', [
            'nomor_surat' => '100/TEST/2026',
            'asal_surat' => 'Kementerian Keuangan RI',
        ]);
    }

    public function test_master_cannot_create_surat_masuk()
    {
        $response = $this->actingAs($this->master)->post('/surat/masuk', [
            'nomor_surat' => '200/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Badan Kepegawaian Negara',
            'uraian' => 'Verifikasi Data ASN',
            'link_google_drive' => 'https://drive.google.com/file/d/test200/view',
        ]);

        $response->assertStatus(403);
    }

    public function test_kasubag_can_update_surat_masuk()
    {
        $suratMasuk = SuratMasuk::create([
            'nomor_surat' => '300/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Pendidikan',
            'uraian' => 'Undangan Workshop',
            'link_google_drive' => 'https://drive.google.com/file/d/test300/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->kasubag)->put('/surat/masuk/' . $suratMasuk->id, [
            'nomor_surat' => '300/TEST-REVISI/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Pendidikan & Kebudayaan',
            'uraian' => 'Undangan Workshop Revisi',
            'keterangan' => 'Sudah ditindaklanjuti',
            'link_google_drive' => 'https://drive.google.com/file/d/test300-rev/view',
        ]);

        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseHas('surat_masuk', [
            'id' => $suratMasuk->id,
            'nomor_surat' => '300/TEST-REVISI/2026',
            'asal_surat' => 'Dinas Pendidikan & Kebudayaan',
        ]);
    }

    public function test_master_cannot_update_surat_masuk()
    {
        $suratMasuk = SuratMasuk::create([
            'nomor_surat' => '400/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Kesehatan',
            'uraian' => 'Rakor Kesehatan',
            'link_google_drive' => 'https://drive.google.com/file/d/test400/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->master)->put('/surat/masuk/' . $suratMasuk->id, [
            'nomor_surat' => '400/TEST-HACK/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Kesehatan',
            'uraian' => 'Rakor Kesehatan',
            'link_google_drive' => 'https://drive.google.com/file/d/test400/view',
        ]);

        $response->assertStatus(403);
    }

    public function test_kasubag_can_delete_surat_masuk()
    {
        $suratMasuk = SuratMasuk::create([
            'nomor_surat' => '500/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Sosial',
            'uraian' => 'Penyaluran Bantuan',
            'link_google_drive' => 'https://drive.google.com/file/d/test500/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->kasubag)->delete('/surat/masuk/' . $suratMasuk->id);
        $response->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseMissing('surat_masuk', [
            'id' => $suratMasuk->id,
        ]);
    }

    public function test_master_cannot_delete_surat_masuk()
    {
        $suratMasuk = SuratMasuk::create([
            'nomor_surat' => '600/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Perhubungan',
            'uraian' => 'Surat Uji Kendaraan',
            'link_google_drive' => 'https://drive.google.com/file/d/test600/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->master)->delete('/surat/masuk/' . $suratMasuk->id);
        $response->assertStatus(403);
        $this->assertDatabaseHas('surat_masuk', [
            'id' => $suratMasuk->id,
        ]);
    }

    public function test_create_form_provides_asal_surat_suggestions()
    {
        SuratMasuk::create([
            'nomor_surat' => '111/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Bappeda Bone Bolango',
            'uraian' => 'Rakor Pembangunan',
            'link_google_drive' => 'https://drive.google.com/file/d/test111/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->kasubag)->get('/surat/masuk/create');
        $response->assertStatus(200);
        $response->assertViewHas('daftarAsalSurat');
        $response->assertSee('Bappeda Bone Bolango');
    }

    public function test_kasubag_can_export_surat_masuk_pdf()
    {
        SuratMasuk::create([
            'nomor_surat' => '777/TEST/2026',
            'tanggal_surat' => '2026-09-14',
            'asal_surat' => 'Dinas Lingkungan Hidup',
            'uraian' => 'Uji Emisi Kendaraan',
            'link_google_drive' => 'https://drive.google.com/file/d/test777/view',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->kasubag)->get('/surat/masuk/export-pdf');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_master_can_export_surat_masuk_pdf()
    {
        $response = $this->actingAs($this->master)->get('/surat/masuk/export-pdf');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pegawai_cannot_export_surat_masuk_pdf()
    {
        $response = $this->actingAs($this->pegawai)->get('/surat/masuk/export-pdf');
        $response->assertStatus(403);
    }

    public function test_surat_keluar_still_accessible()
    {
        $response = $this->actingAs($this->kasubag)->get('/surat');
        $response->assertStatus(200);
        $response->assertSee('Surat Keluar');
        $response->assertSee('Surat Masuk');
    }

    public function test_quick_filter_month_filters_records_correctly()
    {
        // Data bulan ini
        SuratMasuk::create([
            'nomor_surat' => 'BULAN-INI-01',
            'tanggal_surat' => now()->startOfMonth()->addDays(2)->format('Y-m-d'),
            'asal_surat' => 'Instansi Bulan Ini',
            'uraian' => 'Surat masuk periode bulan ini',
            'link_google_drive' => 'https://drive.google.com/file/d/test-month/view',
            'created_by' => $this->kasubag->id,
        ]);

        // Data bulan lalu
        SuratMasuk::create([
            'nomor_surat' => 'BULAN-LALU-01',
            'tanggal_surat' => now()->subMonths(2)->format('Y-m-d'),
            'asal_surat' => 'Instansi Bulan Lalu',
            'uraian' => 'Surat masuk periode bulan lalu',
            'link_google_drive' => 'https://drive.google.com/file/d/test-last-month/view',
            'created_by' => $this->kasubag->id,
        ]);

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->kasubag)->get("/surat/masuk?start_date={$startDate}&end_date={$endDate}");
        $response->assertStatus(200);
        $response->assertSee('BULAN-INI-01');
        $response->assertDontSee('BULAN-LALU-01');
    }

    public function test_quick_filter_year_filters_records_correctly()
    {
        // Data tahun ini
        SuratMasuk::create([
            'nomor_surat' => 'TAHUN-INI-01',
            'tanggal_surat' => now()->format('Y-m-d'),
            'asal_surat' => 'Instansi Tahun Ini',
            'uraian' => 'Surat masuk tahun ini',
            'link_google_drive' => 'https://drive.google.com/file/d/test-year/view',
            'created_by' => $this->kasubag->id,
        ]);

        // Data tahun lalu
        SuratMasuk::create([
            'nomor_surat' => 'TAHUN-LALU-01',
            'tanggal_surat' => now()->subYears(2)->format('Y-m-d'),
            'asal_surat' => 'Instansi Tahun Lalu',
            'uraian' => 'Surat masuk tahun lalu',
            'link_google_drive' => 'https://drive.google.com/file/d/test-last-year/view',
            'created_by' => $this->kasubag->id,
        ]);

        $currentYear = now()->year;
        $response = $this->actingAs($this->kasubag)->get("/surat/masuk?year={$currentYear}");
        $response->assertStatus(200);
        $response->assertSee('TAHUN-INI-01');
        $response->assertDontSee('TAHUN-LALU-01');
    }

    public function test_quick_filter_combined_with_search_filters_correctly()
    {
        SuratMasuk::create([
            'nomor_surat' => 'MATCH-01',
            'tanggal_surat' => now()->format('Y-m-d'),
            'asal_surat' => 'Kementerian Kominfo',
            'uraian' => 'Surat sinkronisasi data',
            'link_google_drive' => 'https://drive.google.com/file/d/test-match/view',
            'created_by' => $this->kasubag->id,
        ]);

        SuratMasuk::create([
            'nomor_surat' => 'OTHER-01',
            'tanggal_surat' => now()->format('Y-m-d'),
            'asal_surat' => 'Kementerian Perhubungan',
            'uraian' => 'Surat Dishub',
            'link_google_drive' => 'https://drive.google.com/file/d/test-other/view',
            'created_by' => $this->kasubag->id,
        ]);

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->kasubag)->get("/surat/masuk?start_date={$startDate}&end_date={$endDate}&search=Kominfo");
        $response->assertStatus(200);
        $response->assertSee('MATCH-01');
        $response->assertDontSee('OTHER-01');
    }

    public function test_export_pdf_respects_quick_filters()
    {
        SuratMasuk::create([
            'nomor_surat' => 'PDF-MONTH-01',
            'tanggal_surat' => now()->format('Y-m-d'),
            'asal_surat' => 'Dinas Pendidikan',
            'uraian' => 'Surat Masuk PDF Filtered',
            'link_google_drive' => 'https://drive.google.com/file/d/test-pdf-month/view',
            'created_by' => $this->kasubag->id,
        ]);

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->kasubag)->get("/surat/masuk/export-pdf?start_date={$startDate}&end_date={$endDate}");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
