<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JenisBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisBarang = [
            // Bahan Pokok
            [
                'nama_jenis_barang' => 'Beras',
                'keterangan' => 'Beras berbagai jenis dan kualitas',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Minyak Goreng',
                'keterangan' => 'Minyak goreng berbagai merk dan kemasan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Gula',
                'keterangan' => 'Gula pasir, gula merah, dan gula aren',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Tepung',
                'keterangan' => 'Tepung terigu, tepung beras, dan tepung lainnya',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Garam',
                'keterangan' => 'Garam dapur dan garam beryodium',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Bumbu dan Rempah
            [
                'nama_jenis_barang' => 'Bumbu Dapur',
                'keterangan' => 'Bumbu masak seperti bawang, cabai, jahe',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Rempah Kering',
                'keterangan' => 'Rempah kering seperti merica, ketumbar, jintan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Bumbu Instan',
                'keterangan' => 'Bumbu masak instan dan penyedap rasa',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Minuman
            [
                'nama_jenis_barang' => 'Minuman Ringan',
                'keterangan' => 'Minuman bersoda, jus, dan minuman kemasan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Kopi dan Teh',
                'keterangan' => 'Kopi bubuk, teh celup, dan minuman hangat',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Susu',
                'keterangan' => 'Susu cair, susu bubuk, dan produk susu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Makanan Ringan
            [
                'nama_jenis_barang' => 'Snack',
                'keterangan' => 'Keripik, biskuit, dan makanan ringan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Permen dan Coklat',
                'keterangan' => 'Permen, coklat, dan manisan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Produk Kebersihan
            [
                'nama_jenis_barang' => 'Sabun Mandi',
                'keterangan' => 'Sabun batang dan sabun cair',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Deterjen',
                'keterangan' => 'Deterjen cuci pakaian dan pelembut',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Pasta Gigi',
                'keterangan' => 'Pasta gigi dan sikat gigi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Produk Rumah Tangga
            [
                'nama_jenis_barang' => 'Tissue',
                'keterangan' => 'Tissue wajah, tissue toilet, dan tisu basah',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_jenis_barang' => 'Alat Tulis',
                'keterangan' => 'Pulpen, pensil, buku tulis, dan alat tulis',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('template_jenis_barang')->insert($jenisBarang);
    }
}