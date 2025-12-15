<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'Peternakan Ayam Maju Jaya',
                'alamat' => 'Jl. Raya Sukabumi No. 123, Bogor',
                'no_hp' => '08123456789',
                'email' => 'majujaya@gmail.com',
                'keterangan' => 'Supplier telur ayam ras grade A',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_supplier' => 'Peternakan Bebek Sari Murni',
                'alamat' => 'Desa Sukamaju, Cianjur',
                'no_hp' => '08234567890',
                'email' => 'sarimurni@gmail.com',
                'keterangan' => 'Supplier telur bebek segar',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_supplier' => 'Farm Puyuh Berkah',
                'alamat' => 'Jl. Veteran No. 45, Bandung',
                'no_hp' => '08345678901',
                'email' => 'puyuhberkah@gmail.com',
                'keterangan' => 'Spesialis telur puyuh berkualitas',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_supplier' => 'CV. Telur Kampung Asli',
                'alamat' => 'Kampung Babakan, Garut',
                'no_hp' => '08456789012',
                'email' => 'telurksmpung@gmail.com',
                'keterangan' => 'Telur ayam kampung organik',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_supplier' => 'Peternakan Integrated Sejahtera',
                'alamat' => 'Jl. Industri No. 89, Bekasi',
                'no_hp' => '08567890123',
                'email' => 'integrated@gmail.com',
                'keterangan' => 'Supplier telur olahan dan segar',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('supplier')->insert($suppliers);
    }
} 