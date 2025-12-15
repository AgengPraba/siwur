<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PembayaranPenjualan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\LivewireTenancy;

#[Title('Detail Penjualan')]
class Show extends Component
{
    use Toast, LivewireTenancy;

    public $penjualan;
    public $penjualanDetails;
    public $pembayaranList;
    public $breadcrumbs;
    public $selectedPembayaran = null;

    // Form pembayaran baru
    #[Rule('required|string')]
    public $jenis_pembayaran = 'cash';

    #[Rule('required|numeric|min:1')]
    public $jumlah = 0;

    #[Rule('nullable|string')]
    public $keterangan = '';

    // Modal konfirmasi hapus
    public $showDeleteModal = false;
    public $pembayaranIdToDelete = null;

    // Modal detail pembayaran
    public $showDetailModal = false;
    public $selectedPembayaranDetail = null;

    // Loading states
    public $isLoading = false;
    public $isRefreshing = false;

    public function mount($id)
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Penjualan', 'link' => route('penjualan.index')],
            ['label' => 'Detail Penjualan'],
        ];

        $this->loadPenjualanData($id);
    }

    private function loadPenjualanData($id)
    {
        // Load penjualan dengan relasi yang diperlukan dan optimasi query
        $this->penjualan = Penjualan::with([
            'customer:id,nama_customer,email,no_hp',
            'user:id,name',
            'penjualanDetails' => function ($query) {
                $query->withOptimizedRelations();
            }
        ])->findOrFail($id);
        
        // Validate toko ownership
        if (!$this->validateTokoOwnership($this->penjualan)) {
            return redirect()->route('penjualan.index');
        }

        // Load detail penjualan (relasi sudah dimuat melalui withOptimizedRelations)
        $this->penjualanDetails = $this->penjualan->penjualanDetails;

        // Load pembayaran dengan optimasi
        $this->pembayaranList = PembayaranPenjualan::where('penjualan_id', $this->penjualan->id)
            ->select('id', 'penjualan_id', 'user_id', 'jenis_pembayaran', 'jumlah', 'keterangan', 'created_at')
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Recalculate total untuk memastikan konsistensi
        $this->penjualan->autoRecalculateTotal();
        $this->penjualan->refresh();

        // Cek status pembayaran secara otomatis
        $this->updateStatusPembayaran();
    }

    public function getTotalPembayaranProperty()
    {
        return $this->pembayaranList->sum('jumlah');
    }

    public function getSisaPembayaranProperty()
    {
        return $this->penjualan->total_harga - $this->totalPembayaran;
    }

    public function getStatusPembayaranProperty()
    {
        if ($this->totalPembayaran == 0) {
            return 'belum_bayar';
        } elseif ($this->totalPembayaran < $this->penjualan->total_harga) {
            return 'belum_lunas';
        } else {
            return 'lunas';
        }
    }

    public function getPersentasePembayaranProperty()
    {
        if ($this->penjualan->total_harga <= 0) {
            return 100;
        }

        $persentase = ($this->totalPembayaran / $this->penjualan->total_harga) * 100;
        
        // Only show 100% if payment is actually complete or exceeds total
        if ($this->totalPembayaran >= $this->penjualan->total_harga) {
            return 100;
        }
        
        // For partial payments, round down to avoid showing 100% when not fully paid
        return floor($persentase);
    }

    public function getProgressColorProperty()
    {
        if ($this->persentasePembayaran == 0) {
            return 'error';
        } elseif ($this->persentasePembayaran < 100) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    public function updateStatusPembayaran($showNotification = false)
    {
        $status = $this->statusPembayaran;

        // Update status di database jika berbeda
        if ($this->penjualan->status !== $status) {
            $penjualan = Penjualan::find($this->penjualan->id);
            $penjualan->status = $status;
            $penjualan->save();

            // Refresh data
            $this->penjualan = $penjualan;

            if ($showNotification) {
                $this->success('Status pembayaran berhasil diperbarui menjadi ' . $this->penjualan->statusLabel);
            }

            return true;
        }

        return false;
    }

    public function cetakInvoice()
    {
        $this->redirect(route('penjualan.print.invoice', ['id' => $this->penjualan->id]));
    }

    public function cetakPembayaran($pembayaranId)
    {
        $this->selectedPembayaran = $pembayaranId;
        $this->redirect(route('penjualan.print.pembayaran', ['id' => $pembayaranId]));
    }

    public function refreshPembayaran()
    {
        // Refresh semua data
        $this->loadPenjualanData($this->penjualan->id);

        // Update status pembayaran dan tampilkan notifikasi
        $this->updateStatusPembayaran(true);

        $this->success('Data pembayaran berhasil diperbarui');
    }

    public function refreshData()
    {
        $this->isRefreshing = true;

        try {
            // Method untuk refresh semua data
            $this->loadPenjualanData($this->penjualan->id);
            $this->success('Data berhasil diperbarui');
        } catch (\Exception $e) {
            $this->error('Gagal memperbarui data: ' . $e->getMessage());
        } finally {
            $this->isRefreshing = false;
        }
    }

    public function tambahPembayaran()
    {
        // Validasi input menggunakan attribute rules
        $this->validate();

        // Cek apakah jumlah pembayaran valid
        if ($this->jumlah <= 0) {
            $this->error('Jumlah pembayaran harus lebih dari 0');
            return;
        }

        try {
            DB::beginTransaction();

            // Hitung kembalian jika pembayaran melebihi sisa pembayaran
            $kembalian = 0;
            $totalSudahDibayar = $this->totalPembayaran;
            $sisaYangHarusDibayar = $this->penjualan->total_harga - $totalSudahDibayar;

            if ($this->jumlah > $sisaYangHarusDibayar) {
                $kembalian = $this->jumlah - $sisaYangHarusDibayar;
            }

            // Simpan pembayaran baru
            PembayaranPenjualan::create([
                'penjualan_id' => $this->penjualan->id,
                'user_id' => Auth::user()->id,
                'jenis_pembayaran' => (string)$this->jenis_pembayaran, // Explicitly cast to string
                'jumlah' => $this->jumlah,
                'keterangan' => $this->keterangan,
                'kembalian' => $kembalian,
            ]);

            DB::commit();

            // Reset form
            $this->reset(['jumlah', 'keterangan']);
            $this->jenis_pembayaran = 'cash';

            // Refresh data pembayaran
            $this->refreshPembayaran();

            // Tampilkan notifikasi
            if ($kembalian > 0) {
                $this->success('Pembayaran berhasil ditambahkan dengan kembalian Rp ' . number_format($kembalian, 0, ',', '.'));
            } else {
                $this->success('Pembayaran berhasil ditambahkan');
            }
            return $this->redirectRoute('penjualan.show', ['id' => $this->penjualan->id], navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bayarLunas()
    {
        if ($this->sisaPembayaran <= 0) {
            $this->error('Pembayaran sudah lunas');
            return;
        }

        // Set jumlah pembayaran sama dengan sisa pembayaran
        $this->jumlah = $this->sisaPembayaran;
        $this->keterangan = 'Pelunasan';

        // Lakukan pembayaran
        $this->tambahPembayaran();
    }

    public function bayarSebagian($persentase)
    {
        if ($this->sisaPembayaran <= 0) {
            $this->error('Pembayaran sudah lunas');
            return;
        }

        // Hitung jumlah pembayaran berdasarkan persentase dari sisa pembayaran
        $jumlah = ($persentase / 100) * $this->sisaPembayaran;

        // Set jumlah pembayaran
        $this->jumlah = round($jumlah);
        $this->keterangan = 'Pembayaran ' . $persentase . '%';

        // Lakukan pembayaran
        $this->tambahPembayaran();
    }

    public function konfirmasiHapusPembayaran($pembayaranId)
    {
        $this->pembayaranIdToDelete = $pembayaranId;
        $this->showDeleteModal = true;

        // Tutup modal detail jika terbuka
        if ($this->showDetailModal) {
            $this->showDetailModal = false;
            $this->selectedPembayaranDetail = null;
        }
    }

    public function batalHapusPembayaran()
    {
        $this->showDeleteModal = false;
        $this->pembayaranIdToDelete = null;
    }

    public function hapusPembayaran()
    {
        if (!$this->pembayaranIdToDelete) {
            $this->error('ID pembayaran tidak valid');
            return;
        }

        try {
            DB::beginTransaction();

            // Cari pembayaran yang akan dihapus
            $pembayaran = PembayaranPenjualan::findOrFail($this->pembayaranIdToDelete);

            // Pastikan pembayaran milik penjualan ini
            if ($pembayaran->penjualan_id != $this->penjualan->id) {
                $this->error('Pembayaran tidak valid');
                return;
            }

            // Hapus pembayaran
            $pembayaran->delete();

            DB::commit();

            // Tutup modal
            $this->showDeleteModal = false;
            $this->pembayaranIdToDelete = null;

            // Refresh data pembayaran
            $this->refreshPembayaran();

            // Tampilkan notifikasi
            $this->success('Pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function lihatDetailPembayaran($pembayaranId)
    {
        $pembayaran = PembayaranPenjualan::with(['user', 'penjualan'])
            ->findOrFail($pembayaranId);

        $this->selectedPembayaranDetail = $pembayaran;
        $this->showDetailModal = true;
    }

    public function tutupDetailPembayaran()
    {
        $this->showDetailModal = false;
        $this->selectedPembayaranDetail = null;
    }

    /**
     * Get purchase information for items when payment exceeds total price
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getPurchaseInfoProperty()
    {
        if ($this->sisaPembayaran >= 0) {
            return collect();
        }

        // Get unique purchase details from penjualan details
        $purchaseInfo = $this->penjualanDetails
            ->whereNotNull('pembelian_detail_id')
            ->groupBy('pembelian_detail.pembelian_id')
            ->map(function ($details, $pembelianId) {
                $firstDetail = $details->first();
                $pembelian = $firstDetail->pembelianDetail->pembelian;

                return [
                    'pembelian_id' => $pembelianId,
                    'nomor_pembelian' => $pembelian->nomor_pembelian,
                    'tanggal_pembelian' => $pembelian->tanggal_pembelian,
                    'supplier' => $pembelian->supplier->nama_supplier,
                    'total_harga_pembelian' => $pembelian->total_harga,
                    'formatted_total_harga_pembelian' => 'Rp ' . number_format($pembelian->total_harga, 0, ',', '.'),
                    'items' => $details->map(function ($detail) {
                        return [
                            'barang_nama' => $detail->barang->nama_barang,
                            'satuan_nama' => $detail->satuan->nama_satuan,
                            'gudang_nama' => $detail->gudang->nama_gudang,
                            'harga_beli' => $detail->pembelianDetail->harga_satuan,
                            'formatted_harga_beli' => 'Rp ' . number_format($detail->pembelianDetail->harga_satuan, 0, ',', '.'),
                            'harga_jual' => $detail->harga_satuan,
                            'formatted_harga_jual' => 'Rp ' . number_format($detail->harga_satuan, 0, ',', '.'),
                            'jumlah' => $detail->jumlah,
                            'subtotal_beli' => $detail->pembelianDetail->harga_satuan * $detail->jumlah,
                            'formatted_subtotal_beli' => 'Rp ' . number_format($detail->pembelianDetail->harga_satuan * $detail->jumlah, 0, ',', '.'),
                            'subtotal_jual' => $detail->harga_satuan * $detail->jumlah,
                            'formatted_subtotal_jual' => 'Rp ' . number_format($detail->harga_satuan * $detail->jumlah, 0, ',', '.'),
                            'profit' => $detail->profit,
                            'formatted_profit' => 'Rp ' . number_format($detail->profit, 0, ',', '.'),
                            'margin_profit' => $detail->pembelianDetail->harga_satuan > 0 ?
                                round((($detail->harga_satuan - $detail->pembelianDetail->harga_satuan) / $detail->pembelianDetail->harga_satuan) * 100, 2) : 0
                        ];
                    })
                ];
            });

        return $purchaseInfo;
    }

    /**
     * Check if payment exceeds total price
     * 
     * @return bool
     */
    public function getPaymentExceedsTotalProperty()
    {
        return $this->sisaPembayaran < 0;
    }

    /**
     * Get total purchase cost for all items
     * 
     * @return float
     */
    public function getTotalPurchaseCostProperty()
    {
        return $this->penjualanDetails
            ->whereNotNull('pembelian_detail_id')
            ->sum(function ($detail) {
                return $detail->pembelianDetail->harga_satuan * $detail->jumlah;
            });
    }

    /**
     * Get total profit from all items
     * 
     * @return float
     */
    public function getTotalProfitProperty()
    {
        return $this->penjualanDetails->sum('profit');
    }

    /**
     * Get formatted total purchase cost
     * 
     * @return string
     */
    public function getFormattedTotalPurchaseCostProperty()
    {
        return 'Rp ' . number_format($this->totalPurchaseCost, 0, ',', '.');
    }

    /**
     * Get formatted total profit
     * 
     * @return string
     */
    public function getFormattedTotalProfitProperty()
    {
        return 'Rp ' . number_format($this->totalProfit, 0, ',', '.');
    }

    /**
     * Get total kembalian from penjualan record
     * 
     * @return float
     */
    public function getTotalKembalianProperty()
    {
        return $this->penjualan->kembalian ?? 0;
    }

    /**
     * Get formatted total kembalian
     * 
     * @return string
     */
    public function getFormattedTotalKembalianProperty()
    {
        return 'Rp ' . number_format($this->totalKembalian, 0, ',', '.');
    }

    /**
     * Get formatted total pembayaran
     * 
     * @return string
     */
    public function getFormattedTotalPembayaranProperty()
    {
        return 'Rp ' . number_format($this->totalPembayaran, 0, ',', '.');
    }

    /**
     * Get formatted sisa pembayaran (absolute value)
     * 
     * @return string
     */
    public function getFormattedSisaPembayaranProperty()
    {
        return 'Rp ' . number_format(abs($this->sisaPembayaran), 0, ',', '.');
    }

    public function render()
    {
        // Pastikan status pembayaran selalu up-to-date
        $this->updateStatusPembayaran();

        return view('livewire.penjualan.show');
    }
}

/* End of file komponen show */
/* Location: ./app/Livewire/Penjualan/Show.php */
/* Created at 2025-07-03 23:22:50 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */