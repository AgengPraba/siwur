<?php

$string = "
@extends('layouts.app')
@section('title','Lihat ".str_replace('_',' ',ucwords($m))."')
@section('content')
<div class=\"row\">
	<div class=\"col-md-12\">
		<div class=\"card\">
			<h5 class=\"card-header bg-transparent border-bottom mt-0\"> Detail Data " . str_replace('_', ' ', ucwords($nama_class)) . " </h5>
			<div class=\"card-body\">
				<table class=\"table table-striped\">";
foreach ($non_pk as $row) {
	$string .= "\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td width=\"20%\"><b>" . label($row["column_name"]) . "</b></td>\n\t\t\t\t\t\t\t<td>{{ \$$nama_class"."_data->" . $row["column_name"] ." }}</td>\n\t\t\t\t\t</tr>";
}
$string .= "\n\t\t\t\t</table>\n\t\t\t\t<hr><a href=\"{{route('" . $nama_class . ".index')}}\" class=\"btn btn-danger float-right\">\n\t\t\t\t\t\t<i class=\"fas fa-sign-out-alt\"></i> Kembali\n\t\t\t\t</a>
			</div>
		</div>
	</div>
</div>
@endsection";

$hasil_view_show = createFile($string, $target."/" .$modul_name."/Resources/views/"  . $nama_class . "/" . $v_show_file);