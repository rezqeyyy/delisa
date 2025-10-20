document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.querySelector('.js-open-skrining-modal');
  const modal = document.querySelector('.js-puskesmas-modal');
  const backdrop = document.querySelector('.js-puskesmas-modal-backdrop');

  if (!openBtn || !modal || !backdrop) return;

  const closeBtns = modal.querySelectorAll('.js-modal-close');
  const searchInput = modal.querySelector('.js-puskesmas-search');
  const listEl = modal.querySelector('.js-puskesmas-list');
  const submitBtn = modal.querySelector('.js-modal-submit');
  const createUrl = modal.dataset.createUrl;
  const searchUrl = modal.dataset.searchUrl;

  let selectedId = null;

  function openModal() {
    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
    fetchList('');
  }
  function closeModal() {
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
    selectedId = null;
    submitBtn.disabled = true;
  }

  openBtn.addEventListener('click', openModal);
  closeBtns.forEach(btn => btn.addEventListener('click', closeModal));
  backdrop.addEventListener('click', closeModal);

  async function fetchList(q) {
    try {
      const res = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`);
      const rows = await res.json();
      renderList(rows);
    } catch {
      listEl.innerHTML = '<div class="px-4 py-6 text-center text-sm text-[#7C7C7C]">Gagal memuat data</div>';
    }
  }

  function renderList(rows) {
    listEl.innerHTML = '';
    rows.forEach(row => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'w-full text-left px-4 py-2 hover:bg-[#F7F7F7] flex items-center justify-between';
      item.dataset.id = row.id;
      item.innerHTML = `
        <div>
          <div class="text-sm font-medium text-[#1D1D1D]">${row.nama_puskesmas}</div>
          <div class="text-xs text-[#7C7C7C]">${row.kecamatan ?? ''}</div>
        </div>
        <span class="text-xs text-[#B9257F]">Pilih</span>`;
      item.addEventListener('click', () => {
        selectedId = row.id;
        submitBtn.disabled = false;
        Array.from(listEl.children).forEach(el => el.classList.remove('bg-[#FFF1F7]','border','border-[#B9257F]/30'));
        item.classList.add('bg-[#FFF1F7]','border','border-[#B9257F]/30');
      });
      listEl.appendChild(item);
    });
    if (rows.length === 0) {
      listEl.innerHTML = '<div class="px-4 py-6 text-center text-sm text-[#7C7C7C]">Tidak ada hasil</div>';
    }
  }

  let t;
  searchInput.addEventListener('input', e => {
    clearTimeout(t);
    t = setTimeout(() => fetchList(e.target.value), 250);
  });

  submitBtn.addEventListener('click', () => {
    if (!selectedId) return;
    const url = `${createUrl}?puskesmas_id=${encodeURIComponent(selectedId)}`;
    window.location.href = url;
  });
});