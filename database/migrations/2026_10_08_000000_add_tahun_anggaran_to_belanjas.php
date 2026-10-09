<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->integer('tahun_anggaran')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->dropColumn('tahun_anggaran');
        });
    }
};
