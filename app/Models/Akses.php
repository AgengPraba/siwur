<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akses extends Model
{
    protected $table = 'akses';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',
        'toko_id',
        'role'
    ];
    
    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relasi ke Toko
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }
}
