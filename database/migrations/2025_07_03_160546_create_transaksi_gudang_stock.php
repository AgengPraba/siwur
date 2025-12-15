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
        Schema::create('transaksi_gudang_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_stock_id')->constrained('gudang_stock');
            $table->unsignedBigInteger('pembelian_detail_id')->nullable();
            $table->unsignedBigInteger('penjualan_detail_id')->nullable();
            $table->decimal('jumlah', 10, 2);
            $table->decimal('konversi_satuan_terkecil', 10, 2)->default(0);
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->timestamps();
            
            // Foreign key constraints with nullable support
            $table->foreign('pembelian_detail_id')->references('id')->on('pembelian_detail')->onDelete('set null');
            $table->foreign('penjualan_detail_id')->references('id')->on('penjualan_detail')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_gudang_stock');
    }
};
