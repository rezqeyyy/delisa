document.addEventListener('DOMContentLoaded', () => {

    // Modul pengisian hierarki wilayah (Provinsi -> Kabupaten -> Kecamatan -> Kelurahan)
    // Fokus: memuat data bertingkat via API, menyinkronkan dengan Tom Select bila ada,
    // dan merestore pilihan dari nilai lama (OLD) saat form memiliki state sebelumnya.

    // Referensi elemen wrapper; keluar jika tidak ditemukan (tidak perlu menjalankan modul).
    const wrapper = document.getElementById('wilayah-wrapper');
    if (!wrapper) return;

    // Referensi setiap <select> untuk tingkatan wilayah.
    const provSel = document.getElementById('provinsi');
    const kabSel  = document.getElementById('kabupaten');
    const kecSel  = document.getElementById('kecamatan');
    const kelSel  = document.getElementById('kelurahan');

    // Nilai lama (OLD) untuk auto-set pilihan bila form memiliki data sebelumnya.
    // Diambil dari data-* attributes pada wrapper.
    const OLD = {
        prov: wrapper.dataset.prov || '',
        kab:  wrapper.dataset.kab  || '',
        kec:  wrapper.dataset.kec  || '',
        kel:  wrapper.dataset.kel  || '',
    };

    // Endpoint API yang digunakan untuk memuat daftar wilayah.
    // Beberapa endpoint berupa fungsi karena membutuhkan id parent.
    const API = {
        prov: wrapper.dataset.urlProvinces,
        kab:  (provId) => `${wrapper.dataset.urlRegencies}/${provId}`,
        kec:  (kabId)  => `${wrapper.dataset.urlDistricts}/${kabId}`,
        kel:  (kecId)  => `${wrapper.dataset.urlVillages}/${kecId}`,
    };


    // setLoading:
    // - Mengatur state loading pada <select> (disable + opsi placeholder).
    // - Sinkronisasi dengan Tom Select bila tersedia (clear/add options, enable/disable).
    function setLoading(sel, loading, label = 'Memuat...') {
        sel.disabled = loading;
        if (loading) {
        sel.innerHTML = `<option value="">${label}</option>`;
        if (sel.tomselect) {
            sel.tomselect.clearOptions();
            sel.tomselect.addOptions([{ value: '', text: label }]);
            sel.tomselect.clear(true);
            sel.tomselect.disable();
        }
        } else {
        if (sel.tomselect) sel.tomselect.enable();
        }
    }

    // fillSelect:
    // - Mengisi <select> dengan data dari server (id/name).
    // - Menambahkan opsi pertama sebagai placeholder.
    // - Menyetel nilai terpilih bila ada (selectedValue).
    // - Sinkronisasi opsi dengan Tom Select bila tersedia.
    function fillSelect(sel, data, selectedValue, emptyLabel = 'Pilih...') {
        const frag = document.createDocumentFragment();
        const first = document.createElement('option');
        first.value = '';
        first.textContent = emptyLabel;
        frag.appendChild(first);

        data.forEach(item => {
        const opt = document.createElement('option');
        opt.value = String(item.id ?? item.value ?? '');
        opt.textContent = item.name ?? item.text ?? '';
        frag.appendChild(opt);
        });

        sel.innerHTML = '';
        sel.appendChild(frag);
        sel.disabled = false;

        if (selectedValue) sel.value = String(selectedValue);

        // Sinkronisasi dengan Tom Select bila ada
        if (sel.tomselect) {
        const tsOptions = data.map(item => ({
            value: String(item.id ?? item.value ?? ''),
            text: item.name ?? item.text ?? ''
        }));
        sel.tomselect.clearOptions();
        sel.tomselect.addOptions([{ value: '', text: emptyLabel }, ...tsOptions]);
        if (selectedValue) sel.tomselect.setValue(String(selectedValue), true);
        else sel.tomselect.clear(true);
        }
    }

    // resetBelow:
    // - Mereset semua <select> di bawah level yang berubah ke keadaan default.
    // - Membersihkan opsi dan men-disable hingga data baru dimuat.
    // - Menjaga konsistensi tampilan Tom Select bila dipakai.
    function resetBelow(sel) {
        const order = [provSel, kabSel, kecSel, kelSel];
        const idx = order.indexOf(sel);
        for (let i = idx + 1; i < order.length; i++) {
        const s = order[i];
        s.innerHTML = '<option value="">Pilih...</option>';
        s.disabled = true;
        if (s.tomselect) {
            s.tomselect.clearOptions();
            s.tomselect.addOptions([{ value: '', text: 'Pilih...' }]);
            s.tomselect.clear(true);
            s.tomselect.disable();
        }
        }
    }

    // Inisialisasi: memuat daftar provinsi saat awal halaman dengan state loading.
    setLoading(provSel, true);
    fetch(API.prov)
        .then(r => r.json())
        .then(data => fillSelect(provSel, data, OLD.prov, 'Pilih Provinsi'))
        .catch(() => fillSelect(provSel, [], '', 'Gagal memuat provinsi'))
        .finally(() => setLoading(provSel, false));

    // Event: ketika Provinsi berubah, reset level di bawahnya dan muat Kabupaten/Kota.
    provSel.addEventListener('change', () => {
        const provId = provSel.value;
        resetBelow(provSel);
        if (!provId) return;

        setLoading(kabSel, true);
        fetch(API.kab(provId))
        .then(r => r.json())
        .then(data => fillSelect(kabSel, data, OLD.kab, 'Pilih Kota/Kabupaten'))
        .catch(() => fillSelect(kabSel, [], '', 'Gagal memuat kota/kabupaten'))
        .finally(() => setLoading(kabSel, false));
    });

    // Event: ketika Kabupaten/Kota berubah, reset level di bawahnya dan muat Kecamatan.
    kabSel.addEventListener('change', () => {
        const kabId = kabSel.value;
        resetBelow(kabSel);
        if (!kabId) return;

        setLoading(kecSel, true);
        fetch(API.kec(kabId))
        .then(r => r.json())
        .then(data => fillSelect(kecSel, data, OLD.kec, 'Pilih Kecamatan'))
        .catch(() => fillSelect(kecSel, [], '', 'Gagal memuat kecamatan'))
        .finally(() => setLoading(kecSel, false));
    });

    // Event: ketika Kecamatan berubah, reset level di bawahnya dan muat Kelurahan.
    kecSel.addEventListener('change', () => {
        const kecId = kecSel.value;
        resetBelow(kecSel);
        if (!kecId) return;

        setLoading(kelSel, true);
        fetch(API.kel(kecId))
        .then(r => r.json())
        .then(data => fillSelect(kelSel, data, OLD.kel, 'Pilih Kelurahan'))
        .catch(() => fillSelect(kelSel, [], '', 'Gagal memuat kelurahan'))
        .finally(() => setLoading(kelSel, false));
    });

    // Auto-trigger:
    // - Menjalankan event berantai untuk mengisi level di bawahnya berdasarkan nilai OLD
    //   sehingga form otomatis ter-rehydrate ke pilihan sebelumnya.
    if (OLD.prov) provSel.dispatchEvent(new Event('change'));
    if (OLD.kab)  kabSel.dispatchEvent(new Event('change'));
    if (OLD.kec)  kecSel.dispatchEvent(new Event('change'));
});