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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-bidan.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">List Pasien Nifas</h1>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Total Pasien Nifas</h3>
                        <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        
                    </div>
                    <div class="text-3xl font-bold text-[#1D1D1D]">{{ $totalPasienNifas ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Sudah KFI</h3>
                        <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Belum KFI</h3>
                        <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-[#1D1D1D]">{{ $belumKFI ?? 0 }}</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="pl-9 pr-4 py-2 border border-[#D9D9D9] rounded-full text-sm w-64 focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                        <span class="absolute inset-y-0 left-3 flex items-center">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[#7C7C7C] bg-[#FAFAFA]">
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">ID PASIEN</th>
                                <th class="px-4 py-3 font-semibold">NAMA PASIEN</th>
                                <th class="px-4 py-3 font-semibold">TANGGAL</th>
                                <th class="px-4 py-3 font-semibold">ALAMAT</th>
                                <th class="px-4 py-3 font-semibold">NO. TELP</th>
                                <th class="px-4 py-3 font-semibold">PENGINGAT</th>
                                <th class="px-4 py-3 font-semibold">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E9E9E9]">
                            @forelse($pasienNifas as $pasien)
                            <tr class="hover:bg-[#FAFAFA]">
                                <td class="px-4 py-3 font-medium text-[#1D1D1D]">#{{ $pasien->pasien_id ?? 'N/A' }}</td>
                                <td class="px-4 py-3 font-medium text-[#1D1D1D]">{{ $pasien->nama_pasien ?? $pasien->nik ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-[#7C7C7C]">
                                    @if(isset($pasien->tanggal))
                                        {{ \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[#7C7C7C] max-w-xs truncate">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ $pasien->telp ?? '-' }}</td>
                                <td class="px-4 py-3"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-[#FFC400] text-[#1D1D1D]">Belum KFI</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button class="px-3 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">KF 2</button>
                                        <button class="px-3 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">KF 3</button>
                                        <button class="px-3 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">KF 4</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-[#7C7C7C]">
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
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-[#7C7C7C]">Menampilkan {{ $pasienNifas->firstItem() ?? 0 }} - {{ $pasienNifas->lastItem() ?? 0 }} dari {{ $pasienNifas->total() }} data</div>
                    <div class="flex gap-1">
                        @if($pasienNifas->onFirstPage())
                        <span class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Sebelumnya</span>
                        @else
                        <a href="{{ $pasienNifas->previousPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Sebelumnya</a>
                        @endif
                        @if($pasienNifas->hasMorePages())
                        <a href="{{ $pasienNifas->nextPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Selanjutnya</a>
                        @else
                        <span class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Selanjutnya</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">© 2025 Dinas Kesehatan Kota Depok — DeLISA</footer>
        </main>
    </div>
</body>
</html>