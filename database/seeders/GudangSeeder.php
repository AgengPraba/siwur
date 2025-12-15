<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gudang = [
            [
                'nama_gudang' => 'Gudang Pusat',
                'keterangan' => 'Gudang utama dengan fasilitas pendingin untuk penyimpanan telur',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // [
            //     'nama_gudang' => 'Gudang Cabang Bandung',
            //     'keterangan' => 'Gudang regional Bandung untuk distribusi wilayah Jawa Barat',
            //     'created_at' => Carbon::now(),
            //     'updated_at' => Carbon::now(),
            // ],
            // [
            //     'nama_gudang' => 'Gudang Cabang Surabaya',
            //     'keterangan' => 'Gudang regional Surabaya untuk distribusi wilayah Jawa Timur',
            //     'created_at' => Carbon::now(),
            //     'updated_at' => Carbon::now(),
            // ],
            // [
            //     'nama_gudang' => 'Cold Storage Jakarta',
            //     'keterangan' => 'Fasilitas penyimpanan dingin khusus untuk telur premium',
            //     'created_at' => Carbon::now(),
            //     'updated_at' => Carbon::now(),
            // ],
        ];

        DB::table('gudang')->insert($gudang);
    }
} 