<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class Customer extends Model
{
    use HasFactory, HasTenancy;
    protected $table = 'customer';
    protected $fillable = [
        'nama_customer',
        'alamat',
        'no_hp',
        'email',
        'keterangan',
        'is_opname',
        'toko_id'
    ];
    protected $primaryKey = 'id';

    // Relationship dengan Penjualan
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'customer_id');
    }

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    // Accessor untuk nama lengkap dengan info kontak
    public function getDisplayNameAttribute()
    {
        $display = $this->nama_customer;
        if ($this->no_hp) {
            $display .= ' (' . $this->no_hp . ')';
        }
        return $display;
    }

    // Accessor untuk format nomor HP
    public function getFormattedNoHpAttribute()
    {
        if (!$this->no_hp) return '-';
        
        // Format nomor HP Indonesia
        $no_hp = preg_replace('/[^0-9]/', '', $this->no_hp);
        if (substr($no_hp, 0, 1) === '0') {
            $no_hp = '62' . substr($no_hp, 1);
        }
        
        return '+' . $no_hp;
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nama_customer) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(no_hp) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }
        return $query;
    }

    public function scopeForCurrentUserToko($query)
    {
        return $this->scopeForToko($query);
    }

    // Scope untuk customer aktif (yang memiliki transaksi)
    public function scopeActive($query)
    {
        return $query->whereHas('penjualan');
    }

    // Method untuk mendapatkan total penjualan customer
    public function getTotalPenjualanAttribute()
    {
        return $this->penjualan()->sum('total_harga');
    }

    // Method untuk mendapatkan jumlah transaksi customer
    public function getJumlahTransaksiAttribute()
    {
        return $this->penjualan()->count();
    }
}

/* End of file Customer.php */
/* Location: ./app/Models/Customer.php */
/* Created at 2025-07-03 16:23:31 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */