
<div>
<x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6">
        <div class="md:col-span-12">
         <x-card title="Form {{ $type == 'create' ? 'Tambah' : 'Edit' }} Transaksi gudang stock" subtitle="Isikan Data Transaksi gudang stock di bawah ini" shadow separator>
        <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="gap-2">
                
                            <x-select wire:model="gudang_stock_id" label="Gudang Id" :options="$gudang_stock_data" placeholder="Pilih Gudang Id" />
                            <x-select wire:model="pembelian_detail_id" label="Pembelian Id" :options="$pembelian_detail_data" placeholder="Pilih Pembelian Id" />
                            <x-select wire:model="penjualan_detail_id" label="Penjualan Id" :options="$penjualan_detail_data" placeholder="Pilih Penjualan Id" />
                           <x-input wire:model="jumlah" label="Jumlah" placeholder="Jumlah" />
                        
                           <x-input wire:model="konversi_satuan_terkecil" label="Konversi Satuan Terkecil" placeholder="Konversi Satuan Terkecil" />
                        
                           <x-input wire:model="tipe" label="Tipe" placeholder="Tipe" />
                        
             <x-slot:actions>
                        <x-button icon="o-check" label="Simpan" type="submit" class="btn-primary" spinner />
                        <x-button icon="o-backspace" :href="route('transaksi-gudang-stock.index')" label="Kembali" class="btn-error text-white" wire:navigate />
            </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
    <x-back-refresh />
</div>