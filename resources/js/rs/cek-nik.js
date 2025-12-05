document.addEventListener('DOMContentLoaded', function() {
    const btnCekNik = document.getElementById('btnCekNik');
    const nikInput = document.getElementById('nik');
    const nikAlert = document.getElementById('nikAlert');
    const statusRisikoCard = document.getElementById('statusRisikoCard');

    if (!btnCekNik) return;

    btnCekNik.addEventListener('click', async function() {
        const nik = nikInput.value.trim();

        // Validasi NIK
        if (!nik || nik.length !== 16) {
            showAlert('error', 'NIK harus terdiri dari 16 digit');
            return;
        }

        // Disable button dan tampilkan loading
        btnCekNik.disabled = true;
        const originalHTML = btnCekNik.innerHTML;
        btnCekNik.innerHTML = `
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mencari...</span>
        `;

        try {
            const url = btnCekNik.getAttribute('data-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nik: nik })
            });

            const data = await response.json();

            if (data.found && data.pasien) {
                // Pasien ditemukan - auto-fill semua field
                autofillForm(data.pasien);
                
                // Tampilkan alert sukses
                showAlert('success', data.message);
                
                // Tampilkan card status risiko jika ada skrining
                if (data.pasien.has_skrining) {
                    showStatusRisiko(data.pasien.status_risiko, data.pasien.status_type);
                }
            } else {
                // Pasien tidak ditemukan
                showAlert('info', data.message);
                hideStatusRisiko();
            }

        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat mengecek NIK. Silakan coba lagi.');
        } finally {
            // Restore button
            btnCekNik.disabled = false;
            btnCekNik.innerHTML = originalHTML;
        }
    });

    /**
     * Auto-fill semua field form dengan data pasien
     */
    function autofillForm(pasien) {
        setFieldValue('nama_pasien', pasien.nama);
        setFieldValue('no_telepon', pasien.no_telepon);

        if (window.WilayahCascade) {
            window.WilayahCascade.setByNames({
                prov: pasien.provinsi || '',
                kab:  pasien.kota || '',
                kec:  pasien.kecamatan || '',
                kel:  pasien.kelurahan || ''
            });
        }

        setFieldValue('domisili', pasien.domisili);
        setFieldValue('rt', pasien.rt);
        setFieldValue('rw', pasien.rw);
        setFieldValue('kode_pos', pasien.kode_pos);

        function normalizeDate(v){ if(!v) return v; const s=String(v); if(s.includes('/')){const [d,m,y]=s.split('/'); if(d&&m&&y){return `${y}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;} } return s; }
        function mapStatusPerkawinanRS(v){ const t=String(v||'').toLowerCase(); if(v===1||v==='1'||t.includes('menikah')||t.includes('kawin')) return 'Kawin'; if(v===0||v==='0'||t.includes('belum')) return 'Belum Kawin'; return ''; }
        function mapPembiayaanRS(v){ const s=String(v||'').toLowerCase(); if(s.includes('bpjs')||s.includes('jkn')) return 'BPJS'; if(s.includes('pribadi')||s.includes('umum')||s.includes('tunai')) return 'UMUM'; if(s.includes('internasional')) return 'INTERNASIONAL'; if(s.includes('asuransi')) return 'LAINNYA'; return ''; }

        setFieldValue('tempat_lahir', pasien.tempat_lahir);
        setFieldValue('tanggal_lahir', normalizeDate(pasien.tanggal_lahir));
        setFieldValue('status_perkawinan', mapStatusPerkawinanRS(pasien.status_perkawinan));
        setFieldValue('pekerjaan', pasien.pekerjaan);
        setFieldValue('pendidikan', pasien.pendidikan);

        setFieldValue('pembiayaan_kesehatan', mapPembiayaanRS(pasien.pembiayaan_kesehatan));
        setFieldValue('golongan_darah', pasien.golongan_darah);
        setFieldValue('no_jkn', pasien.no_jkn);
    }

    /**
     * Set value ke field form
     */
    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        const val = (value === null || value === undefined) ? '' : String(value).trim();

        if (field.tagName === 'SELECT') {
            const opts = Array.from(field.options);
            const byValue = opts.find(opt => String(opt.value).trim().toLowerCase() === val.toLowerCase());
            const byText  = opts.find(opt => opt.textContent.trim().toLowerCase() === val.toLowerCase());
            if (byValue) field.value = byValue.value;
            else if (byText) field.value = byText.value;
            else field.value = val;
        } else {
            field.value = val;
        }

        const event = new Event('change', { bubbles: true });
        field.dispatchEvent(event);
    }

    /**
     * Tampilkan alert
     */
    function showAlert(type, message) {
        const alertTypes = {
            success: {
                bgColor: 'bg-emerald-50',
                borderColor: 'border-emerald-100',
                textColor: 'text-emerald-800',
                icon: `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        <path d="M9 12l2 2 4-4" />
                    </svg>
                `
            },
            error: {
                bgColor: 'bg-red-50',
                borderColor: 'border-red-100',
                textColor: 'text-red-800',
                icon: `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v5" />
                        <path d="M12 16h.01" />
                    </svg>
                `
            },
            info: {
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-100',
                textColor: 'text-blue-800',
                icon: `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                `
            }
        };

        const alert = alertTypes[type] || alertTypes.info;

        nikAlert.className = `flex items-start gap-2 rounded-xl border ${alert.borderColor} ${alert.bgColor} px-3 py-2 text-xs sm:text-sm ${alert.textColor}`;
        nikAlert.innerHTML = `
            <span class="mt-0.5">${alert.icon}</span>
            <span>${message}</span>
        `;
        nikAlert.classList.remove('hidden');

        // Auto hide setelah 5 detik
        setTimeout(() => {
            nikAlert.classList.add('hidden');
        }, 5000);
    }

    /**
     * Tampilkan card status risiko
     */
    function showStatusRisiko(statusLabel, statusType) {
        const statusConfig = {
            beresiko: {
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200',
                textColor: 'text-red-800',
                badgeBg: 'bg-red-100',
                badgeText: 'text-red-800'
            },
            waspada: {
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200',
                textColor: 'text-yellow-800',
                badgeBg: 'bg-yellow-100',
                badgeText: 'text-yellow-800'
            },
            normal: {
                bgColor: 'bg-emerald-50',
                borderColor: 'border-emerald-200',
                textColor: 'text-emerald-800',
                badgeBg: 'bg-emerald-100',
                badgeText: 'text-emerald-800'
            }
        };

        const config = statusConfig[statusType] || statusConfig.normal;

        statusRisikoCard.className = `rounded-xl border ${config.borderColor} ${config.bgColor} p-4`;
        statusRisikoCard.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 ${config.textColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold ${config.textColor} mb-1">Status Risiko Pasien</h3>
                    <p class="text-xs ${config.textColor} mb-2">
                        Pasien ini memiliki data skrining sebelumnya dengan status:
                    </p>
                    <span class="inline-flex items-center rounded-full ${config.badgeBg} px-3 py-1 text-xs font-medium ${config.badgeText}">
                        ${statusLabel}
                    </span>
                </div>
            </div>
        `;
        statusRisikoCard.classList.remove('hidden');
    }

    /**
     * Sembunyikan card status risiko
     */
    function hideStatusRisiko() {
        statusRisikoCard.classList.add('hidden');
    }
});