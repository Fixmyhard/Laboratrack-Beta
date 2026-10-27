<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('karyawans')->insert([
            [
                'nama'        => 'Darius',
                'jabatan'     => 'Admin',
                'gaji_pokok'  => 2600000,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Ari',
                'jabatan'     => 'Tukang',
                'gaji_pokok'  => 2600000,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'S',
                'jabatan'     => 'Semi Tukang',
                'gaji_pokok'  => 2600000,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'C',
                'jabatan'     => 'Tukang',
                'gaji_pokok'  => 2600000,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
