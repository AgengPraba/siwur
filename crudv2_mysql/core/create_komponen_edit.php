<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $nama_komponen . ";

use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use App\Models" . $backslash . "" . $m . ";";

$string .= "\n\nclass Edit extends Component
{ ";


$string .= "\n\n \t\t  
public \$" . $nama_class . "_ID;";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\npublic \$" . $row['column_name'] . ";";
    }
}
foreach ($relasi as $row_relasi) :
    $string .= "\n\t\t\tpublic \$" . $row_relasi['referenced_table'] . "_data;";
endforeach;
$string .= "\n

   public function mount(\$id)
    {
        // Ambil data " . $nama_class . " berdasarkan ID
        \$data = " . $m . "::findOrFail(\$id);
        
        if (\$data) {
            \$this->" . $nama_class . "_ID = \$data->" . $pk . ";\n";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\$this->" . $row['column_name'] . " = \$data->" . $row['column_name'] . ";\n";
    }
}
foreach ($relasi as $row_relasi) :
    $string .= "\n\t\t\t\t\t \$this->" . $row_relasi['referenced_table'] . "_data =  DB::table('" . $row_relasi['referenced_table'] . "')->get();";
endforeach;
$string .= "
            
        }

    }

    public function update()
    {
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
        Alert::toast('Berhasil update data', 'success');
        
        // Redirect
        return \$this->redirectRoute('" . $nama_class . ".index', navigate: true);
    }

    public function render()
    {
        return view('livewire." . $nama_view_komponen . ".edit');
    }

";



$string .= "\n\n}\n\n/* End of file komponen edit */
/* Location: ./app/Livewire/$m/Edit.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $nama_komponen . "/Edit.php");
