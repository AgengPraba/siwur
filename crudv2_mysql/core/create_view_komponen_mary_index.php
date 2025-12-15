<?php
$string = "
<div>
    <x-breadcrumbs :items=\"\$breadcrumbs\"/>
    <x-header />
    <div class=\"grid grid-cols-1 md:grid-cols-12 gap-4 pb-4\">
        <div class=\"md:col-span-12\">
            <x-card title=\"List " . str_replace('_', ' ', ucwords($table_name)) . "\" shadow separator>
                <div class=\"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4\">
                <x-button label=\"Tambah " . str_replace('_', ' ', ucwords($table_name)) . "\" link=\"{{ route('".$nama_view_komponen.".create') }}\" wire:navigate
                        icon=\"o-plus\" class=\"btn-primary\" />

                    <div class=\"w-full md:w-60\">
                        <x-input wire:model.live.debounce.500ms=\"search\" autocomplete=\"off\"
                            placeholder=\"Cari " . str_replace('_', ' ', ucwords($table_name)) . "\" />
                    </div>
                </div>
            
       <div class=\"overflow-x-auto\">
                    <table class=\"mt-6 w-full min-w-[600px]\">
                        <thead>
                    <tr class=\"text-sm\">
                        <th class=\"w-12 p-2\" wire:click=\"sortBy(null)\">#</th>
                        
                        ";
                        foreach ($non_pk as $row) {
                            if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
                                $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
                                if ($cek_field == 0) {
                                    $string .= "\n\t\t\t\t\t
                                    <th class=\"p-2\" wire:click=\"sortBy('" . $row['column_name'] . "')\">
                                        <div class=\"p2 cursor-pointer items-center gap-2 select-none\">
                                            <span>" . label($row['column_name']) . "</span>
                                            @if (\$sortField === '" . $row['column_name'] . "')
                                                @if (\$sortDirection === 'asc')
                                                   <x-icon name=\"o-chevron-up\" class=\"size-4\" />
                                                @else
                                                    <x-icon name=\"o-chevron-down\" class=\"size-4\" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>";
                                } else {
                                   
                                    $string .= "\n\t\t\t\t\t\t\t\t
                                    <th class=\"p-2\" wire:click=\"sortBy('" . $cek_field['field_relasi'] . "')\">
                                        <div class=\"p2 cursor-pointer items-center gap-2 select-none\">
                                            <span>" . label($cek_field['field_relasi']) . "</span>
                                            @if (\$sortField === '" . $cek_field['field_relasi'] . "')
                                                @if (\$sortDirection === 'asc')
                                                   <x-icon name=\"o-chevron-up\" class=\"size-4\" />
                                                @else
                                                    <x-icon name=\"o-chevron-down\" class=\"size-4\" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>";
                                }
                            }
                        }
                        $string .= "
                        <th class=\"w-40 p-2\">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @if (!\$" . $nama_class . "_data->isEmpty())
                        @foreach (\$" . $nama_class . "_data as \$".$nama_class.")
                            <tr class=\"border-t border-zinc-200 dark:border-zinc-700\">
                               <td class=\"p-2 text-center align-middle\">{{ \$start + \$loop->index }}</td>
                                ";
                                foreach ($non_pk as $row) {
                                    if ($row['column_name'] != 'created_at' and $row['column_name'] != 'updated_at') {
                                        $cek_field = $hc->cek_field_relation($table_murni, $row['column_name']);
                                        if ($cek_field == 0) {
                                            $string .= "\n\t\t\t\t\t\t\t\t<td class=\"p-2\">{{\$" . $nama_class . "->" . $row['column_name'] . " }}</td>";
                                        } else {
                                           
                                            $string .= "\n\t\t\t\t
                                            <td class=\"p-2\">{{\$" . $nama_class . "->" . $cek_field['field_relasi'] . " }}</td>
                                            ";
                                        }
                                    }
                                }

                                $string .="
                                <td class=\"flex justify-center gap-2 p-2\">
                                    <x-button icon=\"o-eye\" class=\"btn-success btn-sm text-white\" :href=\"route('".$nama_view_komponen.".show', \$".$nama_class."->id)\"
                                        wire:navigate>
                                        Lihat
                                    </x:button>
                                    <x-button icon=\"o-pencil\" class=\"btn-info btn-sm text-white\"
                                        :href=\"route('".$nama_view_komponen.".edit', \$".$nama_class."->id)\" wire:navigate>
                                        Edit
                                    </x:button>
                                    
                                    <x-button icon=\"o-trash\" class=\"btn-error btn-sm text-white\"
                                        x-on:click=\"\$wire.idToDelete = '{{ \$".$nama_class."->id }}'\">
                                        Hapus
                                    </x:button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                       <tr>
                                    <td class=\"p-4 text-center\" colspan=\"4\">
                                        <div
                                            class=\"flex flex-col items-center justify-center gap-2 text-center text-sm text-zinc-500 dark:text-zinc-400\">
                                            <x-icon name=\"o-document\" />
                                            Tidak Ada Data
                                        </div>
                                    </td>
                                </tr>
                    @endif
                </tbody>
            </table>

            <x-pagination :rows=\"\$" . $nama_class . "_data\" wire:model.live=\"perPage\" />
        </div>
    </div>
    </x-card>
    <div class=\"fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 bg-opacity-50\"
        x-show=\"\$wire.idToDelete\" x-cloak x-transition>
        <div class=\"container max-w-screen-sm px-4\" x-on:click.outside=\"\$wire.idToDelete = null\">
            <div class=\"rounded-lg bg-white p-4 shadow-lg dark:bg-zinc-800\">
                <div class=\"text-lg font-semibold\">Delete ".label($m)."</div>
                <div class=\"mt-4 text-sm\">Apakah Anda Yakin Menghapus Data ini?</div>
                <div class=\"mt-4 flex justify-end gap-2\">
                    <x-button icon=\"o-trash\" responsive class=\"btn-error btn-sm text-white\" wire:click=\"destroy\">Hapus</x-button>
                    <x-button icon=\"o-backspace\" responsive class=\"btn-sm\"
                        x-on:click=\"\$wire.idToDelete = null\">Batal</x-button>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh />
</div>";

$hasil_view_index = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_index);
