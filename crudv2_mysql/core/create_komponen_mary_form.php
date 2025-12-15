<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models" . $backslash . "" . $m . ";";


$string .= "\n\n
#[Title('Form " . str_replace('_', ' ', ucwords($table_name)) . "')]
class Form extends Component
{ 
\n
public \$breadcrumbs;
public \$type = 'create';
public \$" . $nama_class . "_ID;
public \$" . $nama_class . ";";


$string .= "\n  ";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\npublic \$" . $row['column_name'] . ";";
    }
}

foreach ($relasi as $row_relasi) :
    $string .= "\npublic \$" . $row_relasi['referenced_table'] . "_data;";
endforeach;

$string .= "
use Toast;
\n
    public function mount(\$id = null)
    {
     ";
foreach ($relasi as $row_relasi) :
    $string .= "\n\t\t\t
    \$columns_" . $row_relasi['referenced_table'] . " = Schema::getColumnListing('" . $row_relasi['referenced_table'] . "');
		\$field0_" . $row_relasi['referenced_table'] . " = \$columns_" . $row_relasi['referenced_table'] . "[0]; // Nama kolom pertama
		\$field1_" . $row_relasi['referenced_table'] . " = \$columns_" . $row_relasi['referenced_table'] . "[1]; // Nama kolom kedua
    \$this->" . $row_relasi['referenced_table'] . "_data = DB::table('" . $row_relasi['referenced_table'] . "')
         ->select(
                DB::raw(\$field0_" . $row_relasi['referenced_table'] . ".' as id'),
                DB::raw(\$field1_" . $row_relasi['referenced_table'] . ".' as name')
            )->get()->toArray();";
endforeach;
$string .= "
        if(\$id){
                // Ambil data " . $nama_class . " berdasarkan ID
                \$data = " . $m . "::findOrFail(\$id);
                \$this->type = 'edit';
                if (\$data) {
                    \$this->" . $nama_class . "_ID = \$data->" . $pk . ";\n";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\$this->" . $row['column_name'] . " = \$data->" . $row['column_name'] . ";\n";
    }
}

$string .= "
                    
            }
        }
        \$this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data " . str_replace('_', ' ', ucwords($table_name)) . "', 'href' => route('" . $nama_view_komponen . ".index')],
            ['label' => \$this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     \$this->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:200',";
    }
}
$string .= "\n\t\t\t]);

        // Update data 
        " . $m . "::findOrFail(\$this->" . $nama_class . "_ID)->update([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => \$this->" . $row['column_name'] . ",";
    }
}
$string .= "\n\t\t\t]);
        
        // Flash message
        \$this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return \$this->redirectRoute('" . $nama_view_komponen . ".index', navigate: true);
    }

    public function store()
    {
    \$this->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:200',";
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
        \$this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return \$this->redirectRoute('" . $nama_view_komponen . ".index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire." . $nama_view_komponen . ".form');
    }
";



$string .= "\n\n}\n\n/* End of file komponen create */
/* Location: ./app/Livewire/$m/Form.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Form.php");
