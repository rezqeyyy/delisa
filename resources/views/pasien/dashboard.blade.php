<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
    <style>
        /* Mengimpor font Poppins dari Google Fonts agar visual teks 100% cocok dengan desain modern */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-pasien.sidebar class="hidden xl:flex z-30" />

        <!-- Sidebar overlay (mobile) -->
        <x-pasien.sidebar
            x-cloak
            x-show="openSidebar"
            class="xl:hidden z-50 transform"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        />
        <!-- Background overlay untuk menutup -->
        <div
            x-cloak
            x-show="openSidebar"
            class="fixed inset-0 z-40 bg-black/40 xl:hidden"
            @click="openSidebar = false">
        </div>
    
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex flex-wrap items-start gap-4">
                <div class="relative flex-1 min-w-0">
                    <span class="absolute inset-y-0 left-3 flex items-center">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                    </span>
                    <input type="text" placeholder="Search..."
                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end md:justify-start flex-shrink-0">
                    <a class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    <button class="relative w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" class="w-4 h-4 opacity-90" alt="Notif">
                        <span class="absolute top-1.5 right-1.5 inline-block w-2.5 h-2.5 bg-[#B9257F] rounded-full ring-2 ring-white"></span>
                    </button>

                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn" class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            
                            @if (Auth::user()?->photo)
                                <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                    class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                            @else
                                <span
                                    class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                        <path
                                            d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </span>
                            @endif


                            <div class="leading-tight pr-1 text-left">
                                <p class="text-[13px] font-semibold text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Pasien' }}</p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Pasien' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout.pasien') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-[#F9F9F9] flex items-center gap-2">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <section class="bg-white rounded-2xl shadow-md p-6">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="flex-1 min-w-[240px]">
                        <h2 class="text-xl font-semibold text-[#1D1D1D]">List Skrining</h2>
                    </div>

                    <div class="ml-auto flex flex-col md:flex-row items-stretch md:items-center gap-3 w-full md:w-auto">
                        <form action="{{ route('pasien.dashboard') }}" method="GET"
                            class="flex w-full md:w-auto items-center gap-2 flex-wrap md:flex-nowrap">
                            <div class="relative w-full md:w-auto">
                                <select name="status"
                                        class="w-full pl-3 pr-9 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                    @php $currentStatus = $status ?? ''; @endphp
                                    <option value="" {{ $currentStatus === '' ? 'selected' : '' }}>Cari Berdasarkan Status</option>
                                    <option value="Aman" {{ $currentStatus === 'Aman' ? 'selected' : '' }}>Aman</option>
                                    <option value="Waspada" {{ $currentStatus === 'Waspada' ? 'selected' : '' }}>Waspada</option>
                                    <option value="Beresiko" {{ $currentStatus === 'Beresiko' ? 'selected' : '' }}>Beresiko</option>
                                </select>
                            </div>
                            <button class="w-full md:w-auto px-4 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70]">
                                Cari
                            </button>
                        </form>

                        @php
                        $ajukanUrl = \Illuminate\Support\Facades\Route::has('pasien.skrining.create')
                            ? route('pasien.skrining.create')
                            : '#';
                        @endphp
                        <a href="{{ $ajukanUrl }}"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 h-9 rounded-full bg-[#B9257F] text-white text-sm font-semibold shadow hover:bg-[#a31f70]">
                            <span class="text-base leading-none">+</span>
                            <span class="leading-none">Ajukan Skrining</span>
                        </a>
                    </div>
                </div>

                @php
                $badgeClass = function ($st) {
                    $st = strtolower($st ?? '');
                    return match ($st) {
                    'aman', 'normal'    => 'bg-[#2EDB58] text-white',
                    'waspada'           => 'bg-[#FFC700] text-white',
                    'beresiko'          => 'bg-[#EB1D1D] text-white',
                    default             => 'bg-gray-300 text-gray-700',
                    };
                };
                @endphp

                <div class="mt-5 space-y-3">
                @forelse ($skrinings as $skrining)
                    @php
                    $editUrl = \Illuminate\Support\Facades\Route::has('pasien.skrining.edit')
                        ? route('pasien.skrining.edit', $skrining->id)
                        : '#';
                    $viewUrl = \Illuminate\Support\Facades\Route::has('pasien.skrining.show')
                        ? route('pasien.skrining.show', $skrining->id)
                        : '#';
                    $namaPasien = optional($skrining->pasien?->user)->name ?? (auth()->user()->name ?? 'Pasien');
                    $alamat = auth()->user()->address ?? ($skrining->pasien?->PKabupaten ?? '-');
                    @endphp

                    <div class="flex items-center justify-between bg-[#F7F7F7] rounded-xl px-4 py-3">
                    <div class="flex items-center gap-4 min-w-0">
                        <span class="w-8 h-8 rounded-full bg-[#EFEFEF] flex items-center justify-center">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Profile.svg') }}" class="w-4 h-4 opacity-80" alt="avatar">
                        </span>

                        <div class="w-[200px]">
                        <div class="text-sm font-medium text-[#1D1D1D] truncate">{{ $namaPasien }}</div>
                        <div class="text-xs text-[#7C7C7C]">Nama Pasien</div>
                        </div>

                        <div class="w-[160px]">
                        <div class="text-sm font-medium text-[#1D1D1D]">
                            {{ optional($skrining->created_at)->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-[#7C7C7C]">Tanggal Pengisian</div>
                        </div>

                        <div class="w-[160px]">
                        <div class="text-sm font-medium text-[#1D1D1D] truncate">{{ $alamat }}</div>
                        <div class="text-xs text-[#7C7C7C]">Alamat</div>
                        </div>

                        <div class="w-[160px]">
                        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold {{ $badgeClass($skrining->kesimpulan) }}">
                            {{ $skrining->kesimpulan ?? '—' }}
                        </span>
                        <div class="text-xs text-[#7C7C7C] mt-1">Kesimpulan</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ $editUrl }}" class="px-3 py-1.5 rounded-full bg-white border border-[#E5E5E5] text-xs hover:bg-[#F0F0F0]">
                        Edit
                        </a>
                        <a href="{{ $viewUrl }}" class="px-3 py-1.5 rounded-full bg-white border border-[#E5E5E5] text-xs hover:bg-[#F0F0F0]">
                        View
                        </a>
                    </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-[#7C7C7C] py-8">
                    Belum ada data skrining.
                    </div>
                @endforelse
                </div>

                @if(isset($skrinings) && method_exists($skrinings, 'hasPages') && $skrinings->hasPages())
                <div class="mt-4">
                    {{ $skrinings->onEachSide(1)->links() }}
                </div>
                @endif
            </section>


            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>

    </div>
</body>
</html>
