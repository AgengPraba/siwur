<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembayaranPembelian;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PembayaranPenjualan;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Print purchase receipt
     */
    private function calculateTotalDiskonFromDetails($details)
    {
        $total_diskon = 0;
        foreach ($details as $detail) {
            $jumlah = (float) ($detail->jumlah);
            $diskon = (float) ($detail->diskon);

            $total_diskon += $diskon * $jumlah;
        }
        return $total_diskon;
    }
    
    public function printPurchase($id)
    {
        // Ambil data pembelian dengan relasi
        $pembelian = Pembelian::with(['supplier', 'user'])
            ->findOrFail($id);

        // Ambil detail pembelian dengan relasi
        $details = PembelianDetail::with(['barang', 'satuan', 'gudang'])
            ->where('pembelian_id', $id)
            ->get();

        // Calculate subtotal
        $subtotal = $details->sum('subtotal');

        // Calculate Total Diskon
        $total_diskon = $this->calculateTotalDiskonFromDetails($details);

        // Calculate Biaya Lain
        $total_biaya_lain = $details->sum('biaya_lain');
        
        // Calculate payments
        $payments = PembayaranPembelian::where('pembelian_id', $id)->get();
        $total_paid = $payments->sum('jumlah');
        $remaining = $pembelian->total_harga - $total_paid;
        
        // Set print data
        $printDate = Carbon::now()->format('d/m/Y H:i:s');
        
        // Return view with data
        return view('prints.purchase', [
            'pembelian' => $pembelian,
            'details' => $details,
            'subtotal' => $subtotal,
            'total_paid' => $total_paid,
            'remaining' => $remaining,
            'printDate' => $printDate,
            'total_diskon' => $total_diskon,
            'total_biaya_lain' => $total_biaya_lain,
        ]);
    }

    /**
     * Print payment receipt
     */
    public function printPayment($id)
    {
        // Ambil data pembelian dengan relasi
        $pembelian = Pembelian::with(['supplier', 'user'])
            ->findOrFail($id);

        // Ambil pembayaran pembelian dengan relasi
        $payments = PembayaranPembelian::with(['user'])
            ->where('pembelian_id', $id)
            ->get();
            
        // Calculate total
        $total_paid = $payments->sum('jumlah');
        $remaining = $pembelian->total_harga - $total_paid;
        
        // Set print data
        $printDate = Carbon::now()->format('d/m/Y H:i:s');
        
        // Return view with data
        return view('prints.payment', [
            'pembelian' => $pembelian,
            'payments' => $payments,
            'total_paid' => $total_paid,
            'remaining' => $remaining,
            'printDate' => $printDate
        ]);
    }
    
    /**
     * Print sales invoice
     */
    public function printInvoice($id)
    {
        // Ambil data penjualan dengan relasi
        $penjualan = Penjualan::with(['customer', 'user'])
            ->findOrFail($id);

        // Ambil detail penjualan dengan relasi
        $penjualanDetails = PenjualanDetail::with(['barang', 'satuan', 'gudang'])
            ->where('penjualan_id', $id)
            ->get();

        // Ambil data pembayaran
        $pembayaranList = PembayaranPenjualan::with('user')
            ->where('penjualan_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Hitung total pembayaran dan sisa pembayaran
        $totalPembayaran = $pembayaranList->sum('jumlah');
        $sisaPembayaran = $penjualan->total_harga - $totalPembayaran;

        return view('livewire.penjualan.print-invoice', compact('penjualan', 'penjualanDetails', 'pembayaranList', 'totalPembayaran', 'sisaPembayaran'));
    }

    /**
     * Print sales payment receipt
     */
    public function printPembayaran($id)
    {
        // Ambil data pembayaran dengan relasi
        $pembayaran = PembayaranPenjualan::with(['penjualan.customer', 'user'])
            ->findOrFail($id);

        $redirectUrl = session('pos_redirect_after_print');

        return view('livewire.penjualan.print-pembayaran', [
            'pembayaran' => $pembayaran,
            'redirectUrl' => $redirectUrl,
        ]);
    }

    /**
     * Print return purchase receipt
     */
    public function printReturPembelian($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        // Ambil data retur pembelian dengan relasi
        $returPembelian = ReturPembelian::with([
            'pembelian.supplier',
            'details.barang',
            'details.satuan',
            'gudang',
            'dibuatOleh',
        ])
        ->where('toko_id', $user->akses->toko_id)
        ->findOrFail($id);

        // Set print data
        $printDate = Carbon::now()->format('d/m/Y H:i:s');
        
        // Mapping alasan retur untuk tampilan
        $alasanReturLabels = [
            'rusak' => 'Barang Rusak',
            'tidak_sesuai' => 'Tidak Sesuai Pesanan',
            'kelebihan' => 'Kelebihan Pengiriman',
            'kadaluarsa' => 'Mendekati Kadaluarsa',
            'lainnya' => 'Lainnya',
        ];
        
        // Return view with data
        return view('prints.retur-pembelian', [
            'returPembelian' => $returPembelian,
            'printDate' => $printDate,
            'alasanReturLabels' => $alasanReturLabels
        ]);
    }

    /**
     * Print return sales receipt
     */
    public function printReturPenjualan($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        // Ambil data retur penjualan dengan relasi
        $returPenjualan = ReturPenjualan::with([
            'penjualan.customer',
            'details.barang',
            'details.satuan',
            'gudang',
            'dibuatOleh'
        ])
        ->where('toko_id', $user->akses->toko_id)
        ->findOrFail($id);

        // Set print data
        $printDate = Carbon::now()->format('d/m/Y H:i:s');
        
        // Mapping alasan retur untuk tampilan
        $alasanReturLabels = [
            'rusak' => 'Barang Rusak',
            'tidak_sesuai' => 'Tidak Sesuai Pesanan',
            'kelebihan' => 'Kelebihan Pengiriman',
            'kadaluarsa' => 'Mendekati Kadaluarsa',
            'lainnya' => 'Lainnya',
        ];
        
        // Return view with data
        return view('prints.retur-penjualan', [
            'returPenjualan' => $returPenjualan,
            'printDate' => $printDate,
            'alasanReturLabels' => $alasanReturLabels
        ]);
    }

    /**
     * Print stock opname
     */
    public function printOpname($id)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        $opname = \App\Models\StockOpname::with([
            'details.gudangStock.barang', 
            'details.gudangStock.satuanTerkecil',
            'gudang', 
            'user',
            'toko'
        ])
        ->where('toko_id', $user->akses->toko_id)
        ->findOrFail($id);
        
        return view('prints.opname', ['opname' => $opname]);
    }
    
    /**
     * Print profit report
     */
    public function printLaporanProfit(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        // Decode data from request
        $encodedData = $request->query('data');
        $decodedData = json_decode(base64_decode($encodedData), true);
        
        $filters = $decodedData['filters'] ?? [];
        $summary = $decodedData['summary'] ?? [];
        
        // Get penjualan data based on filters
        $penjualans = Penjualan::query()
            ->with(['customer:id,nama_customer', 'user:id,name', 'penjualanDetails:id,penjualan_id,barang_id,satuan_id,jumlah,harga_satuan,subtotal,profit,diskon,biaya_lain', 'penjualanDetails.barang:id,nama_barang', 'penjualanDetails.satuan:id,nama_satuan'])
            ->where('toko_id', $user->akses->toko_id)
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('nomor_penjualan', 'like', '%' . $filters['search'] . '%')
                      ->orWhereHas('customer', function ($customerQuery) use ($filters) {
                          $customerQuery->where('nama_customer', 'like', '%' . $filters['search'] . '%');
                      })
                      ->orWhereHas('user', function ($userQuery) use ($filters) {
                          $userQuery->where('name', 'like', '%' . $filters['search'] . '%');
                      });
                });
            })
            ->when(!empty($filters['customer_id']), function ($query) use ($filters) {
                $query->where('customer_id', $filters['customer_id']);
            })
            ->when(!empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('tanggal_penjualan', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('tanggal_penjualan', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();
        
        return view('reports.laporan-profit-print', [
            'penjualans' => $penjualans,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }
}