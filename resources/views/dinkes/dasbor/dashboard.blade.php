{{-- Deklarasi dokumen HTML5 --}}
<!DOCTYPE html>
{{-- Atur bahasa utama halaman menjadi Bahasa Indonesia --}}
<html lang="id">

<head>
    {{-- Set karakter encoding ke UTF-8 --}}
    <meta charset="UTF-8" />
    {{-- Supaya layout responsif di layar HP / desktop --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- Judul halaman yang tampil di tab browser --}}
    <title>DINKES – Dasbor</title>
    {{-- Memuat file CSS & JS via Vite (asset bundler Laravel) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/dashboard-filters.js', 'resources/js/dinkes/kf-filters.js', 'resources/js/dinkes/donut-nifas-filters.js', 'resources/js/dinkes/pasien-preeklampsia-search.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

{{-- Body utama halaman, dengan background abu dan font Poppins --}}

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    {{-- Wrapper vertikal seluruh halaman (sidebar + konten) --}}
    <div class="flex flex-col min-h-screen">
        {{-- Komponen sidebar khusus Dinkes --}}
        <x-dinkes.sidebar />

        {{-- Konten utama dashboard --}}
        <!-- Konten utama -->
        {{-- Main content: bergeser ke kanan pada layar besar karena ada sidebar lebar 260px --}}
        <main class="lg:ml-[260px] ml-0 flex-1 p-4 sm:p-6 lg:p-8 space-y-6 lg:space-y-8 transition-[margin]">

            {{-- HEADER ATAS DASHBOARD --}}
            <!-- Header -->
            {{-- Baris header putih dengan shadow, berisi tombol profil dan setting --}}
            <div
                class="flex flex-wrap items-center gap-3 justify-between bg-white px-4 sm:px-5 py-3 sm:py-4 rounded-2xl shadow-md">
                {{-- Kolom kiri header (saat ini kosong, bisa diisi judul nanti) --}}
                <div class="flex items-center gap-3 min-w-0"> </div>

                {{-- Kolom kanan header: tombol pengaturan + dropdown profil --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Tombol menuju halaman edit profil dinkes --}}
                    <!-- Settings: icon disembunyikan di mobile (ganti teks 'Set') -->
                    <a href="{{ route('dinkes.profile.edit') }}"
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        {{-- Ikon gear / setting --}}
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}"
                            class="sm:block w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    {{-- Wrapper dropdown profil --}}
                    <!-- Profile dropdown -->
                    <div id="profileWrapper" class="relative">
                        {{-- Tombol yang jika diklik menampilkan menu profil --}}
                        <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            {{-- Jika user punya foto di kolom photo --}}
                            @if (Auth::user()?->photo)
                                {{-- Tampilkan foto profil dari storage --}}
                                <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                    class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                            @else
                                {{-- Kalau tidak punya foto, pakai avatar bulat default --}}
                                <span
                                    class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                    {{-- Ikon user default --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                        <path
                                            d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </span>
                            @endif

                            {{-- Teks nama & email, disembunyikan di mobile (hidden sm:block) --}}
                            <div class="leading-tight pr-1 text-left hidden sm:block">
                                {{-- Nama user, fallback ke 'Nama Dinkes' --}}
                                <p
                                    class="text-[13px] font-semibold text-[#1D1D1D] truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                {{-- Email user, fallback ke 'email Dinkes' --}}
                                <p class="text-[11px] text-[#7C7C7C] -mt-0.5 truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                            </div>
                            {{-- Ikon caret (panah bawah) untuk menandakan dropdown --}}
                            <!-- caret disembunyikan di mobile -->
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="sm:block w-4 h-4 opacity-70" alt="More" />
                        </button>

                        {{-- Menu dropdown profil (default disembunyikan dengan class hidden) --}}
                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            {{-- Bagian atas menu: info singkat nama & email --}}
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Dinkes' }}</p>
                                <p class="text-xs text-[#7C7C7C] truncate">
                                    {{ auth()->user()->email ?? 'email Dinkes' }}</p>
                            </div>
                            {{-- Form logout dengan method POST --}}
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

            {{-- GRID KPI UTAMA (KIRI–KANAN) --}}
            <!-- GRID KPI (LAYOUT 1,5 BARIS KIRI vs 3 KARTU KANAN) -->
            {{-- Grid 2 kolom di layar besar, 1 kolom di mobile --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 lg:grid-rows-6 gap-4 sm:gap-6">

                {{-- 1. KIRI ATAS: Daerah Asal Pasien (span 3 dari 6 row) --}}
                {{-- Kartu besar kiri atas untuk statistik asal pasien (Depok vs non Depok) --}}
                <div class="lg:row-span-3">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 h-full flex flex-col">
                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                {{-- Icon kecil bulat --}}
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                {{-- Judul kartu --}}
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Daerah Asal Pasien</h3>
                            </div>
                        </div>

                        {{-- Isi kartu: dua kolom (Depok dan Non Depok) --}}
                        <div class="flex flex-1 items-center justify-center text-center divide-x divide-[#E9E9E9]">
                            {{-- Kolom Depok --}}
                            <div class="flex-1 px-4">
                                <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Depok</div>
                                <br>
                                {{-- Angka besar jumlah pasien dari Depok --}}
                                <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">
                                    {{ $asalDepok ?? 0 }}
                                </div>
                            </div>
                            {{-- Kolom Non Depok --}}
                            <div class="flex-1 px-4">
                                <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Non Depok</div>
                                <br>
                                {{-- Angka besar jumlah pasien Non Depok --}}
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
                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Group 36721.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Resiko Preeklampsia</h3>
                            </div>
                        </div>

                        {{-- Isi kartu: dua baris angka (Normal vs Berisiko) --}}
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
                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Pasien Hadir</h3>
                            </div>
                        </div>

                        {{-- Isi kartu: jumlah hadir vs tidak hadir --}}
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
                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Regular/Light/3 User.svg') }}"
                                        class="sm:block w-3.5 h-3.5" alt="">
                                </span>
                                <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien Nifas</h3>
                            </div>
                        </div>

                        {{-- Isi kartu: total nifas dan sudah KFI --}}
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
                        {{-- Header kartu --}}
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

                        {{-- Isi kartu: Sehat, Dirujuk, Meninggal --}}
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

            {{-- CHART: Kunjungan Nifas per Bulan (KF) --}}
            {{-- Kunjungan Nifas per Bulan (KF) --}}
            <section class="bg-white rounded-2xl p-5 shadow-md relative">
                {{-- Header section chart KF --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="sm:block w-3.5 h-3.5"
                            alt="">
                    </span>
                    <h2 class="font-semibold">Kunjungan Nifas per Bulan</h2>

                    {{-- Badge tahun aktif --}}
                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-[#F5F5F5] text-[#4B4B4B]">
                        Tahun: {{ $selectedYear ?? now()->year }}
                    </span>

                    {{-- Tombol membuka panel filter tahun KF --}}
                    <button id="btnKfFilter" type="button"
                        class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm ml-auto">
                        Filter
                    </button>
                </div>

                {{-- Panel filter (floating card), default disembunyikan --}}
                {{-- Panel filter --}}
                <div id="kfFilterPanel"
                    class="hidden absolute right-4 sm:right-5 top-16 z-20 w-[calc(100%-2rem)] sm:w-64 bg-white border border-[#E5E7EB] rounded-2xl shadow-xl p-4">
                    {{-- Form filter tahun (GET) --}}
                    <form method="GET" class="space-y-3">
                        <div>
                            <label for="year" class="block text-sm font-medium text-[#4B4B4B] mb-1">
                                Pilih Tahun
                            </label>
                            {{-- Dropdown pilihan tahun dari $availableYears --}}
                            <select id="year" name="year"
                                class="w-full border border-[#CAC7C7] rounded-xl px-3 py-2 text-sm focus:outline-none">
                                @foreach ($availableYears ?? [now()->year] as $y)
                                    <option value="{{ $y }}" @selected(($selectedYear ?? now()->year) == $y)>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            {{-- Link reset kembali ke URL tanpa query --}}
                            <a href="{{ url()->current() }}" class="text-sm text-[#B9257F] hover:underline">Reset</a>
                            {{-- Tombol submit filter --}}
                            <button type="submit" class="bg-[#B9257F] text-white text-sm px-4 py-2 rounded-xl">
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Blok PHP untuk mempersiapkan data chart (months, series, scale, dll) --}}
                @php
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                    // Data kunjungan per bulan (12 slot), fallback ke array 0 jika belum ada
                    $data = $seriesBulanan ?? array_fill(0, 12, 0);

                    // Nilai maksimum untuk skala & jumlah total untuk cek apakah ada data
                    $max = max($data) ?: 1;
                    $sum = array_sum($data);

                    $H = 260; // tinggi SVG
                    $padT = 12; // padding atas
                    $padB = 40; // padding bawah
                    $innerH = $H - $padT - $padB;

                    $W = 1200; // lebar SVG
                    // Skala sumbu Y minimal 80, atau mengikuti max data jika lebih besar
                    $scale = max(80, $max);

                    $gridColor = '#1F2937';
                    $gridOpacity = 0.45;
                    $gridWidth = 1.5;
                    $gridDash = '4,4';

                    // Titik-titik di sumbu Y (0,20,40,60,80)
                    $yTicks = [0, 20, 40, 60, 80];
                @endphp


                {{-- Wrapper agar SVG bisa di-scroll horizontal kalau kurang lebar --}}
                <div class="-mx-4">
                    <div class="overflow-x-auto">
                        {{-- SVG chart batang (bar chart) untuk KF bulanan --}}
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none"
                            class="w-full h-56 block">
                            @php
                                $padL = 48; // padding kiri untuk label Y
                                $padR = 48; // padding kanan
                                $innerW = $W - $padL - $padR;
                                $n = 12; // 12 bulan

                                // Lebar bar ideal & gap minimal
                                $barPx = 50;
                                $minGap = 12;
                                $needMin = $n * $barPx + ($n - 1) * $minGap;

                                if ($needMin <= $innerW) {
                                    // Jika innerW cukup lebar, bar pakai lebar ideal dan gap dibesarkan secara merata
                                    $extra = $innerW - $needMin;
                                    $gap = $minGap + $extra / ($n - 1);
                                    $barWidthF = array_fill(0, $n, $barPx);
                                } else {
                                    // Jika tidak cukup, bar diperkecil agar muat
                                    $barPxFit = max(2, ($innerW - ($n - 1) * $minGap) / $n);
                                    $gap = $minGap;
                                    $barWidthF = array_fill(0, $n, $barPxFit);
                                }

                                // Konversi lebar bar ke integer (floor)
                                $barWInt = array_map(fn($w) => (int) floor($w), $barWidthF);
                                $sumBars = array_sum($barWInt);
                                $gapInt = (int) floor($gap);
                                $remainder = (int) round($innerW - ($sumBars + $gapInt * ($n - 1)));

                                // Sebarkan selisih lebar (remainder) ke bar-bar
                                for ($i = 0; $i < $remainder; $i++) {
                                    $barWInt[$i % $n] += 1;
                                }

                                // Array gap antar bar
                                $gapAdds = array_fill(0, $n - 1, $gapInt);
                                $remain2 = (int) round($innerW - (array_sum($barWInt) + $gapInt * ($n - 1)));

                                // Sebarkan selisih gap bila ada
                                for ($i = 0; $i < $remain2; $i++) {
                                    $gapAdds[$i % ($n - 1)] += 1;
                                }

                                // Lebar bar final
                                $barWidths = $barWInt;
                            @endphp

                            {{-- GRID horizontal + label sumbu Y --}}
                            {{-- GRID + LABEL Y --}}
                            @foreach ($yTicks as $tick)
                                @php
                                    $ratio = $tick / $scale;
                                    $yLine = $padT + ($innerH - $ratio * $innerH);
                                @endphp

                                {{-- Garis grid horizontal putus-putus --}}
                                <line x1="{{ $padL }}" y1="{{ $yLine }}" x2="{{ $W - $padR }}"
                                    y2="{{ $yLine }}" stroke="{{ $gridColor }}"
                                    stroke-opacity="{{ $gridOpacity }}" stroke-width="{{ $gridWidth }}"
                                    stroke-linecap="round" stroke-dasharray="{{ $gridDash }}" />

                                {{-- Label angka di kiri garis Y --}}
                                <text x="{{ $padL - 10 }}" y="{{ $yLine + 4 }}" text-anchor="end"
                                    font-size="10" fill="#6B7280">
                                    {{ $tick }}
                                </text>
                            @endforeach

                            {{-- Garis dasar (sumbu X) --}}
                            {{-- Garis dasar (0) --}}
                            <line x1="{{ $padL }}" y1="{{ $padT + $innerH }}" x2="{{ $W - $padR }}"
                                y2="{{ $padT + $innerH }}" stroke="{{ $gridColor }}" stroke-opacity="0.55"
                                stroke-width="2" stroke-linecap="round" />

                            {{-- BATANG (bar) untuk setiap bulan --}}
                            {{-- BATANG --}}
                            @php
                                $xAcc = $padL;
                                $n = 12;
                            @endphp

                            {{-- Jika ada data (sum > 0) tampilkan bar sesuai nilai --}}
                            @if ($sum > 0)
                                @foreach ($data as $i => $val)
                                    @php
                                        $myW = $barWidths[$i];
                                        $hVal = ($val / $scale) * $innerH; // tinggi bar sesuai skala
                                        $yTop = $padT + ($innerH - $hVal);
                                        $yBot = $yTop + $hVal;
                                        $r = min($myW / 2, 16, $hVal); // radius sudut

                                        $xL = $xAcc;
                                        $xR = $xAcc + $myW;
                                    @endphp

                                    {{-- Satu group bar + label --}}
                                    <g class="kf-chart-bar">
                                        {{-- Path bar dengan sudut atas melengkung --}}
                                        <path d="M {{ $xL }},{{ $yBot }}
                                     L {{ $xL }},{{ $yTop + $r }}
                                     Q {{ $xL }},{{ $yTop }} {{ $xL + $r }},{{ $yTop }}
                                     L {{ $xR - $r }},{{ $yTop }}
                                     Q {{ $xR }},{{ $yTop }} {{ $xR }},{{ $yTop + $r }}
                                     L {{ $xR }},{{ $yBot }} Z" fill="#B9257F" />

                                        {{-- Label angka di atas bar (hanya jika val > 0) --}}
                                        @if ($val > 0)
                                            {{-- Label nilai, hanya muncul saat hover (diatur via CSS/JS) --}}
                                            <text class="kf-chart-bar-label" x="{{ $xAcc + $myW / 2 }}"
                                                y="{{ max(14, $yTop - 8) }}" text-anchor="middle" font-size="10"
                                                fill="#B9257F">
                                                {{ $val }}
                                            </text>
                                        @endif
                                    </g>

                                    @php
                                        // Geser posisi x untuk bar berikutnya
                                        $xAcc += $myW + ($i < $n - 1 ? $gapAdds[$i] : 0);
                                    @endphp
                                @endforeach
                            @else
                                {{-- Jika belum ada data sama sekali, tampilkan bar pendek placeholder --}}
                                {{-- Placeholder jika belum ada data --}}
                                @for ($i = 0; $i < $n; $i++)
                                    @php
                                        $myW = $barWidths[$i];
                                        $hVal = 10; // tinggi kecil
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

                            {{-- LABEL bulan di sumbu X --}}
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

                    {{-- Pesan jika tidak ada data KF di tahun terpilih --}}
                    @if ($sum === 0)
                        <p class="text-sm text-[#7C7C7C] mt-2 px-5">
                            Belum ada data kunjungan nifas pada tahun {{ $selectedYear ?? now()->year }}.
                        </p>
                    @endif
                </div>
            </section>


            {{-- TABEL: Data Pasien Pre-Eklampsia --}}
            <!-- Tabel: Data Pasien Pre-Eklampsia -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                {{-- Header tabel: judul + search + filter + unduh --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}"
                                class="sm:block w-3.5 h-3.5" alt="">
                        </span>
                        <h2 class="font-semibold">Data Pasien Pre-Eklampsia</h2>
                    </div>

                    {{-- Bagian kanan: form search, tombol unduh, tombol filter --}}
                    <!-- Search + Filter -->
                    <div class="flex items-center gap-2">
                        {{-- Form pencarian (GET) berdasarkan q, plus hidden filter lain --}}
                        <form id="peSearchForm" role="search" method="GET" action="{{ url()->current() }}"
                            class="relative w-36 xs:w-44 sm:w-48">
                            {{-- Hidden input untuk mempertahankan filter lain saat search --}}
                            <input type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
                            <input type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
                            <input type="hidden" name="resiko" value="{{ $filters['resiko'] ?? '' }}">
                            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                            <input type="hidden" name="kategori" value="{{ $filters['kategori'] ?? '' }}">
                            <input type="hidden" name="puskesmas_id" value="{{ $filters['puskesmas_id'] ?? '' }}">

                            {{-- Ikon search di kiri input (disembunyikan di mobile kecil) --}}
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#7C7C7C] hidden sm:inline">
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                    class="w-4 h-4 opacity-60" alt="search">
                            </span>

                            {{-- Input text pencarian q --}}
                            <input id="peSearchInput" type="search" name="q"
                                value="{{ $filters['q'] ?? '' }}" placeholder="Cari…" autocomplete="off"
                                class="w-full h-8 border border-[#CAC7C7] rounded-full pl-3 sm:pl-8 pr-7 text-sm focus:outline-none focus:ring-2 focus:ring-black/10"
                                data-autosubmit="true" />

                            {{-- Tombol kecil "x" untuk clear pencarian --}}
                            <button type="button" id="peSearchClear"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 text-xs border border-[#CAC7C7] rounded-full"
                                aria-label="Bersihkan pencarian">×</button>
                        </form>

                        {{-- Tombol membuka modal filter data PE --}}
                        <button id="btnPeFilter" type="button"
                            class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">
                            Filter
                        </button>

                        {{-- Tombol Unduh Data (menggunakan filter query yang sama) --}}
                        {{-- TOMBOL UNDUH (PAKAI FILTER YANG SAMA) --}}
                        <a href="{{ route('dinkes.dashboard.pe-export', request()->query()) }}"
                            class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm whitespace-nowrap">
                            Unduh Data
                        </a>
                    </div>

                </div>

                {{-- Tabel utama daftar pasien PE --}}
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
                            {{-- Looping setiap baris pasien PE --}}
                            @forelse ($peList as $i => $row)
                                <tr>
                                    {{-- nomor urut mengikuti halaman --}}
                                    <td class="py-3 tabular-nums">
                                        {{ str_pad(($peList->firstItem() ?? 0) + $i, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    {{-- Nama pasien --}}
                                    <td class="max-w-[220px] truncate">{{ $row->nama ?? '-' }}</td>
                                    {{-- NIK (masked bila tersedia) --}}
                                    <td class="hidden md:table-cell">{{ $row->nik_masked ?? ($row->nik ?? '-') }}
                                    </td>
                                    {{-- Umur (tahun) --}}
                                    <td class="tabular-nums">{{ $row->umur ?? '-' }}</td>
                                    {{-- Usia kehamilan (minggu) --}}
                                    <td class="hidden sm:table-cell">
                                        {{ $row->usia_kehamilan ? $row->usia_kehamilan . ' Minggu' : '-' }}
                                    </td>
                                    {{-- Tanggal skrining --}}
                                    <td class="hidden lg:table-cell">{{ $row->tanggal ?? '-' }}</td>
                                    {{-- Status hadir / mangkir --}}
                                    <td>
                                        @php
                                            // Tentukan warna badge status hadir
                                            $hadirClass =
                                                $row->status_hadir ?? false
                                                    ? 'bg-[#39E93F33] text-[#39E93F]'
                                                    : 'bg-[#E20D0D33] text-[#E20D0D]';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full {{ $hadirClass }}">
                                            {{ $row->status_hadir ?? false ? 'Hadir' : 'Mangkir' }}
                                        </span>
                                    </td>
                                    {{-- Badge kategori risiko (Normal/Sedang/Tinggi) --}}
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
                                            // Ambil mapping sesuai nilai resiko di row
                                            $rk = $mapRisk[$row->resiko ?? 'non-risk'];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full"
                                            style="background: {{ $rk['bg'] }}; color: {{ $rk['tx'] }};">
                                            {{ $rk['label'] }}
                                        </span>
                                    </td>
                                    {{-- Tombol menuju halaman detail pasien --}}
                                    <td>
                                        <a href="{{ route('dinkes.pasien.show', $row->pasien_id) }}"
                                            class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">View</a>
                                    </td>
                                </tr>
                            @empty
                                {{-- Jika tidak ada data sama sekali --}}
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-[#7C7C7C]">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer tabel: info jumlah data & pagination --}}
                {{-- Footer: total & pagination --}}
                @if ($peList->count())
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                        {{-- Info "Menampilkan X–Y dari Z data" --}}
                        <div class="text-[#7C7C7C]">
                            Menampilkan
                            <span class="font-medium text-[#000000cc]">{{ $peList->firstItem() }}</span> –
                            <span class="font-medium text-[#000000cc]">{{ $peList->lastItem() }}</span>
                            dari
                            <span class="font-medium text-[#000000cc]">{{ $peList->total() }}</span>
                            data
                        </div>

                        {{-- Laravel Tailwind pagination --}}
                        {{-- Laravel Tailwind pagination --}}
                        <div class="w-full sm:w-auto" id="pePagination">
                            {{ $peList->onEachSide(1)->links() }}
                        </div>
                    </div>
                @endif

                {{-- Backdrop gelap untuk modal filter (di belakang modal) --}}
                <!-- Backdrop -->
                <div id="peFilterBackdrop" class="hidden fixed inset-0 bg-black/30 z-40"></div>

                {{-- Modal filter data PE (tengah layar) --}}
                <!-- Modal Filter -->
                <div id="peFilterModal" class="hidden fixed inset-0 z-50 p-4">
                    <div class="grid place-items-center min-h-screen">
                        <div
                            class="bg-white w-full max-w-md max-h-[90vh] rounded-2xl shadow-xl border border-[#E9E9E9] flex flex-col">

                            {{-- Header modal: judul + tombol close --}}
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

                            {{-- Isi modal + scroll --}}
                            {{-- Isi + scroll --}}
                            <form id="pe-filter-form" method="GET" action="{{ request()->url() }}"
                                class="p-5 grid gap-4 flex-1 overflow-y-auto">

                                {{-- Filter rentang tanggal from --}}
                                {{-- Rentang tanggal --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Dari Tanggal</label>
                                    <input type="date" name="from" value="{{ request('from') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>
                                {{-- Filter rentang tanggal to --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Sampai Tanggal</label>
                                    <input type="date" name="to" value="{{ request('to') }}"
                                        class="w-full border rounded-md px-3 py-2 text-sm">
                                </div>

                                {{-- Filter resiko tinggi/sedang/normal --}}
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

                                {{-- Filter status hadir/mangkir --}}
                                {{-- Status hadir --}}
                                <div>
                                    <label class="block text-xs text-[#7C7C7C] mb-1">Status</label>
                                    <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                                        <option value="">Semua Status</option>
                                        <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
                                        <option value="mangkir" @selected(request('status') === 'mangkir')>Mangkir</option>
                                    </select>
                                </div>

                                {{-- Filter kategori (remaja, JKN, asuransi, domisili, BB) --}}
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

                                {{-- Filter Puskesmas --}}
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

                                {{-- Filter UI riwayat penyakit dengan chip multiple --}}
                                {{-- UI SAJA: Riwayat Penyakit (multi select chip) --}}
                                <div class="pt-1 border-t border-dashed border-[#E9E9E9] mt-1">
                                    <p class="block text-xs text-[#7C7C7C] mb-2">
                                        Riwayat Penyakit <span class="text-[10px]">(bisa pilih lebih dari satu)</span>
                                    </p>

                                    @php
                                        // Opsi riwayat penyakit (mapping value => label)
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
                                        // Ambil kunci yang terpilih dari query string
                                        $rpSelected = (array) request('riwayat_penyakit_ui', []);
                                    @endphp

                                    {{-- Grid chip 2 kolom di layar besar, 1 di mobile --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                                        @foreach ($rpOptions as $val => $label)
                                            <label class="cursor-pointer">

                                                {{-- Checkbox real disembunyikan, hanya untuk value --}}
                                                {{-- input checklistnya disembunyikan --}}
                                                <input type="checkbox" name="riwayat_penyakit_ui[]"
                                                    value="{{ $val }}" class="peer hidden"
                                                    @checked(in_array($val, $rpSelected))>

                                                {{-- Tampilan chip (kapsul) yang berubah saat peer-checked --}}
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

                                {{-- Hidden q agar nilai search tetap ikut ketika apply filter --}}
                                <input type="hidden" name="q" value="{{ request('q') }}">

                                {{-- Footer tombol Reset & Terapkan --}}
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

            {{-- Footer kecil di bawah dashboard --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>

            {{-- Anchor kosong di bawah halaman (misal untuk scroll ke bawah) --}}
            <span id="page-bottom"></span>

        </main>
    </div>
</body>

</html>
