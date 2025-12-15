<?php

namespace App\Livewire\Barang;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Satuan;
use App\Models\AturanHargaBarang;
use Illuminate\Support\Facades\Auth;

#[Title('Show Barang')]
class Show extends Component
{
    use Toast;

    public $data;
    public $breadcrumbs;
    public $barangSatuanList = [];
    public $satuanOptions = [];
    public $aturanHargaList = [];

    // Form properties for adding/editing barang satuan
    public $showForm = false;
    public $editMode = false;
    public $editId = null;
    public $satuan_id = '';
    public $konversi_satuan_terkecil = '';
    public $is_satuan_terkecil = 'tidak';

    // Form properties for adding/editing aturan harga
    public $showAturanHargaForm = false;
    public $editAturanHargaMode = false;
    public $editAturanHargaId = null;
    public $aturan_satuan_id = '';
    public $minimal_penjualan = '';
    public $maksimal_penjualan = '';
    public $harga_jual = '';
    public $satuanAturanOptions = [];

    public function mount($id)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        $akses = $user->akses ?? null;
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';

        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Barang - ' . $namaTokoLabel, 'href' => route('barang.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data barang dengan relationships (global scope akan otomatis filter berdasarkan toko_id)
        $this->data = Barang::with(['jenisBarang', 'satuanTerkecil', 'barangSatuan.satuan', 'toko'])
            ->findOrFail($id);

