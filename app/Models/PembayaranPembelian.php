<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPembelian extends Model
{
    use HasFactory;
    
    protected $table = 'pembayaran_pembelian';
    protected $fillable = [
        'pembelian_id',
        'user_id',
        'jenis_pembayaran',
        'jumlah',
        'keterangan'
    ];
    protected $primaryKey = 'id';

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    // Relationship dengan Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate kembalian based on payment amount and total price
     * 
     * @param float $totalHarga Total price of the purchase
     * @return float Amount of kembalian
     */
    public function calculateKembalian($totalHarga)
    {
        $kembalian = $this->jumlah - $totalHarga;
        return max(0, $kembalian); // Return 0 if kembalian is negative
    }

    /**
     * Get formatted kembalian amount
     * 
     * @return string Formatted kembalian amount
     */
    public function getFormattedKembalianAttribute()
    {
        return 'Rp ' . number_format($this->kembalian ?? 0, 0, ',', '.');
    }

    /**
     * Boot method to automatically calculate kembalian when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pembayaran) {
            if ($pembayaran->pembelian) {
                $pembayaran->kembalian = $pembayaran->calculateKembalian($pembayaran->pembelian->total_harga);
            }
        });

        static::updating(function ($pembayaran) {
            if ($pembayaran->pembelian) {
                $pembayaran->kembalian = $pembayaran->calculateKembalian($pembayaran->pembelian->total_harga);
            }
        });
    }
}

/* End of file PembayaranPembelian.php */
/* Location: ./app/Models/PembayaranPembelian.php */