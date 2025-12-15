<?php

namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\User;
use App\Models\Akses;
use App\Traits\LivewireTenancy;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;

#[Title('Form User')]
class Form extends Component
{
    use Toast, LivewireTenancy;

    public $breadcrumbs;
    public $type = 'create';
    public $user_ID;
    public $user;

    #[Validate]
    public $name = '';

    #[Validate]
    public $email = '';

    #[Validate]
    public $password = '';

    #[Validate]
    public $password_confirmation = '';

    #[Validate]
    public $role = '';

    public $roles = [];

    public function rules()
    {
        $rules = [
            'name' => 'required|string|min:3|max:200',
            'email' => 'required|email|max:200|unique:users,email',
            'role' => 'required|string|in:admin,kasir,staff_gudang,akuntan',
        ];

        if ($this->type === 'create') {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['password_confirmation'] = 'nullable|string|min:8';
            $rules['email'] = 'required|email|max:200|unique:users,email,' . $this->user_ID;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ];
    }

    public function mount($id = null)
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }

        // Only admin can access user management
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        // Load available roles
        $this->roles = [
            ['id' => 'admin', 'name' => 'Admin'],
            ['id' => 'kasir', 'name' => 'Kasir'],
            ['id' => 'staff_gudang', 'name' => 'Staff Gudang'],
            ['id' => 'akuntan', 'name' => 'Akuntan'],
        ];

        if ($id) {
            try {
                $data = User::findOrFail($id);
                
                // Check if user has access to current toko
                $currentToko = $this->getCurrentToko();
                $akses = Akses::where('user_id', $data->id)
                    ->where('toko_id', $currentToko->id)
                    ->first();
                
                if (!$akses) {
                    $this->error('Error', 'User tidak ditemukan.');
                    return redirect()->route('user.index');
                }

                $this->type = 'edit';
                $this->user_ID = $data->id;
                $this->name = $data->name;
                $this->email = $data->email;
                $this->role = $akses->role;
                
            } catch (\Exception $e) {
                $this->error('Error', 'Data user tidak ditemukan.');
                return redirect()->route('user.index');
            }
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data User', 'link' => route('user.index')],
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

            $user = User::findOrFail($this->user_ID);
            $currentToko = $this->getCurrentToko();

            // Update user
            $userData = [
                'name' => trim($this->name),
                'email' => trim($this->email),
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $user->update($userData);

            // Update akses
            Akses::where('user_id', $user->id)
                ->where('toko_id', $currentToko->id)
                ->update(['role' => $this->role]);

            // Sync role using Spatie
            $user->syncRoles([$this->role]);

            DB::commit();

            $this->success('Berhasil!', 'Data user berhasil diperbarui.', redirectTo: route('user.index'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal!', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function store()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $currentToko = $this->getCurrentToko();

            // Create user
            $user = User::create([
                'name' => trim($this->name),
                'email' => trim($this->email),
                'password' => Hash::make($this->password),
            ]);

            // Create akses
            Akses::create([
                'user_id' => $user->id,
                'toko_id' => $currentToko->id,
                'role' => $this->role,
            ]);

            // Assign role using Spatie
            $user->assignRole($this->role);

            DB::commit();

            $this->success('Berhasil!', 'User baru berhasil ditambahkan.', redirectTo: route('user.index'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal!', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.user.form');
    }
}
