<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Dasbor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex flex-col min-h-screen">
        <x-dinkes.sidebar />

        <!-- Konten utama -->
        <main class="ml-[260px] flex-1 p-8 space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between bg-white px-5 py-4 rounded-2xl shadow-md">
                <div class="relative w-[520px] max-w-[58%]">
                    <span class="absolute inset-y-0 left-3 flex items-center">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60"
                            alt="Search">
                    </span>
                    <input type="text" placeholder="Search data..."
                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('dinkes.profile.edit') }}"
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90"
                            alt="Setting">
                    </a>


                    <button
                        class="relative w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" class="w-4 h-4 opacity-90"
                            alt="Notif">
                        <span
                            class="absolute top-1.5 right-1.5 inline-block w-2.5 h-2.5 bg-[#B9257F] rounded-full ring-2 ring-white"></span>
                    </button>

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


                            <div class="leading-tight pr-1 text-left">
                                <p class="text-[13px] font-semibold text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                <p class="text-[11px] text-[#7C7C7C] -mt-0.5">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                <p class="text-xs text-[#7C7C7C] truncate">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}
                                </p>
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

            <!-- GRID KPI: baris grid tinggi tetap -->
            <section class="grid grid-cols-12 gap-6 lg:auto-rows-[280px]">
                <!-- KIRI ATAS: Daerah Asal Pasien — 2 Donut Simetris (Depok | Non Depok) -->
                <div
                    class="col-span-14 lg:col-span-7 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr] gap-4 overflow-hidden">

                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="w-3.5 h-3.5"
                                    alt="">
                            </span>
                            <h2 class="font-semibold text-lg">Daerah Asal Pasien</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="">
                    </div>

                    @php
                        $depok = $depok ?? 0;
                        $non = $non ?? 0;
                        $total = max($depok + $non, 1);
                        $pDepok = round(($depok / $total) * 100);
                        $pNon = 100 - $pDepok;
                    @endphp

                    <!-- Isi: tata letak sederhana & simetris (kiri | divider | kanan) -->
                    <div class="grid grid-cols-[1fr_auto_1fr] items-center justify-items-center px-2 md:px-6">
                        <!-- Kolom kiri: Depok -->
                        <div class="w-full flex flex-col items-center gap-3">
                            <div class="text-sm font-medium">Depok</div>

                            <!-- Donut responsif, always centered & proportional -->
                            <div class="relative aspect-square" style="width:min(130px,40vw); max-width:200px;">
                                <!-- Track -->
                                <div class="absolute inset-0 rounded-full border-[12px] border-[#F1F1F1]"></div>
                                <!-- Ring -->
                                <div class="absolute inset-0 rounded-full"
                                    style="background: conic-gradient(#B9257F {{ $pDepok }}%, #F1F1F1 0 100%);">
                                </div>
                                <!-- Hole -->
                                <div class="absolute inset-[10px] bg-white rounded-full"></div>
                                <!-- Label tengah -->
                                <div class="absolute inset-0 grid place-items-center text-center">
                                    <div>
                                        <div class="text-3xl font-extrabold leading-none tabular-nums">
                                            {{ $pDepok }}%</div>
                                        <div class="text-xs text-[#7C7C7C] mt-0.5">Proporsi</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Angka jumlah -->
                            <div class="text-sm flex items-center gap-2">
                                <span class="text-[#7C7C7C]">Jumlah:</span>
                                <span class="font-semibold tabular-nums">{{ $depok }}</span>
                            </div>
                        </div>

                        <!-- Divider tengah -->
                        <div class="hidden sm:block w-px self-stretch bg-[#E5E5E5] mx-2"></div>

                        <!-- Kolom kanan: Non Depok -->
                        <div class="w-full flex flex-col items-center gap-3">
                            <div class="text-sm font-medium">Non Depok</div>

                            <div class="relative aspect-square" style="width:min(130px,40vw); max-width:200px;">
                                <div class="absolute inset-0 rounded-full border-[12px] border-[#F1F1F1]"></div>
                                <div class="absolute inset-0 rounded-full"
                                    style="background: conic-gradient(#E9A9CD {{ $pNon }}%, #F1F1F1 0 100%);">
                                </div>
                                <div class="absolute inset-[10px] bg-white rounded-full"></div>
                                <div class="absolute inset-0 grid place-items-center text-center">
                                    <div>
                                        <div class="text-3xl font-extrabold leading-none tabular-nums">
                                            {{ $pNon }}%</div>
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
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Group 36721.svg') }}"
                                    class="w-3.5 h-3.5" alt="">
                            </span>
                            <h2 class="font-semibold text-lg">Risiko Pre-Eklampsia</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="">
                    </div>

                    @php
                        $normal = $normal ?? 0;
                        $risk = $risk ?? 0;
                        $sum = max(($normal ?? 0) + ($risk ?? 0), 1);
                        $pRisk = round(($risk / $sum) * 100);
                    @endphp

                    <div class="grid grid-cols-2 gap-4 items-center text-center">
                        <div class="relative w-36 h-36 place-self-center">
                            <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0"
                                    stroke-width="4"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#B9257F"
                                    stroke-width="4" stroke-dasharray="{{ $pRisk }},100"
                                    stroke-linecap="round"></circle>
                            </svg>
                            <div class="absolute inset-0 grid place-content-center text-center">
                                <span class="text-2xl font-bold tabular-nums">{{ $pRisk }}%</span>
                                <span class="text-xs text-[#7C7C7C] -mt-1">Beresiko</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2"><span
                                        class="w-3 h-3 rounded-sm bg-[#F0F0F0]"></span>Normal</div>
                                <span class="font-semibold tabular-nums">{{ $normal }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex c gap-2"><span
                                        class="w-3 h-3 rounded-sm bg-[#B9257F]"></span>Beresiko</div>
                                <span class="font-semibold tabular-nums">{{ $risk }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-[#7C7C7C] border-t pt-2">
                                <span>Total</span><span class="tabular-nums">{{ $normal + $risk }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KIRI BAWAH: Data Pasien Nifas – versi DONUT sepenuhnya responsif & proporsional -->
                <div
                    class="col-span-12 lg:col-span-7 lg:row-span-2 bg-white rounded-2xl p-5 shadow-md grid grid-rows-[auto_1fr_auto] gap-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Regular/Light/3 User.svg') }}" class="w-3.5 h-3.5"
                                    alt="">
                            </span>
                            <h2 class="font-semibold text-lg">Data Pasien Nifas</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="">
                    </div>

                    @php
                        $totalNifas = $totalNifas ?? 0;
                        $kf1 = $kf1 ?? 0;
                        $kf2 = $kf2 ?? 0;
                        $kf3 = $kf3 ?? 0;
                        $kf4 = $kf4 ?? 0;
                        $sum = max($totalNifas, 1);
                        $p1 = round(($kf1 / $sum) * 100);
                        $p2 = round(($kf2 / $sum) * 100);
                        $p3 = round(($kf3 / $sum) * 100);
                        $p4 = round(($kf4 / $sum) * 100);
                        // Cakupan total KFi dibanding target 4x kunjungan per pasien
                        $coverage = round((($kf1 + $kf2 + $kf3 + $kf4) / max(1, $totalNifas * 4)) * 100);
                        $coverage = max(0, min(100, $coverage));
                    @endphp

                    <!-- AREA TENGAH: Semua donat proporsional dalam kontainer -->
                    <div class="grid grid-cols-12 gap-6">
                        <!-- Donat besar: Cakupan Total KF1–KF4 -->
                        <div class="col-span-12 lg:col-span-7 grid grid-rows-[auto_1fr]">
                            <div class="flex items-baseline gap-3 mb-3">
                                <span class="text-4xl font-bold tabular-nums">{{ $totalNifas }}</span>
                                <span class="text-sm text-[#7C7C7C]">total pasien nifas</span>
                            </div>

                            <!-- Pembungkus agar donat benar-benar memenuhi tinggi kolom kiri -->
                            <div class="relative w-full h-full">
                                <!-- rasio kotak agar menjadi lingkaran sempurna & selalu ngepas -->
                                <div class="absolute inset-0 grid place-items-center">
                                    <div class="relative aspect-square w-full max-w-[min(360px,100%)]">
                                        <!-- Donut ring -->
                                        <div class="relative w-full h-full rounded-full"
                                            style="background: conic-gradient(#B9257F {{ $coverage }}%, #F1F1F1 {{ $coverage }}% 100%);">
                                            <!-- lubang donat -->
                                            <div class="absolute inset-0 m-[11%] bg-white rounded-full"></div>
                                            <!-- teks di tengah -->
                                            <div class="absolute inset-0 grid place-items-center text-center">
                                                <div>
                                                    <div class="text-3xl font-bold tabular-nums">{{ $coverage }}%
                                                    </div>
                                                    <div class="text-xs text-[#7C7C7C]">Cakupan Kunjungan (KF1–KF4)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Donat kecil: distribusi KF1–KF4 -->
                        <div class="col-span-12 lg:col-span-5 grid grid-cols-2 lg:grid-cols-2 gap-4 content-start">
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
                                    <div class="text-xs text-[#7C7C7C]">{{ $k['label'] }}</div>
                                    <div class="relative w-full h-full grid place-items-center py-2">
                                        <div class="relative aspect-square w-full max-w-[min(180px,100%)]">
                                            <div class="relative w-full h-full rounded-full"
                                                style="background: conic-gradient({{ $k['col'] }} {{ $k['pct'] }}%, #F1F1F1 {{ $k['pct'] }}% 100%);">
                                                <div class="absolute inset-0 m-[14%] bg-white rounded-full"></div>
                                                <div class="absolute inset-0 grid place-items-center">
                                                    <div class="text-lg font-semibold tabular-nums">
                                                        {{ $k['pct'] }}%</div>
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

                    <!-- Footer: legend ringkas & ring progress segmented opsional -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-sm flex-wrap">
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm"
                                    style="background:#B9257F"></span>KF1</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm"
                                    style="background:#D24A97"></span>KF2</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm"
                                    style="background:#E178B3"></span>KF3</span>
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm"
                                    style="background:#F0A6CF"></span>KF4</span>
                        </div>
                        <div class="text-xs text-[#7C7C7C]">Target 4× kunjungan per pasien</div>
                    </div>
                </div>


                <!-- KANAN TENGAH: Pasien Hadir (isi proporsional penuh) -->
                <div
                    class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-2 bg-white rounded-2xl p-5 shadow-md
            grid grid-rows-[auto_1fr_auto]">
                    <!-- header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}" class="w-3.5 h-3.5"
                                    alt="Hadir">
                            </span>
                            <h2 class="font-semibold text-lg">Pasien Hadir</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>

                    @php
                        $hadir = $hadir ?? 0;
                        $mangkir = $mangkir ?? 0;
                        $totalA = max($hadir + $mangkir, 1);
                        $pHadir = round(($hadir / $totalA) * 100);
                        $pMangkir = 100 - $pHadir;
                        $seriesAbsensi = $seriesAbsensi ?? array_fill(0, 12, 0);
                        if (count($seriesAbsensi) < 12) {
                            $seriesAbsensi = array_pad($seriesAbsensi, 12, 0);
                        }
                    @endphp

                    <!-- isi utama (1fr): donut + panel progress -->
                    <div class="grid grid-cols-2 gap-6 items-center">
                        <!-- Donut besar -->
                        <div class="relative w-full h-full grid place-content-center">
                            <svg viewBox="0 0 36 36" class="w-40 h-40 -rotate-90 mx-auto">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0"
                                    stroke-width="4"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#B9257F"
                                    stroke-width="4" stroke-dasharray="{{ $pHadir }},100"
                                    stroke-linecap="round"></circle>
                            </svg>
                            <div class="absolute inset-0 grid place-content-center text-center">
                                <span class="text-3xl font-bold tabular-nums">{{ $pHadir }}%</span>
                                <span class="text-xs text-[#7C7C7C] -mt-1">Hadir</span>
                            </div>
                        </div>

                        <!-- Progress & legend + spark bars -->
                        <div class="flex flex-col justify-center gap-4">
                            <div class="h-4 w-full rounded-full bg-[#F1F1F1] overflow-hidden">
                                <div class="h-full bg-[#39E93F] inline-block" style="width: {{ $pHadir }}%">
                                </div>
                                <div class="h-full bg="#E20D0D inline-block style="width: {{ $pMangkir }}%">
                                </div>
                            </div>

                            <div class="text-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2"><span
                                            class="w-3 h-3 rounded-sm bg-[#39E93F]"></span>Pasien Hadir</div>
                                    <div class="font-semibold tabular-nums">{{ $hadir }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2"><span
                                            class="w-3 h-3 rounded-sm bg-[#E20D0D]"></span>Pasien Tidak Hadir</div>
                                    <div class="font-semibold tabular-nums">{{ $mangkir }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-1 items-end h-14">
                                @foreach ($seriesAbsensi as $v)
                                    @php $h = 10 + min(100, (int) $v); @endphp
                                    <div class="rounded-[6px] bg-[#B9257F]/60" style="height: {{ $h }}%">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-[#7C7C7C] flex justify-between">
                        <span>Total</span>
                        <span class="tabular-nums">{{ $hadir + $mangkir }}</span>
                    </div>
                </div>


                <!-- KANAN BAWAH: Pemantauan (3 mini-donut, compact & rapi) -->
                <div
                    class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-3 bg-white rounded-2xl p-5 shadow-md
                           grid grid-rows-[auto_1fr_auto]">
                    <!-- header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Sharp/Outline/Activity.svg') }}" class="w-3.5 h-3.5"
                                    alt="Pemantauan">
                            </span>
                            <h2 class="font-semibold text-lg">Pemantauan</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>

                    @php
                        $sehat = $sehat ?? 0;
                        $dirujuk = $dirujuk ?? 0;
                        $meninggal = $meninggal ?? 0;
                        $sumP = max($sehat + $dirujuk + $meninggal, 1);
                        $pSehat = round(($sehat / $sumP) * 100);
                        $pDirujuk = round(($dirujuk / $sumP) * 100);
                        $pMeninggal = round(($meninggal / $sumP) * 100);
                    @endphp

                    <!-- isi utama -->
                    <div class="grid grid-cols-3 gap-4 items-center justify-items-center">
                        @foreach ([['label' => 'Sehat', 'val' => $sehat, 'p' => $pSehat, 'color' => '#39E93F'], ['label' => 'Total Dirujuk', 'val' => $dirujuk, 'p' => $pDirujuk, 'color' => '#F5A524'], ['label' => 'Meninggal', 'val' => $meninggal, 'p' => $pMeninggal, 'color' => '#E20D0D']] as $i)
                            <div class="flex flex-col items-center gap-2">
                                <div class="relative w-32 h-32">
                                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                        <circle cx="18" cy="18" r="16" fill="none" stroke="#F0F0F0"
                                            stroke-width="4"></circle>
                                        <circle cx="18" cy="18" r="16" fill="none"
                                            stroke="{{ $i['color'] }}" stroke-width="4"
                                            stroke-dasharray="{{ $i['p'] }},100" stroke-linecap="round">
                                        </circle>
                                    </svg>
                                    <div class="absolute inset-0 grid place-content-center leading-tight text-center">
                                        <span class="text-xl font-bold tabular-nums">{{ $i['val'] }}</span>
                                        <span class="text-[13px] text-[#7C7C7C]">{{ $i['label'] }}</span>
                                    </div>
                                </div>
                                <span
                                    class="block w-24 text-center text-xs text-[#7C7C7C] tabular-nums">{{ $i['p'] }}%</span>
                            </div>
                        @endforeach
                    </div>

                    <!-- footer -->
                    <div class="text-xs text-[#7C7C7C] flex justify-between">
                        <span>Total</span>
                        <span class="tabular-nums">{{ $sehat + $dirujuk + $meninggal }}</span>
                    </div>
                </div>

            </section>

            <!-- Chart contoh -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="w-3.5 h-3.5"
                            alt="">
                    </span>
                    <h2 class="font-semibold">Daerah Asal Pasien</h2>
                </div>
                <div class="grid grid-cols-12 gap-3 h-56 items-end">
                    @foreach (range(1, 12) as $i)
                        <div
                            class="h-{{ [14, 24, 36, 28, 52, 16, 32, 12, 16, 40, 14, 20][$i - 1] }} bg-[#B9257F] rounded-xl">
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-12 text-center text-xs text-[#7C7C7C] mt-3">
                    <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>Mei</span><span>Jun</span><span>Jul</span><span>Agu</span><span>Sep</span><span>Okt</span><span>Nov</span><span>Des</span>
                </div>
            </section>

            <!-- Table: Data Pasien Pre-Eklampsia (dinamis jika $peList tersedia) -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="w-3.5 h-3.5"
                                alt="">
                        </span>
                        <h2 class="font-semibold">Data Pasien Pre-Eklampsia</h2>
                    </div>
                    <button class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">Filter</button>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-[#7C7C7C] border-b border-[#CAC7C7]">
                        <tr>
                            <th class="py-2 text-left">No</th>
                            <th class="text-left">Pasien</th>
                            <th class="text-left">NIK</th>
                            <th class="text-left">Umur</th>
                            <th class="text-left">Usia Kehamilan</th>
                            <th class="text-left">Tanggal</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Resiko</th>
                            <th class="text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#CAC7C7]">
                        @forelse (($peList ?? []) as $i => $row)
                            <tr>
                                <td class="py-3 tabular-nums">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $row->nama ?? '-' }}</td>
                                <td>{{ $row->nik_masked ?? ($row->nik ?? '-') }}</td>
                                <td class="tabular-nums">{{ $row->umur ?? '-' }}</td>
                                <td>{{ $row->usia_kehamilan ? $row->usia_kehamilan . ' Minggu' : '-' }}</td>
                                <td>{{ $row->tanggal ?? '-' }}</td>
                                <td>
                                    @php $hadirClass = ($row->status_hadir ?? false) ? 'bg-[#39E93F33] text-[#39E93F]' : 'bg-[#E20D0D33] text-[#E20D0D]'; @endphp
                                    <span
                                        class="px-3 py-1 rounded-full {{ $hadirClass }}">{{ $row->status_hadir ?? false ? 'Hadir' : 'Mangkir' }}</span>
                                </td>
                                <td>
                                    @php
                                        $mapRisk = [
                                            'non-risk' => ['bg' => '#39E93F33', 'tx' => '#39E93F', 'label' => 'Normal'],
                                            'sedang' => ['bg' => '#E2D30D33', 'tx' => '#E2D30D', 'label' => 'Sedang'],
                                            'tinggi' => ['bg' => '#E20D0D33', 'tx' => '#E20D0D', 'label' => 'Tinggi'],
                                        ];
                                        $rk = $mapRisk[$row->resiko ?? 'non-risk'];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full"
                                        style="background: {{ $rk['bg'] }}; color: {{ $rk['tx'] }};">{{ $rk['label'] }}</span>
                                </td>
                                <td>
                                    <a href="#"
                                        class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <!-- fallback demo rows jika $peList kosong -->
                            <tr>
                                <td class="py-3">01</td>
                                <td>Almira R</td>
                                <td>3201•••8899</td>
                                <td>26</td>
                                <td>28 Minggu</td>
                                <td>12/09/2025</td>
                                <td><span class="px-3 py-1 bg-[#39E93F33] text-[#39E93F] rounded-full">Hadir</span>
                                </td>
                                <td><span class="px-3 py-1 bg-[#E2D30D33] text-[#E2D30D] rounded-full">Sedang</span>
                                </td>
                                <td><button
                                        class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">Detail</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3">02</td>
                                <td>Nadia P</td>
                                <td>3204•••1122</td>
                                <td>30</td>
                                <td>16 Minggu</td>
                                <td>10/09/2025</td>
                                <td><span class="px-3 py-1 bg-[#E20D0D33] text-[#E20D0D] rounded-full">Mangkir</span>
                                </td>
                                <td><span class="px-3 py-1 bg-[#E20D0D33] text-[#E20D0D] rounded-full">Tinggi</span>
                                </td>
                                <td><button
                                        class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">Detail</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
