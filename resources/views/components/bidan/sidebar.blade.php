@props(['current' => request()->route()->getName()])

@php
    // Kelas aktif & idle (sekarang lebar penuh: w-full)
    $active =
        'group flex items-center gap-3 w-full px-6 py-3 bg-[#B9257F] text-white font-medium rounded-xl transition';
    $idle =
        'group flex items-center gap-3 w-full px-6 py-3 hover:text-white hover:font-medium hover:bg-[#B9257F] rounded-xl transition';

    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

{{-- ========== SIDEBAR ========== --}}
<aside id="sidebar" aria-label="Navigasi Bidan"
    class="fixed top-0 left-0 z-50 h-screen w-[260px] bg-white shadow-lg
           flex flex-col
           transform transition-transform duration-200
           -translate-x-full">

    <!-- Bagian atas (logo + tombol close mobile) -->
    <div class="shrink-0">
        <div class="flex items-center justify-between gap-3 p-6 border-b border-[#CAC7C7]">
            <a href="{{ route('bidan.dashboard') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
            </a>

            <!-- Tombol close minimal tanpa border/shadow -->
            <button id="sidebarCloseBtn" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-transparent hover:bg-black/5 transition outline-none focus:outline-none ring-0 shadow-none border-0" aria-label="Tutup menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#1D1D1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
        </div>

        <nav class="mt-4 px-3 pb-4 overflow-y-auto max-h-[calc(100vh-150px)] space-y-4">
            <div class="space-y-2">
                <p class="px-6 text-[13px] leading-[46px] font-medium text-black/50 tracking-[0.15em] uppercase">HOME</p>

                <a href="{{ route('bidan.dashboard') }}" class="{{ $is('bidan.dashboard*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Group 36729.png') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Dashboard">
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('bidan.skrining') }}" class="{{ $is('bidan.skrining*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Group 36805.svg') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Skrining">
                    <span>Skrining</span>
                </a>

                <a href="{{ route('bidan.pasien-nifas') }}" class="{{ $is('bidan.pasien-nifas*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pasien Nifas">
                    <span>Pasien Nifas</span>
                </a>
            </div>

            <div class="space-y-2 pt-2">
                <p class="px-6 text-[13px] leading-[46px] font-medium text-black/50 tracking-[0.15em] uppercase">ACCOUNT</p>

                <a href="{{ route('bidan.profile.edit') }}" class="{{ $is('bidan.profile*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Iconly/Regular/Outline/Setting.svg') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pengaturan">
                    <span>Pengaturan</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="{{ $idle }} text-[#E53935] underline underline-offset-4 decoration-2 hover:text-white">
                        <img src="{{ asset('icons/Iconly/Regular/Outline/Logout.svg') }}" alt="Keluar" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert">
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>
</aside>

<button id="sidebarOpenBtn"
    class="fixed z-40 left-1 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-7 h-20 rounded-r-xl bg-white border border-[#E5E5E5] shadow focus:outline-none focus:ring-2 focus:ring-black/10"
    aria-controls="sidebar" aria-label="Buka menu">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span class="sr-only">Buka menu</span>
</button>