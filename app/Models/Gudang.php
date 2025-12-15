<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class Gudang extends Model
{
    use HasFactory, HasTenancy;
    protected $table = 'gudang';
    protected $fillable = ['nama_gudang','keterangan','toko_id'];
    protected $primaryKey = 'id';

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    // Relationship dengan GudangStock
    public function gudangStock()
    {
        return $this->hasMany(GudangStock::class, 'gudang_id');
    }

    public function scopeForCurrentUserToko($query)
    {
        return $this->scopeForToko($query);
    }
}


/* End of file Gudang.php */
/* Location: ./app/Models/Gudang.php */
/* Created at 2025-07-03 16:23:21 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */