<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratTelaahController;
use App\Http\Controllers\SuratSkController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenaikanPangkatController;
use App\Http\Controllers\PegawaiDashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InstansiPengirimController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArsipDokumenController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PasswordResetController;
use App\Models\Setting;
use App\Models\KenpaBerkala;
use App\Models\Pegawai;
use App\Models\Surat;
use App\Models\SuratMasuk;
use App\Models\AsetPeralatanMesin;
use App\Models\PendaftaranMagang;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// ==========================================
// RUTE PUBLIK & AUTENTIKASI
// ==========================================

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('landing');

Route::get('/magang', function () {
    return view('landing.magang');
})->name('landing.magang');

// Rute Publik Detail Aset & QR Code (Dapat diakses saat QR Code di-scan)
Route::get('/aset/{id}/qr-image', [AdminController::class, 'qrImage'])->name('aset.qr_image')->where('id', '.*');
Route::get('/aset/{id}', [AdminController::class, 'publicDetail'])->name('aset.public_detail')->where('id', '^(?!export-pdf$).*');
Route::get('/api/aset/{id}', [AdminController::class, 'apiDetail'])->name('api.aset.detail')->where('id', '.*');
Route::get('/berita-acara/{id}/preview', [AdminController::class, 'previewBeritaAcara'])->name('berita_acara.preview');
Route::get('/berita-acara/{id}/download-pdf', [AdminController::class, 'downloadPdfBeritaAcara'])->name('berita_acara.download_pdf');

// Rute Login & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login/pegawai', [AuthController::class, 'showLoginPegawai'])->name('login.pegawai');
Route::get('/login/mahasiswa', [AuthController::class, 'showLoginMahasiswa'])->name('login.mahasiswa');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Registrasi Akun Mahasiswa Magang (Publik)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rute Lupa & Reset Password
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// ==========================================
// RUTE TEROTENTIKASI (AUTH + FORCE PASSWORD RESET + 2FA)
// ==========================================

