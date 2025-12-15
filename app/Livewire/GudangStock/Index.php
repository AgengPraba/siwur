<?php
namespace App\Livewire\GudangStock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Title('Data Stok Gudang')]
class Index extends Component
{ 
    use WithPagination, Toast;
    
    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    public function mount(){
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')], 
            ['label' => 'Data Stok Gudang']
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset halaman saat pencarian diubah
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle arah sorting
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc'; // Set default sorting ke ascending
        }
    }
        
    public function render()
    {
        $sortField = $this->sortField;
        
        // Handle sorting for satuan_terkecil field
        if ($sortField === 'satuan_terkecil') {
            $sortField = 'satuan.nama_satuan';
        } elseif ($sortField === 'nama_gudang') {
            $sortField = 'gudang.nama_gudang';
        } elseif ($sortField === 'nama_barang') {
            $sortField = 'barang.nama_barang';
        } elseif ($sortField === 'jumlah') {
            $sortField = 'gudang_stock.jumlah';
        }
        
        // Filter berdasarkan toko_id user yang login dan pencarian
        $data = GudangStock::forCurrentUserToko()
                ->search($this->search)
                ->orderBy($sortField, $this->sortDirection)
                ->paginate($this->perPage)
                ->withQueryString();
                
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;
       
        return view('livewire.gudang-stock.index', [
            'gudang_stock_data' => $data
        ]);
    }
    
    
    public function exportPDFDaily()
    {
        $this->toast(
            'info',
            'Memproses Laporan',
            'Sedang menyiapkan laporan harian...',
            null,
            'o-calendar'
        );
        
        // Set date range for daily report (today)
        $date = Carbon::today()->format('Y-m-d');
        
        // Get the data for PDF
        $data = $this->getDataForPDF($date, $date);
        
        // Generate the PDF
        return $this->generatePDF($data, 'Laporan Stok Gudang Harian - ' . Carbon::today()->format('d M Y'));
    }
    
    public function exportPDFWeekly()
    {
        $this->toast(
            'info',
            'Memproses Laporan',
            'Sedang menyiapkan laporan mingguan...',
            null,
            'o-numbered-list'
        );
        
        // Set date range for weekly report (last 7 days)
        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');
        
        // Get the data for PDF
        $data = $this->getDataForPDF($startDate, $endDate);
        
        // Generate the PDF
        $title = 'Laporan Stok Gudang Mingguan ' . Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y');
        return $this->generatePDF($data, $title);
    }
    
    public function exportPDFMonthly()
    {
        $this->toast(
            'info',
            'Memproses Laporan',
            'Sedang menyiapkan laporan bulanan...',
            null,
            'o-calendar-days'
        );
        
        // Set date range for monthly report (current month)
        $startDate = Carbon::today()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::today()->endOfMonth()->format('Y-m-d');
        
        // Get the data for PDF
        $data = $this->getDataForPDF($startDate, $endDate);
        
        // Generate the PDF
        $title = 'Laporan Stok Gudang Bulanan ' . Carbon::today()->format('F Y');
        return $this->generatePDF($data, $title);
    }
    
    public function exportPDFCustom()
    {
        // For the custom option, we'll just show a toast message for now
        // In a real implementation, this would open a modal for date selection
        $this->toast(
            'warning',
            'Fitur Dalam Pengembangan',
            'Fitur laporan custom sedang dalam pengembangan.',
            null,
            'o-adjustments-horizontal'
        );
    }
    
    private function getDataForPDF($startDate, $endDate)
    {
        // Get user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses) {
            return collect();
        }
        
        $tokoId = $user->akses->toko_id;
        
        // Get all stock items with their relationships
        $stocks = DB::table('gudang_stock')
            ->join('barang', 'gudang_stock.barang_id', '=', 'barang.id')
            ->join('gudang', 'gudang_stock.gudang_id', '=', 'gudang.id')
            ->leftJoin('satuan', 'barang.satuan_terkecil_id', '=', 'satuan.id')
            ->where('barang.toko_id', $tokoId)
            ->where('gudang.toko_id', $tokoId)
            ->select(
                'gudang_stock.id as gudang_stock_id',
                'gudang_stock.gudang_id',
                'gudang_stock.barang_id',
                'gudang_stock.jumlah as stok_akhir',
                'barang.kode_barang',
                'barang.nama_barang',
                'gudang.nama_gudang',
                'satuan.nama_satuan as satuan_terkecil'
            )
            ->orderBy('barang.kode_barang', 'asc')
            ->get();
            
        // Process each stock item to add additional fields
        foreach ($stocks as $stock) {
            // Calculate initial stock (stok awal)
            $initialStock = $this->calculateInitialStock($stock->gudang_id, $stock->barang_id, $startDate);
            $stock->stok_awal = $initialStock;
            
            // Calculate incoming stock (stok masuk - pembelian/retur penjualan)
            $incomingStock = $this->calculateIncomingStock($stock->gudang_id, $stock->barang_id, $startDate, $endDate);
            $stock->stok_masuk = $incomingStock;
            
            // Calculate outgoing stock (stok keluar - penjualan/retur pembelian)
            $outgoingStock = $this->calculateOutgoingStock($stock->gudang_id, $stock->barang_id, $startDate, $endDate);
            $stock->stok_keluar = $outgoingStock;
            
            // Calculate adjustments (from stock opname)
            $adjustments = $this->calculateAdjustments($stock->gudang_id, $stock->barang_id, $startDate, $endDate);
            $stock->penyesuaian = $adjustments;
            
            // Calculate average purchase price
            $stock->harga_beli = $this->calculateAveragePurchasePrice($stock->barang_id);
            
            // Calculate average selling price
            $stock->harga_jual = $this->calculateAverageSellingPrice($stock->barang_id);
            
            // Validate formula: stok_akhir = stok_awal + stok_masuk - stok_keluar + penyesuaian
            $calculatedFinalStock = $initialStock + $incomingStock - $outgoingStock + $adjustments;
            
            // Handle any discrepancies - in a real system, this should be investigated
            if (abs($calculatedFinalStock - $stock->stok_akhir) > 0.01) {
                // There's a discrepancy - use the calculated value for consistency
                $stock->stok_akhir = max(0, $calculatedFinalStock); // Ensure no negative stock
            }
        }
        
        return $stocks;
    }
    
    private function generatePDF($data, $title)
    {
        // Get user's toko info
        $user = Auth::user();
        $tokoName = $user->akses->toko->nama_toko ?? 'Toko';
        
        // Prepare data for PDF
        $pdfData = [
            'title' => $title,
            'date' => Carbon::now()->format('d M Y H:i'),
            'toko' => $tokoName,
            'data' => $data,
            'user' => $user->name
        ];
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.gudang-stock-report', $pdfData);
        
        // Set PDF options
        $pdf->setPaper('a4', 'landscape');
        
        // Generate a filename
        $filename = str_replace(' ', '_', strtolower($title)) . '.pdf';
        
        // Return the PDF for download
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
    
    private function calculateInitialStock($gudangId, $barangId, $startDate)
    {
        // Start with zero stock
        $initialStock = 0;
        
        // Find the earliest record of this stock item
        $earliestTransaction = TransaksiGudangStock::where('gudang_stock_id', function($query) use ($gudangId, $barangId) {
                $query->select('id')
                      ->from('gudang_stock')
                      ->where('gudang_id', $gudangId)
                      ->where('barang_id', $barangId)
                      ->limit(1);
            })
            ->orderBy('created_at', 'asc')
            ->first();
        
        // If there are no transactions before the start date, initial stock is 0
        if (!$earliestTransaction) {
            return 0;
        }
        
        // Calculate all stock movements before the start date
        $incoming = TransaksiGudangStock::where('gudang_stock_id', function($query) use ($gudangId, $barangId) {
                $query->select('id')
                      ->from('gudang_stock')
                      ->where('gudang_id', $gudangId)
                      ->where('barang_id', $barangId)
                      ->limit(1);
            })
            ->where('tipe', 'masuk')
            ->where('created_at', '<', $startDate)
            ->sum('jumlah');
        
        $outgoing = TransaksiGudangStock::where('gudang_stock_id', function($query) use ($gudangId, $barangId) {
                $query->select('id')
                      ->from('gudang_stock')
                      ->where('gudang_id', $gudangId)
                      ->where('barang_id', $barangId)
                      ->limit(1);
            })
            ->where('tipe', 'keluar')
            ->where('created_at', '<', $startDate)
            ->sum('jumlah');
        
        $initialStock = $incoming - $outgoing;
        
        // Ensure no negative stock
        return max(0, $initialStock);
    }
    
    private function calculateIncomingStock($gudangId, $barangId, $startDate, $endDate)
    {
        // Calculate all incoming stock during the selected date range
        $incoming = TransaksiGudangStock::where('gudang_stock_id', function($query) use ($gudangId, $barangId) {
                $query->select('id')
                      ->from('gudang_stock')
                      ->where('gudang_id', $gudangId)
                      ->where('barang_id', $barangId)
                      ->limit(1);
            })
            ->where('tipe', 'masuk')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('jumlah');
        
        return $incoming;
    }
    
    private function calculateOutgoingStock($gudangId, $barangId, $startDate, $endDate)
    {
        // Calculate all outgoing stock during the selected date range
        $outgoing = TransaksiGudangStock::where('gudang_stock_id', function($query) use ($gudangId, $barangId) {
                $query->select('id')
                      ->from('gudang_stock')
                      ->where('gudang_id', $gudangId)
                      ->where('barang_id', $barangId)
                      ->limit(1);
            })
            ->where('tipe', 'keluar')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('jumlah');
        
        return $outgoing;
    }
    
    private function calculateAdjustments($gudangId, $barangId, $startDate, $endDate)
    {
        // First, we need to get the gudang_stock_id that corresponds to this barang_id and gudang_id
        $gudangStockId = DB::table('gudang_stock')
            ->where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->value('id');
        
        if (!$gudangStockId) {
            return 0; // No stock record found
        }
        
        // Calculate adjustments (stock opname) during the selected date range
        $adjustments = DB::table('stock_opname_detail')
            ->join('stock_opname', 'stock_opname_detail.stock_opname_id', '=', 'stock_opname.id')
            ->where('stock_opname.gudang_id', $gudangId)
            ->where('stock_opname_detail.gudang_stock_id', $gudangStockId)
            ->whereBetween('stock_opname.tanggal_opname', [$startDate, $endDate])
            ->sum('stock_opname_detail.selisih');
        
        return $adjustments;
    }
    
    private function calculateAveragePurchasePrice($barangId)
    {
        // Calculate average purchase price from the last 5 purchases
        $avgPrice = DB::table('pembelian_detail')
            ->where('barang_id', $barangId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->avg('harga_satuan');
        
        return $avgPrice ?: 0; // Return 0 if no purchase records found
    }
    
    private function calculateAverageSellingPrice($barangId)
    {
        // Calculate average selling price from the last 5 sales
        $avgPrice = DB::table('penjualan_detail')
            ->where('barang_id', $barangId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->avg('harga_satuan');
        
        return $avgPrice ?: 0; // Return 0 if no sales records found
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/GudangStock/Index.php */
/* Created at 2025-07-03 23:23:50 */
/* Updated: Added toko_id filtering based on user access */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */