<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas - Pasien Nifas</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/puskesmas/sidebar-toggle.js', 'resources/js/puskesmas/delete-confirm.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1D1D1D]">Pasien Nifas</h1>
                    <p class="text-[#7C7C7C] mt-1">Data pasien nifas di puskesmas</p>
                </div>
            </div>

            <!-- Konten Pasien Nifas -->
            <div class="flex-1 flex flex-col">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Total Pasien Nifas</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $totalPasienNifas ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Sudah KFI</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Belum KFI</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $belumKFI ?? 0 }}</div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 flex-1">
                    <!-- Header Table -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-[#1D1D1D]">List Pasien Nifas</h2>
                        <div class="flex gap-2">
                            <div class="relative">
                                <form method="GET" action="{{ route('puskesmas.pasien-nifas.index') }}">
                                    <div class="relative">
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Cari nama pasien..."
                                            class="pl-9 pr-4 py-2 border border-[#D9D9D9] rounded-full text-sm w-64
                   focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                        <span class="absolute inset-y-0 left-3 flex items-center">
                                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                                class="w-4 h-4 opacity-60">
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama Pasien</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">No Telp</th>
                                    <th class="px-4 py-3 font-semibold">Tanggal Mulai NIFAS</th>
                                    <th class="px-4 py-3 font-semibold">Alamat</th>
                                    <th class="px-4 py-3 font-semibold">Asal Data</th>
                                    <th class="px-4 py-3 font-semibold">Status KF</th>
                                    <th class="px-4 py-3 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                <!-- DATA DARI RS -->
                                @php
                                    $rsCounter = 0;
                                @endphp
                                @forelse($dataRs as $item)
                                    @php
                                        $rsCounter++;
                                    @endphp
                                    <tr class="hover:bg-[#FAFAFA]">
                                        <!-- NO -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $rsCounter }}
                                        </td>

                                        <!-- NAMA PASIEN -->
                                        <td class="px-4 py-3 font-medium text-[#1D1D1D]">
                                            {{ $item->pasien->user->name ?? 'N/A' }}
                                        </td>

                                        <!-- NIK -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $item->pasien->nik ?? 'N/A' }}
                                        </td>

                                        <!-- NO TELP -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $item->pasien->user->phone ?? ($item->pasien->no_telepon ?? 'N/A') }}
                                        </td>

                                        <!-- TANGGAL MULAI NIFAS -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            @if ($item->tanggal_mulai_nifas)
                                                {{ \Carbon\Carbon::parse($item->tanggal_mulai_nifas)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>

                                        <!-- ALAMAT -->
                                        <td class="px-4 py-3 text-[#7C7C7C] max-w-xs truncate">
                                            {{ $item->pasien->PKecamatan ?? ($item->pasien->PKabupaten ?? 'N/A') }}
                                        </td>

                                        <!-- ASAL DATA -->
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                RS: {{ $item->rs->nama ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <!-- STATUS KF -->
                                        <td class="px-4 py-3">
                                            @php
                                                $kf1Status = $item->getKfStatus(1);
                                                $kf2Status = $item->getKfStatus(2);
                                                $kf3Status = $item->getKfStatus(3);
                                                $kf4Status = $item->getKfStatus(4);

                                                if (
                                                    $kf1Status == 'terlambat' ||
                                                    $kf2Status == 'terlambat' ||
                                                    $kf3Status == 'terlambat' ||
                                                    $kf4Status == 'terlambat'
                                                ) {
                                                    $badgeColor = 'bg-red-100 text-red-800';
                                                    $badgeText = 'Ada KF Terlambat';
                                                } elseif (
                                                    $kf1Status == 'dalam_periode' ||
                                                    $kf2Status == 'dalam_periode' ||
                                                    $kf3Status == 'dalam_periode' ||
                                                    $kf4Status == 'dalam_periode'
                                                ) {
                                                    $badgeColor = 'bg-amber-100 text-amber-800';
                                                    $badgeText = 'Perlu KF';
                                                } elseif (
                                                    $kf1Status == 'selesai' &&
                                                    $kf2Status == 'selesai' &&
                                                    $kf3Status == 'selesai' &&
                                                    $kf4Status == 'selesai'
                                                ) {
                                                    $badgeColor = 'bg-green-100 text-green-800';
                                                    $badgeText = 'Semua KF Selesai';
                                                } else {
                                                    $badgeColor = 'bg-gray-100 text-gray-800';
                                                    $badgeText = 'Menunggu';
                                                }
                                            @endphp
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                                {{ $badgeText }}
                                            </span>
                                        </td>

                                        <!-- ACTION -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <!-- Tombol Detail -->
                                                <a href="{{ route('puskesmas.pasien-nifas.show', ['type' => 'rs', 'id' => $item->id]) }}"
                                                    class="p-1.5 rounded-lg border border-[#D9D9D9] hover:bg-[#F5F5F5] transition-colors"
                                                    title="Detail Pasien">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                <!-- Tombol KF1 -->
                                                @if ($item->canDoKf(1) && !$item->isKfSelesai(1))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'rs', 'id' => $item->id, 'jenisKf' => 1]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(1) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF1 - {{ $item->getKfStatus(1) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(1) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF1
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(1))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF1 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF1 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF1</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF2 -->
                                                @if ($item->canDoKf(2) && !$item->isKfSelesai(2))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'rs', 'id' => $item->id, 'jenisKf' => 2]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(2) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF2 - {{ $item->getKfStatus(2) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(2) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF2
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(2))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF2 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF2 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF2</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF3 -->
                                                @if ($item->canDoKf(3) && !$item->isKfSelesai(3))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'rs', 'id' => $item->id, 'jenisKf' => 3]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(3) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF3 - {{ $item->getKfStatus(3) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(3) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF3
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(3))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF3 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF3 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF3</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF4 -->
                                                @if ($item->canDoKf(4) && !$item->isKfSelesai(4))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'rs', 'id' => $item->id, 'jenisKf' => 4]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(4) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF4 - {{ $item->getKfStatus(4) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(4) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF4
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(4))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF4 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF4 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF4</span>
                                                    </span>
                                                @endif

                                                {{-- Tombol Hapus (RS) --}}
                                                @if (Route::has('puskesmas.pasien-nifas.destroy'))
                                                    <form
                                                        action="{{ route('puskesmas.pasien-nifas.destroy', ['type' => 'rs', 'id' => $item->id]) }}"
                                                        method="POST" class="inline js-delete-skrining-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="js-delete-skrining-btn p-1.5 rounded-lg border border-red-300 bg-red-50 hover:bg-red-100 transition-colors"
                                                            title="Hapus Pasien">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-4 h-4 text-red-600" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif


                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <!-- Jika tidak ada data RS -->
                                @endforelse

                                <!-- DATA DARI BIDAN -->
                                @php
                                    $bidanCounter = $rsCounter;
                                @endphp
                                @forelse($dataBidan as $item)
                                    @php
                                        $bidanCounter++;
                                    @endphp
                                    <tr class="hover:bg-[#FAFAFA]">
                                        <!-- NO -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $bidanCounter }}
                                        </td>

                                        <!-- NAMA PASIEN -->
                                        <td class="px-4 py-3 font-medium text-[#1D1D1D]">
                                            {{ $item->pasien->user->name ?? 'N/A' }}
                                        </td>

                                        <!-- NIK -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $item->pasien->nik ?? 'N/A' }}
                                        </td>

                                        <!-- NO TELP -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $item->pasien->user->phone ?? ($item->pasien->no_telepon ?? 'N/A') }}
                                        </td>

                                        <!-- TANGGAL MULAI NIFAS -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            @if ($item->tanggal_mulai_nifas)
                                                {{ \Carbon\Carbon::parse($item->tanggal_mulai_nifas)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>

                                        <!-- ALAMAT -->
                                        <td class="px-4 py-3 text-[#7C7C7C] max-w-xs truncate">
                                            {{ $item->pasien->PKecamatan ?? ($item->pasien->PKabupaten ?? 'N/A') }}
                                        </td>

                                        <!-- ASAL DATA -->
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                BIDAN: {{ $item->bidan->nama ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <!-- STATUS KF -->
                                        <td class="px-4 py-3">
                                            @php
                                                $kf1Status = $item->getKfStatus(1);
                                                $kf2Status = $item->getKfStatus(2);
                                                $kf3Status = $item->getKfStatus(3);
                                                $kf4Status = $item->getKfStatus(4);

                                                if (
                                                    $kf1Status == 'terlambat' ||
                                                    $kf2Status == 'terlambat' ||
                                                    $kf3Status == 'terlambat' ||
                                                    $kf4Status == 'terlambat'
                                                ) {
                                                    $badgeColor = 'bg-red-100 text-red-800';
                                                    $badgeText = 'Ada KF Terlambat';
                                                } elseif (
                                                    $kf1Status == 'dalam_periode' ||
                                                    $kf2Status == 'dalam_periode' ||
                                                    $kf3Status == 'dalam_periode' ||
                                                    $kf4Status == 'dalam_periode'
                                                ) {
                                                    $badgeColor = 'bg-amber-100 text-amber-800';
                                                    $badgeText = 'Perlu KF';
                                                } elseif (
                                                    $kf1Status == 'selesai' &&
                                                    $kf2Status == 'selesai' &&
                                                    $kf3Status == 'selesai' &&
                                                    $kf4Status == 'selesai'
                                                ) {
                                                    $badgeColor = 'bg-green-100 text-green-800';
                                                    $badgeText = 'Semua KF Selesai';
                                                } else {
                                                    $badgeColor = 'bg-gray-100 text-gray-800';
                                                    $badgeText = 'Menunggu';
                                                }
                                            @endphp
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                                {{ $badgeText }}
                                            </span>
                                        </td>

                                        <!-- ACTION -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <!-- Tombol Detail -->
                                                <a href="{{ route('puskesmas.pasien-nifas.show', ['type' => 'bidan', 'id' => $item->id]) }}"
                                                    class="p-1.5 rounded-lg border border-[#D9D9D9] hover:bg-[#F5F5F5] transition-colors"
                                                    title="Detail Pasien">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                <!-- Tombol KF1 -->
                                                @if ($item->canDoKf(1) && !$item->isKfSelesai(1))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'bidan', 'id' => $item->id, 'jenisKf' => 1]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(1) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF1 - {{ $item->getKfStatus(1) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(1) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF1
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(1))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF1 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF1 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF1</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF2 -->
                                                @if ($item->canDoKf(2) && !$item->isKfSelesai(2))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'bidan', 'id' => $item->id, 'jenisKf' => 2]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(2) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF2 - {{ $item->getKfStatus(2) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(2) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF2
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(2))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF2 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF2 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF2</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF3 -->
                                                @if ($item->canDoKf(3) && !$item->isKfSelesai(3))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'bidan', 'id' => $item->id, 'jenisKf' => 3]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(3) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF3 - {{ $item->getKfStatus(3) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(3) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF3
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(3))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF3 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF3 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF3</span>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF4 -->
                                                @if ($item->canDoKf(4) && !$item->isKfSelesai(4))
                                                    <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => 'bidan', 'id' => $item->id, 'jenisKf' => 4]) }}"
                                                        class="p-1.5 rounded-lg border {{ $item->getKfStatus(4) == 'terlambat' ? 'border-red-300 bg-red-50 hover:bg-red-100' : 'border-amber-300 bg-amber-50 hover:bg-amber-100' }} transition-colors"
                                                        title="Catat KF4 - {{ $item->getKfStatus(4) == 'terlambat' ? 'TERLAMBAT' : 'Dalam Periode' }}">
                                                        <span
                                                            class="text-xs font-bold {{ $item->getKfStatus(4) == 'terlambat' ? 'text-red-600' : 'text-amber-600' }}">
                                                            KF4
                                                        </span>
                                                    </a>
                                                @elseif($item->isKfSelesai(4))
                                                    <span
                                                        class="p-1.5 rounded-lg border border-green-300 bg-green-50 cursor-default"
                                                        title="KF4 Sudah Selesai">
                                                        <span class="text-xs font-bold text-green-600">✓</span>
                                                    </span>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="KF4 Belum Tersedia">
                                                        <span class="text-xs font-bold text-gray-400">KF4</span>
                                                    </span>
                                                @endif

                                                {{-- Tombol Hapus (Bidan) --}}
                                                @if (Route::has('puskesmas.pasien-nifas.destroy'))
                                                    <form
                                                        action="{{ route('puskesmas.pasien-nifas.destroy', ['type' => 'bidan', 'id' => $item->id]) }}"
                                                        method="POST" class="inline js-delete-skrining-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="js-delete-skrining-btn p-1.5 rounded-lg border border-red-300 bg-red-50 hover:bg-red-100 transition-colors"
                                                            title="Hapus Pasien">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-4 h-4 text-red-600" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <!-- Jika tidak ada data Bidan -->
                                @endforelse

                                <!-- Jika tidak ada data sama sekali -->
                                @if (($dataRs->count() ?? 0) === 0 && ($dataBidan->count() ?? 0) === 0)
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-[#7C7C7C]">
                                            <div class="flex flex-col items-center justify-center py-8">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-12 h-12 text-[#D9D9D9] mb-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <p class="text-sm">Belum ada data pasien nifas</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (hanya untuk data RS karena dataBidan tidak paginated) -->
                    @if ($dataRs instanceof \Illuminate\Pagination\LengthAwarePaginator && $dataRs->hasPages())
                        <div class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-[#7C7C7C]">
                                Menampilkan {{ $dataRs->firstItem() ?? 0 }} - {{ $dataRs->lastItem() ?? 0 }} dari
                                {{ $dataRs->total() }} data RS
                            </div>
                            <div class="flex gap-1">
                                @if ($dataRs->onFirstPage())
                                    <span
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Sebelumnya</span>
                                @else
                                    <a href="{{ $dataRs->previousPageUrl() }}"
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Sebelumnya</a>
                                @endif

                                @if ($dataRs->hasMorePages())
                                    <a href="{{ $dataRs->nextPageUrl() }}"
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Selanjutnya</a>
                                @else
                                    <span
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Selanjutnya</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
