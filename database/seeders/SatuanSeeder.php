<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuan = [
            // Satuan Berat
            [
                'nama_satuan' => 'Gram',
                'keterangan' => 'Satuan berat gram',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Kilogram',
                'keterangan' => 'Satuan berat kilogram',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Ons',
                'keterangan' => 'Satuan berat ons (100 gram)',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Satuan Volume
            [
                'nama_satuan' => 'Liter',
                'keterangan' => 'Satuan volume liter',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Mililiter',
                'keterangan' => 'Satuan volume mililiter',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Satuan Kemasan
            [
                'nama_satuan' => 'Pcs',
                'keterangan' => 'Satuan per pieces/buah',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Pack',
                'keterangan' => 'Satuan per pack/kemasan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Dus',
                'keterangan' => 'Satuan per dus/kardus',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Karton',
                'keterangan' => 'Satuan per karton besar',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Botol',
                'keterangan' => 'Satuan per botol',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Kaleng',
                'keterangan' => 'Satuan per kaleng',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Sachet',
                'keterangan' => 'Satuan per sachet/kemasan kecil',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Bungkus',
                'keterangan' => 'Satuan per bungkus',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Satuan Khusus
            [
                'nama_satuan' => 'Lusin',
                'keterangan' => 'Satuan per lusin (12 buah)',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Gross',
                'keterangan' => 'Satuan per gross (144 buah)',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_satuan' => 'Roll',
                'keterangan' => 'Satuan per roll/gulungan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('template_satuan')->insert($satuan);
    }
}