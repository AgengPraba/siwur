<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;
    protected $table = 'penjualan_detail';
    protected $fillable = ['penjualan_id', 'pembelian_detail_id', 'barang_id', 'satuan_id', 'gudang_id', 'harga_satuan', 'jumlah', 'konversi_satuan_terkecil', 'subtotal', 'profit', 'diskon', 'biaya_lain'];
    protected $primaryKey = 'id';

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'konversi_satuan_terkecil' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'profit' => 'decimal:2',
        'diskon' => 'decimal:2',
        'biaya_lain' => 'decimal:2',
    ];

    // Eager loading relationships untuk optimasi performa
    protected $with = ['barang'];

    protected $attributes = [
        'diskon' => 0,
        'biaya_lain' => 0,
    ];

    // Relationship dengan Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    // Relationship dengan PembelianDetail
    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
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
        return $this->hasMany(TransaksiGudangStock::class, 'penjualan_detail_id');
    }

    // Accessor untuk subtotal setelah diskon dan biaya lain
    public function getSubtotalFinalAttribute()
    {
        return $this->subtotal - $this->diskon + $this->biaya_lain;
    }

    // Accessor untuk format harga satuan
    public function getFormattedHargaSatuanAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }

    // Accessor untuk format subtotal
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    // Accessor untuk format diskon
    public function getFormattedDiskonAttribute()
    {
        return 'Rp ' . number_format($this->diskon, 0, ',', '.');
    }

    // Accessor untuk format biaya lain
    public function getFormattedBiayaLainAttribute()
    {
        return 'Rp ' . number_format($this->biaya_lain, 0, ',', '.');
    }

    // Accessor untuk format subtotal final
    public function getFormattedSubtotalFinalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal_final, 0, ',', '.');
    }

    // Method untuk menghitung ulang subtotal berdasarkan harga dan jumlah
    public function calculateSubtotal()
    {
        $this->subtotal = $this->harga_satuan * $this->jumlah;
        return $this;
    }

    // Method untuk menghitung profit
    public function calculateProfit($hargaBeli)
    {
        $this->profit = $this->subtotal_final - ($hargaBeli * $this->jumlah);
        return $this;
    }

    // Boot method untuk auto-recalculate total penjualan
    protected static function boot()
    {
        parent::boot();

        // Event listener untuk auto-recalculate total penjualan
        static::saved(function ($penjualanDetail) {
            $penjualanDetail->penjualan->autoRecalculateTotal();
        });

        static::deleted(function ($penjualanDetail) {
            $penjualanDetail->penjualan->autoRecalculateTotal();
        });
    }

   

    /**
     * Scope untuk mengoptimalkan query dengan eager loading
     */
    public function scopeWithOptimizedRelations($query)
    {
        return $query->with([
            'barang:id,nama_barang', 
            'penjualan:id,nomor_penjualan',
            'satuan:id,nama_satuan',
            'gudang:id,nama_gudang',
            'pembelianDetail:id,pembelian_id,barang_id,harga_satuan',
            'pembelianDetail.pembelian:id,nomor_pembelian,tanggal_pembelian,supplier_id',
            'pembelianDetail.pembelian.supplier:id,nama_supplier'
        ]);
    }
}


/* End of file PenjualanDetail.php */
/* Location: ./app/Models/PenjualanDetail.php */
/* Created at 2025-07-03 16:22:56 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */