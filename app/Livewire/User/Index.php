<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\User;
use App\Models\Akses;
use App\Traits\LivewireTenancy;
use Spatie\Permission\Models\Role;

#[Title('List User')]
class Index extends Component
{
    use WithPagination, Toast, LivewireTenancy;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $idToDelete = null;
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    public function mount()
    {
        $this->checkTokoAccess();
        
        // Only admin can access user management
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data User']
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        try {
            // Prevent deleting own account
            if ($id == auth()->id()) {
                $this->error('Gagal!', 'Tidak dapat menghapus akun sendiri.');
                return;
            }

            $user = User::findOrFail($id);
            
            // Delete related akses first
            Akses::where('user_id', $id)->delete();
            
            // Delete user
            $user->delete();
            
            $this->success('Berhasil!', 'Data user berhasil dihapus.');
            
        } catch (\Exception $e) {
            $this->error('Gagal!', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function destroy()
    {
        if ($this->idToDelete != null) {
            $this->delete($this->idToDelete);
            $this->idToDelete = null;
            
            $count = User::count();
            if ($count == 0) {
                return redirect(route('user.index'));
            } else {
                return $this->redirectRoute('user.index', navigate: true);
            }
        }
    }

    public function render()
    {
        $currentToko = $this->getCurrentToko();
        
        // Get users that have access to current toko
        $data = User::query()
            ->whereHas('akses', function ($query) use ($currentToko) {
                $query->where('toko_id', $currentToko->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();

        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        return view('livewire.user.index', [
            'user_data' => $data,
            'currentToko' => $currentToko
        ]);
    }
}
