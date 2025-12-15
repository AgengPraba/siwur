<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\HasTenancy;

class Penjualan extends Model
{
    use HasFactory, HasTenancy;
    protected $table = 'penjualan';
    protected $fillable = [
        'nomor_penjualan',
        'tanggal_penjualan',
        'customer_id',
        'user_id',
        'keterangan',
        'total_harga',
        'status',
        'toko_id',
        'kembalian',
    ];
    protected $primaryKey = 'id';

    protected $casts = [
        'tanggal_penjualan' => 'datetime',
        'total_harga' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'belum_bayar',
    ];

    // Relationship dengan Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship dengan Toko
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    // Relationship dengan PenjualanDetail
    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    // Relationship dengan PembayaranPenjualan
    public function pembayaranPenjualan()
    {
        return $this->hasMany(PembayaranPenjualan::class, 'penjualan_id');
    }

    // Relationship dengan ReturPenjualan
    public function returPenjualan()
    {
        return $this->hasMany(ReturPenjualan::class, 'penjualan_id');
    }

    // Accessor untuk format tanggal yang lebih baik
    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_penjualan->format('d M Y, H:i');
    }

    // Accessor untuk format mata uang
    public function getFormattedTotalHargaAttribute()
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    // Accessor untuk total diskon dari detail
    public function getTotalDiskonAttribute()
    {
        return $this->penjualanDetails()->sum('diskon');
    }

    // Accessor untuk total biaya lain dari detail
    public function getTotalBiayaLainAttribute()
    {
        return $this->penjualanDetails()->sum('biaya_lain');
    }

    // Accessor untuk subtotal sebelum diskon dan biaya lain
    public function getSubtotalSebelumAdjustmentAttribute()
    {
        return $this->penjualanDetails()->sum('subtotal');
    }

    // Accessor untuk format total diskon
    public function getFormattedTotalDiskonAttribute()
    {
        return 'Rp ' . number_format($this->total_diskon, 0, ',', '.');
    }

    // Accessor untuk format total biaya lain
    public function getFormattedTotalBiayaLainAttribute()
    {
        return 'Rp ' . number_format($this->total_biaya_lain, 0, ',', '.');
    }

    // Accessor untuk total item count
    public function getTotalItemsAttribute()
    {
        return $this->penjualanDetails()->count();
    }

    // Accessor untuk total quantity
    public function getTotalQuantityAttribute()
    {
        return $this->penjualanDetails()->sum('jumlah');
    }

    // Accessor untuk total pembayaran
    public function getTotalPembayaranAttribute()
    {
        return $this->pembayaranPenjualan()->sum('jumlah');
    }

    // Accessor untuk format total pembayaran
    public function getFormattedTotalPembayaranAttribute()
    {
        return 'Rp ' . number_format($this->total_pembayaran, 0, ',', '.');
    }

    // Accessor untuk total kembalian (dari kolom kembalian di tabel penjualan)
    public function getTotalKembalianAttribute()
    {
        $storedChange = (float) ($this->attributes['kembalian'] ?? 0);
        if ($storedChange > 0) {
            return $storedChange;
        }

        $totalPaid = $this->relationLoaded('pembayaranPenjualan')
            ? (float) $this->pembayaranPenjualan->sum('jumlah')
            : (float) $this->pembayaranPenjualan()->sum('jumlah');

        $totalHarga = (float) ($this->attributes['total_harga'] ?? 0);

        return max($totalPaid - $totalHarga, 0);
    }

    // Accessor untuk format total kembalian
    public function getFormattedTotalKembalianAttribute()
    {
        return 'Rp ' . number_format($this->total_kembalian, 0, ',', '.');
    }

    // Accessor untuk kembalian per pembayaran (untuk display)
    public function getKembalianPerPaymentAttribute()
    {
        $payments = $this->pembayaranPenjualan;
        $totalHarga = $this->total_harga;
        $remainingAmount = $totalHarga;

        $kembalianPerPayment = [];

        foreach ($payments as $payment) {
            if ($remainingAmount <= 0) {
                // If already fully paid, all payment goes to kembalian
                $kembalianPerPayment[] = [
                    'payment_id' => $payment->id,
                    'kembalian' => $payment->jumlah
                ];
            } else {
                // Calculate how much of this payment is kembalian
                $kembalian = max(0, $payment->jumlah - $remainingAmount);
                $kembalianPerPayment[] = [
                    'payment_id' => $payment->id,
                    'kembalian' => $kembalian
                ];
                $remainingAmount -= min($payment->jumlah, $remainingAmount);
            }
        }

        return $kembalianPerPayment;
    }

    /**
     * Recalculate total harga berdasarkan detail penjualan
     * 
     * Menghitung ulang total harga penjualan berdasarkan:
     * - Subtotal dari semua detail penjualan
     * - Ditambah total biaya lain dari detail
     * - Dikurangi total diskon dari detail
     * 
     * Formula: Total = sum(subtotal) + sum(biaya_lain) - sum(diskon)
     * 
     * @return float Total harga yang telah dihitung
     */
    public function recalculateTotalHarga()
    {
        // Hitung total dari detail penjualan
        $totalSubtotal = $this->penjualanDetails()->sum('subtotal');
        $totalBiayaLain = $this->penjualanDetails()->sum('biaya_lain');
        $totalDiskon = $this->penjualanDetails()->sum('diskon');

        // Set nilai total yang dihitung
    $calculatedTotal = (round(((float) $totalSubtotal) / 100) * 100) + $totalBiayaLain - $totalDiskon;
    /** @phpstan-ignore-next-line */
    $this->total_harga = $calculatedTotal;

        // Simpan perubahan ke database
        $this->save();

        return (float) $calculatedTotal;
    }

    /**
     * Method untuk trigger recalculate total secara otomatis
     * Digunakan oleh event listener di PenjualanDetail
     * 
     * @return void
     */
    public function autoRecalculateTotal()
    {
        $this->recalculateTotalHarga();
    }


    // Accessor untuk status badge class
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'belum_bayar' => 'badge-error',
            'belum_lunas' => 'badge-warning',
            'lunas' => 'badge-success',
            default => 'badge-neutral'
        };
    }

    // Accessor untuk status label
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'belum_bayar' => 'Belum Bayar',
            'belum_lunas' => 'Belum Lunas',
            'lunas' => 'Lunas',
            default => 'Unknown'
        };
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    // Scope untuk filter berdasarkan customer
    public function scopeByCustomer($query, $customerId)
    {
        if ($customerId) {
            return $query->where('customer_id', $customerId);
        }
        return $query;
    }

    // Scope untuk filter berdasarkan rentang tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('tanggal_penjualan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal_penjualan', '<=', $endDate);
        }
        return $query;
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nomor_penjualan) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->whereRaw('LOWER(nama_customer) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    });
            });
        }
        return $query;
    }
}

/* End of file Penjualan.php */
/* Location: ./app/Models/Penjualan.php */
/* Created at 2025-07-03 16:22:50 */
/* Mohammad Irham Akbar Laravel 11 CRUD Generator : */