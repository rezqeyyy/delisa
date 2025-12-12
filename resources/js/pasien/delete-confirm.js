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
            <div class="p-6 space-y-4 text-center">
                <h2 class="text-xl font-bold text-[#1D1D1D]">Ingin Menghapus Skrining?</h2>
                <p class="text-sm text-[#7C7C7C]">Pilih \"Ya\" untuk menghapus dan \"Batal\" untuk membatalkan</p>
                <div class="mt-2 flex items-center justify-center gap-3">
                    <button data-confirm class="px-6 py-2 rounded-full bg-[#39E93F] text-white font-semibold hover:bg-[#2AC933]">Ya</button>
                    <button data-cancel class="px-6 py-2 rounded-full bg-[#E20D0D] text-white font-semibold hover:bg-[#C10B0B]">Batal</button>
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