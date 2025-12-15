<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class Supplier extends Model
{
    use HasFactory, HasTenancy;
    protected $table = 'supplier';
    protected $fillable = ['nama_supplier', 'alamat', 'no_hp', 'email', 'keterangan', 'is_opname', 'toko_id'];
    protected $primaryKey = 'id';

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'supplier_id');
    }

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function scopeForCurrentUserToko($query)
    {
        return $this->scopeForToko($query);
    }
}


/* End of file Supplier.php */
/* Location: ./app/Models/Supplier.php */
/* Created at 2025-07-03 16:22:37 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */