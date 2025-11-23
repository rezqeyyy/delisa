// resources/js/dinkes/pe-search.js
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
  const form         = document.getElementById('peSearchForm');
  const input        = document.getElementById('peSearchInput');
  const clear        = document.getElementById('peSearchClear');
  const section      = document.getElementById('pe-table-section');
  const pePagination = document.getElementById('pePagination');

  // ====== Guard ======
  if (!form || !input) return;

  // ====== Clear button ======
  const toggleClear = () => clear && clear.classList.toggle(!(input.value && input.value.length));
  toggleClear();

  // ====== Submit helper: selalu tambahkan hash agar setelah reload auto-scroll ======
  const submitWithAnchor = () => {
    form.action = form.action.split('#')[0] + '#page-bottom';
    form.submit();
  };

  // ====== Auto-submit saat ketik (>=2 huruf) / kosong ======
  const debounced = debounce(() => {
    const v = input.value.trim();
    if (v.length === 0 || v.length >= 2) submitWithAnchor();
  }, 450);

  input.addEventListener('input', () => { toggleClear(); debounced(); });

  // ====== Submit manual (Enter/klik tombol) ======
  form.addEventListener('submit', () => {
    form.action = form.action.split('#')[0] + '#page-bottom';
  });

  // ====== Tombol clear ======
  if (clear) {
    clear.addEventListener('click', () => {
      input.value = '';
      toggleClear();
      submitWithAnchor();
    });
  }

  // ====== Dekorasi link pagination: tambahkan hash hanya saat pagination diklik ======
  // Pastikan #pePagination berisi link <a> ke ?page=…
  if (pePagination) {
    pePagination.querySelectorAll('a[href*="page="]').forEach(a => {
      a.addEventListener('click', (e) => {
        // Mutakhirkan href-nya agar menyertakan hash, baru biarkan default navigation
        a.href = a.href.split('#')[0] + '#page-bottom';
      });
    });
  }

  // ====== Auto-scroll setelah reload: hanya jika ada hash #page-bottom ======
  if (location.hash === '#page-bottom') {
    requestAnimationFrame(() => scrollToBottom());
  } else {
    // Khusus hasil pencarian (punya ?q=), boleh auto-scroll agar fokus ke tabel
    const hasQueryQ = new URLSearchParams(window.location.search).has('q');
    if (hasQueryQ) scrollToBottom();
  }

  // (opsional) jika datang dengan hash ke section tabel
  if (location.hash === '#pe-table-section' && section) {
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});
