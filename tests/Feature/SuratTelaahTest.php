<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Surat;
use App\Models\Pegawai;
use App\Models\SuratTelaah;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuratTelaahTest extends TestCase
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

    public function test_kasubag_and_admin_can_access_surat_telaah_index(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('surat.telaah'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Surat Telaah');

        $responseKasubag = $this->actingAs($this->kasubag)->get(route('surat.telaah'));
        $responseKasubag->assertStatus(200);
        $responseKasubag->assertSee('Surat Telaah');
    }

    public function test_pegawai_cannot_access_surat_telaah(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('surat.telaah'));
        $response->assertStatus(403);
    }

    public function test_create_and_store_surat_telaah(): void
    {
        $responseCreate = $this->actingAs($this->kasubag)->get(route('surat.telaah.create'));
        $responseCreate->assertStatus(200);

        $pegawaiSample = Pegawai::whereNotIn('id', [1, 2, 3, 4])->first();

        $responseStore = $this->actingAs($this->kasubag)->post(route('surat.telaah.store'), [
            'mode_nomor' => 'manual',
            'nomor_manual' => '001/TLH/KOMINFO/IX/2026',
            'tanggal_telaah' => '2026-09-19',
            'uraian' => 'Telaah teknis implementasi sistem informasi terintegrasi.',
            'tujuan' => 'Dinas Komunikasi dan Informatika Provinsi Gorontalo',
            'keterangan' => 'Telaah staf kepegawaian',
            'pegawai_id' => $pegawaiSample ? [$pegawaiSample->id] : [],
        ]);

        $responseStore->assertRedirect(route('surat.telaah'));

        $this->assertDatabaseHas('surat_telaahs', [
            'nomor_telaah' => '001/TLH/KOMINFO/IX/2026',
            'tujuan' => 'Dinas Komunikasi dan Informatika Provinsi Gorontalo',
        ]);
    }

    public function test_export_surat_telaah_pdf(): void
    {
        SuratTelaah::create([
            'nomor_telaah' => '002/TLH/KOMINFO/IX/2026',
            'tanggal_telaah' => '2026-09-19',
            'uraian' => 'Telaah kebutuhan server cloud',
            'tujuan' => 'Bappeda Bone Bolango',
            'created_by' => $this->kasubag->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('surat.telaah.exportPdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
