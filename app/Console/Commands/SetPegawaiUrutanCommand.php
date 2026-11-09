<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetPegawaiUrutanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:set-urutan {--dry-run : Simulasi perubahan tanpa menyimpan ke basis data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menata dan menetapkan urutan pegawai 1–26 berdasarkan struktur jabatan resmi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = (bool)$this->option('dry-run');

        if ($isDryRun) {
            $this->warn('=== MODE DRY-RUN AKTIF: Perubahan TIDAK akan disimpan ke basis data ===');
        } else {
            $this->info('=== MENJALANKAN PENETAPAN URUTAN PEGAWAI SECARA LANGSUNG ===');
        }

        $csvPath = database_path('seeders/data/pegawai.csv');
        if (!file_exists($csvPath)) {
            $this->error("Berkas CSV referensi tidak ditemukan di: {$csvPath}");
            return 1;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // urutan,nama,nip,jabatan,golongan

        $csvRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || !isset($row[2])) {
                continue;
            }
            $csvRows[] = [
                'urutan'   => (int)trim($row[0]),
                'nama'     => trim($row[1]),
                'nip'      => trim($row[2]),
                'nip_norm' => preg_replace('/[^0-9]/', '', $row[2]),
                'jabatan'  => trim($row[3] ?? ''),
                'golongan' => trim($row[4] ?? ''),
            ];
        }
        fclose($handle);

        $allUsers = User::all();
        $matchedUserIds = [];
        $differences = [];
        $missingNips = [];
        $matchedRows = [];

        foreach ($csvRows as $csvItem) {
            $user = $allUsers->first(function ($u) use ($csvItem) {
                if (empty($u->nip)) return false;
                $userNormNip = preg_replace('/[^0-9]/', '', $u->nip);
                return $userNormNip === $csvItem['nip_norm'];
            });

            if ($user) {
                $matchedUserIds[] = $user->id;
                $matchedRows[] = [
                    'user'       => $user,
                    'new_urutan' => $csvItem['urutan'],
                    'csv_item'   => $csvItem,
                ];

                // Cek perbedaan jabatan / golongan tanpa menimpanya
                $diffJabatan = (trim($user->jabatan ?? '') !== $csvItem['jabatan']);
                $diffGol = (trim($user->golongan ?? '') !== $csvItem['golongan']);

                if ($diffJabatan || $diffGol) {
                    $differences[] = [
                        'user_id'     => $user->id,
                        'nama_db'     => $user->nama,
                        'nip'         => $user->nip,
                        'db_jabatan'  => $user->jabatan ?: '-',
                        'csv_jabatan' => $csvItem['jabatan'] ?: '-',
                        'db_gol'      => $user->golongan ?: '-',
                        'csv_gol'     => $csvItem['golongan'] ?: '-',
                    ];
                }
            } else {
                $missingNips[] = $csvItem;
            }
        }

        // Cari user yang belum masuk ke dalam 26 daftar di atas (misal akun pegawai tambahan)
        $unlistedUsers = User::whereNotIn('id', $matchedUserIds)
            ->where('role', 'pegawai')
            ->orderBy('nama', 'asc')
            ->get();

        $this->newLine();
        $this->info("Menemukan " . count($matchedRows) . " akun pegawai yang cocok dari 26 daftar resmi.");

        // Tabel hasil pencocokan
        $tableData = [];
        foreach ($matchedRows as $m) {
            $tableData[] = [
                'ID' => $m['user']->id,
                'Urutan Baru' => $m['new_urutan'],
                'Urutan Lama' => $m['user']->urutan ?? '-',
                'Nama' => $m['user']->nama,
                'NIP' => $m['user']->nip,
                'Role' => $m['user']->role,
            ];
        }
        $this->table(['ID', 'Urutan Baru', 'Urutan Lama', 'Nama', 'NIP', 'Role'], $tableData);

        // Laporkan perbedaan Jabatan/Golongan
        if (!empty($differences)) {
            $this->newLine();
            $this->warn("Terdapat " . count($differences) . " perbedaan Jabatan/Golongan di CSV vs Database (TIDAK DITIMPA sesuai instruksi):");
            $this->table(
                ['ID', 'Nama', 'NIP', 'Jabatan (DB)', 'Jabatan (CSV)', 'Gol (DB)', 'Gol (CSV)'],
                $differences
            );
        }

        // Laporkan NIP yang belum ada di database
        if (!empty($missingNips)) {
            $this->newLine();
            $this->error("Terdapat " . count($missingNips) . " NIP dari daftar yang TIDAK DITEMUKAN di database (tidak dibuat baru):");
            $missingTable = [];
            foreach ($missingNips as $mis) {
                $missingTable[] = [
                    'Urutan' => $mis['urutan'],
                    'Nama'   => $mis['nama'],
                    'NIP'    => $mis['nip'],
                ];
            }
            $this->table(['Urutan', 'Nama', 'NIP'], $missingTable);
        }

        // Laporkan pegawai di luar 26 daftar resmi yang ditempatkan setelah urutan 26
        $unlistedCount = $unlistedUsers->count();
        if ($unlistedCount > 0) {
            $this->newLine();
            $this->comment("Terdapat {$unlistedCount} akun pegawai di luar 26 daftar resmi. Ditempatkan mulai urutan 27 ke atas (diurutkan alfabet nama):");
            $unlistedTable = [];
            $nextUrutan = 27;
            foreach ($unlistedUsers as $u) {
                $unlistedTable[] = [
                    'ID' => $u->id,
                    'Urutan Baru' => $nextUrutan,
                    'Urutan Lama' => $u->urutan ?? '-',
                    'Nama' => $u->nama,
                    'NIP' => $u->nip ?: '-',
                ];
                $nextUrutan++;
            }
            $this->table(['ID', 'Urutan Baru', 'Urutan Lama', 'Nama', 'NIP'], $unlistedTable);
        }

        // Terapkan perubahan ke database jika bukan dry-run
        if (!$isDryRun) {
            DB::transaction(function () use ($matchedRows, $unlistedUsers) {
                foreach ($matchedRows as $m) {
                    $m['user']->urutan = $m['new_urutan'];
                    $m['user']->save();
                }

                $nextUrutan = 27;
                foreach ($unlistedUsers as $u) {
                    $u->urutan = $nextUrutan++;
                    $u->save();
                }
            });

            $this->newLine();
            $this->info("Urutan seluruh pegawai berhasil disimpan permanen ke basis data!");
        } else {
            $this->newLine();
            $this->warn("Dry-run selesai. Jalankan tanpa opsi --dry-run untuk menerapkan perubahan.");
        }

        return 0;
    }
}

