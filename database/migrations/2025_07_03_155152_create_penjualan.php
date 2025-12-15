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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_penjualan')->unique();
            $table->datetime('tanggal_penjualan');
            $table->foreignId('customer_id')->constrained('customer');
            $table->foreignId('user_id')->constrained('users');
            $table->string('keterangan')->nullable();
            $table->foreignId('toko_id')->constrained('toko');
            $table->decimal('kembalian', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
