document.addEventListener('DOMContentLoaded', () => {
    const MODAL_ID = 'delete-confirm-modal';

    function closeModal() {
        const modal = document.getElementById(MODAL_ID);
        if (modal) modal.remove();
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('keydown', onEsc);
    }

    function onEsc(e) {
        if (e.key === 'Escape') closeModal();
    }

    function openModal(form) {
        const existing = document.getElementById(MODAL_ID);
        if (existing) existing.remove();

        const wrap = document.createElement('div');
        wrap.id = MODAL_ID;
        wrap.className = 'fixed inset-0 z-50 grid place-items-center';
        wrap.innerHTML = `
        <div data-backdrop class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-xl">
            <div class="p-5 space-y-3">
                <h2 class="text-lg font-bold text-[#1D1D1D]">Hapus Data?</h2>
                <p class="text-sm text-[#7C7C7C]">Tindakan ini tidak bisa dibatalkan.</p>
                <div class="mt-4 flex items-center justify-end gap-2">
                    <button data-cancel class="px-4 py-2 rounded-full border border-[#E5E5E5] bg-white text-[#1D1D1D] hover:bg-[#F8F8F8]">Batal</button>
                    <button data-confirm class="px-4 py-2 rounded-full bg-[#E53935] text-white hover:bg-[#d32f2f]">Hapus</button>
                </div>
            </div>
        </div>
        `;

        document.body.appendChild(wrap);
        document.body.classList.add('overflow-hidden');

        const btnCancel = wrap.querySelector('[data-cancel]');
        const btnConfirm = wrap.querySelector('[data-confirm]');
        const backdrop = wrap.querySelector('[data-backdrop]');

        btnCancel.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        btnConfirm.addEventListener('click', () => { form.submit(); closeModal(); });

        document.addEventListener('keydown', onEsc);
        btnConfirm.focus();
    }

    document.querySelectorAll('.js-delete-skrining-form .js-delete-skrining-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
        const form = e.currentTarget.closest('form');
        if (form) openModal(form);
        });
    });
});