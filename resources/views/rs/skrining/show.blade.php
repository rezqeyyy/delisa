<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemeriksaan Pasien - DELISA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

    {{-- Print styles --}}
    <style>
        @media print {
            .print-hidden {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            main {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <div class="print-hidden">
            <x-rs.sidebar />
        </div>

        <main class="flex-1 w-full xl:ml-[260px] bg-[#FAFAFA] max-w-none min-w-0 overflow-y-auto print:ml-0">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print-hidden">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('rs.skrining.index') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 18l-6-6 6-6" />
                                </svg>
                            </span>
                            <span>Kembali</span>
                        </a>
                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate">
                                Hasil Pemeriksaan Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})
                            </h1>
                            <p class="text-xs text-[#7C7C7C]">
                                Ringkasan hasil skrining puskesmas dan pemeriksaan di RS
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Print Header (only visible when printing) --}}
                <div class="hidden print:block mb-6">
                    <div class="text-center border-b-2 border-[#E91E8C] pb-4 mb-4">
                        <h1 class="text-xl font-bold text-[#1D1D1D]">HASIL PEMERIKSAAN PASIEN</h1>
                        <p class="text-sm text-[#7C7C7C]">Sistem DeLISA - Dinas Kesehatan Kota Depok</p>
                        <p class="text-xs text-[#9CA3AF] mt-1">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>

                {{-- Alert sukses --}}
                @if (session('success'))
                    <div
                        class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800 print-hidden">
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

                {{-- Kartu: Informasi Pasien --}}
                <section
                    class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden print:shadow-none print:border-gray-300">
                    <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-[#FCE7F3] flex items-center justify-center print:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#E91E8C]"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <span>Informasi Pasien</span>
                        </h2>
                    </div>

                    <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                        {{-- Nama --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Nama Lengkap
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                {{ $skrining->pasien->user->name ?? '-' }}
                            </div>
                        </div>

                        {{-- NIK --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                NIK
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                {{ $skrining->pasien->nik ?? '-' }}
                            </div>
                        </div>

                        {{-- Tanggal pemeriksaan awal --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Tanggal Pemeriksaan Awal
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                @if ($skrining->created_at)
                                    {{ $skrining->created_at->format('d F Y, H:i') }} WIB
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        {{-- Usia kehamilan --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Usia Kehamilan
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                {{ $skrining->kondisiKesehatan->usia_kehamilan ?? '-' }} minggu
                            </div>
                        </div>

                        {{-- Status awal --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Status Awal
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                @php
                                    $conclusion =
                                        $skrining->kesimpulan ?? ($skrining->status_pre_eklampsia ?? 'Normal');
                                    $badgeClass = match (strtolower($conclusion)) {
                                        'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                        'normal', 'aman' => 'bg-[#D1FAE5] text-[#059669]',
                                        'waspada', 'menengah' => 'bg-[#FEF3C7] text-[#D97706]',
                                        default => 'bg-[#F5F5F5] text-[#6B7280]',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <span>{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Kartu: Hasil Pemeriksaan di Rumah Sakit --}}
                @php
                    $kk = $skrining->kondisiKesehatan;
                    $sistol = $kk->sdp ?? null;
                    $diastol = $kk->dbp ?? null;
                    $proteinUrine = $kk->pemeriksaan_protein_urine ?? null;
                @endphp

                <section
                    class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden print:shadow-none print:border-gray-300">
                    <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                            <span>Hasil Pemeriksaan di Rumah Sakit</span>
                        </h2>
                    </div>

                    <div class="px-4 sm:px-5 py-4">
                        @if ($rujukan)
                            <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm -mx-4 sm:-mx-5">
                                {{-- Pasien Datang --}}
                                <div class="flex flex-col sm:flex-row">
                                    <div
                                        class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                        Pasien Datang
                                    </div>
                                    <div class="flex-1 px-4 sm:px-5 py-3">
                                        @if ($rujukan->pasien_datang == 1)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="m9 12 2 2 4-4" />
                                                </svg>
                                                <span>Ya</span>
                                            </span>
                                        @elseif($rujukan->pasien_datang == 0)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-[#FEE2E2] text-[#DC2626] px-3 py-1 text-[11px] font-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="m15 9-6 6" />
                                                    <path d="m9 9 6 6" />
                                                </svg>
                                                <span>Tidak</span>
                                            </span>
                                        @else
                                            <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Riwayat Tekanan Darah (diambil dari hasil skrining) --}}
                                <div class="flex flex-col sm:flex-row">
                                    <div
                                        class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                        Riwayat Tekanan Darah
                                    </div>
                                    <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                        @if ($sistol || $diastol)
                                            {{ $sistol ?? '?' }}/{{ $diastol ?? '?' }} mmHg
                                        @else
                                            <span class="text-[#9CA3AF] italic">
                                                Belum ada data tekanan darah dari hasil skrining
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Hasil Protein Urin (diambil dari hasil skrining) --}}
                                <div class="flex flex-col sm:flex-row">
                                    <div
                                        class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                        Hasil Pemeriksaan Protein Urin
                                    </div>
                                    <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                        @if ($proteinUrine)
                                            {{ $proteinUrine }}
                                        @else
                                            <span class="text-[#9CA3AF] italic">
                                                Belum ada data pemeriksaan protein urin dari hasil skrining
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Perlu Pemeriksaan Lanjutan --}}
                                <div class="flex flex-col sm:flex-row">
                                    <div
                                        class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                        Perlu Pemeriksaan Lanjutan
                                    </div>
                                    <div class="flex-1 px-4 sm:px-5 py-3">
                                        @if ($rujukan->perlu_pemeriksaan_lanjut == 1)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 text-[11px] font-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                    <line x1="12" y1="9" x2="12"
                                                        y2="13" />
                                                    <line x1="12" y1="17" x2="12.01"
                                                        y2="17" />
                                                </svg>
                                                <span>Ya</span>
                                            </span>
                                        @elseif($rujukan->perlu_pemeriksaan_lanjut == 0)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <polyline points="20,6 9,17 4,12" />
                                                </svg>
                                                <span>Tidak</span>
                                            </span>
                                        @else
                                            <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Tindakan + Anjuran Kontrol + Kunjungan Berikutnya --}}
                                @if ($riwayatRujukan)
                                    {{-- Tindakan --}}
                                    <div class="flex flex-col sm:flex-row">
                                        <div
                                            class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                            Tindakan
                                        </div>
                                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                            @if ($riwayatRujukan->tindakan)
                                                {{ $riwayatRujukan->tindakan }}
                                            @else
                                                <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Anjuran Kontrol --}}
                                    <div class="flex flex-col sm:flex-row">
                                        <div
                                            class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                            Anjuran Kontrol
                                        </div>
                                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                            @php
                                                $anjuranLabel = null;
                                                if ($riwayatRujukan->anjuran_kontrol === 'fktp') {
                                                    $anjuranLabel = 'Kontrol ke FKTP (Puskesmas/Klinik)';
                                                } elseif ($riwayatRujukan->anjuran_kontrol === 'rs') {
                                                    $anjuranLabel = 'Kontrol ke Rumah Sakit (RS)';
                                                }
                                            @endphp

                                            @if ($anjuranLabel)
                                                {{ $anjuranLabel }}
                                            @else
                                                <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Kunjungan Berikutnya --}}
                                    <div class="flex flex-col sm:flex-row">
                                        <div
                                            class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                            Kunjungan Berikutnya
                                        </div>
                                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                            @if ($riwayatRujukan->kunjungan_berikutnya)
                                                {{ $riwayatRujukan->kunjungan_berikutnya }}
                                            @else
                                                <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif



                                {{-- Catatan Riwayat Rujukan --}}
                                @if ($riwayatRujukan && $riwayatRujukan->catatan)
                                    <div class="flex flex-col sm:flex-row">
                                        <div
                                            class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                            Catatan Riwayat Rujukan
                                        </div>
                                        <div class="flex-1 px-4 sm:px-5 py-3">
                                            <div
                                                class="rounded-xl border border-[#E5E5E5] bg-[#F9FAFB] px-3 py-2 text-[11px] sm:text-xs text-[#4B4B4B] leading-relaxed">
                                                {{ $riwayatRujukan->catatan }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Catatan Rujukan (jika ada) --}}
                                @if ($rujukan->catatan_rujukan)
                                    <div class="flex flex-col sm:flex-row">
                                        <div
                                            class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                            Catatan Tambahan
                                        </div>
                                        <div class="flex-1 px-4 sm:px-5 py-3">
                                            <div
                                                class="rounded-xl border border-[#E5E5E5] bg-[#F9FAFB] px-3 py-2 text-[11px] sm:text-xs text-[#4B4B4B] leading-relaxed">
                                                {{ $rujukan->catatan_rujukan }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8 space-y-3 print-hidden">
                                <div
                                    class="mx-auto w-12 h-12 rounded-full bg-[#F5F5F5] flex items-center justify-center text-[#BDBDBD]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 11l3 3L22 4" />
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-[#1D1D1D]">
                                    Belum ada data pemeriksaan dari rumah sakit
                                </p>
                                <p class="text-xs text-[#7C7C7C] max-w-md mx-auto">
                                    Tambahkan data hasil pemeriksaan pasien di rumah sakit untuk melengkapi riwayat
                                    klinis preeklampsia.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('rs.skrining.edit', $skrining->id) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14" />
                                            <path d="M5 12h14" />
                                        </svg>
                                        <span>Tambah Data Pemeriksaan</span>
                                    </a>
                                </div>
                            </div>
                            <div class="hidden print:block text-center py-4 text-[#9CA3AF] italic">
                                Belum ada data pemeriksaan dari rumah sakit
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Kartu: Resep Obat --}}
                @if ($rujukan && $resepObats->count() > 0)
                    <section
                        class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden print:shadow-none print:border-gray-300">
                        <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                            <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                                <span
                                    class="w-8 h-8 rounded-full bg-[#ECFEFF] flex items-center justify-center print:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#0E7490]"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10.5 20.5L5.5 15.5L15.5 5.5L20.5 10.5L10.5 20.5Z" />
                                        <path d="M8.5 12.5L12.5 8.5" />
                                        <path d="M2 22L5.5 18.5" />
                                    </svg>
                                </span>
                                <span>Resep Obat</span>
                            </h2>
                        </div>

                        <div class="px-4 sm:px-5 py-4">
                            <div class="overflow-x-auto rounded-xl border border-[#E5E5E5]">
                                <table class="min-w-full text-xs sm:text-sm">
                                    <thead class="bg-[#FAFAFA] text-[#6B7280]">
                                        <tr>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                No
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Nama Obat
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Dosis
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Cara Penggunaan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#F3F3F3] bg-white">
                                        @foreach ($resepObats as $index => $resep)
                                            <tr class="hover:bg-[#FAFAFA]">
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                    <span class="font-semibold">{{ $resep->resep_obat }}</span>
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                    {{ $resep->dosis ?? '-' }}
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                    {{ $resep->penggunaan ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Kartu: Kesimpulan Skrining Awal --}}
                <section
                    class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden print:shadow-none print:border-gray-300">
                    <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-[#FEF3C7] flex items-center justify-center print:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#D97706]"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14,2 14,8 20,8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10,9 9,9 8,9" />
                                </svg>
                            </span>
                            <span>Kesimpulan Skrining Awal</span>
                        </h2>
                    </div>

                    <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                        {{-- Jumlah risiko sedang --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Jumlah Risiko Sedang
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                {{ $skrining->jumlah_resiko_sedang ?? '0' }}
                            </div>
                        </div>

                        {{-- Jumlah risiko tinggi --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Jumlah Risiko Tinggi
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                {{ $skrining->jumlah_resiko_tinggi ?? '0' }}
                            </div>
                        </div>

                        {{-- Kesimpulan --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Kesimpulan
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3">
                                @php
                                    $conclusion =
                                        $skrining->kesimpulan ?? ($skrining->status_pre_eklampsia ?? 'Normal');
                                    $badgeClass2 = match (strtolower($conclusion)) {
                                        'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                        'normal', 'aman' => 'bg-[#D1FAE5] text-[#059669]',
                                        'waspada', 'menengah' => 'bg-[#FEF3C7] text-[#D97706]',
                                        default => 'bg-[#F5F5F5] text-[#6B7280]',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass2 }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <span>{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Rekomendasi --}}
                        <div class="flex flex-col sm:flex-row">
                            <div
                                class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Rekomendasi Awal
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                {{ $skrining->rekomendasi ?? '-' }}
                            </div>
                        </div>

                        {{-- Catatan --}}
                        @if ($skrining->catatan)
                            <div class="flex flex-col sm:flex-row">
                                <div
                                    class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                    Catatan dari Puskesmas
                                </div>
                                <div class="flex-1 px-4 sm:px-5 py-3">
                                    <div
                                        class="rounded-xl border border-[#E5E5E5] bg-[#F9FAFB] px-3 py-2 text-[11px] sm:text-xs text-[#4B4B4B] leading-relaxed">
                                        {{ $skrining->catatan }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Print Footer --}}
                <div class="hidden print:block mt-8 pt-4 border-t border-gray-300">
                    <div class="flex justify-between items-end">
                        <div class="text-xs text-[#7C7C7C]">
                            <p>Dokumen ini dicetak dari sistem DeLISA</p>
                            <p>© 2025 Dinas Kesehatan Kota Depok</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-[#7C7C7C] mb-16">Depok, {{ now()->format('d F Y') }}</p>
                            <p class="text-xs font-semibold text-[#1D1D1D]">Petugas RS</p>
                            <p class="text-xs text-[#7C7C7C]">(_______________________)</p>
                        </div>
                    </div>
                </div>

                {{-- Aksi bawah --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2 print-hidden">
                    <a href="{{ route('rs.skrining.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                        <span>Kembali ke List</span>
                    </a>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        {{-- Tombol Cetak PDF --}}
                        <a href="{{ route('rs.skrining.exportPdf', $skrining->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-[#DC2626] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#DC2626] hover:bg-[#FEE2E2] w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14,2 14,8 20,8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10,9 9,9 8,9" />
                            </svg>
                            <span>Unduh PDF</span>
                        </a>

                        <a href="{{ route('rs.skrining.edit', $skrining->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            <span>Edit Data Pemeriksaan</span>
                        </a>
                    </div>
                </div>

                <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print-hidden">
                    © 2025 Dinas Kesehatan Kota Depok — DeLISA
                </footer>
            </div>
        </main>
    </div>
</body>

</html>
