<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Sakit — Dashboard</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js', 
        'resources/js/rs/sidebar-toggle.js'
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
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            <section class="space-y-4">
                <h1 class="text-2xl font-semibold text-[#1D1D1D]">List Pasien Nifas</h1>
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                                <p class="text-xs text-[#7C7C7C]">Data pasien yang sedang nifas pada rumah sakit ini</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('rs.pasien-nifas.download-pdf') }}" class="px-5 py-2 rounded-full bg-[#E9E9E9] text-[#1D1D1D] font-semibold">Download Data</a>
                        </div>
                    </div>
                    <br>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-[#7C7C7C]">
                                <tr class="text-left">
                                    <th class="px-3 py-2 w-10"><input type="checkbox" class="rounded"></th>
                                    <th class="px-3 py-2">NIK Pasien</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">Tanggal Mulai Nifas</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse($pasienNifas as $pn)
                                    @php($pas = optional($pn)->pasien)
                                    @php($usr = optional($pas)->user)
                                    @php($statusDisplay = $pn->status_display ?? 'Tepat Waktu')
                                    @php($isLate = $statusDisplay === 'Telat')
                                    @php($isWarn = $statusDisplay === 'Waspada')
                                    <tr>
                                        <td class="px-3 py-3"><input type="checkbox" class="rounded"></td>
                                        <td class="px-3 py-3 font-medium text-[#1D1D1D]">{{ $pas->nik ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $usr->name ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ optional($pn->tanggal_mulai_nifas)->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $pas->PKecamatan ?? $pas->PWilayah ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $usr->phone ?? '-' }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isLate ? 'bg-[#E20D0D] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                                {{ $statusDisplay }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('rs.pasien-nifas.show', $pn->id) }}" class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">Edit</a>
                                                <a href="{{ route('rs.pasien-nifas.detail', $pn->id) }}" class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">Belum ada data pasien nifas di RS ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-[#7C7C7C]">
                        <div>
                            Halaman {{ $pasienNifas->currentPage() }} dari {{ $pasienNifas->lastPage() }}
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