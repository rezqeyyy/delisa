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
        'resources/js/dropdown.js',
        'resources/js/bidan/sidebar-toggle.js'
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
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-[#1D1D1D]">List Pasien Nifas</h1>
            </header>
            
            {{-- Card utama untuk tabel data --}}
            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                    {{-- Header card: judul + tombol tambah --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                                <p class="text-xs text-[#7C7C7C]">Data pasien yang sedang nifas pada puskesmas ini</p>
                            </div>
                        </div>
                        <a href="{{ route('bidan.pasien-nifas.create') }}" 
                            class="px-5 py-2 rounded-full bg-[#FF5BAE] text-white font-semibold hover:bg-[#E91E8C] transition-colors">
                            + Tambah Pasien
                        </a>
                    </div>
                    
                    {{-- Wrapper tabel agar bisa scroll horizontal --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <br>
                            {{-- Header tabel --}}
                            <thead class="border-b border-[#EFEFEF] bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama Pasien</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">Tanggal Mulai Nifas</th>
                                    <th class="px-4 py-3 font-semibold">Alamat</th>
                                    <th class="px-4 py-3 font-semibold">No Telp</th>
                                    <th class="px-4 py-3 font-semibold">Pengingat</th>
                                    <th class="px-4 py-3 font-semibold">Kunjungan Nifas</th>
                                    <th class="px-4 py-3 font-semibold">Aksi</th>
                                </tr>
                            </thead>

                            {{-- Isi tabel --}}
                            <tbody>

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
                                    <td class="px-4 py-3 font-medium">
                                        {{ isset($pasien->tanggal) ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y') : '-' }}
                                    </td>

                                    {{-- Alamat --}}
                                    <td class="px-4 py-3 font-medium">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>

                                    {{-- Telepon --}}
                                    <td class="px-4 py-3 font-medium">{{ $pasien->telp ?? '-' }}</td>

                                    {{-- Badge status (Aman / Peringatan) --}}
                                    <td class="px-4 py-3">
                                        @php($state = $pasien->peringat_state ?? 'early')
                                        <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold
                                            @if($state==='late') bg-red-100 text-red-800 border border-red-200
                                            @elseif($state==='window') bg-amber-100 text-amber-800 border border-amber-200
                                            @elseif($state==='done') bg-emerald-100 text-emerald-800 border border-emerald-200
                                            @elseif($state==='no_date') bg-gray-100 text-gray-700 border border-gray-200
                                            @else bg-gray-100 text-gray-700 border border-gray-200 @endif">
                                            {{ $pasien->peringat_label ?? 'Aman' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="inline-flex items-center gap-2">
                                            @php($maxKe = $pasien->max_ke ?? 0)
                                            @foreach([1,2,3,4] as $jk)
                                                <a href="{{ route('bidan.pasien-nifas.show', $pasien->id) }}" class="px-3 py-1.5 rounded-full border text-xs {{ $maxKe >= $jk ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'border-[#E5E5E5]' }}">KF{{ $jk }}</a>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if (Route::has('bidan.pasien-nifas.anak.create'))
                                                <a href="{{ route('bidan.pasien-nifas.anak.create', $pasien->id) }}" class="px-3 py-1.5 rounded-full border border-[#E5E5E5] text-xs">Tambah Data Anak</a>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" title="Route tambah anak belum tersedia" disabled>Tambah Data Anak</button>
                                            @endif

                                            @if (Route::has('bidan.pasien-nifas.show'))
                                                <a href="{{ route('bidan.pasien-nifas.show', $pasien->id) }}" class="px-3 py-1.5 rounded-full border border-[#E5E5E5] text-xs">View</a>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" title="Route detail belum tersedia" disabled>View</button>
                                            @endif

                                            @if (Route::has('bidan.pasien-nifas.destroy'))
                                                <form action="{{ route('bidan.pasien-nifas.destroy', $pasien->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data pasien nifas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">Hapus</button>
                                                </form>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" title="Route hapus belum tersedia" disabled>Hapus</button>
                                            @endif
                                        </div>
                                    </td>

                                </tr>

                                {{-- Jika tidak ada data --}}
                                @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-2 h-16 text-center text-[#7C7C7C]">
                                        <p class="text-sm">Belum ada data pasien nifas</p>
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
            </section>

            {{-- Footer --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>