Route::middleware(['auth', 'force.password.reset', 'two_factor'])->group(function () {
    
    // Rute Wajib Ganti Password (Semua Role saat must_change_password = true)
    Route::get('/reset-password-wajib', [AuthController::class, 'showForceResetPassword'])->name('password.force_reset');
    Route::post('/reset-password-wajib', [AuthController::class, 'forceResetPassword'])->name('password.force_reset.update');
    Route::get('/security/change-password', [SecurityController::class, 'showChangePassword'])->name('password.change');
    Route::post('/security/change-password', [SecurityController::class, 'updatePassword'])->name('password.change.update');

    // Rute 2FA (Setup, Konfirmasi, Recovery Codes, dan Verifikasi Rutin)
    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [TwoFactorController::class, 'confirmSetup'])->name('2fa.setup.confirm');
    Route::get('/security/2fa/setup', [SecurityController::class, 'showTwoFactorSetup']);
    Route::post('/security/2fa/setup', [SecurityController::class, 'confirmTwoFactorSetup']);

    Route::get('/2fa/recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('2fa.recovery-codes');
    Route::post('/2fa/recovery-codes', [TwoFactorController::class, 'acknowledgeRecoveryCodes'])->name('2fa.recovery-codes.acknowledge');
    Route::get('/security/2fa/recovery-codes', [SecurityController::class, 'showRecoveryCodes'])->name('2fa.recovery_codes');
    Route::post('/security/2fa/recovery-codes', [SecurityController::class, 'ackRecoveryCodes'])->name('2fa.recovery_codes.ack');

    Route::get('/2fa/verify', [TwoFactorController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.check');
    Route::get('/security/2fa/verify', [SecurityController::class, 'showTwoFactorVerify']);
    Route::post('/security/2fa/verify', [SecurityController::class, 'checkTwoFactorVerify']);

    // Halaman Keamanan Akun (dapat diakses dari header profile dropdown)
    Route::get('/keamanan-akun', [TwoFactorController::class, 'showAccountSecurity'])->name('account.security');
    Route::post('/keamanan-akun/regenerate-recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('account.security.regenerate-recovery-codes');
    Route::post('/keamanan-akun/re-enroll', [TwoFactorController::class, 'reEnroll'])->name('account.security.re-enroll');
    Route::post('/account/security/regenerate-recovery', [SecurityController::class, 'regenerateRecoveryCodes'])->name('account.security.regenerate_recovery');
    Route::post('/account/security/reset-device', [SecurityController::class, 'resetTwoFactorDevice'])->name('account.security.reset_device');

    // Rute Notifikasi (Semua Role Terotentikasi)
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifikasi/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifikasi/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifikasi/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Rute Khusus Admin Master (Role 1) - Pengaturan & Manajemen User
    Route::middleware('role:1')->group(function () {
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users-list', [UserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::post('/admin/users/{id}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('admin.users.reset-2fa');
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Modul Kelola Magang (Dikelola oleh Admin Master (1))
        Route::get('/admin/magang', [AdminController::class, 'magang'])->name('admin.magang');
        Route::post('/admin/magang/{id}/status', [AdminController::class, 'updateStatusMagang'])->name('admin.magang.update');
    });

    // Rute untuk Admin dan Kasubag (Role 1 dan 2) - Hak Akses Baca, Lihat Detail, Cetak, Export & Download
    Route::middleware('role:1,2')->group(function () {
        Route::get('/dashboard/master', function () {
            $totalPegawai = Pegawai::count();
            $totalSurat = Surat::count();
            $totalSuratMasuk = SuratMasuk::count();
            $totalAset = class_exists(AsetPeralatanMesin::class) ? AsetPeralatanMesin::count() : 0;
            $totalMagang = class_exists(PendaftaranMagang::class) ? PendaftaranMagang::count() : 0;
            return view('dashboard.master', compact('totalPegawai', 'totalSurat', 'totalSuratMasuk', 'totalAset', 'totalMagang'));
        })->name('dashboard.master');
        
        // Redirect /surat lama ke /surat/keluar
        Route::get('/surat', function () {
            return redirect()->route('surat.keluar', request()->query());
        })->name('surat.index');

        // =========================================================================
        // SUB-MODUL READ-ONLY (ROLE 1 & 2: INDEX, REKAP, DETAIL, EXPORT, PRINT)
        // =========================================================================

        // Sub-modul 1: Surat Keluar (SPT & SPPD) - Read Only & Export/Download/Print
        Route::get('/surat/keluar', [SuratController::class, 'index'])->name('surat.keluar');
        Route::get('/surat/rekap', [SuratController::class, 'rekapIndex'])->name('surat.rekap');
        Route::get('/surat/rekap/export-pdf', [SuratController::class, 'exportRekapPdf'])->name('surat.rekap.exportPdf');

        // Sub-modul 2: Surat Masuk - Read Only, Export & Kelola Instansi (Lihat Daftar)
        Route::get('/surat/masuk', [SuratMasukController::class, 'index'])->name('surat.masuk');
        Route::get('/surat/masuk-legacy', [SuratMasukController::class, 'index'])->name('surat-masuk.index'); // backward-compatibility
        Route::get('/surat/masuk/export-pdf', [SuratMasukController::class, 'exportPdf'])->name('surat-masuk.export-pdf');
        Route::get('/surat/masuk/instansi', [InstansiPengirimController::class, 'index'])->name('surat-masuk.instansi.index');
        Route::get('/surat/masuk/instansi/{id}/count', [InstansiPengirimController::class, 'countAffected'])->name('surat-masuk.instansi.count')->whereNumber('id');

        // Sub-modul 3: Surat Telaah - Read Only & Export
        Route::get('/surat/telaah', [SuratTelaahController::class, 'index'])->name('surat.telaah');
        Route::get('/surat/telaah/export-pdf', [SuratTelaahController::class, 'exportRekapPdf'])->name('surat.telaah.exportPdf');

        // Sub-modul 4: Surat SK - Read Only & Export/Download
        Route::get('/surat/sk', [SuratSkController::class, 'index'])->name('surat.sk');
        Route::get('/surat/sk/export-pdf', [SuratSkController::class, 'exportRekapPdf'])->name('surat.sk.exportPdf');

        // Sub-modul 5: Kenaikan Pangkat & Berkala - Read Only
        Route::get('/kenaikan-pangkat', [KenaikanPangkatController::class, 'index'])->name('kenaikan-pangkat.index');
        Route::get('/kenaikan-pangkat/{id}/detail', [KenaikanPangkatController::class, 'show'])->name('kenaikan-pangkat.show')->whereNumber('id');

        // API Routes untuk Pengecekan Real-time (Duplikat & Backdate)
        Route::get('/surat/api/check-nomor-duplikat', [SuratController::class, 'checkNomorDuplikatApi'])->name('surat.api.checkDuplikat');
        Route::get('/surat/api/check-nomor-duplikat/check', [SuratController::class, 'checkNomorDuplikatApi'])->name('surat.api.checkNomorDuplikat');
        Route::get('/surat/api/check-surat-masuk-duplikat', [SuratMasukController::class, 'checkSuratMasukDuplikatApi'])->name('surat-masuk.api.checkDuplikat');
        Route::get('/surat/api/check-backdate-kategori', [SuratController::class, 'checkBackdateKategoriApi'])->name('surat.api.checkBackdateKategori');

        // =========================================================================
        // RUTE MUTASI / OPERASI UBAH DATA (EKSKLUSIF KASUBAG / ROLE 2)
        // =========================================================================
        Route::middleware('role:2')->group(function () {
            // Surat Keluar (SPT & SPPD) - Operasi Mutasi & Penerbitan
            Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
            Route::match(['get', 'post'], '/surat/create/step-2', [SuratController::class, 'createStep2'])->name('surat.create.step2');
            Route::match(['get', 'post'], '/surat/create/step-3', [SuratController::class, 'createStep3'])->name('surat.create.step3');
            Route::match(['get', 'post'], '/surat/draft', [SuratController::class, 'storeDraft'])->name('surat.draft');

            // SPPD Standalone Wizard (3 Langkah bergaya SPT)
            Route::get('/surat/sppd/create', [SuratController::class, 'createSppd'])->name('surat.sppd.create');
            Route::match(['get', 'post'], '/surat/sppd/create/step-2', [SuratController::class, 'createSppdStep2'])->name('surat.sppd.create.step2');
            Route::match(['get', 'post'], '/surat/sppd/create/step-3', [SuratController::class, 'createSppdStep3'])->name('surat.sppd.create.step3');
            Route::post('/surat/sppd/draft', [SuratController::class, 'storeSppdDraft'])->name('surat.sppd.draft');
            Route::post('/surat/sppd/store', [SuratController::class, 'storeSppd'])->name('surat.sppd.store');

            Route::get('/surat/api/next-sppd-counter', [SuratController::class, 'getNextSppdCounterApi'])->name('surat.api.nextSppd');
            Route::get('/surat/api/check-backdate', [SuratController::class, 'checkBackdateApi'])->name('surat.api.checkBackdate');
            Route::post('/surat/api/pegawai-p3k', [SuratController::class, 'storePegawaiP3kApi'])->name('surat.api.storePegawaiP3k');
            Route::post('/surat/sppd/store-standalone', [SuratController::class, 'storeSppdStandalone'])->name('surat.sppd.storeStandalone');
            Route::post('/surat/{id}/tambah-sppd', [SuratController::class, 'storeSppdChild'])->name('surat.sppd.storeChild')->whereNumber('id');
            Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
            Route::get('/surat/lainnya/create', [SuratController::class, 'createLainnya'])->name('surat.lainnya.create');
            Route::post('/surat/lainnya', [SuratController::class, 'storeLainnya'])->name('surat.lainnya.store');
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

            // Surat Masuk - Kelola Instansi Pengirim (Tambah, Ubah, Hapus)
            Route::post('/surat/masuk/instansi', [InstansiPengirimController::class, 'store'])->name('surat-masuk.instansi.store');
            Route::put('/surat/masuk/instansi/{id}', [InstansiPengirimController::class, 'update'])->name('surat-masuk.instansi.update')->whereNumber('id');
            Route::delete('/surat/masuk/instansi/{id}', [InstansiPengirimController::class, 'destroy'])->name('surat-masuk.instansi.destroy')->whereNumber('id');

            // Surat Telaah - Operasi Mutasi
            Route::get('/surat/telaah/create', [SuratTelaahController::class, 'create'])->name('surat.telaah.create');
            Route::post('/surat/telaah', [SuratTelaahController::class, 'store'])->name('surat.telaah.store');
            Route::get('/surat/telaah/{id}/edit', [SuratTelaahController::class, 'edit'])->name('surat.telaah.edit')->whereNumber('id');
            Route::match(['put', 'post'], '/surat/telaah/{id}', [SuratTelaahController::class, 'update'])->name('surat.telaah.update')->whereNumber('id');
            Route::delete('/surat/telaah/{id}', [SuratTelaahController::class, 'destroy'])->name('surat.telaah.destroy')->whereNumber('id');

            // Surat SK - Operasi Mutasi
            Route::get('/surat/sk/create', [SuratSkController::class, 'create'])->name('surat.sk.create');
            Route::post('/surat/sk', [SuratSkController::class, 'store'])->name('surat.sk.store');
            Route::get('/surat/sk/{id}/edit', [SuratSkController::class, 'edit'])->name('surat.sk.edit')->whereNumber('id');
            Route::match(['put', 'post'], '/surat/sk/{id}', [SuratSkController::class, 'update'])->name('surat.sk.update')->whereNumber('id');
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

    // Rute untuk Pegawai (Role 3)
    Route::middleware('role:3')->group(function () {
        Route::get('/dashboard/pegawai', [PegawaiDashboardController::class, 'index'])->name('dashboard.pegawai');
        Route::post('/dashboard/pegawai/upload', [PegawaiDashboardController::class, 'uploadDokumen'])->name('dashboard.pegawai.upload');
        Route::delete('/dashboard/pegawai/dokumen/{id}', [PegawaiDashboardController::class, 'destroyDokumen'])->name('dashboard.pegawai.dokumen.destroy');
    });

    // =========================================================================
    // MODUL ARSIP DOKUMEN PEGAWAI (AKSES MANDIRI DOKUMEN SENDIRI)
    // =========================================================================
    Route::middleware('role:pegawai,admin,admin_kasubag,kasubag,1,2,3')->group(function () {
        Route::get('/pegawai/dokumen', [ArsipDokumenController::class, 'myDocuments'])->name('pegawai.dokumen');
        Route::post('/pegawai/dokumen', [ArsipDokumenController::class, 'storePegawai'])->name('pegawai.dokumen.store');
        Route::put('/pegawai/dokumen/{id}', [ArsipDokumenController::class, 'updatePegawai'])->name('pegawai.dokumen.update');
        Route::delete('/pegawai/dokumen/{id}', [ArsipDokumenController::class, 'destroyPegawai'])->name('pegawai.dokumen.destroy');
        Route::get('/dokumen/{id}/download', [ArsipDokumenController::class, 'download'])->name('dokumen.download');
        Route::get('/dokumen/{id}/preview', [ArsipDokumenController::class, 'preview'])->name('dokumen.preview');
    });

    // =========================================================================
    // MODUL KELOLA ARSIP DOKUMEN & PEGAWAI (KASUBAG & ADMIN MASTER)
    // =========================================================================
    Route::middleware('role:admin,admin_kasubag,kasubag,1,2')->group(function () {
        Route::get('/admin/arsip-dokumen', [ArsipDokumenController::class, 'index'])->name('admin.arsip.index');
        Route::post('/admin/arsip-dokumen', [ArsipDokumenController::class, 'store'])->name('admin.arsip.store');
        Route::put('/admin/arsip-dokumen/{id}', [ArsipDokumenController::class, 'update'])->name('admin.arsip.update');
        Route::delete('/admin/arsip-dokumen/{id}', [ArsipDokumenController::class, 'destroy'])->name('admin.arsip.destroy');

        Route::post('/admin/pegawai/store', [ArsipDokumenController::class, 'storePegawaiAccount'])->name('admin.pegawai.store');
        Route::put('/admin/pegawai/{id}', [ArsipDokumenController::class, 'updatePegawaiAccount'])->name('admin.pegawai.update');
        Route::post('/admin/pegawai/{id}/toggle-status', [ArsipDokumenController::class, 'toggleStatusPegawaiAccount'])->name('admin.pegawai.toggle_status');
        Route::delete('/admin/pegawai/{id}', [ArsipDokumenController::class, 'destroyPegawaiAccount'])->name('admin.pegawai.destroy');
    });

    // =========================================================================
    // MODUL MANAJEMEN ASET & BERITA ACARA (BENDAHARA BARANG & ADMIN MASTER)
    // =========================================================================
    Route::middleware('role:bendahara_barang,admin,4,1')->group(function () {
        Route::get('/admin/aset', [AdminController::class, 'aset'])->name('admin.aset');
        Route::get('/admin/aset/berita-acara', [AdminController::class, 'beritaAcara'])->name('admin.aset.berita_acara');
        Route::get('/admin/berita-acara/{id}/file', [AdminController::class, 'fileBeritaAcara'])->name('admin.berita_acara.file');
        Route::get('/admin/berita-acara/{id}/file-doc', [AdminController::class, 'fileBeritaAcara'])->name('admin.berita-acara.file');
        Route::get('/admin/aset/laporan', [AdminController::class, 'laporanRekap'])->name('admin.aset.laporan');
        Route::get('/admin/aset/laporan/pdf', [AdminController::class, 'exportLaporanPdf'])->name('admin.aset.laporan.pdf');
        Route::get('/admin/aset/export-pdf', [AdminController::class, 'exportLaporanPdf'])->name('admin.aset.export-pdf');
        Route::get('/aset/export-pdf', [AdminController::class, 'exportLaporanPdf'])->name('aset.export');
        Route::post('/admin/aset', [AdminController::class, 'storeAset'])->name('admin.aset.store');
        Route::put('/admin/aset/{no_reg_pemda}', [AdminController::class, 'updateAset'])->name('admin.aset.update')->where('no_reg_pemda', '.*');
        Route::delete('/admin/aset/{no_reg_pemda}', [AdminController::class, 'destroyAset'])->name('admin.aset.destroy')->where('no_reg_pemda', '.*');
        Route::post('/admin/berita-acara', [AdminController::class, 'storeBeritaAcara'])->name('admin.berita_acara.store');
        Route::post('/admin/berita-acara/generate', [AdminController::class, 'generateBeritaAcara'])->name('admin.berita_acara.generate');
        Route::get('/admin/berita-acara/check-nomor', [AdminController::class, 'checkNomorBeritaAcara'])->name('admin.berita_acara.check_nomor');
    });

    // =========================================================================
    // MODUL PORTAL MAHASISWA MAGANG (ROLE 5 & ADMIN)
    // =========================================================================
    Route::middleware('role:mahasiswa,admin,5,1')->group(function () {
        Route::get('/mahasiswa/dashboard', [MahasiswaController::class, 'index'])->name('mahasiswa.dashboard');
        Route::post('/mahasiswa/magang', [MahasiswaController::class, 'storeMagang'])->name('mahasiswa.magang.store');
        Route::get('/mahasiswa/surat-balasan/{id}/download', [MahasiswaController::class, 'downloadSuratBalasan'])->name('mahasiswa.surat-balasan.download');
    });

    // Fallback dashboard route for generic dashboard requests
    Route::get('/dashboard', function () {
        $role = (int) \Illuminate\Support\Facades\Auth::user()->role_id;
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
