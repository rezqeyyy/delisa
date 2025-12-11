// resources/js/rs/cek-nik.js

document.addEventListener('DOMContentLoaded', function () {
    const btnCekNik = document.getElementById('btnCekNik');
    const inputNik = document.getElementById('nik');
    const nikAlert = document.getElementById('nikAlert');
    const statusRisikoCard = document.getElementById('statusRisikoCard');
    const statusRisikoWrapper = document.getElementById('statusRisikoWrapper');
    const statusRisikoSelect = document.getElementById('status_risiko_manual');

    if (!btnCekNik || !inputNik) {
        return;
    }

    btnCekNik.addEventListener('click', async function () {
        const nik = inputNik.value.trim();

        // Validasi NIK
        if (!nik || nik.length !== 16) {
            showAlert('error', 'NIK harus 16 digit!');
            return;
        }

        // Disable button & show loading
        btnCekNik.disabled = true;
        const originalHTML = btnCekNik.innerHTML;
        btnCekNik.innerHTML = `
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengecek...</span>
        `;

        try {
            const url = btnCekNik.dataset.url;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ nik })
            });

            const data = await response.json();

            if (data.found) {
                // Pasien ditemukan - auto-fill data
                showAlert('success', data.message);

                const pasien = data.pasien;

                // Fill data pasien
                document.getElementById('nama_pasien').value = pasien.nama || '';
                document.getElementById('no_telepon').value = pasien.no_telepon || '';
                document.getElementById('domisili').value = pasien.domisili || '';
                document.getElementById('tempat_lahir').value = pasien.tempat_lahir || '';
                document.getElementById('tanggal_lahir').value = pasien.tanggal_lahir || '';
                document.getElementById('rt').value = pasien.rt || '';
                document.getElementById('rw').value = pasien.rw || '';
                document.getElementById('kode_pos').value = pasien.kode_pos || '';
                document.getElementById('pekerjaan').value = pasien.pekerjaan || '';
                document.getElementById('pendidikan').value = pasien.pendidikan || '';
                document.getElementById('no_jkn').value = pasien.no_jkn || '';

                // Isi select: status perkawinan, pembiayaan kesehatan, golongan darah
                function setSelectByValueOrText(selId, val) {
                    const sel = document.getElementById(selId);
                    if (!sel) return;
                    const v = String(val || '').trim();
                    if (!v) return;
                    const opts = Array.from(sel.options);
                    let opt = opts.find(o => String(o.value).trim().toLowerCase() === v.toLowerCase());
                    if (!opt) opt = opts.find(o => o.textContent.trim().toLowerCase() === v.toLowerCase());
                    if (opt) sel.value = opt.value;
                }

                function mapStatusPerkawinan(v) {
                    const s = String(v || '').toLowerCase().trim();
                    if (!s) return '';
                    if (s.includes('kawin') || s.includes('menikah') || s === 'married') return 'Menikah';
                    if (s.includes('belum') || s.includes('single') || s.includes('tidak kawin') || s.includes('tidak menikah') || s.includes('cerai')) return 'Belum Menikah';
                    return '';
                }

                function mapPembiayaan(v) {
                    const s = String(v || '').toLowerCase().trim();
                    if (!s) return '';
                    if (s.includes('bpjs') || s.includes('jkn')) return 'BPJS Kesehatan (JKN)';
                    if (s.includes('umum') || s.includes('pribadi') || s.includes('tunai') || s.includes('cash')) return 'Pribadi';
                    if (s.includes('asuransi') || s.includes('insurance')) return 'Asuransi Lainnya';
                    return 'Asuransi Lainnya';
                }

                function mapGolongan(v) {
                    const s = String(v || '').toUpperCase().trim();
                    return ['A','B','AB','O'].includes(s) ? s : '';
                }

                setSelectByValueOrText('status_perkawinan', mapStatusPerkawinan(pasien.status_perkawinan));
                setSelectByValueOrText('pembiayaan_kesehatan', mapPembiayaan(pasien.pembiayaan_kesehatan));
                setSelectByValueOrText('golongan_darah', mapGolongan(pasien.golongan_darah));

                // Auto-fill dropdown wilayah seperti implementasi di Bidan
                if (window.WilayahCascade) {
                    window.WilayahCascade.setByNames({
                        prov: pasien.provinsi || '',
                        kab:  pasien.kota || '',
                        kec:  pasien.kecamatan || '',
                        kel:  pasien.kelurahan || ''
                    });
                } else {
                    const wilayahWrapper = document.getElementById('wilayah-wrapper');
                    if (wilayahWrapper) {
                        wilayahWrapper.setAttribute('data-prov', pasien.provinsi || '');
                        wilayahWrapper.setAttribute('data-kab',  pasien.kota || '');
                        wilayahWrapper.setAttribute('data-kec',  pasien.kecamatan || '');
                        wilayahWrapper.setAttribute('data-kel',  pasien.kelurahan || '');
                    }
                }

                // ============================================
                // LOGIC STATUS RISIKO - CEK APAKAH ADA SKRINING
                // ============================================
                if (pasien.has_skrining) {
                    // Jika pasien punya data skrining, SEMBUNYIKAN dropdown manual
                    statusRisikoWrapper.classList.add('hidden');
                    statusRisikoSelect.removeAttribute('required');
                    statusRisikoSelect.value = ''; // Clear value
                    
                    // Tampilkan card info status dari skrining
                    showStatusRisikoCard(pasien.status_type, pasien.status_risiko);
                } else {
                    // Jika pasien TIDAK punya skrining, TAMPILKAN dropdown manual
                    statusRisikoWrapper.classList.remove('hidden');
                    statusRisikoSelect.setAttribute('required', 'required');
                    
                    // Hide card status
                    if (statusRisikoCard) {
                        statusRisikoCard.classList.add('hidden');
                    }
                    
                    showAlert('info', 'Pasien belum memiliki data skrining. Silakan pilih status risiko secara manual.');
                }

            } else {
                // Pasien tidak ditemukan
                showAlert('info', data.message);
                
                // Pasien baru = belum ada skrining = WAJIB pilih status manual
                statusRisikoWrapper.classList.remove('hidden');
                statusRisikoSelect.setAttribute('required', 'required');
                
                // Hide card status
                if (statusRisikoCard) {
                    statusRisikoCard.classList.add('hidden');
                }
            }

        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat mengecek NIK: ' + error.message);
        } finally {
            // Re-enable button
            btnCekNik.disabled = false;
            btnCekNik.innerHTML = originalHTML;
        }
    });

    /**
     * Show alert notification
     */
    function showAlert(type, message) {
        const alertColors = {
            success: 'border-emerald-100 bg-emerald-50 text-emerald-800',
            error: 'border-red-100 bg-red-50 text-red-800',
            info: 'border-blue-100 bg-blue-50 text-blue-800'
        };

        const alertIcons = {
            success: `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /><path d="M9 12l2 2 4-4" />`,
            error: `<circle cx="12" cy="12" r="10" /><path d="M12 8v5" /><path d="M12 16h.01" />`,
            info: `<circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" />`
        };

        nikAlert.className = `flex items-start gap-2 rounded-xl border px-3 py-2 text-xs sm:text-sm ${alertColors[type]}`;
        nikAlert.innerHTML = `
            <span class="mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    ${alertIcons[type]}
                </svg>
            </span>
            <span>${message}</span>
        `;
        nikAlert.classList.remove('hidden');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            nikAlert.classList.add('hidden');
        }, 5000);
    }

    /**
     * Show status risiko card (jika pasien punya skrining)
     */
    function showStatusRisikoCard(statusType, statusLabel) {
        if (!statusRisikoCard) return;

        const cardColors = {
            beresiko: 'border-red-200 bg-red-50',
            waspada: 'border-amber-200 bg-amber-50',
            normal: 'border-emerald-200 bg-emerald-50'
        };

        const cardIcons = {
            beresiko: `<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" />`,
            waspada: `<circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" />`,
            normal: `<path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="10" />`
        };

        const textColors = {
            beresiko: 'text-red-800',
            waspada: 'text-amber-800',
            normal: 'text-emerald-800'
        };

        const iconColors = {
            beresiko: 'text-red-500',
            waspada: 'text-amber-500',
            normal: 'text-emerald-500'
        };

        statusRisikoCard.className = `flex items-start gap-3 rounded-xl border px-4 py-3 ${cardColors[statusType]}`;
        statusRisikoCard.innerHTML = `
            <span class="mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ${iconColors[statusType]}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    ${cardIcons[statusType]}
                </svg>
            </span>
            <div>
                <h3 class="text-sm font-semibold ${textColors[statusType]}">Status Risiko: ${statusLabel}</h3>
                <p class="text-xs ${textColors[statusType]} mt-1">
                    Status risiko ini diambil dari data skrining yang sudah ada. Tidak perlu memilih status risiko manual.
                </p>
            </div>
        `;
        statusRisikoCard.classList.remove('hidden');
    }
});