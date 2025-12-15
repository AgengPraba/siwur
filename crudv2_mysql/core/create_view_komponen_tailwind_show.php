<?php
$string = "
@section('title','Lihat " . str_replace('_', ' ', ucwords($m)) . "')
<div>

<x-notif></x-notif>
  <flux:breadcrumbs>
        <flux:breadcrumbs.item :href=\"route('home')\">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href=\"route('".$nama_class.".index')\">".label($m)."</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Lihat</flux:breadcrumbs.item>
    </flux:breadcrumbs>

  <flux:heading class=\"mt-2\" size=\"xl\">Lihat ".label($m)."</flux:heading>
    <div class=\"mt-6 gap-4 lg:grid-cols-2 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6\">
  <div class=\"overflow-x-auto\">
    <table class=\"w-full\">";
        foreach ($non_pk as $row) {
            $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
            if ($cek_field == 0) {
                $string .= "\n\t\t\t<tr class=\"border-b border-zinc-200 dark:border-zinc-700\">
                \n\t\t\t\t\t\t<th class=\"w-40 p-2 text-start\"><b>" . label($row["column_name"]) . "</b></th>
                \n\t\t\t\t\t\t\t
                <td class=\"p-2 text-start\">{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}
                </td>
                \n\t\t\t\t\t</tr>";
            } else {
                $string .= "\n\t\t\t\t\t
                <tr class=\"border-b border-zinc-200 dark:border-zinc-700\">
                \n\t\t\t\t\t\t
                <th class=\"w-40 p-2 text-start\"><b>" . label($cek_field['field_relasi']) . "</b></th>\n\t\t\t\t\t\t\t
                 <td class=\"p-2 text-start\">{{ \$$nama_class" . "_data->" . $cek_field['field_relasi'] . " }}</td>\n\t\t\t\t\t</tr>";
            }
        }
        $string .= "
      </tr>
    </table>
    <div class=\"mt-4\">
   <flux:button :href=\"route('".$nama_class.".index')\" wire:navigate variant=\"danger\">Kembali</flux:button>
  </div>
  </div>
  </div>
  
  <x-back-refresh></x-back-refresh>
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_show);
