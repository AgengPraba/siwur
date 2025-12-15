<?php
$column_non_pk = array();
foreach ($non_pk as $row) {
    $column_non_pk[] .= "\n\t\t\t\t\t\t\t\t\t{\n\t\t\t\t\t\t\t\t\t\t\"data\": \"" . $row['column_name'] . "\"\n\t\t\t\t\t\t\t\t\t}";
}
$col_non_pk = implode(',', $column_non_pk);
$string = "
@extends('layouts.app')
@section('title','Data ".str_replace('_',' ',ucwords($m))."')
@section('content')
<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"card\">
            <div class=\"card-header d-flex align-items-center justify-content-between\">
                <h5 class=\"mb-0\"> Data " . str_replace('_', ' ', ucwords($m)) . " </h5>
                <a href=\"{{route('" . $nama_class . ".create')}}\" class=\"btn btn-primary text-white float-end\">Tambah Data</a>
            </div>
            <div class=\"card-body\">
                <div class=\"table-responsive mt-3\">
                    <table class=\"table table-bordered dt-responsive nowrap\" style=\"border-collapse: collapse; border-spacing: 0; width: 100%;\" id=\"table-$m\">
                        <thead>
                            <tr>
                                <th class=\"text-center\" width=\"5%\">No</th>";
foreach ($non_pk as $row) {
    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
        $cek_field = $hc->cek_field_relation($table_murni,$row['column_name']);
        if ($cek_field == 0) {
            $string .= "\n\t\t\t\t\t\t\t\t<th>" . label($row['column_name']) . "</th>";
        }else{
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
        $cek_field = $hc->cek_field_relation($table_murni,$row['column_name']);
        if ($cek_field == 0) {
            $column_non_pk[] .= "\n\t\t\t\t{\n\t\t\t\t\t\"data\": \"" . $row['column_name'] . "\"\n\t\t\t\t}";
        }else{
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
@endsection
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
        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: \"DELETE\",
                        url:  \"{{ route('$nama_class.delete') }}\",
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(data) {
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
@endsection
";

$hasil_view_list = createFile($string, "../resources/views/" . $nama_class . "/" . $v_list_file);
