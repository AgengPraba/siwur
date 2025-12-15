<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models" . $backslash . "" . $m . ";";


$string .= "\n\nclass Form extends Component
{ 
\n
public \$type = 'create';
public \$" . $nama_class . "_ID;
public \$".$nama_class.";";


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

$string .= "\n
    public function mount(\$id = null)
    {
    ";
    foreach ($relasi as $row_relasi) :
        $string .= "\n\t\t\t\$this->" . $row_relasi['referenced_table'] . "_data = DB::table('" . $row_relasi['referenced_table'] . "')->get();";
    endforeach;
    $string .="
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
       

    }

     public function update(){
     \$this->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
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
        session()->flash('message', 'Berhasil Edit Data.');
        session()->flash('type', 'success');
        
        // Redirect
        return \$this->redirectRoute('" . $nama_class . ".index', navigate: true);
    }

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
        session()->flash('message', 'Berhasil Tambah Data');
        session()->flash('type', 'success');
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

        return view('livewire." . $nama_view_komponen . ".form');
    }
";



$string .= "\n\n}\n\n/* End of file komponen create */
/* Location: ./app/Livewire/$m/Form.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Form.php");
