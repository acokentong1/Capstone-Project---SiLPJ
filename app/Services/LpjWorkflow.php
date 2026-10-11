<?php
namespace App\Services;

use App\Models\Belanja;
use App\Models\LpjEvent;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LpjWorkflow
{
    public static function assertEditable(Belanja $belanja): void
    {
        if (!in_array($belanja->review_status, ['draf', 'revisi'], true)) {
            throw ValidationException::withMessages([
                'lpj' =>
                    'Transaksi terkunci karena sedang diajukan atau sudah disetujui. Minta pemeriksa mengembalikan untuk revisi.',
            ]);
        }
    }

    public static function record(
        Belanja $belanja,
        string $action,
        ?string $reason = null,
        array $details = [],
        ?string $from = null,
    ): void {
        $actor = auth()->user();
        LpjEvent::create([
            'belanja_id' => $belanja->id,
            'school_profile_id' => $belanja->school_profile_id,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $belanja->review_status,
            'reason' => $reason,
            'details' => $details,
        ]);
    }

    public function transition(
        User $actor,
        int $id,
        string $action,
        ?string $reason,
    ): void {
        DB::transaction(function () use ($actor, $id, $action, $reason) {
            // Same locking order as spending updates: school, then transaction.
            SchoolProfile::whereKey($actor->school_profile_id)
                ->lockForUpdate()
                ->firstOrFail();
            $belanja = Belanja::where(
                'school_profile_id',
                $actor->school_profile_id ?? 0,
            )
                ->lockForUpdate()
                ->findOrFail($id);
            $from = $belanja->review_status;
            if ($action === 'ajukan') {
                abort_unless($actor->isBendahara(), 403);
                self::assertEditable($belanja);
                if (
                    !$belanja->notaPesanan()->exists() ||
                    !$belanja->kwitansi()->exists() ||
                    !$belanja->bapb()->exists() ||
                    !$belanja->attachments()->exists()
                ) {
                    throw ValidationException::withMessages([
                        'lpj' =>
                            'Lengkapi Nota Pesanan, Kwitansi, BAPB, dan minimal satu lampiran sebelum mengajukan.',
                    ]);
                }
                $belanja->review_status = 'diajukan';
                $belanja->submitted_by = $actor->id;
                $belanja->submitted_at = now();
                $belanja->approved_by = null;
                $belanja->approved_at = null;
            } else {
                abort_unless($actor->isKepalaSekolah(), 403);
                $expected = $action === 'buka' ? 'disetujui' : 'diajukan';
                if (
                    !in_array($action, ['setujui', 'revisi', 'buka'], true) ||
                    $from !== $expected
                ) {
                    throw ValidationException::withMessages([
                        'lpj' =>
                            'Status sudah berubah atau tindakan tidak sesuai. Muat ulang halaman.',
                    ]);
                }
                if ($action === 'setujui') {
                    abort_if(
                        (int) $belanja->submitted_by === (int) $actor->id ||
                            (int) $belanja->user_id === (int) $actor->id,
                        403,
                        'Persetujuan harus dilakukan akun pemeriksa yang berbeda dari pembuat dan pengaju.',
                    );
                    $belanja->review_status = 'disetujui';
                    $belanja->approved_by = $actor->id;
                    $belanja->approved_at = now();
                } else {
                    if (!is_string($reason) || mb_strlen(trim($reason)) < 5) {
                        throw ValidationException::withMessages([
                            'reason' =>
                                'Tuliskan alasan revisi/pembukaan kembali minimal 5 karakter.',
                        ]);
                    }
                    $belanja->review_status = 'revisi';
                    $belanja->approved_by = null;
                    $belanja->approved_at = null;
                }
            }
            $belanja->save();
            self::record(
                $belanja,
                $action,
                $reason,
                [
                    'nomor_bukti' => $belanja->nomor_bukti,
                    'total' => $belanja->total,
                    'snapshot' => $belanja->only([
                        'tanggal',
                        'tahun_anggaran',
                        'uraian',
                        'kategori',
                        'jumlah',
                        'harga_satuan',
                        'total',
                    ]),
                    'school' => $belanja->schoolProfile?->only([
                        'nama_sekolah',
                        'npsn',
                        'kepala_sekolah',
                        'bendahara',
                    ]),
                    'evidence' => $belanja
                        ->attachments()
                        ->get(['filename', 'sha256'])
                        ->toArray(),
                ],
                $from,
            );
        });
    }
}
