<?php

// jika tanpa modul
// print_r($table_murni);
// die();
// set data
$pecah = explode('.', $table_murni);
$schema = $pecah[0];
$pecahlagi = explode('-', $pecah[1]);
$table_name = $pecahlagi[0];
$primary_key = $pecahlagi[1];
// echo $primary_key;
// die();
$c = str_replace('_', '', ucfirst($table_name) . 'Controller');
$m = str_replace('_', '', ucfirst($table_name));
$v_list = 'index' . str_replace('_', '', ucfirst($table_name)) . ".blade";
$v_show = 'show' . str_replace('_', '', ucfirst($table_name)) . ".blade";
$v_form = 'form' . str_replace('_', '', ucfirst($table_name)) . ".blade";
// $v_doc = $table_name . "_doc";
// $v_pdf = $table_name . "_pdf";

// url
$c_url = str_replace('_', '', strtolower($c));
$nama_class = str_replace('_', '', strtolower($m));
// filename
$c_file = $c . '.php';
$m_file = $m . '.php';
$v_list_file = $v_list . '.php';
$v_show_file = $v_show . '.php';
$v_form_file = $v_form . '.php';
// $v_doc_file = $v_doc . '.php';
// $v_pdf_file = $v_pdf . '.php';

// show setting
$get_setting = readJSON('core/dengan_modul_settingjson.cfg');
$target = $get_setting->target;
//  $targetViews = $get_setting->$targetViews;
$backslash = str_replace("'", "", "'\'");
if (!file_exists($target."/" .$modul_name."/Resources/views/". $nama_class)) {
   mkdir($target."/" .$modul_name."/Resources/views/" . $nama_class, 0777, true);
   $file = $target."/" .$modul_name."/Routes/web.php";
   include 'dengan_modul_create_routing_web.php';
   file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
}

$pk = $primary_key;
$non_pk = $hc->not_primary_field($table_murni, $pk);
$all = $hc->all_field($table_murni);
//  print_r($all);
//  die();
// generate
include 'core/dengan_modul_create_controller.php';
include 'core/dengan_modul_create_model.php';
if ($jenis_tabel == 'reguler_table') {
   include 'core/dengan_modul_create_view_list.php';
   include 'core/dengan_modul_create_config_pagination.php';
} else if ($jenis_tabel == 'datatables') {
   include 'core/dengan_modul_create_view_list_datatables.php';
   // include 'core/create_libraries_datatables.php';
} else if ($jenis_tabel == 'clientside') {
   include 'core/dengan_modul_create_view_list_clientside.php';
}
include 'core/dengan_modul_create_view_form.php';
include 'core/dengan_modul_create_view_show.php';



$hasil[] = $hasil_controller;
$hasil[] = $hasil_model;
$hasil[] = $hasil_view_list;
$hasil[] = $hasil_view_form;
$hasil[] = $hasil_view_show;