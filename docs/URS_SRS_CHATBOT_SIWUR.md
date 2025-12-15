# URS dan SRS Chatbot RAG SIWUR

---

## 1. USER REQUIREMENTS SPECIFICATION (URS)

### 1.1 URS Pemilik Toko

| Kode | Kebutuhan |
|------|-----------|
| URS-OWN-001 | Pengguna dapat menanyakan informasi stok barang secara real-time |
| URS-OWN-002 | Pengguna dapat menanyakan daftar dan informasi supplier |
| URS-OWN-003 | Pengguna dapat menanyakan daftar dan informasi customer |
| URS-OWN-004 | Pengguna dapat menanyakan informasi gudang dan kapasitasnya |
| URS-OWN-005 | Pengguna dapat menanyakan kategori barang dan jumlah produk per kategori |
| URS-OWN-006 | Pengguna dapat menanyakan ringkasan penjualan (total transaksi, omzet, rata-rata) |
| URS-OWN-007 | Pengguna dapat menanyakan ringkasan pembelian terbaru |
| URS-OWN-008 | Pengguna dapat menanyakan produk terlaris dalam periode tertentu |
| URS-OWN-009 | Pengguna dapat menanyakan analisis profit dan margin keuntungan |
| URS-OWN-010 | Pengguna dapat menanyakan rekomendasi produk bundling berdasarkan Market Basket Analysis |
| URS-OWN-011 | Pengguna dapat menanyakan peluang cross-selling antar produk |
| URS-OWN-012 | Pengguna dapat menanyakan produk dengan stok rendah yang perlu restock |
| URS-OWN-013 | Pengguna dapat menanyakan panduan penggunaan fitur sistem SIWUR |
| URS-OWN-014 | Pengguna dapat menanyakan informasi toko (nama, alamat) |
| URS-OWN-015 | Pengguna dapat menanyakan hak akses dan izin per role pengguna |

### 1.2 URS Akuntan

| Kode | Kebutuhan |
|------|-----------|
| URS-ACC-001 | Pengguna dapat menanyakan total penjualan dalam periode tertentu |
| URS-ACC-002 | Pengguna dapat menanyakan total profit dan margin keuntungan |
| URS-ACC-003 | Pengguna dapat menanyakan rata-rata nilai transaksi |
| URS-ACC-004 | Pengguna dapat menanyakan daftar transaksi penjualan terbaru |
| URS-ACC-005 | Pengguna dapat menanyakan daftar transaksi pembelian terbaru |
| URS-ACC-006 | Pengguna dapat menanyakan perbandingan pembelian vs penjualan |
| URS-ACC-007 | Pengguna dapat menanyakan panduan cara mengakses laporan keuangan |
| URS-ACC-008 | Pengguna dapat menanyakan panduan cara melihat laporan profit |
| URS-ACC-009 | Pengguna dapat menanyakan informasi customer untuk keperluan pembayaran |
| URS-ACC-010 | Pengguna dapat menanyakan informasi supplier untuk keperluan hutang |

### 1.3 URS Staff Gudang

| Kode | Kebutuhan |
|------|-----------|
| URS-WH-001 | Pengguna dapat menanyakan stok barang tertentu secara spesifik |
| URS-WH-002 | Pengguna dapat menanyakan stok barang di gudang tertentu |
| URS-WH-003 | Pengguna dapat menanyakan daftar barang dengan stok rendah (menipis) |
| URS-WH-004 | Pengguna dapat menanyakan total item dan stok per gudang |
| URS-WH-005 | Pengguna dapat menanyakan daftar kategori barang |
| URS-WH-006 | Pengguna dapat menanyakan barang berdasarkan kategori tertentu |
| URS-WH-007 | Pengguna dapat menanyakan panduan cara melakukan stock opname |
| URS-WH-008 | Pengguna dapat menanyakan panduan cara input barang masuk (pembelian) |
| URS-WH-009 | Pengguna dapat menanyakan panduan cara transfer stok antar gudang |
| URS-WH-010 | Pengguna dapat mencari barang berdasarkan nama atau kode |

---

## 2. SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

### 2.1 SRS Frontend (Chatbot Interface)

| Kode | Kebutuhan |
|------|-----------|
| SRS-FE-001 | Sistem harus menampilkan tombol floating untuk membuka/menutup chatbot |
| SRS-FE-002 | Sistem harus menyimpan state buka/tutup chatbot di localStorage |
| SRS-FE-003 | Sistem harus menampilkan header chatbot dengan judul dan tombol close |
| SRS-FE-004 | Sistem harus menampilkan tombol untuk menghapus riwayat chat |
| SRS-FE-005 | Sistem harus menampilkan area pesan dengan scroll otomatis |
| SRS-FE-006 | Sistem harus membedakan tampilan pesan user (kanan) dan bot (kiri) |
| SRS-FE-007 | Sistem harus menampilkan nama pengirim dan waktu pada setiap pesan |
| SRS-FE-008 | Sistem harus menampilkan indikator typing (loading dots) saat bot memproses |
| SRS-FE-009 | Sistem harus menyediakan input field untuk mengetik pertanyaan |
| SRS-FE-010 | Sistem harus menyediakan tombol kirim dengan spinner saat memproses |
| SRS-FE-011 | Sistem harus melakukan auto-scroll ke pesan terbaru |
| SRS-FE-012 | Sistem harus menyimpan riwayat chat di session browser |
| SRS-FE-013 | Sistem harus menampilkan pesan pembuka saat chat pertama kali dibuka |
| SRS-FE-014 | Sistem harus responsif pada berbagai ukuran layar (mobile dan desktop) |

