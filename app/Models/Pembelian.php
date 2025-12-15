<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasTenancy;

class Pembelian extends Model
{
    use HasFactory, HasTenancy;
    protected $table = 'pembelian';
    protected $fillable = ['nomor_pembelian', 'tanggal_pembelian', 'supplier_id', 'user_id', 'keterangan', 'total_harga', 'status', 'informasi_tambahan', 'balasan_informasi_tambahan', 'toko_id', 'kembalian'];
    protected $primaryKey = 'id';
    
    protected $casts = [
        'tanggal_pembelian' => 'datetime',
    ];

    // Relationship dengan Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    // Relationship dengan PembelianDetail
    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }

    // Alias untuk compatibility
    public function details()
    {
        return $this->pembelianDetails();
    }

    // Relationship dengan PembayaranPembelian
    public function pembayaranPembelian()
    {
        return $this->hasMany(PembayaranPembelian::class, 'pembelian_id');
    }

    // Relationship dengan ReturPembelian
    public function returPembelian()
    {
        return $this->hasMany(ReturPembelian::class, 'pembelian_id');
    }

    // Helper method to calculate total diskon from all details
    public function getTotalDiskonAttribute()
    {
        return $this->pembelianDetails->sum(function($d){
         return ($d->diskon * $d->jumlah);
     });
    }

    // Helper method to calculate total biaya lain from all details
    public function getTotalBiayaLainAttribute()
    {
        return $this->pembelianDetails()->sum('biaya_lain') ?? 0;
    }

    // Helper method to calculate subtotal before diskon and biaya lain
    public function getSubtotalAttribute()
    {
        return $this->pembelianDetails()->sum('subtotal') ?? 0;
    }

    // Helper method to calculate grand total (subtotal - total_diskon + total_biaya_lain)
    public function getGrandTotalAttribute()
    {
        $subtotal = $this->subtotal;
        $totalDiskon = $this->total_diskon;
        $totalBiayaLain = $this->total_biaya_lain;

        return $subtotal - $totalDiskon + $totalBiayaLain;
    }

    // Helper method to get total items count
    public function getTotalItemsAttribute()
    {
        return $this->pembelianDetails()->count();
    }

    // Helper method to get total quantity
    public function getTotalQuantityAttribute()
    {
        return $this->pembelianDetails()->sum('jumlah') ?? 0;
    }

    // Accessor untuk format tanggal
    public function getTanggalPembelianFormattedAttribute()
    {
        return $this->tanggal_pembelian ? $this->tanggal_pembelian->format('d/m/Y') : '-';
    }

    // Helper method to check if pembelian has discount
    public function getHasDiscountAttribute()
    {
        return $this->total_diskon > 0;
    }

    // Helper method to check if pembelian has additional costs
    public function getHasAdditionalCostsAttribute()
    {
        return $this->total_biaya_lain > 0;
    }

    // Helper method to calculate total kembalian from all payments
    public function getTotalKembalianAttribute()
    {
        return $this->pembayaranPembelian()->sum('kembalian') ?? 0;
    }

    // Helper method to get formatted total kembalian
    public function getFormattedTotalKembalianAttribute()
    {
        return 'Rp ' . number_format($this->total_kembalian, 0, ',', '.');
    }

    /**
     * Scope untuk filter berdasarkan toko_id user yang login
     * (Alias untuk compatibility dengan kode yang sudah ada)
     */
    public function scopeForCurrentUserToko($query)
    {
        return $this->scopeForToko($query);
    }
    

    /**
     * Helper method untuk mendapatkan toko_id user yang login
     * (Alias untuk compatibility dengan kode yang sudah ada)
     */
    public static function getUserTokoId()
    {
        return static::getCurrentTokoId();
    }
}


/* End of file Pembelian.php */
/* Location: ./app/Models/Pembelian.php */
/* Created at 2025-07-03 16:23:02 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */