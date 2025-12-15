<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    protected $table = 'toko';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nama_toko',
        'logo_toko',
        'alamat_toko',
        'user_id'
    ];
    
    /**
     * Relasi ke User (pemilik toko)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relasi ke Akses (user yang memiliki akses ke toko)
     */
    public function akses(): HasMany
    {
        return $this->hasMany(Akses::class);
    }
}
