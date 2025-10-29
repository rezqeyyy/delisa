document.addEventListener('DOMContentLoaded', () => {

    // Inisialisasi: ambil elemen input yang dipakai untuk perhitungan dan tampilan IMT
    const heightEl = document.getElementById('tinggi_badan');
    const weightEl = document.getElementById('berat_badan');
    const resultEl = document.getElementById('imt_result');
    const categoryEl = document.getElementById('imt_category');

    if (!heightEl || !weightEl || !resultEl || !categoryEl) return;

    const defaultValue = 'Akan terisi otomatis oleh sistem';

    /**
     * setAppearance(category)
     * Tujuan: Mengatur tampilan input hasil IMT (background, border, dan warna teks) sesuai kategori IMT.
     * Parameter:
     *  - category: string nama kategori IMT.
     * Efek: Memodifikasi style pada elemen `resultEl` untuk mencerminkan kategori yang dihitung.
     */
    function setAppearance(category) {
    const map = {
        'Kurus Berat': { bg: '#F1C40F', border: '#F1C40F', text: 'black' },   
        'Kurus Ringan': { bg: '#F1C40F', border: '#F1C40F', text: 'black' }, 
        'Normal': { bg: '#DAFFDE', border: '#DAFFDE', text: 'black' },       
        'Gemuk Ringan': { bg: '#DC2626', border: '#DC2626', text: 'black' },  
        'Gemuk Berat': { bg: '#DC2626', border: '#DC2626', text: 'black' }    
    };
    const style = map[category] || { bg: '#F8FAFB', border: '#B9257F' };
    resultEl.style.backgroundColor = style.bg;
    resultEl.style.borderColor = style.border;
    resultEl.style.color = style.text;
    }


    /**
     * categorize(bmi)
     * Tujuan: Menentukan kategori IMT dari nilai BMI yang dihitung.
     * Parameter:
     *  - bmi: number nilai BMI (kg/m^2).
     * Return: string kategori IMT (mis. "Normal", "Gemuk Ringan", dll).
     */
    function categorize(bmi) {
    if (bmi < 17) return 'Kurus Berat';
    if (bmi >= 17 && bmi <= 18.4) return 'Kurus Ringan';
    if (bmi >= 18.5 && bmi <= 25) return 'Normal';
    if (bmi > 25 && bmi <= 27) return 'Gemuk Ringan';
    if (bmi > 27) return 'Gemuk Berat';       
    return 'Normal';
    }

    /**
     * compute()
     * Tujuan: Membaca input tinggi dan berat, menghitung IMT, memperbarui nilai pada input IMT,
     *         menentukan kategori, mengatur tampilan sesuai kategori, serta menampilkan label kategori.
     * Detail:
     *  - Validasi: jika input invalid (kosong/<=0), kembalikan ke tampilan default.
     *  - Perhitungan: konversi tinggi cm -> meter dan gunakan rumus BMI = kg / (m^2).
     */
    function compute() {
    const hCm = parseFloat(heightEl.value);
    const wKg = parseFloat(weightEl.value);

    if (!isFinite(hCm) || hCm <= 0 || !isFinite(wKg) || wKg <= 0) {
        resultEl.value = defaultValue;
        categoryEl.classList.add('hidden');
        resultEl.style.backgroundColor = '#F8FAFB';
        resultEl.style.borderColor = '#B9257F';
        resultEl.style.color = '#B9257F';
        return;
    }

    const hM = hCm / 100;
    const bmi = wKg / (hM * hM);
    const bmiFixed = Math.round(bmi * 100) / 100;

    resultEl.value = bmiFixed.toFixed(2);

    const cat = categorize(bmiFixed);
    setAppearance(cat);

    let label = '';
    switch (cat) {
        case 'Kurus Berat':
        label = 'Kurus Berat (IMT < 17)';
        break;
        case 'Kurus Ringan':
        label = 'Kurus Ringan (IMT 17 - 18.4)';
        break;
        case 'Normal':
        label = 'IMT Normal (18.5 - 25)';
        break;
        case 'Gemuk Ringan':
        label = 'Gemuk Ringan (IMT 25 - 27)';
        break;
        case 'Gemuk Berat':
        label = 'Gemuk Berat (IMT > 27)';
        break;
    }

    categoryEl.textContent = label;
    categoryEl.classList.remove('hidden');
    }

    /**
     * Event handlers
     * Tujuan: Menghitung ulang IMT setiap kali pengguna mengubah tinggi badan atau berat badan.
     */
    heightEl.addEventListener('input', compute);
    weightEl.addEventListener('input', compute);

    // Inisialisasi saat halaman dimuat (jika ada nilai awal)
    compute();
});