<?php
$string = "
@extends('layouts.app')
@section('title',\$button.' " . str_replace('_', ' ', ucwords($m)) . "')
@section('content')
<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"card\">
            <h5 class=\"card-header bg-transparent border-bottom mt-0\">  {{\$button}} Data " . str_replace('_', ' ', ucwords($m)) . " </h5>
            <div class=\"card-body\">
            <form action=\"{{\$action}}\" method=\"post\" style=\"padding:10px;\">
            {{ csrf_field() }}
            @if (\$button == 'Edit'){{ method_field('PUT') }}@endif
            ";

foreach ($non_pk as $row) {
    if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
    } else {
        if ($row["data_type"] == 'text' or $row["data_type"] == 'longtext' or $row["data_type"] == 'mediumtext') {
            $string .= "\n\t\t\t\t\t<div class=\"form-group row\">
                        <label class=\"col-md-2 col-form-label\" for=\"" . $row["data_type"] . "\">" . label($row["column_name"]) . "</label>
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
            $string .= "\n\t\t\t\t\t<div class=\"form-group row\">
                        <label class=\"col-md-2\" for=\"" . $row["data_type"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-4\">
                            <div class=\"input-group\">
                                <div class=\"input-group-prepend\">
                                    <span class=\"input-group-text\"><i class=\"fas fa-calendar-alt\"></i></span>
                                </div>
                                <input type=\"text\" class=\"form-control datepicker\" data-language=\"en\" data-date-format=\"yyyy-mm-dd\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" value=\"@if (\$button == 'Tambah'){{ old('" . $row["column_name"] . "') }}@else{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}@endif\" readonly />
                                @if (\$errors->has('" . $row["column_name"] . "'))
                                    <div class=\"text-danger\">
                                        {{ \$errors->first('" . $row["column_name"] . "') }}
                                    </div>
                                @endif
                           
                            </div>
                        </div>
                    </div>";
        } else {
            $string .= "\n\t\t\t\t\t<div class=\"form-group row\">
                        <label class=\"col-md-2\" for=\"" . $row["data_type"] . "\">" . label($row["column_name"]) . "</label>
                        <div class=\"col-md-6\">
                            <input type=\"text\" class=\"form-control\" name=\"" . $row["column_name"] . "\" id=\"" . $row["column_name"] . "\" placeholder=\"" . label($row["column_name"]) . "\" value=\"@if (\$button == 'Tambah'){{ old('" . $row["column_name"] . "') }}@else{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}@endif\" />
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

$string .= "\n\t\t\t\t\t<div class=\"form-group row\">
                        <div class=\"col-md-6 offset-md-2\">
                          
                            <button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save\"></i> <?= \$button ?></button>
                            <a href=\"{{route('" . $nama_class . ".index')}}\" class=\"btn btn-danger\"><i class=\"fas fa-sign-out-alt\"></i> Kembali</a>
                        </div>
                    </div>";
$string .= "\n\t\t\t\t</form>
            </div>
        </div>
    </div>
</div>
@endsection";

$hasil_view_form = createFile($string, $target."/" .$modul_name."/Resources/views/" . $nama_class . "/" . $v_form_file);
