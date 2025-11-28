<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Pasien Nifas</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/pasien-nifas.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    <div class="flex flex-col min-h-screen">
        <x-dinkes.sidebar />

        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex-1">
                {{-- HEADER --}}
                <header class="w-full space-y-3 sm:space-y-4">
                    <div>
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000000]">
                            Data Pemantauan Pasien Nifas
                        </h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Pantau jadwal kunjungan nifas (KF) di seluruh Puskesmas dan fasilitas rujukan.
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                        {{-- FORM SEARCH (kiri) --}}
                        <form action="{{ route('dinkes.pasien-nifas') }}" method="GET" class="w-full md:w-auto">
                            <div class="relative w-full lg:w-[320px]">
                                <input type="text" name="q" value="{{ $q ?? '' }}"
                                    placeholder="Cari nama atau NIK..."
                                    class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm
                           focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                    class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                            </div>
                        </form>

                        {{-- FILTER + UNDUH DATA (pojok kanan atas) --}}
                        <div class="flex items-center justify-end gap-2 md:w-auto" x-data="{ openFilter: false }">

                            {{-- FORM FILTER (hanya untuk puskesmas + sort) --}}
                            <form action="{{ route('dinkes.pasien-nifas') }}" method="GET" class="relative">
                                {{-- Pertahankan q saat apply filter --}}
                                <input type="hidden" name="q" value="{{ $q ?? '' }}">

                                {{-- Tombol FILTER --}}
                                <button type="button" @click="openFilter = !openFilter"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#B61E7B]
                               px-4 py-2 text-sm font-semibold text-white hover:bg-[#B9257F] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M6 12h12M10 18h4" />
                                    </svg>
                                    <span>Filter</span>
                                </button>

                                {{-- Dropdown Filter --}}
                                <div x-show="openFilter" x-transition @click.outside="openFilter = false"
                                    class="origin-top-right absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl
                            shadow-lg border border-[#E5E5E5] z-20">
                                    <div class="p-4 space-y-3">

                                        {{-- Filter Puskesmas --}}
                                        <div class="space-y-1">
                                            <p class="text-xs font-semibold text-[#6B7280]">Filter Puskesmas</p>
                                            <select name="puskesmas_id"
                                                class="w-full px-3 py-2 rounded-xl border border-[#D9D9D9]
                                           text-xs sm:text-sm focus:outline-none focus:ring-1
                                           focus:ring-[#B9257F]/40 bg-white">
                                                <option value="">Semua Puskesmas</option>
                                                @foreach ($puskesmasList as $pk)
                                                    <option value="{{ $pk->id }}" @selected((string) ($selectedPuskesmasId ?? '') === (string) $pk->id)>
                                                        {{ $pk->nama_puskesmas }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Filter Sort --}}
                                        <div class="space-y-1">
                                            <p class="text-xs font-semibold text-[#6B7280]">Urutkan</p>
                                            <select name="sort"
                                                class="w-full px-3 py-2 rounded-xl border border-[#D9D9D9]
                                           text-xs sm:text-sm focus:outline-none focus:ring-1
                                           focus:ring-[#B9257F]/40 bg-white">
                                                <option value="prioritas" @selected(($sort ?? 'prioritas') === 'prioritas')>
                                                    Prioritas (Hitam → Hijau)
                                                </option>
                                                <option value="nama_asc" @selected(($sort ?? '') === 'nama_asc')>
                                                    Nama A → Z
                                                </option>
                                                <option value="nama_desc" @selected(($sort ?? '') === 'nama_desc')>
                                                    Nama Z → A
                                                </option>
                                                <option value="kf_terbaru" @selected(($sort ?? '') === 'kf_terbaru')>
                                                    KF Terbaru
                                                </option>
                                                <option value="kf_terlama" @selected(($sort ?? '') === 'kf_terlama')>
                                                    KF Terlama
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Tombol reset & submit --}}
                                        <div class="flex items-center justify-between gap-2 pt-2">
                                            {{-- Reset filter, tapi q tetap dipertahankan --}}
                                            <a href="{{ route('dinkes.pasien-nifas', ['q' => $q ?: null]) }}"
                                                class="px-3 py-2 rounded-full border border-[#D1D5DB]
                                      text-xs sm:text-sm text-[#4B5563] hover:bg-[#F3F4F6] transition">
                                                Reset
                                            </a>
                                            <button type="submit"
                                                class="px-4 py-2 rounded-full bg-[#B9257F] text-white
                                           text-xs sm:text-sm font-semibold hover:bg-[#a31f70] transition">
                                                Terapkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            {{-- TOMBOL EXPORT — MENEMPEL DI KANAN FILTER --}}
                            <a href="{{ route('dinkes.pasien-nifas.export', [
                                'q' => $q ?? null,
                                'puskesmas_id' => $selectedPuskesmasId ?? null,
                                'sort' => $sort ?? null,
                            ]) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#B61E7B]
                      px-4 py-2 text-sm font-semibold text-white hover:bg-[#B9257F] transition
                      ">
                                <img src="{{ asset('icons/Iconly/Regular/Outline/Paper Download.svg') }}"
                                    class="w-5 h-5" alt="Download">
                                <span>Unduh Data</span>
                            </a>
                        </div>
                    </div>
                </header>



                {{-- LIST MODE KARTU (MOBILE) --}}
                <section class="md:hidden mt-2 space-y-3">
                    @forelse ($rows as $idx => $row)
                        <article class="rounded-2xl bg-white shadow p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 space-y-1.5">
                                    <div class="text-xs text-[#7C7C7C]">
                                        #{{ $rows->firstItem() + $idx }}
                                    </div>

                                    <h3 class="font-semibold text-base leading-snug truncate">
                                        {{ $row->name }}
                                    </h3>

                                    <div class="text-xs text-[#7C7C7C] break-all">
                                        NIK: {{ $row->nik }}
                                    </div>

                                    <div class="text-xs">
                                        <span class="text-[#7C7C7C]">Puskesmas:</span>
                                        <span class="font-medium">
                                            {{ $row->puskesmas_nama ?? '—' }}
                                        </span>
                                    </div>

                                    <div class="text-xs">
                                        <span class="text-[#7C7C7C]">Jadwal KF:</span>
                                        <span class="block">
                                            {{ $row->jadwal_kf_text }}
                                        </span>
                                    </div>

                                    <div class="text-xs flex items-center gap-2 mt-1.5">
                                        <span class="text-[#7C7C7C]">Sisa waktu:</span>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold {{ $row->badge_class }}">
                                            {{ $row->sisa_waktu_label }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('dinkes.pasien-nifas.show', $row->nifas_id) }}"
                                        class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs flex items-center justify-center hover:bg-[#F5F5F5] transition">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center text-[#7C7C7C] py-6">
                            Belum ada data pemantauan nifas.
                        </div>
                    @endforelse

                    @if ($rows->hasPages())
                        <div class="pt-2">
                            {{ $rows->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- TABEL (DESKTOP / TABLET) --}}
                <section class="hidden md:block mt-2 rounded-2xl overflow-hidden border border-[#EDEDED] bg-white">
                    <div class="overflow-x-auto">
                        <table class="min-w-[900px] w-full text-sm text-left border-collapse">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">Puskesmas</th>
                                    <th class="px-4 py-3 font-semibold">Jadwal KF</th>
                                    <th class="px-4 py-3 font-semibold text-center w-[160px]">
                                        Sisa Waktu
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $idx => $row)
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        {{-- No --}}
                                        <td class="pl-6 pr-4 py-3">
                                            {{ $rows->firstItem() + $idx }}
                                        </td>

                                        {{-- Nama (link ke detail) --}}
                                        <td class="px-4 py-3">
                                            <a href="{{ route('dinkes.pasien-nifas.show', $row->nifas_id) }}"
                                                class="font-medium text-[#000000] hover:underline">
                                                {{ $row->name }}
                                            </a>
                                        </td>

                                        {{-- NIK --}}
                                        <td class="px-4 py-3">
                                            {{ $row->nik }}
                                        </td>

                                        {{-- Puskesmas --}}
                                        <td class="px-4 py-3">
                                            {{ $row->puskesmas_nama ?? '—' }}
                                        </td>

                                        {{-- Jadwal KF --}}
                                        <td class="px-4 py-3">
                                            {{ $row->jadwal_kf_text }}
                                        </td>

                                        {{-- Sisa Waktu --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $row->badge_class }}">
                                                    {{ $row->sisa_waktu_label }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-6 text-center text-[#7C7C7C]">
                                            Belum ada data pemantauan nifas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
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
