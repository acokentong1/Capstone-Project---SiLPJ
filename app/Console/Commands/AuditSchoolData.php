<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditSchoolData extends Command
{
    protected $signature = 'silpj:audit-data {--repair-null-links : Isi relasi kosong hanya jika sekolah pemilik akun sudah diketahui}';
    protected $description = 'Periksa relasi sekolah dan integritas nominal tanpa menghapus data.';

    public function handle(): int
    {
        if ($this->option('repair-null-links')) {
            DB::transaction(function () {
                foreach (['belanjas', 'anggarans'] as $table) {
                    DB::table($table)
                        ->whereNull('school_profile_id')
                        ->orderBy('id')
                        ->chunkById(200, function ($rows) use ($table) {
                            foreach ($rows as $row) {
                                $school = DB::table('users')
                                    ->where('id', $row->user_id)
                                    ->value('school_profile_id');
                                if (
                                    $school &&
                                    DB::table('school_profiles')
                                        ->where('id', $school)
                                        ->exists()
                                ) {
                                    DB::table($table)
                                        ->where('id', $row->id)
                                        ->whereNull('school_profile_id')
                                        ->update([
                                            'school_profile_id' => $school,
                                        ]);
                                }
                            }
                        });
                }
            });
            $this->info(
                'Relasi kosong yang pemilik sekolahnya diketahui telah diisi. Relasi yang sudah terisi tidak diubah.',
            );
        }
        $checks = [];
        foreach (['users', 'belanjas', 'anggarans'] as $table) {
            $checks[] = [
                $table . ' tanpa sekolah',
                DB::table($table)->whereNull('school_profile_id')->count(),
            ];
            $checks[] = [
                $table . ' sekolah tidak ditemukan',
                DB::table($table . ' as data')
                    ->leftJoin(
                        'school_profiles as school',
                        'data.school_profile_id',
                        '=',
                        'school.id',
                    )
                    ->whereNotNull('data.school_profile_id')
                    ->whereNull('school.id')
                    ->count(),
            ];
        }
        $checks[] = [
            'Belanja total berbeda jumlah × harga',
            DB::table('belanjas')
                ->whereRaw('total <> jumlah * harga_satuan')
                ->count(),
        ];
        $checks[] = [
            'Kwitansi berbeda total belanja',
            DB::table('kwitansis as k')
                ->join('belanjas as b', 'b.id', '=', 'k.belanja_id')
                ->whereColumn('k.jumlah_uang', '<>', 'b.total')
                ->count(),
        ];
        $checks[] = [
            'Anggaran tahun/sekolah ganda',
            DB::table('anggarans')
                ->select('school_profile_id', 'tahun')
                ->groupBy('school_profile_id', 'tahun')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count(),
        ];
        $this->table(['Pemeriksaan', 'Jumlah temuan'], $checks);
        $this->line(
            'Akun belum ditugaskan bisa wajar. Periksa setiap temuan sebelum menggunakan data untuk laporan final.',
        );
        return collect($checks)->sum(fn($row) => $row[1]) > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
