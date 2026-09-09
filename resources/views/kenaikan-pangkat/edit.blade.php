@extends('layouts.app')

@section('title', 'Edit Data Pegawai')

@section('content')
<div class="max-w-4xl mx-auto bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
    <div class="px-6 py-5 border-b border-blue-200/40 bg-white/60 flex justify-between items-center">
        <h3 class="text-xl font-bold text-navy">Edit Data Pegawai & Jadwal</h3>
        <a href="{{ route('kenaikan-pangkat.index') }}" class="btn-pill-secondary px-4 py-1.5 text-xs font-bold">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('kenaikan-pangkat.update', $pegawai->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- KOTAK PESAN ERROR VALIDASI -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-6 py-4 mx-6 mt-6 rounded-2xl">
                <div class="font-bold mb-1">Penyimpanan gagal karena:</div>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-6 space-y-4">
            <!-- Nama Lengkap -->
            <div>
                <label class="form-label">Nama Lengkap & Gelar</label>
                <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}" required class="form-input">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIP -->
                <div>
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}" required class="form-input">
                </div>

                <!-- No. WhatsApp -->
                <div>
                    <label class="form-label">No. WhatsApp</label>
                    <input type="text" name="no_wa" value="{{ old('no_wa', $pegawai->no_wa) }}" required class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Jabatan -->
                <div>
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $pegawai->jabatan) }}" required class="form-input">
                </div>

                <!-- Pangkat / Golongan -->
                <div>
                    <label class="form-label">Pangkat / Golongan</label>
                    <input type="text" name="pangkat_golongan" list="list-pangkat" value="{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}" placeholder="Ketik atau pilih pangkat..." class="form-input">
                    
                    <datalist id="list-pangkat">
                        <option value="Juru Muda / I.a">
                        <option value="Juru Muda Tingkat I / I.b">
                        <option value="Juru / I.c">
                        <option value="Juru Tingkat I / I.d">
                        <option value="Pengatur Muda / II.a">
                        <option value="Pengatur Muda Tingkat I / II.b">
                        <option value="Pengatur / II.c">
                        <option value="Pengatur Tingkat I / II.d">
                        <option value="Penata Muda / III.a">
                        <option value="Penata Muda Tingkat I / III.b">
                        <option value="Penata / III.c">
                        <option value="Penata Tingkat I / III.d">
                        <option value="Pembina / IV.a">
                        <option value="Pembina Tingkat I / IV.b">
                        <option value="Pembina Utama Muda / IV.c">
                        <option value="Pembina Utama Madya / IV.d">
                        <option value="Pembina Utama / IV.e">
                    </datalist>
                    <p class="text-[10px] text-slate-500 mt-1">*Ketik untuk mencari atau klik untuk memilih opsi standar.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Jenis Kenaikan -->
                <div>
                    <label class="form-label">Jenis Kenaikan</label>
                    <select name="jenis" required class="form-input">
                        <option value="Kenaikan Pangkat" {{ old('jenis', optional($kenpa)->jenis) == 'Kenaikan Pangkat' ? 'selected' : '' }}>Kenaikan Pangkat (+4 Tahun)</option>
                        <option value="Berkala" {{ old('jenis', optional($kenpa)->jenis) == 'Berkala' ? 'selected' : '' }}>Gaji Berkala (+2 Tahun)</option>
                        <option value="Keduanya" {{ old('jenis', optional($kenpa)->jenis) == 'Keduanya' ? 'selected' : '' }}>Keduanya (Pangkat & Berkala)</option>
                    </select>
                </div>

                <!-- Tanggal Terakhir (SK Sebelumnya) -->
                <div>
                    <label class="form-label">Tanggal SK Terakhir (TMT)</label>
                    <input type="date" name="tgl_terakhir" value="{{ old('tgl_terakhir', optional($kenpa)->tgl_terakhir ? \Carbon\Carbon::parse($kenpa->tgl_terakhir)->format('Y-m-d') : '') }}" class="form-input">
                    <p class="text-[10px] text-slate-500 mt-1">*Jatuh tempo akan dihitung otomatis dari tanggal ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Status Jadwal -->
                <div>
                    <label class="form-label">Status Jadwal</label>
                    <select name="status" required class="form-input">
                        <option value="Aktif" {{ old('status', optional($kenpa)->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', optional($kenpa)->status) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Progres Berkas Otomatis -->
                <div>
                    <label class="form-label">Progres Kelengkapan Berkas</label>
                    <div class="bg-white/80 border border-blue-200/50 rounded-xl p-2.5">
                        @php 
                            $progresVal = optional($kenpa)->progres_berkas ?? 0; 
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex-grow progress-track h-2.5">
                                <div class="progress-bar-primary h-2.5 rounded-full transition-all duration-500" style="width: {{ $progresVal }}%"></div>
                            </div>
                            <span class="text-sm font-extrabold text-navy w-10 text-right">{{ $progresVal }}%</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">*Progres dihitung otomatis dari unggahan mandiri pegawai.</p>
                </div>
            </div>

            <!-- Status ACC -->
            <div>
                <label class="form-label">Status Persetujuan (ACC)</label>
                <select name="status_acc" required class="form-input">
                    <option value="Menunggu" {{ old('status_acc', optional($kenpa)->status_acc) == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Disetujui" {{ old('status_acc', optional($kenpa)->status_acc) == 'Disetujui' ? 'selected' : '' }}>Disetujui (ACC)</option>
                    <option value="Ditolak" {{ old('status_acc', optional($kenpa)->status_acc) == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="form-label">Keterangan / Catatan (Opsional)</label>
                <textarea name="keterangan" rows="2" class="w-full rounded-xl border border-slate-300 p-2.5 focus:border-blue-500 focus:outline-none text-sm text-navy bg-white/90" placeholder="Catatan tambahan...">{{ old('keterangan', optional($kenpa)->keterangan) }}</textarea>
            </div>
        </div>

        <div class="bg-white/60 px-6 py-4 flex justify-end gap-3 border-t border-blue-200/40">
            <a href="{{ route('kenaikan-pangkat.index') }}" class="btn-pill-secondary px-5 py-2.5 text-xs font-bold">
                Batal
            </a>
            <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection