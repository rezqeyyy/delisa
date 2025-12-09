// resources/js/rs/skrining-edit.js

document.addEventListener('DOMContentLoaded', () => {
    const btnTambahObat = document.getElementById('btnTambahObat');
    const obatTableBody = document.getElementById('obatTableBody');
    const sectionResepObat = document.getElementById('sectionResepObat');
    const form = document.querySelector('form');

    if (!btnTambahObat || !obatTableBody || !sectionResepObat) {
        // Kalau elemen tidak ada (misal dipakai di halaman lain), jangan error
        return;
    }

    // Fungsi untuk validasi text-only (tidak boleh angka)
    function validateTextOnly(input) {
        const textOnlyPattern = /^[a-zA-Z\s.,\-\/]+$/;
        const value = input.value.trim();
        
        if (value && !textOnlyPattern.test(value)) {
            input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            input.classList.remove('border-[#E5E5E5]', 'focus:ring-[#E91E8C]/30', 'focus:border-[#E91E8C]');
            
            // Tampilkan pesan error jika belum ada
            let errorMsg = input.parentElement.querySelector('.error-message');
            if (!errorMsg) {
                errorMsg = document.createElement('p');
                errorMsg.className = 'text-[10px] text-red-500 mt-1 error-message';
                errorMsg.textContent = 'Hanya boleh diisi dengan huruf, spasi, dan tanda baca (. , - /)';
                input.parentElement.appendChild(errorMsg);
            }
            errorMsg.classList.remove('hidden');
            return false;
        } else {
            input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            input.classList.add('border-[#E5E5E5]', 'focus:ring-[#E91E8C]/30', 'focus:border-[#E91E8C]');
            
            const errorMsg = input.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.classList.add('hidden');
            }
            return true;
        }
    }

    // Attach validasi ke input penggunaan yang sudah ada
    function attachValidationToExisting() {
        const existingPenggunaanInputs = obatTableBody.querySelectorAll('input[name^="penggunaan"]');
        existingPenggunaanInputs.forEach(input => {
            input.setAttribute('data-text-only', 'true');
            input.addEventListener('input', function() {
                validateTextOnly(this);
            });
        });
    }

    // Jalankan saat halaman load
    attachValidationToExisting();

    // Handler klik tombol "Tambah Obat"
    btnTambahObat.addEventListener('click', () => {
        // Hapus baris "empty" kalau masih ada
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) {
            emptyRow.remove();
        }

        let nextIndex = parseInt(sectionResepObat.dataset.nextIndex || '0', 10);

        const newRow = document.createElement('tr');
        newRow.className = 'bg-white hover:bg-[#FAFAFA] obat-row';
        newRow.innerHTML = `
            <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                <input type="text" name="resep_obat[${nextIndex}]"
                    placeholder="Nama obat..."
                    required
                    class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] font-medium placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]"
                    autofocus>
            </td>
            <td class="px-3 sm:px-4 py-2.5 align-top">
                <input type="text" name="dosis[${nextIndex}]"
                    placeholder="Masukkan dosis..."
                    required
                    class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
            </td>
            <td class="px-3 sm:px-4 py-2.5 align-top">
                <input type="text" name="penggunaan[${nextIndex}]"
                    placeholder="Cara penggunaan..."
                    required
                    data-text-only="true"
                    class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
            </td>
            <td class="px-3 sm:px-4 py-2.5 align-top text-center">
                <button type="button"
                    data-action="hapus-obat"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[#DC2626] hover:bg-[#FEE2E2] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <line x1="10" y1="11" x2="10" y2="17"/>
                        <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                </button>
            </td>
        `;

        obatTableBody.appendChild(newRow);
        sectionResepObat.dataset.nextIndex = (nextIndex + 1).toString();

        // Attach validasi ke input penggunaan yang baru ditambahkan
        const penggunaanInput = newRow.querySelector('input[data-text-only="true"]');
        if (penggunaanInput) {
            penggunaanInput.addEventListener('input', function() {
                validateTextOnly(this);
            });
        }

        // Fokus ke input nama obat baru
        const inputNamaObat = newRow.querySelector('input[name^="resep_obat"]');
        if (inputNamaObat) {
            inputNamaObat.focus();
        }
    });

    // Event delegation untuk tombol "hapus"
    obatTableBody.addEventListener('click', (event) => {
        const target = event.target;
        const button = target.closest('[data-action="hapus-obat"]');
        if (!button) return;

        const row = button.closest('tr');
        if (!row) return;

        row.remove();

        // Kalau sudah tidak ada baris obat lagi, tampilkan kembali baris kosong
        const remainingRows = obatTableBody.querySelectorAll('.obat-row');
        if (remainingRows.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.id = 'emptyRow';
            emptyRow.className = 'bg-white';
            emptyRow.innerHTML = `
                <td colspan="4" class="px-3 sm:px-4 py-8 text-center text-[#9CA3AF]">
                    <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#E5E5E5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M10.5 20.5L5.5 15.5L15.5 5.5L20.5 10.5L10.5 20.5Z"/>
                            <path d="M8.5 12.5L12.5 8.5"/>
                            <path d="M2 22L5.5 18.5"/>
                        </svg>
                        <span class="text-xs">Belum ada resep obat. Klik "Tambah Obat" untuk menambahkan.</span>
                    </div>
                </td>
            `;
            obatTableBody.appendChild(emptyRow);
        }
    });

    // Validasi form sebelum submit
    if (form) {
        form.addEventListener('submit', (event) => {
            let isValid = true;

            // Validasi semua input penggunaan
            const penggunaanInputs = obatTableBody.querySelectorAll('input[data-text-only="true"]');
            penggunaanInputs.forEach(input => {
                if (!validateTextOnly(input)) {
                    isValid = false;
                }
            });

            // Validasi bahwa semua field obat terisi jika ada baris obat
            const obatRows = obatTableBody.querySelectorAll('.obat-row');
            obatRows.forEach(row => {
                const inputs = row.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });
            });

            if (!isValid) {
                event.preventDefault();
                
                // Scroll ke error pertama
                const firstError = form.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }

                // Tampilkan notifikasi
                alert('Mohon lengkapi semua field resep obat dengan benar.\n\nCatatan:\n- Semua field wajib diisi\n- Field "Digunakan" hanya boleh berisi huruf, spasi, dan tanda baca (. , - /)');
            }
        });
    }
});