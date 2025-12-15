<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

trait LivewireTenancy
{
    use Toast;

    /**
     * Get current user's toko_id
     */
    protected function getCurrentTokoId()
    {
        if (!Auth::check() || !Auth::user()->akses) {
            $this->error('Error', 'Anda tidak memiliki akses ke toko manapun.');
            return null;
        }
        
        return Auth::user()->akses->toko_id;
    }

    /**
     * Check if user has toko access
     */
    protected function checkTokoAccess()
    {
        if (!$this->getCurrentTokoId()) {
            $this->error('Akses Ditolak', 'Anda tidak memiliki akses ke toko ini.');
            return false;
        }
        
        return true;
    }

    /**
     * Get current toko information
     */
    protected function getCurrentToko()
    {
        if (!Auth::check() || !Auth::user()->akses) {
            return null;
        }
        
        return Auth::user()->akses->toko;
    }

    /**
     * Scope query to current toko
     */
    protected function scopeToCurrentToko($query)
    {
        $tokoId = $this->getCurrentTokoId();
        
        if ($tokoId) {
            return $query->where('toko_id', $tokoId);
        }
        
        return $query->whereNull('id'); // Return empty result if no toko access
    }

    /**
     * Validate record belongs to current toko
     */
    protected function validateTokoOwnership($model)
    {
        $currentTokoId = $this->getCurrentTokoId();
        
        if (!$currentTokoId) {
            return false;
        }
        
        if ($model->toko_id !== $currentTokoId) {
            $this->error('Akses Ditolak', 'Data ini tidak dapat diakses dari toko Anda.');
            return false;
        }
        
        return true;
    }
}