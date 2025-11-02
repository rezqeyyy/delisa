(function() {
    const btn = document.querySelector('#btnAjukanSkrining') ||
                document.querySelector('a[href*="/pasien/skrining/ajukan"]');
    if (!btn) return;

    const startUrl  = btn.dataset?.startUrl  || '/pasien/skrining/ajukan';
    const searchUrl = btn.dataset?.searchUrl || '/pasien/puskesmas/search';

    const modal = document.createElement('div');
    modal.id = 'puskesmasModal';
    modal.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-black/40';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#1D1D1D]">Pilih Puskesmas</h3>
                <button type="button" id="closePuskesmasModal" class="text-[#B9257F] hover:underline">Tutup</button>
            </div>
            <p class="text-sm text-[#6B7280]">Pilih Puskesmas tempat Anda melakukan skrining.</p>
            <div class="mt-3">
                <input id="puskesmasSearchInput" type="text" placeholder="Ketik untuk mencari puskesmas..."
                       class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]">
            </div>
            <div id="puskesmasList" class="mt-3 max-h-64 overflow-y-auto border border-[#D9D9D9] rounded-xl"></div>
            <div class="mt-4 flex justify-end">
                <button type="button" id="startSkriningBtn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-gray-400 text-white disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-block w-4 h-4 bg-white/40 rounded"></span>
                    Mulai Skrining
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    const btnClose = modal.querySelector('#closePuskesmasModal');
    const input    = modal.querySelector('#puskesmasSearchInput');
    const list     = modal.querySelector('#puskesmasList');
    const btnStart = modal.querySelector('#startSkriningBtn');

    let selectedId = null;

    function setBtnStyle(enabled) {
        // reset kelas warna
        btnStart.classList.remove('bg-gray-400','hover:bg-gray-500','bg-[#B9257F]','hover:bg-[#a31f70]');
        if (enabled) {
            btnStart.classList.add('bg-[#B9257F]','hover:bg-[#a31f70]');
            btnStart.disabled = false;
        } else {
            btnStart.classList.add('bg-gray-400'); // gaya default seperti sekarang
            btnStart.disabled = true;
        }
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        input.value = '';
        selectedId = null;
        setBtnStyle(false);
        loadOptions('');
        setTimeout(() => input.focus(), 50);
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        selectedId = null;
        btnStart.disabled = true;
        list.innerHTML = '';
    }

    async function loadOptions(q) {
        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', q || '');
            const res = await fetch(url.toString(), { method: 'GET' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            renderList(data);
        } catch {
            list.innerHTML = '<div class="p-3 text-sm text-red-600">Gagal memuat daftar puskesmas.</div>';
        }
    }

    function renderList(items) {
        if (!items?.length) {
            list.innerHTML = '<div class="p-3 text-sm text-[#6B7280]">Tidak ada hasil.</div>';
            return;
        }
        list.innerHTML = items.map(item => `
            <button type="button" data-id="${item.id}"
                    class="w-full text-left px-4 py-2 hover:bg-[#F9E5F1] border-b last:border-b-0">
                <div class="font-medium text-[#1D1D1D]">${item.nama_puskesmas}</div>
                <div class="text-xs text-[#6B7280]">Kec. ${item.kecamatan ?? '-'}</div>
            </button>
        `).join('');
        list.querySelectorAll('button[data-id]').forEach(it => {
            it.addEventListener('click', () => {
                // Tandai terpilih
                list.querySelectorAll('button[data-id]').forEach(el => {
                    el.classList.remove('bg-[#F9E5F1]', 'border', 'border-[#B9257F]/40');
                });
                it.classList.add('bg-[#F9E5F1]', 'border', 'border-[#B9257F]/40');

                // Simpan pilihan & aktifkan tombol mulai
                selectedId = it.getAttribute('data-id') || null;
                setBtnStyle(Boolean(selectedId));
            });
        });
    }

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
    });
    btnClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    let typingTimer = null;
    input.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => loadOptions(input.value.trim()), 250);
    });

    btnStart.addEventListener('click', () => {
        if (!selectedId) return;
        const target = new URL(startUrl, window.location.origin);
        target.searchParams.set('puskesmas_id', selectedId);
        window.location.href = target.toString();
    });
})();