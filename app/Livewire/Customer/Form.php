<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;
use App\Traits\LivewireTenancy;
use Livewire\Attributes\Validate;

#[Title('Form Customer')]
class Form extends Component
{ 
    use Toast, LivewireTenancy;

    public $breadcrumbs;
    public $type = 'create';
    public $customer_ID;
    public $customer;
    
    #[Validate]
    public $nama_customer = '';
    
    #[Validate]
    public $alamat = '';
    
    #[Validate]
    public $no_hp = '';
    
    #[Validate]
    public $email = '';
    
    public $keterangan = '';


    public function rules()
    {
        $rules = [
            'nama_customer' => 'required|string|min:3|max:200',
            'alamat' => 'required|string|min:10|max:500',
            'no_hp' => 'required|string|min:10|max:20|regex:/^[\+]?[0-9\-\s\(\)]+$/',
            'email' => 'required|email|max:200|unique:customer,email',
            'keterangan' => 'nullable|string|max:1000',
        ];

        // For edit mode, exclude current record from unique validation
        if ($this->type === 'edit' && $this->customer_ID) {
            $rules['email'] = 'required|email|max:200|unique:customer,email,' . $this->customer_ID;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nama_customer.required' => 'Nama customer wajib diisi.',
            'nama_customer.min' => 'Nama customer minimal 3 karakter.',
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
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }

        if ($id) {
            try {
                // Ambil data customer berdasarkan ID dengan validasi toko
                $data = Customer::findOrFail($id);
                
                // Validate ownership
                if (!$this->validateTokoOwnership($data)) {
                    return redirect()->route('customer.index');
                }
                
                $this->type = 'edit';
                $this->customer_ID = $data->id;
                $this->nama_customer = $data->nama_customer;
                $this->alamat = $data->alamat;
                $this->no_hp = $data->no_hp;
                $this->email = $data->email;
                $this->keterangan = $data->keterangan ?? '';
            } catch (\Exception $e) {
                $this->error('Error', 'Data customer tidak ditemukan.');
                return redirect()->route('customer.index');
            }
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Customer', 'link' => route('customer.index')],
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

            $customer = Customer::findOrFail($this->customer_ID);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($customer)) {
                return redirect()->route('customer.index');
            }

            $customer->update([
                'nama_customer' => trim($this->nama_customer),
                'alamat' => trim($this->alamat),
                'no_hp' => trim($this->no_hp),
                'email' => trim($this->email),
                'keterangan' => trim($this->keterangan),
            ]);

            DB::commit();
            
            $this->success('Berhasil!', 'Data customer berhasil diperbarui.', redirectTo: route('customer.index'));
            
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

            $customer = Customer::create([
                'nama_customer' => trim($this->nama_customer),
                'alamat' => trim($this->alamat),
                'no_hp' => trim($this->no_hp),
                'email' => trim($this->email),
                'keterangan' => trim($this->keterangan),
                'toko_id' => $this->getCurrentTokoId(),
            ]);

            DB::commit();
            
            $this->success('Berhasil!', 'Customer baru berhasil ditambahkan.', redirectTo: route('customer.index'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal!', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function resetForm()
    {
        $this->reset(['nama_customer', 'alamat', 'no_hp', 'email', 'keterangan']);
        $this->resetValidation();
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.customer.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/Customer/Form.php */
/* Created at 2025-07-03 23:23:31 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */