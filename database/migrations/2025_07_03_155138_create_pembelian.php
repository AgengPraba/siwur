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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pembelian')->unique();
            $table->datetime('tanggal_pembelian');
            $table->foreignId('supplier_id')->constrained('supplier');
            $table->foreignId('user_id')->constrained('users');
            $table->string('keterangan')->nullable();
            $table->decimal('total_harga', 15, 2);
            $table->enum('status', ['belum_bayar', 'belum_lunas', 'lunas'])->default('belum_bayar');
            $table->text('informasi_tambahan')->nullable();
            $table->text('balasan_informasi_tambahan')->nullable();
            $table->foreignId('toko_id')->constrained('toko');
            $table->decimal('kembalian', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
