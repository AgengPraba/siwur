# Laporan Evaluasi Chatbot RAG SIWUR Menggunakan Framework RAGAS

**Untuk Keperluan Laporan Skripsi S1**

---

## Informasi Evaluasi

| Parameter | Nilai |
|-----------|-------|
| Tanggal Evaluasi | 12 Desember 2025 |
| Jumlah Pertanyaan | 20 pertanyaan |
| Metode Evaluasi | RAGAS Framework + Simple Fallback |
| Model LLM | Google Gemini 2.0 Flash (via OpenRouter) |
| Embedding Model | sentence-transformers/all-MiniLM-L6-v2 |

---

## 1. Pendahuluan

Evaluasi ini dilakukan untuk mengukur performa chatbot RAG (Retrieval Augmented Generation) pada sistem SIWUR (Sistem Informasi Wirausaha). Evaluasi menggunakan framework RAGAS yang merupakan standar industri untuk mengevaluasi sistem RAG dengan 4 metrik utama.

### 1.1 Metrik Evaluasi RAGAS

| Metrik | Deskripsi |
|--------|-----------|
| **Context Precision** | Mengukur seberapa tepat konteks yang di-retrieve relevan dengan pertanyaan |
| **Context Recall** | Mengukur seberapa lengkap konteks mencakup informasi yang dibutuhkan untuk menjawab |
| **Faithfulness** | Mengukur seberapa akurat jawaban berdasarkan konteks yang diberikan (tanpa hallucination) |
| **Answer Relevancy** | Mengukur seberapa relevan jawaban dengan pertanyaan yang diajukan |

### 1.2 Interpretasi Skor RAGAS

| Rentang Skor | Interpretasi |
|--------------|--------------|
| 0.9 - 1.0 | Excellent |
| 0.7 - 0.9 | Good |
| 0.5 - 0.7 | Fair |
| < 0.5 | Poor - Perlu Perbaikan |

---

## 2. Hasil Evaluasi Keseluruhan

### 2.1 Skor Metrik RAGAS

| Metrik | Skor | Persentase | Interpretasi |
|--------|------|------------|--------------|
| **Context Precision** | 0.0770 | 7.70% | Poor |
| **Context Recall** | 0.1540 | 15.40% | Poor |
| **Faithfulness** | 0.3344 | 33.44% | Poor |
| **Answer Relevancy** | 0.4511 | 45.11% | Poor |

### 2.2 Visualisasi Skor (Text-based)

```
Context Precision  [##--------] 7.70%
Context Recall     [##--------] 15.40%
Faithfulness       [###-------] 33.44%
Answer Relevancy   [####------] 45.11%
```

### 2.3 Rata-rata Skor Keseluruhan

**Rata-rata 4 Metrik RAGAS: 0.2541 (25.41%)**

---

## 3. Hasil Evaluasi per Kategori Pertanyaan

### 3.1 Distribusi Kategori

| Kategori | Jumlah Pertanyaan | Persentase |
|----------|-------------------|------------|
| Factoid | 10 | 50% |
| Analytical | 10 | 50% |

### 3.2 Skor per Kategori

#### 3.2.1 Kategori FACTOID (10 pertanyaan)

| Metrik | Skor | Persentase |
|--------|------|------------|
| Context Precision | 0.1243 | 12.43% |
| Context Recall | 0.2036 | 20.36% |
| Faithfulness | 0.4014 | 40.14% |
| Answer Relevancy | 0.5797 | 57.97% |

**Rata-rata Factoid: 0.3273 (32.73%)**

#### 3.2.2 Kategori ANALYTICAL (10 pertanyaan)

| Metrik | Skor | Persentase |
|--------|------|------------|
| Context Precision | 0.0296 | 2.96% |
| Context Recall | 0.1045 | 10.45% |
| Faithfulness | 0.2674 | 26.74% |
| Answer Relevancy | 0.3224 | 32.24% |

**Rata-rata Analytical: 0.1810 (18.10%)**

### 3.3 Perbandingan Kategori

```
                   Context    Context    Faithful-  Answer
                   Precision  Recall     ness       Relevancy
FACTOID           [##-------] [##-------] [####-----] [######----]
                   12.43%     20.36%      40.14%      57.97%

ANALYTICAL        [#--------] [#--------] [###------] [###-------]
                   2.96%      10.45%      26.74%      32.24%
```

**Temuan:** Kategori Factoid memiliki performa lebih baik dibanding Analytical karena pertanyaan faktual sederhana lebih mudah dijawab dengan data dari database.

