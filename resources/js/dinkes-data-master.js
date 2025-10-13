document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.dm-tab');
  const activeClasses = 'dm-tab px-4 py-2 rounded-full text-sm font-medium bg-[#B9257F] text-white';
  const inactiveClasses = 'dm-tab px-4 py-2 rounded-full text-sm font-medium bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]';

  // highlight tab aktif secara visual
  tabs.forEach(tab => {
    tab.addEventListener('mouseover', () => {
      if (!tab.classList.contains('text-white')) tab.classList.add('bg-[#F5F5F5]');
    });
    tab.addEventListener('mouseout', () => {
      if (!tab.classList.contains('text-white')) tab.className = inactiveClasses;
    });
  });

  // prevent link flicker
  tabs.forEach(tab => tab.addEventListener('click', e => {
    e.currentTarget.className = activeClasses;
  }));
});
