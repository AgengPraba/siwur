<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;
    protected $table = 'pembelian_detail';
    protected $fillable = ['pembelian_id', 'barang_id', 'satuan_id', 'gudang_id', 'harga_satuan', 'jumlah', 'konversi_satuan_terkecil', 'subtotal', 'rencana_harga_jual', 'diskon', 'biaya_lain'];
    protected $primaryKey = 'id';

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'konversi_satuan_terkecil' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'rencana_harga_jual' => 'decimal:2',
        'diskon' => 'decimal:2',
        'biaya_lain' => 'decimal:2',
    ];

    // Relationship dengan Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    // Relationship dengan Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relationship dengan Satuan
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    // Relationship dengan Gudang
    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'gudang_id');
    }

    // Relationship dengan TransaksiGudangStock
    public function transaksiGudangStock()
    {
        return $this->hasMany(TransaksiGudangStock::class, 'pembelian_detail_id');
    }

    // Relationship dengan PenjualanDetail (items sold from this purchase)
    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'pembelian_detail_id');
    }

    // Helper to get remaining stock available for sale
    public function getRemainingStockAttribute()
    {
        $totalSold = $this->penjualanDetails()->sum('jumlah');
        return $this->jumlah - $totalSold;
    }

    // Helper to calculate subtotal after discount
    public function getSubtotalAfterDiscountAttribute()
    {
        return $this->subtotal - ($this->diskon ?? 0);
    }

    // Helper to calculate final total (subtotal - discount + biaya_lain)
    public function getFinalTotalAttribute()
    {
        return $this->subtotal - ($this->diskon ?? 0) + ($this->biaya_lain ?? 0);
    }

    // Helper to get discount percentage
    public function getDiscountPercentageAttribute()
    {
        if ($this->subtotal > 0 && $this->diskon > 0) {
            return round(($this->diskon / $this->subtotal) * 100, 2);
        }
        return 0;
    }
}


/* End of file PembelianDetail.php */
/* Location: ./app/Models/PembelianDetail.php */
/* Created at 2025-07-03 16:23:07 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */