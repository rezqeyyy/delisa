<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien Nifas - DELISA</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        @media print {
            .print\\:hidden {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- HEADER ATAS --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('rs.pasien-nifas.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                        <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </span>
                        <span>Kembali</span>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate">
                            Data Pasien Nifas — {{ $pasienNifas->pasien->user->name ?? 'N/A' }}
                        </h1>
                        <p class="text-xs text-[#7C7C7C]">
                            Ringkasan nifas ibu, riwayat persalinan, dan kondisi bayi
                        </p>
                    </div>
                </div>

                <a href="{{ route('rs.pasien-nifas.show', $pasienNifas->id) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    <span>Tambah Data Anak</span>
                </a>
            </div>

            {{-- ALERT SUKSES --}}
            @if (session('success'))
                <div class="alert flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @php
                $anakPertama = $pasienNifas->anakPasien->first();

                $totalAnak = $pasienNifas->anakPasien->count();
                $anakBBLR = $pasienNifas->anakPasien->filter(fn($a) => $a->berat_lahir_anak < 2.5)->count();
                $anakPreterm = $pasienNifas->anakPasien
                    ->filter(function ($a) {
                        $usia = (int) filter_var($a->usia_kehamilan_saat_lahir, FILTER_SANITIZE_NUMBER_INT);
                        return $usia < 37;
                    })
                    ->count();
                $anakRiwayat = $pasienNifas->anakPasien
                    ->filter(fn($a) => $a->riwayat_penyakit && count($a->riwayat_penyakit) > 0)
                    ->count();

                $adaResiko = $anakBBLR > 0 || $anakPreterm > 0 || $anakRiwayat > 0;
                $statusResikoText = $adaResiko
                    ? 'Terdapat faktor risiko pada sebagian anak, perlu pemantauan ketat dan kunjungan rutin.'
                    : 'Tidak ada faktor risiko berat yang terdeteksi. Kondisi anak-anak dalam batas normal.';
            @endphp

            {{-- =========================
                 1. INFORMASI PASIEN
               ========================== --}}
            <section class="bg-[#F3F3F3] rounded-3xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                        Informasi Pasien dan Data Kehamilan
                    </h2>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-[#ECECEC] overflow-hidden">
                    {{-- Header bar --}}
                    <div class="grid grid-cols-2 text-[11px] sm:text-xs font-semibold text-[#7C7C7C] bg-[#FAFAFA] border-b border-[#F0F0F0]">
                        <div class="px-4 sm:px-6 py-3 border-r border-[#F0F0F0]">
                            Informasi
                        </div>
                        <div class="px-4 sm:px-6 py-3">
                            Data
                        </div>
                    </div>

                    {{-- Rows --}}
                    <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                        {{-- Tanggal Pemeriksaan --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Tanggal Pemeriksaan
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                @if ($pasienNifas->tanggal_mulai_nifas)
                                    {{ \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->translatedFormat('d F Y') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Nama
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $pasienNifas->pasien->user->name ?? '-' }}
                            </div>
                        </div>

                        {{-- NIK --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                NIK
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $pasienNifas->pasien->nik ?? '-' }}
                            </div>
                        </div>

                        {{-- Usia Kehamilan --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Usia Kehamilan
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                @if ($anakPertama)
                                    {{ $anakPertama->usia_kehamilan_saat_lahir }} Minggu
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Alamat
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D] leading-relaxed">
                                {{ $pasienNifas->pasien->PWilayah ?? '-' }},
                                {{ $pasienNifas->pasien->PKecamatan ?? '-' }},
                                {{ $pasienNifas->pasien->PKabupaten ?? '-' }},
                                {{ $pasienNifas->pasien->PProvinsi ?? '-' }}
                            </div>
                        </div>

                        {{-- Nomor Telepon --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Nomor Telepon
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $pasienNifas->pasien->user->phone ?? ($pasienNifas->pasien->no_telepon ?? '-') }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- =========================
                 2. DATA ANAK (RINGKASAN)
               ========================== --}}
            <section class="bg-[#F3F3F3] rounded-3xl p-4 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                        Data Anak
                    </h2>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-[#ECECEC] overflow-hidden">
                    {{-- Header bar --}}
                    <div class="grid grid-cols-2 text-[11px] sm:text-xs font-semibold text-[#7C7C7C] bg-[#FAFAFA] border-b border-[#F0F0F0]">
                        <div class="px-4 sm:px-6 py-3 border-r border-[#F0F0F0]">
                            Informasi
                        </div>
                        <div class="px-4 sm:px-6 py-3">
                            Data
                        </div>
                    </div>

                    <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                        {{-- Jumlah Anak --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Jumlah Anak
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $totalAnak }}
                            </div>
                        </div>

                        {{-- Jumlah BBLR --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Jumlah BBLR (&lt; 2,5 kg)
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $anakBBLR }}
                            </div>
                        </div>

                        {{-- Jumlah Prematur --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Jumlah Prematur (&lt; 37 minggu)
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $anakPreterm }}
                            </div>
                        </div>

                        {{-- Jumlah Riwayat Komplikasi --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Jumlah dengan Riwayat Komplikasi Ibu
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $anakRiwayat }}
                            </div>
                        </div>

                        {{-- Status Risiko Anak --}}
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                Status Risiko Anak
                            </div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                <p>{{ $statusResikoText }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- TOMBOL AKSI BAWAH --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                <a href="{{ route('rs.pasien-nifas.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    <span>Kembali ke List</span>
                </a>

                @if ($pasienNifas->anakPasien->count() > 0)
                    <button type="button" onclick="window.print()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#10B981] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#059669] w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M6 9V2h12v7" />
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                            <path d="M6 14h12v8H6z" />
                        </svg>
                        <span>Cetak Data</span>
                    </button>
                @endif
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print:hidden">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    <script>
        // Auto-hide alert sukses setelah 5 detik
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>