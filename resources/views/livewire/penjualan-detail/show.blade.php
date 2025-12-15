
<div>
 <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6">
        <div class="md:col-span-12">
            <x-card title="Lihat PenjualanDetail" shadow separator>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-md text-gray-700 dark:text-gray-300">
                        <tbody>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nomor Penjualan</b></th>
							
                 <td class="px-4 py-3">{{ $penjualan_detail_data->nomor_penjualan }}</td>
					</tr>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Pembelian Id</b></th>
							
                 <td class="px-4 py-3">{{ $penjualan_detail_data->pembelian_id }}</td>
					</tr>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nama Barang</b></th>
							
                 <td class="px-4 py-3">{{ $penjualan_detail_data->nama_barang }}</td>
					</tr>
					
               <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						
                <th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nama Satuan</b></th>
							
                 <td class="px-4 py-3">{{ $penjualan_detail_data->nama_satuan }}</td>
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Harga Satuan</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->harga_satuan }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Jumlah</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->jumlah }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Konversi Satuan Terkecil</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->konversi_satuan_terkecil }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Subtotal</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->subtotal }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Profit</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->profit }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Created At</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->created_at }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Updated At</b></th>
                
							
                <td class="px-4 py-3">{{ $penjualan_detail_data->updated_at }}
                </td>
                
					</tr>
      </tr>
    </table>
     <x-slot:actions>
                    <x-button :href="route('penjualan-detail.index')" wire:navigate class="btn-error text-white btn-sm end"
                        icon="o-backspace">Kembali</x-button>
                </x-slot:actions>
   </x-card>
  </div>
  </div>
  <x-back-refresh />
</div>