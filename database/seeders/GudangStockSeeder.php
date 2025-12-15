<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GudangStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $gudangStock = [
        //     // Gudang Pusat Jakarta
        //     [
        //         'gudang_id' => 1, // Gudang Pusat Jakarta
        //         'barang_id' => 1, // Telur Ayam Ras Grade A
        //         'jumlah' => 5000.00, // 5000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 1,
        //         'barang_id' => 2, // Telur Ayam Ras Grade B
        //         'jumlah' => 3000.00, // 3000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 1,
        //         'barang_id' => 3, // Telur Ayam Kampung
        //         'jumlah' => 1500.00, // 1500 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 1,
        //         'barang_id' => 4, // Telur Bebek Segar
        //         'jumlah' => 800.00, // 800 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 1,
        //         'barang_id' => 7, // Telur Asin
        //         'jumlah' => 500.00, // 500 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],

        //     // Gudang Cabang Bandung
        //     [
        //         'gudang_id' => 2, // Gudang Cabang Bandung
        //         'barang_id' => 1, // Telur Ayam Ras Grade A
        //         'jumlah' => 2000.00, // 2000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 2,
        //         'barang_id' => 2, // Telur Ayam Ras Grade B
        //         'jumlah' => 1500.00, // 1500 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 2,
        //         'barang_id' => 3, // Telur Ayam Kampung
        //         'jumlah' => 1000.00, // 1000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 2,
        //         'barang_id' => 6, // Telur Puyuh Segar
        //         'jumlah' => 2000.00, // 2000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],

        //     // Gudang Cabang Surabaya
        //     [
        //         'gudang_id' => 3, // Gudang Cabang Surabaya
        //         'barang_id' => 1, // Telur Ayam Ras Grade A
        //         'jumlah' => 3000.00, // 3000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 3,
        //         'barang_id' => 4, // Telur Bebek Segar
        //         'jumlah' => 1200.00, // 1200 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 3,
        //         'barang_id' => 5, // Telur Bebek Manila
        //         'jumlah' => 600.00, // 600 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 3,
        //         'barang_id' => 8, // Telur Balut
        //         'jumlah' => 300.00, // 300 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],

        //     // Cold Storage Jakarta
        //     [
        //         'gudang_id' => 4, // Cold Storage Jakarta
        //         'barang_id' => 1, // Telur Ayam Ras Grade A
        //         'jumlah' => 10000.00, // 10000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 4,
        //         'barang_id' => 3, // Telur Ayam Kampung
        //         'jumlah' => 2000.00, // 2000 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 4,
        //         'barang_id' => 4, // Telur Bebek Segar
        //         'jumlah' => 1500.00, // 1500 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'gudang_id' => 4,
        //         'barang_id' => 9, // Telur Pindang
        //         'jumlah' => 800.00, // 800 butir
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        // ];

        // DB::table('gudang_stock')->insert($gudangStock);
    }
} 