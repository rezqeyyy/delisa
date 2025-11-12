document.addEventListener('DOMContentLoaded', () => {
  const openBtn   = document.getElementById('btnPeFilter');
  const modal     = document.getElementById('peFilterModal');
  const backdrop  = document.getElementById('peFilterBackdrop');
  const closeBtns = modal ? modal.querySelectorAll('[data-close]') : [];

  const open = () => {
    if (!modal) return;
    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
    // fokus ke elemen pertama
    const first = modal.querySelector('input,select,button');
    if (first) first.focus();
  };

  const close = () => {
    if (!modal) return;
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
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
        form.querySelectorAll('input[type="date"], select').forEach(el => el.value = '');
      });
    }
  }
});
