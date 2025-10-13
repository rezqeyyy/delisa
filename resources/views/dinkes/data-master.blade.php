<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Data Master</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes-data-master.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            class="fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between rounded-r-2xl">
            <div>
                <div class="flex items-center gap-3 p-6 border-b border-[#CAC7C7]">
                    <img src="{{ asset('images/logo_fulltext 2.png') }}" alt="DeLISA"
                        class="w-28 h-auto object-contain">
                </div>

                <nav class="mt-6 space-y-1">
                    <a href="{{ route('dinkes.dashboard') }}"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Group 36729.svg') }}" class="w-4 h-4" alt="Dashboard">
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('dinkes.data-master') }}"
                        class="flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white rounded-r-full font-medium">
                        <img src="{{ asset('icons/Group 36805.svg') }}" class="w-4 h-4" alt="Data Master">
                        <span>Data Master</span>
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="w-4 h-4"
                            alt="Akun Baru">
                        <span>Akun Baru</span>
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}" class="w-4 h-4"
                            alt="Pasien Nifas">
                        <span>Pasien Nifas</span>
                    </a>
                </nav>
            </div>

            <div class="p-6 border-t border-[#CAC7C7] text-sm text-[#7C7C7C]">
                <p class="uppercase text-xs font-semibold mb-1">Account</p>
            </div>
        </aside>

        {{-- Konten utama --}}
        <main class="ml-[260px] flex-1 p-8 space-y-8">
            {{-- Header --}}
            <header>
                <h1 class="text-[28px] font-bold leading-tight text-[#000000]">List Daftar Akun</h1>
                <p class="text-sm text-[#7C7C7C]">Manage the Details of Your Menu Account</p>
            </header>

            {{-- 🔔 FLASH / ERROR ALERTS --}}
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
            <section class="flex items-center gap-3">
                <a href="{{ route('dinkes.data-master', ['tab' => 'bidan', 'q' => $q ?? '']) }}"
                    class="dm-tab px-4 py-2 rounded-full text-sm font-medium {{ $tab === 'bidan' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">Bidan
                    PKM</a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'rs', 'q' => $q ?? '']) }}"
                    class="dm-tab px-4 py-2 rounded-full text-sm font-medium {{ $tab === 'rs' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">Rumah
                    Sakit</a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'puskesmas', 'q' => $q ?? '']) }}"
                    class="dm-tab px-4 py-2 rounded-full text-sm font-medium {{ $tab === 'puskesmas' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">Puskesmas</a>
            </section>

            {{-- ALERT FLASH MESSAGE --}}
            @if (session('ok'))
                <div class="rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('ok') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- Search + Tambah --}}
            <section class="flex items-center gap-3">
                <form action="{{ route('dinkes.data-master') }}" method="GET" class="flex items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative w-full max-w-[360px]">
                        <input name="q" value="{{ $q ?? '' }}" type="text" placeholder="Search data..."
                            class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                            class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                    </div>
                    <button class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium">Search</button>
                </form>

                <div class="ml-auto">
                    <a href="{{ route('dinkes.data-master.create', ['tab' => $tab]) }}" id="btnTambahAkun"
                        class="flex items-center gap-2 bg-[#B9257F] text-white px-5 py-2 rounded-full text-sm font-medium shadow-md hover:bg-[#a31f70] transition">
                        <span class="text-lg font-bold">+</span> Tambah Akun
                    </a>
                </div>
            </section>

            {{-- Tabel Data --}}
            <section id="dataMasterContent">
                <section class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <table class="w-full text-sm text-left">
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
                                        <div class="flex justify-end gap-2">

                                            {{-- Reset Password --}}
                                            <form method="POST"
                                                action="{{ route('dinkes.data-master.reset', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                onsubmit="return confirm('Reset password untuk {{ $acc->name }}?');">
                                                @csrf
                                                <button
                                                    class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                    Reset Password
                                                </button>
                                            </form>

                                            {{-- Detail --}}
                                            <a href="{{ route('dinkes.data-master.show', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                Detail
                                            </a>

                                            {{-- Update --}}
                                            <a href="{{ route('dinkes.data-master.edit', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                Update
                                            </a>

                                            {{-- Delete --}}
                                            <form method="POST"
                                                action="{{ route('dinkes.data-master.destroy', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                onsubmit="return confirm('Hapus akun {{ $acc->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs text-[#E20D0D] hover:bg-[#FFF0F0]">
                                                    Delete
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[#7C7C7C]">Data tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($accounts->hasPages())
                        <div class="px-6 py-4">
                            {{ $accounts->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
