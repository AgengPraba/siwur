<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanProfitItemSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected array $filters;
    protected $penjualans;

    public function __construct(array $filters, $penjualans)
    {
        $this->filters = $filters;
        $this->penjualans = $penjualans;
    }

    public function title(): string
    {
        return 'Detail Per Item';
    }

    public function collection()
    {
        $items = collect();
        $index = 0;

        foreach ($this->penjualans as $penjualan) {
            foreach ($penjualan->penjualanDetails as $detail) {
                $index++;
                $subtotalFinal = $detail->subtotal - $detail->diskon + $detail->biaya_lain;
                $margin = $subtotalFinal > 0 ? ($detail->profit / $subtotalFinal) * 100 : 0;

                $items->push([
                    'no' => $index,
                    'tanggal' => Carbon::parse($penjualan->tanggal_penjualan)->format('d/m/Y'),
                    'no_penjualan' => $penjualan->nomor_penjualan,
                    'customer' => $penjualan->customer->nama_customer ?? '-',
                    'nama_barang' => $detail->barang->nama_barang ?? '-',
                    'satuan' => $detail->satuan->nama_satuan ?? '-',
                    'qty' => number_format($detail->jumlah, 2),
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                    'diskon' => $detail->diskon,
                    'biaya_lain' => $detail->biaya_lain,
                    'subtotal_final' => $subtotalFinal,
                    'profit' => $detail->profit,
                    'margin' => number_format($margin, 2) . '%',
                ]);
            }
        }

        return $items;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Penjualan',
            'Customer',
            'Nama Barang',
            'Satuan',
            'Qty',
            'Harga Satuan',
            'Subtotal',
            'Diskon',
            'Biaya Lain',
            'Subtotal Final',
            'Profit',
            'Margin (%)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 16,
            'D' => 20,
            'E' => 25,
            'F' => 12,
            'G' => 10,
            'H' => 15,
            'I' => 15,
            'J' => 12,
            'K' => 12,
            'L' => 15,
            'M' => 15,
            'N' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E7D32']
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
                
                $sheet->setCellValue('A2', 'LAPORAN DETAIL PROFIT PER ITEM');
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
                
                // Number formatting for currency columns (H, I, J, K, L, M)
                $dataStartRow = 9;
                $dataEndRow = $highestRow;
                
                $currencyCols = ['H', 'I', 'J', 'K', 'L', 'M'];
                foreach ($currencyCols as $col) {
                    $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")
                        ->getNumberFormat()
                        ->setFormatCode('"Rp "#,##0');
                }
                
                // Center align specific columns
                $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("N{$dataStartRow}:N{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align currency columns
                foreach ($currencyCols as $col) {
                    $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                // Add borders to data
                $sheet->getStyle("A8:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // ========== FOOTER TOTALS ==========
                $footerRow = $highestRow + 1;
                $sheet->setCellValue("A{$footerRow}", 'TOTAL');
                $sheet->mergeCells("A{$footerRow}:G{$footerRow}");
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
                
                // Calculate totals
                $totalSubtotal = 0;
                $totalDiskon = 0;
                $totalBiayaLain = 0;
                $totalSubtotalFinal = 0;
                $totalProfit = 0;

                foreach ($this->penjualans as $penjualan) {
                    foreach ($penjualan->penjualanDetails as $detail) {
                        $totalSubtotal += $detail->subtotal;
                        $totalDiskon += $detail->diskon;
                        $totalBiayaLain += $detail->biaya_lain;
                        $totalSubtotalFinal += ($detail->subtotal - $detail->diskon + $detail->biaya_lain);
                        $totalProfit += $detail->profit;
                    }
                }
                
                $totalMargin = $totalSubtotalFinal > 0 ? ($totalProfit / $totalSubtotalFinal) * 100 : 0;
                
                $sheet->setCellValue("I{$footerRow}", $totalSubtotal);
                $sheet->setCellValue("J{$footerRow}", $totalDiskon);
                $sheet->setCellValue("K{$footerRow}", $totalBiayaLain);
                $sheet->setCellValue("L{$footerRow}", $totalSubtotalFinal);
                $sheet->setCellValue("M{$footerRow}", $totalProfit);
                $sheet->setCellValue("N{$footerRow}", number_format($totalMargin, 2) . '%');
                
                // Format footer currency
                foreach (['I', 'J', 'K', 'L', 'M'] as $col) {
                    $sheet->getStyle("{$col}{$footerRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                }
                
                // Profit color coding
                if ($totalProfit >= 0) {
                    $sheet->getStyle("M{$footerRow}")->getFont()->getColor()->setRGB('006600');
                } else {
                    $sheet->getStyle("M{$footerRow}")->getFont()->getColor()->setRGB('CC0000');
                }
                
                // Border for footer
                $sheet->getStyle("A{$footerRow}:N{$footerRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
            }
        ];
    }
}
