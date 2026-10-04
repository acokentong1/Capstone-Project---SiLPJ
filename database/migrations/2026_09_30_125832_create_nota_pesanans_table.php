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
    Schema::create('nota_pesanans', function (Blueprint $table) {

        $table->id();

        // pemilik sekolah
        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();


        // mengambil transaksi belanja
        $table->foreignId('belanja_id')
              ->constrained()
              ->cascadeOnDelete();


        $table->string('nomor_nota');

        $table->date('tanggal_nota');


        $table->string('status')
              ->default('Draft');


        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_pesanans');
    }
};
