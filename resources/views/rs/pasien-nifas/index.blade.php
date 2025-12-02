<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Sakit — Pasien Nifas</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js', 
        'resources/js/rs/sidebar-toggle.js'
    ])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v5" />
                            <path d="M12 16h.01" />
                        </svg>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="flex items-start gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs sm:text-sm text-blue-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                    </span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <section class="space-y-4">
                <h1 class="text-2xl font-semibold text-[#1D1D1D]">List Pasien Nifas</h1>
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor">
                                    <path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                                <p class="text-xs text-[#7C7C7C]">Data pasien yang sedang nifas pada rumah sakit ini</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('rs.pasien-nifas.create') }}" 
                               class="px-5 py-2 rounded-full bg-[#FF5BAE] text-white font-semibold hover:bg-[#E91E8C] transition-colors">
                                + Tambah Pasien
                            </a>

                            <a href="{{ route('rs.pasien-nifas.download-pdf') }}" 
                               class="px-5 py-2 rounded-full bg-[#E9E9E9] text-[#1D1D1D] font-semibold hover:bg-[#D9D9D9] transition-colors">
                                Download Data
                            </a>
                        </div>
                    </div>


                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-[#7C7C7C] bg-[#FAFAFA]">
                                <tr class="text-left">
                                    <th class="px-3 py-2 w-10"><input type="checkbox" class="rounded"></th>
                                    <th class="px-3 py-2">NIK Pasien</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">Tanggal Mulai Nifas</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Status Risiko</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse($pasienNifas as $pn)
                                    @php
                                        $pas = optional($pn)->pasien;
                                        $usr = optional($pas)->user;
                                        $statusDisplay = $pn->status_display ?? 'Tidak Berisiko';
                                        $statusType = $pn->status_type ?? 'normal';
                                        
                                        // Tentukan warna berdasarkan status
                                        $statusClass = match($statusType) {
                                            'beresiko' => 'bg-[#E20D0D] text-white',
                                            'waspada' => 'bg-[#FFC400] text-[#1D1D1D]',
                                            default => 'bg-[#39E93F] text-white'
                                        };
                                    @endphp
                                    <tr class="hover:bg-[#FFF7FC]/50">
                                        <td class="px-3 py-3"><input type="checkbox" class="rounded"></td>
                                        <td class="px-3 py-3 font-medium text-[#1D1D1D]">{{ $pas->nik ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $usr->name ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ optional($pn->tanggal_mulai_nifas)->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $pas->PKecamatan ?? $pas->PWilayah ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $usr->phone ?? '-' }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                @if($statusType === 'beresiko')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                                        <line x1="12" y1="9" x2="12" y2="13" />
                                                        <line x1="12" y1="17" x2="12.01" y2="17" />
                                                    </svg>
                                                @elseif($statusType === 'waspada')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <path d="M12 8v4" />
                                                        <path d="M12 16h.01" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M9 12l2 2 4-4" />
                                                        <circle cx="12" cy="12" r="10" />
                                                    </svg>
                                                @endif
                                                {{ $statusDisplay }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('rs.pasien-nifas.show', $pn->id) }}" 
                                                   class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-[#F5F5F5] transition-colors">
                                                    Edit
                                                </a>
                                                <a href="{{ route('rs.pasien-nifas.detail', $pn->id) }}" 
                                                   class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-[#F5F5F5] transition-colors">
                                                    Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#D9D9D9]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>
                                                <p>Belum ada data pasien nifas di RS ini.</p>
                                                <a href="{{ route('rs.pasien-nifas.create') }}" class="text-[#E91E8C] hover:underline">
                                                    + Tambah Pasien Baru
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4 flex items-center justify-between text-sm text-[#7C7C7C]">
                        <div>
                            Menampilkan {{ $pasienNifas->firstItem() ?? 0 }} - {{ $pasienNifas->lastItem() ?? 0 }} 
                            dari {{ $pasienNifas->total() }} data
                        </div>
                        <div>
                            {{ $pasienNifas->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>

    </div>
</body>
</html>