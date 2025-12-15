<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LaporanProfitDetailSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected array $filters;
    protected $penjualans;
    protected array $summary;
    protected int $index = 0;

    public function __construct(array $filters, $penjualans, array $summary)
    {
        $this->filters = $filters;
        $this->penjualans = $penjualans;
        $this->summary = $summary;
    }

    public function title(): string
    {
        return 'Detail Transaksi';
    }

    public function collection()
    {
        return collect($this->penjualans);
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Penjualan',
            'Customer',
            'Kasir',
            'Items',
            'Qty',
            'Subtotal',
            'Diskon',
            'Biaya Lain',
            'Total Penjualan',
            'Profit',
            'Margin (%)',
            'Status',
        ];
    }

    public function map($penjualan): array
    {
        $this->index++;
        
        $totalProfit = $penjualan->penjualanDetails->sum('profit');
        $totalDiskon = $penjualan->penjualanDetails->sum('diskon');
        $totalBiayaLain = $penjualan->penjualanDetails->sum('biaya_lain');
        $totalItems = $penjualan->penjualanDetails->count();
        $totalQuantity = $penjualan->penjualanDetails->sum('jumlah');
        $profitMargin = $penjualan->total_harga > 0 ? ($totalProfit / $penjualan->total_harga) * 100 : 0;

        return [
            $this->index,
            Carbon::parse($penjualan->tanggal_penjualan)->format('d/m/Y H:i'),
            $penjualan->nomor_penjualan,
            $penjualan->customer->nama_customer ?? '-',
            $penjualan->user->name ?? '-',
            $totalItems,
            number_format($totalQuantity, 2),
            $penjualan->penjualanDetails->sum('subtotal'),
            $totalDiskon,
            $totalBiayaLain,
            $penjualan->total_harga,
            $totalProfit,
            number_format($profitMargin, 2) . '%',
            $penjualan->status_label,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 18,
            'D' => 22,
            'E' => 15,
            'F' => 8,
            'G' => 10,
            'H' => 18,
            'I' => 15,
            'J' => 15,
            'K' => 18,
            'L' => 18,
            'M' => 12,
            'N' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            10 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                
                // ========== HEADER ==========
                $sheet->setCellValue('A1', strtoupper(config('app.name', 'SIWUR')));
                $sheet->mergeCells('A1:N1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->setCellValue('A2', 'LAPORAN DETAIL TRANSAKSI PROFIT');
                $sheet->mergeCells('A2:N2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Periode
                $startDate = Carbon::parse($this->filters['start_date'])->format('d M Y');
                $endDate = Carbon::parse($this->filters['end_date'])->format('d M Y');
                $sheet->setCellValue('A3', "Periode: {$startDate} - {$endDate}");
                $sheet->mergeCells('A3:N3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Info
                $sheet->setCellValue('A5', 'Dicetak: ' . now()->format('d/m/Y H:i'));
                $sheet->setCellValue('D5', 'Oleh: ' . (Auth::user()->name ?? 'System'));
                
                // Filter info
                $filterInfo = [];
                if (!empty($this->filters['customer_name'])) {
                    $filterInfo[] = 'Customer: ' . $this->filters['customer_name'];
                }
                if (!empty($this->filters['status'])) {
                    $filterInfo[] = 'Status: ' . ucfirst(str_replace('_', ' ', $this->filters['status']));
                }
                if (!empty($this->filters['search'])) {
                    $filterInfo[] = 'Pencarian: ' . $this->filters['search'];
                }
                if (!empty($filterInfo)) {
                    $sheet->setCellValue('A6', 'Filter: ' . implode(' | ', $filterInfo));
                    $sheet->mergeCells('A6:N6');
                }
                
                // Number formatting for currency columns
                $dataStartRow = 11;
                $dataEndRow = $highestRow;
                
                // Format currency columns (H, I, J, K, L)
                $currencyCols = ['H', 'I', 'J', 'K', 'L'];
                foreach ($currencyCols as $col) {
                    $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")
                        ->getNumberFormat()
                        ->setFormatCode('"Rp "#,##0');
                }
                
                // Center align specific columns
                $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M{$dataStartRow}:N{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align currency columns
                foreach ($currencyCols as $col) {
                    $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                // Add borders to data
                $sheet->getStyle("A10:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // ========== FOOTER TOTALS ==========
                $footerRow = $highestRow + 1;
                $sheet->setCellValue("A{$footerRow}", 'TOTAL');
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
                
                $sheet->setCellValue("F{$footerRow}", $this->summary['total_transaksi']);
                $sheet->setCellValue("H{$footerRow}", $this->summary['subtotal_sebelum_adjustment']);
                $sheet->setCellValue("I{$footerRow}", $this->summary['total_diskon']);
                $sheet->setCellValue("J{$footerRow}", $this->summary['total_biaya_lain']);
                $sheet->setCellValue("K{$footerRow}", $this->summary['total_penjualan']);
                $sheet->setCellValue("L{$footerRow}", $this->summary['total_profit']);
                $sheet->setCellValue("M{$footerRow}", number_format($this->summary['profit_margin'], 2) . '%');
                
                // Format footer currency
                foreach (['H', 'I', 'J', 'K', 'L'] as $col) {
                    $sheet->getStyle("{$col}{$footerRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                }
                
                // Profit color coding
                if ($this->summary['total_profit'] >= 0) {
                    $sheet->getStyle("L{$footerRow}")->getFont()->getColor()->setRGB('006600');
                } else {
                    $sheet->getStyle("L{$footerRow}")->getFont()->getColor()->setRGB('CC0000');
                }
                
                // Border for footer
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
            }
        ];
    }
}