        $this->loadBarangSatuan();
        $this->loadSatuanOptions();
        $this->loadAturanHarga();
        $this->loadSatuanAturanOptions();
    }

    public function loadBarangSatuan()
    {
        $this->barangSatuanList = BarangSatuan::where('barang_id', $this->data->id)
            ->with('satuan')
            ->get();
    }

    public function loadSatuanOptions()
    {
        // Get all satuan except those already assigned to this barang
        $assignedSatuanIds = collect($this->barangSatuanList)->pluck('satuan_id')->toArray();

        $this->satuanOptions = Satuan::whereNotIn('id', $assignedSatuanIds)
            ->get()
            ->map(function ($satuan) {
                return [
                    'id' => $satuan->id,
                    'name' => $satuan->nama_satuan . ' - ' . $satuan->keterangan
                ];
            })->toArray();
    }

    public function loadAturanHarga()
    {
        $this->aturanHargaList = AturanHargaBarang::where('barang_id', $this->data->id)
            ->with('satuan')
            ->orderBy('minimal_penjualan')
            ->get();
    }

    public function loadSatuanAturanOptions()
    {
        // Get satuan from barang satuan untuk aturan harga
        $this->satuanAturanOptions = BarangSatuan::where('barang_id', $this->data->id)
            ->with('satuan')
            ->get()
            ->map(function ($barangSatuan) {
                return [
                    'id' => $barangSatuan->satuan_id,
                    'name' => $barangSatuan->satuan->nama_satuan . ' - ' . $barangSatuan->satuan->keterangan
                ];
            })->toArray();
    }

    public function showAddForm()
    {
        $this->resetForm();

        // Jika belum ada satuan sama sekali, otomatis set sebagai satuan terkecil
        if (count($this->barangSatuanList) === 0) {
            $this->is_satuan_terkecil = 'ya';
        } else {
            $this->is_satuan_terkecil = 'tidak';
        }

        $this->showForm = true;
        $this->editMode = false;
    }

    public function editBarangSatuan($id)
    {
        $barangSatuan = BarangSatuan::findOrFail($id);

        // Tidak bisa edit satuan terkecil
        if ($barangSatuan->is_satuan_terkecil === 'ya') {
            $this->error('Satuan terkecil tidak dapat diedit karena digunakan sebagai referensi konversi');
            return;
        }

        $this->editId = $id;
        $this->satuan_id = $barangSatuan->satuan_id;
        $this->konversi_satuan_terkecil = $barangSatuan->konversi_satuan_terkecil;
        $this->is_satuan_terkecil = $barangSatuan->is_satuan_terkecil;

        $this->editMode = true;
        $this->showForm = true;

        // Add current satuan to options when editing
        $this->loadSatuanOptions();
        $currentSatuan = Satuan::find($this->satuan_id);
        if ($currentSatuan) {
            $found = false;
            foreach ($this->satuanOptions as $option) {
                if ($option['id'] == $this->satuan_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->satuanOptions[] = [
                    'id' => $currentSatuan->id,
                    'name' => $currentSatuan->nama_satuan . ' - ' . $currentSatuan->keterangan
                ];
            }
        }
    }

    public function saveBarangSatuan()
    {
        $this->validate([
            'satuan_id' => 'required|exists:satuan,id',
            'konversi_satuan_terkecil' => 'required|numeric|min:0.01',
            'is_satuan_terkecil' => 'required|in:ya,tidak'
        ], [
            'satuan_id.required' => 'Satuan harus dipilih',
            'satuan_id.exists' => 'Satuan tidak valid',
            'konversi_satuan_terkecil.required' => 'Konversi satuan harus diisi',
            'konversi_satuan_terkecil.numeric' => 'Konversi satuan harus berupa angka',
            'konversi_satuan_terkecil.min' => 'Konversi satuan minimal 0.01',
            'is_satuan_terkecil.required' => 'Status satuan terkecil harus dipilih',
        ]);

        // Check for duplicate satuan (exclude current record when editing)
        $query = BarangSatuan::where('barang_id', $this->data->id)
            ->where('satuan_id', $this->satuan_id);

        if ($this->editMode) {
            $query->where('id', '!=', $this->editId);
        }

        if ($query->exists()) {
            $this->addError('satuan_id', 'Satuan sudah ada untuk barang ini');
            return;
        }

        // Validasi satuan terkecil
        $existingSatuanTerkecil = BarangSatuan::where('barang_id', $this->data->id)
            ->where('is_satuan_terkecil', 'ya');

        if ($this->editMode) {
            $existingSatuanTerkecil->where('id', '!=', $this->editId);
        }

        $existingSatuanTerkecil = $existingSatuanTerkecil->exists();

        // Jika sudah ada satuan terkecil dan user menambah satuan terkecil baru
        if ($existingSatuanTerkecil && $this->is_satuan_terkecil === 'ya') {
            $this->addError('is_satuan_terkecil', 'Sudah ada satuan terkecil untuk barang ini. Hanya boleh ada satu satuan terkecil.');
            return;
        }

        // Jika ini adalah satu-satunya satuan dan user tidak mengatur sebagai satuan terkecil
        $totalSatuan = BarangSatuan::where('barang_id', $this->data->id);
        if ($this->editMode) {
            $totalSatuan->where('id', '!=', $this->editId);
        }
        $totalSatuan = $totalSatuan->count();

        if ($totalSatuan === 0 && $this->is_satuan_terkecil === 'tidak') {
            $this->addError('is_satuan_terkecil', 'Satuan pertama harus ditetapkan sebagai satuan terkecil.');
            return;
        }

        try {
            if ($this->editMode) {
                BarangSatuan::where('id', $this->editId)->update([
                    'satuan_id' => $this->satuan_id,
                    'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
                    'is_satuan_terkecil' => $this->is_satuan_terkecil,
                ]);
                $this->success('Satuan barang berhasil diperbarui');
            } else {
                BarangSatuan::create([
                    'barang_id' => $this->data->id,
                    'satuan_id' => $this->satuan_id,
                    'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
                    'is_satuan_terkecil' => $this->is_satuan_terkecil,
                ]);
                $this->success('Satuan barang berhasil ditambahkan');
            }

            $this->loadBarangSatuan();
            $this->loadSatuanOptions();
            $this->loadAturanHarga();
            $this->loadSatuanAturanOptions();
            $this->closeForm();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deleteBarangSatuan($id)
    {
        try {
            $barangSatuan = BarangSatuan::findOrFail($id);

            // Tidak bisa hapus satuan terkecil
            if ($barangSatuan->is_satuan_terkecil === 'ya') {
                $this->error('Satuan terkecil tidak dapat dihapus karena digunakan sebagai referensi konversi');
                return;
            }

            $barangSatuan->delete();
            $this->success('Satuan barang berhasil dihapus');
            $this->loadBarangSatuan();
            $this->loadSatuanOptions();
            $this->loadAturanHarga();
            $this->loadSatuanAturanOptions();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->satuan_id = '';
        $this->konversi_satuan_terkecil = '';
        $this->is_satuan_terkecil = 'tidak'; // Default ke "tidak"
        $this->editMode = false;
        $this->editId = null;
        $this->resetErrorBag();
    }

    // Methods untuk aturan harga barang
    public function showAddAturanHargaForm()
    {
        $this->resetAturanHargaForm();
        $this->showAturanHargaForm = true;
        $this->editAturanHargaMode = false;
    }

    public function editAturanHarga($id)
    {
        $aturanHarga = AturanHargaBarang::findOrFail($id);

        $this->editAturanHargaId = $id;
        $this->aturan_satuan_id = $aturanHarga->satuan_id;
        $this->minimal_penjualan = $aturanHarga->minimal_penjualan;
        $this->maksimal_penjualan = $aturanHarga->maksimal_penjualan;
        $this->harga_jual = $aturanHarga->harga_jual;

        $this->editAturanHargaMode = true;
        $this->showAturanHargaForm = true;
    }

    public function saveAturanHarga()
    {
        $this->validate([
            'aturan_satuan_id' => 'required|exists:satuan,id',
            'minimal_penjualan' => 'required|numeric|min:1',
            'maksimal_penjualan' => 'nullable|numeric|min:1',
            'harga_jual' => 'required|numeric|min:0.01'
        ], [
            'aturan_satuan_id.required' => 'Satuan harus dipilih',
            'aturan_satuan_id.exists' => 'Satuan tidak valid',
            'minimal_penjualan.required' => 'Minimal penjualan harus diisi',
            'minimal_penjualan.numeric' => 'Minimal penjualan harus berupa angka',
            'minimal_penjualan.min' => 'Minimal penjualan minimal 1',
            'maksimal_penjualan.numeric' => 'Maksimal penjualan harus berupa angka',
            'maksimal_penjualan.min' => 'Maksimal penjualan minimal 1',
            'harga_jual.required' => 'Harga jual harus diisi',
            'harga_jual.numeric' => 'Harga jual harus berupa angka',
            'harga_jual.min' => 'Harga jual minimal 0.01',
        ]);

        // Validasi maksimal > minimal jika diisi
        if ($this->maksimal_penjualan && $this->maksimal_penjualan <= $this->minimal_penjualan) {
            $this->addError('maksimal_penjualan', 'Maksimal penjualan harus lebih besar dari minimal penjualan');
            return;
        }

        // Check for overlapping ranges
        $query = AturanHargaBarang::where('barang_id', $this->data->id)
            ->where('satuan_id', $this->aturan_satuan_id);

        if ($this->editAturanHargaMode) {
            $query->where('id', '!=', $this->editAturanHargaId);
        }

        $existingRules = $query->get();

        foreach ($existingRules as $rule) {
            $existingMin = $rule->minimal_penjualan;
            $existingMax = $rule->maksimal_penjualan ?: PHP_INT_MAX;
            $newMin = $this->minimal_penjualan;
            $newMax = $this->maksimal_penjualan ?: PHP_INT_MAX;

            // Check if ranges overlap
            if ($newMin <= $existingMax && $newMax >= $existingMin) {
                $this->addError('minimal_penjualan', 'Range penjualan bertumpang tindih dengan aturan yang sudah ada');
                return;
            }
        }

        try {
            if ($this->editAturanHargaMode) {
                AturanHargaBarang::where('id', $this->editAturanHargaId)->update([
                    'satuan_id' => $this->aturan_satuan_id,
                    'minimal_penjualan' => $this->minimal_penjualan,
                    'maksimal_penjualan' => $this->maksimal_penjualan ?: null,
                    'harga_jual' => $this->harga_jual,
                ]);
                $this->success('Aturan harga berhasil diperbarui');
            } else {
                AturanHargaBarang::create([
                    'barang_id' => $this->data->id,
                    'satuan_id' => $this->aturan_satuan_id,
                    'minimal_penjualan' => $this->minimal_penjualan,
                    'maksimal_penjualan' => $this->maksimal_penjualan ?: null,
                    'harga_jual' => $this->harga_jual,
                ]);
                $this->success('Aturan harga berhasil ditambahkan');
            }

            $this->loadAturanHarga();
            $this->closeAturanHargaForm();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deleteAturanHarga($id)
    {
        try {
            AturanHargaBarang::findOrFail($id)->delete();
            $this->success('Aturan harga berhasil dihapus');
            $this->loadAturanHarga();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeAturanHargaForm()
    {
        $this->showAturanHargaForm = false;
        $this->resetAturanHargaForm();
    }

    private function resetAturanHargaForm()
    {
        $this->aturan_satuan_id = '';
        $this->minimal_penjualan = '';
        $this->maksimal_penjualan = '';
        $this->harga_jual = '';
        $this->editAturanHargaMode = false;
        $this->editAturanHargaId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.barang.show', [
            'barang_data' => $this->data,
        ]);
    }
}

/* End of file komponen show */
/* Location: ./app/Livewire/Barang/Show.php */
/* Created at 2025-07-03 23:23:37 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */