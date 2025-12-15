<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPenjualanDetail extends Model
{
    protected $table = 'retur_penjualan_detail';

    protected $fillable = [
        'retur_penjualan_id',
        'barang_id',
        'satuan_id',
        'qty_retur',
        'harga_satuan',
        'total_harga',
        'alasan_retur'
    ];

    protected $casts = [
        'qty_retur' => 'integer',
        'harga_satuan' => 'float',
        'total_harga' => 'float'
    ];

    // Relationships
    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    // Accessors
    public function getFormattedHargaSatuanAttribute()
    {
        return number_format((float)$this->harga_satuan, 0, ',', '.');
    }

    public function getFormattedTotalHargaAttribute()
    {
        return number_format((float)$this->total_harga, 0, ',', '.');
    }

    // Helper methods to get price from original penjualan detail
    public static function getHargaFromPenjualanDetail($penjualanId, $barangId, $satuanId)
    {
        $penjualanDetail = PenjualanDetail::where('penjualan_id', $penjualanId)
            ->where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->first();

        if (!$penjualanDetail) {
            return 0;
        }

        // Calculate actual price per unit considering discount
        $hargaSetelahDiskon = $penjualanDetail->harga - $penjualanDetail->diskon;
        
        return $hargaSetelahDiskon;
    }

    // Calculate total harga automatically
    public function calculateTotalHarga()
    {
        $this->total_harga = $this->qty_retur * $this->harga_satuan;
        return $this->total_harga;
    }

    // Boot method for auto-calculation
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateTotalHarga();
        });
    }
}
