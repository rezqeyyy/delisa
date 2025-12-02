document.addEventListener('DOMContentLoaded', () => {
  const btnOpen  = document.getElementById('sidebarOpenBtn');
  const btnClose = document.getElementById('sidebarCloseBtn');
  const sidebar  = document.getElementById('sidebar');
  const backdropId = 'bidan-sidebar-backdrop';
  const shiftEls = Array.from(document.querySelectorAll('.lg\\:ml-\\[260px\\], .xl\\:ml-\\[260px\\]'));

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
    if (window.innerWidth < 1024) bd.classList.remove('hidden');
    document.body.classList.toggle('overflow-hidden', window.innerWidth < 1024);
    shiftEls.forEach(el => {
      el.classList.add('lg:ml-[260px]', 'xl:ml-[260px]');
      el.classList.remove('lg:ml-0', 'xl:ml-0');
    });
    if (btnOpen) btnOpen.classList.add('hidden');
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    const bd = ensureBackdrop();
    bd.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    shiftEls.forEach(el => {
      el.classList.remove('lg:ml-[260px]', 'xl:ml-[260px]');
      el.classList.add('lg:ml-0', 'xl:ml-0');
    });
    if (btnOpen) btnOpen.classList.remove('hidden');
  }

  if (btnOpen)  btnOpen.addEventListener('click', () => {
    const isOpen = sidebar.getBoundingClientRect().left >= 0;
    if (isOpen) closeSidebar(); else openSidebar();
  });
  if (btnClose) btnClose.addEventListener('click', closeSidebar);

  document.addEventListener('click', (e) => {
    if (window.innerWidth >= 1024) return;
    const isOpen = sidebar.getBoundingClientRect().left >= 0;
    if (!isOpen) return;
    if (!sidebar.contains(e.target) && e.target.id !== 'sidebarOpenBtn') closeSidebar();
  });

  window.addEventListener('resize', () => {
    const bd = ensureBackdrop();
    if (window.innerWidth >= 1024) {
      bd.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      shiftEls.forEach(el => {
        el.classList.add('lg:ml-[260px]', 'xl:ml-[260px]');
        el.classList.remove('lg:ml-0', 'xl:ml-0');
      });
      if (btnOpen) btnOpen.classList.add('hidden');
    } else {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      shiftEls.forEach(el => {
        el.classList.remove('lg:ml-[260px]', 'xl:ml-[260px]');
        el.classList.add('lg:ml-0', 'xl:ml-0');
      });
      if (btnOpen) btnOpen.classList.remove('hidden');
    }
  });

  window.dispatchEvent(new Event('resize'));
});