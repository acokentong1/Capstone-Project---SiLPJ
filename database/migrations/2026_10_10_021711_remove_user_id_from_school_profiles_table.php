<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            // hapus foreign key dahulu
            $table->dropForeign(['user_id']);

            // hapus index unique
            $table->dropUnique(['user_id']);

            // baru hapus kolom
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->unique();

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
