<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $jsonPath = database_path('seeders/barangExisting.json');
        $jsonContent = file_get_contents($jsonPath);
        $barangData = json_decode($jsonContent, true);

        $barang = [];
        foreach ($barangData as $item) {
            $barang[] = [
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'jenis_barang_id' => 1, // Bahan Pokok
                'satuan_terkecil_id' => 6, // Pcs
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('template_barang')->insert($barang);

        $templateBarang = DB::table('template_barang')->get();

        $barangSatuan = [];
        foreach ($templateBarang as $barang) {
            $barangSatuan[] = [
                'barang_id' => $barang->id,
                'satuan_id' => 6, // Pcs
                'konversi_satuan_terkecil' => 1.00,
                'is_satuan_terkecil' => 'ya',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('template_barang_satuan')->insert($barangSatuan);
    }
}
