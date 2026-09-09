@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="text-2xl font-bold text-navy">Manajemen User & Pegawai</h1>
            <p class="text-sm text-slate-500">Kelola akun login, NIP, email, dan hak akses pengguna aplikasi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Form Tambah User / Pegawai -->
    <div class="bg-card-gradient p-6 sm:p-8 rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50">
        <h2 class="text-lg font-bold text-navy mb-4">Tambah User Baru</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Nama lengkap..." class="form-input">
            </div>
            <div>
                <label class="form-label">Email / NIP</label>
                <input type="text" name="identity" required placeholder="NIP atau Email..." class="form-input">
            </div>
            <div>
                <label class="form-label">Password Default</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="form-input">
            </div>
            <div>
                <label class="form-label">Role / Hak Akses</label>
                <select name="role_id" class="form-input">
                    <option value="1">Admin Master</option>
                    <option value="2">Kasubag / Operator</option>
                    <option value="3">Pegawai</option>
                    <option value="4">Bendahara Barang</option>
                    <option value="5">Mahasiswa</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end pt-2">
                <button type="submit" class="btn-pill-primary px-6 py-2.5 text-xs font-bold shadow-md">
                    Simpan User Baru
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar User -->
    <div class="bg-card-gradient rounded-3xl shadow-xl shadow-blue-900/5 border border-blue-200/50 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white/70 border-b border-blue-200/40 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-4 px-6">Nama</th>
                    <th class="py-4 px-6">Email / NIP Login</th>
                    <th class="py-4 px-6">Role</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blue-100/40 text-sm text-slate-700">
                @foreach($users as $user)
                <tr class="hover:bg-white/40 transition-colors">
                    <td class="py-4 px-6 font-bold text-navy">{{ $user->name }}</td>
                    <td class="py-4 px-6 font-medium text-slate-600">{{ $user->email }}</td>
                    <td class="py-4 px-6">
                        <span class="px-3 py-1 text-xs font-bold rounded-full 
                            {{ $user->role_id == 1 ? 'badge-pink' : ($user->role_id == 2 ? 'badge-blue' : ($user->role_id == 4 ? 'bg-amber-100 text-amber-800' : ($user->role_id == 5 ? 'bg-purple-100 text-purple-800' : 'badge-navy'))) }}">
                            {{ $user->role->nama ?? 'Role ' . $user->role_id }}
                        </span>
                    </td>
                   <td class="py-4 px-6 text-center">
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?')" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-pill-secondary !px-3 !py-1 !text-xs !border-red-200 text-red-600 hover:!bg-red-50 font-bold">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection