# 4.2.3 Desain Basis Pengetahuan Asisten Virtual SIWUR

Pada subbab ini dijelaskan mengenai desain basis pengetahuan yang digunakan oleh asisten virtual (chatbot) berbasis Retrieval-Augmented Generation (RAG) pada sistem SIWUR. Basis pengetahuan merupakan komponen fundamental yang menentukan kemampuan chatbot dalam memberikan jawaban yang akurat dan kontekstual kepada pengguna. Desain basis pengetahuan pada sistem ini terdiri dari tiga komponen utama, yaitu basis data MySQL untuk data operasional, sumber pengetahuan manual untuk panduan penggunaan, dan basis pengetahuan vektor untuk pencarian semantik.

---

## a. Desain Basis Data MySQL

Basis data MySQL pada sistem SIWUR berfungsi sebagai sumber pengetahuan dinamis (*live data source*) yang menyimpan data operasional toko secara *real-time*. Komponen chatbot RAG mengakses basis data ini untuk menjawab pertanyaan pengguna yang berkaitan dengan informasi aktual seperti stok barang, transaksi penjualan, data pelanggan, dan informasi operasional lainnya.

### a.1. Arsitektur Basis Data untuk Fitur Chatbot

Arsitektur basis data yang diakses oleh chatbot mengikuti prinsip *multi-tenant isolation*, dimana setiap toko memiliki data yang terisolasi berdasarkan kolom `toko_id`. Prinsip ini memastikan bahwa chatbot hanya dapat mengakses data yang relevan dengan konteks toko dari pengguna yang sedang berinteraksi, sehingga menjaga keamanan dan privasi data antar tenant.

Berikut adalah diagram relasi antar tabel yang digunakan oleh fitur chatbot:

**Gambar 4.x: Entity Relationship Diagram (ERD) Basis Data untuk Chatbot**

```
                              ┌─────────────────┐
                              │      toko       │
                              │─────────────────│
                              │ PK id           │
                              │    nama_toko    │
                              │    alamat_toko  │
                              │ FK user_id      │
                              └────────┬────────┘
                                       │
           ┌───────────────┬───────────┼───────────┬───────────────┐
           │               │           │           │               │
           ▼               ▼           ▼           ▼               ▼
┌─────────────────┐ ┌─────────────┐ ┌─────────┐ ┌─────────┐ ┌──────────────┐
│  jenis_barang   │ │   satuan    │ │  gudang │ │customer │ │   supplier   │
│─────────────────│ │─────────────│ │─────────│ │─────────│ │──────────────│
│ PK id           │ │ PK id       │ │ PK id   │ │ PK id   │ │ PK id        │
│    nama_jenis   │ │    nama     │ │    nama │ │    nama │ │    nama      │
│ FK toko_id      │ │ FK toko_id  │ │FK toko  │ │FK toko  │ │ FK toko_id   │
└────────┬────────┘ └──────┬──────┘ └────┬────┘ └────┬────┘ └──────────────┘
         │                 │             │           │
         │                 │             │           │
         └────────┬────────┘             │           │
                  │                      │           │
                  ▼                      │           │
         ┌─────────────────┐             │           │
         │     barang      │             │           │
         │─────────────────│             │           │
         │ PK id           │             │           │
         │    kode_barang  │             │           │
         │    nama_barang  │             │           │
         │ FK jenis_id     │             │           │
         │ FK satuan_id    │             │           │
         │ FK toko_id      │             │           │
         └────────┬────────┘             │           │
                  │                      │           │
         ┌────────┴────────┐             │           │
         │                 │             │           │
         ▼                 ▼             ▼           ▼
┌─────────────────┐ ┌──────────────────────────────────────┐
│  gudang_stock   │ │            penjualan                 │
│─────────────────│ │──────────────────────────────────────│
│ PK id           │ │ PK id                                │
│ FK gudang_id    │ │    nomor_penjualan                   │
│ FK barang_id    │ │    tanggal_penjualan                 │
│    jumlah       │ │ FK customer_id ◄─────────────────────┤
└─────────────────┘ │ FK toko_id                           │
                    │    total_harga, status               │
                    └───────────────────┬──────────────────┘
                                        │
                                        ▼
                    ┌──────────────────────────────────────┐
                    │         penjualan_detail             │
                    │──────────────────────────────────────│
                    │ PK id                                │
                    │ FK penjualan_id                      │
                    │ FK barang_id                         │
                    │    jumlah, harga, subtotal, profit   │
                    └──────────────────────────────────────┘

                    ┌──────────────────────────────────────┐
                    │          chat_messages               │
                    │──────────────────────────────────────│
                    │ PK id                                │
                    │    session_id                        │
                    │ FK toko_id                           │
                    │    role (user/bot)                   │
                    │    content                           │
                    └──────────────────────────────────────┘
```

### a.2. Struktur Tabel yang Diakses Chatbot

Berdasarkan implementasi aktual pada sistem, chatbot RAG mengakses 11 tabel utama dalam basis data MySQL. Berikut adalah *Data Definition Language* (DDL) dari masing-masing tabel tersebut:

#### 1. Tabel `toko` (Informasi Toko)

Tabel ini menyimpan informasi identitas toko yang menjadi konteks utama dalam setiap interaksi chatbot.

```sql
CREATE TABLE toko (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(255) NOT NULL,
    logo_toko VARCHAR(255) NULL,
    alamat_toko VARCHAR(255) NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 2. Tabel `chat_messages` (Riwayat Percakapan)

Tabel ini menyimpan seluruh riwayat percakapan antara pengguna dengan chatbot untuk keperluan konteks percakapan dan analisis penggunaan.

```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    role ENUM('user', 'bot') NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_session_id (session_id),
    FOREIGN KEY (toko_id) REFERENCES toko(id) ON DELETE CASCADE
);
```

**Tabel 4.x: Penjelasan Kolom Tabel `chat_messages`**

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| `id` | BIGINT UNSIGNED | Primary key dengan auto-increment |
| `session_id` | VARCHAR(255) | Identifier unik untuk mengelompokkan sesi percakapan |
| `toko_id` | BIGINT UNSIGNED | Foreign key ke tabel toko untuk isolasi data multi-tenant |
| `role` | ENUM | Penanda peran pengirim pesan ('user' untuk pengguna, 'bot' untuk chatbot) |
| `content` | TEXT | Isi pesan dalam format teks |
| `created_at` | TIMESTAMP | Waktu pembuatan record |
| `updated_at` | TIMESTAMP | Waktu pembaruan record terakhir |

#### 3. Tabel `jenis_barang` (Kategori Produk)

```sql
CREATE TABLE jenis_barang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_jenis_barang VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 4. Tabel `satuan` (Unit Pengukuran)

```sql
CREATE TABLE satuan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_satuan VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 5. Tabel `barang` (Data Produk)

Tabel ini menyimpan informasi produk yang merupakan entitas utama yang sering ditanyakan melalui chatbot.

```sql
CREATE TABLE barang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(255) NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    jenis_barang_id BIGINT UNSIGNED NOT NULL,
    satuan_terkecil_id BIGINT UNSIGNED NOT NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_kode_toko (kode_barang, toko_id),
    FOREIGN KEY (jenis_barang_id) REFERENCES jenis_barang(id),
    FOREIGN KEY (satuan_terkecil_id) REFERENCES satuan(id),
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 6. Tabel `gudang` (Lokasi Penyimpanan)

```sql
CREATE TABLE gudang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_gudang VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 7. Tabel `gudang_stock` (Stok per Gudang)

Tabel ini menyimpan informasi jumlah stok setiap barang di masing-masing gudang.

```sql
CREATE TABLE gudang_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gudang_id BIGINT UNSIGNED NOT NULL,
    barang_id BIGINT UNSIGNED NOT NULL,
    jumlah DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_gudang_barang (gudang_id, barang_id),
    FOREIGN KEY (gudang_id) REFERENCES gudang(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id)
);
```

#### 8. Tabel `customer` (Data Pelanggan)

```sql
CREATE TABLE customer (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_customer VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NULL,
    no_hp VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL,
    is_opname BOOLEAN DEFAULT FALSE,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 9. Tabel `supplier` (Data Pemasok)

```sql
CREATE TABLE supplier (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_supplier VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NULL,
    no_hp VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL,
    is_opname BOOLEAN DEFAULT FALSE,
    toko_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 10. Tabel `penjualan` (Transaksi Penjualan)

```sql
CREATE TABLE penjualan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_penjualan VARCHAR(255) NOT NULL UNIQUE,
    tanggal_penjualan DATETIME NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    toko_id BIGINT UNSIGNED NOT NULL,
    total_harga DECIMAL(15, 2) NOT NULL DEFAULT 0,
    status ENUM('belum_bayar', 'belum_lunas', 'lunas') DEFAULT 'belum_bayar',
    keterangan VARCHAR(255) NULL,
    kembalian DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customer(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (toko_id) REFERENCES toko(id)
);
```

#### 11. Tabel `penjualan_detail` (Detail Transaksi)

```sql
CREATE TABLE penjualan_detail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penjualan_id BIGINT UNSIGNED NOT NULL,
    pembelian_detail_id BIGINT UNSIGNED NOT NULL,
    barang_id BIGINT UNSIGNED NOT NULL,
    satuan_id BIGINT UNSIGNED NOT NULL,
    gudang_id BIGINT UNSIGNED NOT NULL,
    harga_satuan DECIMAL(15, 2) NOT NULL,
    diskon DECIMAL(15, 2) DEFAULT 0,
    biaya_lain DECIMAL(15, 2) DEFAULT 0,
    jumlah DECIMAL(15, 2) NOT NULL,
    konversi_satuan_terkecil DECIMAL(15, 2) DEFAULT 0,
    subtotal DECIMAL(15, 2) NOT NULL,
    profit DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id),
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (satuan_id) REFERENCES satuan(id),
    FOREIGN KEY (gudang_id) REFERENCES gudang(id)
);
```

### a.3. Query Template untuk Chatbot RAG

Sistem chatbot menggunakan *query template* yang telah didefinisikan sebelumnya untuk mengambil data dari basis data secara efisien dan aman. Pendekatan ini dipilih untuk menghindari risiko *SQL injection* dan memastikan konsistensi format data yang dihasilkan.

**Tabel 4.x: Daftar Query Template yang Digunakan Chatbot**

| No | Nama Template | Fungsi | Contoh Pertanyaan Pengguna |
|----|---------------|--------|---------------------------|
| 1 | `toko_info` | Mengambil informasi identitas toko | "Apa nama toko saya?" |
| 2 | `stock_info` | Mengambil informasi stok seluruh barang | "Tampilkan semua stok barang" |
| 3 | `low_stock` | Menampilkan barang dengan stok di bawah ambang batas | "Barang apa yang hampir habis?" |
| 4 | `search_product` | Mencari produk berdasarkan nama atau kode | "Berapa stok Indomie?" |
| 5 | `categories` | Menampilkan daftar kategori dan jumlah produk | "Ada kategori apa saja?" |
| 6 | `product_by_category` | Menampilkan produk berdasarkan kategori | "Tampilkan barang kategori Minuman" |
| 7 | `warehouses` | Menampilkan informasi gudang dan total stok | "Ada berapa gudang?" |
| 8 | `customers` | Menampilkan daftar pelanggan | "Siapa saja pelanggan saya?" |
| 9 | `suppliers` | Menampilkan daftar supplier | "Tampilkan daftar supplier" |
| 10 | `recent_sales` | Menampilkan transaksi penjualan terbaru | "Transaksi terakhir apa?" |
| 11 | `recent_purchases` | Menampilkan transaksi pembelian terbaru | "Pembelian terakhir dari mana?" |
| 12 | `top_selling_products` | Mengambil produk terlaris dalam periode tertentu | "Produk apa yang paling laku?" |
| 13 | `sales_summary` | Ringkasan penjualan (total transaksi, nilai) | "Berapa total penjualan bulan ini?" |
| 14 | `profit_summary` | Ringkasan keuntungan dan margin | "Berapa profit minggu ini?" |
| 15 | `item_pairs_frequency` | Analisis pasangan produk yang sering dibeli bersamaan | "Produk apa yang sering dibeli bersamaan?" |
| 16 | `transaction_baskets` | Data keranjang transaksi untuk Market Basket Analysis | (Digunakan internal untuk analisis) |

**Contoh Implementasi Query Template:**

```python
# File: ai_service/core/database.py

