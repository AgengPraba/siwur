<?php
date_default_timezone_set('Asia/Jakarta');
$dengan_modul_nama_class = $nama_class;
$nama_modul = strtolower($modul_name);
$string = "<?php

namespace Modules". $backslash .$modul_name."\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use DataTables;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\Controller;
use App\Models" . $backslash . "" . $m . ";";



$string .= "\n\nclass " . $c . "  extends Controller
{
    function __construct()
    {
        \$this->middleware('auth');
        ";
$string .= "
    }";

if ($jenis_tabel == 'reguler_table') {

    $string .= "\n\n    public function index(): View
    {
        Paginator::useBootstrap();
        \$data_list = $m::paginate(10);
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/index" . $m . "', ['" . $dengan_modul_nama_class . "_data' => \$data_list, 'i' => 1]);
       
    }
    public function cari(Request \$request): View
    {
        // menangkap data pencarian
        \$cari = \$request->cari;
        Paginator::useBootstrap();
        // mengambil data dari table $c sesuai pencarian data
      
        \$data_list = $m::where('$pk', 'like', '$cari')->paginate(10);
        // mengirim data $c ke view index
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/index', ['" . $dengan_modul_nama_class . "_data' => \$data_list, 'i' => 1]);
    }
    ";
} else if ($jenis_tabel == 'datatables') {

    $string .= "\n\n    public function index(): View
    {
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/index" . $m . "');
    } 
    
    public function data_json( Request \$request) 
    {
        if (\$request->ajax()) {
            \$data =  " . $m . "::latest()->get();
            return Datatables::of(\$data)
                ->addIndexColumn()
                ->addColumn('action', function (\$row) {
                    \$id = \$row['$pk'];
                    \$btn = " . "\"<a href='\" . route('$dengan_modul_nama_class.show', \$id) . \"' class='show btn btn-info btn-sm'>Lihat</a>
                    <a href='\" . route('$dengan_modul_nama_class.edit', \$id) . \"' class='edit btn btn-success btn-sm'>Edit</a> <a data-id='\$id'  href='' class='delete btn btn-danger btn-sm'>Hapus</a>
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
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/form" . $m . "', [ 'action' => route('$dengan_modul_nama_class.store'),'button'=>'Tambah']);
    }

    public function store(Request \$request): RedirectResponse
    {
            \$this->validate(\$request, [";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
    }
}
$string .= "\n\t\t\t]);
            $m::create(\$request->all());
            Alert::toast('Berhasil tambah data " . label($m) . "','success');
            return redirect(route('$dengan_modul_nama_class.index'));
        }
    

    public function show(\$id): View
    {
        \$data_list = $m::findOrFail(\$id);
        // passing data $m yang didapat
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/show" . $m . "', ['" . $dengan_modul_nama_class . "_data' => \$data_list]);
       
    }

    public function edit(\$id): View
    {
        \$data_list = $m::findOrFail(\$id);
        // passing data $m yang didapat
        return view('".$nama_modul."::" . $dengan_modul_nama_class . "/form" . $m . "', ['" . $dengan_modul_nama_class . "_data' => \$data_list, 'action' => route('$dengan_modul_nama_class.update', \$id),'button'=>'Edit']);
    }
    
    public function update(\$id, Request \$request): RedirectResponse 
    {
        \$this->validate(\$request, [";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t'" . $row['column_name'] . "' => 'required|max:100',";
    }
}
$string .= "\n\t\t\t]);
               \$$dengan_modul_nama_class = $m::where('$pk', \$id)->first();";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        $string .= "\n\t\t\t\t \$$dengan_modul_nama_class" . "->" . $row['column_name'] . " =\$request->" . $row['column_name'] . ";";
    }
}
$string .= "
             \$$dengan_modul_nama_class" . "->save(); 
        // alihkan halaman ke halaman $m
        Alert::toast('Berhasil edit data " . label($m) . "','success');
        return redirect(route('$dengan_modul_nama_class.index'));
           
    }
    
    public function delete(Request \$request) 
    {
        if (\$request->ajax()) {
            \$$dengan_modul_nama_class = $m::findOrFail(\$request->id);
            \$$dengan_modul_nama_class" . "->delete();
            Alert::toast('Berhasil hapus data " . label($m) . "','success');
        }
    }
";


$string .= "\n\n}\n\n/* End of file $c_file */
/* Location: ./Modules/". $backslash .$modul_name."/Http/Controllers/$c_file */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 10 */";

$hasil_controller = createFile($string, $target."/" .$modul_name."/Http/Controllers/" . $c_file);
