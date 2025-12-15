<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPembelianDetail extends Model
{
    protected $table = 'retur_pembelian_detail';

    protected $fillable = [
        'retur_pembelian_id',
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
    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class);
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

    // Helper methods to get price from original pembelian detail
    public static function getHargaFromPembelianDetail($pembelianId, $barangId, $satuanId)
    {
        $pembelianDetail = PembelianDetail::where('pembelian_id', $pembelianId)
            ->where('barang_id', $barangId)
            ->where('satuan_id', $satuanId)
            ->first();

        if (!$pembelianDetail) {
            return 0;
        }

        // Calculate actual price per unit considering discount
        $hargaSetelahDiskon = $pembelianDetail->harga - $pembelianDetail->diskon;
        
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