QUERY_TEMPLATES = {
    "stock_info": """
        SELECT 
            b.kode_barang,
            b.nama_barang,
            jb.nama_jenis_barang as kategori,
            gs.jumlah as stok,
            s.nama_satuan as satuan,
            g.nama_gudang as gudang
        FROM gudang_stock gs
        JOIN barang b ON gs.barang_id = b.id
        JOIN satuan s ON b.satuan_terkecil_id = s.id
        JOIN jenis_barang jb ON b.jenis_barang_id = jb.id
        JOIN gudang g ON gs.gudang_id = g.id
        WHERE b.toko_id = :toko_id
        ORDER BY b.nama_barang
    """,
    
    "low_stock": """
        SELECT 
            b.kode_barang,
            b.nama_barang,
            gs.jumlah as stok,
            s.nama_satuan as satuan
        FROM gudang_stock gs
        JOIN barang b ON gs.barang_id = b.id
        JOIN satuan s ON b.satuan_terkecil_id = s.id
        WHERE b.toko_id = :toko_id AND gs.jumlah < 50
        ORDER BY gs.jumlah ASC
    """,
    
    "top_selling_products": """
        SELECT 
            b.nama_barang,
            SUM(pd.jumlah) as total_terjual,
            SUM(pd.subtotal) as total_nilai,
            COUNT(DISTINCT pd.penjualan_id) as jumlah_transaksi
        FROM penjualan_detail pd
        JOIN penjualan p ON pd.penjualan_id = p.id
        JOIN barang b ON pd.barang_id = b.id
        WHERE p.toko_id = :toko_id
            AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY b.id
        ORDER BY total_terjual DESC
        LIMIT 10
    """,
    
    "profit_summary": """
        SELECT 
            SUM(pd.subtotal) as total_penjualan,
            SUM(pd.profit) as total_profit,
            (SUM(pd.profit) / NULLIF(SUM(pd.subtotal), 0)) * 100 as margin_persen
        FROM penjualan_detail pd
        JOIN penjualan p ON pd.penjualan_id = p.id
        WHERE p.toko_id = :toko_id
            AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    """,
    
    "item_pairs_frequency": """
        SELECT 
            b1.nama_barang as item_a,
            b2.nama_barang as item_b,
            COUNT(*) as frequency
        FROM penjualan_detail pd1
        JOIN penjualan_detail pd2 ON pd1.penjualan_id = pd2.penjualan_id 
            AND pd1.barang_id < pd2.barang_id
        JOIN penjualan p ON pd1.penjualan_id = p.id
        JOIN barang b1 ON pd1.barang_id = b1.id
        JOIN barang b2 ON pd2.barang_id = b2.id
        WHERE p.toko_id = :toko_id
            AND p.tanggal_penjualan >= DATE_SUB(NOW(), INTERVAL :days DAY)
        GROUP BY pd1.barang_id, pd2.barang_id
        HAVING frequency >= 3
        ORDER BY frequency DESC
        LIMIT 20
    """
}
```

### a.4. Mekanisme Koneksi dan Eksekusi Query

Koneksi ke basis data MySQL dikelola menggunakan library SQLAlchemy dengan mekanisme *connection pooling* untuk efisiensi penggunaan resource. Berikut adalah implementasinya:

```python
# File: ai_service/core/database.py

from sqlalchemy import create_engine, text
from sqlalchemy.pool import QueuePool

engine = None

def get_engine():
    """Inisialisasi engine database dengan connection pooling"""
    global engine
    if engine is None:
        engine = create_engine(
            settings.database_url,
            poolclass=QueuePool,
            pool_size=5,          # Jumlah koneksi tetap dalam pool
            max_overflow=10,      # Koneksi tambahan saat pool penuh
            pool_pre_ping=True,   # Validasi koneksi sebelum digunakan
        )
    return engine

def execute_query(query: str, params: dict) -> List[Dict]:
    """Eksekusi query dan kembalikan hasil sebagai list dictionary"""
    with get_db_connection() as conn:
        result = conn.execute(text(query), params or {})
        columns = result.keys()
        rows = result.fetchall()
        return [dict(zip(columns, row)) for row in rows]

def get_context_from_db(toko_id: int, query_type: str, **kwargs) -> str:
    """Ambil konteks terformat dari database untuk RAG"""
    if query_type not in QUERY_TEMPLATES:
        return ""
    
    params = {"toko_id": toko_id, **kwargs}
    results = execute_query(QUERY_TEMPLATES[query_type], params)
    
    if not results:
        return f"Tidak ada data {query_type} ditemukan."
    
    # Format hasil menjadi string konteks
    context = f"Data {query_type.replace('_', ' ').title()}:\n"
    for row in results:
        context += "- " + ", ".join(f"{k}: {v}" for k, v in row.items()) + "\n"
    
    return context
