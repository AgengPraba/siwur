<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Traits\LivewireTenancy;

#[Title('Show Customer')]
class Show extends Component
{
    use Toast, LivewireTenancy; 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
        $this->checkTokoAccess();
        
        $this->data = Customer::select('customer.*')->findOrFail($id);
        
        // Validate ownership
        if (!$this->validateTokoOwnership($this->data)) {
            return redirect()->route('customer.index');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Customer', 'link' => route('customer.index')],
            ['label' => 'Lihat'],
        ];
    }

    public function render()
    {
        return view('livewire.customer.show', [
            'customer_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/Customer/Show.php */
/* Created at 2025-07-03 23:23:31 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */