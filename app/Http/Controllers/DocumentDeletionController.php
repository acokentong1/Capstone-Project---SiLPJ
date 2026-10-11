<?php

namespace App\Http\Controllers;

use App\Models\{Belanja, Kwitansi, NotaPesanan, Bapb};
use App\Services\LpjWorkflow;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentDeletionController extends Controller
{
    public function destroy(Request $request, string $kind, int $id)
    {
        $types = [
            'kwitansi' => [Kwitansi::class, 'Kwitansi'],
            'nota-pesanan' => [NotaPesanan::class, 'Nota Pesanan'],
            'bapb' => [Bapb::class, 'BAPB'],
        ];
        abort_unless(isset($types[$kind]), 404);
        abort_unless($request->user()->isBendahara(), 403);
        [$model, $label] = $types[$kind];
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'confirm_delete' => ['required', 'accepted'],
        ], [
            'reason.required' => 'Isi alasan penghapusan dokumen.',
            'reason.min' => 'Alasan penghapusan minimal 5 karakter.',
            'confirm_delete.required' => 'Centang konfirmasi penghapusan terlebih dahulu.',
            'confirm_delete.accepted' => 'Centang konfirmasi penghapusan terlebih dahulu.',
        ]);
        DB::transaction(function () use ($request, $model, $id, $kind, $label, $data) {
            $schoolId = $request->user()->school_profile_id ?? 0;
            $document = $model::whereHas('belanja', fn ($q) => $q->where('school_profile_id', $schoolId))->findOrFail($id);
            // Same parent-row lock as document creation and LPJ submission.
            $belanja = Belanja::where('school_profile_id', $schoolId)->lockForUpdate()->findOrFail($document->belanja_id);
            LpjWorkflow::assertEditable($belanja);
            $document = $model::where('belanja_id', $belanja->id)->lockForUpdate()->findOrFail($id);
            $snapshot = $document->getAttributes();
            LpjWorkflow::record($belanja, 'hapus_dokumen', $data['reason'], [
                'jenis' => $kind, 'dokumen' => $snapshot,
            ]);
            ActivityLogger::record($request->user()->id, 'delete', 'Menghapus '.$label, $document, [
                'belanja_id' => $belanja->id, 'reason' => $data['reason'], 'document' => $snapshot,
            ]);
            $document->delete();
        });
        return redirect()->route($kind.'.index')->with('success', $label.' berhasil dihapus. Transaksi belanja dan dokumen lainnya tetap tersimpan. Dokumen dapat dibuat ulang.');
    }
}
