document.addEventListener('DOMContentLoaded', () => {
    const btnOpen  = document.getElementById('sidebarOpenBtn');
    const btnClose = document.getElementById('sidebarCloseBtn');
    const sidebar  = document.getElementById('sidebar');

    const backdropId = 'rs-sidebar-backdrop';

    // Elemen yang harus "geser" ketika sidebar open (layout utama)
    const shiftEls = Array.from(document.querySelectorAll('.lg\\:ml-\\[260px\\], .xl\\:ml-\\[260px\\]'));

    if (!sidebar) return;

    // =========================================================
    // CONFIG
    // =========================================================
    const BREAKPOINT = 1024; // sama dengan lg
    const STORAGE_KEY = 'rs_sidebar_open'; // simpan state

    // =========================================================
    // HELPERS
    // =========================================================
    function isDesktop() {
        return window.innerWidth >= BREAKPOINT;
    }

    function ensureBackdrop() {
        let bd = document.getElementById(backdropId);
        if (!bd) {
            bd = document.createElement('div');
            bd.id = backdropId;
            bd.className = 'fixed inset-0 bg-black/30 z-40 lg:hidden hidden';
            document.body.appendChild(bd);
            bd.addEventListener('click', closeSidebar);
        }
        return bd;
    }

    function setStoredState(open) {
        try {
            localStorage.setItem(STORAGE_KEY, open ? '1' : '0');
        } catch (e) {
            // kalau localStorage diblokir, kita diam saja
        }
    }

    function getStoredState() {
        try {
            const v = localStorage.getItem(STORAGE_KEY);
            if (v === null) return null; // belum ada preferensi
            return v === '1';
        } catch (e) {
            return null;
        }
    }

    function applyShift(open) {
        shiftEls.forEach(el => {
            if (open) {
                el.classList.add('lg:ml-[260px]');
                el.classList.add('xl:ml-[260px]');
                el.classList.remove('lg:ml-0');
                el.classList.remove('xl:ml-0');
            } else {
                el.classList.remove('lg:ml-[260px]');
                el.classList.remove('xl:ml-[260px]');
                el.classList.add('lg:ml-0');
                el.classList.add('xl:ml-0');
            }
        });
    }

    function syncButtons(open) {
        if (!btnOpen) return;
        // tombol open hanya tampil kalau sidebar tertutup
        btnOpen.classList.toggle('hidden', open);
    }

    // =========================================================
    // OPEN / CLOSE
    // =========================================================
    function openSidebar({ persist = true } = {}) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');

        const bd = ensureBackdrop();

        // backdrop hanya untuk mobile
        if (!isDesktop()) bd.classList.remove('hidden');
        else bd.classList.add('hidden');

        // lock scroll hanya untuk mobile
        document.body.classList.toggle('overflow-hidden', !isDesktop());

        applyShift(true);
        syncButtons(true);

        if (persist) setStoredState(true);
    }

    function closeSidebar({ persist = true } = {}) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');

        const bd = ensureBackdrop();
        bd.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');

        applyShift(false);
        syncButtons(false);

        if (persist) setStoredState(false);
    }

    function isSidebarOpen() {
        return sidebar.classList.contains('translate-x-0') && !sidebar.classList.contains('-translate-x-full');
    }

    // =========================================================
    // INIT STATE (INI YANG MENGHILANGKAN "TUTUP-BUKA" TIAP HALAMAN)
    // =========================================================
    function initSidebarState() {
        const stored = getStoredState();

        if (isDesktop()) {
            // Desktop: default OPEN kalau belum ada preferensi
            if (stored === null) openSidebar({ persist: false });
            else stored ? openSidebar({ persist: false }) : closeSidebar({ persist: false });
        } else {
            // Mobile: default CLOSED kalau belum ada preferensi
            if (stored === null) closeSidebar({ persist: false });
            else stored ? openSidebar({ persist: false }) : closeSidebar({ persist: false });
        }
    }

    initSidebarState();

    // =========================================================
    // EVENTS
    // =========================================================
    if (btnOpen) {
        btnOpen.addEventListener('click', () => {
            if (isSidebarOpen()) closeSidebar();
            else openSidebar();
        });
    }

    if (btnClose) {
        btnClose.addEventListener('click', () => closeSidebar());
    }

    // klik di luar sidebar untuk menutup (mobile saja)
    document.addEventListener('click', (e) => {
        if (isDesktop()) return;
        if (!isSidebarOpen()) return;

        // kalau klik tombol open, jangan tutup
        if (e.target && e.target.id === 'sidebarOpenBtn') return;

        if (!sidebar.contains(e.target)) {
            closeSidebar();
        }
    });

    // ESC untuk menutup (mobile)
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (isDesktop()) return;
        if (!isSidebarOpen()) return;
        closeSidebar();
    });

    // resize: jangan paksa reset yang bikin kedip,
    // cukup sesuaikan backdrop/scroll lock & shift sesuai state saat ini
    window.addEventListener('resize', () => {
        const open = isSidebarOpen();
        const bd = ensureBackdrop();

        if (isDesktop()) {
            bd.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else {
            // kalau mobile dan sidebar open, tampilkan backdrop + lock scroll
            bd.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        }

        // shift ikut state
        applyShift(open);
        syncButtons(open);
    });
});
