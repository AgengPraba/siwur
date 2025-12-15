<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LaporanProfitRingkasanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected array $filters;
    protected array $summary;

    public function __construct(array $filters, array $summary)
    {
        $this->filters = $filters;
        $this->summary = $summary;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 20,
            'F' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ========== HEADER PERUSAHAAN ==========
                $sheet->setCellValue('A1', strtoupper(config('app.name', 'SIWUR')));
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->setCellValue('A2', 'LAPORAN LABA RUGI / PROFIT PENJUALAN');
                $sheet->mergeCells('A2:F2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Periode
                $startDate = Carbon::parse($this->filters['start_date'])->format('d M Y');
                $endDate = Carbon::parse($this->filters['end_date'])->format('d M Y');
                $sheet->setCellValue('A3', "Periode: {$startDate} - {$endDate}");
                $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // ========== INFO CETAK ==========
                $sheet->setCellValue('A5', 'Dicetak pada:');
                $sheet->setCellValue('B5', now()->format('d M Y H:i:s'));
                $sheet->setCellValue('A6', 'Dicetak oleh:');
                $sheet->setCellValue('B6', Auth::user()->name ?? 'System');
                
                // Customer filter info
                if (!empty($this->filters['customer_name'])) {
                    $sheet->setCellValue('A7', 'Customer:');
                    $sheet->setCellValue('B7', $this->filters['customer_name']);
                }
                
                // Status filter info
                if (!empty($this->filters['status'])) {
                    $row = !empty($this->filters['customer_name']) ? 8 : 7;
                    $sheet->setCellValue("A{$row}", 'Status:');
                    $sheet->setCellValue("B{$row}", ucfirst(str_replace('_', ' ', $this->filters['status'])));
                }
                
                // ========== RINGKASAN PENJUALAN ==========
                $startRow = 10;
                
                $sheet->setCellValue("A{$startRow}", 'RINGKASAN LAPORAN PROFIT');
                $sheet->mergeCells("A{$startRow}:F{$startRow}");
                $sheet->getStyle("A{$startRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
                $sheet->getStyle("A{$startRow}")->getFont()->getColor()->setRGB('FFFFFF');
                
                // ========== PENDAPATAN ==========
                $row = $startRow + 2;
                $sheet->setCellValue("A{$row}", 'PENDAPATAN');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6F3E6');
                $sheet->mergeCells("A{$row}:F{$row}");
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Penjualan Kotor (Subtotal)');
                $sheet->setCellValue("D{$row}", $this->summary['subtotal_sebelum_adjustment']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Dikurangi: Diskon Penjualan');
                $sheet->setCellValue("D{$row}", -$this->summary['total_diskon']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('CC0000');
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Ditambah: Biaya Lain-lain');
                $sheet->setCellValue("D{$row}", $this->summary['total_biaya_lain']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                
                $row++;
                $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue("A{$row}", 'Total Penjualan Bersih');
                $sheet->setCellValue("D{$row}", $this->summary['total_penjualan']);
                $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                
                // ========== LABA/RUGI ==========
                $row += 2;
                $sheet->setCellValue("A{$row}", 'LABA / RUGI');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E6');
                $sheet->mergeCells("A{$row}:F{$row}");
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Total Laba Kotor (Gross Profit)');
                $sheet->setCellValue("D{$row}", $this->summary['total_profit']);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                if ($this->summary['total_profit'] < 0) {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('CC0000');
                } else {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('006600');
                }
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Margin Laba (Profit Margin)');
                $sheet->setCellValue("D{$row}", number_format($this->summary['profit_margin'], 2) . '%');
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                if ($this->summary['profit_margin'] < 0) {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('CC0000');
                } else {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('006600');
                }
                
                // ========== STATISTIK TRANSAKSI ==========
                $row += 2;
                $sheet->setCellValue("A{$row}", 'STATISTIK TRANSAKSI');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6F3');
                $sheet->mergeCells("A{$row}:F{$row}");
                
                $row++;
                $sheet->setCellValue("A{$row}", '  Jumlah Transaksi');
                $sheet->setCellValue("D{$row}", number_format($this->summary['total_transaksi']) . ' transaksi');
                
                $row++;
                $averageTransaction = $this->summary['total_transaksi'] > 0 
                    ? $this->summary['total_penjualan'] / $this->summary['total_transaksi'] 
                    : 0;
                $sheet->setCellValue("A{$row}", '  Rata-rata per Transaksi');
                $sheet->setCellValue("D{$row}", $averageTransaction);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                
                $row++;
                $averageProfit = $this->summary['total_transaksi'] > 0 
                    ? $this->summary['total_profit'] / $this->summary['total_transaksi'] 
                    : 0;
                $sheet->setCellValue("A{$row}", '  Rata-rata Profit per Transaksi');
                $sheet->setCellValue("D{$row}", $averageProfit);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                if ($averageProfit < 0) {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('CC0000');
                } else {
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('006600');
                }
                
                // ========== PERNYATAAN ==========
                $row += 3;
                $sheet->setCellValue("A{$row}", 'Catatan:');
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
                $row++;
                $sheet->setCellValue("A{$row}", '- Laporan ini dihasilkan secara otomatis oleh sistem');
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);
                $row++;
                $sheet->setCellValue("A{$row}", '- Profit dihitung berdasarkan selisih harga jual dengan harga beli per item');
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);
                $row++;
                $sheet->setCellValue("A{$row}", '- Margin laba = (Total Profit / Total Penjualan) × 100%');
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);
                
                // ========== TTD ==========
                $row += 3;
                $sheet->setCellValue("E{$row}", 'Mengetahui,');
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row += 4;
                $sheet->setCellValue("E{$row}", '___________________');
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
                $sheet->setCellValue("E{$row}", 'Pimpinan/Akuntan');
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Border for the main content area
                $sheet->getStyle('A10:F' . ($row - 8))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }
}
