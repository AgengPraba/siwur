<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barang';
    protected $fillable = ['kode_barang', 'nama_barang', 'keterangan', 'jenis_barang_id', 'satuan_terkecil_id', 'toko_id'];    
    protected $primaryKey = 'id';
    
    /**
     * Boot method untuk menambahkan global scope
     */
    protected static function boot()
    {
        parent::boot();
        
        // Global scope untuk filter berdasarkan toko_id user yang login
        static::addGlobalScope('toko', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                $akses = $user->akses;
                if ($akses && $akses->toko_id) {
                    $builder->where('barang.toko_id', $akses->toko_id);
                }
            }
        });
        
        // Auto-assign toko_id saat creating
        static::creating(function ($model) {
            if (Auth::check() && !$model->toko_id) {
                $user = Auth::user();
                $akses = $user->akses;
                if ($akses && $akses->toko_id) {
                    $model->toko_id = $akses->toko_id;
                }
            }
        });
    }
    
    /**
     * Helper method untuk mendapatkan toko_id user yang login
     */
    public static function getUserTokoId()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $akses = $user->akses;
            return $akses ? $akses->toko_id : null;
        }
        return null;
    }

    // Relationship dengan JenisBarang
    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class, 'jenis_barang_id');
    }

    // Relationship dengan Satuan (satuan terkecil)
    public function satuanTerkecil()
    {
        return $this->belongsTo(Satuan::class, 'satuan_terkecil_id');
    }

    // Relationship dengan BarangSatuan
    public function barangSatuan()
    {
        return $this->hasMany(BarangSatuan::class, 'barang_id');
    }

    // Relationship many-to-many dengan Satuan melalui BarangSatuan
    public function satuan()
    {
        return $this->belongsToMany(Satuan::class, 'barang_satuan', 'barang_id', 'satuan_id')
                    ->withPivot('konversi_satuan_terkecil', 'is_satuan_terkecil')
                    ->withTimestamps();
    }

    // Relationship dengan AturanHargaBarang
    public function aturanHarga()
    {
        return $this->hasMany(AturanHargaBarang::class, 'barang_id');
    }

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    // Relationship dengan GudangStock
    public function gudangStock()
    {
        return $this->hasMany(GudangStock::class, 'barang_id');
    }

    // Relationship dengan PembelianDetail
    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'barang_id');
    }

    // Relationship dengan PenjualanDetail
    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'barang_id');
    }

    // Relationship dengan ReturPembelianDetail
    public function returPembelianDetails()
    {
        return $this->hasMany(ReturPembelianDetail::class, 'barang_id');
    }

    // Relationship dengan ReturPenjualanDetail
    public function returPenjualanDetails()
    {
        return $this->hasMany(ReturPenjualanDetail::class, 'barang_id');
    }

    /**
     * Check if barang can be deleted (no related records exist)
     */
    public function canBeDeleted()
    {
        // Check if there are any unit configurations (barang_satuan)
        if ($this->barangSatuan()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki konfigurasi satuan dan tidak dapat dihapus. Hapus konfigurasi satuan terlebih dahulu.'
            ];
        }

        // Check if there's any stock in any warehouse
        if ($this->gudangStock()->where('jumlah', '>', 0)->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang masih memiliki stok di gudang dan tidak dapat dihapus.'
            ];
        }

        // Check if there are any purchase records
        if ($this->pembelianDetails()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki riwayat pembelian dan tidak dapat dihapus.'
            ];
        }

        // Check if there are any sales records
        if ($this->penjualanDetails()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki riwayat penjualan dan tidak dapat dihapus.'
            ];
        }

        // Check if there are any purchase return records
        if ($this->returPembelianDetails()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki riwayat retur pembelian dan tidak dapat dihapus.'
            ];
        }

        // Check if there are any sales return records
        if ($this->returPenjualanDetails()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki riwayat retur penjualan dan tidak dapat dihapus.'
            ];
        }

        // Check if there are any price rules
        if ($this->aturanHarga()->exists()) {
            return [
                'can_delete' => false,
                'reason' => 'Barang memiliki aturan harga dan tidak dapat dihapus.'
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null
        ];
    }
}


/* End of file Barang.php */
/* Location: ./app/Models/Barang.php */
/* Created at 2025-07-03 16:23:37 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */