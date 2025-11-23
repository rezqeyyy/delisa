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
});
