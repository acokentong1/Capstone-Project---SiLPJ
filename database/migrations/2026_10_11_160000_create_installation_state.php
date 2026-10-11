<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('installation_state', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('completed')->default(false);
        });
        // Existing installations cannot be claimed through the public setup page.
        DB::table('installation_state')->insert([
            'id' => 1,
            'completed' => DB::table('users')->exists() || DB::table('school_profiles')->exists(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_state');
    }
};
