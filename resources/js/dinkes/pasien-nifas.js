// resources/js/dinkes/pasien-nifas.js
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.form-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const name = form.querySelector('.btn-delete')?.dataset?.name || 'pasien';
      const ok = window.confirm(`Hapus ${name} dari daftar nifas? Tindakan ini tidak menghapus akun pasien.`);
      if (!ok) e.preventDefault();
    });
  });
});
