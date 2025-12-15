<?php
$string = "
@section('title','Tambah " . str_replace('_', ' ', ucwords($m)) . "')
<div class=\"row-xl\" style=\"padding:10px;\">
    <div class=\"col-md-12\">
        <div class=\"card shadow-sm mb-4\">
            <h5 class=\"card-header\">  Tambah Data " . str_replace('_', ' ', ucwords($m)) . " </h5>
            <div class=\"card-body\">
            <form wire:submit.prevent=\"store\" enctype=\"multipart/form-data\">";
foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        if ($row["data_type"] == 'text' or $row["data_type"] == 'longtext' or $row["data_type"] == 'mediumtext') {
            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2  col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-6\">
                            <textarea class=\"form-control @error('" . $row["column_name"] . "') is-invalid @enderror\" rows=\"3\" wire:model=\"" . $row["column_name"] . "\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\"></textarea>
                            @if (\$errors->has('" . $row["column_name"] . "'))
                                <div class=\"alert alert-danger mt-2\">
                                    {{ \$errors->first('" . $row["column_name"] . "') }}
                                </div>
                            @endif

                        </div>
                    </div>";
        } else if ($row['data_type'] == 'date') {
            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-4\">
                            <input type=\"date\" class=\"form-control @error('" . $row["column_name"] . "') is-invalid @enderror\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" wire:model=\"" . $row["column_name"] . "\" />
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"alert alert-danger mt-2\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif
                        </div>
                    </div>";
        } else {
            $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
            if ($cek_field == 0) {

                $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-6\">
                           <input type=\"text\" class=\"form-control @error('" . $row["column_name"] . "') is-invalid @enderror\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" wire:model=\"" . $row["column_name"] . "\" />
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"alert alert-danger mt-2\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif

                        </div>
                    </div>";
            } else {
                if ($cek_field['field_relasi'] === "USER") {
                    $cek_field['field_relasi'] = 'name';
                }
                $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($cek_field['field_relasi']) . "</label>
                        <div class=\"col-md-6\">
                        <select class=\"form-control select2 @error('" . $row["column_name"] . "') is-invalid @enderror\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" wire:model=\"" . $row["column_name"] . "\">
                            <option value=''>-- Pilih ". $cek_field['field_relasi'] ."--</option>
                            @foreach(\$" . $cek_field["referenced_table"] . "_data as \$item)
                            <option value=\"{{ \$item->" . $cek_field['referenced_column'] . " }}\"
                            >{{ \$item->" . $cek_field['field_relasi'] . " }}</option>
                            @endforeach
                        </select>
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"alert alert-danger mt-2\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif

                        </div>
                    </div>";
            }
        }
    }
}

$string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <div class=\"col-md-6 offset-md-2\">

                            <button type=\"submit\" class=\"btn btn-primary text-white\">Simpan</button>
                            <a href=\"{{route('" . $nama_class . ".index')}}\" wire:navigate class=\"btn btn-danger text-white\">Kembali</a>
                        </div>
                    </div>";
$string .= "\n\t\t\t\t</form>
            </div>
        </div>
    </div>
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_create);
