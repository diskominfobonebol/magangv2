<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratTelaahController;
use App\Http\Controllers\SuratSkController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenaikanPangkatController;
use App\Http\Controllers\PegawaiDashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MahasiswaController;
use App\Models\Setting;
use App\Models\KenpaBerkala;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// ==========================================
// RUTE PUBLIK & LANDING PAGE
// ==========================================
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('landing');

Route::get('/magang', function () {
    return view('landing.magang');
})->name('landing.magang');

Route::get('/home', fn() => redirect()->route('dashboard'));
Route::get('/admin/dashboard', fn() => redirect()->route('dashboard'));

// Rute Publik Detail Aset & QR Code (Dapat diakses saat QR Code di-scan)
Route::get('/aset/{id}/qr-image', [AdminController::class, 'qrImage'])->name('aset.qr_image')->where('id', '.*');
Route::get('/aset/{id}', [AdminController::class, 'publicDetail'])->name('aset.public_detail')->where('id', '^(?!export-pdf$).*');
Route::get('/api/aset/{id}', [AdminController::class, 'apiDetail'])->name('api.aset.detail')->where('id', '.*');

// ==========================================
// RUTE AUTENTIKASI TAMU (GUEST)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login/pegawai', [AuthController::class, 'showLoginPegawai'])->name('login.pegawai');
    Route::get('/login/mahasiswa', [AuthController::class, 'showLoginMahasiswa'])->name('login.mahasiswa');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Logout Route
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// RUTE PENGGUNA TERAUTENTIKASI (AUTH + FORCE PASSWORD RESET)
// ==========================================
Route::middleware(['auth', 'force.password.reset'])->group(function () {

    // Rute Wajib Ganti Password (Semua Role saat must_change_password = true)
    Route::get('/reset-password-wajib', [AuthController::class, 'showForceResetPassword'])->name('password.force_reset');
    Route::post('/reset-password-wajib', [AuthController::class, 'forceResetPassword'])->name('password.force_reset.update');

    // ------------------------------------------------------------------
    // RUTE KHUSUS ADMIN MASTER (ROLE 1)
    // ------------------------------------------------------------------
    Route::middleware('role:1')->group(function () {
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/admin/users/index', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/user', [UserController::class, 'index'])->name('admin.user');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::post('/admin/user', [UserController::class, 'store'])->name('admin.user.store');
        Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::put('/admin/user/{id}', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle_status');
        Route::post('/admin/users/{id}/toggle-status-alt', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::post('/admin/user/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.user.toggle_status');
        Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::post('/admin/user/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.user.reset_password');
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::delete('/admin/user/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

        // Kelola Magang
        Route::get('/admin/magang', [AdminController::class, 'magang'])->name('admin.magang');
        Route::post('/admin/magang/{id}/status', [AdminController::class, 'updateStatusMagang'])->name('admin.magang.update');
    });

    // ------------------------------------------------------------------
    // RUTE SURAT MENYURAT & KEPEGAWAIAN (ROLE 1 & ROLE 2)
    // ------------------------------------------------------------------
    Route::middleware('role:1,2')->group(function () {
        Route::get('/dashboard/master', function () {
            $totalPegawai = \App\Models\Pegawai::count();
            $totalSurat = \App\Models\Surat::count();
            $totalSuratMasuk = \App\Models\SuratMasuk::count();
            $totalAset = \App\Models\AsetPeralatanMesin::count();
            $totalMagang = \App\Models\PendaftaranMagang::count();
            return view('dashboard.master', compact('totalPegawai', 'totalSurat', 'totalSuratMasuk', 'totalAset', 'totalMagang'));
        })->name('dashboard.master');

        // Redirect /surat ke /surat/keluar
        Route::get('/surat', function () {
            return redirect()->route('surat.keluar');
        })->name('surat.index');

        // Sub-modul 1: Surat Keluar (SPT & SPPD) - Read Only & Export/Download/Print
        Route::get('/surat/keluar', [SuratController::class, 'index'])->name('surat.keluar');
        Route::get('/surat/rekap', [SuratController::class, 'rekapIndex'])->name('surat.rekap');
        Route::get('/surat/rekap/export-pdf', [SuratController::class, 'exportRekapPdf'])->name('surat.rekap.exportPdf');

        // Sub-modul 2: Surat Masuk - Read Only & Export
        Route::get('/surat/masuk', [SuratMasukController::class, 'index'])->name('surat.masuk');
        Route::get('/surat/masuk-legacy', [SuratMasukController::class, 'index'])->name('surat-masuk.index');
        Route::get('/surat/masuk/export-pdf', [SuratMasukController::class, 'exportPdf'])->name('surat-masuk.export-pdf');

        // Sub-modul 3: Surat Telaah - Read Only & Export
        Route::get('/surat/telaah', [SuratTelaahController::class, 'index'])->name('surat.telaah');
        Route::get('/surat/telaah/export-pdf', [SuratTelaahController::class, 'exportRekapPdf'])->name('surat.telaah.exportPdf');

        // Sub-modul 4: Surat SK - Read Only & Export
        Route::get('/surat/sk', [SuratSkController::class, 'index'])->name('surat.sk');
        Route::get('/surat/sk/export-pdf', [SuratSkController::class, 'exportRekapPdf'])->name('surat.sk.exportPdf');

        // Sub-modul 5: Kenaikan Pangkat & Berkala - Read Only
        Route::get('/kenaikan-pangkat', [KenaikanPangkatController::class, 'index'])->name('kenaikan-pangkat.index');
        Route::get('/kenaikan-pangkat/{id}/detail', [KenaikanPangkatController::class, 'show'])->name('kenaikan-pangkat.show')->whereNumber('id');

        // =========================================================================
        // SUB-MODUL MUTASI (KHUSUS ROLE 2 / KASUBAG: CREATE, STORE, EDIT, UPDATE, DELETE)
        // =========================================================================
        Route::middleware('role:2')->group(function () {
            // Surat Keluar (SPT & SPPD) - Operasi Mutasi
            Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
            Route::match(['get', 'post'], '/surat/create/step-2', [SuratController::class, 'createStep2'])->name('surat.create.step2');
            Route::match(['get', 'post'], '/surat/create/step-3', [SuratController::class, 'createStep3'])->name('surat.create.step3');
            Route::match(['get', 'post'], '/surat/draft', [SuratController::class, 'storeDraft'])->name('surat.draft');
            Route::get('/surat/api/next-sppd-counter', [SuratController::class, 'getNextSppdCounterApi'])->name('surat.api.nextSppd');
            Route::get('/surat/api/check-backdate', [SuratController::class, 'checkBackdateApi'])->name('surat.api.checkBackdate');
            Route::post('/surat/api/pegawai-p3k', [SuratController::class, 'storePegawaiP3kApi'])->name('surat.api.storePegawaiP3k');
            Route::post('/surat/sppd/store-standalone', [SuratController::class, 'storeSppdStandalone'])->name('surat.sppd.storeStandalone');
            Route::post('/surat/{id}/tambah-sppd', [SuratController::class, 'storeSppdChild'])->name('surat.sppd.storeChild')->whereNumber('id');
            Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit')->whereNumber('id');
            Route::match(['put', 'post'], '/surat/{id}', [SuratController::class, 'update'])->name('surat.update')->whereNumber('id');
            Route::post('/surat/{id}/upload-file', [SuratController::class, 'uploadFileSurat'])->name('surat.uploadFile')->whereNumber('id');
            Route::post('/surat/{id}/retry-drive', [SuratController::class, 'retryDriveUpload'])->name('surat.retryDrive')->whereNumber('id');
            Route::post('/surat/{id}/hubungkan-spt', [SuratController::class, 'hubungkanSpt'])->name('surat.hubungkanSpt')->whereNumber('id');
            Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('surat.destroy')->whereNumber('id');

            // Surat Masuk - Operasi Mutasi
            Route::get('/surat/masuk/create', [SuratMasukController::class, 'create'])->name('surat-masuk.create');
            Route::post('/surat/masuk', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
            Route::get('/surat/masuk/{id}/edit', [SuratMasukController::class, 'edit'])->name('surat-masuk.edit')->whereNumber('id');
            Route::put('/surat/masuk/{id}', [SuratMasukController::class, 'update'])->name('surat-masuk.update')->whereNumber('id');
            Route::delete('/surat/masuk/{id}', [SuratMasukController::class, 'destroy'])->name('surat-masuk.destroy')->whereNumber('id');
            Route::get('/surat/masuk/{id}/download', [SuratMasukController::class, 'downloadFile'])->name('surat-masuk.download')->whereNumber('id');

            // Surat Telaah - Operasi Mutasi
            Route::get('/surat/telaah/create', [SuratTelaahController::class, 'create'])->name('surat.telaah.create');
            Route::post('/surat/telaah', [SuratTelaahController::class, 'store'])->name('surat.telaah.store');
            Route::delete('/surat/telaah/{id}', [SuratTelaahController::class, 'destroy'])->name('surat.telaah.destroy')->whereNumber('id');

            // Surat SK - Operasi Mutasi
            Route::get('/surat/sk/create', [SuratSkController::class, 'create'])->name('surat.sk.create');
            Route::post('/surat/sk', [SuratSkController::class, 'store'])->name('surat.sk.store');
            Route::delete('/surat/sk/{id}', [SuratSkController::class, 'destroy'])->name('surat.sk.destroy')->whereNumber('id');

            // Kenaikan Pangkat & Berkala - Operasi Mutasi
            Route::post('/kenaikan-pangkat/store', [KenaikanPangkatController::class, 'store'])->name('kenaikan-pangkat.store');
            Route::get('/kenaikan-pangkat/{id}/edit', [KenaikanPangkatController::class, 'edit'])->name('kenaikan-pangkat.edit')->whereNumber('id');
            Route::put('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'update'])->name('kenaikan-pangkat.update')->whereNumber('id');
            Route::put('/kenaikan-pangkat/dokumen/{id}/verifikasi', [KenaikanPangkatController::class, 'verifikasiDokumen'])->name('kenaikan-pangkat.dokumen.verifikasi')->whereNumber('id');
            Route::delete('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'destroy'])->name('kenaikan-pangkat.destroy')->whereNumber('id');
        });

        // Detail / Print / Download routes for Role 1 and 2 (with numeric ID constraints)
        Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show')->whereNumber('id');
        Route::get('/surat/{id}/download', [SuratController::class, 'downloadPdf'])->name('surat.download')->whereNumber('id');
        Route::get('/surat/{id}/print', [SuratController::class, 'print'])->name('surat.print')->whereNumber('id');
        Route::get('/surat/{id}/download-pdf', [SuratController::class, 'downloadPdf'])->name('surat.downloadPdf')->whereNumber('id');
        Route::get('/surat/masuk/{id}', [SuratMasukController::class, 'show'])->name('surat-masuk.show')->whereNumber('id');
        Route::get('/surat/telaah/{id}', [SuratTelaahController::class, 'show'])->name('surat.telaah.show')->whereNumber('id');
        Route::get('/surat/sk/{id}', [SuratSkController::class, 'show'])->name('surat.sk.show')->whereNumber('id');
        Route::get('/surat/sk/{id}/download', [SuratSkController::class, 'download'])->name('surat.sk.download')->whereNumber('id');
    });

    // ------------------------------------------------------------------
    // MODUL MANAJEMEN ASET (BENDAHARA BARANG (4) & ADMIN MASTER (1))
    // ------------------------------------------------------------------
    Route::middleware('role:bendahara_barang,admin,4,1')->group(function () {
        Route::get('/admin/aset', [AdminController::class, 'aset'])->name('admin.aset');
        Route::get('/admin/aset/laporan/pdf', [AdminController::class, 'exportLaporanPdf'])->name('admin.aset.laporan.pdf');
        Route::get('/admin/aset/export-pdf', [AdminController::class, 'exportLaporanPdf'])->name('admin.aset.export-pdf');
        Route::get('/aset/export-pdf', [AdminController::class, 'exportLaporanPdf'])->name('aset.export');
        Route::post('/admin/aset', [AdminController::class, 'storeAset'])->name('admin.aset.store');
        Route::put('/admin/aset/{no_reg_pemda}', [AdminController::class, 'updateAset'])->name('admin.aset.update')->where('no_reg_pemda', '.*');
        Route::delete('/admin/aset/{no_reg_pemda}', [AdminController::class, 'destroyAset'])->name('admin.aset.destroy')->where('no_reg_pemda', '.*');
    });

    // ------------------------------------------------------------------
    // MODUL PORTAL MAHASISWA MAGANG (ROLE 5 & ADMIN)
    // ------------------------------------------------------------------
    Route::middleware('role:mahasiswa,admin,5,1')->group(function () {
        Route::get('/mahasiswa/dashboard', [MahasiswaController::class, 'index'])->name('mahasiswa.dashboard');
        Route::post('/mahasiswa/magang', [MahasiswaController::class, 'storeMagang'])->name('mahasiswa.magang.store');
        Route::get('/mahasiswa/surat-balasan/{id}/download', [MahasiswaController::class, 'downloadSuratBalasan'])->name('mahasiswa.surat-balasan.download');
    });

    // ------------------------------------------------------------------
    // RUTE UNTUK PEGAWAI (ROLE 3)
    // ------------------------------------------------------------------
    Route::middleware('role:3,pegawai')->group(function () {
        Route::get('/dashboard/pegawai', [PegawaiDashboardController::class, 'index'])->name('dashboard.pegawai');
        Route::post('/dashboard/pegawai/upload', [PegawaiDashboardController::class, 'uploadDokumen'])->name('dashboard.pegawai.upload');
        Route::post('/dashboard/pegawai/dokumen/upload', [PegawaiDashboardController::class, 'uploadDokumen'])->name('dashboard.pegawai.dokumen.upload');
        Route::delete('/dashboard/pegawai/dokumen/{id}', [PegawaiDashboardController::class, 'destroyDokumen'])->name('dashboard.pegawai.dokumen.destroy');
    });

    // ------------------------------------------------------------------
    // FALLBACK DASHBOARD SESUAI ROLE
    // ------------------------------------------------------------------
    Route::get('/dashboard', function () {
        $role = (int) Auth::user()->role_id;
        if ($role === 1) return redirect()->route('dashboard.master');
        if ($role === 2) return redirect('/surat');
        if ($role === 4) return redirect()->route('admin.aset');
        if ($role === 5) return redirect()->route('mahasiswa.dashboard');
        return redirect()->route('dashboard.pegawai');
    })->name('dashboard');
});

// ==========================================
// RUTE UJI COBA KIRIM WHATSAPP (FONNTE)
// ==========================================
Route::get('/test-wa', function() {
    $token = Setting::where('key', 'wa_token')->value('value');
    $device = Setting::where('key', 'wa_device')->value('value');

    $tujuan = '62895346804700'; 
    $pesan = 'Halo! Ini adalah pesan uji coba otomatis dari sistem Sinosip & Fonnte.';

    $payload = [
        'target' => $tujuan,
        'message' => $pesan,
    ];

    if ($device) {
        $payload['device'] = $device;
    }

    $response = Http::withHeaders([
        'Authorization' => $token,
    ])->post('https://api.fonnte.com/send', $payload);

    return $response->json();
});

// ==========================================
// RUTE UJI COBA PENGINGAT H-1 BULAN (ASN ONLY)
// ==========================================
Route::get('/test-reminder', function() {
    $targetTanggal = Carbon::now()->addMonth()->format('Y-m-d');

    $dataPengajuan = KenpaBerkala::with('pegawai')
        ->whereHas('pegawai', function($q) {
            $q->where('kategori_pegawai', 'ASN');
        })
        ->whereDate('tgl_jatuh_tempo', $targetTanggal)
        ->get();

    $controller = new KenaikanPangkatController();
    $hasilKirim = [];

    foreach ($dataPengajuan as $item) {
        $pegawai = $item->pegawai;
        if ($pegawai && $pegawai->no_wa) {
            $pesan = "Halo *{$pegawai->nama}*, ini adalah pengingat otomatis dari sistem Sinosip. Masa *{$item->jenis}* Anda akan jatuh tempo pada tanggal *{$item->tgl_jatuh_tempo}* (1 bulan lagi). Mohon segera persiapkan berkas yang diperlukan.";
            
            $response = $controller->kirimWhatsApp($pegawai->no_wa, $pesan);
            $hasilKirim[] = [
                'nama' => $pegawai->nama,
                'no_wa' => $pegawai->no_wa,
                'response' => $response
            ];
        }
    }

    return response()->json([
        'target_tanggal_pencarian' => $targetTanggal,
        'jumlah_terkirim' => count($hasilKirim),
        'detail' => $hasilKirim
    ]);
});