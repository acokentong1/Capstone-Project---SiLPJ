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
    Schema::create('belanjas', function (Blueprint $table) {

        $table->id();

        // relasi dengan akun sekolah
        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();


        $table->date('tanggal');

        $table->string('nomor_bukti')
              ->nullable();


        $table->string('uraian');


        $table->string('kategori')
              ->nullable();


        $table->integer('jumlah')
              ->default(1);


        $table->integer('harga_satuan')
              ->default(0);


        $table->integer('total')
              ->default(0);


        $table->string('nota')
              ->nullable();


        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belanjas');
    }
};
