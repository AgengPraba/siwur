<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Satuan extends Model
{
    use HasFactory;
    protected $table = 'satuan';
    protected $fillable = ['nama_satuan','keterangan','toko_id'];
    protected $primaryKey = 'id';
    
    /**
     * Relasi ke Toko
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}


/* End of file Satuan.php */
/* Location: ./app/Models/Satuan.php */
/* Created at 2025-07-03 16:21:53 */
/* Mohammad Irham Akbar Laravel 12 CRUD Generator : */