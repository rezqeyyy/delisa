// resources/js/rs/skrinning-edit.js

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalTambahObat");
    const btnTambah = document.getElementById("btnTambahObat");
    const btnClose = document.getElementById("btnCloseModal");
    const btnCancel = document.getElementById("btnCancelModal");
    const btnSave = document.getElementById("btnSimpanObat");

    const inputNama = document.getElementById("inputNamaObat");
    const inputDosis = document.getElementById("inputDosisObat");
    const inputGunakan = document.getElementById("inputGunakanObat");

    const tbody = document.getElementById("obatTableBody");
    const section = document.getElementById("sectionResepObat");

    // Ambil index awal dari data-next-index (Blade), fallback ke jumlah tr
    let indexCounter = 0;
    if (section && section.dataset.nextIndex) {
        indexCounter = parseInt(section.dataset.nextIndex, 10) || 0;
    } else if (tbody) {
        indexCounter = tbody.querySelectorAll("tr").length;
    }

    function openModal() {
        if (!modal) return;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        if (inputNama) inputNama.value = "";
        if (inputDosis) inputDosis.value = "";
        if (inputGunakan) inputGunakan.value = "";
    }

    btnTambah?.addEventListener("click", openModal);
    btnClose?.addEventListener("click", closeModal);
    btnCancel?.addEventListener("click", closeModal);

    btnSave?.addEventListener("click", () => {
        if (!tbody || !inputNama || !inputDosis || !inputGunakan) return;
        if (!inputNama.value.trim()) return;

        const i = indexCounter++;

        const tr = document.createElement("tr");
        tr.className = "bg-[#F9FAFB] hover:bg-[#FAFAFA]";

        tr.innerHTML = `
        <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
            <input
                type="text"
                name="resep_obat[${i}]"
                value="${inputNama.value}"
                placeholder="Nama obat..."
                class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5
                       text-xs sm:text-sm text-[#1D1D1D] font-medium
                       placeholder:text-[#9CA3AF]
                       focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]"
            >
        </td>
        <td class="px-3 sm:px-4 py-2.5 align-top">
            <input
                type="text"
                name="dosis[${i}]"
                value="${inputDosis.value}"
                placeholder="Masukkan dosis..."
                class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5
                       text-xs sm:text-sm text-[#1D1D1D]
                       placeholder:text-[#9CA3AF]
                       focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]"
            >
        </td>
        <td class="px-3 sm:px-4 py-2.5 align-top">
            <input
                type="text"
                name="penggunaan[${i}]"
                value="${inputGunakan.value}"
                placeholder="Cara penggunaan..."
                class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5
                       text-xs sm:text-sm text-[#1D1D1D]
                       placeholder:text-[#9CA3AF]
                       focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]"
            >
        </td>
    `;

        tbody.appendChild(tr);
        closeModal();
    });
});
