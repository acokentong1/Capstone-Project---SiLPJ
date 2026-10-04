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
    Schema::create('school_profiles', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('nama_sekolah');
        $table->string('npsn')->nullable();
        $table->text('alamat')->nullable();

        $table->string('kepala_sekolah')->nullable();
        $table->string('nip_kepala')->nullable();

        $table->string('bendahara')->nullable();
        $table->string('nip_bendahara')->nullable();

        $table->string('logo')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
    }
};