### 2.2 SRS Backend (Laravel Controller & Livewire)

| Kode | Kebutuhan |
|------|-----------|
| SRS-BE-001 | Sistem harus memvalidasi input pertanyaan (required, max 2048 karakter) |
| SRS-BE-002 | Sistem harus memvalidasi session_id (required) |
| SRS-BE-003 | Sistem harus me-resolve toko_id dari user context yang sedang login |
| SRS-BE-004 | Sistem harus memiliki fallback chain untuk resolve toko_id (request → session → akses → owned toko) |
| SRS-BE-005 | Sistem harus mengembalikan error jika toko_id tidak ditemukan |
| SRS-BE-006 | Sistem harus menyimpan pesan user ke tabel chat_messages |
| SRS-BE-007 | Sistem harus menyimpan jawaban bot ke tabel chat_messages |
| SRS-BE-008 | Sistem harus mengirim request ke AI Service via HTTP POST |
| SRS-BE-009 | Sistem harus mengatur timeout 60 detik untuk request ke AI Service |
| SRS-BE-010 | Sistem harus menangani error dari AI Service dengan response yang informatif |
| SRS-BE-011 | Sistem harus mencatat log untuk setiap request dan response |
| SRS-BE-012 | Sistem harus mengelola session ID unik per sesi chat |
| SRS-BE-013 | Sistem harus menyimpan riwayat pesan di session Laravel |
| SRS-BE-014 | Sistem harus mendukung fitur clear chat (reset session) |

### 2.3 SRS AI Service (Python FastAPI)

| Kode | Kebutuhan |
|------|-----------|
| SRS-AI-001 | Sistem harus menyediakan endpoint POST /chat untuk menerima pertanyaan |
| SRS-AI-002 | Sistem harus menyediakan endpoint GET /health untuk health check |
| SRS-AI-003 | Sistem harus menyediakan endpoint POST /mba untuk Market Basket Analysis |
| SRS-AI-004 | Sistem harus melakukan query routing untuk menentukan datasource (database/manual/analytics/hybrid) |
| SRS-AI-005 | Sistem harus mengambil data dari database MySQL berdasarkan konteks pertanyaan |
| SRS-AI-006 | Sistem harus melakukan semantic search di knowledge base menggunakan ChromaDB |
| SRS-AI-007 | Sistem harus menjalankan Market Basket Analysis untuk pertanyaan analytics |
| SRS-AI-008 | Sistem harus melakukan document grading untuk memfilter dokumen yang relevan |
| SRS-AI-009 | Sistem harus melakukan query rewriting jika dokumen tidak ditemukan |
| SRS-AI-010 | Sistem harus menghasilkan jawaban menggunakan Google Gemini LLM |
| SRS-AI-011 | Sistem harus melakukan hallucination check pada jawaban yang dihasilkan |
| SRS-AI-012 | Sistem harus regenerate jawaban jika terdeteksi hallucination |
| SRS-AI-013 | Sistem harus memfilter data berdasarkan toko_id (multi-tenant isolation) |
| SRS-AI-014 | Sistem harus mengembalikan response dalam format JSON dengan field success, answer, datasource, documents_count |
| SRS-AI-015 | Sistem harus mencatat processing_time_ms untuk setiap request |
| SRS-AI-016 | Sistem harus memiliki fallback embedding (Gemini → OpenAI → Local) |
| SRS-AI-017 | Sistem harus melakukan auto-rebuild index jika manual file berubah |
| SRS-AI-018 | Sistem harus memiliki fallback keyword search jika vector search gagal |
| SRS-AI-019 | Sistem harus mendukung max_retries untuk error handling |
| SRS-AI-020 | Sistem harus menghasilkan jawaban dalam Bahasa Indonesia |

---

## 3. TRACEABILITY MATRIX

### 3.1 URS to SRS Mapping

| URS Code | Related SRS |
|----------|-------------|
| URS-OWN-001 s/d URS-OWN-006 | SRS-AI-005, SRS-AI-013 |
| URS-OWN-007 s/d URS-OWN-009 | SRS-AI-005, SRS-AI-007 |
| URS-OWN-010, URS-OWN-011 | SRS-AI-003, SRS-AI-007 |
| URS-OWN-012 | SRS-AI-005 (low_stock query) |
| URS-OWN-013 s/d URS-OWN-015 | SRS-AI-006 (manual search) |
| URS-ACC-001 s/d URS-ACC-006 | SRS-AI-005 (sales/profit queries) |
| URS-ACC-007 s/d URS-ACC-010 | SRS-AI-006 (manual search) |
| URS-WH-001 s/d URS-WH-006 | SRS-AI-005 (stock queries) |
| URS-WH-007 s/d URS-WH-010 | SRS-AI-006 (manual search) |

### 3.2 SRS Component Dependencies

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   SRS-FE-*      │────►│   SRS-BE-*      │────►│   SRS-AI-*      │
│   (Frontend)    │     │   (Backend)     │     │   (AI Service)  │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │                       │
        ▼                       ▼                       ▼
   localStorage            MySQL DB              ChromaDB +
   Session Storage      chat_messages           Gemini API
```

---

*Dokumen URS & SRS Chatbot RAG SIWUR*
*Versi: 1.0 | Tanggal: Desember 2025*
