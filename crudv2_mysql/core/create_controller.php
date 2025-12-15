<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Yajra\DataTables\DataTables as Datatables;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\Controller;
use App\Models" . $backslash . "" . $m . ";";



$string .= "\n\nclass " . $c . "  extends Controller
{
    function __construct()
    {
        ";
$string .= "
    }";

if ($jenis_tabel == 'reguler_table') {

    $string .= "\n\n    public function index(): View
    {
        Paginator::useBootstrap();
        \$data_list = $m::paginate(10);
        return view('" . $nama_class . "/index" . $m . "', ['" . $nama_class . "_data' => \$data_list, 'i' => 1]);

    }
    public function cari(Request \$request): View
    {
        // menangkap data pencarian
        \$cari = \$request->cari;
        Paginator::useBootstrap();
        // mengambil data dari table $c sesuai pencarian data

        \$data_list = $m::where('$pk', 'like', '$cari')->paginate(10);
        // mengirim data $c ke view index
        return view('" . $nama_class . "/index', ['" . $nama_class . "_data' => \$data_list, 'i' => 1]);
    }
    ";
} else if ($jenis_tabel == 'datatables') {

    $string .= "\n\n    public function index(): View
    {
        return view('" . $nama_class . "/index" . $m . "');
    }

    public function data_json( Request \$request)
    {
        if (\$request->ajax()) {
            \$data =  " . $m . "::";$urut_relasi = 0; foreach ($relasi as $row_relasi) {  $urut_relasi++; $urut_relasi == 1 ? $string .= "join('".$row_relasi['referenced_table'] ."','".$table_name.".".$row_relasi['column_name']."','=','".$row_relasi['referenced_table'].".".$row_relasi['referenced_column']."')" : $string .= "->join('".$row_relasi['referenced_table'] ."','".$table_name.".".$row_relasi['column_name']."','=','".$row_relasi['referenced_table'].".".$row_relasi['referenced_column']."')";} if ($urut_relasi != 0) {
                $string .= "->select('".$table_name.".*'";  foreach ($relasi as $row_relasi) { $string .=",'".$row_relasi['referenced_table'].".".$row_relasi['field_relasi']."'"; }$string .=");";
            }else{
                $string .= "select('".$table_name.".*'";  foreach ($relasi as $row_relasi) { $string .=",'".$row_relasi['referenced_table'].".".$row_relasi['field_relasi']."'"; }$string .=");";
            }
            $string .= "if (\$request->has('order')) {
                \$orderColumnIndex = \$request->get('order')[0]['column']; // Indeks kolom yang ingin di-sort
                \$orderDirection = \$request->get('order')[0]['dir']; // 'asc' atau 'desc'
                // Mendapatkan nama kolom berdasarkan indeks
                \$columns = [";
                $string .="'".$table_name.".".$pk."',";
                foreach ($non_pk as $row) {
                    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
                    } else {
                        $string .="'".$table_name.".".$row['column_name']."',";
                    }
                  
                } 
                foreach ($relasi as $row_relasi) { 
                    
                    $string .="'".$row_relasi['referenced_table'].".".$row_relasi['field_relasi']."',";
                 }
                $string .="]; // Sesuaikan dengan nama kolom yang ada
                \$orderColumn = \$columns[\$orderColumnIndex];
                \$data->orderBy(\$orderColumn, \$orderDirection);
            } else {
                \$data->latest();
            }
            return Datatables::of(\$data)
                ->filter(function (\$query) use (\$request) {
                    if (\$request->has('search') && !empty(\$request->get('search')['value'])) {
                        \$search = strtolower(\$request->get('search')['value']);
                        \$query->where(function (\$q) use (\$search) {";
                        $urut_kolom = 0;
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        if($urut_kolom == 0){
            $string .="\$q->whereRaw('LOWER(".$table_name.".".$row['column_name'].") LIKE ?', [\"%{\$search}%\"])";
        }else{
            $string .="->orWhereRaw('LOWER(".$table_name.".".$row['column_name'].") LIKE ?', [\"%{\$search}%\"])";
        }
        
    }
    $urut_kolom++;
} 
foreach ($relasi as $row_relasi) { 
    
    $string .="->orWhereRaw('LOWER(".$row_relasi['referenced_table'].".".$row_relasi['field_relasi'].") LIKE ?', [\"%{\$search}%\"])";
 }
$string .= "\n\t\t;
});
}
})
              
                         
                ->addIndexColumn()
                ->addColumn('action', function (\$row) {
                    \$id = \$row['$pk'];
                    \$btn = " . "\"<a href='\" . route('$nama_class.show', \$id) . \"' class='lihat btn btn-info btn-sm text-white'>Lihat</a>
                    <a href='\" . route('$nama_class.edit', \$id) . \"' class='edit btn btn-success btn-sm text-white'>Edit</a> <a data-id='\$id'  href='' class='delete btn btn-danger btn-sm text-white'>Hapus</a>
                   \"" . ";
                    return \$btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }";
}

