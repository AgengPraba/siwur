<?php
$string = "
@section('title', ucwords(\$type == 'create' ? 'Tambah' : 'Edit'). ' ". str_replace('_', ' ', ucwords($m)) . "')
<div>
<x-notif></x-notif>
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href=\"route('home')\">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href=\"route('".$nama_class.".index')\">".label($m)."</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ \$type == 'create' ? 'Tambah' : 'Edit' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <flux:heading class=\"mt-2\" size=\"xl\">{{ \$type == 'create' ? 'Tambah' : 'Edit' }} ".label($m)."</flux:heading>
    <div class=\"grid gap-4 md:grid-cols-1\">
        <div class=\"mt-6 gap-4 lg:grid-cols-2 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6\">";
            foreach ($non_pk as $row) {
                if ($row['column_name'] == 'created_at' || $row['column_name'] == 'updated_at' || $row['column_name'] == 'deleted_at') {
                } else {
                    if ($row["data_type"] == 'text' or $row["data_type"] == 'longtext' or $row["data_type"] == 'mediumtext') {
                        $string .= "
                         <div class=\"mt-6 grid gap-4\">
                           <flux:textarea wire:model=\"" . $row["column_name"] . "\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        </div>";
                    } else if ($row['data_type'] == 'date') {
                        $string .= "
                        <div class=\"mt-6 grid gap-4\">
                           <flux:input wire:model=\"" . $row["column_name"] . "\" type=\"date\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        </div>
                        ";
                    } else {
                        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
                        if ($cek_field == 0) {
            
                            $string .= "
                            
                        <div class=\"mt-6 grid gap-4\">
                           <flux:input wire:model=\"" . $row["column_name"] . "\" label=\"" . label($row["column_name"]) . "\" placeholder=\"" . label($row["column_name"]) . "\" />
                        </div>
                        ";
                        } else {
                            $string .= "
                            <div class=\"mt-6 grid gap-4\">
                            <flux:select wire:model=\"" . $row["column_name"] . "\" label=\"" . label($cek_field['field_relasi']) . "\">
                                <flux:select.option value=\"\">Pilih " . label($cek_field['field_relasi']) . "...</flux:select.option>
                                @foreach(\$" . $cek_field["referenced_table"] . "_data as \$item)
                                    <flux:select.option value=\"{{ \$item->" . $cek_field['referenced_column'] . " }}\">
                                    {{ \$item->" . $cek_field['field_relasi'] . " }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            </div>";
                        }
                    }
                }
            }

            $string .= "<div class=\"mt-4 flex gap-2\">
                
                @if (\$type == 'create')
                    <flux:button wire:click=\"store\" variant=\"primary\">Simpan</flux:button>
                    @else
                    <flux:button wire:click=\"update\" variant=\"primary\">Simpan</flux:button>
                @endif
                <flux:button :href=\"route('".$nama_class.".index')\" wire:navigate variant=\"danger\">Kembali</flux:button>
            </div>
        </div>
    </div>
    <x-back-refresh></x-back-refresh>
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_form);
