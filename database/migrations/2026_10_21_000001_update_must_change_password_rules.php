<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah default kolom must_change_password menjadi false di tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->change();
        });

        // 2. Koreksi data existing:
        // Semua akun non-pegawai (Admin Master, Kasubag, Bendahara Barang) diset false
        DB::table('users')
            ->where('role', '!=', 'pegawai')
            ->update(['must_change_password' => false]);

        // Akun Pegawai tetap true karena memakai password default bersama
        DB::table('users')
            ->where('role', 'pegawai')
            ->update(['must_change_password' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(true)->change();
        });

        DB::table('users')->update(['must_change_password' => true]);
    }
};
