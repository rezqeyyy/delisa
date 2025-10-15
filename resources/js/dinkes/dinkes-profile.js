document.addEventListener('DOMContentLoaded', () => {
  const file     = document.getElementById('photo');
  const img      = document.getElementById('avatarPreview');
  const fallback = document.getElementById('avatarFallback');
  const btnRemove= document.getElementById('btnRemovePhoto');
  const formRm   = document.getElementById('removePhotoForm');

  // Tampilkan foto tersimpan
  if (img && img.dataset.hasSrc === '1') {
    fallback?.classList.add('hidden');
    img.classList.remove('hidden');
  }

  // Preview upload → ganti avatar default
  file?.addEventListener('change', (e) => {
    const f = e.target.files?.[0];
    if (!f) return;
    const reader = new FileReader();
    reader.onload = ev => {
      img.src = ev.target.result;
      img.classList.remove('hidden');
      fallback?.classList.add('hidden');
    };
    reader.readAsDataURL(f);
  });

  // Hapus foto (konfirmasi + submit form DELETE)
  btnRemove?.addEventListener('click', (e) => {
    e.preventDefault();
    if (confirm('Hapus foto profil?')) {
      formRm?.submit();
    }
  });
});
