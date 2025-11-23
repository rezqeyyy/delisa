// resources/js/dinkes/data-master-form.js

document.addEventListener('DOMContentLoaded', () => {
    // Semua form yang punya mapping kecamatan → kelurahan
    const forms = document.querySelectorAll('form[data-rs-kelurahan-map]');

    forms.forEach((form) => {
        const raw = form.getAttribute('data-rs-kelurahan-map') || '{}';

        let kelurahanMap = {};
        try {
            kelurahanMap = JSON.parse(raw);
        } catch (e) {
            console.error('data-rs-kelurahan-map bukan JSON valid', e);
            return;
        }

        const kecSelect =
            form.querySelector('#rsKecamatanCreate') ||
            form.querySelector('#rsKecamatanEdit');

        const kelSelect =
            form.querySelector('#rsKelurahanCreate') ||
            form.querySelector('#rsKelurahanEdit');

        if (!kecSelect || !kelSelect) {
            return;
        }

        const placeholderText =
            kelSelect.getAttribute('data-placeholder') || '-- Pilih Kelurahan --';

        function rebuildKelurahanOptions(kecamatan, selectedKelurahan) {
            // kosongkan dulu semua option
            while (kelSelect.firstChild) {
                kelSelect.removeChild(kelSelect.firstChild);
            }

            // tambahkan placeholder
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderText;
            kelSelect.appendChild(placeholder);

            // kalau belum pilih kecamatan, selesai di sini
            if (!kecamatan || !Object.prototype.hasOwnProperty.call(kelurahanMap, kecamatan)) {
                kelSelect.value = '';
                return;
            }

            const list = kelurahanMap[kecamatan] || [];

            list.forEach((kel) => {
                const opt = document.createElement('option');
                opt.value = kel;
                opt.textContent = kel; // label sederhana; kalau mau pakai "(Kec. xxx)" bisa diubah di sini
                if (selectedKelurahan && selectedKelurahan === kel) {
                    opt.selected = true;
                }
                kelSelect.appendChild(opt);
            });

            if (selectedKelurahan && list.includes(selectedKelurahan)) {
                kelSelect.value = selectedKelurahan;
            }
        }

        const ds = form.dataset || {};

        // Ambil nilai awal (untuk kasus old() setelah validation error)
        const initialKecamatan =
            ds.oldKecamatan && ds.oldKecamatan.length > 0
                ? ds.oldKecamatan
                : (kecSelect.value || '');

        const initialKelurahan =
            ds.oldKelurahan && ds.oldKelurahan.length > 0
                ? ds.oldKelurahan
                : (kelSelect.value || '');

        // Selalu rebuild sekali di awal
        rebuildKelurahanOptions(initialKecamatan, initialKelurahan);

        // Rebuild setiap kali kecamatan berubah
        kecSelect.addEventListener('change', () => {
            rebuildKelurahanOptions(kecSelect.value, null);
        });
    });
});
