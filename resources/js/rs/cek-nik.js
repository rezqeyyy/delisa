/**
 * Cek NIK - JavaScript untuk auto-fill form pasien nifas
 * 
 * Simpan file ini di: resources/js/rs/cek-nik.js
 * 
 * Lalu import di Vite atau langsung di blade dengan @vite
 */

document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.getElementById('nik');
    const btnCekNik = document.getElementById('btnCekNik');

    if (btnCekNik) {
        btnCekNik.addEventListener('click', cekNik);
    }

    if (nikInput) {
        // Hanya angka, max 16 digit
        nikInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });

        // Enter untuk cek
        nikInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                cekNik();
            }
        });
    }
});

async function cekNik() {
    const nikInput = document.getElementById('nik');
    const nik = nikInput.value.trim();
    const btnCek = document.getElementById('btnCekNik');
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    // Validasi NIK
    if (!nik) {
        showAlert('warning', 'Masukkan NIK terlebih dahulu!');
        nikInput.focus();
        return;
    }

    if (nik.length !== 16) {
        showAlert('warning', 'NIK harus 16 digit!');
        nikInput.focus();
        return;
    }

    if (!/^\d+$/.test(nik)) {
        showAlert('warning', 'NIK hanya boleh berisi angka!');
        nikInput.focus();
        return;
    }

    // Loading state
    btnCek.disabled = true;
    btnCek.innerHTML = '<svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Mencari...</span>';

    try {
        // Ambil URL dari data attribute
        const cekNikUrl = btnCek.dataset.url || '/rs/pasien-nifas/cek-nik';
        
        const response = await fetch(cekNikUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
            },
            body: JSON.stringify({ nik: nik })
        });

        const data = await response.json();

        if (data.found) {
            fillForm(data.pasien);
            showStatusRisiko(data.pasien);
            showAlert('success', 'Data pasien "' + data.pasien.nama + '" ditemukan! Form telah diisi otomatis.');
        } else {
            clearFormExceptNik();
            hideStatusRisiko();
            showAlert('info', 'NIK tidak ditemukan. Silakan isi data pasien baru.');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mencari data. Silakan coba lagi.');
    } finally {
        // Reset button
        btnCek.disabled = false;
        btnCek.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" /></svg><span>Cek</span>';
    }
}

function fillForm(pasien) {
    const fields = {
        'nama_pasien': pasien.nama || '',
        'no_telepon': pasien.no_telepon || '',
        'provinsi': pasien.provinsi || '',
        'kota': pasien.kota || '',
        'kecamatan': pasien.kecamatan || '',
        'kelurahan': pasien.kelurahan || '',
        'domisili': pasien.domisili || ''
    };

    Object.keys(fields).forEach(function(fieldId) {
        const input = document.getElementById(fieldId);
        if (input) {
            input.value = fields[fieldId];
            
            // Highlight effect
            if (fields[fieldId]) {
                input.classList.add('bg-emerald-50', 'border-emerald-300');
                setTimeout(function() {
                    input.classList.remove('bg-emerald-50', 'border-emerald-300');
                }, 3000);
            }
        }
    });

    // Isi dropdown wilayah secara berantai berdasarkan nama jika modul tersedia
    if (window.WilayahCascade && typeof window.WilayahCascade.setByNames === 'function') {
        window.WilayahCascade.setByNames({
            prov: pasien.provinsi || '',
            kab: pasien.kota || '',
            kec: pasien.kecamatan || '',
            kel: pasien.kelurahan || ''
        });
    }
}

function clearFormExceptNik() {
    const nikValue = document.getElementById('nik').value;
    const form = document.getElementById('formPasienNifas');
    if (form) {
        form.reset();
        document.getElementById('nik').value = nikValue;
    }
}

function showStatusRisiko(pasien) {
    const card = document.getElementById('statusRisikoCard');
    if (!card) return;

    const statusType = pasien.status_type || 'normal';
    const statusLabel = pasien.status_risiko || 'Tidak Berisiko';
    const hasSkrining = pasien.has_skrining || false;

    var bgColor, borderColor, textColor, icon;

    if (statusType === 'beresiko') {
        bgColor = 'bg-red-50';
        borderColor = 'border-red-200';
        textColor = 'text-red-800';
        icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>';
    } else if (statusType === 'waspada') {
        bgColor = 'bg-amber-50';
        borderColor = 'border-amber-200';
        textColor = 'text-amber-800';
        icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>';
    } else {
        bgColor = 'bg-emerald-50';
        borderColor = 'border-emerald-200';
        textColor = 'text-emerald-800';
        icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="10" /></svg>';
    }

    card.className = 'rounded-2xl border p-4 ' + bgColor + ' ' + borderColor;
    card.innerHTML = '<div class="flex items-center gap-4">' + icon + '<div><h3 class="font-semibold ' + textColor + '">Status Risiko: ' + statusLabel + '</h3><p class="text-xs ' + textColor + ' opacity-75">' + (hasSkrining ? 'Berdasarkan hasil skrining pre-eklampsia' : 'Pasien belum memiliki data skrining') + '</p></div></div>';
    card.classList.remove('hidden');
}

function hideStatusRisiko() {
    const card = document.getElementById('statusRisikoCard');
    if (card) {
        card.classList.add('hidden');
    }
}

function showAlert(type, message) {
    const nikAlert = document.getElementById('nikAlert');
    if (!nikAlert) return;

    const colors = {
        success: 'border-emerald-100 bg-emerald-50 text-emerald-800',
        error: 'border-red-100 bg-red-50 text-red-800',
        warning: 'border-amber-100 bg-amber-50 text-amber-800',
        info: 'border-blue-100 bg-blue-50 text-blue-800'
    };

    const icons = {
        success: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /><path d="M9 12l2 2 4-4" />',
        error: '<circle cx="12" cy="12" r="10" /><path d="M12 8v5" /><path d="M12 16h.01" />',
        warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />',
        info: '<circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" />'
    };

    nikAlert.className = 'flex items-start gap-2 rounded-xl border px-3 py-2 text-xs sm:text-sm ' + colors[type];
    nikAlert.innerHTML = `
        <span class="mt-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                ${icons[type]}
            </svg>
        </span>
        <span>${message}</span>
        <button type="button" class="ml-auto close-alert">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18" /><path d="M6 6l12 12" />
            </svg>
        </button>
    `;
    nikAlert.classList.remove('hidden');

    // Close button handler
    const closeBtn = nikAlert.querySelector('.close-alert');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            nikAlert.classList.add('hidden');
        });
    }

    // Auto hide
    setTimeout(function() {
        nikAlert.classList.add('hidden');
    }, 5000);
}