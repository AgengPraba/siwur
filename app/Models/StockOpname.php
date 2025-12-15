<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasTenancy;
use Carbon\Carbon;

class StockOpname extends Model
{
    use HasTenancy;

    protected $table = 'stock_opname';
    
    protected $fillable = [
        'nomor_opname',
        'tanggal_opname',
        'toko_id',
        'user_id',
        'gudang_id',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_opname' => 'datetime',
    ];

    // Relationships
    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    // Accessors
    public function getTanggalOpnameFormattedAttribute()
    {
        return $this->tanggal_opname ? $this->tanggal_opname->format('d M Y H:i') : '-';
    }

    public function getTotalItemsAttribute()
    {
        return $this->details()->count();
    }

    public function getTotalSelisihAttribute()
    {
        return $this->details()->sum('selisih');
    }

    public function getTotalStokSistemAttribute()
    {
        return $this->details()->sum('stok_sistem');
    }

    public function getTotalStokFisikAttribute()
    {
        return $this->details()->sum('stok_fisik');
    }

    public static function generateNomor($tokoId = null)
    {
        $user = Auth::user();
        $tokoId =  $user->akses->toko_id;
        $prefix = 'OPN' .$tokoId. date('Ymd');

        $last = self::where('nomor_opname', 'like', $prefix . '%')
            ->where('toko_id', $tokoId)
            ->latest('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->nomor_opname, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeByGudang($query, $gudangId)
    {
        return $query->where('gudang_id', $gudangId);
    }

    // Method untuk handle stock adjustment
    public function adjustStock()
    {
        foreach ($this->details as $detail) {
            if ($detail->gudang_stock_id) {
                $gudangStock = GudangStock::find($detail->gudang_stock_id);
                if ($gudangStock) {
                    // Lock row untuk prevent race condition
                    $gudangStock = GudangStock::lockForUpdate()->find($detail->gudang_stock_id);
                    
                    // Update jumlah sesuai stok fisik
                    $gudangStock->update([
                        'jumlah' => $detail->after_qty
                    ]);
                }
            }
        }
    }
    
    // Method untuk revert stock adjustment saat delete
    public function revertStockAdjustment()
    {
        // Load details relationship jika belum di-load
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        
        foreach ($this->details as $detail) {
            if ($detail->gudang_stock_id) {
                $gudangStock = GudangStock::find($detail->gudang_stock_id);
                if ($gudangStock) {
                    // Lock row untuk prevent race condition
                    $gudangStock = GudangStock::lockForUpdate()->find($detail->gudang_stock_id);
                    
                    // Calculate the adjustment amount instead of directly setting to before_qty
                    $selisih = $detail->after_qty - $detail->before_qty;
                    
                    // Apply the reverse adjustment (increment/decrement) to preserve other transactions
                    if ($selisih > 0) {
                        // If adjustment was an increase, decrease the stock by the same amount
                        $gudangStock->decrement('jumlah', $selisih);
                    } elseif ($selisih < 0) {
                        // If adjustment was a decrease, increase the stock by the same amount
                        $gudangStock->increment('jumlah', abs($selisih));
                    }
                    
                    // Log the reversion
                    \Illuminate\Support\Facades\Log::info(
                        "Stock opname reversion for GudangStock ID {$gudangStock->id}: " .
                        "Current: {$gudangStock->jumlah}, " . 
                        "Adjustment: " . (-$selisih) . ", " .
                        "Before: {$detail->before_qty}, " .
                        "After: {$detail->after_qty}"
                    );
                }
            }
        }
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_opname', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Boot method untuk auto-generate nomor
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_opname)) {
                $model->nomor_opname = self::generateNomor($model->toko_id);
            }
            
            if (empty($model->tanggal_opname)) {
                $model->tanggal_opname = Carbon::now();
            }
        });
        
        static::deleting(function ($model) {
            // Revert stock adjustment sebelum delete
            $model->revertStockAdjustment();
            
            // Delete all details after revert
            $model->details()->delete();
        });
    }
}