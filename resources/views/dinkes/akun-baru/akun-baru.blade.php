<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Pengajuan Akun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
    </head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex flex-col min-h-screen">

        {{-- Sidebar --}}
        <x-dinkes.sidebar />

        {{-- Konten --}}
        <main class="ml-[260px] flex-1 min-h-screen flex flex-col p-8 space-y-8">
            <div class="flex-1">
                <header>
                    <h1 class="text-[28px] font-bold leading-tight text-[#000000]">Daftar Pengajuan Akun</h1>
                    <p class="text-sm text-[#7C7C7C]">Kelola Detail Pengajuan Akun Anda</p>
                </header>

                @if (session('ok'))
                    <div class="mt-4 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                        {{ session('ok') }}
                    </div>
                @endif

                {{-- Search --}}
                <section class="flex items-center gap-3 mt-6 mb-6">
                    <form action="{{ route('dinkes.akun-baru') }}" method="GET"
                          class="flex items-center gap-3">
                        {{-- ✅ Clamp lebar seperti Pasien Nifas/Data Master --}}
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

                {{-- Tabel --}}
                <section class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                            <tr>
                                <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama</th>
                                <th class="px-4 py-3 font-semibold">E-mail</th>
                                <th class="px-4 py-3 font-semibold">Role</th>
                                <th class="px-4 py-3 font-semibold text-center w-[180px]">Aksi</th>
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
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ route('dinkes.akun-baru.approve', $req->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Terima pengajuan akun {{ $req->name }}?');">
                                                @csrf
                                                <button
                                                    class="px-3 py-1 rounded-full bg-[#A3E4D7] text-[#007965] text-xs font-medium hover:opacity-90 transition">
                                                    ✓ Terima
                                                </button>
                                            </form>

                                            <form action="{{ route('dinkes.akun-baru.reject', $req->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Tolak pengajuan akun {{ $req->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="px-3 py-1 rounded-full bg-[#B9257F] text-white text-xs font-medium hover:bg-[#a31f70] transition">
                                                    ✕ Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-[#7C7C7C]">
                                        Tidak ada pengajuan akun.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($requests->hasPages())
                        <div class="px-6 py-4">
                            {{ $requests->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </div>

            {{-- Footer --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>
