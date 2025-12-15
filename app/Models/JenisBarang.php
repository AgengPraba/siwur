<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class JenisBarang extends Model
{
    use HasFactory;
    protected $table = 'jenis_barang';
    protected $fillable = ['nama_jenis_barang', 'keterangan', 'toko_id'];
    protected $primaryKey = 'id';

    /**
     * Relasi ke Toko
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    /**
     * Scope untuk filter berdasarkan toko_id user yang login
     */
    public function scopeForCurrentUser($query)
    {
        $user = Auth::user();
        if ($user && $user->akses) {
            return $query->where('toko_id', $user->akses->toko_id);
        }
        return $query;
    }

    /**
     * Get toko_id dari user yang sedang login
     */
    public static function getCurrentUserTokoId()
    {
        $user = Auth::user();
        return $user && $user->akses ? $user->akses->toko_id : null;
    }
}


/* End of file JenisBarang.php */
/* Location: ./app/Models/JenisBarang.php */
/* Created at 2025-07-03 16:23:15 */
/* Mohammad Irham Akbar Laravel 12 CRUD Generator : */