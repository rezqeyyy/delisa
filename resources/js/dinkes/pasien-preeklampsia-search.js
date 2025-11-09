// resources/js/pe-search.js
const $ = (sel, root = document) => root.querySelector(sel);

function debounce(fn, wait = 400) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

function scrollToBottom() {
  const bottomAnchor = document.getElementById('page-bottom');
  if (bottomAnchor) {
    bottomAnchor.scrollIntoView({ behavior: 'auto', block: 'end' });
  } else {
    window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'auto' });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const form    = document.getElementById('peSearchForm');
  const input   = document.getElementById('peSearchInput');
  const clear   = document.getElementById('peSearchClear');
  const section = document.getElementById('pe-table-section');

  if (!form || !input) return;

  const toggleClear = () => clear && clear.classList.toggle(!(input.value && input.value.length));
  toggleClear();

  // ——— Util: submit dengan anchor agar setelah reload langsung scroll ke bawah
  const submitWithAnchor = () => {
    form.action = form.action.split('#')[0] + '#page-bottom';
    form.submit();
  };

  // Auto-submit saat ketik (>=2 huruf) / kosong (reset)
  const debounced = debounce(() => {
    const v = input.value.trim();
    if (v.length === 0 || v.length >= 2) submitWithAnchor();
  }, 450);

  input.addEventListener('input', () => { toggleClear(); debounced(); });

  // Submit manual (Enter/klik tombol)
  form.addEventListener('submit', () => {
    // biarkan default submit; pastikan ada hash untuk auto-scroll setelah reload
    form.action = form.action.split('#')[0] + '#page-bottom';
  });

  // Tombol clear (jika ada)
  if (clear) {
    clear.addEventListener('click', () => {
      input.value = '';
      toggleClear();
      submitWithAnchor();
    });
  }

  // === Auto-scroll setelah reload ===
  if (location.hash === '#page-bottom') {
    // pastikan benar-benar sampai mentok
    requestAnimationFrame(() => scrollToBottom());
  } else {
    const hasQueryQ = new URLSearchParams(window.location.search).has('q');
    if (hasQueryQ) scrollToBottom();
  }

  // (opsional) jika datang dengan hash ke section tabel
  if (location.hash === '#pe-table-section' && section) {
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});
