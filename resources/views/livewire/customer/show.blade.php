
<div>
 <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6">
        <div class="md:col-span-12">
            <x-card title="Lihat Customer" shadow separator>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-md text-gray-700 dark:text-gray-300">
                        <tbody>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Nama Customer</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->nama_customer }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Alamat</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->alamat }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>No Hp</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->no_hp }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Email</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->email }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Keterangan</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->keterangan }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Created At</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->created_at }}
                </td>
                
					</tr>
			
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                
						<th class="w-44 px-4 py-3 text-left font-semibold bg-gray-100 dark:bg-gray-900"><b>Updated At</b></th>
                
							
                <td class="px-4 py-3">{{ $customer_data->updated_at }}
                </td>
                
					</tr>
      </tr>
    </table>
     <x-slot:actions>
                    <x-button :href="route('customer.index')" wire:navigate class="btn-error text-white btn-sm end"
                        icon="o-backspace">Kembali</x-button>
                </x-slot:actions>
   </x-card>
  </div>
  </div>
  <x-back-refresh />
</div>