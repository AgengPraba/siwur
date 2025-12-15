<?php

$hasil = array();

if (isset($_POST['generate'])) {
    // get form data
    $table_murni = safe($_POST['table_name']);
    $jenis_tabel = safe($_POST['jenis_tabel']);
    $jenis_frontend = safe($_POST['jenis_frontend']);
    // $export_excel = safe($_POST['export_excel']);
    // $export_word = safe($_POST['export_word']);
    // $export_pdf = safe($_POST['export_pdf']);
    //$modul_name = safe($_POST['modulnya']);
    $controller = safe($_POST['controller']);
    $model = safe($_POST['model']);
    $form = safe($_POST['form']);
    $list = safe($_POST['list']);
    $show = safe($_POST['show']);
    // print_r($_POST);
    if (($table_murni <> '') and ($jenis_frontend <> '')) {
        if ($jenis_frontend == 'livewire') {
            include 'proses_tanpa_modul_livewire.php';
        } elseif ($jenis_frontend == 'livewire_tailwind') {
            include 'proses_tanpa_modul_livewire_tailwind.php';
        } elseif ($jenis_frontend == 'livewire_mary') {
            include 'proses_tanpa_modul_livewire_mary.php';
        } else {
            if ($jenis_tabel == 'datatables') {
                include 'proses_tanpa_modul.php';
            } else {
                echo "Untuk Regular Table pada frontend blade belum tersedia";
            }
        }
    } else {
        $hasil[] = 'No table selected.';
    }
}