```

---

## b. Sumber Pengetahuan Manual

Sumber pengetahuan manual merupakan dokumen teks terstruktur yang berisi panduan lengkap penggunaan sistem SIWUR. Dokumen ini berfungsi sebagai basis pengetahuan statis yang digunakan chatbot untuk menjawab pertanyaan prosedural tentang cara menggunakan fitur-fitur sistem.

### b.1. Lokasi dan Format File

| Atribut | Nilai |
|---------|-------|
| **Lokasi File** | `ai_service/general_manuals/siwur_complete_manual.txt` |
| **Format** | Plain Text (.txt) |
| **Encoding** | UTF-8 |
| **Ukuran** | ~38.000 karakter |

Format *plain text* dipilih dengan pertimbangan berikut:
1. Mudah diproses oleh komponen *text splitter* tanpa parsing khusus
2. Tidak memerlukan library tambahan seperti untuk PDF atau DOCX
3. Ukuran file lebih kecil sehingga efisien untuk di-load ke memori
4. Mudah di-update dan kompatibel dengan sistem *version control*

### b.2. Struktur dan Organisasi Dokumen

Dokumen manual SIWUR disusun dengan struktur hierarkis yang terdiri dari 10 bagian utama sebagai berikut:

**Tabel 4.x: Struktur Dokumen Manual SIWUR**

| No | Bagian | Deskripsi Konten |
|----|--------|------------------|
| 1 | Tinjauan Sistem | Gambaran umum SIWUR, fitur utama, arsitektur sistem |
| 2 | Peran dan Izin Pengguna | Penjelasan 4 role: Pemilik, Staff Gudang, Kasir, Akuntan |
| 3 | Fitur Dashboard | Komponen statistik, tombol aksi cepat, transaksi tertunda |
| 4 | Manajemen Data Utama | Panduan CRUD: Satuan, Jenis Barang, Barang, Supplier, Customer, Gudang |
| 5 | Fitur Transaksi | Panduan: Pembelian, Penjualan, Retur Pembelian, Retur Penjualan |
| 6 | Fitur Laporan | Laporan Stok Gudang, Pembayaran, Keuntungan |
| 7 | Stock Opname | Prosedur penghitungan dan penyesuaian stok fisik |
| 8 | Manajemen Pengguna | Panduan membuat dan mengelola akun pengguna |
| 9 | Pintasan Keyboard | Daftar shortcut untuk form penjualan dan pembelian |
| 10 | Matriks Izin | Tabel lengkap hak akses per role untuk setiap fitur |

### b.3. Konvensi Format Penulisan

Dokumen manual menggunakan format penulisan yang konsisten untuk memudahkan proses *chunking* dan ekstraksi informasi oleh sistem RAG:

```text
================================================================================
4. MANAJEMEN DATA UTAMA
================================================================================

4.1. UNIT (SATUAN)
------------------
Lokasi: Menu > Data Induk > Satuan
URL: /satuan

Deskripsi: Menentukan satuan ukuran untuk barang (misalnya, pcs, kg, kotak).

Fitur:
- Daftar semua unit dengan pencarian dan pagination
- Buat unit baru
- Edit unit yang ada
- Hapus unit (jika tidak digunakan dalam item)

