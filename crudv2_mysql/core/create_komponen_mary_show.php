<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models" . $backslash . "" . $m . ";";

$string .= "\n
#[Title('Show " . str_replace('_', ' ', ucwords($table_name)) . "')]
class Show extends Component
{ ";


$string .= "\n 
    public \$data;
    public \$breadcrumbs;

    public function mount(\$id)
    {
    \$this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data " . str_replace('_', ' ', ucwords($table_name)) . "', 'href' => route('".$nama_view_komponen.".index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data
        \$this->data = " . $m . "::";
$urut_relasi = 0;
foreach ($relasi as $row_relasi) {
    $urut_relasi++;
    $urut_relasi == 1 ? $string .= "join('" . $row_relasi['referenced_table'] . "','" . $table_name . "." . $row_relasi['column_name'] . "','=','" . $row_relasi['referenced_table'] . "." . $row_relasi['referenced_column'] . "')" : $string .= "->join('" . $row_relasi['referenced_table'] . "','" . $table_name . "." . $row_relasi['column_name'] . "','=','" . $row_relasi['referenced_table'] . "." . $row_relasi['referenced_column'] . "')";
}
if ($urut_relasi != 0) {
    $string .= "->select('" . $table_name . ".*'";
    foreach ($relasi as $row_relasi) {
        if ($row_relasi['field_relasi'] === "USER") {
            $row_relasi['field_relasi'] = 'name';
        }
        $string .= ",'" . $row_relasi['referenced_table'] . "." . $row_relasi['field_relasi'] . "'";
    }
    $string .= ")";
} else {
    $string .= "select('" . $table_name . ".*'";
    foreach ($relasi as $row_relasi) {
        if ($row_relasi['field_relasi'] === "USER") {
            $row_relasi['field_relasi'] = 'name';
        }
        $string .= ",'" . $row_relasi['referenced_table'] . "." . $row_relasi['field_relasi'] . "'";
    }
    $string .= ")";
}
$string .= "->findOrFail(\$id);
    }

    public function render()
    {
        return view('livewire." . $nama_view_komponen . ".show', [
            '" . $nama_class . "_data' => \$this->data,
        ]);
    }
";



$string .= "\n\n}\n\n/* End of file komponen show */
/* Location: ./app/Livewire/$m/Show.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Show.php");
