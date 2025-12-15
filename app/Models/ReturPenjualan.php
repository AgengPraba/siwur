<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturPenjualan extends Model
{
    use HasTenancy;

    protected $table = 'retur_penjualan';

    protected $fillable = [
        'nomor_retur_penjualan',
        'penjualan_id',
        'customer_id',
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
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(ReturPenjualanDetail::class);
    }

    // Accessors
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
        $prefix = 'RTJ';
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

    public function scopeByCustomer($query, $customerId)
    {
        if ($customerId) {
            return $query->where('customer_id', $customerId);
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

    // Additional accessors
    public function getNomorReturAttribute()
    {
        return $this->nomor_retur_penjualan;
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

    public function getKeteranganAttribute()
    {
        return $this->catatan;
    }

    /**
     * Reverse stock changes when retur is deleted
     * For retur penjualan: reduce stock (because original retur increased stock)
     */
    public function reverseStockChanges()
    {
        foreach ($this->details as $detail) {
            $gudangStock = \App\Models\GudangStock::where([
                'gudang_id' => $this->gudang_id,
                'barang_id' => $detail->barang_id
            ])->first();
            
            if ($gudangStock) {
                // Get konversi from original penjualan detail
                $konversiSatuanTerkecil = 1;
                if ($this->penjualan) {
                    $penjualanDetail = $this->penjualan->penjualanDetails()
                        ->where('barang_id', $detail->barang_id)
                        ->where('satuan_id', $detail->satuan_id)
                        ->first();
                    
                    if ($penjualanDetail) {
                        $konversiSatuanTerkecil = $penjualanDetail->konversi_satuan_terkecil;
                    }
                }
                
                $jumlahTerkecil = $detail->qty_retur * $konversiSatuanTerkecil;
                
                // Check if there's enough stock to reduce
                if ($gudangStock->jumlah >= $jumlahTerkecil) {
                    // Reduce stock (reverse the addition from retur penjualan)
                    $gudangStock->decrement('jumlah', $jumlahTerkecil);
                } else {
                    // If not enough stock, set to 0 and log warning
                    \Illuminate\Support\Facades\Log::warning("Insufficient stock to reverse retur penjualan. Available: {$gudangStock->jumlah}, Required: {$jumlahTerkecil}");
                    $gudangStock->update(['jumlah' => 0]);
                }
                
                // Delete related stock transaction records for this retur
                \App\Models\TransaksiGudangStock::where('gudang_stock_id', $gudangStock->id)
                    ->where('tipe', 'masuk')
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
        
        static::deleting(function ($returPenjualan) {
            // Load details relationship before deletion
            $returPenjualan->load('details', 'penjualan.penjualanDetails');
            
            // Reverse stock changes
            $returPenjualan->reverseStockChanges();
        });
    }
}