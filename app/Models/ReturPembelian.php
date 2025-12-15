<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturPembelian extends Model
{
    use HasTenancy;

    protected $table = 'retur_pembelian';

    protected $fillable = [
        'nomor_retur_pembelian',
        'pembelian_id',
        'supplier_id',
        'gudang_id',
        'toko_id',
        'tanggal_retur',
        'catatan',
        'dibuat_oleh'
    ];

    protected $casts = [
        'tanggal_retur' => 'datetime'
    ];

    // Relationships
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class);
    }

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReturPembelianDetail::class);
    }

    // Accessors
    public function getNomorReturAttribute()
    {
        return $this->nomor_retur_pembelian;
    }
    
    public function getKeteranganAttribute()
    {
        return $this->catatan;
    }
    
    public function getTanggalReturFormattedAttribute()
    {
        return $this->tanggal_retur ? $this->tanggal_retur->format('d/m/Y H:i') : '-';
    }
    
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '-';
    }
    
    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total_nilai_retur, 0, ',', '.');
    }

    public function getTotalItemsAttribute()
    {
        return $this->details()->count();
    }

    public function getTotalQtyReturAttribute()
    {
        return $this->details()->sum('qty_retur');
    }

    public function getTotalNilaiReturAttribute()
    {
        return $this->details()->sum('total_harga');
    }

    public function getFormattedTotalNilaiReturAttribute()
    {
        return number_format($this->total_nilai_retur, 0, ',', '.');
    }

    // Static helper for generating nomor retur
    public static function generateNomor($tokoId = null)
    {
        $tokoId = $tokoId ?? Auth::user()->akses->toko_id ?? 1;
        $today = Carbon::now();
        $prefix = 'RTB';
        $dateFormat = $today->format('Ymd');
        
        $lastNumber = self::where('toko_id', $tokoId)
            ->whereDate('created_at', $today->toDateString())
            ->count();
        
        $sequence = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $dateFormat . $sequence;
    }

    // Scopes
    public function scopeForCurrentUserToko($query)
    {
        $user = Auth::user();
        if ($user && $user->akses && $user->akses->toko_id) {
            return $query->where('toko_id', $user->akses->toko_id);
        }
        return $query;
    }

    public function scopeBySupplier($query, $supplierId)
    {
        if ($supplierId) {
            return $query->where('supplier_id', $supplierId);
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            // Pastikan tanggal akhir mencakup seluruh hari (sampai 23:59:59)
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            return $query->whereBetween('tanggal_retur', [$start, $end]);
        }
        return $query;
    }

    /**
     * Reverse stock changes when retur is deleted
     * For retur pembelian: add back stock (because original retur reduced stock)
     */
    public function reverseStockChanges()
    {
        foreach ($this->details as $detail) {
            $gudangStock = \App\Models\GudangStock::where([
                'gudang_id' => $this->gudang_id,
                'barang_id' => $detail->barang_id
            ])->first();
            
            if ($gudangStock) {
                // Get konversi from original pembelian detail
                $konversiSatuanTerkecil = 1;
                if ($this->pembelian) {
                    $pembelianDetail = $this->pembelian->pembelianDetails()
                        ->where('barang_id', $detail->barang_id)
                        ->where('satuan_id', $detail->satuan_id)
                        ->first();
                    
                    if ($pembelianDetail) {
                        $konversiSatuanTerkecil = $pembelianDetail->konversi_satuan_terkecil;
                    }
                }
                
                $jumlahTerkecil = $detail->qty_retur * $konversiSatuanTerkecil;
                
                // Add back stock (reverse the reduction from retur pembelian)
                $gudangStock->increment('jumlah', $jumlahTerkecil);
                
                // Delete related stock transaction records for this retur
                \App\Models\TransaksiGudangStock::where('gudang_stock_id', $gudangStock->id)
                    ->where('tipe', 'keluar')
                    ->where('jumlah', $detail->qty_retur)
                    ->where('konversi_satuan_terkecil', $konversiSatuanTerkecil)
                    ->whereNull('pembelian_detail_id')
                    ->whereNull('penjualan_detail_id')
                    ->delete();
            }
        }
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($returPembelian) {
            // Load details relationship before deletion
            $returPembelian->load('details', 'pembelian.pembelianDetails');
            
            // Reverse stock changes
            $returPembelian->reverseStockChanges();
        });
    }
}