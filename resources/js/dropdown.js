document.addEventListener('DOMContentLoaded', function() {
    const btn  = document.getElementById('profileBtn');
    const menu = document.getElementById('profileMenu');
    const wrap = document.getElementById('profileWrapper');

    if (!btn || !menu || !wrap) return; // safety check

    // buka/tutup dropdown saat tombol diklik
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    // tutup dropdown kalau klik di luar
    document.addEventListener('click', function(e) {
        if (!wrap.contains(e.target)) menu.classList.add('hidden');
    });

    // tekan Escape untuk menutup
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') menu.classList.add('hidden');
    });

    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('pasienSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const main = document.getElementById('pasienMain');

    if (!toggle || !sidebar || !main) return;

    function setOpen(open) {
        // posisi sidebar
        sidebar.classList.toggle('-translate-x-full', !open);
        sidebar.classList.toggle('translate-x-0', open);
        sidebar.classList.toggle('xl:translate-x-0', open);
        sidebar.classList.toggle('xl:-translate-x-full', !open);

        // margin konten agar header/page ikut menyesuaikan
        main.classList.toggle('xl:ml-[260px]', open);
        main.classList.toggle('xl:ml-0', !open);

        // overlay hanya untuk layar kecil
        const isXL = window.matchMedia('(min-width: 1280px)').matches;
        if (backdrop) backdrop.classList.toggle('hidden', !open || isXL);
    }

    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isClosed =
        sidebar.classList.contains('-translate-x-full') ||
        sidebar.classList.contains('xl:-translate-x-full');
        setOpen(isClosed);
    });

    backdrop?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

});
