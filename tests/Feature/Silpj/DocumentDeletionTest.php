<?php

namespace Tests\Feature\Silpj;

use App\Models\{SchoolProfile, User, Belanja, LpjEvent};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $status = 'draf'): array
    {
        $school = SchoolProfile::create(['nama_sekolah' => 'Sekolah Uji']);
        $user = User::factory()->create(['role' => 'bendahara', 'school_profile_id' => $school->id]);
        $belanja = Belanja::create([
            'user_id' => $user->id, 'school_profile_id' => $school->id,
            'tahun_anggaran' => 2026, 'tanggal' => '2026-10-11',
            'nomor_bukti' => 'B-'.$user->id, 'uraian' => 'ATK', 'kategori' => 'Barang',
            'jumlah' => 1, 'harga_satuan' => 10000, 'total' => 10000,
        ]);
        $belanja->forceFill(['review_status' => $status])->save();
        $docs = [
            'kwitansi' => $belanja->kwitansi()->create(['nomor_kwitansi' => 'KW-'.$user->id, 'tanggal_kwitansi' => '2026-10-11', 'penerima' => 'Toko', 'jumlah_uang' => 10000, 'terbilang' => 'sepuluh ribu']),
            'nota-pesanan' => $belanja->notaPesanan()->create(['user_id' => $user->id, 'nomor_nota' => 'NP-'.$user->id, 'tanggal_nota' => '2026-10-11', 'status' => 'Selesai']),
            'bapb' => $belanja->bapb()->create(['user_id' => $user->id, 'nomor_bapb' => 'BA-'.$user->id, 'tanggal_bapb' => '2026-10-11', 'hasil_pemeriksaan' => 'Baik']),
        ];
        return [$user, $belanja, $docs];
    }

    private function payload(): array
    {
        return ['reason' => 'Dokumen dibuat keliru', 'confirm_delete' => '1'];
    }

    public function test_each_document_can_be_deleted_without_deleting_transaction_or_other_documents(): void
    {
        foreach (['kwitansi', 'nota-pesanan', 'bapb'] as $kind) {
            [$user, $belanja, $docs] = $this->fixture();
            $doc = $docs[$kind];
            $this->actingAs($user)->delete(route('documents.destroy', [$kind, $doc->id]), $this->payload())->assertRedirect(route($kind.'.index'));
            $this->assertDatabaseMissing($doc->getTable(), ['id' => $doc->id]);
            $this->assertDatabaseHas('belanjas', ['id' => $belanja->id, 'total' => 10000]);
            foreach ($docs as $otherKind => $other) {
                if ($otherKind !== $kind) {
                    $this->assertDatabaseHas($other->getTable(), ['id' => $other->id]);
                }
            }
            $event = LpjEvent::where('belanja_id', $belanja->id)->where('action', 'hapus_dokumen')->firstOrFail();
            $this->assertSame($kind, $event->details['jenis']);
            $this->assertEquals($doc->id, $event->details['dokumen']['id']);
            $this->assertSame('Dokumen dibuat keliru', $event->reason);
        }
    }

    public function test_submitted_and_approved_documents_cannot_be_deleted_even_by_admin(): void
    {
        foreach (['diajukan', 'disetujui'] as $status) {
            [$user, $belanja, $docs] = $this->fixture($status);
            $user->forceFill(['role' => 'admin'])->save();
            foreach ($docs as $kind => $doc) {
                $this->actingAs($user)->delete(route('documents.destroy', [$kind, $doc->id]), $this->payload())->assertSessionHasErrors('lpj');
                $this->assertDatabaseHas($doc->getTable(), ['id' => $doc->id]);
            }
            $this->assertSame(0, LpjEvent::where('belanja_id', $belanja->id)->where('action', 'hapus_dokumen')->count());
        }
    }

    public function test_reason_and_confirmation_are_required(): void
    {
        [$user, , $docs] = $this->fixture();
        $doc = $docs['kwitansi'];
        $this->actingAs($user)->delete(route('documents.destroy', ['kwitansi', $doc->id]), [])->assertSessionHasErrors(['reason', 'confirm_delete']);
        $this->assertDatabaseHas($doc->getTable(), ['id' => $doc->id]);
    }

    public function test_reader_head_and_other_school_cannot_delete_documents(): void
    {
        [$owner, , $docs] = $this->fixture();
        foreach (['user', 'kepala_sekolah'] as $role) {
            $actor = User::factory()->create(['role' => $role, 'school_profile_id' => $owner->school_profile_id]);
            foreach ($docs as $kind => $doc) {
                $this->actingAs($actor)->delete(route('documents.destroy', [$kind, $doc->id]), $this->payload())->assertForbidden();
            }
        }
        $other = SchoolProfile::create(['nama_sekolah' => 'Sekolah lain']);
        $actor = User::factory()->create(['role' => 'admin', 'school_profile_id' => $other->id]);
        foreach ($docs as $kind => $doc) {
            $this->actingAs($actor)->delete(route('documents.destroy', [$kind, $doc->id]), $this->payload())->assertNotFound();
            $this->assertDatabaseHas($doc->getTable(), ['id' => $doc->id]);
        }
    }

    public function test_revision_document_can_be_deleted_and_repeated_deletion_returns_not_found(): void
    {
        [$user, , $docs] = $this->fixture('revisi');
        $url = route('documents.destroy', ['bapb', $docs['bapb']->id]);
        $this->actingAs($user)->delete($url, $this->payload())->assertRedirect(route('bapb.index'));
        $this->delete($url, $this->payload())->assertNotFound();
    }
}
