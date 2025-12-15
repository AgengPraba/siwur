<?php

namespace App\Services;

use App\Exports\LaporanPembayaranExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanPembayaranExcelService
{
    public function generateExcel($filters)
    {
        $filename = $this->generateFilename($filters);
        
        return Excel::download(new LaporanPembayaranExport($filters), $filename);
    }
    
    private function generateFilename($filters)
    {
        $jenis = $filters['jenis_laporan'];
        $tanggal = Carbon::parse($filters['tanggal_mulai'])->format('Y-m-d');
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "laporan-pembayaran-{$jenis}-{$tanggal}-{$timestamp}.xlsx";
    }
}