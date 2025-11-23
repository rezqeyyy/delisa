<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Pengajuan Akun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex flex-col min-h-screen">
        {{-- Sidebar --}}
        <x-dinkes.sidebar />

        {{-- Konten --}}
        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="flex-1">
                <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000000]">Daftar Pengajuan
                            Akun</h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">Kelola Detail Pengajuan Akun Anda</p>
                    </div>
                </header>

                @if (session('ok'))
                    <div class="mt-4 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                        {{ session('ok') }}
                    </div>
                @endif

                {{-- Search --}}
                <section class="flex flex-col sm:flex-row sm:items-center gap-3 mt-4 sm:mt-6 mb-4 sm:mb-6">
                    <form action="{{ route('dinkes.akun-baru') }}" method="GET"
                        class="flex w-full sm:w-auto items-center gap-3">
                        <div class="relative w-full sm:w-[360px]">
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

                {{-- ====== LIST: mode KARTU (mobile) ====== --}}
                <section class="md:hidden space-y-3">
                    @forelse ($requests as $index => $req)
                        <article class="bg-white rounded-2xl shadow p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-xs text-[#7C7C7C]">#{{ $requests->firstItem() + $index }}</div>
                                    <h3 class="font-semibold text-base leading-snug truncate">{{ $req->name }}</h3>
                                    <div class="text-xs text-[#7C7C7C] break-all">{{ $req->email }}</div>
                                    <div class="mt-1 text-xs"><span class="text-[#7C7C7C]">Role:</span>
                                        {{ $req->role->nama_role ?? '-' }}</div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <form action="{{ route('dinkes.akun-baru.approve', $req->id) }}" method="POST"
                                        data-confirm="Terima pengajuan akun {{ $req->name }}?">
                                        @csrf
                                        <button
                                            class="px-3 py-1 rounded-full bg-[#A3E4D7] text-[#007965] text-xs font-medium hover:opacity-90 transition w-full">
                                            ✓ Terima
                                        </button>
                                    </form>

                                    <form action="{{ route('dinkes.akun-baru.reject', $req->id) }}" method="POST"
                                        data-confirm="Tolak pengajuan akun {{ $req->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="px-3 py-1 rounded-full bg-[#B9257F] text-white text-xs font-medium hover:bg-[#a31f70] transition w-full">
                                            ✕ Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center text-[#7C7C7C] py-6">Tidak ada pengajuan akun.</div>
                    @endforelse

                    @if ($requests->hasPages())
                        <div class="pt-2">
                            {{ $requests->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- ====== TABEL (desktop & tablet) ====== --}}
                <section class="hidden md:block bg-white rounded-2xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[760px] w-full text-sm text-left border-collapse">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">E-mail</th>
                                    <th class="px-4 py-3 font-semibold">Role</th>
                                    <th class="px-4 py-3 font-semibold text-center w-[220px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $index => $req)
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        <td class="pl-6 pr-4 py-3">{{ $requests->firstItem() + $index }}</td>
                                        <td class="px-4 py-3">{{ $req->name }}</td>
                                        <td class="px-4 py-3">{{ $req->email }}</td>
                                        <td class="px-4 py-3">{{ $req->role->nama_role ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center flex-wrap gap-2">
                                                <form action="{{ route('dinkes.akun-baru.approve', $req->id) }}"
                                                    method="POST"
                                                    data-confirm="Terima pengajuan akun {{ $req->name }}?">
                                                    @csrf
                                                    <button
                                                        class="px-3 py-3 rounded-full bg-[#A3E4D7] text-[#007965] text-xs font-medium hover:opacity-90 transition">
                                                        ✓ Terima
                                                    </button>
                                                </form>

                                                <form action="{{ route('dinkes.akun-baru.reject', $req->id) }}"
                                                    method="POST"
                                                    data-confirm="Tolak pengajuan akun {{ $req->name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="px-3 py-3 rounded-full bg-[#B9257F] text-white text-xs font-medium hover:bg-[#a31f70] transition">
                                                        ✕ Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-[#7C7C7C]">Tidak ada pengajuan
                                            akun.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($requests->total() > 0)
                        @php
                            $from = $requests->firstItem() ?? 0;
                            $to = $requests->lastItem() ?? 0;
                            $tot = $requests->total();
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
                                {{ $requests->onEachSide(1)->links() }}
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
