<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Pasien NIFAS</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/bidan/sidebar-toggle.js',
        'resources/js/bidan/delete-confirm.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">

    <x-bidan.sidebar />

    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

        <div class="flex items-center gap-3 flex-nowrap">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-3 flex items-center">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                    </span>
                    <input type="text" placeholder="Cari nama pasien..." 
                    id="dashboardSearch"
                    class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                </div>
            </div>

            <div class="flex items-center gap-3 flex-none justify-end">
                <a href="{{ route('bidan.profile.edit') }}" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                </a>

                <div id="profileWrapper" class="relative">
                    <button id="profileBtn" class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                        
                        @if (Auth::user()?->photo)
                            <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                        @else
                            <span class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4 text-pink-500" fill="currentColor">
                                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                </svg>
                            </span>
                        @endif

                        <div class="leading-tight pr-1 text-left">
                            <p class="text-[13px] font-semibold text-[#1D1D1D]">
                                {{ auth()->user()->name ?? 'Bidan' }}
                            </p>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}" class="w-4 h-4 opacity-70" alt="More" />
                    </button>

                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                        <div class="px-4 py-3 border-b border-[#F0F0F0]">
                            <p class="text-sm font-medium text-[#1D1D1D]">
                                {{ auth()->user()->name ?? 'Bidan' }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm hover:bg-[#F9F9F9] flex items-center gap-2">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <section class="space-y-4">
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor">
                                <path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                            <p class="text-xs text-[#7C7C7C]">Data pasien nifas pada puskesmas ini</p>
                        </div>
                    </div>
                </div>

                <br>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">

                        <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                        <tr class="text-left">
                            <th class="px-3 py-2">No</th>
                            <th class="px-3 py-2">Nama Pasien</th>
                            <th class="px-3 py-2">NIK</th>
                            <th class="px-3 py-2">No Telp</th>
                            <th class="px-3 py-2">Tanggal Mulai NIFAS</th>
                            <th class="px-3 py-2">Alamat</th>
                            <th class="px-3 py-2">Asal Data</th>
                            <th class="px-3 py-2">Status KF</th>
                            <th class="px-3 py-2">Action</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-[#E9E9E9]">
                        @forelse($pasienNifas as $pasien)
                            @php
                                $no = method_exists($pasienNifas, 'firstItem') && $pasienNifas->firstItem()
                                    ? $pasienNifas->firstItem() + $loop->index
                                    : $loop->iteration;

                                $asalLabel = $pasien->asal_data_label ?? 'RS';

                                $statusLabel = $pasien->peringat_label ?? 'Perlu KF';
                                $statusClass = $pasien->badge_class ?? null;

                                if (!$statusClass) {
                                    $state = $pasien->peringat_state ?? 'early';
                                    if ($state === 'late') $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                                    elseif ($state === 'window') $statusClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                                    elseif ($state === 'done') $statusClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                    elseif ($state === 'no_date') $statusClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                                    else $statusClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                                }

                                $maxKe = (int) ($pasien->max_ke ?? 0);
                                $nextKe = (int) ($pasien->next_ke ?? 1);

                                $tglMulai = isset($pasien->tanggal)
                                    ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y')
                                    : '-';
                            @endphp

                            <tr>
                                <td class="px-3 py-3 font-medium tabular-nums">{{ $no }}</td>
                                <td class="px-3 py-3">{{ $pasien->nama_pasien ?? '-' }}</td>
                                <td class="px-3 py-3 tabular-nums">{{ $pasien->nik ?? '-' }}</td>
                                <td class="px-3 py-3">{{ $pasien->telp ?? '-' }}</td>
                                <td class="px-3 py-3">{{ $tglMulai }}</td>
                                <td class="px-3 py-3">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-[#EAF1FF] px-3 py-1 text-xs font-semibold text-[#1A4FD8]">
                                        <span class="w-2 h-2 rounded-full bg-[#1A4FD8]"></span>
                                        {{ $asalLabel }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    {{-- Baris 1: View & Hapus --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        @if (Route::has('bidan.pasien-nifas.detail'))
                                            <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                               class="px-3 py-1.5 rounded-full border border-[#E5E5E5] text-xs hover:bg-gray-50">
                                                View
                                            </a>
                                        @else
                                            <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" disabled>
                                                View
                                            </button>
                                        @endif

                                        @if (Route::has('bidan.pasien-nifas.destroy'))
                                            <form action="{{ route('bidan.pasien-nifas.destroy', $pasien->id) }}"
                                                  method="POST"
                                                  class="inline js-delete-skrining-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="js-delete-skrining-btn px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" disabled>
                                                Hapus
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Baris 2: KF1-KF4 --}}
                                    <div class="flex items-center gap-2">
                                        @foreach([1,2,3,4] as $jk)
                                            @php
                                                $chipClass = 'bg-white text-gray-500 border-[#E5E5E5]';
                                                if ($jk <= $maxKe) $chipClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                                                elseif ($jk === $nextKe) $chipClass = 'bg-amber-50 border-amber-200 text-amber-800';
                                            @endphp

                                            @if (Route::has('bidan.pasien-nifas.detail'))
                                                <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                                   class="px-3 py-1.5 rounded-full border text-xs font-semibold {{ $chipClass }}"
                                                   title="Isi KF{{ $jk }} (melalui detail)">
                                                    KF{{ $jk }}
                                                </a>
                                            @else
                                                <span class="px-3 py-1.5 rounded-full border text-xs font-semibold {{ $chipClass }}">
                                                    KF{{ $jk }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-[#7C7C7C]">
                                    <p class="text-sm">Belum ada data pasien nifas</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pasienNifas->hasPages())
                    <div class="mt-6 flex items-center justify-end">
                        <div class="inline-flex items-center gap-2 text-sm">
                            @if($pasienNifas->onFirstPage())
                                <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] text-gray-400 bg-[#FAFAFA]">Previous</span>
                            @else
                                <a href="{{ $pasienNifas->previousPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">Previous</a>
                            @endif

                            @php($last = $pasienNifas->lastPage())
                            @php($current = $pasienNifas->currentPage())

                            @for($i = max(1, $current - 1); $i <= min($last, $current + 1); $i++)
                                @if($i === $current)
                                    <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] bg-[#FAFAFA] font-semibold">{{ $i }}</span>
                                @else
                                    <a href="{{ $pasienNifas->url($i) }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($pasienNifas->hasMorePages())
                                <a href="{{ $pasienNifas->nextPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">Next</a>
                            @else
                                <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] text-gray-400 bg-[#FAFAFA]">Next</span>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </section>

        <footer class="text-center text-xs text-[#7C7C7C] py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </main>
</div>
</body>
</html>