<?php
$string = "
<div>
 <x-breadcrumbs :items=\"\$breadcrumbs\" />
    <x-header />

    <div class=\"grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6\">
        <div class=\"md:col-span-12\">
            <x-card title=\"Lihat ".label($m)."\" shadow separator>
                <div class=\"overflow-x-auto\">
                    <table class=\"w-full border border-gray-200 rounded-md text-gray-700 dark:text-gray-300\">
                        <tbody>";
                           
        foreach ($non_pk as $row) {
            $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
            if ($cek_field == 0) {
                $string .= "\n\t\t\t
                <tr class=\"border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors\">
                \n\t\t\t\t\t\t<th class=\"w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900\"><b>" . label($row["column_name"]) . "</b></th>
                \n\t\t\t\t\t\t\t
                <td class=\"px-4 py-3\">{{ \$$nama_class" . "_data->" . $row["column_name"] . " }}
                </td>
                \n\t\t\t\t\t</tr>";
            } else {
                $string .= "\n\t\t\t\t\t
               <tr class=\"border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors\">
                \n\t\t\t\t\t\t
                <th class=\"w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900\"><b>" . label($cek_field['field_relasi']) . "</b></th>\n\t\t\t\t\t\t\t
                 <td class=\"px-4 py-3\">{{ \$$nama_class" . "_data->" . $cek_field['field_relasi'] . " }}</td>\n\t\t\t\t\t</tr>";
            }
        }
        $string .= "
      </tr>
    </table>
     <x-slot:actions>
                    <x-button :href=\"route('".$nama_view_komponen.".index')\" wire:navigate class=\"btn-error text-white btn-sm end\"
                        icon=\"o-backspace\">Kembali</x-button>
                </x-slot:actions>
   </x-card>
  </div>
  </div>
  <x-back-refresh />
</div>";

$hasil_view_form = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_show);
