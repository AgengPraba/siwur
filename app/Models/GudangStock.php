<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GudangStock extends Model
{
    use HasFactory;
    protected $table = 'gudang_stock';
    protected $fillable = ['gudang_id','barang_id','jumlah',];
    protected $primaryKey = 'id';
    protected $casts = [
        'jumlah' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship dengan Gudang
    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'gudang_id');
    }

    // Relationship dengan Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relationship dengan TransaksiGudangStock
    public function transaksiGudangStock()
    {
        return $this->hasMany(TransaksiGudangStock::class, 'gudang_stock_id');
    }

    // Relationship untuk mendapatkan satuan terkecil melalui barang
    public function satuanTerkecil()
    {
        return $this->hasOneThrough(
            Satuan::class,
            Barang::class,
            'id', // Foreign key on barang table
            'id', // Foreign key on satuan table
            'barang_id', // Local key on gudang_stock table
            'satuan_terkecil_id' // Local key on barang table
        );
    }

    // Accessor untuk status stok berdasarkan jumlah
    public function getStockStatusAttribute()
    {
        if ($this->jumlah > 50) {
            return 'high';
        } elseif ($this->jumlah > 20) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    // Accessor untuk warna status stok
    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'high' => 'green',
            'medium' => 'yellow',
            'low' => 'red',
            default => 'gray'
        };
    }

    // Accessor untuk label status stok
    public function getStockStatusLabelAttribute()
    {
        return match($this->stock_status) {
            'high' => 'Stok Aman',
            'medium' => 'Stok Sedang',
            'low' => 'Stok Rendah',
            default => 'Tidak Diketahui'
        };
    }

    // Scope untuk filter berdasarkan status stok
    public function scopeByStockStatus($query, $status)
    {
        return match($status) {
            'high' => $query->where('jumlah', '>', 50),
            'medium' => $query->whereBetween('jumlah', [21, 50]),
            'low' => $query->where('jumlah', '<=', 20),
            default => $query
        };
    }

    // Scope untuk filter berdasarkan toko_id user yang login
    public function scopeForCurrentUserToko($query)
    {
        if (Auth::check() && Auth::user()->akses) {
            $tokoId = Auth::user()->akses->toko_id;
            return $query->whereHas('gudang', function($q) use ($tokoId) {
                        $q->where('toko_id', $tokoId);
                    })
                    ->whereHas('barang', function($q) use ($tokoId) {
                        $q->where('toko_id', $tokoId);
                    });
        }
        return $query;
    }

    // Method untuk mendapatkan data gudang stock dengan relasi dan filter toko
    public static function getWithRelationsForCurrentToko($id)
    {
        if (!Auth::check() || !Auth::user()->akses) {
            return null;
        }
        
        $tokoId = Auth::user()->akses->toko_id;
        
        return static::with(['gudang', 'barang', 'satuanTerkecil'])
            ->join('barang', 'gudang_stock.barang_id', '=', 'barang.id')
            ->join('gudang', 'gudang_stock.gudang_id', '=', 'gudang.id')
            ->leftJoin('satuan', 'barang.satuan_terkecil_id', '=', 'satuan.id')
            ->where('barang.toko_id', $tokoId)
            ->where('gudang.toko_id', $tokoId)
            ->select(
                'gudang_stock.*',
                'barang.nama_barang',
                'barang.keterangan',
                'gudang.nama_gudang',
                'satuan.nama_satuan as satuan_terkecil'
            )
            ->findOrFail($id);
    }

    // Scope untuk pencarian global
    public function scopeSearch($query, $search)
    {
        $baseQuery = $query->join('barang', 'gudang_stock.barang_id', '=', 'barang.id')
                          ->join('gudang', 'gudang_stock.gudang_id', '=', 'gudang.id')
                          ->join('satuan', 'barang.satuan_terkecil_id', '=', 'satuan.id')
                          ->select(
                              'gudang_stock.*', 
                              'barang.nama_barang', 
                              'gudang.nama_gudang',
                              'satuan.nama_satuan as satuan_terkecil'
                          );
        
        if (empty($search)) {
            return $baseQuery;
        }
        
        return $baseQuery->where(function($q) use ($search) {
            $q->whereRaw('LOWER(barang.nama_barang) LIKE ?', ["%".strtolower($search)."%"])
              ->orWhereRaw('LOWER(gudang.nama_gudang) LIKE ?', ["%".strtolower($search)."%"])
              ->orWhereRaw('LOWER(gudang_stock.jumlah) LIKE ?', ["%".strtolower($search)."%"])
              ->orWhereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%".strtolower($search)."%"]);
        });
    }
}


/* End of file GudangStock.php */
/* Location: ./app/Models/GudangStock.php */
/* Created at 2025-07-03 16:23:50 */
/* Updated: Added satuan terkecil relationship, enhanced search functionality, and toko_id filtering */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator */