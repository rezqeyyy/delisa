document.addEventListener('DOMContentLoaded', () => {

    // Menghitung usia kehamilan (minggu) dari HPHT dan tanggal skrining.
    const hphtEl = document.querySelector('input[name="hpht"]');
    const skriningEl = document.querySelector('input[name="tanggal_skrining"]');

    // Elemen hasil (tampilan) dan hidden (dikirm ke server)
    const usiaResultEl = document.getElementById('usia_kehamilan_result');
    const usiaHiddenEl = document.getElementById('usia_kehamilan_hidden');

    if (!hphtEl || !skriningEl || !usiaResultEl || !usiaHiddenEl) return;

    // Nilai default teks tampilan hasil saat input belum valid
    const defaultText = 'Akan terisi otomatis oleh sistem';

    // Konstanta milidetik per hari untuk konversi selisih waktu
    const MS_PER_DAY = 24 * 60 * 60 * 1000;

    // Helper: parsing string input date (yyyy-mm-dd) menjadi Date
    // Mengembalikan null jika hasil parsing tidak valid.
    function parseDate(v) {
        const d = new Date(v);
        return isNaN(d) ? null : d;
    }

    // Helper: format Date -> string yyyy-mm-dd untuk diisi ke input type="date"
    function compute() {
        const lmp = parseDate(hphtEl.value);
        const scr = parseDate(skriningEl.value);

        const isValid = !!lmp && !!scr && scr >= lmp;

        if (!isValid) {
            usiaResultEl.disabled = false;
            usiaResultEl.placeholder = 'Masukkan usia kehamilan (minggu)';
            if (usiaResultEl.value === defaultText || /minggu$/.test(usiaResultEl.value)) {
                usiaResultEl.value = '';
            }
            const manualWeeks = parseInt(usiaResultEl.value, 10);
            usiaHiddenEl.value = isNaN(manualWeeks) ? '' : String(manualWeeks);
            return;
        }

        const diffDays = Math.floor((scr.getTime() - lmp.getTime()) / MS_PER_DAY);
        const weeks = Math.floor(diffDays / 7);

        usiaResultEl.disabled = true;
        usiaResultEl.placeholder = '';
        usiaResultEl.value = `${weeks} minggu`;
        usiaHiddenEl.value = String(weeks);
    }

    // Reaktif: hitung setiap kali HPHT atau tanggal skrining berubah
    hphtEl.addEventListener('input', compute);
    skriningEl.addEventListener('input', compute);

    // Mode manual: saat input usia diubah, sinkronkan hidden jika HPHT tidak valid
    usiaResultEl.addEventListener('input', () => {
        const lmp = parseDate(hphtEl.value);
        const scr = parseDate(skriningEl.value);
        const isValid = !!lmp && !!scr && scr >= lmp;
        if (!isValid) {
            const manualWeeks = parseInt(usiaResultEl.value, 10);
            usiaHiddenEl.value = isNaN(manualWeeks) ? '' : String(manualWeeks);
        }
    });

    // Hitung saat awal jika ada nilai
    compute();
});