# Hasil Evaluasi RAGAS Chatbot SIWUR

**Tanggal Evaluasi:** 12 Desember 2025  
**Metode Evaluasi:** Simple Evaluation (Keyword Matching + Answer Relevancy)  
**Jumlah Pertanyaan:** 50 pertanyaan

---

## 1. Ringkasan Hasil Evaluasi

| Metrik | Skor | Interpretasi |
|--------|------|--------------|
| **Keyword Match** | 0.2652 (26.52%) | Fair - Menunjukkan kecocokan kata kunci antara jawaban dan ground truth |
| **Answer Relevancy** | 0.8000 (80.00%) | Good - Sebagian besar jawaban relevan dan tidak error |

---

## 2. Hasil Evaluasi per Kategori Pertanyaan

| Kategori | Jumlah Pertanyaan | Keyword Match | Deskripsi |
|----------|-------------------|---------------|-----------|
| **Factoid** | 10 | 0.3294 (32.94%) | Pertanyaan faktual sederhana |
| **Aggregation** | 5 | 0.3469 (34.69%) | Pertanyaan agregasi data |
| **Temporal** | 3 | 0.3077 (30.77%) | Pertanyaan berbasis waktu |
| **Comparative** | 8 | 0.2939 (29.39%) | Pertanyaan perbandingan |
| **Multi-hop** | 7 | 0.2630 (26.30%) | Pertanyaan multi-langkah |
| **Exploratory** | 5 | 0.1977 (19.77%) | Pertanyaan eksploratif |
| **Analytical** | 10 | 0.1916 (19.16%) | Pertanyaan analisis |
| **Recommendation** | 2 | 0.1048 (10.48%) | Pertanyaan rekomendasi |

---

## 3. Hasil Evaluasi per Tingkat Kesulitan

| Tingkat Kesulitan | Jumlah Pertanyaan |
|-------------------|-------------------|
| **Easy** | 14 pertanyaan |
| **Medium** | 20 pertanyaan |
| **Hard** | 16 pertanyaan |

---

## 4. Distribusi Datasource

Chatbot menggunakan adaptive RAG yang memilih datasource terbaik untuk menjawab pertanyaan:

| Datasource | Keterangan |
|------------|------------|
| **database** | Query langsung ke database MySQL |
| **analytics** | Analisis data penjualan dan market basket |
| **hybrid** | Kombinasi database dan vector search |

---

## 5. Analisis Performa per Kategori

### 5.1 Kategori dengan Performa Baik

1. **Aggregation (34.69%)** - Pertanyaan agregasi seperti total stok, jumlah kategori
   - Contoh: "Berapa total stok semua varian mie instan?" ✓ Dijawab dengan akurat

2. **Factoid (32.94%)** - Pertanyaan faktual sederhana
   - Contoh: "Berapa stok Indomie Goreng Original?" ✓ Dijawab: 500 Pcs (sesuai ground truth)
   - Contoh: "Berapa total jenis kategori barang?" ✓ Dijawab: 12 kategori (akurat)

3. **Temporal (30.77%)** - Pertanyaan berbasis waktu
   - Contoh: "Bagaimana performa penjualan bulan November?" ✓ Memberikan data 264 transaksi

### 5.2 Kategori yang Memerlukan Peningkatan

1. **Recommendation (10.48%)** - Skor terendah
   - Chatbot memberikan rekomendasi berbasis data market basket, namun berbeda format dengan ground truth
   
2. **Analytical (19.16%)** - Pertanyaan analisis kompleks
   - Beberapa pertanyaan tidak dapat dijawab karena keterbatasan data

3. **Exploratory (19.77%)** - Pertanyaan eksploratif
   - Masalah: Query supplier dan customer gagal karena kolom 'telepon' tidak ada di database

---

## 6. Temuan dan Masalah

### 6.1 Error Database Schema
Ditemukan inkonsistensi antara schema database dan query yang digunakan:

```
ERROR: Unknown column 'telepon' in 'field list' 
- Tabel: supplier
- Tabel: customer

ERROR: Unknown column 'g.alamat' in 'field list'
- Tabel: gudang
```

**Dampak:** Pertanyaan terkait supplier, customer, dan gudang tidak dapat dijawab dengan akurat.

### 6.2 API Key Issues
- Gemini API Key invalid untuk embeddings
- Fallback ke HuggingFace local embeddings (sentence-transformers/all-MiniLM-L6-v2)

---

## 7. Contoh Jawaban Akurat

| No | Pertanyaan | Jawaban Chatbot | Ground Truth | Status |
|----|------------|-----------------|--------------|--------|
| 1 | Berapa stok Indomie Goreng Original? | 500.00 Pcs | 500 pcs | ✓ AKURAT |
| 2 | Berapa stok Gula Pasir Gulaku 1kg? | 250.00 Pack | 250 pack | ✓ AKURAT |
| 3 | Berapa total jenis kategori barang? | 12 | 12 kategori | ✓ AKURAT |
| 4 | Berapa total stok semua varian mie instan? | 1793.00 | ~1795 pcs | ✓ AKURAT |
| 5 | Stok beras premium vs medium? | 150 vs 80 karung | 150 vs 80 karung | ✓ AKURAT |

---

## 8. Contoh Jawaban yang Memerlukan Perbaikan

| No | Pertanyaan | Masalah |
|----|------------|---------|
| 1 | Daftar semua supplier? | Error: kolom 'telepon' tidak ada |
| 2 | Alamat customer Warung Bu Tini? | Error: kolom 'telepon' tidak ada |
| 3 | Siapa supplier mie instan? | Tidak ada konteks - query error |
| 4 | Berapa jumlah gudang? | Error: kolom 'alamat' tidak ada |

---

## 9. Rekomendasi Perbaikan

### 9.1 Prioritas Tinggi
1. **Perbaiki Schema Database** - Sesuaikan query dengan schema database aktual
2. **Update Query SQL** - Hapus kolom yang tidak ada (telepon, alamat gudang)

### 9.2 Prioritas Medium
1. **Perbaiki API Key Gemini** - Untuk embeddings yang lebih akurat
2. **Tambah data relasi supplier-produk** - Agar dapat menjawab pertanyaan supplier per produk

### 9.3 Prioritas Rendah
1. **Tingkatkan jawaban rekomendasi** - Sesuaikan format dengan kebutuhan pengguna
2. **Tambah analisis temporal** - Data trend penjualan bulanan

---

## 10. Kesimpulan

Evaluasi chatbot SIWUR dengan 50 pertanyaan menunjukkan:

1. **Kelebihan:**
   - Pertanyaan agregasi dan faktual sederhana dijawab dengan akurat
   - Adaptive RAG berhasil memilih datasource yang tepat
   - Answer relevancy tinggi (80%) menunjukkan chatbot memberikan jawaban yang relevan

2. **Kekurangan:**
   - Keyword match rendah (26.52%) karena perbedaan format jawaban
   - Beberapa query database error karena schema tidak sesuai
   - Pertanyaan analitis kompleks sulit dijawab

3. **Skor Keseluruhan:**
   - **Keyword Match: 0.2652 (26.52%)**
   - **Answer Relevancy: 0.8000 (80.00%)**

---

## 11. Lampiran

### 11.1 File Hasil Evaluasi Detail
- Lokasi: `ai_service/evaluation_results/evaluation_results_20251212_113938.csv`
- Format: CSV dengan kolom question, answer, ground_truth, category, difficulty, datasource, documents_count

### 11.2 Dataset Evaluasi
- Lokasi: `ai_service/ragas_evaluation_dataset.csv`
- Total: 50 pertanyaan dengan 8 kategori dan 3 tingkat kesulitan

---

*Dokumen ini dibuat untuk keperluan laporan skripsi S1.*
