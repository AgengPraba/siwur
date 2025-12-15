<?php

namespace App\Exports;

use App\Models\Penjualan;
use App\Models\Akses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanProfitExport implements WithMultipleSheets
{
    protected array $filters;
    protected $penjualans;
    protected array $summary;

    public function __construct(array $filters, $penjualans, array $summary)
    {
        $this->filters = $filters;
        $this->penjualans = $penjualans;
        $this->summary = $summary;
    }

    public function sheets(): array
    {
        return [
            'Ringkasan' => new LaporanProfitRingkasanSheet($this->filters, $this->summary),
            'Detail Transaksi' => new LaporanProfitDetailSheet($this->filters, $this->penjualans, $this->summary),
            'Detail Per Item' => new LaporanProfitItemSheet($this->filters, $this->penjualans),
        ];
    }
}
