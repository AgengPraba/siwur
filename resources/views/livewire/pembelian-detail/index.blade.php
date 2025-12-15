
<div>
    <x-breadcrumbs :items="$breadcrumbs"/>
    <x-header />
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4">
        <div class="md:col-span-12">
            <x-card title="List Pembelian detail" shadow separator>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <x-button label="Tambah Pembelian detail" link="{{ route('pembelian-detail.create') }}" wire:navigate
                        icon="o-plus" class="btn-primary" />

                    <div class="w-full md:w-60">
                        <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                            placeholder="Cari Pembelian detail" />
                    </div>
                </div>
            
       <div class="overflow-x-auto">
                    <table class="mt-6 w-full min-w-[600px]">
                        <thead>
                    <tr class="text-sm">
                        <th class="w-12 p-2" wire:click="sortBy(null)">#</th>
                        
                        
								
                                    <th class="p-2" wire:click="sortBy('nomor_pembelian')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Nomor Pembelian</span>
                                            @if ($sortField === 'nomor_pembelian')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
								
                                    <th class="p-2" wire:click="sortBy('nama_barang')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Nama Barang</span>
                                            @if ($sortField === 'nama_barang')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
								
                                    <th class="p-2" wire:click="sortBy('nama_satuan')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Nama Satuan</span>
                                            @if ($sortField === 'nama_satuan')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
					
                                    <th class="p-2" wire:click="sortBy('harga_satuan')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Harga Satuan</span>
                                            @if ($sortField === 'harga_satuan')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
					
                                    <th class="p-2" wire:click="sortBy('jumlah')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Jumlah</span>
                                            @if ($sortField === 'jumlah')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
					
                                    <th class="p-2" wire:click="sortBy('konversi_satuan_terkecil')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Konversi Satuan Terkecil</span>
                                            @if ($sortField === 'konversi_satuan_terkecil')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
					
                                    <th class="p-2" wire:click="sortBy('subtotal')">
                                        <div class="p2 cursor-pointer items-center gap-2 select-none">
                                            <span>Subtotal</span>
                                            @if ($sortField === 'subtotal')
                                                @if ($sortDirection === 'asc')
                                                   <x-icon name="o-chevron-up" class="size-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="size-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
                        <th class="w-40 p-2">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @if (!$pembelian_detail_data->isEmpty())
                        @foreach ($pembelian_detail_data as $pembelian_detail)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                               <td class="p-2 text-center align-middle">{{ $start + $loop->index }}</td>
                                
				
                                            <td class="p-2">{{$pembelian_detail->nomor_pembelian }}</td>
                                            
				
                                            <td class="p-2">{{$pembelian_detail->nama_barang }}</td>
                                            
				
                                            <td class="p-2">{{$pembelian_detail->nama_satuan }}</td>
                                            
								<td class="p-2">{{$pembelian_detail->harga_satuan }}</td>
								<td class="p-2">{{$pembelian_detail->jumlah }}</td>
								<td class="p-2">{{$pembelian_detail->konversi_satuan_terkecil }}</td>
								<td class="p-2">{{$pembelian_detail->subtotal }}</td>
                                <td class="flex justify-center gap-2 p-2">
                                    <x-button icon="o-eye" class="btn-success btn-sm text-white" :href="route('pembelian-detail.show', $pembelian_detail->id)"
                                        wire:navigate>
                                        Lihat
                                    </x:button>
                                    <x-button icon="o-pencil" class="btn-info btn-sm text-white"
                                        :href="route('pembelian-detail.edit', $pembelian_detail->id)" wire:navigate>
                                        Edit
                                    </x:button>
                                    
                                    <x-button icon="o-trash" class="btn-error btn-sm text-white"
                                        x-on:click="$wire.idToDelete = '{{ $pembelian_detail->id }}'">
                                        Hapus
                                    </x:button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                       <tr>
                                    <td class="p-4 text-center" colspan="4">
                                        <div
                                            class="flex flex-col items-center justify-center gap-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            <x-icon name="o-document" />
                                            Tidak Ada Data
                                        </div>
                                    </td>
                                </tr>
                    @endif
                </tbody>
            </table>

            <x-pagination :rows="$pembelian_detail_data" wire:model.live="perPage" />
        </div>
    </div>
    </x-card>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 bg-opacity-50"
        x-show="$wire.idToDelete" x-cloak x-transition>
        <div class="container max-w-screen-sm px-4" x-on:click.outside="$wire.idToDelete = null">
            <div class="rounded-lg bg-white p-4 shadow-lg dark:bg-zinc-800">
                <div class="text-lg font-semibold">Delete PembelianDetail</div>
                <div class="mt-4 text-sm">Apakah Anda Yakin Menghapus Data ini?</div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button icon="o-trash" responsive class="btn-error btn-sm text-white" wire:click="destroy">Hapus</x-button>
                    <x-button icon="o-backspace" responsive class="btn-sm"
                        x-on:click="$wire.idToDelete = null">Batal</x-button>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh />
</div>