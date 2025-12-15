<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan');
            $table->foreignId('pembelian_detail_id')->constrained('pembelian_detail');
            $table->foreignId('barang_id')->constrained('barang');
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->foreignId('gudang_id')->constrained('gudang');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('biaya_lain', 15, 2)->default(0);
            $table->decimal('jumlah', 15, 2);
            $table->decimal('konversi_satuan_terkecil', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
