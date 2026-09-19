<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratTelaahController;
use App\Http\Controllers\SuratSkController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenaikanPangkatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MahasiswaController;
use App\Models\Setting;
use App\Models\KenpaBerkala;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

// Halaman Utama
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Alias Fallback
Route::get('/home', fn() => redirect()->route('dashboard'));
Route::get('/admin/dashboard', fn() => redirect()->route('dashboard'));

// Rute Publik Detail Aset (Dapat diakses saat QR Code di-scan)
Route::get('/aset/{id}', [AdminController::class, 'publicDetail'])->name('aset.public_detail')->where('id', '.*');

// Rute Autentikasi Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rute Pengguna Terautentikasi (Sudah Login)
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

    // Rute Khusus Admin Master (Role 1) - Pengaturan & Manajemen User
    Route::middleware('role:1')->group(function () {
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Alias kompatibilitas dari sistem-aset-magang
        Route::get('/admin/users-alias', [UserController::class, 'index'])->name('admin.user');
        Route::post('/admin/users-alias', [UserController::class, 'store'])->name('admin.user.store');
        Route::delete('/admin/users-alias/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');
    });

    // Rute Surat Menyurat & Kenaikan Pangkat (Role 1 dan 2)
    Route::middleware('role:1,2')->group(function () {
        Route::get('/dashboard/master', function () {
            $totalPegawai = \App\Models\Pegawai::count();
            $totalSurat = \App\Models\Surat::count();
            $totalSuratMasuk = \App\Models\SuratMasuk::count();
            $totalAset = \App\Models\AsetPeralatanMesin::count();
            $totalMagang = \App\Models\PendaftaranMagang::count();
            return view('dashboard.master', compact('totalPegawai', 'totalSurat', 'totalSuratMasuk', 'totalAset', 'totalMagang'));
        })->name('dashboard.master');
        
        // Sub-modul 1: Surat Keluar (SPT & SPPD)
        Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
        Route::get('/surat/keluar', [SuratController::class, 'index'])->name('surat.keluar');
        Route::get('/surat/rekap', [SuratController::class, 'rekapIndex'])->name('surat.rekap');
        Route::get('/surat/rekap/export-pdf', [SuratController::class, 'exportRekapPdf'])->name('surat.rekap.exportPdf');
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::match(['get', 'post'], '/surat/create/step-2', [SuratController::class, 'createStep2'])->name('surat.create.step2');
        Route::match(['get', 'post'], '/surat/create/step-3', [SuratController::class, 'createStep3'])->name('surat.create.step3');
        Route::match(['get', 'post'], '/surat/draft', [SuratController::class, 'storeDraft'])->name('surat.draft');
        Route::get('/surat/api/next-sppd-counter', [SuratController::class, 'getNextSppdCounterApi'])->name('surat.api.nextSppd');
        Route::get('/surat/api/check-backdate', [SuratController::class, 'checkBackdateApi'])->name('surat.api.checkBackdate');
        Route::post('/surat/api/pegawai-p3k', [SuratController::class, 'storePegawaiP3kApi'])->name('surat.api.storePegawaiP3k');
        Route::post('/surat/sppd/store-standalone', [SuratController::class, 'storeSppdStandalone'])->name('surat.sppd.storeStandalone');
        Route::post('/surat/{id}/tambah-sppd', [SuratController::class, 'storeSppdChild'])->name('surat.sppd.storeChild');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');

        // Sub-modul 2: Surat Masuk
        Route::get('/surat/masuk', [SuratMasukController::class, 'index'])->name('surat.masuk');
        Route::get('/surat/masuk-legacy', [SuratMasukController::class, 'index'])->name('surat-masuk.index'); // backward-compatibility
        Route::get('/surat/masuk/export-pdf', [SuratMasukController::class, 'exportPdf'])->name('surat-masuk.export-pdf');
        
        // Rute Mutasi Surat Masuk Khusus Admin Kasubag (Role 2)
        Route::middleware('role:2')->group(function () {
            Route::get('/surat/masuk/create', [SuratMasukController::class, 'create'])->name('surat-masuk.create');
            Route::post('/surat/masuk', [SuratMasukController::class, 'store'])->name('surat-masuk.store');
            Route::get('/surat/masuk/{id}/edit', [SuratMasukController::class, 'edit'])->name('surat-masuk.edit');
            Route::put('/surat/masuk/{id}', [SuratMasukController::class, 'update'])->name('surat-masuk.update');
            Route::delete('/surat/masuk/{id}', [SuratMasukController::class, 'destroy'])->name('surat-masuk.destroy');
            Route::get('/surat/masuk/{id}/download', [SuratMasukController::class, 'downloadFile'])->name('surat-masuk.download');
        });

        Route::get('/surat/masuk/{id}', [SuratMasukController::class, 'show'])->name('surat-masuk.show');

        // Sub-modul 3: Surat Telaah
        Route::get('/surat/telaah', [SuratTelaahController::class, 'index'])->name('surat.telaah');
        Route::get('/surat/telaah/export-pdf', [SuratTelaahController::class, 'exportRekapPdf'])->name('surat.telaah.exportPdf');
        Route::get('/surat/telaah/create', [SuratTelaahController::class, 'create'])->name('surat.telaah.create');
        Route::post('/surat/telaah', [SuratTelaahController::class, 'store'])->name('surat.telaah.store');
        Route::get('/surat/telaah/{id}', [SuratTelaahController::class, 'show'])->name('surat.telaah.show');
        Route::delete('/surat/telaah/{id}', [SuratTelaahController::class, 'destroy'])->name('surat.telaah.destroy');

        // Sub-modul 4: Surat SK
        Route::get('/surat/sk', [SuratSkController::class, 'index'])->name('surat.sk');
        Route::get('/surat/sk/export-pdf', [SuratSkController::class, 'exportRekapPdf'])->name('surat.sk.exportPdf');
        Route::get('/surat/sk/create', [SuratSkController::class, 'create'])->name('surat.sk.create');
        Route::post('/surat/sk', [SuratSkController::class, 'store'])->name('surat.sk.store');
        Route::get('/surat/sk/{id}', [SuratSkController::class, 'show'])->name('surat.sk.show');
        Route::get('/surat/sk/{id}/download', [SuratSkController::class, 'download'])->name('surat.sk.download');
        Route::delete('/surat/sk/{id}', [SuratSkController::class, 'destroy'])->name('surat.sk.destroy');

        // Operasi Dokumen Surat Keluar (SPT/SPPD)
        Route::get('/surat/{id}', [SuratController::class, 'show'])->name('surat.show');
        Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');
        Route::match(['put', 'post'], '/surat/{id}', [SuratController::class, 'update'])->name('surat.update');
        Route::post('/surat/{id}/upload-file', [SuratController::class, 'uploadFileSurat'])->name('surat.uploadFile');
        Route::post('/surat/{id}/retry-drive', [SuratController::class, 'retryDriveUpload'])->name('surat.retryDrive');
        Route::post('/surat/{id}/hubungkan-spt', [SuratController::class, 'hubungkanSpt'])->name('surat.hubungkanSpt');
        Route::get('/surat/{id}/download', [SuratController::class, 'downloadPdf'])->name('surat.download');
        Route::get('/surat/{id}/print', [SuratController::class, 'print'])->name('surat.print');
        Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('surat.destroy');
        Route::get('/surat/{id}/download-pdf', [SuratController::class, 'downloadPdf'])->name('surat.downloadPdf');

        // Kenaikan Pangkat & Berkala
        Route::get('/kenaikan-pangkat', [KenaikanPangkatController::class, 'index'])->name('kenaikan-pangkat.index');
        Route::post('/kenaikan-pangkat/store', [KenaikanPangkatController::class, 'store'])->name('kenaikan-pangkat.store');
        Route::get('/kenaikan-pangkat/{id}/edit', [KenaikanPangkatController::class, 'edit'])->name('kenaikan-pangkat.edit');
        Route::put('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'update'])->name('kenaikan-pangkat.update');
        Route::get('/kenaikan-pangkat/{id}/detail', [KenaikanPangkatController::class, 'show'])->name('kenaikan-pangkat.show');
        Route::put('/kenaikan-pangkat/dokumen/{id}/verifikasi', [KenaikanPangkatController::class, 'verifikasiDokumen'])->name('kenaikan-pangkat.dokumen.verifikasi');
        Route::delete('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'destroy'])->name('kenaikan-pangkat.destroy');
    });

    // Modul Manajemen Aset (Dikelola oleh Bendahara Barang (4) & Admin Master (1))
    Route::middleware('role:bendahara_barang,admin')->group(function () {
        Route::get('/admin/aset', [AdminController::class, 'aset'])->name('admin.aset');
        Route::get('/admin/aset/export-pdf', [AdminController::class, 'exportLaporanPdf'])->name('admin.aset.laporan.pdf');
        Route::post('/admin/aset', [AdminController::class, 'storeAset'])->name('admin.aset.store');
        Route::put('/admin/aset/{no_reg_pemda}', [AdminController::class, 'updateAset'])->name('admin.aset.update')->where('no_reg_pemda', '.*');
        Route::delete('/admin/aset/{no_reg_pemda}', [AdminController::class, 'destroyAset'])->name('admin.aset.destroy')->where('no_reg_pemda', '.*');
    });

    // Modul Kelola Magang (Dikelola oleh Admin Master (1))
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/magang', [AdminController::class, 'magang'])->name('admin.magang');
        Route::post('/admin/magang/{id}/status', [AdminController::class, 'updateStatusMagang'])->name('admin.magang.update');
    });

    // Modul Portal Mahasiswa (Role 5 & Admin)
    Route::middleware('role:mahasiswa,admin')->group(function () {
        Route::get('/mahasiswa/dashboard', [MahasiswaController::class, 'index'])->name('mahasiswa.dashboard');
        Route::post('/mahasiswa/magang', [MahasiswaController::class, 'storeMagang'])->name('mahasiswa.magang.store');
        Route::get('/mahasiswa/surat-balasan/{id}/download', [MahasiswaController::class, 'downloadSuratBalasan'])->name('mahasiswa.surat-balasan.download');
    });

    // Rute untuk Pegawai (Role 3)
    Route::middleware('role:3')->group(function () {
        Route::get('/dashboard/pegawai', [\App\Http\Controllers\PegawaiDashboardController::class, 'index'])->name('dashboard.pegawai');
        Route::post('/dashboard/pegawai/dokumen/upload', [\App\Http\Controllers\PegawaiDashboardController::class, 'uploadDokumen'])->name('dashboard.pegawai.dokumen.upload');
        Route::delete('/dashboard/pegawai/dokumen/{id}', [\App\Http\Controllers\PegawaiDashboardController::class, 'destroyDokumen'])->name('dashboard.pegawai.dokumen.destroy');
    });

    // Fallback rute dashboard sesuai role
    Route::get('/dashboard', function () {
        $role = (int) Auth::user()->role_id;
        if ($role === 1) return redirect()->route('dashboard.master');
        if ($role === 2) return redirect('/surat');
        if ($role === 4) return redirect()->route('admin.aset');
        if ($role === 5) return redirect()->route('mahasiswa.dashboard');
        return redirect()->route('dashboard.pegawai');
    })->name('dashboard');
});