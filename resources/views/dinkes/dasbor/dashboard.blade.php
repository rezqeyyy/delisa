<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Dasbor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/dashboard-filters.js', 'resources/js/dinkes/kf-filters.js', 'resources/js/dinkes/donut-nifas-filters.js', 'resources/js/dinkes/pasien-preeklampsia-search.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex flex-col min-h-screen">
        <x-dinkes.sidebar />

        <!-- Konten utama -->
        <main class="lg:ml-[260px] ml-0 flex-1 p-4 sm:p-6 lg:p-8 space-y-6 lg:space-y-8 transition-[margin]">

            <!-- Header -->
            <div
                class="flex flex-wrap items-center gap-3 justify-between bg-white px-4 sm:px-5 py-3 sm:py-4 rounded-2xl shadow-md">
                <div class="flex items-center gap-3 min-w-0"> </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Settings: icon disembunyikan di mobile (ganti teks 'Set') -->
                    <a href="{{ route('dinkes.profile.edit') }}"
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}"
                            class="sm:block w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    <!-- Profile dropdown -->
                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            @if (Auth::user()?->photo)
                                <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                    class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                            @else
                                <span
                                    class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                        <path
                                            d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </span>
                            @endif

                            <div class="leading-tight pr-1 text-left hidden sm:block">
                                <p
                                    class="text-[13px] font-semibold text-[#1D1D1D] truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                <p class="text-[11px] text-[#7C7C7C] -mt-0.5 truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                            </div>
                            <!-- caret disembunyikan di mobile -->
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="sm:block w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                <p class="text-xs text-[#7C7C7C] truncate">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-[#F9F9F9] flex items-center gap-2">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRID KPI (LAYOUT 1,5 BARIS KIRI vs 3 KARTU KANAN) -->
            <section class="grid grid-cols-1 lg:grid-cols-2 lg:grid-rows-6 gap-4 sm:gap-6">

                {{-- 1. KIRI ATAS: Daerah Asal Pasien (span 3 dari 6 row) --}}
                <div class="lg:row-span-3">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Daerah Asal Pasien</h3>
                            </div>
                        </div>

                        <div class="flex flex-1 items-center justify-center text-center divide-x divide-[#E9E9E9]">
                            <div class="flex-1 px-4">
                                <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Depok</div>
                                <br>
                                <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">
                                    {{ $asalDepok ?? 0 }}
                                </div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Non Depok</div>
                                <br>
                                <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">
                                    {{ $asalNonDepok ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. KANAN ATAS: Resiko Preeklampsia (span 2 row) --}}
                <div class="lg:row-span-2">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Group 36721.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Resiko Preeklampsia</h3>
                            </div>
                        </div>

                        <div class="space-y-3 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Pasien Normal</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $resikoNormal ?? 0 }}</span>
                            </div>
                            <hr class="border-[#E9E9E9]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Pasien Beresiko Preeklampsia</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $resikoPreeklampsia ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. KANAN TENGAH: Pasien Hadir (span 2 row) --}}
                <div class="lg:row-span-2">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Pasien Hadir</h3>
                            </div>
                        </div>

                        <div class="space-y-3 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Pasien Hadir</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $pasienHadir ?? 0 }}</span>
                            </div>
                            <hr class="border-[#E9E9E9]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Pasien Tidak Hadir</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $pasienTidakHadir ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. KIRI BAWAH: Data Pasien Nifas (span 3 row) --}}
                <div class="lg:row-span-3">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Regular/Light/3 User.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien Nifas</h3>
                            </div>
                        </div>

                        <div class="space-y-3 flex-1 pt-8">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Total Pasien Nifas</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $totalNifas ?? 0 }}</span>
                            </div>
                            <hr class="border-[#E9E9E9]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7C7C7C]">Sudah KFI</span>
                                <span class="font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. KANAN BAWAH: Pemantauan (span 2 row) --}}
                <div class="lg:row-span-2">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Outline/Activity.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Pemantauan</h3>
                            </div>
                        </div>

                        <div class="flex-1 flex items-center text-center divide-x-2 divide-[#E9E9E9]">
                            <div class="flex-1 px-4">
                                <div class="text-xs text-[#7C7C7C]">Sehat</div>
                                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanSehat ?? 0 }}</div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="text-xs text-[#7C7C7C]">Total Dirujuk</div>
                                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanDirujuk ?? 0 }}</div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="text-xs text-[#7C7C7C]">Meninggal</div>
                                <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanMeninggal ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>




            {{-- Kunjungan Nifas per Bulan (KF) --}}
            <section class="bg-white rounded-2xl p-5 shadow-md relative">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="sm:block w-3.5 h-3.5"
                            alt="">
                    </span>
                    <h2 class="font-semibold">Kunjungan Nifas per Bulan</h2>

                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-[#F5F5F5] text-[#4B4B4B]">
                        Tahun: {{ $selectedYear ?? now()->year }}
                    </span>

                    <button id="btnKfFilter" type="button"
                        class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm ml-auto">
                        Filter
                    </button>
                </div>

                {{-- Panel filter --}}
                <div id="kfFilterPanel"
                    class="hidden absolute right-4 sm:right-5 top-16 z-20 w-[calc(100%-2rem)] sm:w-64 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl p-4">
                    <form method="GET" class="space-y-3">
                        <div>
                            <label for="year" class="block text-sm font-medium text-[#4B4B4B] mb-1">
                                Pilih Tahun
                            </label>
                            <select id="year" name="year"
                                class="w-full border border-[#CAC7C7] rounded-xl px-3 py-2 text-sm focus:outline-none">
                                @foreach ($availableYears ?? [now()->year] as $y)
                                    <option value="{{ $y }}" @selected(($selectedYear ?? now()->year) == $y)>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <a href="{{ url()->current() }}" class="text-sm text-[#B9257F] hover:underline">Reset</a>
                            <button type="submit" class="bg-[#B9257F] text-white text-sm px-4 py-2 rounded-xl">
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>

                @php
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    $data = $seriesBulanan ?? array_fill(0, 12, 0);
                    $max = max($data) ?: 1;
                    $sum = array_sum($data);

                    $H = 260;
                    $padT = 12;
                    $padB = 40;
                    $innerH = $H - $padT - $padB;

                    $W = 1200;
                    // Skala tetap konsisten dengan sumbu Y (0–80),
                    // jika data di atas 80, bar akan proporsional sampai atas.
                    $scale = max(80, $max);

                    $gridColor = '#1F2937';
                    $gridOpacity = 0.45;
                    $gridWidth = 1.5;
                    $gridDash = '4,4';

                    // Y-ticks yang diminta: 0, 20, 40, 60, 80
                    $yTicks = [0, 20, 40, 60, 80];
                @endphp

                <div class="-mx-4">
                    <div class="overflow-x-auto">
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none"
                            class="w-full h-56 block">
                            @php
                                $padL = 48;
                                $padR = 48;
                                $innerW = $W - $padL - $padR;
                                $n = 12;

                                $barPx = 50;
                                $minGap = 12;
                                $needMin = $n * $barPx + ($n - 1) * $minGap;

                                if ($needMin <= $innerW) {
                                    $extra = $innerW - $needMin;
                                    $gap = $minGap + $extra / ($n - 1);
                                    $barWidthF = array_fill(0, $n, $barPx);
                                } else {
                                    $barPxFit = max(2, ($innerW - ($n - 1) * $minGap) / $n);
                                    $gap = $minGap;
                                    $barWidthF = array_fill(0, $n, $barPxFit);
                                }

                                $barWInt = array_map(fn($w) => (int) floor($w), $barWidthF);
                                $sumBars = array_sum($barWInt);
                                $gapInt = (int) floor($gap);
                                $remainder = (int) round($innerW - ($sumBars + $gapInt * ($n - 1)));

                                for ($i = 0; $i < $remainder; $i++) {
                                    $barWInt[$i % $n] += 1;
                                }

                                $gapAdds = array_fill(0, $n - 1, $gapInt);
                                $remain2 = (int) round($innerW - (array_sum($barWInt) + $gapInt * ($n - 1)));

                                for ($i = 0; $i < $remain2; $i++) {
                                    $gapAdds[$i % ($n - 1)] += 1;
                                }

                                $barWidths = $barWInt;
                            @endphp

                            {{-- GRID + LABEL Y --}}
                            @foreach ($yTicks as $tick)
                                @php
                                    $ratio = $tick / $scale;
                                    $yLine = $padT + ($innerH - $ratio * $innerH);
                                @endphp

                                {{-- Garis grid --}}
                                <line x1="{{ $padL }}" y1="{{ $yLine }}" x2="{{ $W - $padR }}"
                                    y2="{{ $yLine }}" stroke="{{ $gridColor }}"
                                    stroke-opacity="{{ $gridOpacity }}" stroke-width="{{ $gridWidth }}"
                                    stroke-linecap="round" stroke-dasharray="{{ $gridDash }}" />

                                {{-- Label angka di sumbu Y --}}
                                <text x="{{ $padL - 10 }}" y="{{ $yLine + 4 }}" {{-- +4 untuk kira-kira tengah garis --}}
                                    text-anchor="end" font-size="10" fill="#6B7280">
                                    {{ $tick }}
                                </text>
                            @endforeach

                            {{-- Garis dasar (0) --}}
                            <line x1="{{ $padL }}" y1="{{ $padT + $innerH }}" x2="{{ $W - $padR }}"
                                y2="{{ $padT + $innerH }}" stroke="{{ $gridColor }}" stroke-opacity="0.55"
                                stroke-width="2" stroke-linecap="round" />

                            {{-- BATANG --}}
                            @php
                                $xAcc = $padL;
                                $n = 12;
                            @endphp

                            @if ($sum > 0)
                                @foreach ($data as $i => $val)
                                    @php
                                        $myW = $barWidths[$i];

                                        // Tinggi bar berdasarkan skala yang sama dengan sumbu Y
                                        $hVal = ($val / $scale) * $innerH;

                                        $yTop = $padT + ($innerH - $hVal);
                                        $yBot = $yTop + $hVal;
                                        $r = min($myW / 2, 16, $hVal);

                                        $xL = $xAcc;
                                        $xR = $xAcc + $myW;
                                    @endphp

                                    <g class="kf-chart-bar">
                                        <path d="M {{ $xL }},{{ $yBot }}
                                     L {{ $xL }},{{ $yTop + $r }}
                                     Q {{ $xL }},{{ $yTop }} {{ $xL + $r }},{{ $yTop }}
                                     L {{ $xR - $r }},{{ $yTop }}
                                     Q {{ $xR }},{{ $yTop }} {{ $xR }},{{ $yTop + $r }}
                                     L {{ $xR }},{{ $yBot }} Z" fill="#B9257F" />

                                        @if ($val > 0)
                                            {{-- Label nilai, hanya muncul saat hover --}}
                                            <text class="kf-chart-bar-label" x="{{ $xAcc + $myW / 2 }}"
                                                y="{{ max(14, $yTop - 8) }}" text-anchor="middle" font-size="10"
                                                fill="#B9257F">
                                                {{ $val }}
                                            </text>
                                        @endif
                                    </g>

                                    @php
                                        $xAcc += $myW + ($i < $n - 1 ? $gapAdds[$i] : 0);
                                    @endphp
                                @endforeach
                            @else
                                {{-- Placeholder jika belum ada data --}}
                                @for ($i = 0; $i < $n; $i++)
                                    @php
                                        $myW = $barWidths[$i];
                                        $hVal = 10;
                                        $yTop = $padT + ($innerH - $hVal);
                                        $yBot = $yTop + $hVal;
                                        $r = min($myW / 2, 16, $hVal);
                                        $xL = $xAcc;
                                        $xR = $xAcc + $myW;
                                    @endphp

                                    <path d="M {{ $xL }},{{ $yBot }}
                                 L {{ $xL }},{{ $yTop + $r }}
                                 Q {{ $xL }},{{ $yTop }} {{ $xL + $r }},{{ $yTop }}
                                 L {{ $xR - $r }},{{ $yTop }}
                                 Q {{ $xR }},{{ $yTop }} {{ $xR }},{{ $yTop + $r }}
                                 L {{ $xR }},{{ $yBot }} Z" fill="#B9257F" />

                                    @php
                                        $xAcc += $myW + ($i < $n - 1 ? $gapAdds[$i] : 0);
                                    @endphp
                                @endfor
                            @endif

                            {{-- LABEL BULAN --}}
                            @php $xAcc = $padL; @endphp
                            @foreach ($months as $i => $label)
                                @php
                                    $myW = $barWidths[$i];
                                    $cx = $xAcc + $myW / 2;
                                @endphp

                                <text x="{{ $cx }}" y="{{ $H - 10 }}" text-anchor="middle"
                                    font-size="11" fill="#6B7280">
                                    {{ $label }}
                                </text>

                                @php
                                    $xAcc += $myW + ($i < $n - 1 ? $gapAdds[$i] : 0);
                                @endphp
                            @endforeach
                        </svg>
                    </div>

                    @if ($sum === 0)
                        <p class="text-sm text-[#7C7C7C] mt-2 px-5">
                            Belum ada data kunjungan nifas pada tahun {{ $selectedYear ?? now()->year }}.
                        </p>
                    @endif
                </div>
            </section>


            <!-- Tabel: Data Pasien Pre-Eklampsia -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}"
                                class="sm:block w-3.5 h-3.5" alt="">
                        </span>
                        <h2 class="font-semibold">Data Pasien Pre-Eklampsia</h2>
                    </div>

                    <!-- Search + Filter -->
                    <div class="flex items-center gap-2">
                        <form id="peSearchForm" role="search" method="GET" action="{{ url()->current() }}"
                            class="relative w-36 xs:w-44 sm:w-48">
                            <input type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
                            <input type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
                            <input type="hidden" name="resiko" value="{{ $filters['resiko'] ?? '' }}">
                            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                            <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">
                            <input type="hidden" name="puskesmas_id" value="{{ $filters['puskesmas_id'] ?? '' }}">

                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#7C7C7C] hidden sm:inline">
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                    class="w-4 h-4 opacity-60" alt="search">
                            </span>

                            <input id="peSearchInput" type="search" name="q"
                                value="{{ $filters['q'] ?? '' }}" placeholder="Cari…" autocomplete="off"
                                class="w-full h-8 border border-[#CAC7C7] rounded-full pl-3 sm:pl-8 pr-7 text-sm focus:outline-none focus:ring-2 focus:ring-black/10"
                                data-autosubmit="true" />

                            <button type="button" id="peSearchClear"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 text-xs border border-[#CAC7C7] rounded-full"
                                aria-label="Bersihkan pencarian">×</button>
                        </form>

                        {{-- TOMBOL UNDUH (PAKAI FILTER YANG SAMA) --}}
                        <a href="{{ route('dinkes.dashboard.pe-export', request()->query()) }}"
                            class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm whitespace-nowrap">
                            Unduh Data
                        </a>

                        <button id="btnPeFilter" type="button"
                            class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">
                            Filter
                        </button>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead class="text-[#7C7C7C] border-b border-[#CAC7C7]">
                            <tr>
                                <th class="py-2 text-left">No</th>
                                <th class="text-left">Pasien</th>
                                <th class="text-left hidden md:table-cell">NIK</th>
                                <th class="text-left">Umur</th>
                                <th class="text-left hidden sm:table-cell">Usia Kehamilan</th>
                                <th class="text-left hidden lg:table-cell">Tanggal</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Resiko</th>
                                <th class="text-left">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#CAC7C7]">
                            @forelse ($peList as $i => $row)
                                <tr>
                                    {{-- nomor urut mengikuti halaman --}}
                                    <td class="py-3 tabular-nums">
                                        {{ str_pad(($peList->firstItem() ?? 0) + $i, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="max-w-[220px] truncate">{{ $row->nama ?? '-' }}</td>
                                    <td class="hidden md:table-cell">{{ $row->nik_masked ?? ($row->nik ?? '-') }}
                                    </td>
                                    <td class="tabular-nums">{{ $row->umur ?? '-' }}</td>
                                    <td class="hidden sm:table-cell">
                                        {{ $row->usia_kehamilan ? $row->usia_kehamilan . ' Minggu' : '-' }}
                                    </td>
                                    <td class="hidden lg:table-cell">{{ $row->tanggal ?? '-' }}</td>
                                    <td>
                                        @php $hadirClass = ($row->status_hadir ?? false) ? 'bg-[#39E93F33] text-[#39E93F]' : 'bg-[#E20D0D33] text-[#E20D0D]'; @endphp
                                        <span class="px-3 py-1 rounded-full {{ $hadirClass }}">
                                            {{ $row->status_hadir ?? false ? 'Hadir' : 'Mangkir' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $mapRisk = [
                                                'non-risk' => [
                                                    'bg' => '#39E93F33',
                                                    'tx' => '#39E93F',
                                                    'label' => 'Normal',
                                                ],
                                                'sedang' => [
                                                    'bg' => '#E2D30D33',
                                                    'tx' => '#E2D30D',
                                                    'label' => 'Sedang',
                                                ],
                                                'tinggi' => [
                                                    'bg' => '#E20D0D33',
                                                    'tx' => '#E20D0D',
                                                    'label' => 'Tinggi',
                                                ],
                                            ];
                                            $rk = $mapRisk[$row->resiko ?? 'non-risk'];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full"
                                            style="background: {{ $rk['bg'] }}; color: {{ $rk['tx'] }};">
                                            {{ $rk['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('dinkes.pasien.show', $row->pasien_id) }}"
                                            class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-[#7C7C7C]">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer: total & pagination --}}
                @if ($peList->count())
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                        <div class="text-[#7C7C7C]">
                            Menampilkan
                            <span class="font-medium text-[#000000cc]">{{ $peList->firstItem() }}</span> –
                            <span class="font-medium text-[#000000cc]">{{ $peList->lastItem() }}</span>
                            dari
                            <span class="font-medium text-[#000000cc]">{{ $peList->total() }}</span>
                            data
                        </div>

                        {{-- Laravel Tailwind pagination --}}
                        <div class="w-full sm:w-auto" id="pePagination">
                            {{ $peList->onEachSide(1)->links() }}
                        </div>
                    </div>
                @endif

                <!-- Backdrop -->
                <div id="peFilterBackdrop" class="hidden fixed inset-0 bg-black/30 z-40"></div>

                <!-- Modal Filter -->
                <div id="peFilterModal" class="hidden fixed inset-0 z-50 p-4">
                    <div class="grid place-items-center min-h-screen">
                        <div
                            class="bg-white w-full max-w-md max-h-[90vh] rounded-2xl shadow-xl border border-[#E9E9E9] flex flex-col">

                            {{-- Header --}}
                            <div class="px-5 py-4 border-b flex items-center justify-between shrink-0">
                                <h3 class="font-semibold">Filter Data Pasien Pre-Eklampsia</h3>
                                <button type="button" data-close class="p-1 rounded hover:bg-[#F5F5F5]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 6l12 12M18 6l-12 12" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Isi + scroll --}}
                            <form id="pe-filter-form" method="GET" action="{{ request()->url() }}"
                                class="p-5 grid gap-4 flex-1 overflow-y-auto">

                                {{-- Rentang tanggal --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Dari Tanggal</label>
                                    <input type="date" name="from" value="{{ request('from') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Sampai Tanggal</label>
                                    <input type="date" name="to" value="{{ request('to') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>

                                {{-- Resiko --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Resiko</label>
                                    <select name="resiko" class="w-full border rounded-md px-3 py-2 text-sm">
                                        <option value="">Semua Resiko</option>
                                        <option value="non-risk" @selected(request('resiko') === 'non-risk')>Normal</option>
                                        <option value="sedang" @selected(request('resiko') === 'sedang')>Sedang</option>
                                        <option value="tinggi" @selected(request('resiko') === 'tinggi')>Tinggi</option>
                                    </select>
                                </div>

                                {{-- Status hadir --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Status</label>
                                    <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                                        <option value="">Semua Status</option>
                                        <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                                        <option value="mangkir" @selected(request('status') === 'mangkir')>Mangkir</option>
                                    </select>
                                </div>

                                {{-- Kategori umum --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Kategori</label>
                                    <select name="kategori" class="w-full border rounded-md px-3 py-2 text-sm">
                                        <option value="">Semua Kategori</option>

                                        {{-- masih pakai kategori remaja --}}
                                        <option value="remaja" @selected(request('kategori') === 'remaja')>
                                            Ibu Remaja (&lt; 20 th)
                                        </option>

                                        {{-- Pasien JKN --}}
                                        <option value="jkn" @selected(request('kategori') === 'jkn')>
                                            Pasien JKN
                                        </option>

                                        {{-- Pasien Asuransi (non-JKN) --}}
                                        <option value="asuransi" @selected(request('kategori') === 'asuransi')>
                                            Pasien Asuransi
                                        </option>

                                        {{-- Domisili --}}
                                        <option value="depok" @selected(request('kategori') === 'depok')>
                                            Pasien Domisili Depok
                                        </option>
                                        <option value="non_depok" @selected(request('kategori') === 'non_depok')>
                                            Pasien Domisili Non-Depok
                                        </option>

                                        {{-- Berat badan / IMT --}}
                                        <option value="bb_normal" @selected(request('kategori') === 'bb_normal')>
                                            Berat Badan Normal (IMT normal)
                                        </option>
                                        <option value="bb_kurang" @selected(request('kategori') === 'bb_kurang')>
                                            Berat Badan di Bawah Normal
                                        </option>
                                    </select>
                                </div>

                                {{-- Puskesmas --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Puskesmas</label>
                                    <select name="puskesmas_id" class="w-full border rounded-md px-3 py-2 text-sm">
                                        <option value="">Semua Puskesmas</option>
                                        @foreach ($puskesmasList ?? [] as $pk)
                                            <option value="{{ $pk->id }}" @selected(request('puskesmas_id') == $pk->id)>
                                                {{ $pk->nama_puskesmas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- UI SAJA: Riwayat Penyakit (multi select chip) --}}
                                <div class="pt-1 border-t border-dashed border-[#E9E9E9] mt-1">
                                    <p class="block text-xs text-[#7C7C7C] mb-2">
                                        Riwayat Penyakit <span class="text-[10px]">(bisa pilih lebih dari satu)</span>
                                    </p>

                                    @php
                                        $rpOptions = [
                                            'hipertensi' => 'Hipertensi',
                                            'alergi' => 'Alergi',
                                            'tiroid' => 'Tiroid',
                                            'tb' => 'TB',
                                            'jantung' => 'Jantung',
                                            'hepatitis_b' => 'Hepatitis B',
                                            'jiwa' => 'Jiwa',
                                            'autoimun' => 'Autoimun',
                                            'sifilis' => 'Sifilis',
                                            'diabetes' => 'Diabetes',
                                            'asma' => 'Asma',
                                            'lainnya' => 'Lainnya',
                                        ];
                                        $rpSelected = (array) request('riwayat_penyakit_ui', []);
                                    @endphp

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                                        @foreach ($rpOptions as $val => $label)
                                            <label class="cursor-pointer">

                                                {{-- input checklistnya disembunyikan --}}
                                                <input type="checkbox" name="riwayat_penyakit_ui[]"
                                                    value="{{ $val }}" class="peer hidden"
                                                    @checked(in_array($val, $rpSelected))>

                                                {{-- tampilan chip --}}
                                                <div
                                                    class="
                    flex items-center justify-between
                    border border-pink-400 rounded-full px-4 py-2
                    text-xs sm:text-sm transition-all
                    peer-checked:bg-[#B9257F] peer-checked:text-white peer-checked:border-[#B9257F]
                    hover:bg-[#ffe6f2]
                ">
                                                    <span>{{ $label }}</span>

                                                </div>

                                            </label>
                                        @endforeach

                                    </div>
                                </div>


                                <input type="hidden" name="q" value="{{ request('q') }}">

                                {{-- Footer tombol (tetap nempel di bawah area scroll) --}}
                                <div class="flex items-center justify-end gap-2 pt-2 border-t bg-white">
                                    <button type="button" data-reset
                                        class="border border-[#CAC7C7] rounded-md px-3 py-2 text-sm">
                                        Reset
                                    </button>
                                    <button class="bg-[#B9257F] text-white rounded-md px-4 py-2 text-sm">
                                        Terapkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>

            <span id="page-bottom"></span>

        </main>
    </div>
</body>

</html>
