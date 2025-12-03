// Auto calculate MAP function
function calculateMAP() {
    const sbpInput = document.getElementById('sbp');
    const dbpInput = document.getElementById('dbp');
    const mapInput = document.getElementById('map');
    const mapStatus = document.getElementById('map-status');
    const mapStatusText = document.getElementById('map-status-text');
    
    if (!sbpInput || !dbpInput || !mapInput) return;
    
    const sbpRaw = (sbpInput.value || '').replace(',', '.');
    const dbpRaw = (dbpInput.value || '').replace(',', '.');
    const sbp = parseFloat(sbpRaw);
    const dbp = parseFloat(dbpRaw);
    
    // Validasi input
    if (isNaN(sbp) || isNaN(dbp)) {
        mapInput.value = '';
        if (mapStatus) {
            mapStatus.classList.add('hidden');
        }
        return;
    }
    
    if (sbp <= 0 || dbp <= 0) {
        mapInput.value = '';
        if (mapStatus) {
            mapStatus.classList.add('hidden');
        }
        return;
    }
    
    if (sbp < dbp) {
        mapInput.value = 'Error: SBP < DBP';
        if (mapStatus && mapStatusText) {
            mapStatusText.textContent = 'SBP harus lebih tinggi dari DBP';
            mapStatus.classList.remove('hidden');
            mapStatus.classList.add('text-red-600');
        }
        return;
    }
    
    // Hitung MAP
    const map = (sbp + (2 * dbp)) / 3;
    const roundedMap = Math.round(map * 100) / 100; // 2 decimal places
    
    // Tampilkan hasil
    mapInput.value = roundedMap.toFixed(2);
    
    // Tentukan status tekanan darah
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
    if (mapStatus && mapStatusText) {
        mapStatusText.textContent = statusText;
        mapStatus.classList.remove('hidden');
        mapStatus.className = 'mt-1 text-xs font-medium ' + statusColor;
    }
}

// Event listeners untuk perhitungan otomatis
document.addEventListener('DOMContentLoaded', function() {
    const sbpInput = document.getElementById('sbp');
    const dbpInput = document.getElementById('dbp');
    
    if (sbpInput && dbpInput) {
        // Gunakan event input untuk real-time calculation
        sbpInput.addEventListener('input', calculateMAP);
        dbpInput.addEventListener('input', calculateMAP);
        
        // Juga gunakan event change untuk backup
        sbpInput.addEventListener('change', calculateMAP);
        dbpInput.addEventListener('change', calculateMAP);
        
        // Hitung saat halaman dimuat jika ada nilai
        if (sbpInput.value || dbpInput.value) {
            calculateMAP();
        }
    }
    
    // Set max date to today
    const dateInput = document.querySelector('input[name="tanggal_kunjungan"]');
    if (dateInput) {
        dateInput.max = new Date().toISOString().split('T')[0];
    }
});

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

// Versi dengan debounce jika diperlukan
const debouncedCalculateMAP = debounce(calculateMAP, 300);

// Alternatif: tambahkan event listeners dengan debounce
document.addEventListener('DOMContentLoaded', function() {
    const sbpInput = document.getElementById('sbp');
    const dbpInput = document.getElementById('dbp');
    
    if (sbpInput && dbpInput) {
        sbpInput.addEventListener('input', debouncedCalculateMAP);
        dbpInput.addEventListener('input', debouncedCalculateMAP);
    }
});











