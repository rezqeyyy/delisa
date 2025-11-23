document.addEventListener("DOMContentLoaded", () => {

    const kecSelect = document.getElementById("rsKecamatanReg");
    const kelSelect = document.getElementById("rsKelurahanReg");
    if (!kecSelect || !kelSelect) return;

    const map = kecSelect.dataset.rsKelurahanMap
        ? JSON.parse(kecSelect.dataset.rsKelurahanMap)
        : {};

    const fillKelurahan = () => {
        const kec = kecSelect.value;
        kelSelect.innerHTML = `<option value="">-- Pilih Kelurahan --</option>`;

        if (!map[kec]) return;

        map[kec].forEach(kel => {
            kelSelect.innerHTML += `<option value="${kel}">${kel}</option>`;
        });
    };

    kecSelect.addEventListener("change", fillKelurahan);

    // if user submitted previously but failed validation
    if (kecSelect.value) fillKelurahan();
});
