document.addEventListener('DOMContentLoaded', () => {
    const btnOpen  = document.getElementById('sidebarOpenBtn');
    const btnClose = document.getElementById('sidebarCloseBtn');
    const sidebar  = document.getElementById('sidebar');
    const backdropId = 'pasien-sidebar-backdrop';

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
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        const bd = ensureBackdrop();
        bd.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (btnOpen)  btnOpen.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        const bd = ensureBackdrop();
        if (window.innerWidth >= 1024) {
        bd.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        } else {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        }
    });
});