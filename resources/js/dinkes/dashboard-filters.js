document.addEventListener('DOMContentLoaded', () => {
  const openBtn   = document.getElementById('btnPeFilter');
  const modal     = document.getElementById('peFilterModal');
  const backdrop  = document.getElementById('peFilterBackdrop');
  const closeBtns = modal ? modal.querySelectorAll('[data-close]') : [];

  const open = () => {
    if (!modal) return;
    modal.classList.remove('hidden');
    if (backdrop) backdrop.classList.remove('hidden');

    // fokus ke elemen pertama
    const first = modal.querySelector('input,select,button,textarea');
    if (first) first.focus();
  };

  const close = () => {
    if (!modal) return;
    modal.classList.add('hidden');
    if (backdrop) backdrop.classList.add('hidden');
  };

  if (openBtn) openBtn.addEventListener('click', open);
  closeBtns.forEach(b => b.addEventListener('click', close));
  if (backdrop) backdrop.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

  // --- Form filter (tanpa auto-submit) ---
  const form = document.getElementById('pe-filter-form');
  if (form) {
    const reset = form.querySelector('[data-reset]');
    if (reset) {
      reset.addEventListener('click', () => {
        // 1) Kosongkan semua input tanggal & select
        form.querySelectorAll('input[type="date"], select').forEach(el => {
          el.value = '';
        });

        // 2) Uncheck semua riwayat penyakit (supaya chip balik normal)
        form.querySelectorAll('input[name="riwayat_penyakit_ui[]"]').forEach(cb => {
          cb.checked = false;
        });

        // Kalau mau, bisa sekalian kosongkan hidden q:
        // const qInput = form.querySelector('input[name="q"]');
        // if (qInput) qInput.value = '';

        // Style chip otomatis ikut berubah karena pakai .peer & :checked
      });
    }
  }
});