CARA MEMBUAT UNIT BARU:
Langkah 1: Navigasi ke /satuan
Langkah 2: Klik tombol "Tambah Satuan"
Langkah 3: Masukkan nama unit (misalnya, "Kilogram")
Langkah 4: Tambahkan deskripsi secara opsional
Langkah 5: Klik "Simpan" untuk menyimpan
```

**Konvensi yang digunakan:**
- Judul bagian utama: Menggunakan pemisah `====` sepanjang 80 karakter
- Sub-bagian: Menggunakan pemisah `----`
- Prosedur: Menggunakan format "CARA [AKSI]:" diikuti "Langkah X:"
- Daftar fitur: Menggunakan bullet points dengan tanda "-"

### b.4. Jenis Pengetahuan dalam Manual

Manual SIWUR mencakup tiga jenis pengetahuan yang dapat dijawab oleh chatbot:

**1. Pengetahuan Prosedural**

Berisi langkah-langkah detail untuk melakukan tugas tertentu dalam sistem.

*Contoh pertanyaan:*
- "Bagaimana cara membuat penjualan baru?"
- "Bagaimana cara melakukan stock opname?"
- "Cara menambah barang baru?"

**2. Pengetahuan Konseptual**

Berisi penjelasan tentang fitur, konsep, dan cara kerja sistem.

*Contoh pertanyaan:*
- "Apa perbedaan role kasir dan akuntan?"
- "Apa yang dimaksud dengan multi-tenant?"
- "Fitur apa saja yang bisa diakses staff gudang?"

**3. Pengetahuan Referensi**

Berisi informasi rujukan cepat dalam bentuk tabel atau daftar.

*Contoh pertanyaan:*
- "Apa shortcut keyboard untuk form penjualan?"
- "Siapa yang bisa mengakses laporan profit?"

---

## c. Desain Basis Pengetahuan Vektor

Basis pengetahuan vektor merupakan komponen inti dari sistem RAG yang memungkinkan pencarian semantik (*semantic search*) terhadap dokumen manual. Sistem ini mengkonversi teks menjadi representasi vektor numerik (embedding) sehingga dapat dibandingkan berdasarkan kesamaan makna, bukan hanya kecocokan kata kunci.

### c.1. Arsitektur Sistem

Arsitektur basis pengetahuan vektor terdiri dari dua proses utama, yaitu proses *indexing* yang dilakukan secara offline dan proses *retrieval* yang dilakukan secara online saat pengguna mengajukan pertanyaan.

**Gambar 4.x: Arsitektur Basis Pengetahuan Vektor**

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PROSES INDEXING (Offline)                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────┐                                               │
│  │ siwur_complete_  │                                               │
│  │ manual.txt       │                                               │
│  └────────┬─────────┘                                               │
│           │                                                          │
│           ▼                                                          │
│  ┌──────────────────┐    ┌────────────────────────────────────┐     │
│  │   Text Splitter  │───>│  Document Chunks                   │     │
│  │ (RecursiveChar)  │    │  - chunk_size: 1000 chars          │     │
│  │                  │    │  - overlap: 200 chars              │     │
│  └──────────────────┘    │  - ~40-50 chunks total             │     │
│                          └─────────────┬──────────────────────┘     │
│                                        │                             │
│                                        ▼                             │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                 EMBEDDING MODEL (Fallback Chain)               │  │
│  │                                                                 │  │
│  │   ┌─────────────┐      ┌─────────────┐      ┌──────────────┐  │  │
│  │   │  1. Gemini  │─X───>│  2. OpenAI  │─X───>│  3. Local    │  │  │
│  │   │  (Primary)  │      │  (Fallback) │      │  (MiniLM)    │  │  │
│  │   └─────────────┘      └─────────────┘      └──────────────┘  │  │
│  │                                                                 │  │
│  └─────────────────────────────┬─────────────────────────────────┘  │
│                                │                                     │
│                                ▼                                     │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                    ChromaDB Vector Store                       │  │
│  │   Collection: "siwur_manual"                                   │  │
│  │   Persist: ai_service/manual_index/                           │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    PROSES RETRIEVAL (Online)                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐    ┌──────────────┐    ┌───────────────────────┐  │
│  │ User Query   │───>│  Embedding   │───>│    Query Vector       │  │
│  │ "Cara buat   │    │    Model     │    │    (768 dimensi)      │  │
│  │  penjualan?" │    │              │    │                       │  │
│  └──────────────┘    └──────────────┘    └───────────┬───────────┘  │
│                                                      │               │
│                                                      ▼               │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │              Cosine Similarity Search                          │  │
│  │                                                                 │  │
│  │   Query Vector ───> ChromaDB ───> Top-K Documents (k=4)       │  │
│  └───────────────────────────────────┬───────────────────────────┘  │
│                                      │                               │
│                                      ▼                               │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                  Retrieved Context                             │  │
│  │   [Chunk tentang penjualan, Chunk form penjualan, ...]        │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### c.2. Komponen Text Splitter

Text splitter bertugas memecah dokumen manual menjadi potongan-potongan (*chunks*) yang lebih kecil. Sistem menggunakan `RecursiveCharacterTextSplitter` dari library LangChain.

```python
# File: ai_service/core/embeddings.py

from langchain.text_splitter import RecursiveCharacterTextSplitter

