# Refactoring Notes: Penjualan & PenjualanDetail

## Ringkasan Perubahan

Refactoring ini dilakukan untuk memindahkan `diskon` dan `biaya_lain` dari model `Penjualan` ke `PenjualanDetail` untuk meningkatkan usability dan performa.

## Perubahan yang Dilakukan

### 1. Model PenjualanDetail (`app/Models/PenjualanDetail.php`)

#### Penambahan Atribut:
- `diskon` (decimal:2) - Diskon per item
- `biaya_lain` (decimal:2) - Biaya tambahan per item
- Default value: 0 untuk kedua atribut

#### Method Baru:
- `getSubtotalFinalAttribute()` - Menghitung subtotal final (subtotal + biaya_lain - diskon)
- `scopeWithOptimizedRelations()` - Scope untuk optimasi query dengan eager loading

#### Event Listeners:
- `saved` event - Auto-recalculate total penjualan saat detail disimpan
- `deleted` event - Auto-recalculate total penjualan saat detail dihapus

#### Optimasi:
- Eager loading `barang` relationship secara default

### 2. Model Penjualan (`app/Models/Penjualan.php`)

#### Method yang Diperbaiki:
- `recalculateTotalHarga()` - Menghitung total berdasarkan sum dari detail
  - Formula: `sum(subtotal) + sum(biaya_lain) - sum(diskon)`
  - Auto-save setelah kalkulasi
  - Update `total_diskon` dan `total_biaya_lain`

#### Method Baru:
- `autoRecalculateTotal()` - Trigger untuk recalculate otomatis

### 3. Livewire Component (`app/Livewire/Penjualan/Show.php`)

#### Penambahan Properties:
- `$isLoading` - Loading state untuk UI
- `$isRefreshing` - Refresh state untuk tombol refresh

#### Method yang Diperbaiki:
- `loadPenjualanData()` - Optimasi query dengan eager loading
- `refreshData()` - Dengan loading state dan error handling
- `refreshPembayaran()` - Menggunakan `loadPenjualanData()` untuk konsistensi

#### Optimasi Query:
- Selective field loading untuk relasi
- Eager loading yang dioptimasi
- Penggunaan scope `withOptimizedRelations()`

### 4. Blade View (`resources/views/livewire/penjualan/show.blade.php`)

#### Penambahan Kolom Tabel:
- Kolom "Diskon" untuk menampilkan diskon per item
- Kolom "Biaya Lain" untuk menampilkan biaya tambahan per item
- Kolom "Total" untuk menampilkan subtotal final per item

#### UI Improvements:
- Tombol refresh dengan loading indicator
- Loading overlay saat refresh data
- Loading state pada tombol refresh pembayaran

#### Summary Section:
- Menampilkan total dari `penjualanDetails` bukan dari `penjualan` langsung
- Format currency yang konsisten

## Keuntungan Refactoring

### 1. Usability
- Diskon dan biaya lain dapat diatur per item
- Lebih fleksibel untuk berbagai skenario bisnis
- UI yang lebih informatif dengan detail per item

### 2. Performance
- Eager loading yang dioptimasi
- Selective field loading
- Auto-recalculate yang efisien
- Loading states untuk better UX

### 3. Maintainability
- Kode yang lebih terstruktur
- Event-driven architecture untuk konsistensi data
- Dokumentasi yang lengkap
- Error handling yang proper

## Cara Penggunaan

### Menambah Diskon pada Item:
```php
$penjualanDetail = PenjualanDetail::find($id);
$penjualanDetail->diskon = 5000; // Diskon Rp 5.000
$penjualanDetail->save(); // Auto-recalculate total penjualan
```

### Menambah Biaya Lain pada Item:
```php
$penjualanDetail = PenjualanDetail::find($id);
$penjualanDetail->biaya_lain = 2000; // Biaya tambahan Rp 2.000
$penjualanDetail->save(); // Auto-recalculate total penjualan
```

### Mendapatkan Subtotal Final:
```php
$subtotalFinal = $penjualanDetail->subtotal_final;
// Atau
$subtotalFinal = $penjualanDetail->subtotal + $penjualanDetail->biaya_lain - $penjualanDetail->diskon;
```

## Migration yang Diperlukan

Jika belum ada, tambahkan kolom berikut ke tabel `penjualan_details`:

```php
$table->decimal('diskon', 15, 2)->default(0);
$table->decimal('biaya_lain', 15, 2)->default(0);
```

## Testing

Pastikan untuk test:
1. Auto-recalculate saat CRUD PenjualanDetail
2. Konsistensi total setelah perubahan
3. Performance dengan data besar
4. UI responsiveness dengan loading states

---

**Catatan**: Refactoring ini backward compatible dan tidak mengubah struktur database yang sudah ada, hanya menambahkan fungsionalitas baru.