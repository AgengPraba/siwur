<?php

namespace App\Livewire\User;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\User;
use App\Models\Akses;
use App\Traits\LivewireTenancy;

#[Title('Show User')]
class Show extends Component
{
    use Toast, LivewireTenancy;

    public $data;
    public $akses;
    public $breadcrumbs;

    public function mount($id)
    {
        $this->checkTokoAccess();

        // Only admin can access user management
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $this->data = User::findOrFail($id);
        
        // Get akses for current toko
        $currentToko = $this->getCurrentToko();
        $this->akses = Akses::where('user_id', $id)
            ->where('toko_id', $currentToko->id)
            ->first();

        if (!$this->akses) {
            $this->error('Error', 'User tidak ditemukan.');
            return redirect()->route('user.index');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data User', 'link' => route('user.index')],
            ['label' => 'Lihat'],
        ];
    }

    public function render()
    {
        return view('livewire.user.show', [
            'user_data' => $this->data,
            'akses_data' => $this->akses,
        ]);
    }
}
