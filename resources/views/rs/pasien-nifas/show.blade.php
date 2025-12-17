<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien Nifas - DELISA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            @php
                $statusType = $pasienNifas->status_type ?? 'unknown';
                $statusDisplay = $pasienNifas->status_display ?? 'Belum ada data skrining / Tidak Diketahui';
                $isBeresiko = $statusType === 'beresiko' || $statusType === 'waspada';
                $badgeClass = match ($statusType) {
                    'beresiko' => 'bg-red-100 text-red-700 border-red-200',
                    'waspada' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'unknown' => 'bg-gray-100 text-gray-700 border-gray-200',
                    default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                };

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

                // Hitung kondisi ibu
                $kondisiAman = $pasienNifas->anakPasien->where('kondisi_ibu', 'aman')->count();
                $kondisiPerluTindakLanjut = $pasienNifas->anakPasien
                    ->where('kondisi_ibu', 'perlu_tindak_lanjut')
                    ->count();
            @endphp

            {{-- HEADER ATAS --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="mb-6 flex items-center gap-3">
                    <a href="{{ route('rs.pasien-nifas.index') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-semibold text-[#1D1D1D]">
                            Data Pasien Nifas — {{ $pasienNifas->pasien->user->name ?? 'N/A' }}
                        </h1>
                        <p class="text-l text-[#7C7C7C] mt-1">
                            Ringkasan nifas ibu, riwayat persalinan, dan kondisi bayi
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Badge Status Risiko --}}
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $badgeClass }}">
                        @if ($statusType === 'beresiko')
                            {{-- ICON: Beresiko --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                        @elseif($statusType === 'waspada')
                            {{-- ICON: Waspada --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                            </svg>
                        @elseif($statusType === 'unknown')
                            {{-- ICON: Unknown / Belum ada data skrining --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9.09 9a3 3 0 0 1 5.82 1c0 2-3 2-3 4" />
                                <circle cx="12" cy="17" r="0.75" fill="currentColor" stroke="none" />
                            </svg>
                        @else
                            {{-- ICON: Tidak Beresiko --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        @endif

                        <span class="text-xs font-semibold">{{ $statusDisplay }}</span>
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
            </div>

            {{-- ALERT SUKSES --}}
            @if (session('success'))
                <div
                    class="alert flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
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

            {{-- =========================
                 1. INFORMASI PASIEN
               ========================== --}}

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Pasien dan Data Kehamilan</h2>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        <div class="p-4 text-sm font-semibold">Tanggal Pemeriksaan</div>            
                        <div class="sm:col-span-2 p-4 text-sm">
                            @if ($pasienNifas->tanggal_mulai_nifas)
                                {{ \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->translatedFormat('d F Y') }}
                            @else
                                -
                            @endif
                        </div>

                        <div class="p-4 text-sm font-semibold">Nama</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $pasienNifas->pasien->user->name ?? '-' }}
                        </div>

                        <div class="p-4 text-sm font-semibold">NIK</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $pasienNifas->pasien->nik ?? '-' }}
                        </div>

                        <div class="p-4 text-sm font-semibold">Nomor Telepon</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $pasienNifas->pasien->user->phone ?? ($pasienNifas->pasien->no_telepon ?? '-') }}
                        </div>

                        <div class="p-4 text-sm font-semibold">Status Risiko Preeklampsia</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            <span
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $statusDisplay }}
                            </span>
                        </div>

                        <div class="p-4 text-sm font-semibold">Usia Kehamilan</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            @if ($anakPertama)
                                {{ $anakPertama->usia_kehamilan_saat_lahir }} Minggu
                            @else
                                -
                            @endif
                        </div>

                        <div class="p-4 text-sm font-semibold">Alamat</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $pasienNifas->pasien->PWilayah ?? '-' }},
                            {{ $pasienNifas->pasien->PKecamatan ?? '-' }},
                            {{ $pasienNifas->pasien->PKabupaten ?? '-' }},
                            {{ $pasienNifas->pasien->PProvinsi ?? '-' }}
                        </div>  
                    </div>                  
                </div>         
            </div>

            {{-- =========================
                 2. DATA ANAK (RINGKASAN)
               ========================== --}}

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Ringkasan Data Anak</h2>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        <div class="p-4 text-sm font-semibold">Jumlah Anak</div>            
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $totalAnak }}
                        </div>

                        <div class="p-4 text-sm font-semibold">Jumlah BBLR (&lt; 2,5 kg)</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $anakBBLR }}
                        </div>

                        <div class="p-4 text-sm font-semibold">Jumlah Prematur (&lt; 37 minggu)</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $anakPreterm }}
                        </div>

                        <div class="p-4 text-sm font-semibold">Jumlah dengan Riwayat Komplikasi Ibu</div>  
                        <div class="sm:col-span-2 p-4 text-sm">
                            {{ $pasienNifas->pasien->user->phone ?? ($pasienNifas->pasien->no_telepon ?? '-') }}
                        </div>

                        @if ($isBeresiko)                            
                            <div class="p-4 text-sm font-semibold">Kondisi Ibu - Aman</div>  
                            <div class="sm:col-span-2 p-4 text-sm">
                                {{ $kondisiAman }} anak
                            </div>

                            <div class="p-4 text-sm font-semibold">Kondisi Ibu - Perlu Tindak Lanjut</div>  
                            <div class="sm:col-span-2 p-4 text-sm">
                                {{ $kondisiPerluTindakLanjut }} anak
                            </div>
                        @endif
                    </div>                  
                </div>         
            </div>            

            {{-- =========================
                 3. DETAIL SETIAP ANAK
               ========================== --}}
            @if ($pasienNifas->anakPasien->count() > 0)
                <section class="space-y-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                        Detail Data Anak
                    </h2>

                    @foreach ($pasienNifas->anakPasien as $index => $anak)
                        @php
                            $kondisiIbuClass = match ($anak->kondisi_ibu) {
                                'aman' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'perlu_tindak_lanjut' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-gray-100 text-gray-500 border-gray-200',
                            };
                            $kondisiIbuLabel = match ($anak->kondisi_ibu) {
                                'aman' => 'Aman',
                                'perlu_tindak_lanjut' => 'Perlu Tindak Lanjut',
                                default => 'Belum Diisi',
                            };

                            // ⭐ TAMBAHAN
                            $puskesmasTujuan = $puskesmasTujuanById[$anak->puskesmas_id] ?? null;
                        @endphp


                        <div class="bg-white rounded-3xl p-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-[#E91E8C]/10 flex items-center justify-center">
                                        <span class="text-sm font-bold text-[#E91E8C]">{{ $anak->anak_ke }}</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-[#1D1D1D]">
                                            {{ $anak->nama_anak ?? 'Anak ke-' . $anak->anak_ke }}
                                        </h3>
                                        <p class="text-xs text-[#7C7C7C]">
                                            {{ $anak->jenis_kelamin }} • Lahir
                                            {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Badge Kondisi Ibu (Hanya untuk Beresiko) --}}
                                @if ($isBeresiko && $anak->kondisi_ibu)
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border {{ $kondisiIbuClass }}">
                                        @if ($anak->kondisi_ibu === 'aman')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M9 12l2 2 4-4" />
                                                <circle cx="12" cy="12" r="10" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                <line x1="12" y1="9" x2="12" y2="13" />
                                                <line x1="12" y1="17" x2="12.01" y2="17" />
                                            </svg>
                                        @endif
                                        <span class="text-xs font-semibold">{{ $kondisiIbuLabel }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-white rounded-2xl shadow-sm border border-[#ECECEC] overflow-hidden">
                                <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                                    {{-- Data Anak --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4">
                                        <div>
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Berat Lahir</p>
                                            <p class="font-semibold text-[#1D1D1D]">{{ $anak->berat_lahir_anak }} kg
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Panjang Lahir</p>
                                            <p class="font-semibold text-[#1D1D1D]">{{ $anak->panjang_lahir_anak }} cm
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Lingkar Kepala</p>
                                            <p class="font-semibold text-[#1D1D1D]">{{ $anak->lingkar_kepala_anak }}
                                                cm</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Usia Kehamilan</p>
                                            <p class="font-semibold text-[#1D1D1D]">
                                                {{ $anak->usia_kehamilan_saat_lahir }} minggu</p>
                                        </div>
                                    </div>

                                    {{-- ⭐ Puskesmas Tujuan --}}
                                    <div class="p-4 pt-0">
                                        <br>
                                        <div class="rounded-2xl border border-[#F0F0F0] bg-white px-4 py-3">
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Puskesmas Tujuan</p>

                                            @if ($puskesmasTujuan)
                                                <p class="font-semibold text-[#1D1D1D] leading-relaxed">
                                                    {{ $puskesmasTujuan->nama_puskesmas ?? '-' }}
                                                    @if (!empty($puskesmasTujuan->kecamatan))
                                                        <span class="font-normal text-[#7C7C7C]">• Kec.
                                                            {{ $puskesmasTujuan->kecamatan }}</span>
                                                    @endif
                                                </p>

                                                @if (!empty($puskesmasTujuan->lokasi))
                                                    <p class="text-[11px] text-[#7C7C7C] mt-1 leading-relaxed">
                                                        {{ $puskesmasTujuan->lokasi }}
                                                    </p>
                                                @endif
                                            @else
                                                <p class="font-semibold text-[#1D1D1D]">-</p>
                                            @endif
                                        </div>
                                    </div>


                                    {{-- Info Tambahan --}}
                                    <div class="grid grid-cols-3 gap-4 p-4 bg-white">
                                        <div class="flex items-center gap-2">
                                            @if ($anak->memiliki_buku_kia)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 12l2 2 4-4" />
                                                    <circle cx="12" cy="12" r="10" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="M15 9l-6 6" />
                                                    <path d="M9 9l6 6" />
                                                </svg>
                                            @endif
                                            <span class="text-xs">Buku KIA</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($anak->buku_kia_bayi_kecil)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 12l2 2 4-4" />
                                                    <circle cx="12" cy="12" r="10" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="M15 9l-6 6" />
                                                    <path d="M9 9l6 6" />
                                                </svg>
                                            @endif
                                            <span class="text-xs">Buku KIA Bayi Kecil</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($anak->imd)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 12l2 2 4-4" />
                                                    <circle cx="12" cy="12" r="10" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="M15 9l-6 6" />
                                                    <path d="M9 9l6 6" />
                                                </svg>
                                            @endif
                                            <span class="text-xs">IMD</span>
                                        </div>
                                    </div>

                                    {{-- Riwayat Penyakit --}}
                                    @if ($anak->riwayat_penyakit && count($anak->riwayat_penyakit) > 0)
                                        <div class="p-4">
                                            <p class="text-[10px] text-[#7C7C7C] mb-2">Riwayat Penyakit/Komplikasi Ibu
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($anak->riwayat_penyakit as $penyakit)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-medium">
                                                        {{ $penyakit }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Keterangan Masalah Lain --}}
                                    @if ($anak->keterangan_masalah_lain)
                                        <div class="p-4 bg-gray-50">
                                            <p class="text-[10px] text-[#7C7C7C] mb-1">Keterangan Masalah Lain</p>
                                            <p class="text-xs text-[#1D1D1D]">{{ $anak->keterangan_masalah_lain }}</p>
                                        </div>
                                    @endif

                                    {{-- ===================== KONDISI IBU (HANYA UNTUK BERESIKO) ===================== --}}
                                    @if ($isBeresiko && $anak->kondisi_ibu)
                                        <div
                                            class="p-4 {{ $anak->kondisi_ibu === 'perlu_tindak_lanjut' ? 'bg-red-50' : 'bg-emerald-50' }}">
                                            <div class="flex items-start gap-3">
                                                @if ($anak->kondisi_ibu === 'aman')
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                                            <path d="M9 12l2 2 4-4" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4 text-red-600" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path
                                                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                            <line x1="12" y1="9" x2="12"
                                                                y2="13" />
                                                            <line x1="12" y1="17" x2="12.01"
                                                                y2="17" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="flex-1">
                                                    <h4
                                                        class="text-xs font-semibold {{ $anak->kondisi_ibu === 'perlu_tindak_lanjut' ? 'text-red-800' : 'text-emerald-800' }} mb-1">
                                                        Kondisi Ibu Saat Melahirkan: {{ $kondisiIbuLabel }}
                                                    </h4>
                                                    @if ($anak->catatan_kondisi_ibu)
                                                        <p
                                                            class="text-xs {{ $anak->kondisi_ibu === 'perlu_tindak_lanjut' ? 'text-red-700' : 'text-emerald-700' }} leading-relaxed">
                                                            {{ $anak->catatan_kondisi_ibu }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- TOMBOL AKSI BAWAH --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                <a href="{{ route('rs.pasien-nifas.index') }}"
                    class="rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                    <span>Kembali</span>
                </a>

                @if ($pasienNifas->anakPasien->count() > 0)
                    <a href="{{ route('rs.pasien-nifas.download-single-pdf', $pasienNifas->id) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#DC2626] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#B91C1C] w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <path d="M14 2v6h6" />
                            <path d="M12 18v-6" />
                            <path d="m9 15 3 3 3-3" />
                        </svg>
                        <span>Cetak PDF</span>
                    </a>
                @endif
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print:hidden">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    <script>
        // Auto-hide alert sukses setelah 5 detik
        document.addEventListener('DOMContentLoaded', function() {
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
