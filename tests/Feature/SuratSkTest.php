<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SuratSk;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuratSkTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasubag;
    protected User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@kominfo.bonebolango.id')->first()
            ?? User::where('role_id', 1)->first();
        $this->kasubag = User::where('email', 'kasubag@kominfo.bonebolango.go.id')->first()
            ?? User::where('role_id', 2)->first();
        $this->pegawai = User::where('role_id', 3)->first();
    }

    public function test_kasubag_and_admin_can_access_surat_sk_index(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('surat.sk'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Surat Keputusan (SK)');

        $responseKasubag = $this->actingAs($this->kasubag)->get(route('surat.sk'));
        $responseKasubag->assertStatus(200);
        $responseKasubag->assertSee('Surat Keputusan (SK)');
    }

    public function test_pegawai_cannot_access_surat_sk(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('surat.sk'));
        $response->assertStatus(403);
    }

    public function test_create_and_store_surat_sk(): void
    {
        $responseCreate = $this->actingAs($this->kasubag)->get(route('surat.sk.create'));
        $responseCreate->assertStatus(200);

        $responseStore = $this->actingAs($this->kasubag)->post(route('surat.sk.store'), [
            'mode_nomor' => 'manual',
            'nomor_manual' => '800/KOMINFO-BB/SK/001/IX/2026',
            'tanggal_sk' => '2026-09-19',
            'tentang' => 'Penetapan Tim Pengelola Keamanan Informasi Kabupaten Bone Bolango',
            'keterangan' => 'SK Kepala Dinas',
        ]);

        $responseStore->assertRedirect(route('surat.sk'));

        $this->assertDatabaseHas('surat_sks', [
            'nomor_sk' => '800/KOMINFO-BB/SK/001/IX/2026',
            'tentang' => 'Penetapan Tim Pengelola Keamanan Informasi Kabupaten Bone Bolango',
        ]);
    }

    public function test_export_surat_sk_pdf(): void
    {
        SuratSk::create([
            'nomor_sk' => '800/KOMINFO-BB/SK/002/IX/2026',
            'tanggal_sk' => '2026-09-19',
            'tentang' => 'Penetapan Standar Operasional Prosedur Layanan TIK',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat.sk.exportPdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
