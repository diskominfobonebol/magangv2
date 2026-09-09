@extends('layouts.app')

@section('title', 'Portal Magang Mahasiswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-navy">Portal Pengajuan Magang</h2>
            <p class="text-slate-500 mt-1">Layanan pendaftaran magang mandiri dan pemantauan surat balasan Dinas Kominfo Bone Bolango.</p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm flex items-center space-x-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm space-y-1 shadow-sm">
            <div class="flex items-center space-x-2 font-bold text-rose-700">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                <span>Terdapat Kesalahan Input:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2 text-rose-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. Status Pendaftaran Section -->
    <div class="bg-card-gradient rounded-3xl p-6 sm:p-8 border border-blue-200/50 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-blue-200/40 pb-4">
            <div>
                <h3 class="font-extrabold text-navy text-xl">Status Verifikasi Pengajuan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Status verifikasi dokumen pengantar magang dari instansi Diskominfo.</p>
            </div>
            
            <div>
                @if(isset($pengajuanSaya))
                    @if($pengajuanSaya->status === 'Diterima')
                        <span class="inline-flex items-center gap-1.5 badge-blue px-4 py-1.5 rounded-full text-xs font-bold">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Diterima / Disetujui</span>
                        </span>
                    @elseif($pengajuanSaya->status === 'Ditolak')
                        <span class="inline-flex items-center gap-1.5 bg-rose-100 text-rose-800 border border-rose-200 px-4 py-1.5 rounded-full text-xs font-bold">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>Ditolak</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 badge-pink px-4 py-1.5 rounded-full text-xs font-bold">
                            <i class="fa-solid fa-clock"></i>
                            <span>Menunggu Verifikasi</span>
                        </span>
                    @endif
                @else
                    <span class="inline-flex items-center gap-1.5 badge-navy px-4 py-1.5 rounded-full text-xs font-bold">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Belum Mengajukan</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Area Download Surat Balasan -->
        <div class="bg-white/70 rounded-2xl p-5 border border-blue-200/60 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="icon-circle-blue flex-shrink-0">
                    <i class="fa-solid fa-file-pdf text-xl text-primary"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-navy">Surat Balasan Resmi Penerimaan Magang</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if(isset($pengajuanSaya) && $pengajuanSaya->suratBalasan)
                            No. Surat: <span class="font-semibold text-primary">{{ $pengajuanSaya->suratBalasan->no_surat ?? 'Tersedia' }}</span>
                        @else
                            Surat balasan resmi bertanda tangan digital akan tersedia setelah berkas disetujui.
                        @endif
                    </p>
                </div>
            </div>

            @if(isset($pengajuanSaya) && $pengajuanSaya->suratBalasan && $pengajuanSaya->suratBalasan->file_surat_balasan)
                <a href="{{ route('mahasiswa.surat-balasan.download', $pengajuanSaya->id) }}" class="btn-pill-gradient px-6 py-2.5 text-xs font-bold gap-2 whitespace-nowrap shadow-md">
                    <i class="fa-solid fa-download"></i>
                    <span>Download Surat Balasan (PDF)</span>
                </a>
            @else
                <button disabled class="btn-pill-secondary px-5 py-2.5 text-xs font-semibold gap-2 opacity-50 cursor-not-allowed whitespace-nowrap">
                    <i class="fa-solid fa-lock"></i>
                    <span>Surat Balasan Belum Tersedia</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 2. Form Pendaftaran Magang -->
    <div class="bg-card-gradient rounded-3xl border border-blue-200/50 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-blue-200/40 bg-white/60">
            <h3 class="font-extrabold text-navy text-lg">Formulir Pengajuan Magang Mahasiswa</h3>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi akademik dan lampirkan surat pengantar resmi dari kampus/sekolah Anda.</p>
        </div>

        <form action="{{ route('mahasiswa.magang.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            
            <!-- 1. Data Diri Section -->
            <div>
                <h4 class="text-xs font-extrabold text-primary uppercase tracking-wider mb-4 border-b border-blue-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>1. Data Diri & Asal Kampus</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ Auth::user()->name ?? Auth::user()->nama }}" class="form-input bg-slate-100/80 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="form-label">Email Terdaftar</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-input bg-slate-100/80 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nim" value="{{ old('nim', $pengajuanSaya->nim ?? '') }}" placeholder="Contoh: 531420001" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Asal Perguruan Tinggi / Sekolah <span class="text-rose-500">*</span></label>
                        <input type="text" name="universitas" value="{{ old('universitas', $pengajuanSaya->universitas ?? trim(preg_replace('/\s*\(.*?\)/', '', Auth::user()->instansi_bidang ?? ''))) }}" placeholder="Contoh: Universitas Negeri Gorontalo" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Program Studi / Jurusan <span class="text-rose-500">*</span></label>
                        <input type="text" name="jurusan" value="{{ old('jurusan', $pengajuanSaya->jurusan ?? '') }}" placeholder="Contoh: Sistem Informasi" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                        <input type="tel" name="no_hp" value="{{ old('no_hp', $pengajuanSaya->no_hp ?? '') }}" placeholder="08xxxxxxxxxx" class="form-input" required>
                    </div>
                </div>
            </div>

            <!-- 2. Durasi Pelaksanaan Magang -->
            <div>
                <h4 class="text-xs font-extrabold text-primary uppercase tracking-wider mb-4 border-b border-blue-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>2. Durasi Pelaksanaan Magang</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $pengajuanSaya->tgl_mulai ?? '') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tgl_selesai" value="{{ old('tgl_selesai', $pengajuanSaya->tgl_selesai ?? '') }}" class="form-input" required>
                    </div>
                </div>
            </div>

            <!-- 3. Berkas Surat Pengantar PDF -->
            <div>
                <h4 class="text-xs font-extrabold text-primary uppercase tracking-wider mb-4 border-b border-blue-100 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>3. Berkas Surat Pengantar Kampus</span>
                </h4>
                <div>
                    <label class="form-label">Surat Pengantar / Permohonan (Format PDF, Maks. 5MB) <span class="text-rose-500">*</span></label>
                    <input type="file" name="surat_pengantar" accept=".pdf" class="form-input file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-primary hover:file:bg-blue-200 cursor-pointer" required>
                    @error('surat_pengantar')
                        <p class="text-xs text-rose-500 font-bold mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-blue-100">
                <button type="submit" class="btn-pill-gradient px-8 py-3 text-sm font-bold gap-2 shadow-lg shadow-blue-500/25">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Pengajuan Magang</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
