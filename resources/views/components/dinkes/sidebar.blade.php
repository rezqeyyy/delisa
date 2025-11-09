@props(['current' => request()->route()->getName()])

@php
    // Warna aktif dan idle
    $active = 'group flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white font-medium rounded-xl transition';
    $idle = 'group flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-xl transition';

    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

{{-- ========== SIDEBAR ========== --}}
<aside id="sidebar" aria-label="Navigasi Dinkes"
    class="fixed top-0 left-0 z-50 h-screen w-[260px] bg-white shadow-lg
           flex flex-col
           transform transition-transform duration-200
           -translate-x-full lg:translate-x-0">

    <!-- Bagian atas (logo + tombol close mobile) -->
    <div class="shrink-0">
        <div class="flex items-center justify-between gap-3 p-6 border-b border-[#CAC7C7]">
            <a href="{{ route('dinkes.dashboard') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
            </a>

            <!-- Tombol close: hanya tampil di mobile -->
            <button id="sidebarCloseBtn"
                class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#E5E5E5] bg-white hover:bg-[#F8F8F8] transition"
                aria-label="Tutup menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#1D1D1D]" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>

        </div>

        <!-- Navigasi utama -->
        <nav class="mt-4 px-3 pb-4 overflow-y-auto max-h-[calc(100vh-150px)] space-y-2">
            <a href="{{ route('dinkes.dashboard') }}" class="{{ $is('dinkes.dashboard*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36729.png') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Dashboard">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dinkes.data-master') }}" class="{{ $is('dinkes.data-master*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36805.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Data Master">
                <span>Data Master</span>
            </a>

            <a href="{{ route('dinkes.akun-baru') }}" class="{{ $is('dinkes.akun-baru*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Akun Baru">
                <span>Akun Baru</span>
            </a>

            <a href="{{ route('dinkes.pasien-nifas') }}" class="{{ $is('dinkes.pasien-nifas*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pasien Nifas">
                <span>Pasien Nifas</span>
            </a>
        </nav>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-[#F0F0F0]">
        @csrf
        <button type="submit"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl text-[#7C7C7C] hover:bg-[#F3F3F3]">
            <img src="{{ asset('icons/Iconly/Sharp/Light/Profile.svg') }}" alt="Logout" class="w-4 h-4">
            Logout
        </button>
    </form>
</aside>

{{-- ========== HANDLE PEMBUKA (MOBILE) ========== --}}
<button id="sidebarOpenBtn"
    class="lg:hidden fixed z-40 left-1 top-1/2 -translate-y-1/2
           inline-flex items-center justify-center
           w-7 h-20 rounded-r-xl bg-white border border-[#E5E5E5] shadow
           focus:outline-none focus:ring-2 focus:ring-black/10"
    aria-controls="sidebar" aria-label="Buka menu">
    {{-- ikon panah ke kanan --}}
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor"
        stroke-width="2">
        <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span class="sr-only">Buka menu</span>
</button>
