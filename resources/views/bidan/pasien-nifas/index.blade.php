<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Pasien NIFAS</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js'
        ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-bidan.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">List Pasien Nifas</h1>
            </header>

            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-[#F5F5F5] text-[#1D1D1D]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                            <p class="text-xs text-[#7C7C7C]">Data pasien yang sedang nifas pada puskesmas ini</p>
                        </div>
                    </div>
                    <a href="{{ route('rs.pasien-nifas.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        <span>Tambah Data Pasien</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-list-ol mr-2"></i>No</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-user mr-2"></i>Nama Pasien</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-id-card mr-2"></i>NIK</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-calendar-alt mr-2"></i>Tanggal</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-map-marker-alt mr-2"></i>Alamat</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-phone mr-2"></i>No Telp</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-bell mr-2"></i>Peringat</th>
                                <th class="px-4 py-3 font-semibold"><i class="fas fa-eye mr-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E9E9E9]">
                            @forelse($pasienNifas as $pasien)
                            <tr class="hover:bg-[#FAFAFA]">
                                <td class="px-4 py-3 font-medium text-[#1D1D1D]">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-[#1D1D1D]">{{ $pasien->nama_pasien ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-[#1D1D1D]">{{ $pasien->nik ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ isset($pasien->tanggal) ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ $pasien->telp ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold leading-none whitespace-nowrap {{ $pasien->badge_class ?? 'bg-[#2EDB58] text-white' }}">{{ $pasien->peringat_label ?? 'Aman' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php($k1 = $pasien->next_ke ?? 2)
                                    @php($k2 = min(4, ($pasien->next_ke ?? 2) + 1))
                                    @php($k3 = min(4, $k2 + 1))
                                    <div class="flex items-center gap-2">
                                        <button class="inline-flex items-center rounded-full border border-[#D9D9D9] px-4 h-8 text-sm font-semibold">KF {{ $k1 }}</button>
                                        <button class="inline-flex items-center rounded-full border border-[#D9D9D9] px-4 h-8 text-sm font-semibold">KF {{ $k2 }}</button>
                                        <button class="inline-flex items-center rounded-full border border-[#D9D9D9] px-4 h-8 text-sm font-semibold">KF {{ $k3 }}</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-[#7C7C7C]">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#D9D9D9] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <p class="text-sm">Belum ada data pasien nifas</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pasienNifas->hasPages())
                <div class="mt-6 flex items-center justify-end">
                    <div class="inline-flex items-center gap-2 text-sm">
                        <a href="{{ $pasienNifas->previousPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] hover:bg-[#F5F5F5]">Previous</a>
                        @php($last = $pasienNifas->lastPage())
                        @php($current = $pasienNifas->currentPage())
                        @for($i = max(1, $current - 1); $i <= min($last, $current + 1); $i++)
                            @if($i === $current)
                                <span class="px-3 py-1 rounded-lg border border-[#E9E9E9] bg-[#FAFAFA]">{{ $i }}</span>
                            @else
                                <a href="{{ $pasienNifas->url($i) }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] hover:bg-[#F5F5F5]">{{ $i }}</a>
                            @endif
                        @endfor
                        <a href="{{ $pasienNifas->nextPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] hover:bg-[#F5F5F5]">Next</a>
                    </div>
                </div
                @endif
            </div>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">© 2025 Dinas Kesehatan Kota Depok — DeLISA</footer>
        </main>
    </div>
</body>
</html>