<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas - Pasien Nifas</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/puskesmas/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">

    <x-puskesmas.sidebar />

    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#1D1D1D]">Pasien Nifas</h1>
                <p class="text-[#7C7C7C] mt-1">
                    Data pasien nifas yang <span class="font-semibold">ditugaskan</span> ke puskesmas ini (berdasarkan <span class="font-mono">puskesmas_id</span>) —
                    <span class="font-semibold">sumber data: RS</span>
                </p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-lg text-[#1D1D1D]">Total Pasien Nifas</h3>
                    <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $totalPasienNifas ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-lg text-[#1D1D1D]">Sudah KF1</h3>
                    <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-lg text-[#1D1D1D]">Belum KF1</h3>
                    <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $belumKFI ?? 0 }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
            <form method="GET" action="{{ route('puskesmas.pasien-nifas.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <!-- Search -->
                <div class="lg:col-span-5">
                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Cari Nama Pasien</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama pasien..."
                            class="w-full pl-9 pr-4 py-3 border border-[#E9E9E9] rounded-xl text-sm
                                   focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40"
                        >
                        <span class="absolute inset-y-0 left-3 flex items-center">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60">
                        </span>
                    </div>
                </div>

                <!-- Tanggal mulai -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                           class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl text-sm focus:ring-[#B9257F]/30 focus:border-[#B9257F]">
                </div>

                <!-- Tanggal selesai -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                           class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl text-sm focus:ring-[#B9257F]/30 focus:border-[#B9257F]">
                </div>

                <!-- Action -->
                <div class="lg:col-span-1 flex items-end gap-2">
                    <button type="submit"
                            class="w-full px-4 py-3 bg-[#B9257F] text-white rounded-xl hover:bg-[#9D1B6A] transition-colors text-sm">
                        Filter
                    </button>
                </div>

                <div class="lg:col-span-12">
                    <a href="{{ route('puskesmas.pasien-nifas.index') }}"
                       class="inline-flex items-center text-sm text-[#B9257F] hover:text-[#9D1B6A]">
                        Reset Filter
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-[#1D1D1D]">List Pasien Nifas</h2>
                <div class="text-sm text-[#7C7C7C]">
                    Puskesmas: <span class="font-semibold">{{ $namaPuskesmas ?? '-' }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[#7C7C7C] bg-[#FAFAFA]">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">NO</th>
                        <th class="px-4 py-3 font-semibold">NAMA PASIEN</th>
                        <th class="px-4 py-3 font-semibold">TGL MULAI NIFAS</th>
                        <th class="px-4 py-3 font-semibold">ALAMAT (KEC)</th>
                        <th class="px-4 py-3 font-semibold">ASAL DATA</th>
                        <th class="px-4 py-3 font-semibold">STATUS KF</th>
                        <th class="px-4 py-3 font-semibold">ACTION</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-[#E9E9E9]">
                    @php $no = 0; @endphp

                    @forelse($dataRs as $item)
                        @php $no++; @endphp
                        <tr class="hover:bg-[#FAFAFA]">
                            <td class="px-4 py-3 text-[#7C7C7C]">{{ $no }}</td>

                            <td class="px-4 py-3 font-medium text-[#1D1D1D]">
                                {{ $item->pasien->user->name ?? 'N/A' }}
                            </td>

                            <td class="px-4 py-3 text-[#7C7C7C]">
                                {{ $item->tanggal_mulai_nifas ? \Carbon\Carbon::parse($item->tanggal_mulai_nifas)->format('d/m/Y') : 'N/A' }}
                            </td>

                            <td class="px-4 py-3 text-[#7C7C7C] max-w-xs truncate">
                                {{ $item->pasien->PKecamatan ?? 'N/A' }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    RS: {{ $item->rs->nama ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $kf1Status = $item->getKfStatus(1);
                                    $kf2Status = $item->getKfStatus(2);
                                    $kf3Status = $item->getKfStatus(3);
                                    $kf4Status = $item->getKfStatus(4);

                                    if($kf1Status == 'terlambat' || $kf2Status == 'terlambat' || $kf3Status == 'terlambat' || $kf4Status == 'terlambat') {
                                        $badgeColor = 'bg-red-100 text-red-800';
                                        $badgeText = 'Ada KF Terlambat';
                                    } elseif($kf1Status == 'dalam_periode' || $kf2Status == 'dalam_periode' || $kf3Status == 'dalam_periode' || $kf4Status == 'dalam_periode') {
                                        $badgeColor = 'bg-amber-100 text-amber-800';
                                        $badgeText = 'Perlu KF';
                                    } elseif($kf1Status == 'selesai' && $kf2Status == 'selesai' && $kf3Status == 'selesai' && $kf4Status == 'selesai') {
                                        $badgeColor = 'bg-green-100 text-green-800';
                                        $badgeText = 'Semua KF Selesai';
                                    } else {
                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                        $badgeText = 'Menunggu';
                                    }
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                    {{ $badgeText }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('puskesmas.pasien-nifas.show', ['type' => 'rs', 'id' => $item->id]) }}"
                                       class="p-1.5 rounded-lg border border-[#D9D9D9] hover:bg-[#F5F5F5] transition-colors"
                                       title="Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-[#7C7C7C]">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#D9D9D9] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-sm">Belum ada data pasien nifas yang ditugaskan ke puskesmas ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($dataRs instanceof \Illuminate\Pagination\LengthAwarePaginator && $dataRs->hasPages())
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-[#7C7C7C]">
                        Menampilkan {{ $dataRs->firstItem() ?? 0 }} - {{ $dataRs->lastItem() ?? 0 }} dari {{ $dataRs->total() }} data
                    </div>
                    <div class="flex gap-1">
                        @if($dataRs->onFirstPage())
                            <span class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Sebelumnya</span>
                        @else
                            <a href="{{ $dataRs->previousPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Sebelumnya</a>
                        @endif

                        @if($dataRs->hasMorePages())
                            <a href="{{ $dataRs->nextPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Selanjutnya</a>
                        @else
                            <span class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <footer class="text-center text-xs text-[#7C7C7C] py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>

    </main>
</div>
</body>
</html>
