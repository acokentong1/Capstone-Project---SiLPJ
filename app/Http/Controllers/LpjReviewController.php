<?php
namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\LpjAttachment;
use App\Models\LpjEvent;
use App\Services\LpjWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LpjReviewController extends Controller
{
    private function query(Request $request)
    {
        return Belanja::where(
            'school_profile_id',
            $request->user()->school_profile_id ?? 0,
        );
    }

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:draf,diajukan,revisi,disetujui'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $base = $this->query($request);
        $counts = (clone $base)
            ->selectRaw('review_status, COUNT(*) as total')
            ->groupBy('review_status')
            ->pluck('total', 'review_status');
        $items = $base
            ->when(
                $request->status,
                fn($q, $status) => $q->where('review_status', $status),
            )
            ->when(
                $request->q,
                fn($q, $text) => $q->where(
                    fn($q) => $q
                        ->where('uraian', 'like', '%' . $text . '%')
                        ->orWhere('nomor_bukti', 'like', '%' . $text . '%'),
                ),
            )
            ->withCount('attachments')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
        $events = LpjEvent::where(
            'school_profile_id',
            $request->user()->school_profile_id ?? 0,
        )
            ->latest('id')
            ->limit(30)
            ->get();
        return view('lpj-review.index', compact('items', 'counts', 'events'));
    }

    public function show(Request $request, int $id)
    {
        $item = $this->query($request)
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->findOrFail($id);
        $attachments = $item
            ->attachments()
            ->select(['id', 'belanja_id', 'filename', 'size', 'created_at'])
            ->latest()
            ->get();
        $events = $item->reviewEvents()->latest('id')->get();
        return view(
            'lpj-review.show',
            compact('item', 'attachments', 'events'),
        );
    }

    public function transition(Request $request, int $id, LpjWorkflow $workflow)
    {
        $data = $request->validate([
            'action' => ['required', 'in:ajukan,setujui,revisi,buka'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $workflow->transition(
            $request->user(),
            $id,
            $data['action'],
            $data['reason'] ?? null,
        );
        return back()->with(
            'success',
            'Status pemeriksaan LPJ berhasil diperbarui.',
        );
    }

    public function upload(Request $request, int $id)
    {
        $request->validate([
            'bukti' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:2048',
            ],
        ]);
        $file = $request->file('bukti');
        $bytes = file_get_contents($file->getRealPath());
        if ($bytes === false) {
            throw ValidationException::withMessages([
                'bukti' => 'Berkas gagal dibaca.',
            ]);
        }
        DB::transaction(function () use ($request, $id, $file, $bytes) {
            $item = $this->query($request)->lockForUpdate()->findOrFail($id);
            LpjWorkflow::assertEditable($item);
            if ($item->attachments()->count() >= 5) {
                throw ValidationException::withMessages([
                    'bukti' => 'Maksimal 5 lampiran per transaksi.',
                ]);
            }
            $mime = $file->getMimeType();
            $extension = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            ][$mime];
            $name = preg_replace(
                '/[^\pL\pN._ -]/u',
                '_',
                pathinfo(
                    basename($file->getClientOriginalName()),
                    PATHINFO_FILENAME,
                ),
            );
            $attachment = $item->attachments()->create([
                'uploaded_by' => $request->user()->id,
                'filename' =>
                    mb_substr($name ?: 'bukti', 0, 160) . '.' . $extension,
                'mime_type' => $mime,
                'size' => strlen($bytes),
                'sha256' => hash('sha256', $bytes),
                'contents_base64' => base64_encode($bytes),
            ]);
            LpjWorkflow::record($item, 'unggah_bukti', null, [
                'filename' => $attachment->filename,
                'sha256' => $attachment->sha256,
            ]);
        });
        return back()->with('success', 'Bukti transaksi berhasil diunggah.');
    }

    public function download(Request $request, int $attachment)
    {
        $file = LpjAttachment::whereHas(
            'belanja',
            fn($q) => $q->where(
                'school_profile_id',
                $request->user()->school_profile_id ?? 0,
            ),
        )->findOrFail($attachment);
        $bytes = base64_decode($file->contents_base64, true);
        abort_unless(
            $bytes !== false &&
                hash_equals($file->sha256, hash('sha256', $bytes)),
            409,
            'Integritas lampiran tidak valid.',
        );
        return response()->streamDownload(
            static function () use ($bytes) {
                echo $bytes;
            },
            $file->filename,
            [
                'Content-Type' => $file->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function remove(Request $request, int $id, int $attachment)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        DB::transaction(function () use ($request, $id, $attachment) {
            $item = $this->query($request)->lockForUpdate()->findOrFail($id);
            LpjWorkflow::assertEditable($item);
            $file = $item->attachments()->findOrFail($attachment);
            LpjWorkflow::record($item, 'hapus_bukti', $request->reason, [
                'filename' => $file->filename,
                'sha256' => $file->sha256,
            ]);
            $file->delete();
        });
        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
