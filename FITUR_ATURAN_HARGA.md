# Fitur Aturan Harga Barang

## Deskripsi
Fitur ini memungkinkan pengguna untuk mengatur harga jual barang berdasarkan kuantitas pembelian dan satuan tertentu. Sistem mendukung multiple aturan harga untuk setiap kombinasi barang dan satuan.

## Fitur Utama

### 1. **Manajemen Aturan Harga**
- ✅ Tambah aturan harga baru
- ✅ Edit aturan harga yang sudah ada
- ✅ Hapus aturan harga
- ✅ Validasi range kuantitas tidak bertumpang tindih

### 2. **Interface Yang User-Friendly**
- ✅ Modal form dengan desain yang bersih
- ✅ Preview real-time dari aturan harga
- ✅ Informasi bantuan dan tips penggunaan
- ✅ Validasi input dengan pesan error yang jelas

### 3. **Validasi Bisnis**
- ✅ Range kuantitas tidak boleh tumpang tindih
- ✅ Minimal penjualan harus > 0
- ✅ Maksimal penjualan bisa kosong (unlimited)
- ✅ Harga jual harus > 0
- ✅ Satuan harus sudah terdaftar di barang satuan

## Struktur Database

### Tabel: `aturan_harga_barang`
```sql
- id (Primary Key)
- barang_id (Foreign Key ke tabel barang)
- satuan_id (Foreign Key ke tabel satuan)
- minimal_penjualan (float, minimum quantity)
- maksimal_penjualan (float, nullable, maximum quantity)
- harga_jual (float, price per unit)
- created_at, updated_at
```

## Cara Penggunaan

### 1. **Akses Fitur**
- Buka halaman detail barang
- Scroll ke bagian "Manajemen Aturan Harga Barang"

### 2. **Tambah Aturan Harga**
- Klik tombol "Tambah Aturan Harga"
- Pilih satuan dari dropdown
- Isi minimal penjualan
- Isi maksimal penjualan (opsional, kosongkan untuk unlimited)
- Isi harga jual
- Klik "Simpan"

### 3. **Edit/Hapus Aturan**
- Klik icon pensil untuk edit
- Klik icon trash untuk hapus (dengan konfirmasi)

## Contoh Penggunaan

### Skenario: Telur Ayam
**Satuan Kilogram:**
- 1-10 kg → Rp 25.000 per kg
- 11-50 kg → Rp 23.000 per kg  
- 51+ kg → Rp 21.000 per kg

**Satuan Karton:**
- 1-5 karton → Rp 1.200.000 per karton

## Validasi dan Error Handling

### 1. **Validasi Range**
- Sistem otomatis mencegah range yang bertumpang tindih
- Contoh: Jika sudah ada range 1-10, tidak bisa tambah 5-15

### 2. **Validasi Input**
- Minimal penjualan harus ≥ 1
- Maksimal penjualan harus > minimal (jika diisi)
- Harga jual harus ≥ 0.01

### 3. **Dependency Check**
- Satuan harus sudah terdaftar di barang satuan
- Tidak bisa menambah aturan jika belum ada satuan

## Integrasi dengan Sistem

### 1. **Model Relationships**
```php
// Barang.php
public function aturanHarga()
{
    return $this->hasMany(AturanHargaBarang::class, 'barang_id');
}

// AturanHargaBarang.php  
public function barang()
{
    return $this->belongsTo(Barang::class, 'barang_id');
}

public function satuan()
{
    return $this->belongsTo(Satuan::class, 'satuan_id');
}
```

### 2. **Livewire Component**
- **File**: `app/Livewire/Barang/Show.php`
- **Methods**: 
  - `showAddAturanHargaForm()`
  - `editAturanHarga($id)`
  - `saveAturanHarga()`
  - `deleteAturanHarga($id)`

### 3. **View Template**
- **File**: `resources/views/livewire/barang/show.blade.php`
- **Sections**: Price Rules Management, Modal Forms

## UI/UX Features

### 1. **Visual Design**
- Card-based layout dengan shadow dan hover effects
- Color-coded icons dan badges
- Responsive grid layout
- Dark mode support

### 2. **Interactive Elements**
- Real-time preview saat input
- Modal dengan backdrop blur
- Loading states dan animations
- Success/error toast notifications

### 3. **Accessibility**
- Keyboard navigation support
- Screen reader friendly
- Clear error messages
- Tooltips dan helper text

## Future Enhancements

1. **Bulk Import/Export** - Import aturan harga dari Excel
2. **Price History** - Track perubahan harga dari waktu ke waktu
3. **Auto-calculation** - Hitung harga otomatis berdasarkan markup
4. **Customer Groups** - Aturan harga berbeda per grup customer
5. **Seasonal Pricing** - Harga berbeda berdasarkan periode waktu

## Technical Notes

- Menggunakan Livewire untuk real-time updates
- Mary UI components untuk konsistensi design
- Form validation dengan Laravel Validation Rules
- Database transactions untuk data integrity
- Soft delete support (bisa ditambahkan jika diperlukan)