$string .= "\n\n    public function create(): View
    {
        return view('" . $nama_class . "/form" . $m . "',
                [
                    'action' => route('$nama_class.store'),
                    'button'=>'Tambah',";
        foreach ($relasi as $row_relasi) :
                    $string .="\n\t\t\t\t\t'" . $row_relasi['referenced_table'] . "_data' =>  DB::table('".$row_relasi['referenced_table']."')->get(),";
        endforeach;

        $string .= "\n\t\t\t\t]);";
   $string .="\n\t }

    public function store(Request \$request): RedirectResponse
    {
            \$request->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
    }
}
$string .= "\n\t\t\t]);
            $m::create(\$request->all());
            Alert::toast('Berhasil tambah data " . label($m) . "','success');
            return redirect(route('$nama_class.index'));
        }


    public function show(\$id): View
    {
        \$data_list = $m::";$urut_relasi = 0; foreach ($relasi as $row_relasi) {  $urut_relasi++; $urut_relasi == 1 ? $string .= "join('".$row_relasi['referenced_table'] ."','".$table_name.".".$row_relasi['column_name']."','=','".$row_relasi['referenced_table'].".".$row_relasi['referenced_column']."')" : $string .= "->join('".$row_relasi['referenced_table'] ."','".$table_name.".".$row_relasi['column_name']."','=','".$row_relasi['referenced_table'].".".$row_relasi['referenced_column']."')";} if ($urut_relasi != 0) {
            $string .= "->select('".$table_name.".*'";  foreach ($relasi as $row_relasi) { $string .=",'".$row_relasi['referenced_table'].".".$row_relasi['field_relasi']."'"; }$string .=")->";
        }
        $string .="findOrFail(\$id);
        // passing data $m yang didapat
        return view('" . $nama_class . "/show" . $m . "', ['" . $nama_class . "_data' => \$data_list]);

    }

    public function edit(\$id): View
    {
        \$data_list = $m::findOrFail(\$id);
        // passing data $m yang didapat
        return view('" . $nama_class . "/form" . $m . "',
                [
                    '" . $nama_class . "_data' => \$data_list,
                    'action' => route('$nama_class.update', \$id),
                    'button'=>'Edit',";
                    foreach ($relasi as $row_relasi) :
                        $string .="\n\t\t\t\t\t'" . $row_relasi['referenced_table'] . "_data' =>  DB::table('".$row_relasi['referenced_table']."')->get(),
                        ";
                      endforeach;

    $string .= "\n\t\t\t\t]);
    }

    public function update(\$id, Request \$request): RedirectResponse
    {
        \$request->validate([";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
    }
}
$string .= "\n\t\t\t]);
               \$$nama_class = $m::where('$pk', \$id)->first();";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t \$$nama_class" . "->" . $row['column_name'] . " =\$request->" . $row['column_name'] . ";";
    }
}
$string .= "
             \$$nama_class" . "->save();
        // alihkan halaman ke halaman $m
        Alert::toast('Berhasil edit data " . label($m) . "','success');
        return redirect(route('$nama_class.index'));

    }

    public function delete(Request \$request)
    {
        if (\$request->ajax()) {
            \$$nama_class = $m::findOrFail(\$request->id);
            \$$nama_class" . "->delete();
            Alert::toast('Berhasil hapus data " . label($m) . "','success');
        }
    }
";


$string .= "\n\n}\n\n/* End of file $c_file */
/* Location: ./app/Http/Controllers/$c_file */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 */";

$hasil_controller = createFile($string, $target . "/Http/Controllers/" . $c_file);
