<?php
$string = "
@extends('layouts.app')
@section('title',\$button.' " . str_replace('_', ' ', ucwords($m)) . "')
@section('content')
<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"card shadow-sm mb-4\">
            <h5 class=\"card-header\">  {{\$button}} Data " . str_replace('_', ' ', ucwords($m)) . " </h5>
            <div class=\"card-body\">
            <form action=\"{{\$action}}\" method=\"post\" style=\"padding:10px;\">
            {{ csrf_field() }}
            @if (\$button == 'Edit'){{ method_field('PUT') }}@endif
            ";

foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        if ($row["data_type"] == 'text' or $row["data_type"] == 'longtext' or $row["data_type"] == 'mediumtext') {
            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2  col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-6\">
                            <textarea class=\"form-control\" rows=\"3\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\">@if (\$button == 'Tambah'){{ old('" . $row["column_name"] . "') }}@else{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}@endif</textarea>
                            @if (\$errors->has('" . $row["column_name"] . "'))
                                <div class=\"text-danger\">
                                    {{ \$errors->first('" . $row["column_name"] . "') }}
                                </div>
                            @endif

                        </div>
                    </div>";
        } else if ($row['data_type'] == 'date') {
            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-4\">
                            <input type=\"date\" class=\"form-control\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" value=\"@if (\$button == 'Tambah'){{ old('" . $row["column_name"] . "') }}@else{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}@endif\" />
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"text-danger\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif
                        </div>
                    </div>";
        } else {
            $cek_field = $hc->cek_field_relation($table_murni,$row['column_name']);
        if ($cek_field == 0) {

            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-6\">
                            <input type=\"text\" class=\"form-control\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" value=\"@if (\$button == 'Tambah'){{ old('" . $row["column_name"] . "') }}@else{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}@endif\" />
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"text-danger\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif

                        </div>
                    </div>";
        }else{
            $string .= "\n\t\t\t\t\t<div class=\"row mb-3\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["column_name"] . "\">" . label($cek_field['field_relasi']) . "</label>
                        <div class=\"col-md-6\">
                        <select class=\"form-control select2\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" required>
                            @foreach(\$" . $cek_field["referenced_table"] . "_data as \$item)
                            <option value=\"{{ \$item->".$cek_field['referenced_column']." }}\"
                            @if (\$button == 'Edit')
                                @if (\$item->".$cek_field['referenced_column']." == \$$nama_class" . "_data->" . $row["column_name"] . ")
                                    @selected(true)
                                @endif
                            @endif >{{ \$item->".$cek_field['field_relasi']." }}</option>
                            @endforeach
                        </select>
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"text-danger\">
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

                            <button type=\"submit\" class=\"btn btn-primary text-white\"><?= \$button ?></button>
                            <a href=\"{{route('" . $nama_class . ".index')}}\" class=\"btn btn-danger text-white\">Kembali</a>
                        </div>
                    </div>";
$string .= "\n\t\t\t\t</form>
            </div>
        </div>
    </div>
</div>
@endsection";

$hasil_view_form = createFile($string, "../resources/views/" . $nama_class . "/" . $v_form_file);
