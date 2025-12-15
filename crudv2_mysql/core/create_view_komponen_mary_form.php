<?php
$string = "
<div>
<x-breadcrumbs :items=\"\$breadcrumbs\" />
    <x-header />
<div class=\"grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6\">
        <div class=\"md:col-span-12\">
         <x-card title=\"Form {{ \$type == 'create' ? 'Tambah' : 'Edit' }} " . str_replace('_', ' ', ucwords($table_name)) . "\" subtitle=\"Isikan Data " . str_replace('_', ' ', ucwords($table_name)) . " di bawah ini\" shadow separator>
        <x-form wire:submit.prevent=\"{{ \$type == 'create' ? 'store' : 'update' }}\" no-separator class=\"gap-2\">
                ";
            foreach ($non_pk as $row) {
                if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
                } else {
                    if ($row["data_type"] == 'text' or $row["data_type"] == 'longtext' or $row["data_type"] == 'mediumtext') {
                        $string .= "
                           <x-textarea wire:model=\"" . $row["column_name"] . "\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        ";
                    } else if ($row['data_type'] == 'date') {
                        $string .= "
                           <x-input wire:model=\"" . $row["column_name"] . "\" type=\"date\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        ";
                    } else {
                        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
                        if ($cek_field == 0) {
                            $string .= "
                           <x-input wire:model=\"" . $row["column_name"] . "\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        ";
                        } else {
                            $string .= "
                            <x-select wire:model=\"" . $row["column_name"] . "\" label=\"" . label($cek_field['field_relasi']) . "\" :options=\"\$" . $cek_field['referenced_table'] . "_data\" placeholder=\"Pilih ". label($cek_field['field_relasi']) ."\" />";
                        }
                    }
                }
            }
            $string .= "
             <x-slot:actions>
                        <x-button icon=\"o-check\" label=\"Simpan\" type=\"submit\" class=\"btn-primary\" spinner />
                        <x-button icon=\"o-backspace\" :href=\"route('".$nama_view_komponen.".index')\" label=\"Kembali\" class=\"btn-error text-white\" wire:navigate />
            </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
    <x-back-refresh />
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_form);
