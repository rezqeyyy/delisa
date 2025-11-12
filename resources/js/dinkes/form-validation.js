// resources/js/dinkes/form-validation.js
document.addEventListener('DOMContentLoaded', () => {
  // 1) Fokus ke elemen error pertama (kalau ada)
  const firstError = document.querySelector('[aria-invalid="true"]');
  if (firstError) {
    firstError.focus({ preventScroll: false });
  }

  // 2) Cegah double submit
  document.querySelectorAll('form').forEach((form) => {
    let submitting = false;
    form.addEventListener('submit', () => {
      if (submitting) return;
      submitting = true;

      const btn = form.querySelector('button[type="submit"], button:not([type])');
      if (btn && !btn.dataset.loading) {
        btn.dataset.loading = '1';
        btn.disabled = true;
        const original = btn.innerHTML;
        btn.dataset.original = original;
        btn.innerHTML = 'Memproses…';
      }
    });
  });

  // 3) Auto dismiss flash (menyatu dengan komponen flash Anda)
  document.querySelectorAll('[data-flash]').forEach((el) => {
    const timeout = parseInt(el.getAttribute('data-timeout') || '3500', 10);
    const closer  = el.querySelector('.flash-close');
    const kill = () => el.style.display = 'none';
    if (timeout > 0) setTimeout(kill, timeout);
    if (closer) closer.addEventListener('click', kill);
  });
});
