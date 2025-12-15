<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire;

use Livewire\Component;
";

$string .= "\n\nclass Home extends Component
{ ";


    $string .= "\n\n 
    public function render()
    {
        return view('livewire.home');
    }
";



$string .= "\n\n}\n\n/* End of file komponen home */
/* Location: ./app/Livewire/Home.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/Home.php");
