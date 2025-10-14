<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Pasien Nifas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    <!-- Wrapper vertikal setinggi layar -->
    <div class="flex flex-col min-h-screen">

        {{-- Sidebar (fixed) --}}
        <x-dinkes.sidebar />

        {{-- Konten utama: flex-col + min-h-screen agar footer terdorong ke bawah --}}
        <main class="ml-[260px] flex-1 min-h-screen flex flex-col p-8 space-y-8">

            <!-- === KONTEN (dibungkus flex-1) === -->
            <div class="flex-1">
                {{-- NAVBAR / TOP-BAR PASIEN NIFAS --}}
                <header class="w-full space-y-4">
                    {{-- Judul --}}
                    <div>
                        <h1 class="text-[28px] font-bold leading-tight text-[#000000]">
                            Data Pasien Nifas
                        </h1>
                        <p class="text-sm text-[#7C7C7C]">
                            Kelola Data Pasien Nifas Anda
                        </p>
                    </div>

                    {{-- Search bar + tombol download (sejajar) --}}
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        {{-- Kiri: Search --}}
                        <section class="flex items-center gap-3 mt-6 mb-6">
                            <form action="{{ route('dinkes.pasien-nifas') }}" method="GET"
                                class="flex items-center gap-3">
                                <div class="relative w-full max-w-[360px]">
                                    <input type="text" name="q" value="{{ $q ?? '' }}"
                                        placeholder="Search data..."
                                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                        class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                                </div>
                                <button
                                    class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70] transition">
                                    Search
                                </button>
                            </form>
                        </section>

                        {{-- Kanan: Download Data (dummy link) --}}
                        <a href="#"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#B61E7B] px-4 py-2 text-sm font-semibold text-white hover:bg-[#B9257F] transition">
                            <img src="{{ asset('icons/Iconly/Regular/Outline/Paper Download.svg') }}" alt="Download"
                                class="w-5 h-5 invert-[0] brightness-100 saturate-100" loading="lazy" decoding="async">
                            <span>Download Data</span>
                        </a>
                    </div>
                </header>

                {{-- Tabel --}}
                <section class="mt-2 rounded-2xl overflow-hidden border border-[#EDEDED] bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">Role</th>
                                    <th class="px-4 py-3 font-semibold">Tempat, Tanggal Lahir</th>
                                    <th class="px-4 py-3 font-semibold text-center w-[180px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $idx => $row)
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        <td class="pl-6 pr-4 py-3">{{ $rows->firstItem() + $idx }}</td>
                                        <td class="px-4 py-3">{{ $row->name }}</td>
                                        <td class="px-4 py-3">{{ $row->nik }}</td>
                                        <td class="px-4 py-3">{{ $row->role_penanggung }}</td>
                                        <td class="px-4 py-3">
                                            @php
                                                $ttl = [];
                                                if ($row->tempat_lahir) {
                                                    $ttl[] = $row->tempat_lahir;
                                                }
                                                if ($row->tanggal_lahir) {
                                                    $ttl[] = \Carbon\Carbon::parse(
                                                        $row->tanggal_lahir,
                                                    )->translatedFormat('d F Y');
                                                }
                                            @endphp
                                            {{ implode(', ', $ttl) }}
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex justify-end">
                                                <button class="h-2.5 w-2.5 rounded-full bg-[#B61E7B]"
                                                    aria-label="More"></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-6 text-center text-[#7C7C7C]">
                                            Tidak ada data pasien nifas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($rows->hasPages())
                        <div class="px-6 py-4">
                            {{ $rows->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </div>
            <!-- === /KONTEN === -->

            {{-- === FOOTER (selalu nempel bawah) === --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
