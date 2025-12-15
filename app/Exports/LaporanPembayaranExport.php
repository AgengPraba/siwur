<?php

namespace App\Exports;

use App\Models\PembayaranPembelian;
use App\Models\PembayaranPenjualan;
use App\Models\Akses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPembayaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents
{
    protected $filters;
    protected $statistik;
    
    public function __construct($filters)
    {
        $this->filters = $filters;
        $this->statistik = $this->getStatistik($filters);
    }
    
    public function collection()
    {
        return $this->getData($this->filters);
    }
    
    public function startCell(): string
    {
        return 'A15';
    }
    
    public function headings(): array
    {
        $headings = [
            'No',
            'Tanggal',
            $this->filters['jenis_laporan'] === 'penjualan' ? 'Customer' : 'Supplier',
            'Kode Transaksi',
            'Jenis Pembayaran',
            'Jumlah Bayar',
        ];
        
        if ($this->filters['jenis_laporan'] === 'penjualan') {
            $headings[] = 'Kembalian';
            $headings[] = 'Jumlah Bersih';
        }
        
        $headings[] = 'User';
        $headings[] = 'Keterangan';
        
        return $headings;
    }
    
    public function map($row): array
    {
        static $index = 1;
        
        $mapped = [
            $index++,
            Carbon::parse($row->created_at)->format('d/m/Y H:i'),
            $this->filters['jenis_laporan'] === 'penjualan' 
                ? ($row->penjualan->customer->nama_customer ?? '-')
                : ($row->pembelian->supplier->nama_supplier ?? '-'),
            $this->filters['jenis_laporan'] === 'penjualan' 
                ? ($row->penjualan->nomor_penjualan ?? '-')
                : ($row->pembelian->nomor_pembelian ?? '-'),
            ucfirst($row->jenis_pembayaran),
            $row->jumlah,
        ];
        
        if ($this->filters['jenis_laporan'] === 'penjualan') {
            $mapped[] = $row->kembalian ?? 0;
            $mapped[] = $row->jumlah - ($row->kembalian ?? 0);
        }
        
        $mapped[] = $row->user->name ?? '-';
        $mapped[] = $row->keterangan ?? '-';
        
        return $mapped;
    }
    
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A15:' . $sheet->getHighestColumn() . '15')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        return [];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $companyInfo = [
                    'name' => config('app.name', 'SIWUR'),
                    'address' => 'Alamat Toko',
                    'phone' => 'Nomor Telepon'
                ];
                
                $sheet->setCellValue('A1', strtoupper('Laporan Pembayaran ' . $this->filters['jenis_laporan']));
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->setCellValue('A3', 'Nama Perusahaan:');
                $sheet->setCellValue('B3', $companyInfo['name']);
                $sheet->setCellValue('A4', 'Alamat:');
                $sheet->setCellValue('B4', $companyInfo['address']);
                $sheet->setCellValue('A5', 'Telepon:');
                $sheet->setCellValue('B5', $companyInfo['phone']);
                
                $sheet->setCellValue('A7', 'Periode:');
                $sheet->setCellValue('B7', $this->formatPeriode($this->filters['tanggal_mulai'], $this->filters['tanggal_selesai']));
                $sheet->setCellValue('A8', 'Jenis Pembayaran:');
                $sheet->setCellValue('B8', $this->filters['jenis_pembayaran'] ? ucfirst($this->filters['jenis_pembayaran']) : 'Semua');
                $sheet->setCellValue('A9', 'Dicetak pada:');
                $sheet->setCellValue('B9', now()->format('d M Y H:i:s'));
                $sheet->setCellValue('A10', 'Dicetak oleh:');
                $sheet->setCellValue('B10', Auth::user()->name ?? 'System');
                
                $sheet->setCellValue('D3', 'RINGKASAN:');
                $sheet->getStyle('D3')->getFont()->setBold(true);
                $sheet->setCellValue('D4', 'Total Transaksi:');
                $sheet->setCellValue('E4', number_format($this->statistik['total_transaksi'], 0, ',', '.'));
                $sheet->setCellValue('D5', 'Total Pembayaran:');
                $sheet->setCellValue('E5', 'Rp ' . number_format($this->statistik['total_pembayaran'], 0, ',', '.'));
                
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
    
    private function getData($filters)
    {
        $userTokoId = $this->getUserTokoId();
        
        if ($filters['jenis_laporan'] === 'penjualan') {
            $query = PembayaranPenjualan::with(['penjualan.customer', 'user'])
                ->whereHas('penjualan', function ($q) use ($userTokoId, $filters) {
                    $q->where('toko_id', $userTokoId);
                    
                    if (!empty($filters['search'])) {
                        $q->whereHas('customer', function ($customerQuery) use ($filters) {
                            $customerQuery->where('nama_customer', 'like', '%' . $filters['search'] . '%');
                        });
                    }
                });
        } else {
            $query = PembayaranPembelian::with(['pembelian.supplier', 'user'])
                ->whereHas('pembelian', function ($q) use ($userTokoId, $filters) {
                    $q->where('toko_id', $userTokoId);
                    
                    if (!empty($filters['search'])) {
                        $q->whereHas('supplier', function ($supplierQuery) use ($filters) {
                            $supplierQuery->where('nama_supplier', 'like', '%' . $filters['search'] . '%');
                        });
                    }
                });
        }
        
        if (!empty($filters['tanggal_mulai']) && !empty($filters['tanggal_selesai'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['tanggal_mulai'])->startOfDay(),
                Carbon::parse($filters['tanggal_selesai'])->endOfDay()
            ]);
        }
        
        if (!empty($filters['jenis_pembayaran'])) {
            $query->where('jenis_pembayaran', $filters['jenis_pembayaran']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    private function getStatistik($filters)
    {
        $userTokoId = $this->getUserTokoId();
        
        if ($filters['jenis_laporan'] === 'penjualan') {
            $query = PembayaranPenjualan::whereHas('penjualan', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        } else {
            $query = PembayaranPembelian::whereHas('pembelian', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        }
        
        if (!empty($filters['tanggal_mulai']) && !empty($filters['tanggal_selesai'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['tanggal_mulai'])->startOfDay(),
                Carbon::parse($filters['tanggal_selesai'])->endOfDay()
            ]);
        }
        
        if (!empty($filters['jenis_pembayaran'])) {
            $query->where('jenis_pembayaran', $filters['jenis_pembayaran']);
        }
        
        $data = $query->get();
        
        $stats = [];
        $stats['total_transaksi'] = $data->count();
        $stats['total_cash'] = $data->where('jenis_pembayaran', 'cash')->count();
        $stats['total_transfer'] = $data->where('jenis_pembayaran', 'transfer')->count();
        
        if ($filters['jenis_laporan'] === 'penjualan') {
            $stats['total_kredit'] = $data->where('jenis_pembayaran', 'kredit')->count();
            $stats['total_pembayaran'] = $data->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['total_kembalian'] = $data->sum('kembalian');
            $stats['nilai_cash'] = $data->where('jenis_pembayaran', 'cash')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['nilai_transfer'] = $data->where('jenis_pembayaran', 'transfer')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['nilai_kredit'] = $data->where('jenis_pembayaran', 'kredit')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
        } else {
            $stats['total_check'] = $data->where('jenis_pembayaran', 'check')->count();
            $stats['total_other'] = $data->where('jenis_pembayaran', 'other')->count();
            $stats['total_pembayaran'] = $data->sum('jumlah');
            $stats['total_kembalian'] = 0;
            $stats['nilai_cash'] = $data->where('jenis_pembayaran', 'cash')->sum('jumlah');
            $stats['nilai_transfer'] = $data->where('jenis_pembayaran', 'transfer')->sum('jumlah');
            $stats['nilai_check'] = $data->where('jenis_pembayaran', 'check')->sum('jumlah');
            $stats['nilai_other'] = $data->where('jenis_pembayaran', 'other')->sum('jumlah');
        }
        
        return $stats;
    }
    
    private function getUserTokoId()
    {
        $userId = Auth::id();
        $akses = Akses::where('user_id', $userId)->first();
        
        if (!$akses) {
            throw new \Exception('User tidak memiliki akses ke toko manapun.');
        }
        
        return $akses->toko_id;
    }
    
    private function formatPeriode($tanggalMulai, $tanggalSelesai)
    {
        $mulai = Carbon::parse($tanggalMulai)->format('d M Y');
        $selesai = Carbon::parse($tanggalSelesai)->format('d M Y');
        
        return $mulai . ' - ' . $selesai;
    }
}