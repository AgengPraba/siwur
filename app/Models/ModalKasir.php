<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class ModalKasir extends Model
{
    use HasTenancy;
    
    protected $table = 'modal_kasir';

    protected $primaryKey = 'id';

    protected $fillable = [
        'tanggal',
        'modal',
        'toko_id',
    ];
}
