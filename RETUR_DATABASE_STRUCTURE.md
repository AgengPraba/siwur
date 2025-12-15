# Database Structure dan Models untuk Fitur Retur

## 📊 **Database Tables Created**

### 1. **retur_pembelian** - Header Retur Pembelian

```sql
- id (Primary Key)
- nomor_retur_pembelian (Unique, VARCHAR 50)
- pembelian_id (FK ke tabel pembelian)
- supplier_id (FK ke tabel supplier)
- gudang_id (FK ke tabel gudang)
- toko_id (FK ke tabel toko)
- tanggal_retur (DATETIME)
- status (ENUM: draft, on_progress, review, closed, void)
- catatan (TEXT, nullable)
- dibuat_oleh (FK ke users)
- disetujui_oleh (FK ke users, nullable)
- tanggal_disetujui (TIMESTAMP, nullable)
- created_at, updated_at
```

### 2. **retur_pembelian_detail** - Detail Item Retur Pembelian

```sql
- id (Primary Key)
- retur_pembelian_id (FK ke retur_pembelian)
- barang_id (FK ke barang)
- satuan_id (FK ke satuan)
- qty_retur (INTEGER)
- harga_satuan (DECIMAL 15,2)
- total_harga (DECIMAL 15,2)
- alasan_retur (VARCHAR)
- created_at, updated_at
```

### 3. **retur_penjualan** - Header Retur Penjualan

```sql
- id (Primary Key)
- nomor_dokumen (Unique, VARCHAR 50)
- penjualan_id (FK ke tabel penjualan)
- customer_id (FK ke tabel customer)
- gudang_id (FK ke tabel gudang)
- toko_id (FK ke tabel toko)
- tanggal_retur (DATETIME)
- status (ENUM: draft, on_progress, review, closed, void)
- catatan (TEXT, nullable)
- dibuat_oleh (FK ke users)
- disetujui_oleh (FK ke users, nullable)
- tanggal_disetujui (TIMESTAMP, nullable)
- created_at, updated_at
```

### 4. **retur_penjualan_detail** - Detail Item Retur Penjualan

```sql
- id (Primary Key)
- retur_penjualan_id (FK ke retur_penjualan)
- barang_id (FK ke barang)
- satuan_id (FK ke satuan)
- qty_retur (INTEGER)
- harga_satuan (DECIMAL 15,2)
- total_harga (DECIMAL 15,2)
- alasan_retur (VARCHAR)
- created_at, updated_at
```

## 📝 **Models Created**

### 🔹 **ReturPembelian.php**

**Status Constants:**

-   `STATUS_DRAFT` = 'draft'
-   `STATUS_ON_PROGRESS` = 'on_progress'
-   `STATUS_REVIEW` = 'review'
-   `STATUS_CLOSED` = 'closed'
-   `STATUS_VOID` = 'void'

**Relationships:**

-   `pembelian()` - BelongsTo Pembelian
-   `supplier()` - BelongsTo Supplier
-   `gudang()` - BelongsTo Gudang
-   `toko()` - BelongsTo Toko
-   `dibuatOleh()` - BelongsTo User (dibuat_oleh)
-   `disetujuiOleh()` - BelongsTo User (disetujui_oleh)
-   `details()` - HasMany ReturPembelianDetail

**Accessors:**

-   `status_label` - Formatted status text
-   `status_badge_class` - CSS classes for badge styling
-   `total_items` - Count of detail items
-   `total_qty_retur` - Sum of qty_retur from details
-   `total_nilai_retur` - Sum of total_harga from details
-   `formatted_total_nilai_retur` - Formatted currency

**Helper Methods:**

-   `canEdit()` - Check if retur can be edited (draft only)
-   `canApprove()` - Check if retur can be approved
-   `canVoid()` - Check if retur can be voided
-   `approve($approvedBy)` - Approve retur and set status to closed
-   `void()` - Void retur
-   `generateNomor($tokoId)` - Static method to generate unique retur number

**Scopes:**

-   `forCurrentUserToko()` - Filter by current user's toko
-   `byStatus($status)` - Filter by status
-   `bySupplier($supplierId)` - Filter by supplier
-   `byDateRange($start, $end)` - Filter by date range

### 🔹 **ReturPembelianDetail.php**

**Relationships:**

-   `returPembelian()` - BelongsTo ReturPembelian
-   `barang()` - BelongsTo Barang
-   `satuan()` - BelongsTo Satuan

**Accessors:**

-   `formatted_harga_satuan` - Formatted currency
-   `formatted_total_harga` - Formatted currency

**Helper Methods:**

-   `getHargaFromPembelianDetail($pembelianId, $barangId, $satuanId)` - Static method to get price from original purchase
-   `calculateTotalHarga()` - Auto-calculate total_harga = qty_retur \* harga_satuan

**Auto-calculations:**

-   `boot()` method with `saving` event to auto-calculate total_harga

### 🔹 **ReturPenjualan.php**

**Features:** (Similar to ReturPembelian but for sales)

-   Same status constants and workflow
-   Relationships to Penjualan, Customer instead of Pembelian, Supplier
-   Same helper methods and scopes
-   Generate nomor with prefix 'RTJ' instead of 'RTB'

### 🔹 **ReturPenjualanDetail.php**

**Features:** (Similar to ReturPembelianDetail but for sales)

-   `getHargaFromPenjualanDetail()` - Get price from original sale
-   Same auto-calculation features

## 🔗 **Relationships Added to Existing Models**

### **Pembelian.php**

```php
// Added relationship
public function returPembelian()
{
    return $this->hasMany(ReturPembelian::class, 'pembelian_id');
}
```

### **Penjualan.php**

```php
// Added relationship
public function returPenjualan()
{
    return $this->hasMany(ReturPenjualan::class, 'penjualan_id');
}
```

## 🎯 **Nomor Dokumen Generation**

### **Retur Pembelian:**

-   Format: `RTB{YYYYMMDD}{XXX}`
-   Example: `RTB20250825001`

### **Retur Penjualan:**

-   Format: `RTJ{YYYYMMDD}{XXX}`
-   Example: `RTJ20250825001`

## 📋 **Status Workflow**

```
Draft → On Progress → Review → Closed
                              ↓
                            Void
```

**Status Permissions:**

-   **Draft**: Can edit, can void
-   **On Progress**: Can approve, can void
-   **Review**: Can approve, can void
-   **Closed**: Read-only (final)
-   **Void**: Read-only (cancelled)

## 🔧 **Key Features Implemented**

### ✅ **Data Integrity**

-   Foreign key constraints
-   Cascading deletes where appropriate
-   Unique document numbers

### ✅ **Business Logic**

-   Auto-calculation of totals
-   Status-based permissions
-   Price retrieval from original transactions

### ✅ **Multi-tenancy Support**

-   Uses `HasTenancy` trait
-   Filtered by `toko_id`
-   User-based scopes

### ✅ **Audit Trail**

-   Created by user tracking
-   Approved by user tracking
-   Approval timestamp

### ✅ **Helper Methods**

-   Price helpers for getting original transaction prices
-   Formatted currency accessors
-   Status-based business rules

## 📊 **Database Indexes Created**

**Performance optimization indexes:**

-   `toko_id + status` (composite)
-   `pembelian_id` / `penjualan_id`
-   `supplier_id` / `customer_id`
-   `tanggal_retur`
-   `retur_*_id` on detail tables
-   `barang_id` on detail tables

## 🚀 **Next Steps**

1. **Create Livewire Components** for CRUD operations
2. **Implement Business Logic** for stock adjustments
3. **Create Views** for forms and listings
4. **Add Routes** for retur management
5. **Implement Approval Workflow**
6. **Add Financial Integration** (credit notes, refunds)

## ✅ **Migration Status**

```
✅ 2025_08_25_205152_create_retur_pembelian_table
✅ 2025_08_25_205202_create_retur_pembelian_detail_table
✅ 2025_08_25_205208_create_retur_penjualan_table
✅ 2025_08_25_205214_create_retur_penjualan_detail_table
```

All tables created successfully and ready for use!
