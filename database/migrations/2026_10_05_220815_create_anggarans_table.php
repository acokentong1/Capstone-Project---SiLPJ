<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anggarans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table
                ->foreignId('school_profile_id')
                ->nullable()
                ->constrained('school_profiles')
                ->cascadeOnDelete();

            $table->year('tahun');

            $table->bigInteger('jumlah');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggarans');
    }
};
