<?php
$string = "<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"card\">
            <h5 class=\"card-header bg-transparent border-bottom mt-0\"> Data " . str_replace('_', ' ', ucwords($c)) . " </h5>
            <div class=\"card-body\">
                <a href=\"<?= site_url('" . $c_url . "/create') ?>\" class=\"btn btn-primary\"><i class=\"fa fa-plus\"></i> Tambah Data</a>
                <div class=\"table-responsive mt-3\">
                    <table class=\"table table-bordered table-striped table-hover text-nowrap\" width=\"100%\" id=\"mytable\">
                        <thead>
                            <tr>
                                <th class=\"text-center\" width=\"5%\">No</th>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t\t\t\t\t\t\t<th>" . label($row['column_name']) . "</th>";
}
$string .= "\n\t\t\t\t\t\t\t\t<th class=\"text-center\" width=\"15%\">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>";
$string .= "\n\t\t\t\t\t\t<?php
                            \$no = 1;
                            foreach ($" . $c_url . "_data as \$value) { ?>
                            <tr>";
$string .= "\n\t\t\t\t\t\t\t\t<td class=\"text-center\"><?= \$no++; ?></td>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t\t\t\t\t\t\t<td><?= \$value->" . $row['column_name'] . " ?></td>";
}
$string .= "\n\t\t\t\t\t\t\t\t<td class=\"text-center\">
                                    <a href=\"<?= site_url('" . $c_url . "/read/'.\$value->" . $pk . ") ?>\" title=\"Lihat Detail Data\"class=\"btn btn-success\"><i class=\"fa fa-eye\"></i></a>
                                    <a href=\"<?= site_url('" . $c_url . "/update/'.\$value->" . $pk . ") ?>\" title=\"Ubah Data\" class=\"btn btn-warning\"><i class=\"fa fa-edit\"></i></a>
                                    <a href=\"<?= site_url('" . $c_url . "/delete/'.\$value->" . $pk . ") ?>\" title=\"Hapus Data\" class=\"btn btn-danger hapus\"><i class=\"fa fa-trash\"></i></a>
                                </td>
                            </tr>";
$string .=  "\n\t\t\t\t\t\t<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= call_datatable(\"#mytable\") ?>
<?= swal_delete(\"#mytable\",\".hapus\") ?>";
if ($export_excel == '1') {
    $string .= "\n<a href=\"<?= site_url('" . $c_url . "/excel') ?>\" class=\"btn btn-success\"><i class=\"fas fa-file-excel\"></i> Export Excel</a>";
}
if ($export_pdf == '1') {
    $string .= "\n<a href=\"<?= site_url('" . $c_url . "/pdf') ?>\" class=\"btn btn-danger\" target=\"_blank\"><i class=\"fas fa-file\"></i> Cetak PDF</a>";
}
$hasil_view_list = createFile($string, $target . "views/" . $c_url . "/" . $v_list_file);
