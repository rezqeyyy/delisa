document.addEventListener("DOMContentLoaded", () => {
    const file = document.getElementById("photoInput");
    const img = document.getElementById("avatarPreview");
    const fallback = document.getElementById("avatarFallback");

    const btnRemove = document.getElementById("btnRemovePhoto");
    const formRm = document.getElementById("removePhotoForm");

    if (!img) return;

    // 1) Tampilkan foto tersimpan (kalau memang ada)
    if (img.dataset.hasSrc === "1") {
        fallback?.classList.add("hidden");
        img.classList.remove("hidden");
        btnRemove?.classList.remove("hidden");
    }

    // 2) Preview upload → ganti avatar default (tanpa submit)
    file?.addEventListener("change", (e) => {
        const f = e.target.files?.[0];
        if (!f) return;

        // Validasi ringan client-side
        const isImage =
            (f.type && f.type.startsWith("image/")) ||
            (f.name && f.name.toLowerCase().endsWith(".svg"));

        if (!isImage) return;

        const reader = new FileReader();
        reader.onload = (ev) => {
            img.src = ev.target.result;
            img.classList.remove("hidden");
            fallback?.classList.add("hidden");
            btnRemove?.classList.remove("hidden");
        };
        reader.readAsDataURL(f);
    });

    // 3) Hapus foto (konfirmasi + submit form DELETE)
    btnRemove?.addEventListener("click", (e) => {
        e.preventDefault();
        if (confirm("Hapus foto profil?")) {
            formRm?.submit();
        }
    });
});
