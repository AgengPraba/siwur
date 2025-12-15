<?php
$string = "
@section('title','Data " . str_replace('_', ' ', ucwords($m)) . "')
<div>
<x-notif></x-notif>
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href=\"route('home')\">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>".label($m)."</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <flux:heading class=\"mt-2\" size=\"xl\">Data ".label($m)."</flux:heading>
    <div class=\"mt-4 flex flex-col gap-2 md:flex-row md:justify-between\">
        <flux:button :href=\"route('" . $nama_class . ".create')\" wire:navigate>Tambah ".label($m)."</flux:button>

        <div class=\"w-full md:max-w-60\">
            <flux:input wire:model.live.debounce.500ms=\"search\" icon=\"magnifying-glass\" autocomplete=\"off\" placeholder=\"Cari ".label($m)."\" />
        </div>
    </div>
    <div class=\"mt-6 gap-4 lg:grid-cols-2 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6\">
        <div class=\"overflow-x-auto\">
            <table class=\"min-w-200 mt-6 table w-full\">
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
                                        <div class=\"flex cursor-pointer items-center gap-2\">
                                            <span>" . label($row['column_name']) . "</span>
                                            @if (\$sortField === '" . $row['column_name'] . "')
                                                @if (\$sortDirection === 'asc')
                                                    <flux:icon.chevron-up class=\"size-4\" />
                                                @else
                                                    <flux:icon.chevron-down class=\"size-4\" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>";
                                } else {
                                   
                                    $string .= "\n\t\t\t\t\t\t\t\t
                                    <th class=\"p-2\" wire:click=\"sortBy('" . $cek_field['field_relasi'] . "')\">
                                        <div class=\"flex cursor-pointer items-center gap-2\">
                                            <span>" . label($cek_field['field_relasi']) . "</span>
                                            @if (\$sortField === '" . $cek_field['field_relasi'] . "')
                                                @if (\$sortDirection === 'asc')
                                                    <flux:icon.chevron-up class=\"size-4\" />
                                                @else
                                                    <flux:icon.chevron-down class=\"size-4\" />
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
                                <td class=\"p-2 text-center\">{{ \$loop->iteration }}</td>
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
                                    <flux:button size=\"sm\" :href=\"route('".$nama_class.".show', \$".$nama_class."->id)\"
                                        wire:navigate>
                                        Lihat
                                    </flux:button>
                                    <flux:button size=\"sm\" variant=\"primary\"
                                        :href=\"route('".$nama_class.".edit', \$".$nama_class."->id)\" wire:navigate>
                                        Edit
                                    </flux:button>
                                    
                                    <flux:button size=\"sm\" variant=\"danger\"
                                        x-on:click=\"\$wire.idToDelete = '{{ \$".$nama_class."->id }}'\">
                                        Hapus
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class=\"p-4\" colspan=\"5\">
                                <div class=\"flex flex-col items-center justify-center gap-2 text-center\">
                                    <flux:icon name=\"document\" />
                                    <span class=\"text-sm\">Tidak Ada Data</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class=\"mt-2\">
                 {{ \$" . $nama_class . "_data->links() }}";
                  $string .="
            </div>
        </div>
    </div>
    <div class=\"fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 bg-opacity-50\"
        x-show=\"\$wire.idToDelete\" x-cloak x-transition>
        <div class=\"container max-w-screen-sm px-4\" x-on:click.outside=\"\$wire.idToDelete = null\">
            <div class=\"rounded-lg bg-white p-4 shadow-lg dark:bg-zinc-800\">
                <div class=\"text-lg font-semibold\">Delete ".label($m)."</div>
                <div class=\"mt-4 text-sm\">Apakah Anda Yakin Menghapus Data ini?</div>
                <div class=\"mt-4 flex justify-end gap-2\">
                    <flux:button wire:click=\"destroy\" variant=\"danger\">Hapus</flux:button>
                    <flux:button x-on:click=\"\$wire.idToDelete = null\">Batal</flux:button>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh></x-back-refresh>
</div>";

$hasil_view_index = createFile($string, "../resources/views/livewire/" . $nama_view_komponen . "/" . $v_komponen_index);
