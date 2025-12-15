<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    protected $table = 'stock_opname_detail';
    protected $fillable = [
        'stock_opname_id',
        'gudang_stock_id',
        'before_qty',
        'after_qty',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'adjustment_type',
        'keterangan'
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function gudangStock()
    {
        return $this->belongsTo(GudangStock::class);
    }

    public function setStokFisikAttribute($value)
    {
        $this->attributes['stok_fisik'] = $value;
        $this->attributes['after_qty'] = $value;
    
        // Ambil stok sistem yang sudah tersimpan
        $stokSistem = $this->attributes['stok_sistem'] ?? 0;
        $this->attributes['before_qty'] = $stokSistem;
    
        // Hitung selisih
        $selisih = $value - $stokSistem;
        $this->attributes['selisih'] = $selisih;
        
        // Set adjustment type
        if ($selisih > 0) {
            $this->attributes['adjustment_type'] = 'plus';
        } elseif ($selisih < 0) {
            $this->attributes['adjustment_type'] = 'minus';
        } else {
            $this->attributes['adjustment_type'] = 'sama';
        }
    }
    
    public function getSelisihLabelAttribute()
    {
        if ($this->selisih > 0) {
            return "+{$this->selisih} (Penambahan)";
        }
    
        if ($this->selisih < 0) {
            return abs($this->selisih) . " (Pengurangan)";
        }
    
        return "0 (Tidak Ada Perubahan)";
    }
    
    public function getAdjustmentInfoAttribute()
    {
        switch($this->adjustment_type) {
            case 'plus':
                return [
                    'type' => 'penambahan',
                    'icon' => 'o-arrow-up',
                    'class' => 'text-green-600 bg-green-100',
                    'label' => "+{$this->selisih}"
                ];
            case 'minus':
                return [
                    'type' => 'pengurangan', 
                    'icon' => 'o-arrow-down',
                    'class' => 'text-red-600 bg-red-100',
                    'label' => "{$this->selisih}"
                ];
            default:
                return [
                    'type' => 'tidak_berubah',
                    'icon' => 'o-minus',
                    'class' => 'text-gray-600 bg-gray-100',
                    'label' => '0'
                ];
        }
    }

}