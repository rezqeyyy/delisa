// resources/js/dinkes/data-master-show.js
document.addEventListener('DOMContentLoaded', () => {
  const section = document.getElementById('dmPasswordSection');
  if (!section) return;

  const userId = section.dataset.userId;
  if (!userId) return;

  const key = `dm_pw_${userId}`;

  const valueWrapper = document.getElementById('dmPasswordValueWrapper');
  const valueEl = document.getElementById('dmPasswordValue');
  const infoEl = document.getElementById('dmPasswordInfo');
  const emptyInfoEl = document.getElementById('dmPasswordEmptyInfo');

  const showPassword = (pw, infoText) => {
    if (!valueWrapper || !valueEl) return;
    valueWrapper.classList.remove('hidden');
    valueEl.textContent = pw;

    if (emptyInfoEl) {
      emptyInfoEl.classList.add('hidden');
    }
    if (infoEl && infoText) {
      infoEl.textContent = infoText;
    }
  };

  // 0) Jika ada flag "clear" (reset MANUAL barusan) → hapus password acak & tampilkan pesan default
  const clearFlag = document.getElementById('dmPwClearFlag');
  if (clearFlag) {
    try {
      localStorage.removeItem(key);
    } catch (e) {
      console.error('Gagal menghapus password dari localStorage (manual reset).', e);
    }

    // Pastikan tampilan kembali ke default:
    if (valueWrapper) valueWrapper.classList.add('hidden');
    if (emptyInfoEl) emptyInfoEl.classList.remove('hidden');
    if (infoEl) infoEl.textContent = '';

    // Stop di sini: jangan tampilkan password sama sekali
    return;
  }

  // 1) Jika baru saja reset dan controller mengirim session('new_password')
  const initPw = section.dataset.initPassword;
  if (initPw) {
    try {
      localStorage.setItem(key, initPw);
    } catch (e) {
      console.error('Gagal menyimpan password ke localStorage dari show', e);
    }

    showPassword(
      initPw,
      'Password ini baru saja direset secara otomatis oleh sistem. Simpan untuk diberikan kepada petugas terkait.'
    );
    return;
  }

  // 2) Kalau tidak ada initPw, cek apakah ada password tersimpan di localStorage
  try {
    const stored = localStorage.getItem(key);
    if (stored) {
      showPassword(
        stored,
        'Password ini adalah password acak terakhir hasil reset otomatis yang tersimpan di browser ini.'
      );
      return;
    }
  } catch (e) {
    console.error('Gagal membaca password dari localStorage', e);
  }
});
