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
        Schema::create('bapbs', function (Blueprint $table) {
            $table->id();

            // Akun/sekolah pemilik data
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Data belanja yang diperiksa
            $table->foreignId('belanja_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('nomor_bapb');

            $table->date('tanggal_bapb');

            $table->string('hasil_pemeriksaan')
                ->default('Baik dan sesuai');

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bapbs');
    }
};
