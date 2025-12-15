<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use App\Models" . $backslash . "" . $m . ";";



$string .= "\n\nclass Show extends Component
{ ";


$string .= "\n\n    
    public \$data;

    public function mount(\$id)
    {
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
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Show.php");