---

## 4. Hasil Evaluasi per Tingkat Kesulitan

### 4.1 Distribusi Tingkat Kesulitan

| Tingkat Kesulitan | Jumlah Pertanyaan |
|-------------------|-------------------|
| Easy | 7 |
| Medium | 8 |
| Hard | 5 |

### 4.2 Skor per Tingkat Kesulitan

#### 4.2.1 Tingkat EASY (7 pertanyaan)

| Metrik | Skor | Persentase |
|--------|------|------------|
| Context Precision | 0.1356 | 13.56% |
| Context Recall | 0.2625 | 26.25% |
| Faithfulness | 0.4568 | 45.68% |
| Answer Relevancy | 0.6481 | 64.81% |

**Rata-rata Easy: 0.3758 (37.58%)**

#### 4.2.2 Tingkat MEDIUM (8 pertanyaan)

| Metrik | Skor | Persentase |
|--------|------|------------|
| Context Precision | 0.0556 | 5.56% |
| Context Recall | 0.0821 | 8.21% |
| Faithfulness | 0.3368 | 33.68% |
| Answer Relevancy | 0.3719 | 37.19% |

**Rata-rata Medium: 0.2116 (21.16%)**

#### 4.2.3 Tingkat HARD (5 pertanyaan)

| Metrik | Skor | Persentase |
|--------|------|------------|
| Context Precision | 0.0292 | 2.92% |
| Context Recall | 0.1172 | 11.72% |
| Faithfulness | 0.1591 | 15.91% |
| Answer Relevancy | 0.3018 | 30.18% |

**Rata-rata Hard: 0.1518 (15.18%)**

### 4.3 Perbandingan Tingkat Kesulitan

```
Tingkat Kesulitan vs Rata-rata Skor:

EASY    [####------] 37.58%
MEDIUM  [##--------] 21.16%
HARD    [##--------] 15.18%
```

**Temuan:** Terdapat korelasi terbalik antara tingkat kesulitan dan skor evaluasi. Pertanyaan mudah memiliki skor 2.5x lebih tinggi dari pertanyaan sulit.

---

## 5. Analisis Jawaban Detail

### 5.1 Contoh Jawaban Akurat (High Score)

| No | Pertanyaan | Jawaban Chatbot | Skor |
|----|------------|-----------------|------|
| 1 | Berapa stok Indomie Goreng Original? | 500.00 Pcs | AKURAT |
| 2 | Berapa stok Gula Pasir Gulaku 1kg? | 250.00 Pack | AKURAT |
| 3 | Berapa total jenis kategori barang? | 12 kategori | AKURAT |
| 4 | Produk apa yang paling laku terjual bulan ini? | Indomie Goreng Original (158 unit) | AKURAT |

### 5.2 Contoh Jawaban Tidak Akurat (Low Score)

| No | Pertanyaan | Masalah | Ground Truth |
|----|------------|---------|--------------|
| 1 | Siapa supplier untuk produk mie instan? | Data supplier tidak lengkap | PT Indofood CBP |
| 2 | Alamat customer Warung Makan Bu Tini? | Customer tidak ditemukan | Jl. Raya Pasar Minggu No. 12 |
| 3 | Jam berapa toko paling ramai? | Tidak ada data temporal | Jam 9-11 pagi dan 15-17 sore |

### 5.3 Distribusi Datasource

| Datasource | Jumlah | Persentase |
|------------|--------|------------|
| database | 11 | 55% |
| analytics | 9 | 45% |

---

## 6. Temuan dan Analisis

### 6.1 Kekuatan Sistem

1. **Pertanyaan Stok Produk (Factoid Easy)**
   - Chatbot sangat akurat dalam menjawab pertanyaan stok spesifik
   - Contoh: "Stok Indomie Goreng Original = 500 Pcs" (100% akurat)

2. **Pertanyaan Agregasi Sederhana**
   - Chatbot dapat menghitung total kategori dengan benar
   - Contoh: "Total 12 kategori barang" (100% akurat)

3. **Routing Otomatis**
   - Adaptive RAG berhasil memilih datasource yang tepat (database vs analytics)

### 6.2 Kelemahan Sistem

1. **Context Precision Rendah (7.70%)**
   - Terlalu banyak konteks yang tidak relevan di-retrieve
   - Konteks stok mencakup semua 60 produk, padahal pertanyaan spesifik

2. **Context Recall Rendah (15.40%)**
   - Beberapa informasi penting tidak ter-retrieve
   - Data supplier dan customer tidak lengkap di database

