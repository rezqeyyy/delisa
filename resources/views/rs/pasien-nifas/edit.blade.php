<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Nifas - DELISA</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- Header --}}
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
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                            Data Nifas Pasien
                        </h1>
                        <p class="text-xs text-[#7C7C7C]">
                            Tambahkan data anak untuk pasien nifas ini
                        </p>
                    </div>
                </div>

                {{-- Badge Status Risiko --}}
                @php
                    $statusType = $pasienNifas->status_type ?? 'normal';
                    $statusDisplay = $pasienNifas->status_display ?? 'Tidak Berisiko';
                    $isBeresiko = $statusType === 'beresiko' || $statusType === 'waspada';
                    
                    $badgeClass = match($statusType) {
                        'beresiko' => 'bg-red-100 text-red-700 border-red-200',
                        'waspada' => 'bg-amber-100 text-amber-700 border-amber-200',
                        default => 'bg-emerald-100 text-emerald-700 border-emerald-200'
                    };
                @endphp
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $badgeClass }}">
                    @if($statusType === 'beresiko')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    @elseif($statusType === 'waspada')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4" />
                            <path d="M12 16h.01" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4" />
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                    @endif
                    <span class="text-xs font-semibold">{{ $statusDisplay }}</span>
                </div>
            </div>

            {{-- Alert --}}
            @if(session('success'))
                <div class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v5" />
                            <path d="M12 16h.01" />
                        </svg>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Info Box untuk Pasien Beresiko --}}
            @if($isBeresiko)
                <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Pasien Beresiko Pre-Eklampsia</h3>
                        <p class="text-xs text-red-700 mt-1">
                            Pasien ini terdeteksi beresiko berdasarkan hasil skrining. Form kondisi ibu saat melahirkan wajib diisi untuk pemantauan lebih lanjut.
                        </p>
                    </div>
                </div>
            @endif

            {{-- ===================== DATA ANAK SUDAH TERDAFTAR ===================== --}}
            @if($pasienNifas->anakPasien->count() > 0)
                <section class="bg-white rounded-2xl border border-[#E9E9E9] p-3 sm:p-4 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">
                                Data Anak yang Sudah Terdaftar
                            </h2>
                            <p class="text-xs text-[#7C7C7C]">
                                Riwayat anak yang sudah tercatat pada pasien nifas ini
                            </p>
                        </div>
                        <a href="{{ route('rs.pasien-nifas.detail', $pasienNifas->id) }}"
                           class="inline-flex items-center gap-2 rounded-full border border-[#D9D9D9] bg-white px-4 py-1.5 text-xs font-semibold text-[#1D1D1D] hover:bg-[#F8F8F8]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="3" />
                                <path d="M12 2v2" />
                                <path d="M12 20v2" />
                                <path d="m4.93 4.93 1.41 1.41" />
                                <path d="m17.66 17.66 1.41 1.41" />
                                <path d="M2 12h2" />
                                <path d="M20 12h2" />
                                <path d="m6.34 17.66-1.41 1.41" />
                                <path d="m19.07 4.93-1.41 1.41" />
                            </svg>
                            <span>Lihat Detail</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm">
                            <thead class="text-[#7C7C7C] bg-[#FAFAFA]">
                                <tr class="text-left">
                                    <th class="px-3 py-2">Anak Ke</th>
                                    <th class="px-3 py-2">Nama Anak</th>
                                    <th class="px-3 py-2">Jenis Kelamin</th>
                                    <th class="px-3 py-2">Tanggal Lahir</th>
                                    <th class="px-3 py-2">Berat Lahir</th>
                                    @if($isBeresiko)
                                        <th class="px-3 py-2">Kondisi Ibu</th>
                                    @endif
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @foreach($pasienNifas->anakPasien as $anak)
                                    @php
                                        $kondisiIbuClass = match($anak->kondisi_ibu) {
                                            'aman' => 'bg-emerald-100 text-emerald-700',
                                            'perlu_tindak_lanjut' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-500'
                                        };
                                        $kondisiIbuLabel = match($anak->kondisi_ibu) {
                                            'aman' => 'Aman',
                                            'perlu_tindak_lanjut' => 'Perlu Tindak Lanjut',
                                            default => 'Belum Diisi'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-[#1D1D1D]">
                                            {{ $anak->anak_ke }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $anak->nama_anak }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $anak->jenis_kelamin }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-2">
                                            {{ $anak->berat_lahir_anak }} kg
                                        </td>
                                        @if($isBeresiko)
                                            <td class="px-3 py-2">
                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-[10px] font-semibold {{ $kondisiIbuClass }}">
                                                    {{ $kondisiIbuLabel }}
                                                </span>
                                            </td>
                                        @endif
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                                <span class="mr-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Terdaftar
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            {{-- ===================== FORM TAMBAH ANAK BARU ===================== --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] p-3 sm:p-4 space-y-4">
                <div class="border-b border-[#F0F0F0] pb-3">
                    <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">
                        Tambah Data Anak {{ $pasienNifas->anakPasien->count() > 0 ? 'Baru' : '' }}
                    </h2>
                    <p class="text-xs text-[#7C7C7C] mt-1">
                        Lengkapi data berikut untuk menambah catatan anak pada pasien nifas ini
                    </p>
                </div>

                <form action="{{ route('rs.pasien-nifas.store-anak', $pasienNifas->id) }}"
                      method="POST" id="formAnakPasien" class="space-y-4">
                    @csrf

                    {{-- Baris 1: Anak ke & Tanggal lahir --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Anak Ke <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="number"
                                name="anak_ke"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                placeholder="Masukkan Data..."
                                value="{{ old('anak_ke', $pasienNifas->anakPasien->count() + 1) }}"
                                required>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Tanggal Lahir <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="date"
                                name="tanggal_lahir"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                value="{{ old('tanggal_lahir') }}"
                                required>
                        </div>
                    </div>

                    {{-- Baris 2: Jenis kelamin & Usia kehamilan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Jenis Kelamin <span class="text-pink-600">*</span>
                            </label>
                            <select
                                name="jenis_kelamin"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Usia Kehamilan Saat Lahir (Dalam Minggu) <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                name="usia_kehamilan_saat_lahir"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                placeholder="Masukkan Data..."
                                value="{{ old('usia_kehamilan_saat_lahir') }}"
                                required>
                        </div>
                    </div>

                    {{-- Baris 3: Berat, Panjang --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Berat Lahir Anak (kg) <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="berat_lahir_anak"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                placeholder="Masukkan Data..."
                                value="{{ old('berat_lahir_anak') }}"
                                required>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Panjang Lahir Anak (cm) <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="panjang_lahir_anak"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                placeholder="Masukkan Data..."
                                value="{{ old('panjang_lahir_anak') }}"
                                required>
                        </div>
                    </div>

                    {{-- Baris 4: Lingkar kepala & Buku KIA --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Lingkar Kepala Anak (cm) <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="lingkar_kepala_anak"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                placeholder="Masukkan Data..."
                                value="{{ old('lingkar_kepala_anak') }}"
                                required>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Memiliki Buku KIA <span class="text-pink-600">*</span>
                            </label>
                            <select
                                name="memiliki_buku_kia"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                required>
                                <option value="">Pilih Data Ya/Tidak</option>
                                <option value="1" {{ old('memiliki_buku_kia') == '1' ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ old('memiliki_buku_kia') == '0' ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 5: Buku KIA bayi kecil & IMD --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Memiliki Buku KIA Bayi Kecil <span class="text-pink-600">*</span>
                            </label>
                            <select
                                name="buku_kia_bayi_kecil"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                required>
                                <option value="">Pilih Data Ya/Tidak</option>
                                <option value="1" {{ old('buku_kia_bayi_kecil') == '1' ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ old('buku_kia_bayi_kecil') == '0' ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                IMD <span class="text-pink-600">*</span>
                            </label>
                            <select
                                name="imd"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                required>
                                <option value="">Pilih Data Ya/Tidak</option>
                                <option value="1" {{ old('imd') == '1' ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ old('imd') == '0' ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>
                    </div>

                    {{-- Riwayat penyakit --}}
                    <div class="space-y-2">
                        <label class="block text-[11px] font-semibold text-[#666666]">
                            Riwayat Penyakit atau Komplikasi Ibu (Penyebab BBLR/Preterm)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach(['Hipertensi','Infeksi','KPD','Masalah Plasenta','Inkompetensi Serviks','Masalah Lainnya'] as $item)
                                <label class="flex items-center gap-2 rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs text-[#4B4B4B] hover:border-[#E91E8C]/60 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="riwayat_penyakit[]"
                                        value="{{ $item }}"
                                        class="h-4 w-4 rounded border-[#D4D4D4] text-[#E91E8C] focus:ring-[#E91E8C]/40"
                                        {{ in_array($item, old('riwayat_penyakit', [])) ? 'checked' : '' }}>
                                    <span>{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Keterangan masalah lain --}}
                    <div class="space-y-2">
                        <label class="block text-[11px] font-semibold text-[#666666]">
                            Keterangan Masalah Lain (Opsional)
                        </label>
                        <textarea
                            name="keterangan_masalah_lain"
                            rows="3"
                            class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Masukkan Keterangan Lain...">{{ old('keterangan_masalah_lain') }}</textarea>
                    </div>

                    {{-- ===================== FORM KONDISI IBU (HANYA UNTUK PASIEN BERESIKO) ===================== --}}
                    @if($isBeresiko)
                        <div class="border-t border-[#F0F0F0] pt-4 mt-4">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-[#1D1D1D]">
                                        Kondisi Ibu Saat Melahirkan
                                    </h3>
                                    <p class="text-[10px] text-red-600">
                                        * Wajib diisi untuk pasien beresiko
                                    </p>
                                </div>
                            </div>

                            {{-- Pilihan Kondisi Ibu --}}
                            <div class="space-y-3">
                                <label class="block text-[11px] font-semibold text-[#666666]">
                                    Status Kondisi Ibu <span class="text-pink-600">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {{-- Opsi Aman --}}
                                    <label class="relative flex items-start gap-3 rounded-xl border-2 border-[#E5E5E5] bg-white p-4 cursor-pointer hover:border-emerald-300 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                                        <input
                                            type="radio"
                                            name="kondisi_ibu"
                                            value="aman"
                                            class="mt-1 h-4 w-4 border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                            {{ old('kondisi_ibu') == 'aman' ? 'checked' : '' }}
                                            required>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                                    <path d="M9 12l2 2 4-4" />
                                                </svg>
                                                <span class="text-sm font-semibold text-emerald-700">Aman</span>
                                            </div>
                                            <p class="text-[11px] text-[#7C7C7C] mt-1">
                                                Kondisi ibu stabil, tidak ada komplikasi serius saat persalinan
                                            </p>
                                        </div>
                                    </label>

                                    {{-- Opsi Perlu Tindak Lanjut --}}
                                    <label class="relative flex items-start gap-3 rounded-xl border-2 border-[#E5E5E5] bg-white p-4 cursor-pointer hover:border-red-300 transition-colors has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                        <input
                                            type="radio"
                                            name="kondisi_ibu"
                                            value="perlu_tindak_lanjut"
                                            class="mt-1 h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500"
                                            {{ old('kondisi_ibu') == 'perlu_tindak_lanjut' ? 'checked' : '' }}
                                            required>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                    <line x1="12" y1="9" x2="12" y2="13" />
                                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                                </svg>
                                                <span class="text-sm font-semibold text-red-700">Perlu Tindak Lanjut</span>
                                            </div>
                                            <p class="text-[11px] text-[#7C7C7C] mt-1">
                                                Terjadi komplikasi atau kondisi yang memerlukan penanganan lebih lanjut
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Catatan Kondisi Ibu --}}
                            <div class="mt-4 space-y-2">
                                <label class="block text-[11px] font-semibold text-[#666666]">
                                    Catatan Kondisi Ibu <span class="text-pink-600">*</span>
                                </label>
                                <textarea
                                    name="catatan_kondisi_ibu"
                                    rows="4"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                                    placeholder="Jelaskan kondisi ibu saat melahirkan, komplikasi yang terjadi, penanganan yang dilakukan, dan rekomendasi tindak lanjut..."
                                    required>{{ old('catatan_kondisi_ibu') }}</textarea>
                                <p class="text-[10px] text-[#7C7C7C]">
                                    Catatan ini akan ditampilkan di halaman detail untuk memudahkan pemantauan kondisi ibu
                                </p>
                            </div>
                        </div>
                    @endif
                </form>
            </section>

            {{-- Button Actions --}}
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                <a href="{{ route('rs.pasien-nifas.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    <span>Kembali</span>
                </a>

                <button type="submit" form="formAnakPasien"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-[#C2185B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    <span>Simpan Data</span>
                </button>
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>