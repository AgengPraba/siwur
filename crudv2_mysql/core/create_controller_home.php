<?php
date_default_timezone_set('Asia/Jakarta');
$c = 'HomeController';
$string = "<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
";

$string .= "\n\nclass " . $c . "  extends Controller
{
    

    public function index()
    {
        return view('home');
    }
    
";





$string .= "\n\n}\n\n/* End of file $c */
/* Location: ./app/Http/Controllers/$c */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 blade*/";

$hasil_controller = createFile($string, $target . "/Http/Controllers/" . $c.".php");
