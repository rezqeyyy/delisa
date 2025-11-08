document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('div.flex.min-h-screen[x-data]') || document.querySelector('[x-data]');
    if (!root) return;

    function setState(open) {
        if (window.Alpine && root.__x && root.__x.$data && typeof root.__x.$data.openSidebar !== 'undefined') {
        root.__x.$data.openSidebar = open;
        } else {
        const mobileSidebar = document.querySelector('aside[class*="xl:hidden"]');
        const overlay = document.querySelector('div[class*="bg-black/40"][class*="fixed"]');
        if (mobileSidebar) mobileSidebar.classList.toggle('hidden', !open);
        if (overlay) overlay.classList.toggle('hidden', !open);
        }
        document.body.classList.toggle('overflow-hidden', open);
    }

    const openBtn = document.getElementById('pasienSidebarOpen');
    if (openBtn) openBtn.addEventListener('click', function () { setState(true); });

    const overlay = document.querySelector('div[class*="bg-black/40"][class*="fixed"]');
    if (overlay) overlay.addEventListener('click', function () { setState(false); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setState(false);
    });

    window.addEventListener('resize', function () {
        const isDesktop = window.innerWidth >= 1280;
        if (isDesktop) setState(false);
    });
});