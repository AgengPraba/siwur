<?php
namespace App\Livewire\Gudang;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\Gudang;
use App\Traits\LivewireTenancy;

#[Title('Show Gudang')]
class Show extends Component
{ 
    use Toast, LivewireTenancy;
    
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Gudang', 'link' => route('gudang.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data (global scope otomatis diterapkan)
        $this->data = Gudang::findOrFail($id);
        
        // Validate ownership
        if (!$this->validateTokoOwnership($this->data)) {
            return redirect()->route('gudang.index');
        }
    }

    public function render()
    {
        return view('livewire.gudang.show', [
            'gudang_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/Gudang/Show.php */
/* Created at 2025-07-03 23:23:21 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */