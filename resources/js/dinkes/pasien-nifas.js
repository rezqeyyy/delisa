// resources/js/dinkes/pasien-nifas.js
document.addEventListener('DOMContentLoaded', () => {

  // ====== Dynamic label sort "Prioritas" + lock filter warna saat status pasien dipilih ======
  const prioritySelect = document.getElementById('priority-filter');
  const deathSelect = document.getElementById('death-filter');
  const sortPriorityOption = document.getElementById('sort-priority-option');

  if (!sortPriorityOption) return;

  // label default (kalau normal)
  const LABELS = {
    '': 'Prioritas (Hitam → Hijau)',
    hitam: 'Prioritas Hitam — Terlambat',
    merah: 'Prioritas Merah — Sisa 1–3 hari',
    kuning: 'Prioritas Kuning — Sisa 4–6 hari',
    hijau: 'Prioritas Hijau — Sisa ≥ 7 hari',
    periode: 'Prioritas Ungu — Dalam Periode KF',
    selesai: 'Prioritas Abu — Selesai',
  };

  const getDeathModeLabel = () => {
    const v = deathSelect ? (deathSelect.value || '') : '';
    if (v === 'meninggal') return 'Prioritas (Hanya Pasien Meninggal)';
    if (v === 'hidup') return 'Prioritas (Tidak Meninggal)';
    return null; // null artinya mode normal (Semua Pasien)
  };

  const applyState = () => {
    const deathLabel = getDeathModeLabel();

    // 1) Kalau death dipilih hidup/meninggal → label "Prioritas" jadi label death (override)
    if (deathLabel) {
      sortPriorityOption.textContent = deathLabel;

      // 2) lock priority filter (tidak bisa diklik)
      if (prioritySelect) {
        prioritySelect.disabled = true;

        // opsional: reset value biar nggak ngefilter warna diam-diam
        // (kalau kamu mau filter warna tetap "tersimpan", hapus 2 baris ini)
        prioritySelect.value = '';
      }

      return;
    }

    // Kalau death = semua pasien → balik normal
    if (prioritySelect) {
      prioritySelect.disabled = false;
    }

    // label normal mengikuti pilihan warna prioritas
    const val = prioritySelect ? (prioritySelect.value || '') : '';
    sortPriorityOption.textContent = LABELS[val] || LABELS[''];
  };

  // inisialisasi saat load
  applyState();

  // saat death berubah → apply state
  if (deathSelect) {
    deathSelect.addEventListener('change', applyState);
  }

  // saat priority berubah → update label (tapi hanya berpengaruh kalau death mode normal)
  if (prioritySelect) {
    prioritySelect.addEventListener('change', applyState);
  }
});