3. **Pertanyaan Analytical Sulit Dijawab**
   - Pertanyaan seperti "jam berapa toko ramai?" tidak memiliki data pendukung
   - Market Basket Analysis tidak mencakup semua jenis analisis

### 6.3 Masalah Data

1. **Data Supplier/Customer Tidak Lengkap**
   - Hanya ada 2 supplier generic: "Supplier Opname" dan "Supplier Umum"
   - Customer juga hanya 2: "Customer Opname" dan "Customer Umum"

2. **Tidak Ada Data Temporal Detail**
   - Tidak ada data per jam untuk analisis jam sibuk
   - Trend 3 bulan tidak tersedia, hanya 30 hari terakhir

---

## 7. Rekomendasi Perbaikan

### 7.1 Prioritas Tinggi

| Rekomendasi | Dampak Expected |
|-------------|-----------------|
| Perbaiki data supplier dengan nama lengkap (PT Indofood, dll) | +20% Context Recall |
| Tambah data customer dengan detail lengkap | +15% Context Recall |
| Optimalkan retrieval untuk mengambil konteks lebih spesifik | +30% Context Precision |

### 7.2 Prioritas Medium

| Rekomendasi | Dampak Expected |
|-------------|-----------------|
| Tambah analisis jam sibuk dari data transaksi | +10% Answer Relevancy |
| Implementasi trend penjualan multi-bulan | +10% Answer Relevancy |
| Tambah data harga jual per produk | +15% Faithfulness |

### 7.3 Prioritas Rendah

| Rekomendasi | Dampak Expected |
|-------------|-----------------|
| Fine-tune embedding model untuk domain toko | +5% overall |
| Tambah re-ranking setelah retrieval | +10% Context Precision |

---

## 8. Kesimpulan

### 8.1 Ringkasan Hasil

Evaluasi chatbot RAG SIWUR menggunakan framework RAGAS dengan 20 pertanyaan menunjukkan hasil sebagai berikut:

| Metrik | Skor | Status |
|--------|------|--------|
| Context Precision | 0.0770 | Perlu Perbaikan |
| Context Recall | 0.1540 | Perlu Perbaikan |
| Faithfulness | 0.3344 | Perlu Perbaikan |
| Answer Relevancy | 0.4511 | Perlu Perbaikan |
| **Rata-rata** | **0.2541** | **Perlu Perbaikan** |

### 8.2 Performa per Segmen

| Segmen | Rata-rata Skor | Keterangan |
|--------|----------------|------------|
| Factoid | 32.73% | Cukup untuk pertanyaan sederhana |
| Analytical | 18.10% | Perlu perbaikan signifikan |
| Easy | 37.58% | Performa terbaik |
| Medium | 21.16% | Perlu perbaikan |
| Hard | 15.18% | Perlu perbaikan signifikan |

### 8.3 Catatan untuk Skripsi

1. **Chatbot dapat menjawab pertanyaan faktual sederhana dengan baik** (stok produk, jumlah kategori)
2. **Pertanyaan analytical memerlukan peningkatan data** (supplier, customer, temporal)
3. **Framework RAGAS efektif** untuk mengidentifikasi kelemahan sistem RAG
4. **Skor keseluruhan 25.41%** menunjukkan sistem masih dalam tahap pengembangan awal

---

## 9. Lampiran

### 9.1 File Hasil Evaluasi

| File | Lokasi |
|------|--------|
| Hasil Detail (CSV) | `ai_service/evaluation_results/ragas_evaluation_20251212_144055.csv` |
| Dataset Evaluasi | `ai_service/ragas_evaluation_dataset.csv` |
| Script Evaluasi | `ai_service/run_ragas_evaluation.py` |

### 9.2 Konfigurasi Evaluasi

```python
Metrics = [
    context_precision,
    context_recall,
    faithfulness,
    answer_relevancy,
]
LLM = "google/gemini-2.0-flash-001"
Embeddings = "sentence-transformers/all-MiniLM-L6-v2"
```

### 9.3 Distribusi Pertanyaan Dataset Lengkap (50 Pertanyaan)

| Kategori | Jumlah |
|----------|--------|
| Factoid | 10 |
| Analytical | 10 |
| Comparative | 8 |
| Multi-hop | 7 |
| Exploratory | 5 |
| Aggregation | 5 |
| Temporal | 3 |
| Recommendation | 2 |

---

*Dokumen ini dibuat untuk keperluan Bab Pembahasan pada Laporan Skripsi S1*

*Tanggal: 12 Desember 2025*
