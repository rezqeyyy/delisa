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
        const lmp = parseDate(hphtEl.value);        // HPHT
        const scr = parseDate(skriningEl.value);    // Tanggal skrining

        // Validasi: butuh kedua tanggal dan skrining >= HPHT
        // Jika tidak valid, kembalikan tampilan hasil ke default dan kosongkan hidden (agar tidak terkirim angka salah).
        if (!lmp || !scr || scr < lmp) {
            usiaResultEl.value = defaultText;
            usiaHiddenEl.value = '';
            return;
        }

        const diffDays = Math.floor((scr.getTime() - lmp.getTime()) / MS_PER_DAY);
        const weeks = Math.floor(diffDays / 7);     // usia dalam minggu (pembulatan ke bawah)

        // Tampilkan usia kehamilan dan simpan ke hidden
        usiaResultEl.value = `${weeks} minggu`;
        usiaHiddenEl.value = String(weeks);

    }

    // Reaktif: hitung setiap kali HPHT atau tanggal skrining berubah
    hphtEl.addEventListener('input', compute);
    skriningEl.addEventListener('input', compute);

    // Hitung saat awal jika ada nilai
    compute();
});