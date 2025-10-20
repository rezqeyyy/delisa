@props(['current' => request()->route()->getName()])

@php
    // Kelas aktif & idle
    $active = 'flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white font-medium';
    $idle = 'flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] transition';

    // Fungsi cek route aktif
    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

<aside {{ $attributes->merge(['class' => 'fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-center gap-3 p-6 border-b border-[#CAC7C7]">
            <img src="{{ asset('images/logo_fulltext.png') }}" alt="DeLISA" class="w-42 h-auto object-contain">
        </div>

        <nav class="mt-6 space-y-1">
            <a href="{{ route('pasien.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $current === 'pasien.dashboard' ? $active : $idle }}">
                <img src="{{ asset('icons/Group 36729.png') }}" class="w-4 h-4" alt="Dashboard">
                <span>Dashboard</span>
            </a>
            
            <div class="px-4">
                <div class="mt-6 text-[11px] text-[#7C7C7C] mb-2">ACCOUNT</div>

                @php
                    $logoutRoute = \Illuminate\Support\Facades\Route::has('logout.pasien') ? 'logout.pasien' : 'logout';
                @endphp
                <form method="POST" action="{{ route($logoutRoute) }}" class="mt-2">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl text-[#7C7C7C] hover:bg-[#F3F3F3]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Profile.svg') }}" alt="Logout" class="w-4 h-4">
                        Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>
</aside>