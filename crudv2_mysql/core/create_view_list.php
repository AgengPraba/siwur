<?php
$string = "<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"card shadow-sm mb-4\">
            <h5 class=\"card-header bg-transparent border-bottom mt-0\"> Data " . str_replace('_', ' ', ucwords($c)) . " </h5>
            <div class=\"card-body\">
                <div class=\"row\">
                    <div class=\"col-4\">
                        <a href=\"<?= site_url('" . $c_url . "/create') ?>\" class=\"btn btn-primary\"><i class=\"fa fa-plus\"></i> Tambah Data</a>
                    </div>
                    <div class=\"col-4\">
                         <?= \$pagination ?>
                    </div>
                    <div class=\"col-4\">
                        <form action=\"<?= site_url('$c_url/index'); ?>\" class=\"form-inline float-right\" method=\"get\">
                            <div class=\"input-group mb-3\">
                                <input type=\"text\" class=\"form-control\" name=\"q\" value=\"<?= \$q; ?>\" placeholder=\"Cari Data\">
                              <div class=\"input-group-append\">
                                <?php if (\$q <> '') { ?>
                                    <a href=\"<?= site_url('" . $c_url . "') ?>\" class=\"btn btn-danger\">Reset</a>
                                <?php } ?>
                                <button class=\"btn btn-primary\" type=\"submit\"><i class=\"fas fa-search\"></i> Cari</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class=\"table-responsive p-0\">
                    <table class=\"table table-bordered table-hover text-nowrap\" id=\"mytable\">
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
$string .= "\n\t\t\t\t\t\t\t<?php \$no = 1; foreach ($" . $c_url . "_data as \$value) : ?>
                            <tr>";
$string .= "\n\t\t\t\t\t\t\t\t<td class=\"text-center\"><?= \$no++ ?></td>";
foreach ($non_pk as $row) {
    $string .= "\n\t\t\t\t\t\t\t\t<td><?= \$value->" . $row['column_name'] . " ?></td>";
}
$string .= "\n\t\t\t\t\t\t\t\t<td class=\"text-center\">
                                    <a href=\"<?= site_url('" . $c_url . "/read/'.\$value->" . $pk . ") ?>\" title=\"Lihat Detail Data\"class=\"btn btn-success\"><i class=\"fa fa-eye\"></i></a>
                                    <a href=\"<?= site_url('" . $c_url . "/update/'.\$value->" . $pk . ") ?>\" title=\"Ubah Data\" class=\"btn btn-warning\"><i class=\"fa fa-edit\"></i></a>
                                    <a href=\"<?= site_url('" . $c_url . "/delete/'.\$value->" . $pk . ") ?>\" title=\"Hapus Data\" class=\"btn btn-danger hapus\"><i class=\"fa fa-trash\"></i></a>
                                </td>
                            </tr>";
$string .=  "\n\t\t\t\t\t\t\t<?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div class=\"card-footer clearfix mt-2\">
                <h5>Jumlah Data : <?= \$total_rows ?></h5>
            </div>
        </div>
    </div>
</div>

<?= swal_delete(\"#mytable\",\".hapus\") ?>";

if ($export_excel == '1') {
    $string .= "\n<a href=\"<?= site_url('" . $c_url . "/excel') ?>\" class=\"btn btn-success\"><i class=\"fas fa-file-excel\"></i> Export Excel</a>";
}
if ($export_pdf == '1') {
    $string .= "\n<a href=\"<?= site_url('" . $c_url . "/pdf') ?>\" class=\"btn btn-danger\" target=\"_blank\"><i class=\"fas fa-file\"></i> Cetak PDF</a>";
}

$hasil_view_list = createFile($string, $target . "views/" . $c_url . "/" . $v_list_file);
