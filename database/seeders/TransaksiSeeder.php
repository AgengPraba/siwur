<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiSeeder extends Seeder
{
    /**
     * Seed realistic purchase and sales transactions for chatbot testing.
     * 
     * This seeder creates:
     * - Purchase transactions (pembelian) from suppliers
     * - Sales transactions (penjualan) to customers
     * - Market basket patterns (products often bought together)
     * - Slow-moving products (high stock, low sales)
     * - Best-selling products
     */
    public function run(): void
    {
        // Get toko_id (assuming first toko)
        $toko = DB::table('toko')->first();
        if (!$toko) {
            $this->command->error('No toko found! Please run other seeders first.');
            return;
        }
        $tokoId = $toko->id;

        // Get user (required for pembelian and penjualan)
        $user = DB::table('users')->first();
        if (!$user) {
            $this->command->error('No user found! Please create a user first.');
            return;
        }

        // Get related data
        $gudang = DB::table('gudang')->where('toko_id', $tokoId)->first();
        $suppliers = DB::table('supplier')->where('toko_id', $tokoId)->limit(3)->get();
        $customers = DB::table('customer')->where('toko_id', $tokoId)->limit(5)->get();
        $barangs = DB::table('barang')->where('toko_id', $tokoId)->get();

        if (!$gudang || $suppliers->isEmpty() || $barangs->isEmpty()) {
            $this->command->error('Missing required data! Run other seeders first.');
            return;
        }

        $this->command->info('Starting transaction seeding...');

        // Define product categories for realistic patterns
        $bestSellerIds = $barangs->take(5)->pluck('id')->toArray(); // Top 5 akan jadi best sellers
        $slowMovingIds = $barangs->slice(5, 3)->pluck('id')->toArray(); // 3 produk slow-moving
        $normalIds = $barangs->slice(8)->pluck('id')->toArray(); // Sisanya normal

        // Market basket pairs (produk yang sering dibeli bersamaan)
        $basketPairs = [];
        if (count($bestSellerIds) >= 4) {
            $basketPairs = [
                [$bestSellerIds[0], $bestSellerIds[1]], // Pair 1
                [$bestSellerIds[0], $bestSellerIds[2]], // Pair 2
                [$bestSellerIds[1], $bestSellerIds[3]], // Pair 3
                [$bestSellerIds[2], $bestSellerIds[3]], // Pair 4
            ];
        }

        // ===== STEP 1: PURCHASE TRANSACTIONS (Pembelian) =====
        $this->command->info('Creating purchase transactions...');
        
        $pembelianIds = [];
        $pembelianDetailData = [];
        
        // Create purchases over the last 90 days
        for ($i = 0; $i < 20; $i++) {
            $daysAgo = rand(1, 90);
            $tanggalPembelian = Carbon::now()->subDays($daysAgo);
            $supplier = $suppliers->random();
            
            $pembelianId = DB::table('pembelian')->insertGetId([
                'toko_id' => $tokoId,
                'user_id' => $user->id,
                'supplier_id' => $supplier->id,
                'nomor_pembelian' => 'PB' . $tokoId . $tanggalPembelian->format('ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'tanggal_pembelian' => $tanggalPembelian,
                'total_harga' => 0, // Will update after details
                'status' => 'lunas',
                'keterangan' => 'Pembelian rutin',
                'created_at' => $tanggalPembelian,
                'updated_at' => $tanggalPembelian,
            ]);
            
            $pembelianIds[] = $pembelianId;
            
            // Add 3-7 items per purchase
            $itemCount = rand(3, 7);
            $totalHarga = 0;
            
            $selectedBarangs = $barangs->random(min($itemCount, $barangs->count()));
            
            foreach ($selectedBarangs as $barang) {
                $satuan = DB::table('barang_satuan')
                    ->where('barang_id', $barang->id)
                    ->first();
                
                if (!$satuan) continue;
                
                $jumlah = rand(10, 100);
                $hargaSatuan = rand(5000, 50000);
                $subtotal = $jumlah * $hargaSatuan;
                $totalHarga += $subtotal;
                
                $pembelianDetailId = DB::table('pembelian_detail')->insertGetId([
                    'pembelian_id' => $pembelianId,
                    'barang_id' => $barang->id,
                    'satuan_id' => $satuan->satuan_id,
                    'gudang_id' => $gudang->id,
                    'jumlah' => $jumlah,
                    'konversi_satuan_terkecil' => $satuan->konversi_satuan_terkecil,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'created_at' => $tanggalPembelian,
                    'updated_at' => $tanggalPembelian,
                ]);
                
                $pembelianDetailData[] = [
                    'id' => $pembelianDetailId,
                    'barang_id' => $barang->id,
                    'jumlah_available' => $jumlah * $satuan->konversi_satuan_terkecil,
                    'harga_satuan' => $hargaSatuan,
                ];
                
                // Update stock
                $existingStock = DB::table('gudang_stock')
                    ->where('gudang_id', $gudang->id)
                    ->where('barang_id', $barang->id)
                    ->first();
                
                if ($existingStock) {
                    DB::table('gudang_stock')
                        ->where('id', $existingStock->id)
                        ->increment('jumlah', $jumlah * $satuan->konversi_satuan_terkecil);
                } else {
                    DB::table('gudang_stock')->insert([
                        'gudang_id' => $gudang->id,
                        'barang_id' => $barang->id,
                        'jumlah' => $jumlah * $satuan->konversi_satuan_terkecil,
                        'created_at' => $tanggalPembelian,
                        'updated_at' => $tanggalPembelian,
                    ]);
                }
            }
            
            // Update total pembelian
            DB::table('pembelian')
                ->where('id', $pembelianId)
                ->update(['total_harga' => $totalHarga]);
        }
        
        $this->command->info('✓ Created 20 purchase transactions');

        // ===== STEP 2: SALES TRANSACTIONS (Penjualan) =====
        $this->command->info('Creating sales transactions...');
        
        $transactionCount = 0;
        
        // Create sales: 70% di bulan berjalan, 30% di 2 bulan sebelumnya
        // Generate tanggal untuk setiap periode
        $now = Carbon::now();
        $salesDistribution = [
            // Bulan berjalan (1 Nov - 30 Nov): 200 transaksi
            ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth(), 'transactions' => 200],
            // Bulan lalu (1 Okt - 31 Okt): 100 transaksi
            ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth(), 'transactions' => 100],
            // 2 bulan lalu (1 Sep - 30 Sep): 50 transaksi
            ['start' => $now->copy()->subMonths(2)->startOfMonth(), 'end' => $now->copy()->subMonths(2)->endOfMonth(), 'transactions' => 50],
        ];
        
        foreach ($salesDistribution as $period) {
            $transactionsInPeriod = $period['transactions'];
            $startDate = $period['start'];
            $endDate = $period['end'];
            $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;
            
            for ($t = 0; $t < $transactionsInPeriod; $t++) {
                // Random date dalam periode
                $randomDays = rand(0, $totalDaysInPeriod - 1);
                $tanggal = $startDate->copy()->addDays($randomDays);
            
            // Lewati hari Minggu (asumsi toko tutup)
            if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }
            
            // Weekend has fewer transactions
            if ($tanggal->isWeekend()) {
                // Skip some weekend transactions
                if (rand(0, 2) == 0) continue;
            }
            
            $transactionCount++;
            $customer = $customers->isEmpty() ? null : $customers->random();
                
                // Peak hours: 9-11 AM and 3-5 PM
                $peakHour = rand(0, 10) < 7; // 70% chance peak hour
                if ($peakHour) {
                    $hour = rand(0, 1) == 0 ? rand(9, 11) : rand(15, 17);
                } else {
                    $hour = rand(7, 19); // 7 AM to 7 PM
                }
                $minute = rand(0, 59);
                
                $tanggalPenjualan = $tanggal->copy()->setTime($hour, $minute);
                
                // Generate unique nomor_penjualan
                $prefix = 'PJ' . $tokoId . $tanggalPenjualan->format('ymd');
                $counter = 1;
                do {
                    $nomorPenjualan = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
                    $exists = DB::table('penjualan')->where('nomor_penjualan', $nomorPenjualan)->exists();
                    $counter++;
                } while ($exists);
                
                $penjualanId = DB::table('penjualan')->insertGetId([
                    'toko_id' => $tokoId,
                    'user_id' => $user->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'nomor_penjualan' => $nomorPenjualan,
                    'tanggal_penjualan' => $tanggalPenjualan,
                    'total_harga' => 0,
                    'status' => 'lunas',
                    'keterangan' => '',
                    'created_at' => $tanggalPenjualan,
                    'updated_at' => $tanggalPenjualan,
                ]);
                
                $totalHarga = 0;
                $items = [];
                
                // Determine transaction pattern
                $useBasketPattern = rand(1, 100) <= 40 && !empty($basketPairs); // 40% chance
                $buyBestSeller = rand(1, 100) <= 70; // 70% chance include best seller
                $buySlowMoving = rand(1, 100) <= 5; // 5% chance buy slow-moving
                
                // Best sellers - frequently purchased
                if ($buyBestSeller && !empty($bestSellerIds)) {
                    $items[] = $bestSellerIds[array_rand($bestSellerIds)];
                }
                
                // Market basket pattern
                if ($useBasketPattern) {
                    $pair = $basketPairs[array_rand($basketPairs)];
                    $items = array_merge($items, $pair);
                }
                
                // Slow-moving - rarely purchased
                if ($buySlowMoving && !empty($slowMovingIds)) {
                    $items[] = $slowMovingIds[array_rand($slowMovingIds)];
                }
                
                // Add random normal items
                $normalCount = rand(1, 3);
                if (!empty($normalIds)) {
                    $randomNormal = array_rand(array_flip($normalIds), min($normalCount, count($normalIds)));
                    $items = array_merge($items, is_array($randomNormal) ? $randomNormal : [$randomNormal]);
                }
                
                // Remove duplicates
                $items = array_unique($items);
                
                // If no items selected, pick random
                if (empty($items)) {
                    $items = [$barangs->random()->id];
                }
                
                // Create penjualan details
                foreach ($items as $barangId) {
                    $barang = $barangs->firstWhere('id', $barangId);
                    if (!$barang) continue;
                    
                    $satuan = DB::table('barang_satuan')
                        ->where('barang_id', $barang->id)
                        ->first();
                    
                    if (!$satuan) continue;
                    
                    // Check stock
                    $stock = DB::table('gudang_stock')
                        ->where('gudang_id', $gudang->id)
                        ->where('barang_id', $barang->id)
                        ->first();
                    
                    if (!$stock || $stock->jumlah <= 0) continue;
                    
                    // Find available pembelian_detail for FIFO
                    $pembelianDetail = collect($pembelianDetailData)
                        ->where('barang_id', $barang->id)
                        ->where('jumlah_available', '>', 0)
                        ->first();
                    
                    if (!$pembelianDetail) continue;
                    
                    // Quantity: best sellers more, slow-moving less
                    if (in_array($barangId, $bestSellerIds)) {
                        $jumlah = rand(3, 10); // Best sellers: more quantity
                    } elseif (in_array($barangId, $slowMovingIds)) {
                        $jumlah = rand(1, 2); // Slow-moving: less quantity
                    } else {
                        $jumlah = rand(1, 5); // Normal
                    }
                    
                    $jumlahTerkecil = $jumlah * $satuan->konversi_satuan_terkecil;
                    
                    // Check if enough stock
                    if ($stock->jumlah < $jumlahTerkecil) {
                        $jumlah = floor($stock->jumlah / $satuan->konversi_satuan_terkecil);
                        $jumlahTerkecil = $jumlah * $satuan->konversi_satuan_terkecil;
                    }
                    
                    if ($jumlah <= 0) continue;
                    
                    // Pricing
                    $hargaBeli = $pembelianDetail['harga_satuan'];
                    $markup = rand(15, 40); // 15-40% markup
                    $hargaJual = $hargaBeli + ($hargaBeli * $markup / 100);
                    $hargaJual = ceil($hargaJual / 100) * 100; // Round to nearest 100
                    
                    $subtotal = $hargaJual * $jumlah;
                    $profit = $subtotal - ($hargaBeli * $jumlah);
                    
                    $totalHarga += $subtotal;
                    
                    DB::table('penjualan_detail')->insert([
                        'penjualan_id' => $penjualanId,
                        'barang_id' => $barang->id,
                        'satuan_id' => $satuan->satuan_id,
                        'gudang_id' => $gudang->id,
                        'pembelian_detail_id' => $pembelianDetail['id'],
                        'jumlah' => $jumlah,
                        'konversi_satuan_terkecil' => $satuan->konversi_satuan_terkecil,
                        'harga_satuan' => $hargaJual,
                        'diskon' => 0,
                        'biaya_lain' => 0,
                        'subtotal' => $subtotal,
                        'profit' => $profit,
                        'created_at' => $tanggalPenjualan,
                        'updated_at' => $tanggalPenjualan,
                    ]);
                    
                    // Update stock
                    DB::table('gudang_stock')
                        ->where('id', $stock->id)
                        ->decrement('jumlah', $jumlahTerkecil);
                    
                    // Update pembelian_detail availability
                    foreach ($pembelianDetailData as &$detail) {
                        if ($detail['id'] == $pembelianDetail['id']) {
                            $detail['jumlah_available'] -= $jumlahTerkecil;
                            break;
                        }
                    }
                }
                
                // Update total penjualan
                DB::table('penjualan')
                    ->where('id', $penjualanId)
                    ->update(['total_harga' => $totalHarga]);
            }
        }
        
        $this->command->info("✓ Created {$transactionCount} sales transactions");
        
        // ===== SUMMARY =====
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('Transaction Seeding Summary:');
        $this->command->info('========================================');
        $this->command->info("Purchases: 20 transactions");
        $this->command->info("Sales: {$transactionCount} transactions");
        $this->command->info("Date range: Last 90 days");
        $this->command->info('');
        $this->command->info('Transaction Patterns:');
        $this->command->info("- Best sellers: " . count($bestSellerIds) . " products (high volume)");
        $this->command->info("- Slow-moving: " . count($slowMovingIds) . " products (low sales)");
        $this->command->info("- Market basket pairs: " . count($basketPairs) . " patterns");
        $this->command->info('');
        $this->command->info('✅ Transaction seeding completed!');
        $this->command->info('');
        $this->command->info('Now you can test chatbot with questions like:');
        $this->command->info('- "Produk apa yang paling laku?"');
        $this->command->info('- "Ada berapa barang di gudang?"');
        $this->command->info('- "Produk apa yang tidak laku?"');
        $this->command->info('- "Sarankan paket bundling"');
        $this->command->info('- "Produk apa yang sering dibeli bersamaan?"');
    }
}
