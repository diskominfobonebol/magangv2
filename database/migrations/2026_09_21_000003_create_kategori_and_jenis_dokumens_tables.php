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
        // 1. Pastikan role Kasubag resmi terdaftar di enum users
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'admin_kasubag', 'kasubag', 'bendahara_barang', 'pegawai') NOT NULL DEFAULT 'pegawai'");

        // 2. Tabel Master 10 Kategori Dokumen Baku
        if (!Schema::hasTable('kategori_dokumens')) {
            Schema::create('kategori_dokumens', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kategori', 150)->unique();
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        }

        // 3. Tabel Master Jenis Dokumen (Sub-Kategori)
        if (!Schema::hasTable('jenis_dokumens')) {
            Schema::create('jenis_dokumens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kategori_dokumen_id')->constrained('kategori_dokumens')->onDelete('cascade');
                $table->string('nama_jenis', 255);
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('jenis_dokumens', function (Blueprint $table) {
                if (!Schema::hasColumn('jenis_dokumens', 'kategori_dokumen_id')) {
                    $table->foreignId('kategori_dokumen_id')->nullable()->constrained('kategori_dokumens')->nullOnDelete();
                }
                if (!Schema::hasColumn('jenis_dokumens', 'nama_jenis')) {
                    $table->string('nama_jenis', 255)->nullable();
                }
                if (!Schema::hasColumn('jenis_dokumens', 'urutan')) {
                    $table->integer('urutan')->default(0);
                }
            });
        }

        // 4. Tambah field relasi dan pelacakan audit aksi di tabel arsip_dokumens
        if (Schema::hasTable('arsip_dokumens')) {
            Schema::table('arsip_dokumens', function (Blueprint $table) {
                if (!Schema::hasColumn('arsip_dokumens', 'kategori_dokumen_id')) {
                    $table->foreignId('kategori_dokumen_id')->nullable()->after('user_id')->constrained('kategori_dokumens')->nullOnDelete();
                }
                if (!Schema::hasColumn('arsip_dokumens', 'jenis_dokumen_id')) {
                    $table->foreignId('jenis_dokumen_id')->nullable()->after('kategori_dokumen_id')->constrained('jenis_dokumens')->nullOnDelete();
                }
                if (!Schema::hasColumn('arsip_dokumens', 'last_action_by')) {
                    $table->foreignId('last_action_by')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('arsip_dokumens', 'last_action_role')) {
                    $table->string('last_action_role', 100)->nullable()->after('last_action_by');
                }
                if (!Schema::hasColumn('arsip_dokumens', 'last_action_type')) {
                    $table->string('last_action_type', 50)->nullable()->default('upload')->after('last_action_role');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('arsip_dokumens')) {
            Schema::table('arsip_dokumens', function (Blueprint $table) {
                if (Schema::hasColumn('arsip_dokumens', 'kategori_dokumen_id')) {
                    $table->dropForeign(['kategori_dokumen_id']);
                    $table->dropColumn('kategori_dokumen_id');
                }
                if (Schema::hasColumn('arsip_dokumens', 'jenis_dokumen_id')) {
                    $table->dropForeign(['jenis_dokumen_id']);
                    $table->dropColumn('jenis_dokumen_id');
                }
                if (Schema::hasColumn('arsip_dokumens', 'last_action_by')) {
                    $table->dropForeign(['last_action_by']);
                    $table->dropColumn('last_action_by');
                }
                if (Schema::hasColumn('arsip_dokumens', 'last_action_role')) {
                    $table->dropColumn('last_action_role');
                }
                if (Schema::hasColumn('arsip_dokumens', 'last_action_type')) {
                    $table->dropColumn('last_action_type');
                }
            });
        }

        Schema::dropIfExists('jenis_dokumens');
        Schema::dropIfExists('kategori_dokumens');
    }
};
