<!DOCTYPE html>
<html lang="id">
<head>
    {{-- Set karakter dan pengaturan dokumen --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Pasien NIFAS</title>
    
    {{-- Memuat file CSS & JS dari Laravel Vite --}}
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- State Alpine.js untuk membuka/menutup sidebar --}}
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        {{-- Komponen sidebar bidan --}}
        <x-bidan.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- Judul halaman --}}
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">List Pasien Nifas</h1>
            </header>

            {{-- Card utama untuk tabel data --}}
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                
                {{-- Header card: judul + tombol tambah --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        {{-- Icon buku --}}
                        <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-[#F5F5F5] text-[#1D1D1D]">
                            <svg ...></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                            <p class="text-xs text-[#7C7C7C]">Data pasien yang sedang nifas pada puskesmas ini</p>
                        </div>
                    </div>

                    {{-- Tombol tambah data --}}
                    <a href="{{ route('bidan.pasien-nifas.create') }}"
                        class="inline-flex ...">
                        <svg ...></svg>
                        <span>Tambah Data Pasien</span>
                    </a>
                </div>

                {{-- Wrapper tabel agar bisa scroll horizontal --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        {{-- Header tabel --}}
                        <tr class="text-left">
                            <th class="px-4 py-3 font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Nama Pasien</th>
                            <th class="px-4 py-3 font-semibold">NIK</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Alamat</th>
                            <th class="px-4 py-3 font-semibold">No Telp</th>
                            <th class="px-4 py-3 font-semibold">Peringat</th>
                            <th class="px-4 py-3 font-semibold">Action</th>
                        </tr>
                        </thead>

                        {{-- Isi tabel --}}
                        <tbody class="divide-y divide-[#E9E9E9]">

                            {{-- Loop tiap data pasien --}}
                            @forelse($pasienNifas as $pasien)
                            <tr class="hover:bg-[#FAFAFA]">

                                {{-- Nomor urut --}}
                                <td class="px-4 py-3 font-medium">{{ $loop->iteration }}</td>

                                {{-- Nama pasien --}}
                                <td class="px-4 py-3 font-medium">{{ $pasien->nama_pasien ?? '-' }}</td>

                                {{-- NIK --}}
                                <td class="px-4 py-3 font-medium">{{ $pasien->nik ?? '-' }}</td>

                                {{-- Tanggal (formatting tanggal) --}}
                                <td class="px-4 py-3 text-[#7C7C7C]">
                                    {{ isset($pasien->tanggal) ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- Alamat --}}
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>

                                {{-- Telepon --}}
                                <td class="px-4 py-3 text-[#7C7C7C]">{{ $pasien->telp ?? '-' }}</td>

                                {{-- Badge status (Aman / Peringatan) --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold {{ $pasien->badge_class ?? 'bg-[#2EDB58] text-white' }}">
                                        {{ $pasien->peringat_label ?? 'Aman' }}
                                    </span>
                                </td>

                                {{-- Tombol Action KF --}}
                                <td class="px-4 py-3">
                                    {{-- Hitung KF otomatis berdasarkan next_ke --}}
                                    @php($k1 = $pasien->next_ke ?? 2)
                                    @php($k2 = min(4, ($pasien->next_ke ?? 2) + 1))
                                    @php($k3 = min(4, $k2 + 1))

                                    {{-- Tiga tombol KF --}}
                                    <div class="flex items-center gap-2">
                                        <button class="...">KF {{ $k1 }}</button>
                                        <button class="...">KF {{ $k2 }}</button>
                                        <button class="...">KF {{ $k3 }}</button>
                                    </div>
                                </td>

                            </tr>

                            {{-- Jika tidak ada data --}}
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-[#7C7C7C]">
                                    <div class="flex flex-col items-center py-8">
                                        <svg ...></svg>
                                        <p class="text-sm">Belum ada data pasien nifas</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($pasienNifas->hasPages())
                <div class="mt-6 flex items-center justify-end">
                    <div class="inline-flex items-center gap-2 text-sm">

                        {{-- Tombol sebelumnya --}}
                        <a href="{{ $pasienNifas->previousPageUrl() }}" class="px-3 py-1 ...">Previous</a>

                        {{-- Logika menampilkan halaman di sekitar halaman aktif --}}
                        @php($last = $pasienNifas->lastPage())
                        @php($current = $pasienNifas->currentPage())

                        @for($i = max(1, $current - 1); $i <= min($last, $current + 1); $i++)
                            @if($i === $current)
                                {{-- Halaman aktif --}}
                                <span class="px-3 py-1 rounded-lg border bg-[#FAFAFA]">{{ $i }}</span>
                            @else
                                {{-- Link halaman lain --}}
                                <a href="{{ $pasienNifas->url($i) }}" class="px-3 py-1 ...">{{ $i }}</a>
                            @endif
                        @endfor

                        {{-- Tombol next --}}
                        <a href="{{ $pasienNifas->nextPageUrl() }}" class="px-3 py-1 ...">Next</a>

                    </div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>
