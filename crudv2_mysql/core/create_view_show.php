<?php

$string = "
@extends('layouts.app')
@section('title','Lihat ".str_replace('_',' ',ucwords($m))."')
@section('content')
<div class=\"row\">
	<div class=\"col-md-12\">
		<div class=\"card shadow-sm mb-4\">
			<h5 class=\"card-header\"> Detail Data " . str_replace('_', ' ', ucwords($nama_class)) . " </h5>
			<div class=\"card-body\">
				<table class=\"table\">";
foreach ($non_pk as $row) {
    $cek_field = $hc->cek_field_relation($table_murni,$row['column_name']);
        if ($cek_field == 0) {
            $string .= "\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td width=\"20%\"><b>" . label($row["column_name"]) . "</b></td>\n\t\t\t\t\t\t\t<td>{{ \$$nama_class"."_data->" . $row["column_name"] ." }}</td>\n\t\t\t\t\t</tr>";
        }else{
            $string .= "\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td width=\"20%\"><b>" . label($cek_field['field_relasi']) . "</b></td>\n\t\t\t\t\t\t\t<td>{{ \$$nama_class"."_data->" . $cek_field['field_relasi'] ." }}</td>\n\t\t\t\t\t</tr>";
        }

}
$string .= "\n\t\t\t\t</table>\n\t\t\t\t<br><a href=\"{{route('" . $nama_class . ".index')}}\" class=\"btn btn-danger float-end text-white\">\n\t\t\t\t\t\t Kembali\n\t\t\t\t</a>
			</div>
		</div>
	</div>
</div>
@endsection";

$hasil_view_show = createFile($string, "../resources/views/" . $nama_class . "/" . $v_show_file);
