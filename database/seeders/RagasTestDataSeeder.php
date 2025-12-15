<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * RagasTestDataSeeder - Seeder untuk data testing RAG/RAGAS
 * 
 * Seeder ini membuat data yang realistis untuk toko retail Indonesia
 * dengan skenario yang dapat diuji menggunakan framework RAGAS.
 */
class RagasTestDataSeeder extends Seeder
{
    private $tokoId;
    private $userId;
    private $gudangId;
    private $now;

    public function run(): void
    {
        $this->now = Carbon::now();
        
        $this->command->info('========================================');
        $this->command->info('RAGAS Test Data Seeder - Toko Retail Indonesia');
        $this->command->info('========================================');
        
        DB::beginTransaction();
        
        try {
            $this->createUserAndToko();
            $this->createGudang();
            $this->createJenisBarang();
            $this->createSatuan();
            $this->createSuppliers();
            $this->createCustomers();
            $this->createBarang();
            $this->createPembelian();
            $this->createPenjualan();
            
            DB::commit();
            
            $this->command->info('');
            $this->command->info('✅ RAGAS Test Data seeding completed successfully!');
            $this->command->info('');
            $this->command->info('Toko ID: ' . $this->tokoId);
            $this->command->info('User: kasir.ragas@siwur.test / password123');
            $this->command->info('');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createUserAndToko(): void
    {
        $this->command->info('Creating test user and store...');
        
        // Check if user already exists
        $existingUser = DB::table('users')->where('email', 'agengpraba@gmail.com')->first();
        
        if ($existingUser) {
            $this->userId = $existingUser->id;
            
            // Check if user has akses to a toko
            $existingAkses = DB::table('akses')->where('user_id', $this->userId)->first();
            if ($existingAkses) {
                $this->tokoId = $existingAkses->toko_id;
                $this->command->info('  Using existing user and store (Toko ID: ' . $this->tokoId . ')');
                return;
            }
        } else {
            // Create new user
            $this->userId = DB::table('users')->insertGetId([
                'name' => 'admin',
                'email' => 'admin@siwur.test',
                'password' => Hash::make('admin123'),
                'email_verified_at' => $this->now,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
        
        // Check if toko already exists
        $existingToko = DB::table('toko')->where('nama_toko', 'Toko Sinar Abadi')->first();
        
        if ($existingToko) {
            $this->tokoId = $existingToko->id;
        } else {
            // Create new toko
            $this->tokoId = DB::table('toko')->insertGetId([
                'nama_toko' => 'Toko Sinar Abadi',
                'alamat_toko' => 'Jl. Raya Pasar Minggu No. 45, Jakarta Selatan 12520',
                'user_id' => $this->userId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
        
        // Create akses record to link user with toko
        $existingAkses = DB::table('akses')->where('user_id', $this->userId)->first();
        if (!$existingAkses) {
            DB::table('akses')->insert([
                'user_id' => $this->userId,
                'toko_id' => $this->tokoId,
                'role' => 'owner',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
        
        $this->command->info('  Created user: kasir.ragas@siwur.test');
        $this->command->info('  Created store: Toko Sinar Abadi (ID: ' . $this->tokoId . ')');
    }

    private function createGudang(): void
    {
        $this->command->info('Creating warehouses...');
        
        $existingGudang = DB::table('gudang')->where('toko_id', $this->tokoId)->first();
        
        if ($existingGudang) {
            $this->gudangId = $existingGudang->id;
            $this->command->info('  Using existing warehouse');
            return;
        }
        
        $gudangs = [
            [
                'toko_id' => $this->tokoId,
                'nama_gudang' => 'Gudang Utama',
                'keterangan' => 'Gudang utama untuk penyimpanan semua barang',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
            [
                'toko_id' => $this->tokoId,
                'nama_gudang' => 'Gudang Dingin',
                'keterangan' => 'Gudang berpendingin untuk produk yang mudah rusak',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
        ];
        
        foreach ($gudangs as $gudang) {
            $id = DB::table('gudang')->insertGetId($gudang);
            if (!$this->gudangId) {
                $this->gudangId = $id;
            }
        }
        
        $this->command->info('  Created 2 warehouses');
    }

    private function createJenisBarang(): void
    {
        $this->command->info('Creating product categories...');
        
        $existing = DB::table('jenis_barang')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Categories already exist, skipping...');
            return;
        }
        
        $categories = [
            ['nama_jenis_barang' => 'Beras & Biji-bijian', 'keterangan' => 'Beras, jagung, kacang-kacangan'],
            ['nama_jenis_barang' => 'Minyak & Margarin', 'keterangan' => 'Minyak goreng, margarin, mentega'],
            ['nama_jenis_barang' => 'Gula & Pemanis', 'keterangan' => 'Gula pasir, gula merah, madu'],
            ['nama_jenis_barang' => 'Tepung & Premix', 'keterangan' => 'Tepung terigu, maizena, bumbu pelapis'],
            ['nama_jenis_barang' => 'Bumbu & Rempah', 'keterangan' => 'Bumbu masak, rempah-rempah, penyedap'],
            ['nama_jenis_barang' => 'Mie & Pasta', 'keterangan' => 'Mie instan, bihun, spaghetti'],
            ['nama_jenis_barang' => 'Minuman', 'keterangan' => 'Air mineral, teh, kopi, susu'],
            ['nama_jenis_barang' => 'Snack & Cemilan', 'keterangan' => 'Keripik, biskuit, wafer'],
            ['nama_jenis_barang' => 'Sabun & Deterjen', 'keterangan' => 'Sabun mandi, deterjen, pewangi'],
            ['nama_jenis_barang' => 'Perawatan Tubuh', 'keterangan' => 'Shampo, pasta gigi, deodoran'],
            ['nama_jenis_barang' => 'Produk Bayi', 'keterangan' => 'Susu formula, popok, bedak bayi'],
            ['nama_jenis_barang' => 'Rokok & Korek', 'keterangan' => 'Rokok, korek api, asbak'],
        ];
        
        foreach ($categories as $cat) {
            DB::table('jenis_barang')->insert([
                'toko_id' => $this->tokoId,
                'nama_jenis_barang' => $cat['nama_jenis_barang'],
                'keterangan' => $cat['keterangan'],
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
        
        $this->command->info('  Created ' . count($categories) . ' categories');
    }

    private function createSatuan(): void
    {
        $this->command->info('Creating units...');
        
        $existing = DB::table('satuan')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Units already exist, skipping...');
            return;
        }
        
        $units = [
            ['nama_satuan' => 'Pcs', 'keterangan' => 'Pieces/buah'],
            ['nama_satuan' => 'Dus', 'keterangan' => 'Karton/dus besar'],
            ['nama_satuan' => 'Pack', 'keterangan' => 'Paket/bungkus'],
            ['nama_satuan' => 'Kg', 'keterangan' => 'Kilogram'],
            ['nama_satuan' => 'Liter', 'keterangan' => 'Liter'],
            ['nama_satuan' => 'Lusin', 'keterangan' => '12 buah'],
            ['nama_satuan' => 'Sachet', 'keterangan' => 'Sachet kecil'],
            ['nama_satuan' => 'Botol', 'keterangan' => 'Botol'],
            ['nama_satuan' => 'Karung', 'keterangan' => 'Karung besar'],
            ['nama_satuan' => 'Renteng', 'keterangan' => 'Renteng/gantungan'],
        ];
        
        foreach ($units as $unit) {
            DB::table('satuan')->insert([
                'toko_id' => $this->tokoId,
                'nama_satuan' => $unit['nama_satuan'],
                'keterangan' => $unit['keterangan'],
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }
        
        $this->command->info('  Created ' . count($units) . ' units');
    }

    private function createSuppliers(): void
    {
        $this->command->info('Creating suppliers...');
        
        $existing = DB::table('supplier')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Suppliers already exist, skipping...');
            return;
        }
        
        $suppliers = [
            [
                'nama_supplier' => 'PT Indofood CBP Sukses Makmur',
                'alamat' => 'Jl. Jend. Sudirman Kav. 76-78, Jakarta',
                'no_hp' => '021-57958822',
                'email' => 'sales@indofood.com',
                'keterangan' => 'Supplier mie instan, bumbu, dan makanan olahan',
            ],
            [
                'nama_supplier' => 'PT Unilever Indonesia',
                'alamat' => 'BSD Green Office Park, Tangerang',
                'no_hp' => '021-80827200',
                'email' => 'customercare@unilever.com',
                'keterangan' => 'Supplier sabun, deterjen, dan produk perawatan',
            ],
            [
                'nama_supplier' => 'CV Beras Sejahtera',
                'alamat' => 'Pasar Induk Cipinang, Jakarta Timur',
                'no_hp' => '08128765432',
                'email' => 'berassejahtera@gmail.com',
                'keterangan' => 'Supplier beras dan biji-bijian lokal',
            ],
            [
                'nama_supplier' => 'PT Mayora Indah',
                'alamat' => 'Jl. Tomang Raya No. 21-23, Jakarta Barat',
                'no_hp' => '021-5655322',
                'email' => 'info@mayora.com',
                'keterangan' => 'Supplier biskuit, permen, dan minuman',
            ],
            [
                'nama_supplier' => 'PT Wings Surya',
                'alamat' => 'Jl. Tipar Cakung, Jakarta Timur',
                'no_hp' => '021-4609655',
                'email' => 'sales@wingscorp.com',
                'keterangan' => 'Supplier deterjen dan produk kebersihan',
            ],
            [
                'nama_supplier' => 'UD Maju Bersama',
                'alamat' => 'Pasar Tanah Abang Blok A, Jakarta Pusat',
                'no_hp' => '08159876543',
                'email' => 'majubersama@gmail.com',
                'keterangan' => 'Supplier rokok dan korek api',
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            DB::table('supplier')->insert(array_merge($supplier, [
                'toko_id' => $this->tokoId,
                'is_opname' => 0,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]));
        }
        
        $this->command->info('  Created ' . count($suppliers) . ' suppliers');
    }

    private function createCustomers(): void
    {
        $this->command->info('Creating customers...');
        
        $existing = DB::table('customer')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Customers already exist, skipping...');
            return;
        }
        
        $customers = [
            [
                'nama_customer' => 'Warung Makan Bu Tini',
                'alamat' => 'Jl. Raya Pasar Minggu No. 12, Jakarta Selatan',
                'no_hp' => '08123456001',
                'email' => 'warungbutini@gmail.com',
                'keterangan' => 'Warung makan dengan pembelian harian beras dan minyak',
            ],
            [
                'nama_customer' => 'Toko Kelontong Pak Ahmad',
                'alamat' => 'Jl. Pejaten Barat No. 55, Jakarta Selatan',
                'no_hp' => '08123456002',
                'email' => 'tokoahmad@gmail.com',
                'keterangan' => 'Toko kelontong tetangga yang membeli grosir',
            ],
            [
                'nama_customer' => 'Catering Berkah Ibu',
                'alamat' => 'Jl. Kemang Raya No. 88, Jakarta Selatan',
                'no_hp' => '08123456003',
                'email' => 'cateringberkah@gmail.com',
                'keterangan' => 'Catering dengan pesanan besar untuk acara',
            ],
            [
                'nama_customer' => 'Koperasi Kantor XYZ',
                'alamat' => 'Gedung Perkantoran ABC Lt. 5, Kuningan',
                'no_hp' => '08123456004',
                'email' => 'koperasixyz@gmail.com',
                'keterangan' => 'Koperasi kantor dengan pembelian bulanan',
            ],
            [
                'nama_customer' => 'Ibu Ratna (Pelanggan Setia)',
                'alamat' => 'Perumahan Griya Asri Blok C-12',
                'no_hp' => '08123456005',
                'email' => 'ratna.ibu@gmail.com',
                'keterangan' => 'Pelanggan ibu rumah tangga setia sejak 2010',
            ],
            [
                'nama_customer' => 'Minimarket Mandiri',
                'alamat' => 'Jl. Radio Dalam Raya No. 33',
                'no_hp' => '08123456006',
                'email' => 'minimandiri@gmail.com',
                'keterangan' => 'Minimarket lokal yang membeli grosir',
            ],
            [
                'nama_customer' => 'Pondok Pesantren Al-Hikmah',
                'alamat' => 'Jl. Pondok Labu No. 100',
                'no_hp' => '08123456007',
                'email' => 'alhikmah@gmail.com',
                'keterangan' => 'Pesantren dengan kebutuhan sembako bulanan besar',
            ],
            [
                'nama_customer' => 'Restoran Padang Sari Raso',
                'alamat' => 'Jl. Fatmawati Raya No. 77',
                'no_hp' => '08123456008',
                'email' => 'sariraso@gmail.com',
                'keterangan' => 'Restoran Padang dengan kebutuhan bumbu dan beras tinggi',
            ],
        ];
        
        foreach ($customers as $customer) {
            DB::table('customer')->insert(array_merge($customer, [
                'toko_id' => $this->tokoId,
                'is_opname' => 0,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]));
        }
        
        $this->command->info('  Created ' . count($customers) . ' customers');
    }

    private function createBarang(): void
    {
        $this->command->info('Creating products...');
        
        $existing = DB::table('barang')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Products already exist, skipping...');
            return;
        }
        
        $categories = DB::table('jenis_barang')->where('toko_id', $this->tokoId)->get()->keyBy('nama_jenis_barang');
        $units = DB::table('satuan')->where('toko_id', $this->tokoId)->get()->keyBy('nama_satuan');
        
        if ($categories->isEmpty() || $units->isEmpty()) {
            $this->command->warn('  No categories or units found, skipping products...');
            return;
        }
        
        // Produk-produk realistis toko retail Indonesia
        $products = [
            // Beras & Biji-bijian
            ['kode' => 'BRS001', 'nama' => 'Beras Premium Cap Rojo Lele 5kg', 'kategori' => 'Beras & Biji-bijian', 'satuan' => 'Karung', 'stok' => 150],
            ['kode' => 'BRS002', 'nama' => 'Beras IR64 Medium 10kg', 'kategori' => 'Beras & Biji-bijian', 'satuan' => 'Karung', 'stok' => 80],
            ['kode' => 'BRS003', 'nama' => 'Beras Pulen Cap Pandan Wangi 5kg', 'kategori' => 'Beras & Biji-bijian', 'satuan' => 'Karung', 'stok' => 60],
            ['kode' => 'BRS004', 'nama' => 'Kacang Hijau 500g', 'kategori' => 'Beras & Biji-bijian', 'satuan' => 'Pack', 'stok' => 45],
            
            // Minyak & Margarin
            ['kode' => 'MNY001', 'nama' => 'Minyak Goreng Bimoli 2L', 'kategori' => 'Minyak & Margarin', 'satuan' => 'Botol', 'stok' => 200],
            ['kode' => 'MNY002', 'nama' => 'Minyak Goreng Tropical 1L', 'kategori' => 'Minyak & Margarin', 'satuan' => 'Botol', 'stok' => 180],
            ['kode' => 'MNY003', 'nama' => 'Minyak Goreng Sania Refill 2L', 'kategori' => 'Minyak & Margarin', 'satuan' => 'Pack', 'stok' => 120],
            ['kode' => 'MNY004', 'nama' => 'Margarin Blue Band 200g', 'kategori' => 'Minyak & Margarin', 'satuan' => 'Pcs', 'stok' => 75],
            
            // Gula & Pemanis
            ['kode' => 'GLA001', 'nama' => 'Gula Pasir Gulaku 1kg', 'kategori' => 'Gula & Pemanis', 'satuan' => 'Pack', 'stok' => 250],
            ['kode' => 'GLA002', 'nama' => 'Gula Merah Jawa 500g', 'kategori' => 'Gula & Pemanis', 'satuan' => 'Pack', 'stok' => 40],
            ['kode' => 'GLA003', 'nama' => 'Madu TJ 250ml', 'kategori' => 'Gula & Pemanis', 'satuan' => 'Botol', 'stok' => 20],
            
            // Tepung & Premix
            ['kode' => 'TPG001', 'nama' => 'Tepung Terigu Segitiga Biru 1kg', 'kategori' => 'Tepung & Premix', 'satuan' => 'Pack', 'stok' => 180],
            ['kode' => 'TPG002', 'nama' => 'Tepung Terigu Cakra Kembar 1kg', 'kategori' => 'Tepung & Premix', 'satuan' => 'Pack', 'stok' => 100],
            ['kode' => 'TPG003', 'nama' => 'Tepung Maizena Maizenaku 150g', 'kategori' => 'Tepung & Premix', 'satuan' => 'Pack', 'stok' => 60],
            ['kode' => 'TPG004', 'nama' => 'Tepung Bumbu Sajiku 80g', 'kategori' => 'Tepung & Premix', 'satuan' => 'Sachet', 'stok' => 150],
            
            // Bumbu & Rempah
            ['kode' => 'BMB001', 'nama' => 'Kecap Manis ABC 600ml', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Botol', 'stok' => 120],
            ['kode' => 'BMB002', 'nama' => 'Kecap Manis Bango Refill 550ml', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Pack', 'stok' => 100],
            ['kode' => 'BMB003', 'nama' => 'Saos Sambal ABC 335ml', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Botol', 'stok' => 90],
            ['kode' => 'BMB004', 'nama' => 'Royco Kaldu Ayam 230g', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Pack', 'stok' => 85],
            ['kode' => 'BMB005', 'nama' => 'Masako Rasa Sapi 250g', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Pack', 'stok' => 70],
            ['kode' => 'BMB006', 'nama' => 'Bumbu Racik Indofood Rendang', 'kategori' => 'Bumbu & Rempah', 'satuan' => 'Sachet', 'stok' => 55],
            
            // Mie & Pasta
            ['kode' => 'MIE001', 'nama' => 'Indomie Goreng Original', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pcs', 'stok' => 500],
            ['kode' => 'MIE002', 'nama' => 'Indomie Goreng Rendang', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pcs', 'stok' => 350],
            ['kode' => 'MIE003', 'nama' => 'Indomie Kuah Soto', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pcs', 'stok' => 400],
            ['kode' => 'MIE004', 'nama' => 'Mie Sedaap Goreng', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pcs', 'stok' => 300],
            ['kode' => 'MIE005', 'nama' => 'Sarimi Isi 2 Rasa Ayam', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pcs', 'stok' => 200],
            ['kode' => 'MIE006', 'nama' => 'Bihun Jagung Rose Brand 250g', 'kategori' => 'Mie & Pasta', 'satuan' => 'Pack', 'stok' => 45],
            
            // Minuman
            ['kode' => 'MNM001', 'nama' => 'Aqua Botol 600ml', 'kategori' => 'Minuman', 'satuan' => 'Pcs', 'stok' => 480],
            ['kode' => 'MNM002', 'nama' => 'Aqua Galon 19L', 'kategori' => 'Minuman', 'satuan' => 'Pcs', 'stok' => 25],
            ['kode' => 'MNM003', 'nama' => 'Teh Botol Sosro 450ml', 'kategori' => 'Minuman', 'satuan' => 'Pcs', 'stok' => 240],
            ['kode' => 'MNM004', 'nama' => 'Kopi Kapal Api Special Mix', 'kategori' => 'Minuman', 'satuan' => 'Sachet', 'stok' => 600],
            ['kode' => 'MNM005', 'nama' => 'Susu Ultra Milk 1L Coklat', 'kategori' => 'Minuman', 'satuan' => 'Pcs', 'stok' => 60],
            ['kode' => 'MNM006', 'nama' => 'Susu Kental Manis Frisian Flag 385g', 'kategori' => 'Minuman', 'satuan' => 'Pcs', 'stok' => 100],
            ['kode' => 'MNM007', 'nama' => 'Teh Celup Sariwangi 25 bags', 'kategori' => 'Minuman', 'satuan' => 'Pack', 'stok' => 80],
            
            // Snack & Cemilan
            ['kode' => 'SNK001', 'nama' => 'Chitato Rasa Sapi Panggang 68g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pcs', 'stok' => 120],
            ['kode' => 'SNK002', 'nama' => 'Lays Classic 68g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pcs', 'stok' => 100],
            ['kode' => 'SNK003', 'nama' => 'Oreo Original 137g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pack', 'stok' => 85],
            ['kode' => 'SNK004', 'nama' => 'Roma Kelapa 300g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pack', 'stok' => 70],
            ['kode' => 'SNK005', 'nama' => 'Tango Wafer Coklat 176g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pack', 'stok' => 55],
            ['kode' => 'SNK006', 'nama' => 'Permen Kopiko 150g', 'kategori' => 'Snack & Cemilan', 'satuan' => 'Pack', 'stok' => 65],
            
            // Sabun & Deterjen
            ['kode' => 'SBN001', 'nama' => 'Rinso Anti Noda 800g', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Pack', 'stok' => 90],
            ['kode' => 'SBN002', 'nama' => 'Daia Deterjen 850g', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Pack', 'stok' => 75],
            ['kode' => 'SBN003', 'nama' => 'So Klin Softergent 800g', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Pack', 'stok' => 60],
            ['kode' => 'SBN004', 'nama' => 'Sabun Lifebuoy 75g', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Pcs', 'stok' => 200],
            ['kode' => 'SBN005', 'nama' => 'Sabun Lux 80g', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Pcs', 'stok' => 150],
            ['kode' => 'SBN006', 'nama' => 'Molto Pewangi 800ml', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Botol', 'stok' => 45],
            ['kode' => 'SBN007', 'nama' => 'Sunlight Cuci Piring 755ml', 'kategori' => 'Sabun & Deterjen', 'satuan' => 'Botol', 'stok' => 80],
            
            // Perawatan Tubuh
            ['kode' => 'PRW001', 'nama' => 'Shampo Clear Men 340ml', 'kategori' => 'Perawatan Tubuh', 'satuan' => 'Botol', 'stok' => 40],
            ['kode' => 'PRW002', 'nama' => 'Shampo Sunsilk Hijab 340ml', 'kategori' => 'Perawatan Tubuh', 'satuan' => 'Botol', 'stok' => 35],
            ['kode' => 'PRW003', 'nama' => 'Pasta Gigi Pepsodent 190g', 'kategori' => 'Perawatan Tubuh', 'satuan' => 'Pcs', 'stok' => 100],
            ['kode' => 'PRW004', 'nama' => 'Sikat Gigi Formula Medium', 'kategori' => 'Perawatan Tubuh', 'satuan' => 'Pcs', 'stok' => 80],
            ['kode' => 'PRW005', 'nama' => 'Deodoran Rexona Men 50ml', 'kategori' => 'Perawatan Tubuh', 'satuan' => 'Pcs', 'stok' => 30],
            
            // Produk Bayi
            ['kode' => 'BBY001', 'nama' => 'Susu SGM 1+ 400g', 'kategori' => 'Produk Bayi', 'satuan' => 'Pack', 'stok' => 25],
            ['kode' => 'BBY002', 'nama' => 'Popok Pampers M 32', 'kategori' => 'Produk Bayi', 'satuan' => 'Pack', 'stok' => 18],
            ['kode' => 'BBY003', 'nama' => 'Bedak Bayi Johnson 200g', 'kategori' => 'Produk Bayi', 'satuan' => 'Pcs', 'stok' => 22],
            ['kode' => 'BBY004', 'nama' => 'Minyak Telon Konicare 125ml', 'kategori' => 'Produk Bayi', 'satuan' => 'Botol', 'stok' => 20],
            
            // Rokok & Korek
            ['kode' => 'RKK001', 'nama' => 'Gudang Garam Filter', 'kategori' => 'Rokok & Korek', 'satuan' => 'Pack', 'stok' => 200],
            ['kode' => 'RKK002', 'nama' => 'Djarum Super 16', 'kategori' => 'Rokok & Korek', 'satuan' => 'Pack', 'stok' => 180],
            ['kode' => 'RKK003', 'nama' => 'Sampoerna Mild 16', 'kategori' => 'Rokok & Korek', 'satuan' => 'Pack', 'stok' => 150],
            ['kode' => 'RKK004', 'nama' => 'Korek Api Cricket', 'kategori' => 'Rokok & Korek', 'satuan' => 'Pcs', 'stok' => 100],
        ];
        
        $pcsUnit = $units->get('Pcs');
        $productCount = 0;
        
        foreach ($products as $product) {
            $category = $categories->get($product['kategori']);
            $unit = $units->get($product['satuan']) ?? $pcsUnit;
            
            if (!$category || !$unit) {
                $this->command->warn("  Skipping {$product['nama']}: missing category or unit");
                continue;
            }
            
            $barangId = DB::table('barang')->insertGetId([
                'toko_id' => $this->tokoId,
                'kode_barang' => $product['kode'],
                'nama_barang' => $product['nama'],
                'jenis_barang_id' => $category->id,
                'satuan_terkecil_id' => $unit->id,
                'keterangan' => null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            
            // Create barang_satuan
            DB::table('barang_satuan')->insert([
                'barang_id' => $barangId,
                'satuan_id' => $unit->id,
                'konversi_satuan_terkecil' => 1,
                'is_satuan_terkecil' => 'ya',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            
            // Create initial stock
            DB::table('gudang_stock')->insert([
                'gudang_id' => $this->gudangId,
                'barang_id' => $barangId,
                'jumlah' => $product['stok'],
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            
            $productCount++;
        }
        
        $this->command->info('  Created ' . $productCount . ' products with stock');
    }

    private function createPembelian(): void
    {
        $this->command->info('Creating purchase transactions...');
        
        $existing = DB::table('pembelian')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Purchases already exist, skipping...');
            return;
        }
        
        $suppliers = DB::table('supplier')->where('toko_id', $this->tokoId)->get();
        $barangs = DB::table('barang')->where('toko_id', $this->tokoId)->get();
        
        if ($suppliers->isEmpty() || $barangs->isEmpty()) {
            $this->command->warn('  No suppliers or products found');
            return;
        }
        
        $count = 0;
        
        // IMPORTANT: Create purchase for EVERY product first to ensure pembelian_detail exists
        // This is required because penjualan_detail.pembelian_detail_id is NOT NULL
        $this->command->info('  Creating initial purchases for all products...');
        
        $tanggalAwal = Carbon::now()->subDays(95); // Before sales period
        $supplier = $suppliers->first();
        
        // Create one big initial purchase for all products
        $nomorPembelian = 'PB' . $tanggalAwal->format('Ymd') . '000';
        
        $pembelianId = DB::table('pembelian')->insertGetId([
            'toko_id' => $this->tokoId,
            'user_id' => $this->userId,
            'supplier_id' => $supplier->id,
            'nomor_pembelian' => $nomorPembelian,
            'tanggal_pembelian' => $tanggalAwal,
            'total_harga' => 0,
            'status' => 'lunas',
            'keterangan' => 'Pembelian awal stok',
            'kembalian' => 0,
            'created_at' => $tanggalAwal,
            'updated_at' => $tanggalAwal,
        ]);
        
        $totalHarga = 0;
        
        foreach ($barangs as $barang) {
            $satuan = DB::table('barang_satuan')->where('barang_id', $barang->id)->first();
            if (!$satuan) continue;
            
            $jumlah = rand(500, 1000); // Large initial stock
            $hargaSatuan = rand(5000, 50000);
            $subtotal = $jumlah * $hargaSatuan;
            $totalHarga += $subtotal;
            
            DB::table('pembelian_detail')->insert([
                'pembelian_id' => $pembelianId,
                'barang_id' => $barang->id,
                'satuan_id' => $satuan->satuan_id,
                'gudang_id' => $this->gudangId,
                'jumlah' => $jumlah,
                'konversi_satuan_terkecil' => $satuan->konversi_satuan_terkecil,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
                'rencana_harga_jual' => $hargaSatuan * 1.2,
                'diskon' => 0,
                'biaya_lain' => 0,
                'created_at' => $tanggalAwal,
                'updated_at' => $tanggalAwal,
            ]);
        }
        
        DB::table('pembelian')->where('id', $pembelianId)->update(['total_harga' => $totalHarga]);
        $count++;
        
        // Create additional random purchases over last 3 months
        for ($i = 0; $i < 29; $i++) {
            $daysAgo = rand(1, 90);
            $tanggal = Carbon::now()->subDays($daysAgo);
            $supplier = $suppliers->random();
            
            $nomorPembelian = 'PB' . $tanggal->format('Ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            $pembelianId = DB::table('pembelian')->insertGetId([
                'toko_id' => $this->tokoId,
                'user_id' => $this->userId,
                'supplier_id' => $supplier->id,
                'nomor_pembelian' => $nomorPembelian,
                'tanggal_pembelian' => $tanggal,
                'total_harga' => 0,
                'status' => 'lunas',
                'keterangan' => 'Pembelian dari ' . $supplier->nama_supplier,
                'kembalian' => 0,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);
            
            // Add 3-8 items per purchase
            $itemCount = rand(3, 8);
            $totalHarga = 0;
            $selectedBarangs = $barangs->random(min($itemCount, $barangs->count()));
            
            foreach ($selectedBarangs as $barang) {
                $satuan = DB::table('barang_satuan')->where('barang_id', $barang->id)->first();
                if (!$satuan) continue;
                
                $jumlah = rand(20, 100);
                $hargaSatuan = rand(5000, 50000);
                $subtotal = $jumlah * $hargaSatuan;
                $totalHarga += $subtotal;
                
                DB::table('pembelian_detail')->insert([
                    'pembelian_id' => $pembelianId,
                    'barang_id' => $barang->id,
                    'satuan_id' => $satuan->satuan_id,
                    'gudang_id' => $this->gudangId,
                    'jumlah' => $jumlah,
                    'konversi_satuan_terkecil' => $satuan->konversi_satuan_terkecil,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'rencana_harga_jual' => $hargaSatuan * 1.2,
                    'diskon' => 0,
                    'biaya_lain' => 0,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
            }
            
            DB::table('pembelian')->where('id', $pembelianId)->update(['total_harga' => $totalHarga]);
            $count++;
        }
        
        $this->command->info("  Created {$count} purchase transactions");
    }

    private function createPenjualan(): void
    {
        $this->command->info('Creating sales transactions...');
        
        $existing = DB::table('penjualan')->where('toko_id', $this->tokoId)->count();
        if ($existing > 0) {
            $this->command->info('  Sales already exist, skipping...');
            return;
        }
        
        $customers = DB::table('customer')->where('toko_id', $this->tokoId)->get();
        $barangs = DB::table('barang')->where('toko_id', $this->tokoId)->get();
        
        // Get pembelian_detail indexed by barang_id - we need this for FIFO
        $pembelianDetails = DB::table('pembelian_detail')
            ->join('pembelian', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->where('pembelian.toko_id', $this->tokoId)
            ->select('pembelian_detail.*')
            ->orderBy('pembelian_detail.id', 'asc') // FIFO order
            ->get()
            ->groupBy('barang_id');
        
        if ($barangs->isEmpty()) {
            $this->command->warn('  No products found');
            return;
        }
        
        if ($pembelianDetails->isEmpty()) {
            $this->command->warn('  No purchase details found. Run createPembelian first.');
            return;
        }
        
        // Only use barang that have pembelian_detail
        $barangsWithPurchase = $barangs->filter(function ($barang) use ($pembelianDetails) {
            return $pembelianDetails->has($barang->id);
        });
        
        if ($barangsWithPurchase->isEmpty()) {
            $this->command->warn('  No products with purchase records found.');
            return;
        }
        
        $this->command->info('  Products with purchase records: ' . $barangsWithPurchase->count());
        
        // Products that are best sellers
        $bestSellerCodes = ['MIE001', 'MIE002', 'MIE003', 'MNY001', 'GLA001', 'MNM001', 'MNM004', 'RKK001', 'RKK002', 'RKK003'];
        $bestSellerIds = $barangsWithPurchase->whereIn('kode_barang', $bestSellerCodes)->pluck('id')->toArray();
        
        // Products that are slow moving
        $slowMovingCodes = ['GLA003', 'BBY002', 'PRW005', 'MNM002'];
        $slowMovingIds = $barangsWithPurchase->whereIn('kode_barang', $slowMovingCodes)->pluck('id')->toArray();
        
        $count = 0;
        $now = Carbon::now();
        
        // Sales distribution: current month 200, last month 120, 2 months ago 80
        $salesDistribution = [
            ['start' => $now->copy()->startOfMonth(), 'end' => $now, 'count' => 200],
            ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth(), 'count' => 120],
            ['start' => $now->copy()->subMonths(2)->startOfMonth(), 'end' => $now->copy()->subMonths(2)->endOfMonth(), 'count' => 80],
        ];
        
        foreach ($salesDistribution as $period) {
            $daysInPeriod = $period['start']->diffInDays($period['end']) + 1;
            
            for ($t = 0; $t < $period['count']; $t++) {
                $randomDays = rand(0, max(0, $daysInPeriod - 1));
                $tanggal = $period['start']->copy()->addDays($randomDays);
                
                // Skip Sundays
                if ($tanggal->dayOfWeek === Carbon::SUNDAY) continue;
                
                // Random hour (7 AM - 9 PM)
                $hour = rand(7, 21);
                $tanggal->setTime($hour, rand(0, 59));
                
                $customer = $customers->isEmpty() ? null : $customers->random();
                
                $nomorPenjualan = 'PJ' . $tanggal->format('Ymd') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                
                $penjualanId = DB::table('penjualan')->insertGetId([
                    'toko_id' => $this->tokoId,
                    'user_id' => $this->userId,
                    'customer_id' => $customer?->id,
                    'nomor_penjualan' => $nomorPenjualan,
                    'tanggal_penjualan' => $tanggal,
                    'total_harga' => 0,
                    'status' => 'lunas',
                    'keterangan' => '',
                    'kembalian' => 0,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
                
                $items = [];
                
                // 70% chance include best seller
                if (rand(1, 100) <= 70 && !empty($bestSellerIds)) {
                    $items[] = $bestSellerIds[array_rand($bestSellerIds)];
                }
                
                // 5% chance slow-moving
                if (rand(1, 100) <= 5 && !empty($slowMovingIds)) {
                    $items[] = $slowMovingIds[array_rand($slowMovingIds)];
                }
                
                // Add 1-3 random items from products with purchase records
                $randomCount = rand(1, 3);
                $randomItems = $barangsWithPurchase->random(min($randomCount, $barangsWithPurchase->count()))->pluck('id')->toArray();
                $items = array_merge($items, $randomItems);
                $items = array_unique($items);
                
                if (empty($items)) {
                    $items = [$barangsWithPurchase->random()->id];
                }
                
                $totalHarga = 0;
                $hasValidDetail = false;
                
                foreach ($items as $barangId) {
                    $barang = $barangsWithPurchase->firstWhere('id', $barangId);
                    if (!$barang) continue;
                    
                    $satuan = DB::table('barang_satuan')->where('barang_id', $barang->id)->first();
                    if (!$satuan) continue;
                    
                    // Get pembelian_detail_id for FIFO - this MUST exist
                    $pembelianDetailCollection = $pembelianDetails->get($barangId);
                    if (!$pembelianDetailCollection || $pembelianDetailCollection->isEmpty()) {
                        continue; // Skip if no purchase record
                    }
                    
                    $pembelianDetail = $pembelianDetailCollection->first();
                    
                    // Quantity varies by product type
                    if (in_array($barangId, $bestSellerIds)) {
                        $jumlah = rand(2, 8);
                    } elseif (in_array($barangId, $slowMovingIds)) {
                        $jumlah = 1;
                    } else {
                        $jumlah = rand(1, 4);
                    }
                    
                    $hargaSatuan = rand(3000, 50000);
                    $subtotal = $jumlah * $hargaSatuan;
                    $totalHarga += $subtotal;
                    
                    DB::table('penjualan_detail')->insert([
                        'penjualan_id' => $penjualanId,
                        'pembelian_detail_id' => $pembelianDetail->id,
                        'barang_id' => $barang->id,
                        'satuan_id' => $satuan->satuan_id,
                        'gudang_id' => $this->gudangId,
                        'jumlah' => $jumlah,
                        'konversi_satuan_terkecil' => $satuan->konversi_satuan_terkecil,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                        'profit' => rand(1000, 10000),
                        'diskon' => 0,
                        'biaya_lain' => 0,
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ]);
                    
                    $hasValidDetail = true;
                }
                
                if ($hasValidDetail) {
                    DB::table('penjualan')->where('id', $penjualanId)->update(['total_harga' => $totalHarga]);
                    $count++;
                } else {
                    // Delete penjualan if no valid details
                    DB::table('penjualan')->where('id', $penjualanId)->delete();
                }
            }
        }
        
        $this->command->info("  Created {$count} sales transactions");
    }
}
