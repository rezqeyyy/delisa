/**
 * Modul dropdown wilayah (Provinsi → Kabupaten → Kecamatan → Kelurahan)
 * Fitur:
 * - Memuat opsi bertingkat via endpoint yang di-pasang pada attribute data-*
 * - Restore pilihan (baik tersimpan sebagai ID atau NAMA) saat halaman dimuat
 * - Saat submit form, mengirim NAMA wilayah, bukan ID
 * - Kompatibel dengan Tom Select (bila dipakai), namun bekerja normal tanpa plugin
 */

document.addEventListener('DOMContentLoaded', () => {
  // Wrapper untuk seluruh dropdown wilayah. Keluar jika tidak ada (halaman lain).
  const wrapper = document.getElementById('wilayah-wrapper');
  if (!wrapper) return;

  // Referensi dropdown
  const provSel = document.getElementById('provinsi');
  const kabSel  = document.getElementById('kabupaten');
  const kecSel  = document.getElementById('kecamatan');
  const kelSel  = document.getElementById('kelurahan');

  // Nilai sebelumnya (OLD) yang diberikan lewat attribute data-*
  // Bisa berupa ID (misal "2171") atau NAMA (misal "KOTA DEPOK").
  const OLD = {
    prov: wrapper.dataset.prov || '',
    kab:  wrapper.dataset.kab  || '',
    kec:  wrapper.dataset.kec  || '',
    kel:  wrapper.dataset.kel  || '',
  };

  // Endpoint dari attribute data-*
  // Contoh:
  // - data-url-provinces="/wilayah/provinces"
  // - data-url-regencies="/wilayah/regencies"
  // dst.
  const API = {
    prov: wrapper.dataset.urlProvinces,
    kab:  (provId) => `${wrapper.dataset.urlRegencies}/${provId}`,
    kec:  (kabId)  => `${wrapper.dataset.urlDistricts}/${kabId}`,
    kel:  (kecId)  => `${wrapper.dataset.urlVillages}/${kecId}`,
  };

  // Utility: fetch JSON dengan penanganan error sederhana
  const fetchJson = (url) =>
    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      });

  // Tampilan "loading" pada <select>
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

  // Reset semua dropdown di bawah 'sel' ke keadaan default
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

  /**
   * Pilih option berdasarkan value (ID) atau text (NAMA).
   * Mengembalikan true jika berhasil memilih.
   */
  function selectByValueOrText(sel, selectedValueOrName) {
    if (!selectedValueOrName) return false;

    const selectedStr = String(selectedValueOrName).trim();

    // 1) Coba cocokan berdasarkan value (ID)
    const byValue = Array.from(sel.options)
      .find(opt => String(opt.value).trim() === selectedStr);
    if (byValue) {
      sel.value = byValue.value;
      if (sel.tomselect) sel.tomselect.setValue(byValue.value, true);
      return true;
    }

    // 2) Fallback: cocokan berdasarkan text (NAMA)
    const byText = Array.from(sel.options)
      .find(opt => opt.textContent.trim() === selectedStr);
    if (byText) {
      // tetap set ke value (ID) agar cascade tetap bekerja
      sel.value = byText.value;
      if (sel.tomselect) sel.tomselect.setValue(byText.value, true);
      return true;
    }

    return false;
  }

  /**
   * Isi <select> dengan data { id, name } dari server,
   * lalu pilih item berdasarkan ID/NAMA yang diberikan.
   */
  function fillSelect(sel, data, selectedValueOrName, emptyLabel = 'Pilih...') {
    const frag = document.createDocumentFragment();

    const first = document.createElement('option');
    first.value = '';
    first.textContent = emptyLabel;
    frag.appendChild(first);

    data.forEach(item => {
      const opt = document.createElement('option');
      // Pastikan ID dan NAME berbentuk string
      opt.value = String(item.id ?? item.value ?? '');
      opt.textContent = item.name ?? item.text ?? '';
      frag.appendChild(opt);
    });

    sel.innerHTML = '';
    sel.appendChild(frag);
    sel.disabled = false;

    // Pilih sesuai ID atau NAMA
    selectByValueOrText(sel, selectedValueOrName);

    // Sinkron ke Tom Select bila ada
    if (sel.tomselect) {
      const tsOptions = data.map(item => ({
        value: String(item.id ?? item.value ?? ''),
        text: item.name ?? item.text ?? ''
      }));
      sel.tomselect.clearOptions();
      sel.tomselect.addOptions([{ value: '', text: emptyLabel }, ...tsOptions]);

      const currentVal = sel.value || '';
      if (currentVal) sel.tomselect.setValue(currentVal, true);
      else sel.tomselect.clear(true);
    }
  }

  /**
   * Alur load berantai untuk restore pilihan:
   * - Muat Provinsi → pilih → muat Kab/kota → pilih → muat Kecamatan → pilih → muat Kelurahan → pilih
   * Dipanggil saat halaman dimuat pertama kali.
   */
  async function restoreChain() {
    try {
      // 1) Provinsi
      setLoading(provSel, true);
      const provData = await fetchJson(API.prov).catch(() => []);
      fillSelect(provSel, provData, OLD.prov, 'Pilih Provinsi');
      setLoading(provSel, false);

      // Jika belum ada provinsi terpilih, hentikan
      const provId = provSel.value;
      resetBelow(provSel);
      if (!provId) return;

      // 2) Kabupaten/Kota
      setLoading(kabSel, true);
      const kabData = await fetchJson(API.kab(provId)).catch(() => []);
      fillSelect(kabSel, kabData, OLD.kab, 'Pilih Kota/Kabupaten');
      setLoading(kabSel, false);

      const kabId = kabSel.value;
      resetBelow(kabSel);
      if (!kabId) return;

      // 3) Kecamatan
      setLoading(kecSel, true);
      const kecData = await fetchJson(API.kec(kabId)).catch(() => []);
      fillSelect(kecSel, kecData, OLD.kec, 'Pilih Kecamatan');
      setLoading(kecSel, false);

      const kecId = kecSel.value;
      resetBelow(kecSel);
      if (!kecId) return;

      // 4) Kelurahan
      setLoading(kelSel, true);
      const kelData = await fetchJson(API.kel(kecId)).catch(() => []);
      fillSelect(kelSel, kelData, OLD.kel, 'Pilih Kelurahan');
      setLoading(kelSel, false);
    } catch (e) {
      // Jika terjadi error jaringan, cetak ke console dan tampilkan placeholder
      console.error('Gagal memulihkan pilihan wilayah:', e);
      fillSelect(provSel, [], '', 'Gagal memuat provinsi');
      fillSelect(kabSel,  [], '', 'Gagal memuat kota/kabupaten');
      fillSelect(kecSel,  [], '', 'Gagal memuat kecamatan');
      fillSelect(kelSel,  [], '', 'Gagal memuat kelurahan');
    }
  }

  // Event: berubah Provinsi → muat Kab/Kota
  provSel.addEventListener('change', async () => {
    const provId = provSel.value;
    resetBelow(provSel);
    if (!provId) return;

    setLoading(kabSel, true);
    try {
      const kabData = await fetchJson(API.kab(provId));
      fillSelect(kabSel, kabData, '', 'Pilih Kota/Kabupaten');
    } catch {
      fillSelect(kabSel, [], '', 'Gagal memuat kota/kabupaten');
    } finally {
      setLoading(kabSel, false);
    }
  });

  // Event: berubah Kab/Kota → muat Kecamatan
  kabSel.addEventListener('change', async () => {
    const kabId = kabSel.value;
    resetBelow(kabSel);
    if (!kabId) return;

    setLoading(kecSel, true);
    try {
      const kecData = await fetchJson(API.kec(kabId));
      fillSelect(kecSel, kecData, '', 'Pilih Kecamatan');
    } catch {
      fillSelect(kecSel, [], '', 'Gagal memuat kecamatan');
    } finally {
      setLoading(kecSel, false);
    }
  });

  // Event: berubah Kecamatan → muat Kelurahan
  kecSel.addEventListener('change', async () => {
    const kecId = kecSel.value;
    resetBelow(kecSel);
    if (!kecId) return;

    setLoading(kelSel, true);
    try {
      const kelData = await fetchJson(API.kel(kecId));
      fillSelect(kelSel, kelData, '', 'Pilih Kelurahan');
    } catch {
      fillSelect(kelSel, [], '', 'Gagal memuat kelurahan');
    } finally {
      setLoading(kelSel, false);
    }
  });

  // Restore dropdown saat halaman dimuat
  restoreChain();

  /**
   * Saat submit form:
   * - Ganti nilai <select> agar mengirim NAMA (text) ke server, bukan ID
   *   sehingga kolom `PProvinsi`, `PKabupaten`, `PKecamatan`, `PWilayah`
   *   menyimpan NAMA wilayah.
   * Catatan: jika ingin menyimpan ID juga, tambahkan input hidden di Blade dan isi di sini.
   */
  const form = wrapper.closest('form');
  if (form) {
    form.addEventListener('submit', () => {
      [provSel, kabSel, kecSel, kelSel].forEach(sel => {
        const opt = sel.options[sel.selectedIndex];
        if (opt) {
          // Ubah value yang terkirim menjadi NAMA (textContent)
          const selectedName = opt.textContent || '';
          // Pastikan value benar-benar menjadi nama
          opt.value = selectedName;
          sel.value = selectedName;

          // Sinkronkan ke Tom Select bila ada
          if (sel.tomselect) sel.tomselect.setValue(selectedName, true);
        }
      });
    });
  }
});