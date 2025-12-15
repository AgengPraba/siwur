<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models" . $backslash . "" . $m . ";";



$string .= "\n\nclass Index extends Component
{ ";


    $string .= "\n\n    
    public \$idToDelete = null; // ID yang akan dihapus
    protected \$listeners = ['destroy'];

    public function confirmDelete(\$id)
    {
        \$this->idToDelete = \$id; // Set ID yang akan dihapus
        \$this->dispatch('showDeleteConfirmation', ['id' => \$id]);
    }

    public function destroy()
    {

        if (\$this->idToDelete != null) {
            \$data = ".$m."::findOrFail(\$this->idToDelete);
            // Hapus data
            \$data->delete();
            // Toast Message
            Alert::toast('Berhasil hapus data', 'success');
            // Reset ID yang akan dihapus
            \$this->idToDelete = null;
            // dispatch
            \$this->dispatch('dataDeleted');
        }
    }
    public function render()
    {
        return view('livewire." . $nama_view_komponen . ".index');
    }
";



$string .= "\n\n}\n\n/* End of file komponen index with datatables */
/* Location: ./app/Livewire/$m/Index.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen."/Index.php");
