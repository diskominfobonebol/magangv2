<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SuratDummySeeder extends Seeder
{
    /**
     * Run the database seeds for 15 dummy surat menyurat.
     */
    public function run(): void
    {
        $this->call(SuratSeeder::class);
    }
}
