<?php
$column_non_pk = array();
foreach ($non_pk as $row) {
    $column_non_pk[] .= "\n\t\t\t\t\t\t\t\t\t{\n\t\t\t\t\t\t\t\t\t\t\"data\": \"" . $row['column_name'] . "\"\n\t\t\t\t\t\t\t\t\t}";
}
$col_non_pk = implode(',', $column_non_pk);
$string = "
@section('title','Data " . str_replace('_', ' ', ucwords($m)) . "')
<div class=\"row-xl\" style=\"padding:10px;\">
    <div class=\"col-md-12\">
        <div class=\"card shadow-sm mb-4\">
            <div class=\"card-header d-flex align-items-center justify-content-between\">
                <h5 class=\"mb-0\"> Data " . str_replace('_', ' ', ucwords($m)) . " </h5>
                
            </div>
            <div class=\"card-body\">
            <a href=\"{{route('" . $nama_class . ".create')}}\" wire:navigate class=\"btn btn-primary text-white\">Tambah Data</a>
                <div wire:ignore class=\"table-responsive mt-3\">
                    <table class=\"table table-bordered dt-responsive nowrap\" style=\"border-collapse: collapse; border-spacing: 0; width: 100%;\" id=\"table-$m\">
                        <thead>
                            <tr>
                                <th class=\"text-center\" width=\"5%\">No</th>";
foreach ($non_pk as $row) {
    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
        if ($cek_field == 0) {
            $string .= "\n\t\t\t\t\t\t\t\t<th>" . label($row['column_name']) . "</th>";
        } else {
            if ($cek_field['field_relasi'] === "USER") {
                $cek_field['field_relasi'] = 'name';
            }
            $string .= "\n\t\t\t\t\t\t\t\t<th>" . label($cek_field['field_relasi']) . "</th>";
        }
    }
}
$string .= "\n\t\t\t\t\t\t\t\t<th class=\"text-center\" width=\"15%\">Aksi</th>
                            </tr>
                        </thead>";
$column_non_pk = array();
foreach ($non_pk as $row) {
    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
        if ($cek_field == 0) {
            $column_non_pk[] .= "\n\t\t\t\t{\n\t\t\t\t\t\"data\": \"" . $row['column_name'] . "\"\n\t\t\t\t}";
        } else {
            if ($cek_field['field_relasi'] === "USER") {
                $cek_field['field_relasi'] = 'name';
            }
            $column_non_pk[] .= "\n\t\t\t\t{\n\t\t\t\t\t\"data\": \"" . $cek_field['field_relasi'] . "\"\n\t\t\t\t}";
        }
    }
}
$col_non_pk = implode(',', $column_non_pk);
$string .= "
                    </table>


                </div>
            </div>
        </div>
    </div>
</div>
@section('javascripts')
<script>
        var table = $(\"#table-$m\").DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: \"{{ route('$nama_class.data') }}\",
            columns: [
                {
                    \"data\": \"DT_RowIndex\",
                    \"searchable\": false,
                    \"sortable\": false
                },
                " . $col_non_pk . ",
                {
                    \"data\" : \"action\",
                    \"orderable\": false,
                    \"className\" : \"text-center\"
                },

            ],
        });
         Livewire.on('showDeleteConfirmation', (id) => {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('destroy');
                }
            });
        });

        Livewire.on('dataDeleted', () => {
            table.ajax.reload(null, false);
        });
    </script>
@endsection
";

$hasil_view_index = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_index);
