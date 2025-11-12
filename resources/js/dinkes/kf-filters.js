document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnKfFilter');
  const panel = document.getElementById('kfFilterPanel');
  if (!btn || !panel) return;

  const close = () => panel.classList.add('hidden');

  btn.addEventListener('click', (e) => {
    e.preventDefault();
    panel.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (panel.contains(e.target) || btn.contains(e.target)) return;
    close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
});
