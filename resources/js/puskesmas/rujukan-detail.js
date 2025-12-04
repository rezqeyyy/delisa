// resources/js/puskesmas/rujukan-detail.js

document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Tombol "Tandai Selesai"
    const btnTandaiSelesai = document.getElementById('btnTandaiSelesai');
    if (btnTandaiSelesai) {
        const updateUrl = btnTandaiSelesai.dataset.updateUrl;

        btnTandaiSelesai.addEventListener('click', async () => {
            const ok = window.confirm(
                'Apakah Anda yakin ingin menandai rujukan ini sebagai selesai?'
            );
            if (!ok) return;

            try {
                const response = await fetch(updateUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ done_status: true }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert('✓ ' + (data.message || 'Status berhasil diperbarui'));
                    window.location.reload();
                } else {
                    alert('✗ ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('✗ Gagal menghubungi server');
            }
        });
    }

    // Tombol "Cetak"
    const btnCetak = document.getElementById('btnCetakRujukan');
    if (btnCetak) {
        btnCetak.addEventListener('click', () => {
            window.print();
        });
    }
});
