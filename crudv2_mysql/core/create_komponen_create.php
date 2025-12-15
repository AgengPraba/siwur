<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use App\Models" . $backslash . "" . $m . ";";


$string .= "\n\nclass Create extends Component
{ ";


$string .= "\n\n \t\t  ";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\tpublic \$" . $row['column_name'] . ";\n";
    }
}
$string .= "\n

    public function store()
    {
    \$this->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
    }
}
$string .= "\n\t\t\t]);
       

        $m::create([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => \$this->" . $row['column_name'] . ",";
    }
}
$string .= "\n\t\t\t]);
        //flash message
        Alert::toast('Berhasil tambah data', 'success');
        //redirect
        return \$this->redirectRoute('" . $nama_class . ".index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire." . $nama_view_komponen . ".create', [";
foreach ($relasi as $row_relasi) :
    $string .= "\n\t\t\t\t\t'" . $row_relasi['referenced_table'] . "_data' =>  DB::table('" . $row_relasi['referenced_table'] . "')->get(),";
endforeach;
$string .= "]);
    }
";



$string .= "\n\n}\n\n/* End of file komponen create */
/* Location: ./app/Livewire/$m/Create.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Create.php");
