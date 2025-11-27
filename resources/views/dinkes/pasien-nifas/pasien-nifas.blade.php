<!DOCTYPE html>
<html lang="id">
{{-- 
    Atribut lang="id" → Memberi tahu browser & reader bahwa bahasa utama halaman ini adalah Bahasa Indonesia.
--}}
<head>
    {{-- 
        Encoding karakter:
        - UTF-8 mendukung huruf latin, simbol, dan karakter khusus lain dengan aman.
    --}}
    <meta charset="UTF-8">

    {{-- 
        Viewport:
        - Menjamin tampilan responsif di layar mobile maupun desktop.
        - width=device-width → lebar konten mengikuti lebar layar perangkat.
        - initial-scale=1.0 → zoom awal 100%.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 
        Title tab browser:
        - Menjelaskan halaman yang sedang dibuka: Data Pasien Nifas Dinkes.
    --}}
    <title>DINKES – Pasien Nifas</title>

    {{-- 
        Vite asset bundler:
        - resources/css/app.css           → stylesheet utama (Tailwind + custom).
        - resources/js/app.js             → JS global (Alpine, helper, dll).
        - resources/js/dropdown.js        → script untuk komponen dropdown generik.
        - resources/js/dinkes/pasien-nifas.js → script khusus halaman pasien nifas.
        - resources/js/dinkes/sidebar-toggle.js → handle toggle sidebar untuk Dinkes.
    --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/dinkes/pasien-nifas.js',
        'resources/js/dinkes/sidebar-toggle.js'
    ])
</head>

