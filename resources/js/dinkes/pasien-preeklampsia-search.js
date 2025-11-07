// resources/js/pe-search.js
const $ = (sel, root = document) => root.querySelector(sel);

function debounce(fn, wait = 400) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

async function refreshPeRowsWithAjax(form) {
  const section = document.getElementById('pe-table-section');
  const tbody   = document.getElementById('peTableBody');
  if (!section || !tbody) return false;

  const params = new URLSearchParams(new FormData(form));
  params.set('fragment', 'pe-rows');

  const url = form.action + (form.action.includes('?') ? '&' : '?') + params.toString();

  try {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const html = await res.text();
    tbody.innerHTML = html;

    // Perbarui URL (tanpa param fragment)
    params.delete('fragment');
    const cleanUrl = form.action + '?' + params.toString();
    window.history.replaceState({}, '', cleanUrl);

    // Scroll halus ke tabel (jika AJAX berhasil)
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return true;
  } catch (e) {
    console.warn('AJAX gagal, fallback reload:', e);
    return false;
  }
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

  const toggleClear = () => clear && clear.classList.toggle('hidden', !input.value?.length);
  toggleClear();

  // Auto-submit (AJAX) saat ketik (>=2 huruf) / kosong (reset)
  const debounced = debounce(async () => {
    if (input.value.length === 0 || input.value.trim().length >= 2) {
      const ok = await refreshPeRowsWithAjax(form);
      if (!ok) {
        // fallback reload + hash ke anchor bawah
        form.action = form.action.split('#')[0] + '#page-bottom';
        form.submit();
      }
    }
  }, 450);

  input.addEventListener('input', () => { toggleClear(); debounced(); });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const ok = await refreshPeRowsWithAjax(form);
    if (!ok) {
      form.action = form.action.split('#')[0] + '#page-bottom';
      form.submit();
    }
  });

  if (clear) {
    clear.addEventListener('click', async () => {
      input.value = '';
      toggleClear();
      const ok = await refreshPeRowsWithAjax(form);
      if (!ok) {
        form.action = form.action.split('#')[0] + '#page-bottom';
        form.submit();
      }
    });
  }

  // === Auto-scroll setelah reload ===
  // 1) Jika hash sudah #page-bottom => biarkan browser lompat (tetap pastikan sampai mentok).
  if (location.hash === '#page-bottom') {
    scrollToBottom();
  } else {
    // 2) Jika hash hilang namun ada query q => paksa scroll ke bawah.
    const hasQueryQ = new URLSearchParams(window.location.search).has('q');
    if (hasQueryQ) scrollToBottom();
  }

  // (opsional) jika datang dengan hash ke section tabel
  if (location.hash === '#pe-table-section' && section) {
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});
