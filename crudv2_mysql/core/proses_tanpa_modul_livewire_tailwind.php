<?php
function toCamelCase($string)
{
    // Mengganti underscore dengan spasi, kemudian mengubah setiap kata menjadi kapital  
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
}

function toKebabCase($string)
{
    // Mengganti underscore dengan dash  
    return str_replace('_', '-', $string);
}

$table_name = $table_murni;
if ($jenis_tabel == 'datatables') {
    $c = ucfirst($table_name) . 'Controller';
    // url
    $c_url = strtolower($c);
    // filename
    $c_file = $c . '.php';
}

$m = ucfirst($table_name);
$m_file = $m . '.php';
$komponen_index = 'Index.php';
$komponen_edit = 'Edit.php';
$komponen_show = 'Show.php';
$komponen_create = 'Create.php';
$v_komponen_index = 'index.blade.php';
$v_komponen_edit = 'edit.blade.php';
$v_komponen_show = 'show.blade.php';
$v_komponen_create = 'create.blade.php';
$v_komponen_form = 'form.blade.php';
$nama_class = strtolower($m);
$nama_komponen = toCamelCase($nama_class);
$nama_view_komponen = toKebabCase($table_name);
// show setting
$get_setting = readJSON('core/settingjson_livewire.cfg');
$target = $get_setting->target;
//  $targetViews = $get_setting->$targetViews;
$backslash = str_replace("'", "", "'\'");
if (!file_exists("../resources/views/livewire/" . $nama_view_komponen)) {
    mkdir("../resources/views/livewire/" . $nama_view_komponen, 0777, true);
}

if (!file_exists("../app/Livewire/" . $nama_komponen)) {
    mkdir("../app/Livewire/" . $nama_komponen, 0777, true);
    $file = '../routes/web.php';
    include 'create_routing_web_livewire_tailwind.php';
    file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
}

$pk = $hc->primary_field($table_murni);
$non_pk = $hc->not_primary_field($table_murni, $pk);
$relasi = $hc->table_relation($table_murni);
$all = $hc->all_field($table_murni);
//  print_r($all);
//  die();
// generate 
require 'core/create_model.php';
$hasil_controller = null;
require 'core/create_view_komponen_back-refresh.php';
require 'core/create_view_komponen_notif.php';
require 'core/create_komponen_tailwind_index.php';
require 'core/create_view_komponen_tailwind_index.php';
require 'core/create_komponen_tailwind_form.php';
require 'core/create_view_komponen_tailwind_form.php';
require 'core/create_komponen_tailwind_show.php';
require 'core/create_view_komponen_tailwind_show.php';
$hasil[] = $hasil_controller;
$hasil[] = $hasil_komponen;
$hasil[] = $hasil_model;
// $hasil[] = $hasil_view_list;
// $hasil[] = $hasil_view_form;
// $hasil[] = $hasil_view_show;
