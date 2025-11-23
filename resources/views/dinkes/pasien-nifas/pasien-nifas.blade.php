<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Pasien Nifas</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/dinkes/pasien-nifas.js',
        'resources/js/dinkes/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    <div class="flex flex-col min-h-screen">
        <x-dinkes.sidebar />

        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex-1">
                <header class="w-full space-y-3 sm:space-y-4">
                    <div>
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000000]">
                            Data Pasien Nifas
                        </h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Kelola Data Pasien Nifas Anda
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <section class="flex items-center gap-3">
                            <form action="{{ route('dinkes.pasien-nifas') }}" method="GET"
                                class="flex w-full md:w-auto items-center gap-3">
                                <div class="relative w-full md:w-[360px]">
                                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search data..."
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

                        <a href="{{ route('dinkes.pasien-nifas.export', ['q' => $q ?? null]) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#B61E7B] px-4 py-2 text-sm font-semibold text-white hover:bg-[#B9257F] transition">
                            <img src="{{ asset('icons/Iconly/Regular/Outline/Paper Download.svg') }}" alt="Download"
                                class="w-5 h-5">
                            <span>Unduh Data</span>
                        </a>
                    </div>
                </header>

                {{-- ====== LIST: mode KARTU (mobile) ====== --}}
                <section class="md:hidden mt-2 space-y-3">
                    @forelse ($rows as $idx => $row)
                        <article class="rounded-2xl bg-white shadow p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-xs text-[#7C7C7C]">#{{ $rows->firstItem() + $idx }}</div>
                                    <h3 class="font-semibold text-base leading-snug truncate">{{ $row->name }}</h3>
                                    <div class="text-xs text-[#7C7C7C] break-all">NIK: {{ $row->nik }}</div>
                                    <div class="text-xs mt-1">
                                        <span class="text-[#7C7C7C]">Role:</span> {{ $row->role_penanggung }}
                                    </div>
                                    <div class="text-xs mt-1">
                                        <span class="text-[#7C7C7C]">TTL:</span>
                                        @php
                                            $ttl = [];
                                            if ($row->tempat_lahir) {
                                                $ttl[] = $row->tempat_lahir;
                                            }
                                            if ($row->tanggal_lahir) {
                                                $ttl[] = \Carbon\Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y');
                                            }
                                        @endphp
                                        {{ implode(', ', $ttl) }}
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('dinkes.pasien-nifas.show', $row->id) }}"
                                        class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs flex items-center justify-center hover:bg-[#F5F5F5] transition">
                                        View
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center text-[#7C7C7C] py-6">
                            Tidak ada data pasien nifas.
                        </div>
                    @endforelse

                    @if ($rows->hasPages())
                        <div class="pt-2">
                            {{ $rows->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- ====== TABEL (desktop & tablet) ====== --}}
                <section class="hidden md:block mt-2 rounded-2xl overflow-hidden border border-[#EDEDED] bg-white">
                    <div class="overflow-x-auto">
                        <table class="min-w-[900px] w-full text-sm text-left border-collapse">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">Role Penanggung</th>
                                    <th class="px-4 py-3 font-semibold">Tempat, Tanggal Lahir</th>
                                    <th class="px-4 py-3 font-semibold text-center w-[180px]">Detail</th>
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
                                                    $ttl[] = \Carbon\Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y');
                                                }
                                            @endphp
                                            {{ implode(', ', $ttl) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('dinkes.pasien-nifas.show', $row->id) }}"
                                                    class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70] transition">
                                                    View
                                                </a>
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
                        @php
                            $from = $rows->firstItem() ?? 0;
                            $to = $rows->lastItem() ?? 0;
                            $tot = $rows->total();
                        @endphp
                        <div
                            class="px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
                            <div class="text-[#7C7C7C]">
                                Menampilkan
                                <span class="font-medium text-[#000000cc]">{{ $from }}</span>–<span
                                    class="font-medium text-[#000000cc]">{{ $to }}</span>
                                dari
                                <span class="font-medium text-[#000000cc]">{{ $tot }}</span>
                                data
                            </div>
                            <div>
                                {{ $rows->onEachSide(1)->links() }}
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
