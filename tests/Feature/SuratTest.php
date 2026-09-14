<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Models\JenisSurat;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuratTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_surat_index_can_be_accessed_by_admin(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->get('/surat');

        $response->assertStatus(200);
        $response->assertViewHasAll(['surats', 'totalSpt', 'totalSppd', 'totalBulanIni', 'allPegawais', 'nextSppdCounter']);
    }

    public function test_surat_index_can_be_accessed_by_kasubag(): void
    {
        $kasubag = User::where('email', 'kasubag@kominfo.bonebolango.go.id')->first();
        $response = $this->actingAs($kasubag)->get('/surat');

        $response->assertStatus(200);
    }

    public function test_surat_index_with_filters(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();

        // Test with SPT filter
        $responseSpt = $this->actingAs($admin)->get('/surat?filter_jenis=SPT');
        $responseSpt->assertStatus(200);

        // Test with SPPD filter
        $responseSppd = $this->actingAs($admin)->get('/surat?filter_jenis=SPPD');
        $responseSppd->assertStatus(200);

        // Test with Search filter
        $responseSearch = $this->actingAs($admin)->get('/surat?search=Dinas');
        $responseSearch->assertStatus(200);
    }

    public function test_surat_rekap_can_be_accessed(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->get('/surat/rekap');

        $response->assertStatus(200);
    }

    public function test_next_sppd_counter_api(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->get('/surat/api/next-sppd-counter');

        $response->assertStatus(200);
        $response->assertJsonStructure(['next_counter']);
    }

    public function test_create_step_2_only_includes_structural_pegawai(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->get('/surat/create/step-2');

        $response->assertStatus(200);
        $response->assertViewHas('pegawais');

        $pegawais = $response->viewData('pegawais');
        
        // Memastikan Kepala Dinas ada di daftar (urutan pertama)
        $this->assertEquals('Drs. H. Syamsuddin, M.Si', $pegawais->first()->nama);
        $this->assertEquals('Kepala Dinas Komunikasi dan Informatika', $pegawais->first()->jabatan);

        // Memastikan data generik / placeholder TIDAK ada di daftar
        $excludedNames = ['Admin Master', 'Admin Kasubag', 'Pegawai', 'Bendahara Barang', 'Pegawai Biasa'];
        foreach ($excludedNames as $excluded) {
            $this->assertFalse($pegawais->contains('nama', $excluded), "Daftar pegawai tidak boleh mengandung: {$excluded}");
        }
    }

    public function test_store_pegawai_p3k_api(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->postJson('/surat/api/pegawai-p3k', [
            'nama' => 'Tenaga Honorer Test',
            'nip' => '19950101P3K01',
            'jabatan' => 'Tenaga IT',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pegawai' => [
                'nama' => 'Tenaga Honorer Test',
                'nip' => '19950101P3K01',
                'kategori_pegawai' => 'P3K',
            ]
        ]);

        $this->assertDatabaseHas('pegawais', [
            'nama' => 'Tenaga Honorer Test',
            'kategori_pegawai' => 'P3K',
        ]);
    }

    public function test_export_surat_rekap_pdf(): void
    {
        $admin = User::where('email', 'admin@kominfo.bonebolango.id')->first();
        $response = $this->actingAs($admin)->get('/surat/rekap/export-pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
