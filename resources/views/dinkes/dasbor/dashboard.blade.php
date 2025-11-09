<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Dasbor</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/dinkes/dashboard-filters.js',
        'resources/js/dinkes/kf-filters.js',
        'resources/js/dinkes/donut-nifas-filters.js',
        'resources/js/dinkes/pasien-preeklampsia-search.js',
        'resources/js/dinkes/sidebar-toggle.js',
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
<div class="flex flex-col min-h-screen">
    <x-dinkes.sidebar />

    <!-- Konten utama -->
    <main class="lg:ml-[260px] ml-0 flex-1 p-4 sm:p-6 lg:p-8 space-y-6 lg:space-y-8 transition-[margin]">

        <!-- Header -->
        <div class="flex flex-wrap items-center gap-3 justify-between bg-white px-4 sm:px-5 py-3 sm:py-4 rounded-2xl shadow-md">
            <div class="flex items-center gap-3 min-w-0">
               
                <!-- Analytic Explorer: icon disembunyikan di mobile -->
                <a href="{{ route('dinkes.analytics') }}"
                   class="shrink-0 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#E5E5E5] bg-white hover:bg-[#F8F8F8] transition"
                   aria-label="Buka Analytic Explorer">
                    <span class=" sm:inline-flex w-5 h-5 items-center justify-center">
                        <img src="{{ asset('icons/analytics-analysis-svgrepo-com.svg') }}" class="w-4 h-4 opacity-90" alt="">
                    </span>
                    <span class="text-xs sm:text-sm font-medium">Analytic Explorer</span>
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Settings: icon disembunyikan di mobile (ganti teks 'Set') -->
                <a href="{{ route('dinkes.profile.edit') }}"
                   class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="sm:block w-4 h-4 opacity-90" alt="Setting">
                </a>

                <!-- Profile dropdown -->
                <div id="profileWrapper" class="relative">
                    <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                        @if (Auth::user()?->photo)
                            <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                 class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                        @else
                            <span class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                     class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                </svg>
                            </span>
                        @endif

                        <div class="leading-tight pr-1 text-left hidden xs:block">
                            <p class="text-[13px] font-semibold text-[#1D1D1D] truncate max-w-[140px] sm:max-w-[200px]">
                                {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                            <p class="text-[11px] text-[#7C7C7C] -mt-0.5 truncate max-w-[140px] sm:max-w-[200px]">
                                {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                        </div>
                        <!-- caret disembunyikan di mobile -->
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}" class="hidden sm:block w-4 h-4 opacity-70" alt="More" />
                    </button>

                    <div id="profileMenu"
                         class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                        <div class="px-4 py-3 border-b border-[#F0F0F0]">
                            <p class="text-sm font-medium text-[#1D1D1D]">{{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                            <p class="text-xs text-[#7C7C7C] truncate">{{ auth()->user()->email ?? 'email Dinkes' }}</p>
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

        <!-- GRID KPI -->
        <section class="grid grid-cols-12 gap-4 sm:gap-6 lg:auto-rows-[280px]">
            <!-- KIRI ATAS: Daerah Asal Pasien -->
            <div class="col-span-12 lg:col-span-7 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr] gap-4 overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="">
                        </span>
                        <h2 class="font-semibold text-lg">Daerah Asal Pasien</h2>
                    </div>
                </div>

                @php
                    $depok = $depok ?? 0; $non = $non ?? 0;
                    $total = max($depok + $non, 1);
                    $pDepok = round(($depok / $total) * 100); $pNon = 100 - $pDepok;
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] items-center justify-items-center px-2 md:px-6 gap-6 sm:gap-0">
                    <!-- Depok -->
                    <div class="w-full flex flex-col items-center gap-3">
                        <div class="text-sm font-medium">Depok</div>
                        <div class="relative aspect-square w-[min(160px,45vw)] sm:w-[min(130px,40vw)] max-w-[200px]">
                            <div class="absolute inset-0 rounded-full border-[12px] border-[#F1F1F1]"></div>
                            <div class="absolute inset-0 rounded-full" style="background: conic-gradient(#B9257F {{ $pDepok }}%, #F1F1F1 0 100%);"></div>
                            <div class="absolute inset-[10px] bg-white rounded-full"></div>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <div class="text-3xl font-extrabold leading-none tabular-nums">{{ $pDepok }}%</div>
                                    <div class="text-xs text-[#7C7C7C] mt-0.5">Proporsi</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm flex items-center gap-2">
                            <span class="text-[#7C7C7C]">Jumlah:</span>
                            <span class="font-semibold tabular-nums">{{ $depok }}</span>
                        </div>
                    </div>

                    <div class="hidden sm:block w-px self-stretch bg-[#E5E5E5] mx-2"></div>

                    <!-- Non Depok -->
                    <div class="w-full flex flex-col items-center gap-3">
                        <div class="text-sm font-medium">Non Depok</div>
                        <div class="relative aspect-square w-[min(160px,45vw)] sm:w-[min(130px,40vw)] max-w-[200px]">
                            <div class="absolute inset-0 rounded-full border-[12px] border-[#F1F1F1]"></div>
                            <div class="absolute inset-0 rounded-full" style="background: conic-gradient(#E9A9CD {{ $pNon }}%, #F1F1F1 0 100%);"></div>
                            <div class="absolute inset-[10px] bg-white rounded-full"></div>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <div class="text-3xl font-extrabold leading-none tabular-nums">{{ $pNon }}%</div>
                                    <div class="text-xs text-[#7C7C7C] mt-0.5">Proporsi</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm flex items-center gap-2">
                            <span class="text-[#7C7C7C]">Jumlah:</span>
                            <span class="font-semibold tabular-nums">{{ $non }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN ATAS: Risiko Pre-Eklampsia -->
            <div class="col-span-12 lg:col-span-5 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr]">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Group 36721.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="">
                        </span>
                        <h2 class="font-semibold text-lg">Resiko Pre-Eklampsia</h2>
                    </div>
                </div>

                @php
                    $normal = $normal ?? 0; $risk = $risk ?? 0;
                    $sum = max(($normal ?? 0) + ($risk ?? 0), 1);
                    $pRisk = round(($risk / $sum) * 100);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center text-center">
                    <div class="relative w-28 h-28 sm:w-36 sm:h-36 place-self-center">
                        <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#B9257F" stroke-width="4" stroke-dasharray="{{ $pRisk }},100" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 grid place-content-center text-center">
                            <span class="text-xl sm:text-2xl font-bold tabular-nums">{{ $pRisk }}%</span>
                            <span class="text-[11px] sm:text-xs text-[#7C7C7C] -mt-1">Beresiko</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#F0F0F0]"></span>Normal</div>
                            <span class="font-semibold tabular-nums">{{ $normal }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#B9257F]"></span>Beresiko</div>
                            <span class="font-semibold tabular-nums">{{ $risk }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-[#7C7C7C] border-t pt-2">
                            <span>Total</span><span class="tabular-nums">{{ $normal + $risk }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KIRI BAWAH: Data Pasien Nifas -->
            <div class="col-span-12 lg:col-span-7 lg:row-span-2 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr_auto] gap-4 relative">
                <!-- Header -->
                <div class="flex flex-wrap items-center gap-3 justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/3 User.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="">
                        </span>
                        <h2 class="font-semibold text-lg">Data Pasien Nifas</h2>

                        @if ($isDonutFiltered ?? false)
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-[#F5F5F5] text-[#4B4B4B]">
                                Periode:
                                @if ($dkfMonth) {{ str_pad($dkfMonth, 2, '0', STR_PAD_LEFT) }}/ @endif
                                {{ $dkfYear ?? '—' }}
                            </span>
                        @else
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-[#F5F5F5] text-[#4B4B4B]">
                                Semua data (tanpa filter)
                            </span>
                        @endif
                    </div>

                    <button id="btnDataKfFilter" type="button" class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">
                        Filter
                    </button>
                </div>

                <!-- Panel filter -->
                <div id="dataKfFilterPanel"
                     class="hidden absolute right-4 sm:right-5 top-16 z-20 w-[calc(100%-2rem)] sm:w-72 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl p-4">
                    <form method="GET" class="grid grid-cols-1 gap-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="dkf_month" class="block text-sm font-medium text-[#4B4B4B] mb-1">Bulan</label>
                                <select id="dkf_month" name="dkf_month"
                                        class="w-full border border-[#CAC7C7] rounded-xl px-3 py-2 text-sm focus:outline-none">
                                    <option value="">Semua</option>
                                    @php $bulanList = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Agu',8=>'Sep',9=>'Okt',10=>'Nov',11=>'Des']; @endphp
                                    @foreach ($bulanList as $m => $label)
                                        <option value="{{ $m }}" @selected(($dkfMonth ?? null) === $m)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="dkf_year" class="block text-sm font-medium text-[#4B4B4B] mb-1">Tahun</label>
                                <select id="dkf_year" name="dkf_year"
                                        class="w-full border border-[#CAC7C7] rounded-xl px-3 py-2 text-sm focus:outline-none">
                                    <option value="">Semua</option>
                                    @foreach ($availableYears ?? [now()->year] as $y)
                                        <option value="{{ $y }}" @selected(($dkfYear ?? null) === $y)>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <a href="{{ request()->fullUrlWithQuery(['dkf_month' => null, 'dkf_year' => null]) }}"
                               class="text-sm text-[#B9257F] hover:underline">Reset</a>
                            <button type="submit" class="bg-[#B9257F] text-white text-sm px-4 py-2 rounded-xl">Terapkan</button>
                        </div>
                    </form>
                </div>

                @php
                    $totalNifas = $totalNifas ?? 0;
                    $kf1 = $kf1 ?? 0; $kf2 = $kf2 ?? 0; $kf3 = $kf3 ?? 0; $kf4 = $kf4 ?? 0;
                    $sumTargetBase = max(1, $totalNifas * 4);
                    $coverage = max(0, min(100, round((($kf1 + $kf2 + $kf3 + $kf4) / $sumTargetBase) * 100)));
                    $sum = max($totalNifas, 1);
                    $p1 = round(($kf1 / $sum) * 100);
                    $p2 = round(($kf2 / $sum) * 100);
                    $p3 = round(($kf3 / $sum) * 100);
                    $p4 = round(($kf4 / $sum) * 100);
                @endphp

                <!-- Donut & detail -->
                <div class="grid grid-cols-12 gap-6">
                    <!-- Donut besar (NON-absolute, aman di mobile) -->
                    <div class="col-span-12 lg:col-span-7 grid grid-rows-[auto_1fr]">
                        <div class="flex items-baseline gap-3 mb-3">
                            <span class="text-3xl sm:text-4xl font-bold tabular-nums">{{ $totalNifas }}</span>
                            <span class="text-sm text-[#7C7C7C]">total pasien nifas</span>
                        </div>

                        <div class="w-full grid place-items-center">
                            <div class="relative aspect-square w-[min(220px,80vw)] sm:w-[min(360px,100%)]">
                                <div class="relative w-full h-full rounded-full"
                                     style="background: conic-gradient(#B9257F {{ $coverage }}%, #F1F1F1 {{ $coverage }}% 100%);">
                                    <div class="absolute inset-0 m-[11%] bg-white rounded-full"></div>
                                    <div class="absolute inset-0 grid place-items-center text-center">
                                        <div>
                                            <div class="text-2xl sm:text-3xl font-bold tabular-nums">{{ $coverage }}%</div>
                                            <div class="text-xs text-[#7C7C7C]">Cakupan Kunjungan (KF1–KF4)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Donut kecil KF1–KF4 -->
                    <div class="col-span-12 lg:col-span-5 grid grid-cols-2 gap-4 content-start">
                        @php
                            $kfs = [
                                ['label' => 'KF1', 'val' => $kf1, 'pct' => $p1, 'col' => '#B9257F'],
                                ['label' => 'KF2', 'val' => $kf2, 'pct' => $p2, 'col' => '#D24A97'],
                                ['label' => 'KF3', 'val' => $kf3, 'pct' => $p3, 'col' => '#E178B3'],
                                ['label' => 'KF4', 'val' => $kf4, 'pct' => $p4, 'col' => '#F0A6CF'],
                            ];
                        @endphp
                        @foreach ($kfs as $k)
                            <div class="bg-[#FAFAFA] rounded-xl p-3 grid grid-rows-[auto_1fr_auto]">
                                <div class="text-[11px] sm:text-xs text-[#7C7C7C]">{{ $k['label'] }}</div>
                                <div class="relative w-full h-full grid place-items-center py-2">
                                    <div class="relative aspect-square w-full max-w-[min(160px,100%)] sm:max-w-[min(180px,100%)]">
                                        <div class="relative w-full h-full rounded-full"
                                             style="background: conic-gradient({{ $k['col'] }} {{ $k['pct'] }}%, #F1F1F1 {{ $k['pct'] }}% 100%);">
                                            <div class="absolute inset-0 m-[18%] sm:m-[14%] bg-white rounded-full"></div>
                                            <div class="absolute inset-0 grid place-items-center">
                                                <div class="text-base sm:text-lg font-semibold tabular-nums">{{ $k['pct'] }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span>Jumlah</span>
                                    <span class="font-semibold tabular-nums">{{ $k['val'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer legend -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-sm flex-wrap">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background:#B9257F"></span>KF1</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background:#D24A97"></span>KF2</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background:#E178B3"></span>KF3</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background:#F0A6CF"></span>KF4</span>
                    </div>
                    <div class="text-xs text-[#7C7C7C]">Target 4× kunjungan per pasien</div>
                </div>
            </div>

            <!-- KANAN TENGAH: Pasien Hadir -->
            <div class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-2 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr_auto]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="Hadir">
                        </span>
                        <h2 class="font-semibold text-lg">Pasien Hadir</h2>
                    </div>
                </div>

                @php
                    $hadir = $hadir ?? 0; $mangkir = $mangkir ?? 0;
                    $totalA = max($hadir + $mangkir, 1);
                    $pHadir = round(($hadir / $totalA) * 100);
                    $pMangkir = 100 - $pHadir;
                    $seriesAbsensi = $seriesAbsensi ?? array_fill(0, 12, 0);
                    if (count($seriesAbsensi) < 12) $seriesAbsensi = array_pad($seriesAbsensi, 12, 0);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                    <div class="relative w-full h-full grid place-content-center">
                        <svg viewBox="0 0 36 36" class="w-28 h-28 sm:w-40 sm:h-40 -rotate-90 mx-auto">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#B9257F" stroke-width="4" stroke-dasharray="{{ $pHadir }},100" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 grid place-content-center text-center">
                            <span class="text-xl sm:text-3xl font-bold tabular-nums">{{ $pHadir }}%</span>
                            <span class="text-xs text-[#7C7C7C] -mt-1">Hadir</span>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center gap-4">
                        <div class="h-4 w-full rounded-full bg-[#F1F1F1] overflow-hidden">
                            <div class="h-full bg-[#39E93F] inline-block" style="width: {{ $pHadir }}%"></div>
                            <div class="h-full bg-[#E20D0D] inline-block" style="width: {{ $pMangkir }}%"></div>
                        </div>

                        <div class="text-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#39E93F]"></span>Pasien Hadir</div>
                                <div class="font-semibold tabular-nums">{{ $hadir }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-[#E20D0D]"></span>Pasien Tidak Hadir</div>
                                <div class="font-semibold tabular-nums">{{ $mangkir }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-1 items-end h-14">
                            @foreach ($seriesAbsensi as $v)
                                @php $h = 10 + min(100, (int) $v); @endphp
                                <div class="rounded-[6px] bg-[#B9257F]/60" style="height: {{ $h }}%"></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="text-xs text-[#7C7C7C] flex justify-between">
                    <span>Total</span>
                    <span class="tabular-nums">{{ $hadir + $mangkir }}</span>
                </div>
            </div>

            <!-- KANAN BAWAH: Pemantauan -->
            <div class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-3 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr_auto]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Sharp/Outline/Activity.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="Pemantauan">
                        </span>
                        <h2 class="font-semibold text-lg">Pemantauan</h2>
                    </div>
                </div>

                @php
                    $sehat = $sehat ?? 0; $dirujuk = $dirujuk ?? 0; $meninggal = $meninggal ?? 0;
                    $sumP = max($sehat + $dirujuk + $meninggal, 1);
                    $pSehat = round(($sehat / $sumP) * 100);
                    $pDirujuk = round(($dirujuk / $sumP) * 100);
                    $pMeninggal = round(($meninggal / $sumP) * 100);
                @endphp

                <div class="grid grid-cols-3 gap-3 sm:gap-4 items-center justify-items-center">
                    @foreach ([['label' => 'Sehat', 'val' => $sehat, 'p' => $pSehat, 'color' => '#39E93F'], ['label' => 'Total Dirujuk', 'val' => $dirujuk, 'p' => $pDirujuk, 'color' => '#F5A524'], ['label' => 'Meninggal', 'val' => $meninggal, 'p' => $pMeninggal, 'color' => '#E20D0D']] as $i)
                        <div class="flex flex-col items-center gap-2">
                            <div class="relative w-24 h-24 sm:w-32 sm:h-32">
                                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                    <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0" stroke-width="4"></circle>
                                    <circle cx="18" cy="18" r="16" fill="none" stroke="{{ $i['color'] }}" stroke-width="4" stroke-dasharray="{{ $i['p'] }},100" stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 grid place-content-center leading-tight text-center">
                                    <span class="text-lg sm:text-xl font-bold tabular-nums">{{ $i['val'] }}</span>
                                    <span class="text-[12px] sm:text-[13px] text-[#7C7C7C]">{{ $i['label'] }}</span>
                                </div>
                            </div>
                            <span class="block w-20 sm:w-24 text-center text-xs text-[#7C7C7C] tabular-nums">{{ $i['p'] }}%</span>
                        </div>
                    @endforeach
                </div>

                <div class="text-xs text-[#7C7C7C] flex justify-between">
                    <span>Total</span>
                    <span class="tabular-nums">{{ $sehat + $dirujuk + $meninggal }}</span>
                </div>
            </div>
        </section>

        {{-- Kunjungan Nifas per Bulan (KF) --}}
        <section class="bg-white rounded-2xl p-5 shadow-md relative">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="">
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
                        <label for="year" class="block text-sm font-medium text-[#4B4B4B] mb-1">Pilih Tahun</label>
                        <select id="year" name="year"
                                class="w-full border border-[#CAC7C7] rounded-xl px-3 py-2 text-sm focus:outline-none">
                            @foreach ($availableYears ?? [now()->year] as $y)
                                <option value="{{ $y }}" @selected(($selectedYear ?? now()->year) == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ url()->current() }}" class="text-sm text-[#B9257F] hover:underline">Reset</a>
                        <button type="submit" class="bg-[#B9257F] text-white text-sm px-4 py-2 rounded-xl">Terapkan</button>
                    </div>
                </form>
            </div>

            @php
                $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $data = $seriesBulanan ?? array_fill(0, 12, 0);
                $max = max($data) ?: 1; $sum = array_sum($data);
                $H = 260; $padT = 12; $padB = 40; $innerH = $H - $padT - $padB; $W = 1200;
                $scale = max(80, $max);
                $gridColor = '#1F2937'; $gridOpacity = 0.45; $gridWidth = 1.5; $gridDash = '4,4';
                $yTicks = [0,20,40,60,80];
            @endphp

            <div class="-mx-4">
                <div class="overflow-x-auto">
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none" class="w-full h-56 block">
                        @php
                            $padL = 48; $padR = 48; $innerW = $W - $padL - $padR; $n = 12;
                            $barPx = 50; $minGap = 12;
                            $needMin = $n * $barPx + ($n - 1) * $minGap;
                            if ($needMin <= $innerW) { $extra = $innerW - $needMin; $gap = $minGap + $extra / ($n - 1); $barWidthF = array_fill(0,$n,$barPx); }
                            else { $barPxFit = max(2, ($innerW - ($n - 1) * $minGap) / $n); $gap = $minGap; $barWidthF = array_fill(0,$n,$barPxFit); }
                            $barWInt = array_map(fn($w)=>(int)floor($w), $barWidthF);
                            $sumBars = array_sum($barWInt); $gapInt = (int)floor($gap);
                            $remainder = (int) round($innerW - ($sumBars + $gapInt * ($n - 1)));
                            for ($i=0; $i<$remainder; $i++) { $barWInt[$i % $n] += 1; }
                            $gapAdds = array_fill(0,$n-1,$gapInt);
                            $remain2 = (int) round($innerW - (array_sum($barWInt) + $gapInt*($n-1)));
                            for ($i=0; $i<$remain2; $i++) { $gapAdds[$i % ($n-1)] += 1; }
                            $barWidths = $barWInt;
                        @endphp

                        {{-- GRID --}}
                        @foreach ($yTicks as $tick)
                            @php $ratio = $tick / $scale; $yLine = $padT + ($innerH - $ratio * $innerH); @endphp
                            <line x1="{{ $padL }}" y1="{{ $yLine }}" x2="{{ $W - $padR }}" y2="{{ $yLine }}"
                                  stroke="{{ $gridColor }}" stroke-opacity="{{ $gridOpacity }}"
                                  stroke-width="{{ $gridWidth }}" stroke-linecap="round" stroke-dasharray="{{ $gridDash }}" />
                        @endforeach
                        <line x1="{{ $padL }}" y1="{{ $padT + $innerH }}" x2="{{ $W - $padR }}" y2="{{ $padT + $innerH }}"
                              stroke="{{ $gridColor }}" stroke-opacity="0.55" stroke-width="2" stroke-linecap="round" />

                        {{-- BATANG --}}
                        @php $xAcc = $padL; $n=12; @endphp
                        @if ($sum > 0)
                            @foreach ($data as $i => $val)
                                @php
                                    $myW = $barWidths[$i]; $hVal = ($val / $scale) * $innerH;
                                    $yTop = $padT + ($innerH - $hVal); $yBot = $yTop + $hVal;
                                    $r = min($myW / 2, 16, $hVal); $xL = $xAcc; $xR = $xAcc + $myW;
                                @endphp
                                <path d="M {{ $xL }},{{ $yBot }} L {{ $xL }},{{ $yTop + $r }}
                                         Q {{ $xL }},{{ $yTop }} {{ $xL + $r }},{{ $yTop }}
                                         L {{ $xR - $r }},{{ $yTop }}
                                         Q {{ $xR }},{{ $yTop }} {{ $xR }},{{ $yTop + $r }}
                                         L {{ $xR }},{{ $yBot }} Z" fill="#B9257F" />
                                @if ($val > 0)
                                    <text x="{{ $xAcc + $myW / 2 }}" y="{{ max(14, $yTop - 8) }}" text-anchor="middle" font-size="10" fill="#B9257F">{{ $val }}</text>
                                @endif
                                @php $xAcc += $myW + ($i < $n-1 ? $gapAdds[$i] : 0); @endphp
                            @endforeach
                        @else
                            @for ($i = 0; $i < $n; $i++)
                                @php
                                    $myW = $barWidths[$i]; $hVal = 10;
                                    $yTop = $padT + ($innerH - $hVal); $yBot = $yTop + $hVal;
                                    $r = min($myW / 2, 16, $hVal); $xL = $xAcc; $xR = $xAcc + $myW;
                                @endphp
                                <path d="M {{ $xL }},{{ $yBot }} L {{ $xL }},{{ $yTop + $r }}
                                         Q {{ $xL }},{{ $yTop }} {{ $xL + $r }},{{ $yTop }}
                                         L {{ $xR - $r }},{{ $yTop }}
                                         Q {{ $xR }},{{ $yTop }} {{ $xR }},{{ $yTop + $r }}
                                         L {{ $xR }},{{ $yBot }} Z" fill="#B9257F" />
                                @php $xAcc += $myW + ($i < $n-1 ? $gapAdds[$i] : 0); @endphp
                            @endfor
                        @endif

                        {{-- LABEL BULAN --}}
                        @php $xAcc = $padL; @endphp
                        @foreach ($months as $i => $label)
                            @php $myW = $barWidths[$i]; $cx = $xAcc + $myW / 2; @endphp
                            <text x="{{ $cx }}" y="{{ $H - 10 }}" text-anchor="middle" font-size="11" fill="#6B7280">{{ $label }}</text>
                            @php $xAcc += $myW + ($i < $n-1 ? $gapAdds[$i] : 0); @endphp
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
                        <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="hidden sm:block w-3.5 h-3.5" alt="">
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

                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#7C7C7C] hidden sm:inline">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="search">
                        </span>

                        <input id="peSearchInput" type="search" name="q"
                               value="{{ $filters['q'] ?? '' }}" placeholder="Cari…" autocomplete="off"
                               class="w-full h-8 border border-[#CAC7C7] rounded-full pl-3 sm:pl-8 pr-7 text-sm focus:outline-none focus:ring-2 focus:ring-black/10"
                               data-autosubmit="true" />

                        <button type="button" id="peSearchClear"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 text-xs border border-[#CAC7C7] rounded-full"
                                aria-label="Bersihkan pencarian">×</button>
                    </form>

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
                        @forelse (($peList ?? []) as $i => $row)
                            <tr>
                                <td class="py-3 tabular-nums">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="max-w-[220px] truncate">{{ $row->nama ?? '-' }}</td>
                                <td class="hidden md:table-cell">{{ $row->nik_masked ?? ($row->nik ?? '-') }}</td>
                                <td class="tabular-nums">{{ $row->umur ?? '-' }}</td>
                                <td class="hidden sm:table-cell">{{ $row->usia_kehamilan ? $row->usia_kehamilan . ' Minggu' : '-' }}</td>
                                <td class="hidden lg:table-cell">{{ $row->tanggal ?? '-' }}</td>
                                <td>
                                    @php $hadirClass = ($row->status_hadir ?? false) ? 'bg-[#39E93F33] text-[#39E93F]' : 'bg-[#E20D0D33] text-[#E20D0D]'; @endphp
                                    <span class="px-3 py-1 rounded-full {{ $hadirClass }}">{{ $row->status_hadir ?? false ? 'Hadir' : 'Mangkir' }}</span>
                                </td>
                                <td>
                                    @php
                                        $mapRisk = [
                                            'non-risk' => ['bg' => '#39E93F33', 'tx' => '#39E93F', 'label' => 'Normal'],
                                            'sedang'   => ['bg' => '#E2D30D33', 'tx' => '#E2D30D', 'label' => 'Sedang'],
                                            'tinggi'   => ['bg' => '#E20D0D33', 'tx' => '#E20D0D', 'label' => 'Tinggi'],
                                        ];
                                        $rk = $mapRisk[$row->resiko ?? 'non-risk'];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full" style="background: {{ $rk['bg'] }}; color: {{ $rk['tx'] }};">{{ $rk['label'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('dinkes.pasien.show', $row->pasien_id) }}"
                                       class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">
                                        View
                                    </a>
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

            <div id="peFilterBackdrop" class="hidden fixed inset-0 bg-black/30 z-40"></div>

            <div id="peFilterModal" class="hidden fixed inset-0 z-50 p-4">
                <div class="grid place-items-center min-h-screen">
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-[#E9E9E9]">
                        <div class="px-5 py-4 border-b flex items-center justify-between">
                            <h3 class="font-semibold">Filter Data Pasien Pre-Eklampsia</h3>
                            <button type="button" data-close class="p-1 rounded hover:bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 6l12 12M18 6l-12 12" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <form id="pe-filter-form" method="GET" action="{{ request()->url() }}" class="p-5 grid gap-4">
                            <div>
                                <label class="block text-xs text-[#7C7C7C] mb-1">Dari Tanggal</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-md px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-[#7C7C7C] mb-1">Sampai Tanggal</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-md px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-[#7C7C7C] mb-1">Resiko</label>
                                <select name="resiko" class="w-full border rounded-md px-3 py-2 text-sm">
                                    <option value="">Semua Resiko</option>
                                    <option value="non-risk" @selected(request('resiko') === 'non-risk')>Normal</option>
                                    <option value="sedang" @selected(request('resiko') === 'sedang')>Sedang</option>
                                    <option value="tinggi" @selected(request('resiko') === 'tinggi')>Tinggi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-[#7C7C7C] mb-1">Status</label>
                                <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                                    <option value="">Semua Status</option>
                                    <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                                    <option value="mangkir" @selected(request('status') === 'mangkir')>Mangkir</option>
                                </select>
                            </div>

                            <input type="hidden" name="q" value="{{ request('q') }}">

                            <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                <button type="button" data-reset class="border border-[#CAC7C7] rounded-md px-3 py-2 text-sm">Reset</button>
                                <button class="bg-[#B9257F] text-white rounded-md px-4 py-2 text-sm">Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <span id="page-bottom"></span>

        <footer class="text-center text-xs text-[#7C7C7C] py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </main>
</div>
</body>
</html>