text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=settings.chunk_size,        # 1000 karakter
    chunk_overlap=settings.chunk_overlap,  # 200 karakter
    separators=["\n\n", "\n", ". ", " "],  # Prioritas pemisah
)
```

**Tabel 4.x: Parameter Text Splitter**

| Parameter | Nilai | Penjelasan |
|-----------|-------|------------|
| `chunk_size` | 1000 | Ukuran maksimal setiap chunk dalam karakter |
| `chunk_overlap` | 200 | Jumlah karakter yang tumpang tindih antar chunk untuk menjaga konteks |
| `separators` | ["\n\n", "\n", ". ", " "] | Urutan prioritas pemisah, dimulai dari paragraf hingga spasi |

### c.3. Mekanisme Fallback Embedding

Sistem mengimplementasikan strategi *fallback chain* untuk model embedding guna menjamin ketersediaan layanan dalam berbagai kondisi:

**Tabel 4.x: Fallback Chain Embedding Model**

| Prioritas | Model | Provider | Dimensi Vektor | Kondisi Penggunaan |
|-----------|-------|----------|----------------|-------------------|
| 1 (Primary) | `models/embedding-001` | Google Gemini | 768 | Kondisi normal, API tersedia |
| 2 (Fallback 1) | `text-embedding-3-small` | OpenAI | 1536 | Jika Gemini quota habis atau error |
| 3 (Fallback 2) | `all-MiniLM-L6-v2` | HuggingFace Local | 384 | Jika semua API tidak tersedia |

```python
# File: ai_service/core/embeddings.py

def get_embeddings_with_fallback():
    """
    Mendapatkan embedding dengan fallback chain:
    Gemini -> OpenAI -> Local (sentence-transformers)
    """
    errors = []
    
    # Prioritas 1: Gemini
    try:
        embeddings, emb_type = get_gemini_embeddings()
        if embeddings:
            return embeddings, emb_type
    except QuotaExceededError as e:
        errors.append(f"Gemini: {e}")
        logger.warning("Gemini quota exceeded, trying fallback...")
    except Exception as e:
        errors.append(f"Gemini: {e}")
    
    # Prioritas 2: OpenAI
    try:
        embeddings, emb_type = get_openai_embeddings()
        if embeddings:
            return embeddings, emb_type
    except Exception as e:
        errors.append(f"OpenAI: {e}")
    
    # Prioritas 3: Local
    try:
        embeddings, emb_type = get_local_embeddings()
        if embeddings:
            return embeddings, emb_type
    except Exception as e:
        errors.append(f"Local: {e}")
    
    # Semua gagal
    logger.error("All embedding providers failed")
    return None, None
```

### c.4. Penyimpanan Vector Store

Sistem menggunakan **ChromaDB** sebagai vector database dengan fitur *persistent storage* sehingga index tidak perlu dibuat ulang setiap kali aplikasi dijalankan.

**Struktur Direktori Penyimpanan:**

```
ai_service/manual_index/
├── chroma.sqlite3              # Database utama ChromaDB
├── index_metadata.json         # Metadata index (hash, timestamp, dll)
└── [UUID]/
    ├── data_level0.bin         # Data vektor embedding
    ├── header.bin              # Header informasi
    ├── length.bin              # Informasi panjang
    └── link_lists.bin          # Struktur HNSW index
```

**Contoh Isi File `index_metadata.json`:**

```json
{
    "file_hash": "8f14e45fceea167a5a36dedd4bea2543",
    "embedding_type": "gemini",
    "doc_count": 45,
    "created_at": "2025-12-10T10:30:00.123456",
    "manual_path": "ai_service/general_manuals/siwur_complete_manual.txt"
}
```

### c.5. Mekanisme Smart Indexing

Sistem mengimplementasikan *smart indexing* untuk efisiensi dengan cara menghitung hash MD5 dari file manual dan membandingkannya dengan hash yang tersimpan. Index hanya di-rebuild jika ada perubahan pada file sumber.

```python
def should_rebuild_index() -> Tuple[bool, str]:
    """Mengecek apakah index perlu di-rebuild"""
    metadata = load_index_metadata()
    
    if metadata is None:
        return True, "No existing index found"
    
    # Cek apakah file manual berubah
    current_hash = get_manual_file_hash()
    if current_hash != metadata.get("file_hash", ""):
        return True, "Manual file has changed"
    
    # Cek apakah file index ada
    chroma_db = settings.chroma_persist_dir / "chroma.sqlite3"
    if not chroma_db.exists():
        return True, "Chroma database file missing"
    
    return False, f"Using existing index (created: {metadata.get('created_at')})"
