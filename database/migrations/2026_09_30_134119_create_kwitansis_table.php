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
    Schema::create('kwitansis', function (Blueprint $table) {
        $table->id();

        $table->foreignId('belanja_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('nomor_kwitansi');
        $table->date('tanggal_kwitansi');

        $table->string('penerima');
        $table->decimal('jumlah_uang', 12, 2);

        $table->text('terbilang')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwitansis');
    }
};
