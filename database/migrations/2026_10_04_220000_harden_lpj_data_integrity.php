<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureNoDuplicate('school_profiles', ['user_id'], 'Terdapat lebih dari satu profil sekolah untuk user yang sama. Rapikan data duplikat sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('nota_pesanans', ['belanja_id'], 'Terdapat lebih dari satu Nota Pesanan untuk transaksi yang sama. Rapikan data duplikat sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('kwitansis', ['belanja_id'], 'Terdapat lebih dari satu Kwitansi untuk transaksi yang sama. Rapikan data duplikat sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('bapbs', ['belanja_id'], 'Terdapat lebih dari satu BAPB untuk transaksi yang sama. Rapikan data duplikat sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('nota_pesanans', ['user_id', 'nomor_nota'], 'Terdapat nomor Nota Pesanan ganda pada user yang sama. Rapikan data sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('bapbs', ['user_id', 'nomor_bapb'], 'Terdapat nomor BAPB ganda pada user yang sama. Rapikan data sebelum menjalankan migration ini.');
        $this->ensureNoDuplicate('kwitansis', ['nomor_kwitansi'], 'Terdapat nomor Kwitansi ganda. Rapikan data sebelum menjalankan migration ini.');

        Schema::table('school_profiles', function (Blueprint $table) {
            $table->unique('user_id', 'school_profiles_user_id_unique');
        });

        Schema::table('nota_pesanans', function (Blueprint $table) {
            $table->unique('belanja_id', 'nota_pesanans_belanja_id_unique');
            $table->unique(['user_id', 'nomor_nota'], 'nota_pesanans_user_nomor_unique');
        });

        Schema::table('kwitansis', function (Blueprint $table) {
            $table->unique('belanja_id', 'kwitansis_belanja_id_unique');
            $table->unique('nomor_kwitansi', 'kwitansis_nomor_kwitansi_unique');
        });

        Schema::table('bapbs', function (Blueprint $table) {
            $table->unique('belanja_id', 'bapbs_belanja_id_unique');
            $table->unique(['user_id', 'nomor_bapb'], 'bapbs_user_nomor_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bapbs', function (Blueprint $table) {
            $table->dropUnique('bapbs_belanja_id_unique');
            $table->dropUnique('bapbs_user_nomor_unique');
        });

        Schema::table('kwitansis', function (Blueprint $table) {
            $table->dropUnique('kwitansis_belanja_id_unique');
            $table->dropUnique('kwitansis_nomor_kwitansi_unique');
        });

        Schema::table('nota_pesanans', function (Blueprint $table) {
            $table->dropUnique('nota_pesanans_belanja_id_unique');
            $table->dropUnique('nota_pesanans_user_nomor_unique');
        });

        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropUnique('school_profiles_user_id_unique');
        });
    }

    private function ensureNoDuplicate(string $table, array $columns, string $message): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $query = DB::table($table)
            ->select($columns)
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy(...$columns)
            ->havingRaw('COUNT(*) > 1');

        if ($query->exists()) {
            throw new RuntimeException($message);
        }
    }
};
