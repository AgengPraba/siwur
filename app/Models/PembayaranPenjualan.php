<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPenjualan extends Model
{
    use HasFactory;
    protected $table = 'pembayaran_penjualan';
    protected $fillable = ['penjualan_id', 'user_id', 'jenis_pembayaran', 'jumlah', 'keterangan'];
    protected $primaryKey = 'id';

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    // Relationship dengan Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

  
}


/* End of file PembayaranPenjualan.php */
/* Location: ./app/Models/PembayaranPenjualan.php */
/* Created at 2025-01-XX XX:XX:XX */
/* Mohammad Irham Akbar Laravel 12 CRUD Generator : */
