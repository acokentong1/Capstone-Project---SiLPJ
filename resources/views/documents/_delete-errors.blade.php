@if($errors->any())
    <div class="no-print" role="alert" style="margin:16px;padding:14px;background:#fff0ef;color:#9b1c1c;border-radius:8px">
        <strong>Dokumen belum dihapus:</strong>
        <ul>
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
<style>
.silpj-doc-action{display:inline-flex;align-items:center;justify-content:center;gap:6px;align-self:center;flex:none;white-space:nowrap;font:700 12px/1.2 Arial,sans-serif;padding:9px 12px;border:1px solid;border-radius:9px;cursor:pointer}
.silpj-doc-delete{color:#b42318;background:#fff5f4;border-color:#fecaca}.silpj-doc-delete:hover{background:#fee4e2;border-color:#fda29b}
.silpj-doc-locked{color:#667085;background:#f8fafc;border-color:#e2e8f0}.silpj-doc-locked:hover{background:#eef2f6}
.silpj-doc-action:focus-visible,.silpj-modal button:focus-visible,.silpj-modal a:focus-visible{outline:3px solid #93c5fd;outline-offset:3px}
.silpj-modal{position:fixed;inset:0;margin:auto;width:calc(100% - 32px);max-width:460px;max-height:90vh;overflow:auto;padding:0;border:0;border-radius:18px;background:white;color:#172033;box-shadow:0 24px 80px #10182840;font:14px/1.5 Arial,sans-serif;text-align:left}
.silpj-modal::backdrop{background:rgba(15,23,42,.5)}.silpj-modal-body{padding:28px}.silpj-modal h2{margin:14px 0 8px;font-size:22px;line-height:1.3;color:#172033}.silpj-modal p{margin:0 0 18px;color:#667085;font-size:14px;line-height:1.6}
.silpj-modal-symbol{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;background:#fff0ee;color:#b42318;font-size:24px}.silpj-modal-symbol.lock{background:#f1f5f9;color:#64748b}
.silpj-modal label{display:block;margin:0 0 8px;font-weight:700;font-size:13px}.silpj-modal textarea{display:block;box-sizing:border-box;width:100%;min-height:94px;resize:vertical;padding:12px;border:1px solid #cbd5e1;border-radius:9px;font:14px/1.5 Arial,sans-serif;background:white;color:#172033}.silpj-modal textarea:focus{outline:2px solid #93c5fd;outline-offset:1px}
.silpj-modal small{display:block;color:#667085;margin-top:6px}.silpj-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:18px 28px;border-top:1px solid #edf0f4;background:#fafbfc}
.silpj-modal-footer button,.silpj-modal-footer a{display:inline-flex;align-items:center;justify-content:center;border:1px solid #d0d5dd;background:#fff;color:#344054;border-radius:9px;padding:10px 16px;font:700 13px/1.3 Arial,sans-serif;text-decoration:none;cursor:pointer}.silpj-modal-footer .danger{color:white;background:#b42318;border-color:#b42318}.silpj-modal-footer .primary{color:white;background:#1d4ed8;border-color:#1d4ed8}.silpj-modal-footer button:disabled{opacity:.6;cursor:wait}
@media print{.silpj-doc-action,.silpj-modal{display:none!important}}
</style>
<dialog id="silpj-delete-dialog" class="silpj-modal no-print" aria-labelledby="silpj-delete-title" aria-describedby="silpj-delete-description">
    <form method="POST" id="silpj-delete-form">
        @csrf
        @method('DELETE')
        <input type="hidden" name="confirm_delete" value="1">
        <div class="silpj-modal-body">
            <span class="silpj-modal-symbol" aria-hidden="true">!</span>
            <h2 id="silpj-delete-title">Hapus dokumen?</h2>
            <p id="silpj-delete-description"></p>
            <label for="silpj-delete-reason">Alasan penghapusan</label>
            <textarea id="silpj-delete-reason" name="reason" required minlength="5" maxlength="2000" placeholder="Contoh: dokumen dibuat untuk transaksi yang keliru" aria-describedby="silpj-delete-hint"></textarea>
            <small id="silpj-delete-hint">Minimal 5 karakter. Alasan akan dicatat di riwayat LPJ.</small>
        </div>
        <div class="silpj-modal-footer">
            <button type="button" data-silpj-close>Batal</button>
            <button type="submit" class="danger" id="silpj-delete-submit">Ya, Hapus</button>
        </div>
    </form>
</dialog>
<dialog id="silpj-locked-dialog" class="silpj-modal no-print" aria-labelledby="silpj-locked-title" aria-describedby="silpj-locked-description">
    <div class="silpj-modal-body">
        <span class="silpj-modal-symbol lock" aria-hidden="true">&#128274;</span>
        <h2 id="silpj-locked-title">Dokumen terkunci</h2>
        <p id="silpj-locked-description">LPJ sedang diajukan atau sudah disetujui. Minta pemeriksa mengembalikan LPJ untuk revisi agar dokumen bisa diubah atau dihapus.</p>
    </div>
    <div class="silpj-modal-footer">
        <button type="button" data-silpj-close>Tutup</button>
        <a id="silpj-review-link" class="primary">Lihat Pemeriksaan</a>
    </div>
</dialog>
<script>
(() => {
    const deleteDialog = document.getElementById('silpj-delete-dialog');
    const lockedDialog = document.getElementById('silpj-locked-dialog');
    const form = document.getElementById('silpj-delete-form');
    const submit = document.getElementById('silpj-delete-submit');
    let trigger = null;
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-silpj-delete], [data-silpj-locked], [data-silpj-close]');
        if (!button) return;
        if (button.hasAttribute('data-silpj-close')) {
            button.closest('dialog').close();
            return;
        }
        trigger = button;
        if (button.hasAttribute('data-silpj-delete')) {
            form.reset();
            form.action = button.dataset.action;
            submit.disabled = false;
            submit.textContent = 'Ya, Hapus';
            document.getElementById('silpj-delete-title').textContent = 'Hapus ' + button.dataset.label + '?';
            document.getElementById('silpj-delete-description').textContent = 'Dokumen ' + button.dataset.number + ' akan dihapus. Transaksi belanja dan dokumen lain tetap tersimpan. Dokumen ini dapat dibuat ulang.';
            deleteDialog.showModal();
            document.getElementById('silpj-delete-reason').focus();
        } else {
            document.getElementById('silpj-review-link').href = button.dataset.review;
            lockedDialog.showModal();
        }
    });
    for (const dialog of [deleteDialog, lockedDialog]) {
        dialog.addEventListener('close', () => { if (trigger) trigger.focus(); });
    }
    form.addEventListener('submit', () => {
        submit.disabled = true;
        submit.textContent = 'Menghapus...';
    });
})();
</script>
