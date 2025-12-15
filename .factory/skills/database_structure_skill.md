# Skill: Database Structure Siwur

## Deskripsi
Skill ini menjelaskan struktur database sistem SIWUR untuk keperluan RAG chatbot. Database menggunakan arsitektur multi-tenant dimana setiap toko (toko_id) memiliki data yang terisolasi.

## Arsitektur Multi-Tenant
- Semua tabel utama memiliki `toko_id` sebagai foreign key
- Data selalu di-filter berdasarkan `toko_id` aktif user
- Isolasi data antar toko dijamin oleh sistem

## Tabel-Tabel Utama

### 1. Toko (Master Store)
```sql
toko:
  - id (PK)
  - nama_toko (string)
  - logo_toko (string, nullable)
  - alamat_toko (string, nullable)
  - user_id (FK -> users)
```

### 2. Master Data

#### Satuan (Units)
```sql
satuan:
  - id (PK)
  - nama_satuan (string)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
```

#### Jenis Barang (Item Types/Categories)
```sql
jenis_barang:
  - id (PK)
  - nama_jenis (string)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
```

#### Barang (Items/Products)
```sql
barang:
  - id (PK)
  - kode_barang (string, unique per toko)
  - nama_barang (string)
  - keterangan (string, nullable)
  - jenis_barang_id (FK -> jenis_barang)
  - satuan_terkecil_id (FK -> satuan)
  - toko_id (FK -> toko)
```

#### Barang Satuan (Item Unit Conversions)
```sql
barang_satuan:
  - id (PK)
  - barang_id (FK -> barang)
  - satuan_id (FK -> satuan)
  - konversi (decimal) -- nilai konversi ke satuan terkecil
```

#### Aturan Harga Barang (Pricing Rules)
```sql
aturan_harga_barang:
  - id (PK)
  - barang_id (FK -> barang)
  - satuan_id (FK -> satuan)
  - minimal (integer)
  - maksimal (integer, nullable)
  - harga_jual (decimal)
```

#### Supplier (Vendors)
```sql
supplier:
  - id (PK)
  - nama_supplier (string)
  - alamat (string, nullable)
  - telepon (string, nullable)
  - email (string, nullable)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
```

#### Customer (Pelanggan)
```sql
customer:
  - id (PK)
  - nama_customer (string)
  - alamat (string, nullable)
  - telepon (string, nullable)
  - email (string, nullable)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
```

#### Gudang (Warehouses)
```sql
gudang:
  - id (PK)
  - nama_gudang (string)
  - alamat (string, nullable)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
```

### 3. Inventory

#### Gudang Stock
```sql
gudang_stock:
  - id (PK)
  - gudang_id (FK -> gudang)
  - barang_id (FK -> barang)
  - jumlah (decimal) -- dalam satuan terkecil
  - UNIQUE(gudang_id, barang_id)
```

#### Transaksi Gudang Stock
```sql
transaksi_gudang_stock:
  - id (PK)
  - gudang_id (FK -> gudang)
  - barang_id (FK -> barang)
  - jumlah (decimal)
  - tipe (enum: masuk, keluar)
  - referensi_type (string) -- polymorphic
  - referensi_id (integer)
  - toko_id (FK -> toko)
```

### 4. Transaksi Pembelian

#### Pembelian (Purchases)
```sql
pembelian:
  - id (PK)
  - nomor_pembelian (string, unique)
  - tanggal_pembelian (datetime)
  - supplier_id (FK -> supplier)
  - user_id (FK -> users)
  - keterangan (string, nullable)
  - total_harga (decimal)
  - status (enum: belum_bayar, belum_lunas, lunas)
  - informasi_tambahan (text, nullable)
  - balasan_informasi_tambahan (text, nullable)
  - toko_id (FK -> toko)
  - kembalian (decimal, nullable)
```

#### Pembelian Detail
```sql
pembelian_detail:
  - id (PK)
  - pembelian_id (FK -> pembelian)
  - barang_id (FK -> barang)
  - satuan_id (FK -> satuan)
  - gudang_id (FK -> gudang)
  - harga_satuan (decimal)
  - rencana_harga_jual (decimal)
  - diskon (decimal)
  - biaya_lain (decimal)
  - jumlah (decimal)
  - konversi_satuan_terkecil (decimal)
  - subtotal (decimal)
```