```

### c.6. Proses Similarity Search

Pencarian dokumen relevan dilakukan menggunakan *cosine similarity* antara vektor query dengan vektor dokumen yang tersimpan:

```python
def search_manual(query: str, k: int = 4) -> List[Document]:
    """
    Mencari dokumen manual dengan similarity search.
    
    Args:
        query: Pertanyaan pengguna dalam bahasa natural
        k: Jumlah dokumen yang diambil (default: 4)
    
    Returns:
        List dokumen yang paling relevan
    """
    vectorstore = get_vectorstore()
    
    if vectorstore is None:
        logger.warning("Vectorstore not available")
        return fallback_keyword_search(query, k)
    
    try:
        results = vectorstore.similarity_search(query, k=k)
        return results
    except Exception as e:
        logger.error(f"Search error: {e}")
        return fallback_keyword_search(query, k)
```

### c.7. Fallback Keyword Search

Sebagai mekanisme *fallback* ketika vector store tidak tersedia, sistem menyediakan pencarian berbasis keyword sederhana:

```python
def fallback_keyword_search(query: str, k: int = 4) -> List[Document]:
    """Fallback pencarian berbasis keyword"""
    with open(settings.manual_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    sections = content.split("=" * 80)
    query_words = set(query.lower().split())
    
    # Hapus stopwords bahasa Indonesia
    stopwords = {"yang", "dan", "di", "ke", "dari", "untuk", "dengan", 
                 "adalah", "ini", "itu", "ada", "tidak", "apa", "bagaimana"}
    query_words -= stopwords
    
    # Scoring berdasarkan jumlah keyword match
    scored_sections = []
    for section in sections:
        section_lower = section.lower()
        score = sum(1 for word in query_words if word in section_lower)
        if score > 0:
            scored_sections.append((score, section[:1000]))
    
    scored_sections.sort(reverse=True, key=lambda x: x[0])
    return [Document(page_content=c) for _, c in scored_sections[:k]]
```

### c.8. Ringkasan Spesifikasi Teknis

**Tabel 4.x: Spesifikasi Teknis Basis Pengetahuan Vektor**

| Komponen | Spesifikasi |
|----------|-------------|
| Vector Database | ChromaDB v0.4+ dengan SQLite backend |
| Dimensi Embedding | 768 (Gemini) / 1536 (OpenAI) / 384 (Local) |
| Chunk Size | 1000 karakter |
| Chunk Overlap | 200 karakter |
| Default Top-K | 4 dokumen |
| Similarity Metric | Cosine Similarity |
| Index Persistence | SQLite + Binary Files |
| Fallback Search | Keyword-based dengan stopword removal |
| Smart Indexing | MD5 hash comparison |

---

## Kesimpulan Subbab 4.2.3

Desain basis pengetahuan asisten virtual SIWUR terdiri dari tiga komponen yang saling melengkapi dan terintegrasi:

1. **Basis Data MySQL** menyediakan akses ke data operasional *real-time* melalui 16 query template yang telah dioptimasi, mencakup 11 tabel utama dengan mekanisme *multi-tenant isolation* berdasarkan `toko_id`.

2. **Sumber Pengetahuan Manual** berupa dokumen teks terstruktur (~38.000 karakter) yang berisi 10 bagian panduan penggunaan sistem dengan format konsisten untuk memudahkan pemrosesan oleh text splitter.

3. **Basis Pengetahuan Vektor** menggunakan ChromaDB dengan strategi *fallback chain* embedding (Gemini → OpenAI → Local) untuk menjamin ketersediaan layanan pencarian semantik, dilengkapi dengan mekanisme *smart indexing* dan *fallback keyword search*.

Kombinasi ketiga komponen ini memungkinkan chatbot RAG memberikan jawaban yang akurat dan kontekstual, baik untuk pertanyaan faktual tentang data operasional toko (dari database) maupun pertanyaan prosedural tentang cara penggunaan sistem (dari manual), dengan mekanisme fallback yang menjamin ketersediaan layanan dalam berbagai kondisi operasional.
