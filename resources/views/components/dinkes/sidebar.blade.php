@props(['current' => request()->route()->getName()])

@php
    // Warna aktif dan idle
    $active = 'flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white font-medium rounded-xl transition';
    $idle = 'flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] transition';

    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

<aside class="fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between">
    <div>
        <!-- Logo -->
        <div class="flex items-center gap-3 p-6 border-b border-[#CAC7C7]">
            <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
        </div>

        <!-- Navigasi utama -->
        <nav class="mt-6 space-y-1">

            <a href="{{ route('dinkes.dashboard') }}" class="rounded-xl {{ $is('dinkes.dashboard*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36729.png') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Dashboard">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dinkes.data-master') }}"
                class="rounded-xl {{ $is('dinkes.data-master*') ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36805.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Data Master">
                <span>Data Master</span>
            </a>

            <a href="{{ route('dinkes.akun-baru') }}"
                class="rounded-xl {{ $is('dinkes.akun-baru*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Akun Baru">
                <span>Akun Baru</span>
            </a>

            <a href="{{ route('dinkes.pasien-nifas') }}"
                class="rounded-xl {{ $is('dinkes.pasien-nifas*') ? $active : $idle }}">
                <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}"
                    class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pasien Nifas">
                <span>Pasien Nifas</span>
            </a>
        </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl text-[#7C7C7C] hover:bg-[#F3F3F3]">
            <img src="{{ asset('icons/Iconly/Sharp/Light/Profile.svg') }}" alt="Logout" class="w-4 h-4">
            Logout
        </button>
    </form>
</aside>
