<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'nama_customer' => 'Supermarket Sumber Rejeki',
                'alamat' => 'Jl. Merdeka No. 100, Jakarta Pusat',
                'no_hp' => '02187654321',
                'email' => 'sumberrejeki@gmail.com',
                'keterangan' => 'Supermarket besar dengan high volume purchase',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_customer' => 'Toko Kelontong Barokah',
                'alamat' => 'Jl. Pasar Minggu No. 25, Jakarta Selatan',
                'no_hp' => '08198765432',
                'email' => 'barokah123@gmail.com',
                'keterangan' => 'Toko kelontong tradisional',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_customer' => 'Restoran Padang Minang',
                'alamat' => 'Jl. Sabang No. 15, Jakarta Pusat',
                'no_hp' => '08209876543',
                'email' => 'minangpadang@gmail.com',
                'keterangan' => 'Restoran dengan kebutuhan telur harian tinggi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_customer' => 'Bakery & Cake Shop Delicious',
                'alamat' => 'Jl. Gatot Subroto No. 77, Bandung',
                'no_hp' => '08310987654',
                'email' => 'delicious@gmail.com',
                'keterangan' => 'Toko roti dan kue dengan kebutuhan telur regular',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_customer' => 'Distributor Telur Regional',
                'alamat' => 'Jl. Bypass No. 200, Surabaya',
                'no_hp' => '08421098765',
                'email' => 'regional@gmail.com',
                'keterangan' => 'Distributor telur untuk wilayah Jawa Timur',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_customer' => 'Catering Nusantara',
                'alamat' => 'Jl. HR Rasuna Said No. 50, Jakarta Selatan',
                'no_hp' => '08532109876',
                'email' => 'nusantara@gmail.com',
                'keterangan' => 'Layanan catering dengan pesanan besar',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('customer')->insert($customers);
    }
} 