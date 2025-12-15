<?php
namespace App\Livewire\Supplier;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Supplier;
use Livewire\Attributes\Validate;
use App\Traits\LivewireTenancy;
use Illuminate\Validation\Rule;

#[Title('Form Supplier')]
class Form extends Component
{ 
    use Toast, LivewireTenancy;

    public $breadcrumbs;
    public $type = 'create';
    public $supplier_ID;
    public $supplier;
    
    #[Validate]
    public $nama_supplier = '';
    
    #[Validate]
    public $alamat = '';
    
    #[Validate]
    public $no_hp = '';
    
    #[Validate]
    public $email = '';
    
    #[Validate]
    public $keterangan = '';

    public function rules()
    {
        return [
            'nama_supplier' => [
                'required',
                'string',
                'min:3',
                'max:200',
                Rule::unique('supplier', 'nama_supplier')
                    ->where('toko_id', $this->getCurrentTokoId())
                    ->ignore($this->supplier_ID)
            ],
            'alamat' => 'required|string|min:10|max:500',
            'no_hp' => 'required|string|min:10|max:20|regex:/^[\+]?[0-9\-\s\(\)]+$/',
            'email' => [
                'required',
                'email',
                'max:200',
                Rule::unique('supplier', 'email')
                    ->where('toko_id', $this->getCurrentTokoId())
                    ->ignore($this->supplier_ID)
            ],
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'nama_supplier.min' => 'Nama supplier minimal 3 karakter.',
            'nama_supplier.unique' => 'Nama supplier sudah terdaftar.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.min' => 'Alamat minimal 10 karakter.',
            'no_hp.required' => 'Nomor telepon wajib diisi.',
            'no_hp.regex' => 'Format nomor telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ];
    }

    public function mount($id = null)
    {
        $this->checkTokoAccess();
        
        if ($id) {
            try {
                $data = Supplier::findOrFail($id);
                
                // Validate ownership
                if (!$this->validateTokoOwnership($data)) {
                    return redirect()->route('supplier.index');
                }
                
                $this->type = 'edit';
                $this->supplier_ID = $data->id;
                $this->nama_supplier = $data->nama_supplier;
                $this->alamat = $data->alamat;
                $this->no_hp = $data->no_hp;
                $this->email = $data->email;
                $this->keterangan = $data->keterangan ?? '';
            } catch (\Exception $e) {
                $this->error('Error', 'Data supplier tidak ditemukan.');
                return redirect()->route('supplier.index');
            }
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Supplier', 'link' => route('supplier.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $supplier = Supplier::findOrFail($this->supplier_ID);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($supplier)) {
                return redirect()->route('supplier.index');
            }

            $supplier->update([
                'nama_supplier' => trim($this->nama_supplier),
                'alamat' => trim($this->alamat),
                'no_hp' => trim($this->no_hp),
                'email' => trim($this->email),
                'keterangan' => trim($this->keterangan),
            ]);

            DB::commit();
            
            $this->success('Berhasil!', 'Data supplier berhasil diperbarui.', redirectTo: route('supplier.index'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal!', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    public function store()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $supplier = Supplier::create([
                'nama_supplier' => trim($this->nama_supplier),
                'alamat' => trim($this->alamat),
                'no_hp' => trim($this->no_hp),
                'email' => trim($this->email),
                'keterangan' => trim($this->keterangan),
                'toko_id' => $this->getCurrentTokoId(),
            ]);

            DB::commit();
            
            $this->success('Berhasil!', 'Supplier baru berhasil ditambahkan.', redirectTo: route('supplier.index'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal!', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function resetForm()
    {
        $this->reset(['nama_supplier', 'alamat', 'no_hp', 'email', 'keterangan']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.supplier.form');
    }
}

/* End of file komponen create */
/* Location: ./app/Livewire/Supplier/Form.php */
/* Created at 2025-07-03 23:22:37 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */