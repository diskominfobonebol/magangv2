<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratTelaah;
use App\Models\SuratSk;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LinkGoogleDriveTest extends TestCase
{
    use RefreshDatabase;

    protected $kasubag;
    protected $pegawai;
    protected $pegawaiUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'nama' => 'Admin Master']);
        Role::create(['id' => 2, 'nama' => 'Admin Kasubag']);
        Role::create(['id' => 3, 'nama' => 'Pegawai']);

        JenisSurat::create(['id' => 1, 'nama_jenis' => 'Surat Perintah Perjalanan Dinas', 'kode' => 'SPPD']);
        JenisSurat::create(['id' => 2, 'nama_jenis' => 'Surat Perintah Tugas', 'kode' => 'SPT']);

        $this->kasubag = User::create([
            'id' => 2,
            'name' => 'Admin Kasubag',
            'email' => 'kasubag@sinosip.test',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->pegawaiUser = User::create([
            'id' => 3,
            'name' => 'Pegawai Biasa',
            'email' => 'pegawai@sinosip.test',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->pegawai = Pegawai::create([
            'id' => 1,
            'nama' => 'Budi Santoso, S.Kom',
            'nip' => '198501012010011001',
            'no_wa' => '081234567890',
            'pangkat_golongan' => 'Penata Tk.I (III/d)',
            'jabatan' => 'Pranata Komputer Ahli Muda',
            'kategori_pegawai' => 'ASN',
        ]);
    }

    public function test_spt_stores_and_displays_google_drive_link()
    {
        $driveUrl = 'https://drive.google.com/file/d/spt-test-12345/view';
        $manualNomor = '555/KOMINFO-BB/SPT-DD/888/IX/' . date('Y');

        $payload = [
            'jenis_surat_id' => 2,
            'mode_nomor' => 'manual',
            'nomor_surat_manual' => $manualNomor,
            'tgl_surat' => date('Y-m-d'),
            'tujuan' => 'Kantor Gubernur Kepulauan Bangka Belitung',
            'uraian' => 'Pengujian tautan Google Drive pada SPT',
            'has_sppd' => 0,
            'pegawai_id' => [$this->pegawai->id],
            'nomor_sppd' => ['-'],
            'link_google_drive' => $driveUrl,
        ];

        $response = $this->actingAs($this->kasubag)->post('/surat', $payload);
        $response->assertRedirect(route('surat.index'));

        $spt = Surat::where('nomor_surat', $manualNomor)->first();
        $this->assertNotNull($spt);
        $this->assertEquals($driveUrl, $spt->link_google_drive);

        // Check show page
        $showResponse = $this->actingAs($this->kasubag)->get('/surat/' . $spt->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee($driveUrl);
    }

    public function test_surat_telaah_stores_and_displays_google_drive_link()
    {
        $driveUrl = 'https://drive.google.com/file/d/telaah-test-67890/view';
        $manualNomor = '000.1.5/TELAAH-TEST/' . time();

        $payload = [
            'mode_nomor' => 'manual',
            'nomor_manual' => $manualNomor,
            'tanggal_telaah' => date('Y-m-d'),
            'uraian' => 'Telaahan staf pengujian Google Drive',
            'tujuan' => 'Dinas Pendidikan Provinsi',
            'keterangan' => 'Catatan telaah',
            'pegawai_id' => [$this->pegawai->id],
            'link_google_drive' => $driveUrl,
        ];

        $response = $this->actingAs($this->kasubag)->post('/surat/telaah', $payload);
        $response->assertRedirect(route('surat.telaah'));

        $telaah = SuratTelaah::where('nomor_telaah', $manualNomor)->first();
        $this->assertNotNull($telaah);
        $this->assertEquals($driveUrl, $telaah->link_google_drive);

        // Check index & show JSON
        $indexResponse = $this->actingAs($this->kasubag)->get('/surat/telaah');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($driveUrl);

        $jsonResponse = $this->actingAs($this->kasubag)->getJson('/surat/telaah/' . $telaah->id);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonFragment(['link_google_drive' => $driveUrl]);
    }

    public function test_surat_sk_stores_and_displays_google_drive_link()
    {
        $driveUrl = 'https://drive.google.com/file/d/sk-test-99999/view';
        $manualNomor = '555/KEP/SK-TEST/' . time();

        $payload = [
            'mode_nomor' => 'manual',
            'nomor_manual' => $manualNomor,
            'tanggal_sk' => date('Y-m-d'),
            'tentang' => 'Penetapan Pengujian Link Google Drive',
            'keterangan' => 'Catatan SK',
            'link_google_drive' => $driveUrl,
        ];

        $response = $this->actingAs($this->kasubag)->post('/surat/sk', $payload);
        $response->assertRedirect(route('surat.sk'));

        $sk = SuratSk::where('nomor_sk', $manualNomor)->first();
        $this->assertNotNull($sk);
        $this->assertEquals($driveUrl, $sk->link_google_drive);

        // Check index & show JSON
        $indexResponse = $this->actingAs($this->kasubag)->get('/surat/sk');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($driveUrl);

        $jsonResponse = $this->actingAs($this->kasubag)->getJson('/surat/sk/' . $sk->id);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonFragment(['link_google_drive' => $driveUrl]);
    }

    public function test_invalid_google_drive_links_are_rejected()
    {
        $invalidPayload = [
            'mode_nomor' => 'manual',
            'nomor_manual' => '555/KEP/INVALID/' . time(),
            'tanggal_sk' => date('Y-m-d'),
            'tentang' => 'Test Invalid',
            'link_google_drive' => 'https://dropbox.com/file/123',
        ];

        $response = $this->actingAs($this->kasubag)->post('/surat/sk', $invalidPayload);
        $response->assertSessionHasErrors(['link_google_drive']);
    }
}
