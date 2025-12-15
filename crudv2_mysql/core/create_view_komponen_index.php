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
                <div class=\"table-responsive mt-3\">
                <div class=\"mb-3\">
                        <input type=\"text\" wire:model.live.debounce.300ms=\"search\" placeholder=\"Pencarian...\" class=\"form-control\" />
                    </div>
                    <table class=\"table table-bordered table-striped\">
                        <thead class=\"bg-dark text-white\">
                            <tr>
                                ";
foreach ($non_pk as $row) {
    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
        if ($cek_field == 0) {
            $string .= "\n\t\t\t\t\t\t\t\t<th> <a href=\"#\" wire:click.prevent=\"sortBy('" . $row['column_name'] . "')\">" . label($row['column_name']) . "</a>
                                    @if (\$sortField === '" . $row['column_name'] . "')
                                    <span>{{ \$sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                    </th>";
        } else {
            if ($cek_field['field_relasi'] === "USER") {
                $cek_field['field_relasi'] = 'name';
            }
            $string .= "\n\t\t\t\t\t\t\t\t<th><a href=\"#\" wire:click.prevent=\"sortBy('" . $cek_field['field_relasi'] . "')\">" . label($cek_field['field_relasi']) . "</a>
                                    @if (\$sortField === '" . $cek_field['field_relasi'] . "')
                                    <span>{{ \$sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif</th>";
        }
    }
}
$string .= "
                               
                                <th style=\"width: 20%\">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (\$" . $nama_class . "_data as \$" . $nama_class . ")
                            <tr>
";
foreach ($non_pk as $row) {
    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
        if ($cek_field == 0) {
            $string .= "\n\t\t\t\t\t\t\t\t<td>{{\$" . $nama_class . "->" . $row['column_name'] . " }}</td>";
        } else {
            $string .= "\n\t\t\t\t\t\t\t\t
            <td>{{\$" . $nama_class . "->" . $cek_field['field_relasi'] . " }}</td>
            ";
        }
    }
}
$string .= "
                               
                                <td class=\"text-center\">
                                    <a href=\"{{ route('" . $nama_class . ".show', \$" . $nama_class . "->" . $pk . ") }}\" wire:navigate class=\"btn btn-sm btn-success\">Lihat</a>
                                    <a href=\"{{ route('" . $nama_class . ".edit', \$" . $nama_class . "->" . $pk . ") }}\" wire:navigate class=\"btn btn-sm btn-primary\">Edit</a>
                                    <button class=\"btn btn-sm btn-danger\" wire:click=\"confirmDelete({{ \$" . $nama_class . "->" . $pk . " }})\">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <div class=\"alert alert-danger\">
                                Data belum Tersedia.
                            </div>
                            @endforelse
                        </tbody>
                    </table>
                    {{ \$" . $nama_class . "_data->links() }}";


$col_non_pk = implode(',', $column_non_pk);
$string .= "
                   
                </div>
            </div>
        </div>
    </div>
</div>
@section('javascripts')
<script>
        Livewire.on('showDeleteConfirmation', (dataId) => {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus post ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna mengkonfirmasi, panggil fungsi Livewire untuk menghapus
                    //@this.call('destroy');
                    Livewire.dispatch('destroy', dataId);
                }
            });
        });
    </script>
@endsection
";

$hasil_view_index = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_index);
