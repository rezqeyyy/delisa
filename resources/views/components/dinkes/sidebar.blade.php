@props(['current' => request()->route()->getName()])

@php
    // Kelas aktif & idle
    $active = 'flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white font-medium';
    $idle = 'flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] transition';

    // Fungsi cek route aktif
    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

<aside class="fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between">
    <div>
        <!-- Logo -->
        <div class="flex items-center gap-3 p-6 border-b border-[#CAC7C7]">
            <img src="{{ asset('images/logo_fulltext 2.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
        </div>

        <!-- Navigasi utama -->
        <nav class="mt-6 space-y-1">

            <a href="{{ route('dinkes.dashboard') }}" class="{{ $is('dinkes.dashboard*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36729.png') }}" class="w-4 h-4" alt="Dashboard">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dinkes.data-master') }}" class="{{ $is('dinkes.data-master*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36805.svg') }}" class="w-4 h-4" alt="Data Master">
                <span>Data Master</span>
            </a>

            <a href="{{ route('dinkes.akun-baru') }}" class="{{ $is('dinkes.akun-baru*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="w-4 h-4" alt="Akun Baru">
                <span>Akun Baru</span>
            </a>

            <a href="{{ route('dinkes.pasien-nifas') }}" class="{{ $is('dinkes.pasien-nifas*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}" class="w-4 h-4" alt="Pasien Nifas">
                <span>Pasien Nifas</span>
            </a>
        </nav>
    </div>
</aside>
