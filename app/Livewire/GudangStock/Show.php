<?php

namespace App\Livewire\GudangStock;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Title('Show Gudang stock')]
class Show extends Component
{
    use Toast;
    public $data;
    public $transactions;
    public $breadcrumbs;
    public $stockStats;

    public function mount($id)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data gudang stock', 'link' => route('gudang-stock.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data gudang stock dengan relasi dan filter toko
        $this->data = GudangStock::getWithRelationsForCurrentToko($id);

        // Ambil data transaksi dengan relasi
        $this->transactions = TransaksiGudangStock::with([
            'pembelianDetail.pembelian.supplier',
            'penjualanDetail.penjualan.customer'
        ])
            ->where('gudang_stock_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung statistik stock
        $this->calculateStockStats($id);
    }

    /**
     * Menghitung statistik stock untuk gudang tertentu
     * 
     * Statistik yang dihitung:
     * - Total masuk (dalam satuan asli dan satuan terkecil)
     * - Total keluar (dalam satuan asli dan satuan terkecil)
     * - Saldo (selisih masuk dan keluar)
     * - Total transaksi
     * 
     * @param int $gudangStockId ID gudang stock
     * @return void
     */
    private function calculateStockStats($gudangStockId)
    {
        // Hitung dalam satuan asli
        $masuk = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
            ->where('tipe', 'masuk')
            ->sum('jumlah');

        $keluar = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
            ->where('tipe', 'keluar')
            ->sum('jumlah');

        // Hitung dalam satuan terkecil (jumlah * konversi)
        // Ini penting untuk perhitungan stock berjalan yang akurat
        $masukSatuanTerkecil = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
            ->where('tipe', 'masuk')
            ->selectRaw('SUM(jumlah * konversi_satuan_terkecil) as total')
            ->value('total') ?? 0;

        $keluarSatuanTerkecil = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
            ->where('tipe', 'keluar')
            ->selectRaw('SUM(jumlah * konversi_satuan_terkecil) as total')
            ->value('total') ?? 0;

        $totalTransaksi = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
            ->count();

        // Menyimpan statistik untuk digunakan di view
        // Stock awal dihitung di view dengan rumus:
        // Stock awal = stock saat ini - (total masuk - total keluar)
        $this->stockStats = [
            'total_masuk' => $masuk,
            'total_keluar' => $keluar,
            'total_masuk_satuan_terkecil' => $masukSatuanTerkecil,
            'total_keluar_satuan_terkecil' => $keluarSatuanTerkecil,
            'total_transaksi' => $totalTransaksi,
            'saldo' => $masuk - $keluar,
            'saldo_satuan_terkecil' => $masukSatuanTerkecil - $keluarSatuanTerkecil
        ];

        // Update gudang_stock amount with saldo_satuan_terkecil
        $update = GudangStock::where('id', $gudangStockId)
            ->update(['jumlah' => $this->stockStats['saldo_satuan_terkecil']]);

        //dd(GudangStock::where('id', $gudangStockId)->first());
    }

    /**
     * Menampilkan modal konfirmasi untuk menghitung ulang jumlah stock
     * 
     * Fungsi ini membuka modal konfirmasi sebelum menjalankan
     * proses perhitungan ulang stock untuk mencegah perubahan data
     * yang tidak disengaja.
     * 
     * @return void
     */
    public $showKonfirmasiModal = false;

    public function hitungUlangStock()
    {
        // Buka modal konfirmasi
        $this->showKonfirmasiModal = true;
    }

    /**
     * Proses perhitungan ulang stock setelah konfirmasi
     * 
     * Fungsi ini akan:
     * 1. Mengambil semua transaksi untuk gudang stock tertentu
     * 2. Menghitung total masuk dan keluar dalam satuan terkecil
     * 3. Menghitung saldo akhir (masuk - keluar)
     * 4. Memperbarui jumlah stock di database
     * 5. Memperbarui tampilan dengan data terbaru
     * 
     * Proses ini menggunakan transaksi database untuk memastikan
     * integritas data. Jika terjadi kesalahan, semua perubahan
     * akan dibatalkan (rollback).
     * 
     * @return void
     */
    public function prosesHitungUlangStock()
    {
        try {
            // Validasi data gudang stock
            if (!$this->data || !$this->data->id) {
                $this->error('Data gudang stock tidak valid!');
                return;
            }

            // Mulai transaksi database
            DB::beginTransaction();

            // Ambil ID gudang stock
            $gudangStockId = $this->data->id;
            // Delete invalid transactions where referenced details don't exist
            TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('penjualan_detail_id')
                            ->whereDoesntHave('penjualanDetail');
                    })->orWhere(function ($q) {
                        $q->whereNotNull('pembelian_detail_id')
                            ->whereDoesntHave('pembelianDetail');
                    });
                })
                ->delete();
            // Calculate total incoming stock in smallest unit from both purchases and direct transactions
            $masukSatuanTerkecil = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
                ->where('tipe', 'masuk')
                ->whereHas('pembelianDetail', function ($query) {
                    $query->whereNotNull('id');
                })
                ->selectRaw('SUM(jumlah * konversi_satuan_terkecil) as total')
                ->value('total') ?? 0;

            // Calculate total outgoing stock in smallest unit from both sales and direct transactions  
            $keluarSatuanTerkecil = TransaksiGudangStock::where('gudang_stock_id', $gudangStockId)
                ->where('tipe', 'keluar')
                ->whereHas('penjualanDetail', function ($query) {
                    $query->whereNotNull('id');
                })
                ->selectRaw('SUM(jumlah * konversi_satuan_terkecil) as total')
                ->value('total') ?? 0;

            // Calculate final balance
            $saldoAkhir = $masukSatuanTerkecil - $keluarSatuanTerkecil;

            // Update jumlah stock di database
            GudangStock::where('id', $gudangStockId)
                ->update(['jumlah' => $saldoAkhir]);


            // Commit transaksi database
            DB::commit();

            // Catat aktivitas perhitungan ulang stock (jika ada fitur log aktivitas)
            // activity()->performedOn($this->data)->log('Menghitung ulang stock barang ' . $this->data->nama_barang);

            // Tampilkan notifikasi sukses dengan detail
            $this->success('Stock berhasil dihitung ulang! Nilai stock saat ini: ' . number_format($saldoAkhir, 2) . ' ' . $this->data->satuan_terkecil);
            return $this->redirectRoute('gudang-stock.show', ['id' => $this->data->id]);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();

            // Tampilkan notifikasi error
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.gudang-stock.show', [
            'gudang_stock_data' => $this->data,
            'transactions' => $this->transactions,
            'stockStats' => $this->stockStats,
        ]);
    }
}

/* End of file komponen show */
/* Location: ./app/Livewire/GudangStock/Show.php */
/* Created at 2025-07-03 23:23:50 */
/* Updated: Added toko_id filtering based on user access */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */