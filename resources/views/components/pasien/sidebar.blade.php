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
<aside id="sidebar" aria-label="Navigasi Pasien"
    class="fixed top-0 left-0 z-50 h-screen w-[260px] bg-white shadow-lg
           flex flex-col
           transform transition-transform duration-200
           -translate-x-full lg:translate-x-0">

    <!-- Bagian atas (logo + tombol close mobile) -->
    <div class="shrink-0">
        <div class="flex items-center justify-between gap-3 p-6 border-b border-[#CAC7C7]">
            <a href="{{ route('pasien.dashboard') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
            </a>

            <!-- Tombol close: hanya tampil di mobile -->
            <button id="sidebarCloseBtn" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#E5E5E5] bg-white hover:bg-[#F8F8F8] transition" aria-label="Tutup menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#1D1D1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Navigasi utama -->
        <nav class="mt-4 px-3 pb-4 overflow-y-auto max-h-[calc(100vh-150px)] space-y-4">
            {{-- HOME section --}}
            <div class="space-y-2">
                <p class="px-6 text-[13px] leading-[46px] font-medium text-black/50 tracking-[0.15em] uppercase">HOME                    
                </p>

                <a href="{{ route('pasien.dashboard') }}" class="{{ $is('pasien.dashboard*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Group 36729.png') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Dashboard">
                    <span>Dashboard</span>
                </a>                
            </div>

            {{-- ACCOUNT section --}}
            <div class="space-y-2 pt-2">
                <p class="px-6 text-[13px] leading-[46px] font-medium text-black/50 tracking-[0.15em] uppercase">ACCOUNT                    
                </p>
                
                <a href="{{ route('pasien.profile.edit') }}" class="{{ $is('pasien.profile*') ? $active : $idle }}">
                    <img src="{{ asset('icons/Iconly/Regular/Outline/Setting.svg') }}" class="w-4 h-4 transition group-hover:brightness-0 group-hover:invert" alt="Pengaturan">
                    <span>Pengaturan</span>
                </a>

                <!-- Keluar -->
                <form method="POST" action="{{ route(\Illuminate\Support\Facades\Route::has('logout.pasien') ? 'logout.pasien' : 'logout') }}">
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
    class="lg:hidden fixed z-40 left-1 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-7 h-20 rounded-r-xl bg-white border border-[#E5E5E5] shadow focus:outline-none focus:ring-2 focus:ring-black/10"
    aria-controls="sidebar" aria-label="Buka menu">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span class="sr-only">Buka menu</span>
</button>