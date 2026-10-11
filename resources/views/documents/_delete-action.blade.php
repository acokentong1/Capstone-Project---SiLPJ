@if(auth()->user()->isBendahara() && $document->belanja?->isEditable())
    <button type="button" class="silpj-doc-action silpj-doc-delete no-print"
        data-silpj-delete
        data-action="{{ route('documents.destroy', ['kind'=>$documentKind, 'id'=>$document->id]) }}"
        data-label="{{ $documentLabel }}"
        data-number="{{ $document->nomor_kwitansi ?? $document->nomor_nota ?? $document->nomor_bapb }}"
        aria-haspopup="dialog" aria-controls="silpj-delete-dialog">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/></svg>
        Hapus
    </button>
@elseif(auth()->user()->isBendahara() && $document->belanja)
    @include('documents._locked-action', ['lockedBelanjaId'=>$document->belanja_id])
@endif
