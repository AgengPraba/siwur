<?php

$string = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $m extends Model
{
    use HasFactory;
    protected \$table = '$schema.$table_name';
    protected \$fillable = [";
    foreach ($non_pk as $row) {
        if ($row['column_name'] =='created_at' || $row['column_name'] =='updated_at' || $row['column_name'] =='deleted_at') {
        }else{
            $string .= "'".$row['column_name']."',";
        } 
       
    }
    $string .= "];
    protected \$primaryKey = '$pk';
}


/* End of file $m_file */
/* Location: ./app/Models/$m_file */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar Laravel 8 CRUD Generator : */";




$hasil_model = createFile($string, "../app/Models/" . $m_file);
