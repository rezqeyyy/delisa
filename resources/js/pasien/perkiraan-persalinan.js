document.addEventListener('DOMContentLoaded', () => {

    // Mengisi TPP (Tanggal Perkiraan Persalinan) = HPHT + 280 hari.
    const hphtEl = document.querySelector('input[name="hpht"]');
    const skriningEl = document.querySelector('input[name="tanggal_skrining"]');

    // Elemen hasil (tampilan) dan hidden (dikirm ke server)
    const tppResultEl = document.getElementById('tpp_result');
    const tppHiddenEl = document.getElementById('tpp_hidden');

    if (!hphtEl || !skriningEl || !tppResultEl || !tppHiddenEl) return;

    // Konstanta milidetik per hari untuk konversi selisih waktu
    const MS_PER_DAY = 24 * 60 * 60 * 1000;

    // Helper: parsing string input date (yyyy-mm-dd) menjadi Date
    // Mengembalikan null jika hasil parsing tidak valid.
    function parseDate(v) {
        const d = new Date(v);
        return isNaN(d) ? null : d;
    }

    // Helper: format Date -> string yyyy-mm-dd untuk diisi ke input type="date"
    function formatDateInput(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
    }

    // Fungsi utama perhitungan
    function computeTPP() {
        const lmp = parseDate(hphtEl.value);        // HPHT
        const scr = parseDate(skriningEl.value);    // Tanggal skrining

        // Validasi: butuh kedua tanggal dan skrining >= HPHT
        // Jika tidak valid, kembalikan tampilan hasil ke default dan kosongkan hidden (agar tidak terkirim angka salah).    
        if (!lmp || !scr || scr < lmp) {
            tppResultEl.value = '';
            tppHiddenEl.value = '';
            return;
        }
        // TPP = HPHT + 280 hari (40 minggu)
        const tppDate = new Date(lmp.getTime() + 280 * MS_PER_DAY);
        const tppStr = formatDateInput(tppDate);
            tppResultEl.value = tppStr;
            tppHiddenEl.value = tppStr;
    }

    // Reaktif: hitung setiap kali HPHT atau tanggal skrining berubah
    hphtEl.addEventListener('input', computeTPP);
    skriningEl.addEventListener('input', computeTPP);

    // Hitung saat awal jika ada nilai
    computeTPP();
});