#### Pembayaran Pembelian
```sql
pembayaran_pembelian:
  - id (PK)
  - pembelian_id (FK -> pembelian)
  - jenis_pembayaran (string)
  - jumlah (decimal)
  - catatan (string, nullable)
```

### 5. Transaksi Penjualan

#### Penjualan (Sales)
```sql
penjualan:
  - id (PK)
  - nomor_penjualan (string, unique)
  - tanggal_penjualan (datetime)
  - customer_id (FK -> customer)
  - user_id (FK -> users)
  - keterangan (string, nullable)
  - toko_id (FK -> toko)
  - kembalian (decimal, nullable)
  - total_harga (decimal)
  - status (enum: belum_bayar, belum_lunas, lunas)
```

#### Penjualan Detail
```sql
penjualan_detail:
  - id (PK)
  - penjualan_id (FK -> penjualan)
  - pembelian_detail_id (FK -> pembelian_detail) -- FIFO tracking
  - barang_id (FK -> barang)
  - satuan_id (FK -> satuan)
  - gudang_id (FK -> gudang)
  - harga_satuan (decimal)
  - diskon (decimal)
  - biaya_lain (decimal)
  - jumlah (decimal)
  - konversi_satuan_terkecil (decimal)
  - subtotal (decimal)
  - profit (decimal)
```

#### Pembayaran Penjualan
```sql
pembayaran_penjualan:
  - id (PK)
  - penjualan_id (FK -> penjualan)
  - jenis_pembayaran (string)
  - jumlah (decimal)
  - catatan (string, nullable)
```

### 6. Retur

#### Retur Pembelian
```sql
retur_pembelian:
  - id (PK)
  - nomor_retur (string)
  - tanggal_retur (datetime)
  - pembelian_id (FK -> pembelian)
  - alasan (text)
  - status (enum: draft, progress, review, closed, cancel)
  - toko_id (FK -> toko)
```

#### Retur Penjualan
```sql
retur_penjualan:
  - id (PK)
  - nomor_retur (string)
  - tanggal_retur (datetime)
  - penjualan_id (FK -> penjualan)
  - alasan (text)
  - status (enum: draft, progress, review, closed, cancel)
  - toko_id (FK -> toko)
```

### 7. Stock Opname
```sql
stock_opname:
  - id (PK)
  - nomor_opname (string)
  - tanggal_opname (datetime)
  - gudang_id (FK -> gudang)
  - catatan (text, nullable)
  - toko_id (FK -> toko)

stock_opname_detail:
  - id (PK)
  - stock_opname_id (FK -> stock_opname)
  - barang_id (FK -> barang)
  - stok_sistem (decimal)
  - stok_fisik (decimal)
  - selisih (decimal)
```

## Query Patterns untuk RAG

### Query Stok Barang
```sql
SELECT b.kode_barang, b.nama_barang, gs.jumlah, s.nama_satuan, g.nama_gudang
FROM gudang_stock gs
JOIN barang b ON gs.barang_id = b.id
JOIN satuan s ON b.satuan_terkecil_id = s.id
JOIN gudang g ON gs.gudang_id = g.id
WHERE b.toko_id = :toko_id
```

### Query Penjualan dengan Detail
```sql
SELECT p.nomor_penjualan, p.tanggal_penjualan, c.nama_customer, p.total_harga
FROM penjualan p
JOIN customer c ON p.customer_id = c.id
WHERE p.toko_id = :toko_id
ORDER BY p.tanggal_penjualan DESC
```

### Query Produk Terlaris
```sql
SELECT b.nama_barang, SUM(pd.jumlah) as total_terjual
FROM penjualan_detail pd
JOIN penjualan p ON pd.penjualan_id = p.id
JOIN barang b ON pd.barang_id = b.id
WHERE p.toko_id = :toko_id
GROUP BY b.id
ORDER BY total_terjual DESC
```

## Tips untuk RAG Context Retrieval

1. **Selalu filter by toko_id** - Semua query harus menyertakan filter toko_id
2. **Join dengan nama** - Untuk konteks yang lebih baik, join tabel referensi untuk mendapatkan nama
3. **Agregasi untuk analytics** - Gunakan SUM, COUNT, AVG untuk pertanyaan analytical
4. **Date filtering** - Untuk pertanyaan temporal, gunakan filter tanggal yang tepat
5. **FIFO tracking** - Penjualan detail terhubung ke pembelian_detail untuk tracking HPP
