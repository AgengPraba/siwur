<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models" . $backslash . "" . $m . ";";

$string .= "\n\n
#[Title('List " . str_replace('_', ' ', ucwords($table_name)) . "')]
class Index extends Component
{ ";
$string .= "
    use WithPagination,Toast;
    public \$search = ''; // Properti untuk pencarian
    public \$sortField = 'created_at'; // Kolom untuk sorting
    public \$sortDirection = 'desc'; // Arah sorting
    public \$idToDelete = null; // ID yang akan dihapus
    public \$breadcrumbs;
    public int \$perPage = 10;
    public int \$start;

    public function mount(){
        \$this->breadcrumbs = [['label' => 'Home', 'href' => route('home')], ['label' => 'Data " . str_replace('_', ' ', ucwords($table_name)) . "']];
    }

    public function updatingSearch()
    {
        \$this->resetPage(); // Reset halaman saat pencarian diubah
    }
    
     public function sortBy(\$field)
    {
        if (\$this->sortField === \$field) {
            \$this->sortDirection = \$this->sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle arah sorting
        } else {
            \$this->sortField = \$field;
            \$this->sortDirection = 'asc'; // Set default sorting ke ascending
        }
    }

    

    public function destroy()
    {

        if (\$this->idToDelete != null) {
            \$data = " . $m . "::findOrFail(\$this->idToDelete);
            // Hapus data
            \$data->delete();
            // Toast Message
             \$this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            \$this->idToDelete = null;
            \$count  = " . $m . "::count();
            if (\$count == 0) {
                return redirect(route('" . $nama_view_komponen . ".index'));
            } else {
                return \$this->redirectRoute('" . $nama_view_komponen . ".index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        \$data = " . $m . "::";
$urut_relasi = 0;
foreach ($relasi as $row_relasi) {
    
    $urut_relasi++;
    $urut_relasi == 1 ? $string .= "join('" . $row_relasi['referenced_table'] . "','" . $table_name . "." . $row_relasi['column_name'] . "','=','" . $row_relasi['referenced_table'] . "." . $row_relasi['referenced_column'] . "')" : $string .= "->join('" . $row_relasi['referenced_table'] . "','" . $table_name . "." . $row_relasi['column_name'] . "','=','" . $row_relasi['referenced_table'] . "." . $row_relasi['referenced_column'] . "')";
}
if ($urut_relasi != 0) {
    $string .= "->select('" . $table_name . ".*'";
    foreach ($relasi as $row_relasi) {
        $string .= ",'" . $row_relasi['referenced_table'] . "." . $row_relasi['field_relasi'] . "'";
    }
    $string .= ")";
} else {
    
    $string .= "select('" . $table_name . ".*'";
    foreach ($relasi as $row_relasi) {
        $string .= ",'" . $row_relasi['referenced_table'] . "." . $row_relasi['field_relasi'] . "'";
    }
    $string .= ")";
}

$urut_kolom = 0;
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        if ($urut_kolom == 0) {
            $string .= "->whereRaw('LOWER(" . $table_name . "." . $row['column_name'] . ") LIKE ?', [\"%{\$this->search}%\"])";
        } else {
            $string .= "->orWhereRaw('LOWER(" . $table_name . "." . $row['column_name'] . ") LIKE ?', [\"%{\$this->search}%\"])";
        }
    }
    $urut_kolom++;
}
foreach ($relasi as $row_relasi) {

   
    $string .= "->orWhereRaw('LOWER(" . $row_relasi['referenced_table'] . "." . $row_relasi['field_relasi'] . ") LIKE ?', [\"%{\$this->search}%\"])";
}
$string .= " 
               
                ->orderBy(\$this->sortField, \$this->sortDirection) // Sorting
                ->paginate(10)
                ->withQueryString(); // Mempertahankan query string saat paginasi
            \$currentPage = \$data->currentPage();
            \$this->start = (\$currentPage - 1) * \$this->perPage + 1;
            return view('livewire." . $nama_view_komponen . ".index', [
                '" . $nama_class . "_data' => \$data
            ]);
    }
";



$string .= "\n\n}\n\n/* End of file komponen index */
/* Location: ./app/Livewire/$m/Index.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Index.php");
