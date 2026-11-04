<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update tabel jenis_surats
        Schema::table('jenis_surats', function (Blueprint $table) {
            if (!Schema::hasColumn('jenis_surats', 'nama')) {
                $table->string('nama')->nullable()->after('id');
            }
            // Jadikan kolom kode nullable jika sebelumnya NOT NULL
            $table->string('kode')->nullable()->change();

            if (!Schema::hasColumn('jenis_surats', 'mode_nomor_default')) {
                $table->string('mode_nomor_default', 20)->default('manual')->after('kode');
            }
            if (!Schema::hasColumn('jenis_surats', 'punya_langkah_sppd')) {
                $table->boolean('punya_langkah_sppd')->default(false)->after('mode_nomor_default');
            }
        });

        // 2. Buat tabel registri nomor_surat_terpakai
        if (!Schema::hasTable('nomor_surat_terpakai')) {
            Schema::create('nomor_surat_terpakai', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_normalized', 255)->unique();
                $table->string('nomor_asli', 255);
                $table->string('kategori', 50);
                $table->string('source_table', 50);
                $table->unsignedBigInteger('source_id');
                $table->date('tgl_surat')->nullable();
                $table->timestamps();

                $table->index(['source_table', 'source_id']);
            });
        }

        // 3. Update & Seed data kategori jenis_surats
        if (DB::table('jenis_surats')->whereIn('id', [1, 2])->exists()) {
            // SPT (id = 2)
            DB::table('jenis_surats')->where('id', 2)->update([
                'nama' => 'SPT',
                'nama_jenis' => 'SPT',
                'kode' => '555',
                'mode_nomor_default' => 'otomatis',
                'punya_langkah_sppd' => true,
                'updated_at' => now(),
            ]);

            // SPPD (id = 1)
            DB::table('jenis_surats')->where('id', 1)->update([
                'nama' => 'SPPD',
                'nama_jenis' => 'SPPD',
                'kode' => '090',
                'mode_nomor_default' => 'otomatis',
                'punya_langkah_sppd' => false,
                'updated_at' => now(),
            ]);

            // Surat Telaah
            $telaahExists = DB::table('jenis_surats')->where('nama_jenis', 'Surat Telaah')->orWhere('nama', 'Surat Telaah')->first();
            if (!$telaahExists) {
                DB::table('jenis_surats')->insert([
                    'nama' => 'Surat Telaah',
                    'nama_jenis' => 'Surat Telaah',
                    'kode' => '555/KEP/Telaah.staff',
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('jenis_surats')->where('id', $telaahExists->id)->update([
                    'nama' => 'Surat Telaah',
                    'kode' => '555/KEP/Telaah.staff',
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'updated_at' => now(),
                ]);
            }

            // Surat SK
            $skExists = DB::table('jenis_surats')->where('nama_jenis', 'Surat SK')->orWhere('nama', 'Surat SK')->first();
            if (!$skExists) {
                DB::table('jenis_surats')->insert([
                    'nama' => 'Surat SK',
                    'nama_jenis' => 'Surat SK',
                    'kode' => '555/KEP/Kominfo.BB',
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('jenis_surats')->where('id', $skExists->id)->update([
                    'nama' => 'Surat SK',
                    'kode' => '555/KEP/Kominfo.BB',
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'updated_at' => now(),
                ]);
            }

            // Lainnya (kode harus kosong / null)
            $lainnyaExists = DB::table('jenis_surats')->where('nama_jenis', 'Lainnya')->orWhere('nama', 'Lainnya')->first();
            if (!$lainnyaExists) {
                DB::table('jenis_surats')->insert([
                    'nama' => 'Lainnya',
                    'nama_jenis' => 'Lainnya',
                    'kode' => null,
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('jenis_surats')->where('id', $lainnyaExists->id)->update([
                    'nama' => 'Lainnya',
                    'kode' => null,
                    'mode_nomor_default' => 'manual',
                    'punya_langkah_sppd' => false,
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Seed data nomor lama yang sudah ada ke nomor_surat_terpakai
        $normalize = function($str) {
            return strtolower(preg_replace('/\s+/', ' ', trim($str)));
        };

        // a. Dari surats
        $suratRows = DB::table('surats')
            ->whereNotNull('nomor_surat')
            ->where('nomor_surat', '!=', '')
            ->get(['id', 'jenis_surat_id', 'nomor_surat', 'tgl_surat']);

        foreach ($suratRows as $s) {
            $norm = $normalize($s->nomor_surat);
            if (empty($norm)) continue;

            $kategori = ($s->jenis_surat_id == 1 || str_starts_with($s->nomor_surat, '090/')) ? 'SPPD' : 'SPT';
            DB::table('nomor_surat_terpakai')->insertOrIgnore([
                'nomor_normalized' => $norm,
                'nomor_asli' => trim($s->nomor_surat),
                'kategori' => $kategori,
                'source_table' => 'surats',
                'source_id' => $s->id,
                'tgl_surat' => $s->tgl_surat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // b. Dari surat_pegawai (SPPD personel)
        $pivotRows = DB::table('surat_pegawai')
            ->join('surats', 'surat_pegawai.surat_id', '=', 'surats.id')
            ->whereNotNull('surat_pegawai.nomor_sppd')
            ->where('surat_pegawai.nomor_sppd', '!=', '')
            ->where('surat_pegawai.nomor_sppd', '!=', '-')
            ->select('surat_pegawai.id', 'surat_pegawai.surat_id', 'surat_pegawai.nomor_sppd', 'surats.tgl_surat')
            ->get();

        foreach ($pivotRows as $p) {
            $norm = $normalize($p->nomor_sppd);
            if (empty($norm)) continue;

            DB::table('nomor_surat_terpakai')->insertOrIgnore([
                'nomor_normalized' => $norm,
                'nomor_asli' => trim($p->nomor_sppd),
                'kategori' => 'SPPD',
                'source_table' => 'surat_pegawai',
                'source_id' => $p->id,
                'tgl_surat' => $p->tgl_surat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // c. Dari surat_telaahs
        $telaahRows = DB::table('surat_telaahs')
            ->whereNotNull('nomor_telaah')
            ->where('nomor_telaah', '!=', '')
            ->get(['id', 'nomor_telaah', 'tanggal_telaah']);

        foreach ($telaahRows as $t) {
            $norm = $normalize($t->nomor_telaah);
            if (empty($norm)) continue;

            DB::table('nomor_surat_terpakai')->insertOrIgnore([
                'nomor_normalized' => $norm,
                'nomor_asli' => trim($t->nomor_telaah),
                'kategori' => 'Surat Telaah',
                'source_table' => 'surat_telaahs',
                'source_id' => $t->id,
                'tgl_surat' => $t->tanggal_telaah,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // d. Dari surat_sks
        $skRows = DB::table('surat_sks')
            ->whereNotNull('nomor_sk')
            ->where('nomor_sk', '!=', '')
            ->get(['id', 'nomor_sk', 'tanggal_sk']);

        foreach ($skRows as $k) {
            $norm = $normalize($k->nomor_sk);
            if (empty($norm)) continue;

            DB::table('nomor_surat_terpakai')->insertOrIgnore([
                'nomor_normalized' => $norm,
                'nomor_asli' => trim($k->nomor_sk),
                'kategori' => 'Surat SK',
                'source_table' => 'surat_sks',
                'source_id' => $k->id,
                'tgl_surat' => $k->tanggal_sk,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_terpakai');

        Schema::table('jenis_surats', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surats', 'punya_langkah_sppd')) {
                $table->dropColumn('punya_langkah_sppd');
            }
            if (Schema::hasColumn('jenis_surats', 'mode_nomor_default')) {
                $table->dropColumn('mode_nomor_default');
            }
            if (Schema::hasColumn('jenis_surats', 'nama')) {
                $table->dropColumn('nama');
            }
        });
    }
};
