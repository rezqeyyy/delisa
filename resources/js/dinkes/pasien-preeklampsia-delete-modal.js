// resources/js/dinkes/pasien-preeklampsia-delete-modal.js

document.addEventListener("DOMContentLoaded", () => {
    const backdrop = document.getElementById("peDeleteBackdrop");
    const modal = document.getElementById("peDeleteModal");
    const nameSpan = document.getElementById("peDeletePasienName");
    const idSpan = document.getElementById("peDeleteSkriningId");

    if (!backdrop || !modal) {
        return;
    }

    let currentForm = null;

    function openModal(form, pasienName, skriningId) {
        currentForm = form;

        if (nameSpan) {
            nameSpan.textContent = pasienName || "-";
        }
        if (idSpan) {
            idSpan.textContent = skriningId || "-";
        }

        backdrop.classList.remove("hidden");
        modal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeModal() {
        backdrop.classList.add("hidden");
        modal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
        currentForm = null;
    }

    // Klik tombol Delete di tabel
    document.querySelectorAll(".pe-delete-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            const form = btn.closest("form");
            if (!form) return;

            const pasienName = btn.getAttribute("data-pasien-name") || "";
            const skriningId = btn.getAttribute("data-skrining-id") || "";

            openModal(form, pasienName, skriningId);
        });
    });

    // Tombol batal / close
    modal.querySelectorAll("[data-pe-delete-cancel]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    // Tombol konfirmasi
    const confirmBtn = modal.querySelector("[data-pe-delete-confirm]");
    if (confirmBtn) {
        confirmBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (currentForm) {
                // Tandai di sessionStorage supaya setelah reload, auto scroll ke tabel PE
                sessionStorage.setItem("scrollToPeTable", "1");
                currentForm.submit();
            }
            closeModal();
        });
    }

    // Klik backdrop untuk menutup
    backdrop.addEventListener("click", () => {
        closeModal();
    });

    // Tekan ESC untuk menutup
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.classList.contains("hidden")) {
            closeModal();
        }
    });

    // Setelah halaman reload (misalnya setelah delete), scroll otomatis ke tabel PE
    const needScroll = sessionStorage.getItem("scrollToPeTable");
    if (needScroll === "1") {
        sessionStorage.removeItem("scrollToPeTable");
        const peSection = document.querySelector("[data-pe-table-section]");
        if (peSection) {
            peSection.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }
});
