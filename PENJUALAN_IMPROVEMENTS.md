# Perbaikan Sistem Penjualan - Diskon dan Biaya Lain

## Ringkasan Perubahan

Sistem penjualan telah diperbaiki untuk menangani diskon dan biaya lain dengan lebih baik. Sekarang semua diskon dan biaya tambahan disimpan di level detail penjualan (`penjualan_detail`) dan dihitung secara otomatis untuk ditampilkan di level penjualan.

## Perubahan yang Dilakukan

### 1. Model Penjualan (`app/Models/Penjualan.php`)

**Accessor Baru:**
- `getTotalDiskonAttribute()` - Menghitung total diskon dari semua detail
- `getTotalBiayaLainAttribute()` - Menghitung total biaya lain dari semua detail
- `getSubtotalSebelumAdjustmentAttribute()` - Subtotal sebelum diskon dan biaya lain
- `getFormattedTotalDiskonAttribute()` - Format rupiah untuk total diskon
- `getFormattedTotalBiayaLainAttribute()` - Format rupiah untuk total biaya lain
- `getTotalItemsAttribute()` - Jumlah item dalam penjualan
- `getTotalQuantityAttribute()` - Total quantity semua item

**Method Baru:**
- `recalculateTotalHarga()` - Menghitung ulang total harga berdasarkan detail

### 2. Model PenjualanDetail (`app/Models/PenjualanDetail.php`)

**Accessor Baru:**
- `getSubtotalFinalAttribute()` - Subtotal setelah diskon dan biaya lain
- `getFormattedHargaSatuanAttribute()` - Format rupiah untuk harga satuan
- `getFormattedSubtotalAttribute()` - Format rupiah untuk subtotal
- `getFormattedDiskonAttribute()` - Format rupiah untuk diskon
- `getFormattedBiayaLainAttribute()` - Format rupiah untuk biaya lain
- `getFormattedSubtotalFinalAttribute()` - Format rupiah untuk subtotal final

**Method Baru:**
- `calculateSubtotal()` - Menghitung ulang subtotal
- `calculateProfit($hargaBeli)` - Menghitung profit berdasarkan harga beli

### 3. Livewire Component (`app/Livewire/Penjualan/Index.php`)

**Perubahan:**
- Menambahkan eager loading untuk `penjualanDetails`
- Memperbarui statistik untuk menampilkan total diskon dan biaya lain
- Menambahkan perhitungan total items dan quantity

### 4. View (`resources/views/livewire/penjualan/index.blade.php`)

**Perubahan:**
- Menambahkan 2 card statistik baru untuk total diskon dan biaya lain
- Menambahkan kolom "Items" di tabel desktop
- Menampilkan informasi diskon dan biaya lain di setiap baris
- Menambahkan icon dan tooltip untuk clarity
- Responsive design yang konsisten antara mobile dan desktop

## Fitur Baru

### 1. Statistik Dashboard
- **Total Diskon**: Menampilkan total semua diskon yang diberikan
- **Biaya Lain**: Menampilkan total semua biaya tambahan
- Card statistik dengan warna yang berbeda untuk mudah dibedakan

### 2. Informasi Detail di Tabel
- **Kolom Items**: Menampilkan jumlah item dan total quantity
- **Info Diskon**: Ditampilkan dengan warna merah dan icon
- **Info Biaya Lain**: Ditampilkan dengan warna biru dan icon
- **Tooltip**: Memberikan konteks tambahan saat hover

### 3. Mobile Responsive
- Semua informasi baru juga ditampilkan di mobile view
- Layout yang konsisten dan mudah dibaca

## Cara Penggunaan

### Mengakses Data Diskon dan Biaya Lain

```php
// Di Controller atau Livewire
$penjualan = Penjualan::with('penjualanDetails')->find(1);

// Total diskon dari semua detail
$totalDiskon = $penjualan->total_diskon;

// Total biaya lain dari semua detail
$totalBiayaLain = $penjualan->total_biaya_lain;

// Format rupiah
$formattedDiskon = $penjualan->formatted_total_diskon;
$formattedBiayaLain = $penjualan->formatted_total_biaya_lain;

// Informasi items
$totalItems = $penjualan->total_items;
$totalQuantity = $penjualan->total_quantity;
```

### Menghitung Ulang Total Harga

```php
$penjualan = Penjualan::find(1);
$penjualan->recalculateTotalHarga()->save();
```

### Mengakses Data Detail

```php
$detail = PenjualanDetail::find(1);

// Subtotal setelah diskon dan biaya lain
$subtotalFinal = $detail->subtotal_final;

// Format rupiah
$formattedSubtotalFinal = $detail->formatted_subtotal_final;

// Menghitung ulang subtotal
$detail->calculateSubtotal()->save();

// Menghitung profit
$hargaBeli = 10000;
$detail->calculateProfit($hargaBeli)->save();
```

## Keuntungan Perubahan

1. **Akurasi Data**: Diskon dan biaya lain disimpan di level detail untuk akurasi yang lebih baik
2. **Performa**: Menggunakan eager loading dan accessor untuk mengurangi query database
3. **Fleksibilitas**: Setiap item bisa memiliki diskon dan biaya lain yang berbeda
4. **User Experience**: Interface yang lebih informatif dengan visual yang jelas
5. **Maintainability**: Kode yang lebih terstruktur dan mudah dipelihara

## Catatan Penting

- Field `diskon` dan `biaya_lain` sudah ada di tabel `penjualan_detail`
- Tidak ada perubahan struktur database yang diperlukan
- Semua perhitungan dilakukan secara real-time menggunakan accessor
- Kompatibel dengan data yang sudah ada

## Testing

Pastikan untuk menguji:
1. Tampilan statistik di dashboard
2. Informasi diskon dan biaya lain di tabel
3. Responsive design di mobile
4. Perhitungan total yang akurat
5. Performance dengan data yang banyak