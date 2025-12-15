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
        Schema::create('stock_opname_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_stock_id')->constrained('gudang_stock')->cascadeOnDelete();
            $table->foreignId('stock_opname_id')->constrained('stock_opname')->cascadeOnDelete();
            $table->decimal('stok_sistem', 10, 2)->default(0);
            $table->decimal('stok_fisik', 10, 2)->default(0);
            $table->decimal('selisih', 10, 2)->default(0);
            $table->decimal('before_qty', 10, 2)->default(0)->comment('Stok sebelum opname');
            $table->decimal('after_qty', 10, 2)->default(0)->comment('Stok setelah opname');
            $table->string('adjustment_type', 20)->nullable()->comment('plus, minus, atau sama');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_detail');
    }
};