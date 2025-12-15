<?php

// jika tanpa modul
// print_r($table_murni);
// die();
// set data
// echo $primary_key;
// die();
$table_name = $table_murni;
$c = ucfirst($table_name) . 'Controller';
$m = ucfirst($table_name);
$v_list = 'index' . ucfirst($table_name) . ".blade";
$v_show = 'show' . ucfirst($table_name) . ".blade";
$v_form = 'form' . ucfirst($table_name) . ".blade";
// $v_doc = $table_name . "_doc";
// $v_pdf = $table_name . "_pdf";

// url
$c_url = strtolower($c);
$nama_class = strtolower($m);
// filename
$c_file = $c . '.php';
$m_file = $m . '.php';
$v_list_file = $v_list . '.php';
$v_show_file = $v_show . '.php';
$v_form_file = $v_form . '.php';
// $v_doc_file = $v_doc . '.php';
// $v_pdf_file = $v_pdf . '.php';

// show setting
$get_setting = readJSON('core/settingjson.cfg');
$target = $get_setting->target;
//  $targetViews = $get_setting->$targetViews;
$backslash = str_replace("'", "", "'\'");
if (!file_exists("../resources/views/" . $nama_class)) {
    mkdir("../resources/views/" . $nama_class, 0777, true);
    $file = '../routes/web.php';
    include 'create_routing_web.php';
    file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
}

$pk = $hc->primary_field($table_murni);
$non_pk = $hc->not_primary_field($table_murni, $pk);
$relasi = $hc->table_relation($table_murni);
$all = $hc->all_field($table_murni);
//  print_r($all);
//  die();
// generate
include 'core/create_controller.php';
include 'core/create_model.php';
if ($jenis_tabel == 'reguler_table') {
    include 'core/create_view_list.php';
    include 'core/create_config_pagination.php';
} else if ($jenis_tabel == 'datatables') {
    include 'core/create_view_list_datatables.php';
    // include 'core/create_libraries_datatables.php';
} else if ($jenis_tabel == 'clientside') {
    include 'core/create_view_list_clientside.php';
}
include 'core/create_view_form.php';
include 'core/create_view_show.php';

$hasil[] = $hasil_controller;
$hasil[] = $hasil_model;
$hasil[] = $hasil_view_list;
$hasil[] = $hasil_view_form;
$hasil[] = $hasil_view_show;
