<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTenancy
{
    /**
     * Boot the trait
     */
    protected static function bootHasTenancy()
    {
        // Automatically scope all queries to current user's toko_id
        static::addGlobalScope('tenancy', function (Builder $builder) {
            if (Auth::check() && Auth::user()->akses) {
                // Deteksi apakah query menggunakan alias tabel
                $from = $builder->getQuery()->from;
                $table = $builder->getModel()->getTable();
                
                // Jika ada alias (format: "table as alias"), gunakan alias
                if (strpos($from, ' as ') !== false) {
                    $parts = explode(' as ', $from);
                    $tableReference = trim($parts[1]);
                } else {
                    $tableReference = $table;
                }
                
                $builder->where($tableReference . '.toko_id', Auth::user()->akses->toko_id);
            }
        });

        // Automatically set toko_id when creating new records
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->akses && !$model->toko_id) {
                $model->toko_id = Auth::user()->akses->toko_id;
            }
        });
    }

    /**
     * Scope query to specific toko
     */
    public function scopeForToko(Builder $query, $tokoId = null)
    {
        $tokoId = $tokoId ?? (Auth::check() && Auth::user()->akses ? Auth::user()->akses->toko_id : null);
        
        if ($tokoId) {
            // Deteksi apakah query menggunakan alias tabel
            $from = $query->getQuery()->from;
            $table = $query->getModel()->getTable();
            
            // Jika ada alias (format: "table as alias"), gunakan alias
            if (strpos($from, ' as ') !== false) {
                $parts = explode(' as ', $from);
                $tableReference = trim($parts[1]);
            } else {
                $tableReference = $table;
            }
            
            return $query->where($tableReference . '.toko_id', $tokoId);
        }
        
        return $query;
    }

    /**
     * Get current user's toko_id
     */
    public static function getCurrentTokoId()
    {
        return Auth::check() && Auth::user()->akses ? Auth::user()->akses->toko_id : null;
    }

    /**
     * Check if current user has access to this record
     */
    public function belongsToCurrentToko()
    {
        return $this->toko_id === static::getCurrentTokoId();
    }
}