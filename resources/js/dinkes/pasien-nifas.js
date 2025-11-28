// resources/js/dinkes/pasien-nifas.js
document.addEventListener('DOMContentLoaded', () => {
  // Konfirmasi delete
  document.querySelectorAll('form.form-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const name = form.querySelector('.btn-delete')?.dataset?.name || 'pasien';
      const ok = window.confirm(
        `Hapus ${name} dari daftar nifas? Tindakan ini tidak menghapus akun pasien.`
      );
      if (!ok) e.preventDefault();
    });
  });

  // ====== Dynamic label sort "Prioritas" berdasarkan pilihan warna ======
  const prioritySelect = document.getElementById('priority-filter');
  const sortPriorityOption = document.getElementById('sort-priority-option');

  if (prioritySelect && sortPriorityOption) {
    const LABELS = {
      '': 'Prioritas (Hitam → Hijau)',
      hitam: 'Prioritas Hitam — Terlambat',
      merah: 'Prioritas Merah — Sisa 1–3 hari',
      kuning: 'Prioritas Kuning — Sisa 4–6 hari',
      hijau: 'Prioritas Hijau — Sisa ≥ 7 hari',
    };

    const updateSortLabel = () => {
      const val = prioritySelect.value || '';
      const label = LABELS[val] || LABELS[''];
      sortPriorityOption.textContent = label;
    };

    // Inisialisasi (kalau ada value dari server)
    updateSortLabel();

    // Ubah label setiap kali pilihan warna berubah
    prioritySelect.addEventListener('change', updateSortLabel);
  }
});
