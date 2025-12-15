<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AturanHargaBarang extends Model
{
    use HasFactory;

    protected $table = 'aturan_harga_barang';
    protected $fillable = [
        'barang_id',
        'satuan_id',
        'minimal_penjualan',
        'maksimal_penjualan',
        'harga_jual'
    ];
    protected $primaryKey = 'id';

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
}
