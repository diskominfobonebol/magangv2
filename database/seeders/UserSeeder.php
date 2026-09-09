<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Master', 'email' => 'admin@kominfo.bonebolango.id', 'role_id' => 1],
            ['name' => 'Admin Kasubag', 'email' => 'kasubag@kominfo.bonebolango.go.id', 'role_id' => 2],
            ['name' => 'Pegawai', 'email' => 'pegawai@kominfo.bonebolango.go.id', 'role_id' => 3],
            ['name' => 'Bendahara Barang', 'email' => 'bendahara@kominfo.go.id', 'role_id' => 4],
            ['name' => 'Mahasiswa', 'email' => 'mahasiswa@gmail.com', 'role_id' => 5],
        ];

        foreach ($users as $index => $userData) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'role_id' => $userData['role_id'],
                    'is_active' => true,
                ]
            );

            if (in_array($userData['role_id'], [1, 2, 3, 4])) {
                \App\Models\Pegawai::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => '19800101200501100' . ($index + 1),
                        'nama' => $userData['name'],
                        'pangkat_golongan' => 'Penata / IIIc',
                        'jabatan' => $userData['name'],
                        'no_wa' => '08123456789' . $index,
                    ]
                );
            }
        }
    }
}
