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
        Schema::table('transaksi_gudang_stock', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['pembelian_detail_id']);
            $table->dropForeign(['penjualan_detail_id']);
            
            // Modify columns to be nullable
            $table->unsignedBigInteger('pembelian_detail_id')->nullable()->change();
            $table->unsignedBigInteger('penjualan_detail_id')->nullable()->change();
            
            // Recreate foreign key constraints with nullable support
            $table->foreign('pembelian_detail_id')
                  ->references('id')
                  ->on('pembelian_detail')
                  ->onDelete('set null');
                  
            $table->foreign('penjualan_detail_id')
                  ->references('id')
                  ->on('penjualan_detail')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_gudang_stock', function (Blueprint $table) {
            // Drop the new foreign key constraints
            $table->dropForeign(['pembelian_detail_id']);
            $table->dropForeign(['penjualan_detail_id']);
            
            // Revert columns to not nullable
            $table->unsignedBigInteger('pembelian_detail_id')->nullable(false)->change();
            $table->unsignedBigInteger('penjualan_detail_id')->nullable(false)->change();
            
            // Recreate original foreign key constraints
            $table->foreign('pembelian_detail_id')
                  ->references('id')
                  ->on('pembelian_detail')
                  ->onDelete('cascade');
                  
            $table->foreign('penjualan_detail_id')
                  ->references('id')
                  ->on('penjualan_detail')
                  ->onDelete('cascade');
        });
    }
};
