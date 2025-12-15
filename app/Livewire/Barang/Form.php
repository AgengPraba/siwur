<?php

namespace App\Livewire\Barang;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Barang;
use App\Models\BarangSatuan;
use Illuminate\Support\Facades\Auth;

#[Title('Form Barang')]
class Form extends Component
{

    public $breadcrumbs;
    public $type = 'create';
    public $barang_ID;
    public $barang;

    public $kode_barang;
    public $nama_barang;
    public $keterangan;
    public $jenis_barang_id;
    public $satuan_terkecil_id;
    public $jenis_barang_data;
    public $satuan_data;
    use Toast;

    public function mount($id = null)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        $columns_jenis_barang = Schema::getColumnListing('jenis_barang');
        $field0_jenis_barang = $columns_jenis_barang[0]; // Nama kolom pertama
        $field1_jenis_barang = $columns_jenis_barang[1]; // Nama kolom kedua
        $this->jenis_barang_data = DB::table('jenis_barang')
            ->select(
                DB::raw($field0_jenis_barang . ' as id'),
                DB::raw($field1_jenis_barang . ' as name')
            )->get()->toArray();

        $columns_satuan = Schema::getColumnListing('satuan');
        $field0_satuan = $columns_satuan[0]; // Nama kolom pertama
        $field1_satuan = $columns_satuan[1]; // Nama kolom kedua
        $this->satuan_data = DB::table('satuan')
            ->select(
                DB::raw($field0_satuan . ' as id'),
                DB::raw($field1_satuan . ' as name')
            )->get()->toArray();

        if ($id) {
            // Ambil data barang berdasarkan ID
            $data = Barang::findOrFail($id);
            $this->type = 'edit';
            if ($data) {
                $this->barang_ID = $data->id;
                $this->kode_barang = $data->kode_barang;
                $this->nama_barang = $data->nama_barang;
                $this->keterangan = $data->keterangan;
                $this->jenis_barang_id = $data->jenis_barang_id;
                $this->satuan_terkecil_id = $data->satuan_terkecil_id;
            }
        }

        $akses = $user->akses ?? null;
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Barang', 'link' => route('barang.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    public function update()
    {
        $uniqueRule = $this->type === 'create'
            ? 'required|string|max:50|unique:barang,kode_barang'
            : 'required|string|max:50|unique:barang,kode_barang,' . $this->barang_ID;

        $this->validate([
            'kode_barang' => $uniqueRule,
            'nama_barang' => 'required|max:200|string',
            'keterangan' => 'nullable|max:500|string',
            'jenis_barang_id' => 'required|exists:jenis_barang,id',
            'satuan_terkecil_id' => 'required|exists:satuan,id',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'nama_barang.max' => 'Nama barang maksimal 200 karakter',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
            'jenis_barang_id.required' => 'Jenis barang wajib dipilih',
            'jenis_barang_id.exists' => 'Jenis barang tidak valid',
            'satuan_terkecil_id.required' => 'Satuan terkecil wajib dipilih',
            'satuan_terkecil_id.exists' => 'Satuan tidak valid',
        ]);

        try {
            DB::transaction(function () {
                // Update data barang
                $barang = Barang::findOrFail($this->barang_ID);
                $oldSatuanTerkecil = $barang->satuan_terkecil_id;

                $barang->update([
                    'kode_barang' => $this->kode_barang,
                    'nama_barang' => $this->nama_barang,
                    'keterangan' => $this->keterangan,
                    'jenis_barang_id' => $this->jenis_barang_id,
                    'satuan_terkecil_id' => $this->satuan_terkecil_id,
                ]);

                // Update barang_satuan jika satuan terkecil berubah
                if ($oldSatuanTerkecil != $this->satuan_terkecil_id) {
                    // Update satuan lama menjadi bukan satuan terkecil
                    BarangSatuan::where('barang_id', $this->barang_ID)
                        ->where('satuan_id', $oldSatuanTerkecil)
                        ->update(['is_satuan_terkecil' => 'tidak']);

                    // Cek apakah sudah ada record untuk satuan baru
                    $existingBarangSatuan = BarangSatuan::where('barang_id', $this->barang_ID)
                        ->where('satuan_id', $this->satuan_terkecil_id)
                        ->first();

                    if ($existingBarangSatuan) {
                        // Update yang sudah ada
                        $existingBarangSatuan->update([
                            'is_satuan_terkecil' => 'ya',
                            'konversi_satuan_terkecil' => 1
                        ]);
                    } else {
                        // Buat record baru
                        BarangSatuan::create([
                            'barang_id' => $this->barang_ID,
                            'satuan_id' => $this->satuan_terkecil_id,
                            'konversi_satuan_terkecil' => 1,
                            'is_satuan_terkecil' => 'ya',
                        ]);
                    }
                }
            });

            $this->success('Berhasil!', 'Data barang berhasil diperbarui.');
            return $this->redirectRoute('barang.index', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal!', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function store()
    {
        $this->validate([
            'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
            'nama_barang' => 'required|max:200|string',
            'keterangan' => 'nullable|max:500|string',
            'jenis_barang_id' => 'required|exists:jenis_barang,id',
            'satuan_terkecil_id' => 'required|exists:satuan,id',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'nama_barang.max' => 'Nama barang maksimal 200 karakter',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
            'jenis_barang_id.required' => 'Jenis barang wajib dipilih',
            'jenis_barang_id.exists' => 'Jenis barang tidak valid',
            'satuan_terkecil_id.required' => 'Satuan terkecil wajib dipilih',
            'satuan_terkecil_id.exists' => 'Satuan tidak valid',
        ]);

        try {
            DB::transaction(function () {
                // Create barang (toko_id akan otomatis terisi melalui model boot method)
                $barang = Barang::create([
                    'kode_barang' => $this->kode_barang,
                    'nama_barang' => $this->nama_barang,
                    'keterangan' => $this->keterangan,
                    'jenis_barang_id' => $this->jenis_barang_id,
                    'satuan_terkecil_id' => $this->satuan_terkecil_id,
                ]);

                // Create barang_satuan dengan satuan terkecil
                BarangSatuan::create([
                    'barang_id' => $barang->id,
                    'satuan_id' => $this->satuan_terkecil_id,
                    'konversi_satuan_terkecil' => 1, // Konversi 1 karena ini satuan terkecil
                    'is_satuan_terkecil' => 'ya',
                ]);
            });

            $this->success('Berhasil!', 'Data barang berhasil ditambahkan beserta satuan terkecilnya.');
            return $this->redirectRoute('barang.index', navigate: true);
        } catch (\Exception $e) {
            $this->error('Gagal!', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {
        return view('livewire.barang.form');
    }
}

/* End of file komponen create */
/* Location: ./app/Livewire/Barang/Form.php */
/* Created at 2025-07-03 23:23:37 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */