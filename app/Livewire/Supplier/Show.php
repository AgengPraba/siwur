<?php

namespace App\Livewire\Supplier;

use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Supplier;
use App\Traits\LivewireTenancy;

#[Title('Detail Supplier')]
class Show extends Component
{
    use Toast, LivewireTenancy;
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
        $this->checkTokoAccess();
        
        try {
            // Load supplier with related purchases for statistics
            $this->data = Supplier::with(['pembelian' => function($query) {
                $query->select('id', 'supplier_id', 'total_harga', 'status');
            }])->findOrFail($id);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($this->data)) {
                return redirect()->route('supplier.index');
            }
            
        } catch (\Exception $e) {
            $this->error('Error', 'Data supplier tidak ditemukan.');
            return redirect()->route('supplier.index');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Supplier', 'link' => route('supplier.index')],
            ['label' => 'Detail'],
        ];
    }

    public function render()
    {
        return view('livewire.supplier.show', [
            'supplier_data' => $this->data,
        ]);
    }
}

/* End of file komponen show */
/* Location: ./app/Livewire/Supplier/Show.php */
/* Created at 2025-07-03 23:22:37 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */