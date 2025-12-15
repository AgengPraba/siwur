<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BarangSatuan extends Model
{
    use HasFactory;
    protected $table = 'barang_satuan';
    protected $fillable = ['barang_id', 'satuan_id', 'konversi_satuan_terkecil', 'is_satuan_terkecil',];
    protected $primaryKey = 'id';

    /**
     * Boot method untuk menambahkan global scope
     */
    protected static function boot()
    {
        parent::boot();

        // Global scope untuk memfilter berdasarkan toko_id user
        static::addGlobalScope('user_toko', function (Builder $builder) {
            if (Auth::check()) {
                $tokoId = static::getUserTokoId();
                if ($tokoId) {
                    $builder->whereHas('barang', function ($query) use ($tokoId) {
                        $query->where('barang.toko_id', $tokoId);
                    });
                }
            }
        });
    }

    /**
     * Helper method untuk mendapatkan toko_id user yang sedang login
     */
    public static function getUserTokoId()
    {
        $user = Auth::user();
        return $user && $user->akses ? $user->akses->toko_id : null;
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
}


/* End of file BarangSatuan.php */
/* Location: ./app/Models/BarangSatuan.php */
/* Created at 2025-07-03 16:23:42 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */