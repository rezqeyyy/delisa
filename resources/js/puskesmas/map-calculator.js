// Fungsi untuk menghitung MAP secara otomatis
export function setupMapCalculator() {
    const sbpInput = document.getElementById('sbp');
    const dbpInput = document.getElementById('dbp');
    const mapInput = document.getElementById('map');
    const mapStatus = document.getElementById('map-status');
    const mapStatusText = document.getElementById('map-status-text');
    
    // Jika elemen tidak ditemukan, keluar
    if (!sbpInput || !dbpInput || !mapInput) {
        console.log('Elemen MAP calculator tidak ditemukan');
        return;
    }
    
    // Fungsi untuk menghitung MAP
    const calculateMAP = () => {
        const sbp = parseFloat(sbpInput.value);
        const dbp = parseFloat(dbpInput.value);
        
        // Reset status
        if (mapStatus) {
            mapStatus.classList.add('hidden');
        }
        
        // Validasi input
        if (isNaN(sbp) || isNaN(dbp)) {
            mapInput.value = '';
            return;
        }
        
        if (sbp <= 0 || dbp <= 0) {
            mapInput.value = '';
            return;
        }
        
        // Validasi: SBP harus lebih tinggi dari DBP
        if (sbp < dbp) {
            mapInput.value = 'Error: SBP < DBP';
            if (mapStatus && mapStatusText) {
                mapStatusText.textContent = 'SBP harus lebih tinggi dari DBP';
                mapStatus.classList.remove('hidden');
                mapStatus.classList.remove('text-green-600', 'text-amber-600');
                mapStatus.classList.add('text-red-600');
            }
            return;
        }
        
        // Hitung MAP
        const map = (sbp + (2 * dbp)) / 3;
        const roundedMap = Math.round(map * 100) / 100; // 2 angka desimal
        
        // Tampilkan hasil
        mapInput.value = roundedMap.toFixed(2);
        
        // Tentukan status tekanan darah
        if (mapStatus && mapStatusText) {
            let statusText = '';
            let statusColor = '';
            
            if (roundedMap < 70) {
                statusText = 'Hipotensi (Tekanan darah rendah)';
                statusColor = 'text-red-600';
            } else if (roundedMap >= 70 && roundedMap <= 100) {
                statusText = 'Normal';
                statusColor = 'text-green-600';
            } else if (roundedMap > 100 && roundedMap <= 110) {
                statusText = 'Pra-Hipertensi (Perlu pemantauan)';
                statusColor = 'text-amber-600';
            } else {
                statusText = 'Hipertensi (Tekanan darah tinggi)';
                statusColor = 'text-red-600';
            }
            
            // Tampilkan status
            mapStatusText.textContent = statusText;
            mapStatus.classList.remove('hidden');
            mapStatus.className = 'mt-1 text-xs font-medium ' + statusColor;
        }
    };
    
    // Event listeners untuk perhitungan otomatis
    sbpInput.addEventListener('input', calculateMAP);
    dbpInput.addEventListener('input', calculateMAP);
    
    // Juga gunakan event change untuk backup
    sbpInput.addEventListener('change', calculateMAP);
    dbpInput.addEventListener('change', calculateMAP);
    
    // Hitung saat halaman dimuat jika ada nilai
    if (sbpInput.value || dbpInput.value) {
        calculateMAP();
    }
    
    // Debounce function untuk optimasi (opsional)
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Alternatif dengan debounce (300ms)
    const debouncedCalculateMAP = debounce(calculateMAP, 300);
    
    // Tambahkan juga event listeners dengan debounce
    sbpInput.addEventListener('input', debouncedCalculateMAP);
    dbpInput.addEventListener('input', debouncedCalculateMAP);
    
    console.log('MAP calculator berhasil diinisialisasi');
}

// Fungsi untuk set max date ke hari ini
export function setupDateRestrictions() {
    const dateInput = document.querySelector('input[name="tanggal_kunjungan"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.max = today;
        
        // Jika input kosong, set ke hari ini
        if (!dateInput.value) {
            dateInput.value = today;
        }
    }
}

// Inisialisasi semua fungsi saat DOM siap
document.addEventListener('DOMContentLoaded', function() {
    setupMapCalculator();
    setupDateRestrictions();
});