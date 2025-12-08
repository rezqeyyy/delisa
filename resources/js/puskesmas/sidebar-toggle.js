document.addEventListener('DOMContentLoaded', () => {
    const btnOpen  = document.getElementById('sidebarOpenBtn');
    const btnClose = document.getElementById('sidebarCloseBtn');
    const sidebar  = document.getElementById('sidebar');
    const backdropId = 'puskesmas-sidebar-backdrop';

    if (!sidebar) return;

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

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');

        const bd = ensureBackdrop();
        bd.classList.remove('hidden');

        document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');

        const bd = ensureBackdrop();
        bd.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');
    }

    // === Listeners ===
    if (btnOpen)  btnOpen.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);

    // Optional: klik di luar sidebar (mobile) → tutup
    document.addEventListener('click', (e) => {
        if (window.innerWidth >= 1024) return;
        const isOpen = sidebar.getBoundingClientRect().left >= 0;
        if (!isOpen) return;

        if (!sidebar.contains(e.target) && e.target.id !== 'sidebarOpenBtn') {
            closeSidebar();
        }
    });

    // Auto state on resize (mirip Dinkes)
    window.addEventListener('resize', () => {
        const bd = ensureBackdrop();

        if (window.innerWidth >= 1024) {
            // Desktop: sidebar selalu terbuka, tidak pakai backdrop, tidak lock body
            bd.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        } else {
            // Mobile: default tertutup
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
        }
    });
});
