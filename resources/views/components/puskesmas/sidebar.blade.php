@props(['current' => request()->route()?->getName()])

@php
    // Warna aktif dan idle
    $active = 'flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white font-medium rounded-xl transition';
    $idle = 'flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] transition';

    $is = fn($pattern) => request()->route() && request()->routeIs($pattern);
@endphp

<aside class="fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between overflow-hidden">
    <div>
        <!-- Logo + Icon Settings -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#CAC7C7]">
            <!-- Logo -->
            <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">

            <!-- Ikon Settings (dijaga agar tidak keluar) -->
            <button class="p-0 text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-.426 1.038-.426 1.464 0l1.596 1.596m-7.5 7.5l1.596 1.596m0 0a11.25 11.25 0 1015.912-15.912L16.5 16.5M12 12l4.5 4.5" />
                </svg>
            </button>
        </div>

        <!-- Navigasi utama -->
        <nav class="mt-4 space-y-1 px-4">
            <a href="{{ route('puskesmas.dashboard') }}" class="rounded-xl {{ $is('puskesmas.dashboard*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36729.png') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Dashboard">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('puskesmas.skrining') }}"
                class="rounded-xl {{ $is('puskesmas.skrining*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36805.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Skrining">
                <span>Skrining</span>
            </a>

            <a href="{{ route('puskesmas.laporan') }}"
                class="rounded-xl {{ $is('puskesmas.laporan*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Laporan">
                <span>Laporan</span>
            </a>

            <a href="{{ route('puskesmas.pasien-nifas') }}"
                class="rounded-xl {{ $is('puskesmas.pasien-nifas*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pasien Nifas">
                <span>Pasien Nifas</span>
            </a>
        </nav>

        <!-- Section Account -->
        <div class="mt-8 px-6 pb-2">
            <h3 class="text-xs font-medium text-[#7C7C7C] uppercase tracking-wider">Account</h3>
        </div>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="w-full text-left flex items-center gap-3 px-6 py-3 rounded-xl text-[#7C7C7C] hover:bg-[#F3F3F3] transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 6v-6" />
            </svg>
            Logout
        </button>
    </form>
</aside>