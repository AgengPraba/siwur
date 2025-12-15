
<div>
<x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 mt-6">
        <div class="md:col-span-12">
         <x-card title="Form {{ $type == 'create' ? 'Tambah' : 'Edit' }} Pembelian detail" subtitle="Isikan Data Pembelian detail di bawah ini" shadow separator>
        <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="gap-2">
                
                            <x-select wire:model="pembelian_id" label="Nomor Pembelian" :options="$pembelian_data" placeholder="Pilih Nomor Pembelian" />
                            <x-select wire:model="barang_id" label="Nama Barang" :options="$barang_data" placeholder="Pilih Nama Barang" />
                            <x-select wire:model="satuan_id" label="Nama Satuan" :options="$satuan_data" placeholder="Pilih Nama Satuan" />
                           <x-input wire:model="harga_satuan" label="Harga Satuan" placeholder="Harga Satuan" />
                        
                           <x-input wire:model="jumlah" label="Jumlah" placeholder="Jumlah" />
                        
                           <x-input wire:model="konversi_satuan_terkecil" label="Konversi Satuan Terkecil" placeholder="Konversi Satuan Terkecil" />
                        
                           <x-input wire:model="subtotal" label="Subtotal" placeholder="Subtotal" />
                        
             <x-slot:actions>
                        <x-button icon="o-check" label="Simpan" type="submit" class="btn-primary" spinner />
                        <x-button icon="o-backspace" :href="route('pembelian-detail.index')" label="Kembali" class="btn-error text-white" wire:navigate />
            </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
    <x-back-refresh />
</div>