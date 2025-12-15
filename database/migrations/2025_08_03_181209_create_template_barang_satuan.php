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
        Schema::create('template_barang_satuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('template_barang');
            $table->foreignId('satuan_id')->constrained('template_satuan');
            $table->decimal('konversi_satuan_terkecil', 10, 2);
            $table->enum('is_satuan_terkecil', ['ya', 'tidak'])->default('tidak');
            $table->unique(['barang_id', 'satuan_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_barang_satuan');
    }
};