{{-- 
    BODY:
    - bg-[#F5F5F5] → warna latar abu muda lembut.
    - font-[Poppins] → konsisten dengan desain modern aplikasi.
    - text-[#000000CC] → teks hitam dengan opacity ±80% agar lebih soft.
--}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    {{-- 
        Wrapper utama:
        - flex flex-col → layout kolom (sidebar + main bisa diatur responsif).
        - min-h-screen → minimal setinggi viewport agar footer bisa di posisi bawah.
    --}}
    <div class="flex flex-col min-h-screen">
        {{-- 
            Sidebar Dinkes:
            Komponen Blade yang memuat menu navigasi Dinkes.
            Umumnya fixed di kiri dengan lebar ±260px (diatur di dalam komponennya).
        --}}
        <x-dinkes.sidebar />

        {{-- 
            MAIN CONTENT:
            - ml-0 md:ml-[260px] → di mobile tidak ada margin kiri, di md ke atas memberi ruang untuk sidebar fixed.
            - flex-1 → bagian utama memenuhi sisa tinggi layar.
            - p-4 sm:p-6 lg:p-8 → padding responsif.
            - space-y-6 → jarak antar blok (header/tabel/footer).
        --}}
        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- 
                flex-1 di wrapper dalam main:
                - supaya konten utama mendorong footer ke bawah (footer tetap di dasar layar).
            --}}
            <div class="flex-1">
                {{-- ====================== --}}
                {{-- HEADER HALAMAN         --}}
                {{-- ====================== --}}
                <header class="w-full space-y-3 sm:space-y-4">
                    {{-- Judul dan subjudul halaman --}}
                    <div>
                        {{-- 
                            Judul utama:
                            - ukuran responsif (22px → 28px).
                            - font-bold → menonjol sebagai heading utama.
                        --}}
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000000]">
                            Data Pasien Nifas
                        </h1>

                        {{-- Subjudul penjelasan singkat --}}
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Kelola Data Pasien Nifas Anda
                        </p>
                    </div>

                    {{-- 
                        Baris kedua header:
                        - berisi form pencarian dan tombol unduh data.
                        - di mobile: vertikal.
                        - di md ke atas: horizontal, dengan space antara kiri-kanan.
                    --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        {{-- ==================== --}}
                        {{-- FORM PENCARIAN      --}}
                        {{-- ==================== --}}
                        <section class="flex items-center gap-3">
                            {{-- 
                                Form GET:
                                - action ke route('dinkes.pasien-nifas') → method index.
                                - name="q" → query string pencarian nama/NIK.
                            --}}
                            <form action="{{ route('dinkes.pasien-nifas') }}" method="GET"
                                class="flex w-full md:w-auto items-center gap-3">
                                {{-- 
                                    Wrapper input:
                                    - full width di mobile, fixed 360px di md ke atas.
                                --}}
                                <div class="relative w-full md:w-[360px]">
                                    {{-- 
                                        Input search:
                                        - nilai default diisi dari variabel $q (jika ada).
                                        - placeholder "Search data..." untuk hint.
                                        - rounded-full + border abu untuk desain modern.
                                    --}}
                                    <input
                                        type="text"
                                        name="q"
                                        value="{{ $q ?? '' }}"
                                        placeholder="Search data..."
                                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40"
                                    >

                                    {{-- Icon search di sebelah kiri input --}}
                                    <img
                                        src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                        class="absolute left-3 top-2.5 w-4 h-4 opacity-60"
                                        alt="search"
                                    >
                                </div>

                                {{-- Tombol submit pencarian --}}
                                <button
                                    class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70] transition">
                                    Search
                                </button>
                            </form>
                        </section>

                        {{-- ==================== --}}
                        {{-- TOMBOL EXPORT DATA  --}}
                        {{-- ==================== --}}
                        {{-- 
                            Link ke route export:
                            - route('dinkes.pasien-nifas.export', ['q' => $q ?? null])
                            - membawa parameter q agar export mengikuti filter pencarian yang sama.
                        --}}
                        <a
                            href="{{ route('dinkes.pasien-nifas.export', ['q' => $q ?? null]) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#B61E7B] px-4 py-2 text-sm font-semibold text-white hover:bg-[#B9257F] transition"
                        >
                            {{-- Icon download di sebelah kiri teks --}}
                            <img
                                src="{{ asset('icons/Iconly/Regular/Outline/Paper Download.svg') }}"
                                alt="Download"
                                class="w-5 h-5"
                            >
                            <span>Unduh Data</span>
                        </a>
                    </div>
                </header>

                {{-- ===================================== --}}
                {{-- LIST (MODE KARTU – MOBILE < md)      --}}
                {{-- ===================================== --}}
                {{-- 
                    Section khusus untuk mobile:
                    - md:hidden → hanya muncul di layar < md.
                    - Menampilkan setiap pasien nifas sebagai kartu vertikal.
                --}}
                <section class="md:hidden mt-2 space-y-3">
                    @forelse ($rows as $idx => $row)
                        {{-- 
                            Card satu pasien nifas:
                            - background putih + shadow.
                            - rounded-2xl untuk gaya modern.
                        --}}
                        <article class="rounded-2xl bg-white shadow p-4">
                            <div class="flex items-start justify-between gap-3">
                                {{-- Kolom kiri: informasi utama pasien --}}
                                <div class="min-w-0">
                                    {{-- Nomor urut global (sesuai pagination) --}}
                                    <div class="text-xs text-[#7C7C7C]">
                                        #{{ $rows->firstItem() + $idx }}
                                    </div>

                                    {{-- Nama pasien (truncate jika terlalu panjang) --}}
                                    <h3 class="font-semibold text-base leading-snug truncate">
                                        {{ $row->name }}
                                    </h3>

                                    {{-- NIK pasien, ditampilkan dalam teks kecil --}}
                                    <div class="text-xs text-[#7C7C7C] break-all">
                                        NIK: {{ $row->nik }}
                                    </div>

                                    {{-- Role penanggung nifas (Bidan / Rumah Sakit / Puskesmas) --}}
                                    <div class="text-xs mt-1">
                                        <span class="text-[#7C7C7C]">Role:</span>
                                        {{ $row->role_penanggung }}
                                    </div>

                                    {{-- TTL: Tempat, Tanggal Lahir --}}
                                    <div class="text-xs mt-1">
                                        <span class="text-[#7C7C7C]">TTL:</span>
                                        @php
                                            // Susun array TTL lalu di-implode dengan koma.
                                            $ttl = [];
                                            if ($row->tempat_lahir) {
                                                $ttl[] = $row->tempat_lahir;
                                            }
                                            if ($row->tanggal_lahir) {
                                                // translatedFormat → tanggal dengan nama bulan lokal (Bahasa Indonesia).
                                                $ttl[] = \Carbon\Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y');
                                            }
                                        @endphp
                                        {{ implode(', ', $ttl) }}
                                    </div>
                                </div>

                                {{-- Kolom kanan: tombol aksi untuk card (View detail) --}}
                                <div class="flex flex-col gap-2">
                                    {{-- 
                                        Tombol "View":
                                        - menuju halaman detail pasien nifas Dinkes.
                                        - param route adalah nifas_id (episode nifas), bukan id pasien.
                                    --}}
                                    <a
                                        href="{{ route('dinkes.pasien-nifas.show', $row->nifas_id) }}"
                                        class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs flex items-center justify-center hover:bg-[#F5F5F5] transition"
                                    >
                                        View
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        {{-- Jika tidak ada data sama sekali --}}
                        <div class="text-center text-[#7C7C7C] py-6">
                            Tidak ada data pasien nifas.
                        </div>
                    @endforelse

                    {{-- PAGINATION (mobile) --}}
                    @if ($rows->hasPages())
                        <div class="pt-2">
                            {{-- 
                                links() → render pagination Laravel.
                                onEachSide(1) → tampilkan 1 halaman di kiri & kanan current page.
                            --}}
                            {{ $rows->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- ===================================== --}}
                {{-- TABEL (DESKTOP & TABLET ≥ md)        --}}
                {{-- ===================================== --}}
                {{-- 
                    Section tabel:
                    - hidden md:block → hanya tampil di layar md ke atas.
                    - border + bg-white + rounded → card besar berisi tabel.
                --}}
                <section class="hidden md:block mt-2 rounded-2xl overflow-hidden border border-[#EDEDED] bg-white">
                    <div class="overflow-x-auto">
                        {{-- 
                            Tabel responsif:
                            - min-w-[900px] → memaksa tabel cukup lebar, biar kolom tidak terlalu sempit.
                            - text-sm text-left → ukuran teks & alignment default.
                        --}}
                        <table class="min-w-[900px] w-full text-sm text-left border-collapse">
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">Role Penanggung</th>
                                    <th class="px-4 py-3 font-semibold">Tempat, Tanggal Lahir</th>
                                    <th class="px-4 py-3 font-semibold text-center w-[180px]">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $idx => $row)
                                    {{-- 
                                        Setiap baris data:
                                        - border-b → garis pemisah antar baris.
                                        - hover:bg → highlight saat cursor di atas.
                                    --}}
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        {{-- No urut global (pakai firstItem untuk hitungan pagination) --}}
                                        <td class="pl-6 pr-4 py-3">
                                            {{ $rows->firstItem() + $idx }}
                                        </td>

                                        {{-- Nama pasien --}}
                                        <td class="px-4 py-3">
                                            {{ $row->name }}
                                        </td>

                                        {{-- NIK --}}
                                        <td class="px-4 py-3">
                                            {{ $row->nik }}
                                        </td>

                                        {{-- Role penanggung (Bidan/RS/Puskesmas) --}}
                                        <td class="px-4 py-3">
                                            {{ $row->role_penanggung }}
                                        </td>

                                        {{-- TTL: Tempat, Tanggal Lahir --}}
                                        <td class="px-4 py-3">
                                            @php
                                                $ttl = [];
                                                if ($row->tempat_lahir) {
                                                    $ttl[] = $row->tempat_lahir;
                                                }
                                                if ($row->tanggal_lahir) {
                                                    $ttl[] = \Carbon\Carbon::parse($row->tanggal_lahir)->translatedFormat('d F Y');
                                                }
                                            @endphp
                                            {{ implode(', ', $ttl) }}
                                        </td>

                                        {{-- Kolom aksi (Detail) --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- 
                                                    Tombol View:
                                                    - latar pink tua (#B9257F) sebagai tombol utama.
                                                    - mengarah ke halaman detail pasien nifas (berbasis nifas_id).
                                                --}}
                                                <a
                                                    href="{{ route('dinkes.pasien-nifas.show', $row->nifas_id) }}"
                                                    class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70] transition"
                                                >
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Jika hasil query kosong --}}
                                    <tr>
                                        <td colspan="6" class="px-6 py-6 text-center text-[#7C7C7C]">
                                            Tidak ada data pasien nifas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION + INFO RANGE DATA (desktop) --}}
                    @if ($rows->hasPages())
                        @php
                            // from = index pertama di halaman ini
                            $from = $rows->firstItem() ?? 0;
                            // to = index terakhir di halaman ini
                            $to   = $rows->lastItem() ?? 0;
                            // tot = total seluruh data
                            $tot  = $rows->total();
                        @endphp

                        {{-- 
                            Bar pagination:
                            - menampilkan teks "Menampilkan X–Y dari Z data".
                            - serta link pagination Laravel.
                        --}}
                        <div
                            class="px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
                            <div class="text-[#7C7C7C]">
                                Menampilkan
                                <span class="font-medium text-[#000000cc]">{{ $from }}</span>–<span
                                    class="font-medium text-[#000000cc]">{{ $to }}</span>
                                dari
                                <span class="font-medium text-[#000000cc]">{{ $tot }}</span>
                                data
                            </div>

                            <div>
                                {{ $rows->onEachSide(1)->links() }}
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            {{-- ====================== --}}
            {{-- FOOTER GLOBAL          --}}
            {{-- ====================== --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
