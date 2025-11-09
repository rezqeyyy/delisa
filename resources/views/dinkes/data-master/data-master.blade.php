<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>DINKES – Data Master</title>
    @vite(['resources/css/app.css','resources/js/app.js','resources/js/dropdown.js','resources/js/dinkes/dinkes-data-master.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
<div class="flex flex-col min-h-screen">

    {{-- Sidebar (fixed) --}}
    <x-dinkes.sidebar />

    {{-- Main --}}
    <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="flex-1">
            {{-- Header --}}
            <header class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">Daftar Akun</h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">Kelola Detail Akun Anda</p>
                    </div>
                    <div class="sm:hidden">
                        <a href="{{ route('dinkes.data-master.create', ['tab' => $tab]) }}"
                           class="inline-flex items-center gap-2 bg-[#B9257F] text-white px-4 py-2 rounded-full text-sm font-medium shadow-md hover:bg-[#a31f70]">
                            <span class="text-lg font-bold">+</span> Tambah Akun
                        </a>
                    </div>
                </div>
            </header>

            {{-- Flash / Errors --}}
            @if (session('ok'))
                <div class="flash-alert mb-3 flex items-start gap-3 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700 transition-opacity duration-500"
                     role="alert" data-flash data-timeout="3500">
                    <span class="mt-0.5">✅</span>
                    <div class="flex-1">{{ session('ok') }}</div>
                    <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-alert mb-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 transition-opacity duration-500"
                     role="alert" data-flash data-timeout="3500">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5">⚠️</span>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <section class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
                <a href="{{ route('dinkes.data-master', ['tab' => 'bidan', 'q' => $q ?? '']) }}"
                   class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab==='bidan' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                    Bidan PKM
                </a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'rs', 'q' => $q ?? '']) }}"
                   class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab==='rs' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                    Rumah Sakit
                </a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'puskesmas', 'q' => $q ?? '']) }}"
                   class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab==='puskesmas' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                    Puskesmas
                </a>
            </section>

            {{-- Search + Tambah --}}
            <section class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4 sm:mb-6">
                <form action="{{ route('dinkes.data-master') }}" method="GET" class="flex w-full sm:w-auto items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative w-full sm:w-[360px]">
                        <input name="q" value="{{ $q ?? '' }}" type="text" placeholder="Search data..."
                               class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                             class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                    </div>
                    <button class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium">Search</button>
                </form>

                <div class="hidden sm:block sm:ml-auto">
                    <a href="{{ route('dinkes.data-master.create', ['tab' => $tab]) }}" id="btnTambahAkun"
                       class="flex items-center gap-2 bg-[#B9257F] text-white px-5 py-2 rounded-full text-sm font-medium shadow-md hover:bg-[#a31f70] transition">
                        <span class="text-lg font-bold">+</span> Tambah Akun
                    </a>
                </div>
            </section>

            {{-- 🔐 Password baru (auto-hide) --}}
            @if (session('new_password'))
                <div id="pwToast"
                     class="mb-3 flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800 shadow transition-all duration-300"
                     data-timeout="5000" role="status">
                    <div class="mt-0.5">🔐</div>
                    <div class="flex-1">
                        <div class="font-semibold">Password baru telah dibuat</div>
                        <div class="mt-1">
                            <span class="inline-block font-mono px-2 py-1 rounded bg-white border border-amber-200 select-all" id="pwValue">
                                {{ session('new_password') }}
                            </span>
                            <button type="button" id="pwCopyBtn"
                                    class="ml-2 inline-flex items-center h-7 px-2 rounded border border-amber-300 hover:bg-amber-100">
                                Salin
                            </button>
                        </div>
                        <p class="mt-1 text-xs opacity-80">Simpan password ini dengan aman. Kotak ini akan hilang otomatis.</p>
                    </div>
                    <button type="button" id="pwToastClose" class="opacity-60 hover:opacity-100">✕</button>
                </div>
            @endif

            {{-- ====== LIST: mode KARTU (mobile) ====== --}}
            <section class="md:hidden space-y-3" id="dataMasterCards">
                @forelse ($accounts as $index => $acc)
                    <article class="bg-white rounded-2xl shadow p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs text-[#7C7C7C]">#{{ $accounts->firstItem() + $index }}</div>
                                <h3 class="font-semibold text-base leading-snug">{{ $acc->name }}</h3>
                                <div class="text-xs text-[#7C7C7C] break-all">{{ $acc->email }}</div>
                            </div>
                            {{-- Menu aksi versi kecil: tumpuk --}}
                            <div class="flex flex-col gap-2">
                                <form method="POST"
                                      action="{{ route('dinkes.data-master.reset', ['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                      data-confirm="Reset password untuk {{ $acc->name }}?">
                                    @csrf
                                    <button class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">Reset Password</button>
                                </form>

                                <a href="{{ route('dinkes.data-master.show',['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                   class="h-8 flex items-center justify-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">
                                   Detail
                                </a>

                                <a href="{{ route('dinkes.data-master.edit',['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                   class="h-8 flex items-center justify-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">
                                   Update
                                </a>

                                <form method="POST"
                                      action="{{ route('dinkes.data-master.destroy', ['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                      data-confirm="Hapus akun {{ $acc->name }}? Tindakan ini tidak dapat dibatalkan.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs text-[#E20D0D] hover:bg-[#FFF0F0] w-full">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="text-center text-[#7C7C7C] py-6">Data tidak ditemukan.</div>
                @endforelse

                @if ($accounts->hasPages())
                    <div class="pt-2">
                        {{ $accounts->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>

            {{-- ====== Tabel (desktop & tablet) ====== --}}
            <section id="dataMasterContent" class="hidden md:block">
                <section class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-[760px] w-full text-sm text-left">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">E-mail</th>
                                    <th class="pl-4 pr-3 py-3 font-semibold text-center w-[420px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse ($accounts as $index => $acc)
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        <td class="pl-6 pr-4 py-3">{{ $accounts->firstItem() + $index }}</td>
                                        <td class="px-4 py-3">{{ $acc->name }}</td>
                                        <td class="px-4 py-3">{{ $acc->email }}</td>
                                        <td class="pl-4 pr-3 py-3">
                                            <div class="flex justify-end flex-wrap gap-2">
                                                <form method="POST"
                                                      action="{{ route('dinkes.data-master.reset', ['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                                      data-confirm="Reset password untuk {{ $acc->name }}?">
                                                    @csrf
                                                    <button class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                        Reset Password
                                                    </button>
                                                </form>

                                                <a href="{{ route('dinkes.data-master.show',['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                                   class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                    Detail
                                                </a>

                                                <a href="{{ route('dinkes.data-master.edit',['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                                   class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                    Update
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('dinkes.data-master.destroy', ['user'=>$acc->id,'tab'=>$tab,'q'=>$q ?? '']) }}"
                                                      data-confirm="Hapus akun {{ $acc->name }}? Tindakan ini tidak dapat dibatalkan.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs text-[#E20D0D] hover:bg-[#FFF0F0]">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-[#7C7C7C]">Data tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($accounts->hasPages())
                        <div class="px-4 sm:px-6 py-3 sm:py-4">
                            {{ $accounts->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </section>
        </div>

        {{-- Footer --}}
        <footer class="text-center text-[11px] sm:text-xs text-[#7C7C7C] py-4 sm:py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </main>
</div>
</body>
</html>
