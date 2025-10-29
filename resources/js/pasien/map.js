document.addEventListener('DOMContentLoaded', () => {

    // Fungsi utama: hitung MAP dari input Sistolik (sdp) dan Diastolik (dbp)
    const sdpEl = document.querySelector('input[name="sdp"]');
    const dbpEl = document.querySelector('input[name="dbp"]');
    const resultEl = document.getElementById('map_result');
    const hiddenEl = document.getElementById('map_hidden');

    if (!sdpEl || !dbpEl || !resultEl || !hiddenEl) return;

    const defaultValue = 'Akan terisi otomatis oleh sistem';

    function compute() {
    // Baca dan parsing nilai Sistolik (SDP) dan Diastolik (DBP)
    const sdp = parseFloat(sdpEl.value);
    const dbp = parseFloat(dbpEl.value);

    // Validasi: jika salah satu input kosong/tidak angka/<=0,
    // kembalikan tampilan ke default dan kosongkan nilai yang disimpan.
    // Baris 'hiddenEl.value = ''' bertujuan mengosongkan nilai MAP agar
    // data yang dikirim tidak berisi angka yang tidak valid.
    if (!isFinite(sdp) || sdp <= 0 || !isFinite(dbp) || dbp <= 0) {
        resultEl.value = defaultValue;
        hiddenEl.value = '';
        resultEl.style.backgroundColor = '#F8FAFB';
        resultEl.style.borderColor = '#B9257F';
        resultEl.style.color = '#B9257F';
        return;
    }

    // MAP = (SDP + 2*DBP)/3
    const map = (sdp + 2 * dbp) / 3;

    // Pembulatan ke 2 desimal untuk tampilan yang rapi
    const rounded = Math.round(map * 100) / 100;

    // Tampilkan hasil ke UI dengan satuan mmHg, dan simpan
    // nilai numerik murni ke input hidden.
    resultEl.value = `${rounded.toFixed(2)} mmHg`;
    hiddenEl.value = rounded.toFixed(2);
    }

    sdpEl.addEventListener('input', compute);
    dbpEl.addEventListener('input', compute);
    compute();
    });