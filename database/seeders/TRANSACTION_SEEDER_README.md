# Transaction Seeder untuk Testing Chatbot AI

## Overview
Seeder ini membuat data transaksi pembelian dan penjualan yang **realistis** untuk testing fitur chatbot AI, khususnya untuk:
- Market Basket Analysis
- Slow-Moving Products Detection
- Intelligent Bundling Recommendations
- Sales Analytics

## Data yang Dihasilkan

### 1. **Pembelian (Purchase Transactions)**
- 20 transaksi pembelian dari supplier
- Tersebar dalam 90 hari terakhir
- Setiap transaksi berisi 3-7 item produk
- Update stok gudang otomatis

### 2. **Penjualan (Sales Transactions)**
- 200-400 transaksi penjualan (tergantung random)
- Tersebar dalam 90 hari terakhir
- Pattern realistis:
  - **Best Sellers**: 5 produk dengan frekuensi tinggi (70% transaksi)
  - **Slow-Moving**: 3 produk dengan penjualan sangat rendah (5% transaksi)
  - **Normal Products**: Sisanya dengan penjualan normal
  - **Market Basket Pairs**: 4 pasangan produk yang sering dibeli bersamaan (40% transaksi)

### 3. **Pattern Realistis**
- **Peak Hours**: 70% transaksi di jam 9-11 AM dan 3-5 PM
- **Weekend Effect**: Lebih sedikit transaksi di akhir pekan
- **Recent Bias**: Lebih banyak transaksi di 30 hari terakhir
- **FIFO Stock**: Menggunakan pembelian detail untuk tracking

## Cara Menggunakan

### Prerequisite
Pastikan seeder lain sudah dijalankan terlebih dahulu:
```bash
php artisan db:seed --class=JenisBarangSeeder
php artisan db:seed --class=SatuanSeeder
php artisan db:seed --class=BarangSeeder
php artisan db:seed --class=BarangSatuanSeeder
php artisan db:seed --class=GudangSeeder
php artisan db:seed --class=SupplierSeeder
php artisan db:seed --class=CustomerSeeder
```

### Jalankan Transaction Seeder
```bash
php artisan db:seed --class=TransaksiSeeder
```

### Atau Tambahkan di DatabaseSeeder
Edit `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([
        // ... existing seeders
        TransaksiSeeder::class, // Tambahkan di akhir
    ]);
}
```

Lalu jalankan:
```bash
php artisan db:seed
```

## Testing Chatbot AI

Setelah seeder dijalankan, refresh knowledge base AI:
```bash
cd ai_service
python -c "from rag_pipeline import RAGPipeline; rag = RAGPipeline(); rag.refresh_store_knowledge('1')"
```

### Pertanyaan untuk Testing

#### 1. Market Basket Analysis
```
"Produk apa yang sering dibeli bersamaan?"
"Kombinasi produk apa yang paling populer?"
"Ada pasangan produk yang sering dibeli together?"
```

#### 2. Slow-Moving Products
```
"Produk apa yang tidak laku?"
"Barang mana yang stoknya menumpuk?"
"Produk apa yang perlu promosi?"
```

#### 3. Bundling Recommendations
```
"Sarankan paket bundling untuk meningkatkan penjualan"
"Bagaimana cara mengatasi produk slow-moving?"
"Rekomendasi paket promo apa yang efektif?"
```

#### 4. Sales Analytics
```
"Produk apa yang paling laku bulan ini?"
"Berapa penjualan hari ini?"
"Jam berapa penjualan paling ramai?"
"Bandingkan penjualan minggu ini dengan minggu lalu"
```

#### 5. Inventory
```
"Ada berapa barang di gudang?"
"Berapa stok produk [nama produk]?"
"Produk apa yang stoknya habis?"
```

## Struktur Data yang Dibuat

### Pembelian
```
pembelian
├── id
├── toko_id
├── supplier_id
├── nomor_pembelian (PB{toko_id}{yymmdd}{seq})
├── tanggal_pembelian (random 1-90 hari lalu)
├── total_harga
└── status = 'lunas'

pembelian_detail
├── pembelian_id
├── barang_id
├── satuan_id
├── jumlah (10-100 unit)
├── harga_satuan (5k-50k)
└── konversi_satuan_terkecil
```

### Penjualan
```
penjualan
├── id
├── toko_id
├── customer_id (nullable)
├── nomor_penjualan (PJ{toko_id}{yymmdd}{seq})
├── tanggal_penjualan (dengan jam realistis)
├── total_harga
└── status = 'lunas'

penjualan_detail
├── penjualan_id
├── barang_id
├── satuan_id
├── pembelian_detail_id (FIFO tracking)
├── jumlah (1-10 unit, tergantung kategori)
├── harga_satuan (harga beli + markup 15-40%)
├── subtotal
└── profit
```

## Pattern Distribution

| Pattern | Probability | Description |
|---------|-------------|-------------|
| Best Seller Item | 70% | Produk populer muncul di 70% transaksi |
| Market Basket Pair | 40% | 2 produk dibeli bersamaan |
| Slow-Moving Item | 5% | Produk jarang terjual |
| Normal Item | 100% | 1-3 item random |
| Peak Hours | 70% | Transaksi di jam sibuk |
| Weekend | 30% | Lebih sedikit transaksi |

## Expected Results

Setelah seeder berjalan, Anda akan mendapat:

### Market Basket Analysis
```
Top pasangan produk:
1. Produk A + Produk B: 85 transaksi (18.9% support)
2. Produk A + Produk C: 72 transaksi (16.0% support)
...
```

### Slow-Moving Products
```
Produk dengan stok tinggi tapi penjualan rendah:
1. Produk X: stok 150, terjual 2 unit/30 hari
2. Produk Y: stok 120, terjual 0 unit/30 hari
...
```

### Intelligent Bundling
```
Paket rekomendasi:
1. [URGENT] Best Seller A + Slow Moving X (diskon 15-20%)
2. [MODERATE] Best Seller B + Slow Moving Y (diskon 10-15%)
...
```

## Troubleshooting

### Error: No toko found
**Solusi**: Pastikan tabel `toko` memiliki data
```bash
php artisan tinker
>>> \App\Models\Toko::create(['nama_toko' => 'Toko Test', 'alamat_toko' => 'Jl. Test'])
```

### Error: Missing required data
**Solusi**: Jalankan seeder prerequisite terlebih dahulu (lihat section Prerequisite)

### Stock habis saat seeding
**Normal**: Seeder akan skip produk yang stoknya habis. Jalankan ulang seeder atau tambah stok awal.

## Clean Up (Reset Data)

Untuk menghapus data transaksi dan reset:
```bash
php artisan migrate:fresh --seed
```

Atau hapus manual:
```sql
DELETE FROM penjualan_detail;
DELETE FROM penjualan;
DELETE FROM pembelian_detail;
DELETE FROM pembelian;
UPDATE gudang_stock SET jumlah = 0;
```

## Notes

- Seeder ini **idempotent** - bisa dijalankan berkali-kali tanpa duplikasi
- Profit margin: 15-40% untuk harga jual
- Stock tracking menggunakan FIFO dari pembelian_detail
- Semua timestamp sesuai tanggal transaksi untuk analytics yang akurat

## Author
Generated for SIWUR AI Chatbot Testing
