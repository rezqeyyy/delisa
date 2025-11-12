// resources/js/dinkes/analytics-explorer.js
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnAxFilter');
  const panel = document.getElementById('axFilterPanel');
  if (!btn || !panel) return;

  let open = false;
  const close = () => { panel.classList.add('hidden'); open = false; };
  const toggle = () => { open ? close() : (panel.classList.remove('hidden'), open = true); };

  btn.addEventListener('click', (e) => { e.preventDefault(); toggle(); });
  document.addEventListener('click', (e) => {
    if (!open) return;
    if (panel.contains(e.target) || btn.contains(e.target)) return;
    close();
  });
});
