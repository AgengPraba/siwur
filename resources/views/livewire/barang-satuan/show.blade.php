
<div>
 <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6">
        <div class="md:col-span-12">
            <x-card title="Lihat BarangSatuan" shadow separator>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-md text-gray-700 dark:text-gray-300">
                        <tbody>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nama Barang</b></th>
							
                 <td class="px-4 py-3">{{ $barang_satuan_data->nama_barang }}</td>
					</tr>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nama Satuan</b></th>
							
                 <td class="px-4 py-3">{{ $barang_satuan_data->nama_satuan }}</td>
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Konversi Satuan Terkecil</b></th>
                
							
                <td class="px-4 py-3">{{ $barang_satuan_data->konversi_satuan_terkecil }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Is Satuan Terkecil</b></th>
                
							
                <td class="px-4 py-3">{{ $barang_satuan_data->is_satuan_terkecil }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Created At</b></th>
                
							
                <td class="px-4 py-3">{{ $barang_satuan_data->created_at }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Updated At</b></th>
                
							
                <td class="px-4 py-3">{{ $barang_satuan_data->updated_at }}
                </td>
                
					</tr>
      </tr>
    </table>
     <x-slot:actions>
                    <x-button :href="route('barang-satuan.index')" wire:navigate class="btn-error text-white btn-sm end"
                        icon="o-backspace">Kembali</x-button>
                </x-slot:actions>
   </x-card>
  </div>
  </div>
  <x-back-refresh />
</div>