<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // User Management
            'user.index',
            'user.create',
            'user.show',
            'user.edit',
            'user.delete',

            // Master Data - Satuan
            'satuan.index',
            'satuan.create',
            'satuan.show',
            'satuan.edit',
            'satuan.delete',

            // Master Data - Jenis Barang
            'jenis-barang.index',
            'jenis-barang.create',
            'jenis-barang.show',
            'jenis-barang.edit',
            'jenis-barang.delete',

            // Master Data - Barang
            'barang.index',
            'barang.create',
            'barang.show',
            'barang.edit',
            'barang.delete',

            // Master Data - Supplier
            'supplier.index',
            'supplier.create',
            'supplier.show',
            'supplier.edit',
            'supplier.delete',

            // Master Data - Customer
            'customer.index',
            'customer.create',
            'customer.show',
            'customer.edit',
            'customer.delete',

            // Master Data - Gudang
            'gudang.index',
            'gudang.create',
            'gudang.show',
            'gudang.edit',
            'gudang.delete',

            // Transaksi - Pembelian
            'pembelian.index',
            'pembelian.create',
            'pembelian.show',
            'pembelian.edit',
            'pembelian.delete',
            'pembelian.print',

            // Transaksi - Penjualan
            'penjualan.index',
            'penjualan.create',
            'penjualan.show',
            'penjualan.edit',
            'penjualan.delete',
            'penjualan.print',

            // Retur Pembelian
            'retur-pembelian.index',
            'retur-pembelian.create',
            'retur-pembelian.show',
            'retur-pembelian.edit',
            'retur-pembelian.delete',
            'retur-pembelian.print',

            // Retur Penjualan
            'retur-penjualan.index',
            'retur-penjualan.create',
            'retur-penjualan.show',
            'retur-penjualan.edit',
            'retur-penjualan.delete',
            'retur-penjualan.print',

            // Inventory - Gudang Stock
            'gudang-stock.index',
            'gudang-stock.create',
            'gudang-stock.show',
            'gudang-stock.edit',
            'gudang-stock.delete',

            // Inventory - Stock Opname
            'stock-opname.index',
            'stock-opname.create',
            'stock-opname.show',
            'stock-opname.edit',
            'stock-opname.delete',
            'stock-opname.print',

            // Inventory - Transaksi Gudang Stock
            'transaksi-gudang-stock.index',
            'transaksi-gudang-stock.create',
            'transaksi-gudang-stock.show',
            'transaksi-gudang-stock.edit',
            'transaksi-gudang-stock.delete',

            // Laporan
            'laporan.pembayaran',
            'laporan.profit',
            'laporan.print',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and assign permissions
        
        // 1. Admin - Full Access
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // 2. Kasir - Fokus pada transaksi penjualan
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $kasirRole->syncPermissions([
            // Penjualan
            'penjualan.index',
            'penjualan.create',
            'penjualan.show',
            'penjualan.edit',
            'penjualan.print',
            // Retur Penjualan
            'retur-penjualan.index',
            'retur-penjualan.create',
            'retur-penjualan.show',
            'retur-penjualan.print',
            // Customer (read only)
            'customer.index',
            'customer.show',
            // Barang (read only untuk cek harga/stock)
            'barang.index',
            'barang.show',
        ]);

        // 3. Staff Gudang - Fokus pada inventory dan stock
        $staffGudangRole = Role::firstOrCreate(['name' => 'staff_gudang', 'guard_name' => 'web']);
        $staffGudangRole->syncPermissions([
            // Gudang Stock
            'gudang-stock.index',
            'gudang-stock.show',
            'gudang-stock.edit',
            // Stock Opname
            'stock-opname.index',
            'stock-opname.create',
            'stock-opname.show',
            'stock-opname.print',
            // Barang (read only)
            'barang.index',
            'barang.show',
            // Pembelian (read only untuk terima barang)
            'pembelian.index',
            'pembelian.show',
            // Retur Pembelian (read only)
            'retur-pembelian.index',
            'retur-pembelian.show',
            // Transaksi Gudang Stock
            'transaksi-gudang-stock.index',
            'transaksi-gudang-stock.create',
            'transaksi-gudang-stock.show',
        ]);

        // 4. Akuntan - Fokus pada laporan keuangan
        $akuntanRole = Role::firstOrCreate(['name' => 'akuntan', 'guard_name' => 'web']);
        $akuntanRole->syncPermissions([
            // Laporan
            'laporan.pembayaran',
            'laporan.profit',
            'laporan.print',
            // Pembelian (read only)
            'pembelian.index',
            'pembelian.show',
            'pembelian.print',
            // Penjualan (read only)
            'penjualan.index',
            'penjualan.show',
            'penjualan.print',
            // Retur Pembelian (read only)
            'retur-pembelian.index',
            'retur-pembelian.show',
            'retur-pembelian.print',
            // Retur Penjualan (read only)
            'retur-penjualan.index',
            'retur-penjualan.show',
            'retur-penjualan.print',
        ]);
    }
}
