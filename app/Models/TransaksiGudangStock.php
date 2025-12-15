<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiGudangStock extends Model
{
    use HasFactory;
    protected $table = 'transaksi_gudang_stock';
    protected $fillable = ['gudang_stock_id','pembelian_detail_id','penjualan_detail_id','jumlah','konversi_satuan_terkecil','tipe',];
    protected $primaryKey = 'id';
    
    protected $casts = [
        'pembelian_detail_id' => 'integer',
        'penjualan_detail_id' => 'integer',
        'gudang_stock_id' => 'integer',
        'jumlah' => 'decimal:2',
        'konversi_satuan_terkecil' => 'decimal:2',
    ];

    // Relationship dengan GudangStock
    public function gudangStock()
    {
        return $this->belongsTo(GudangStock::class, 'gudang_stock_id');
    }

    // Relationship dengan PembelianDetail
    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
    }

    // Relationship dengan PenjualanDetail
    public function penjualanDetail()
    {
        return $this->belongsTo(PenjualanDetail::class, 'penjualan_detail_id');
    }

    // Helper method untuk menentukan apakah transaksi berasal dari pembelian
    public function isPembelian()
    {
        return !is_null($this->pembelian_detail_id);
    }

    // Helper method untuk menentukan apakah transaksi berasal dari penjualan
    public function isPenjualan()
    {
        return !is_null($this->penjualan_detail_id);
    }

    // Helper method untuk mendapatkan sumber transaksi
    public function getSource()
    {
        if ($this->isPembelian()) {
            return 'pembelian';
        } elseif ($this->isPenjualan()) {
            return 'penjualan';
        } else {
            return 'adjustment'; // untuk transaksi lain seperti adjustment, transfer, dll
        }
    }

    // Helper method untuk mendapatkan detail transaksi
    public function getSourceDetail()
    {
        if ($this->isPembelian()) {
            return $this->pembelianDetail;
        } elseif ($this->isPenjualan()) {
            return $this->penjualanDetail;
        } else {
            return null;
        }
    }

    // Scope untuk filter berdasarkan tipe transaksi
    public function scopePembelian($query)
    {
        return $query->whereNotNull('pembelian_detail_id');
    }

    public function scopePenjualan($query)
    {
        return $query->whereNotNull('penjualan_detail_id');
    }

    public function scopeAdjustment($query)
    {
        return $query->whereNull('pembelian_detail_id')
                    ->whereNull('penjualan_detail_id');
    }

    public function scopeMasuk($query)
    {
        return $query->where('tipe', 'masuk');
    }

    public function scopeKeluar($query)
    {
        return $query->where('tipe', 'keluar');
    }
}


/* End of file TransaksiGudangStock.php */
/* Location: ./app/Models/TransaksiGudangStock.php */
/* Created at 2025-07-03 16:24:00 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */