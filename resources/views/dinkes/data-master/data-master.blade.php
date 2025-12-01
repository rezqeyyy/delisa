<!DOCTYPE html>
<html lang="id">
    {{-- 
        Atribut lang="id" → menandakan bahwa bahasa utama konten halaman ini adalah Bahasa Indonesia.
        Ini membantu browser dan alat bantu seperti screen reader memahami bahasa yang digunakan.
    --}}
<head>
    {{-- 
        Mengatur encoding karakter dokumen menjadi UTF-8.
        UTF-8 memungkinkan penulisan karakter latin, simbol, dan banyak aksara lain dengan benar.
    --}}
    <meta charset="UTF-8" />

    {{-- 
        Mengatur viewport agar tampilan responsif di berbagai perangkat:
        - width=device-width → lebar konten menyesuaikan lebar layar perangkat.
        - initial-scale=1.0 → level zoom awal = 100%.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- 
        Judul halaman yang akan tampil di tab browser.
        Menjelaskan bahwa halaman ini adalah "Data Master" untuk modul DINKES.
    --}}
    <title>DINKES – Data Master</title>

    {{-- 
        Memanggil asset yang di-bundling menggunakan Vite:
        - resources/css/app.css → stylesheet utama (Tailwind + custom CSS).
        - resources/js/app.js → JavaScript global aplikasi Laravel (misal: Alpine, event global).
        - resources/js/dropdown.js → skrip khusus untuk dropdown (jika digunakan di layout umum).
        - resources/js/dinkes/dinkes-data-master.js → skrip khusus halaman Data Master (konfirmasi, dll).
        - resources/js/dinkes/sidebar-toggle.js → skrip untuk buka/tutup sidebar Dinkes.
        Sesuai guideline, tidak ada inline script, semua JS lewat berkas resource.
    --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/dinkes/dinkes-data-master.js',
        'resources/js/dinkes/sidebar-toggle.js',
    ])
</head>

{{-- 
    <body> sebagai pembungkus utama semua konten HTML.
    class:
    - bg-[#F5F5F5] → warna latar belakang abu-abu muda.
    - font-[Poppins] → font teks menggunakan Poppins (diset di CSS).
    - text-[#000000cc] → warna teks hitam dengan sedikit transparansi (lebih lembut di mata).
--}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    {{-- 
        Wrapper layout utama:
        - flex flex-col → layout dengan arah vertikal (kolom).
        - min-h-screen → tinggi minimal sama dengan tinggi layar agar footer bisa "nempel" di bawah.
        Di dalamnya ada:
        - Sidebar tetap (komponen Blade).
        - Main area (konten utama halaman).
    --}}
    <div class="flex flex-col min-h-screen">

        {{-- 
            Sidebar (fixed):
            Komponen Blade <x-dinkes.sidebar /> berisi menu navigasi untuk role Dinkes.
            Keuntungannya: kode sidebar terpusat, mudah di-maintain, tidak perlu diulang di setiap view.
        --}}
        <x-dinkes.sidebar />

        {{-- 
            MAIN CONTENT:
            - ml-0 → margin kiri 0 di layar kecil (sidebar biasanya overlay).
            - md:ml-[260px] → di layar ≥ md, margin kiri diberi 260px agar konten tidak tertutup sidebar.
            - flex-1 → mengambil sisa tinggi yang ada (flex layout vertical).
            - min-h-screen → memastikan main tetap setinggi layar minimal.
            - flex flex-col → susunan anak-anaknya vertikal (header, konten, footer).
            - p-4 sm:p-6 lg:p-8 → padding responsif di sisi konten.
            - space-y-6 → jarak vertikal antar blok utama di dalam main (header, list, footer).
        --}}
        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- 
                <div class="flex-1">:
                - flex-1 → bagian ini akan mengisi ruang vertikal di atas footer.
                Di dalamnya: header, flash message, tab, pencarian, list data, tabel, dll.
            --}}
            <div class="flex-1">
                {{-- ======================== --}}
                {{-- HEADER HALAMAN DATA MASTER --}}
                {{-- ======================== --}}
                <header class="mb-4 sm:mb-6">
                    {{-- 
                        Baris utama header:
                        - flex → menggunakan flexbox.
                        - flex-col → di layar kecil, elemen bertumpuk vertikal (judul di atas, tombol di bawah).
                        - sm:flex-row → di layar ≥ sm, susun horizontal (judul kiri, tombol kanan).
                        - sm:items-end → di layar ≥ sm, bottom-align (judul dan tombol).
                        - sm:justify-between → jarak antara kiri dan kanan dibuat maksimal.
                        - gap-3 → jarak antar elemen.
                    --}}
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                        {{-- Kolom kiri: judul dan subjudul --}}
                        <div>
                            {{-- 
                                Judul utama halaman:
                                - text-[22px] sm:text-[28px] → ukuran font berbeda di mobile dan desktop.
                                - font-bold → huruf tebal.
                                - leading-tight → jarak antar baris rapat.
                                - text-[#000] → warna hitam solid.
                            --}}
                            <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">
                                Daftar Akun
                            </h1>

                            {{-- 
                                Subjudul yang menjelaskan fungsi halaman:
                                - text-xs sm:text-sm → ukuran lebih kecil dari judul.
                                - text-[#7C7C7C] → abu-abu, sehingga tidak "mengalahkan" judul.
                            --}}
                            <p class="text-xs sm:text-sm text-[#7C7C7C]">
                                Kelola Detail Akun Anda
                            </p>
                        </div>

                        {{-- 
                            Kolom kanan (khusus layar kecil):
                            - sm:hidden → hanya tampil di mobile, disembunyikan di layar ≥ sm.
                            Di layar besar, tombol "Tambah Akun" muncul di lokasi berbeda (di bawah search).
                        --}}
                        <div class="sm:hidden">
                            {{-- 
                                Tombol tambah akun:
                                - href menuju route create data master dengan parameter tab aktif.
                                - inline-flex items-center gap-2 → teks dan ikon "+" berjejer rapi.
                                - bg-[#B9257F] text-white → warna tombol utama (aksen magenta).
                                - px-4 py-2 → padding tombol.
                                - rounded-full → bentuk tombol oval.
                                - text-sm font-medium → ukuran dan ketebalan teks.
                                - shadow-md hover:bg-[#a31f70] → bayangan dan efek hover.
                            --}}
                            <a href="{{ route('dinkes.data-master.create', ['tab' => $tab]) }}"
                                class="inline-flex items-center gap-2 bg-[#B9257F] text-white px-4 py-2 rounded-full text-sm font-medium shadow-md hover:bg-[#a31f70]">
                                <span class="text-lg font-bold">+</span> Tambah Akun
                            </a>
                        </div>
                    </div>
                </header>

                {{-- ================== --}}
                {{-- FLASH & ERROR AREA --}}
                {{-- ================== --}}

                {{-- 
                    Flash sukses:
                    Ditampilkan ketika session('ok') ada, biasanya setelah operasi berhasil (tambah/update/delete/reset).
                --}}
                @if (session('ok'))
                    {{-- 
                        flash-alert:
                        - mb-3 → jarak ke bawah.
                        - flex items-start gap-3 → icon + teks sejajar dengan jarak.
                        - rounded-lg border border-green-300 → box dengan border hijau muda.
                        - bg-green-50 → latar hijau sangat muda.
                        - p-3 → padding isi.
                        - text-sm text-green-700 → ukuran dan warna teks hijau.
                        - transition-opacity duration-500 → animasi memudarkan (digunakan oleh JS auto-hide).
                        - data-flash, data-timeout="3500" → atribut untuk JS agar tahu ini alert yang bisa auto-hide 3.5 detik.
                    --}}
                    <div class="flash-alert mb-3 flex items-start gap-3 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700 transition-opacity duration-500"
                        role="alert" data-flash data-timeout="3500">
                        {{-- Icon status sukses --}}
                        <span class="mt-0.5">✅</span>
                        {{-- Isi pesan, mengambil dari session('ok') --}}
                        <div class="flex-1">{{ session('ok') }}</div>
                        {{-- Tombol close, dikendalikan oleh JS (flash-close) --}}
                        <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                    </div>
                @endif

                {{-- 
                    Alert error validasi atau error lain:
                    Ditampilkan jika $errors memiliki isi.
                --}}
                @if ($errors->any())
                    {{-- 
                        flash-alert error:
                        - border-red-300, bg-red-50, text-red-700 → tema warna merah (error).
                        - data-flash data-timeout → bisa diatur auto-hide oleh JS.
                    --}}
                    <div class="flash-alert mb-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 transition-opacity duration-500"
                        role="alert" data-flash data-timeout="3500">
                        {{-- 
                            flex container untuk icon + list error + tombol close.
                        --}}
                        <div class="flex items-start gap-3">
                            {{-- Icon peringatan --}}
                            <span class="mt-0.5">⚠️</span>
                            {{-- List semua pesan error --}}
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            {{-- Tombol close --}}
                            <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                        </div>
                    </div>
                @endif

                {{-- ========== --}}
                {{-- TAB ROLE  --}}
                {{-- ========== --}}
                {{-- 
                    Section tab untuk memilih jenis akun:
                    - Bidan PKM
                    - Rumah Sakit
                    - Puskesmas
                    class:
                    - flex flex-wrap items-center gap-2 sm:gap-3 → tab bisa terbungkus di beberapa baris jika layar sempit.
                    - mb-4 → jarak ke bawah.
                --}}
                <section class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
                    {{-- Tab: Bidan PKM --}}
                    <a href="{{ route('dinkes.data-master', ['tab' => 'bidan', 'q' => $q ?? '']) }}"
                        {{-- 
                            class dm-tab → bisa digunakan JS untuk manipulasi tab jika perlu.
                            Style:
                            - jika tab aktif = 'bidan' → bg[#B9257F], text putih.
                            - jika tidak → bg putih + border abu + hover abu muda.
                        --}}
                        class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'bidan' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                        Bidan Mandiri
                    </a>

                    {{-- Tab: Rumah Sakit --}}
                    <a href="{{ route('dinkes.data-master', ['tab' => 'rs', 'q' => $q ?? '']) }}"
                        class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'rs' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                        Rumah Sakit
                    </a>

                    {{-- Tab: Puskesmas --}}
                    <a href="{{ route('dinkes.data-master', ['tab' => 'puskesmas', 'q' => $q ?? '']) }}"
                        class="dm-tab px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'puskesmas' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B] hover:bg-[#F5F5F5]' }}">
                        Puskesmas
                    </a>
                </section>

                {{-- ======================= --}}
                {{-- SEARCH + TOMBOL TAMBAH --}}
                {{-- ======================= --}}
                {{-- 
                    Section untuk form pencarian + tombol tambah akun (versi desktop).
                    - flex flex-col sm:flex-row → di mobile bertumpuk, di desktop sejajar.
                    - sm:items-center → align tengah secara vertikal di desktop.
                    - gap-3 → jarak antar elemen.
                    - mb-4 sm:mb-6 → margin bawah.
                --}}
                <section class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4 sm:mb-6">
                    {{-- 
                        Form search:
                        - action route('dinkes.data-master') → memanggil index DataMasterController.
                        - method="GET" → agar parameter pencarian muncul sebagai query string (?q=...&tab=...).
                        class:
                        - flex w-full sm:w-auto items-center gap-3 → form dan input rapi dalam satu baris.
                    --}}
                    <form action="{{ route('dinkes.data-master') }}" method="GET"
                        class="flex w-full sm:w-auto items-center gap-3">
                        {{-- 
                            Hidden input 'tab':
                            - mempertahankan tab aktif saat melakukan pencarian.
                        --}}
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        {{-- 
                            Wrapper input search:
                            - relative → agar icon search bisa di-posisikan absolute di dalam input.
                            - w-full → lebar penuh di mobile.
                            - sm:w-[360px] → di layar ≥ sm, lebar tetap 360px.
                        --}}
                        <div class="relative w-full sm:w-[360px]">
                            {{-- 
                                Input teks pencarian:
                                - name="q" → akan dibaca di controller untuk filter query.
                                - value="{{ $q ?? '' }}" → mempertahankan kata kunci terakhir.
                                - placeholder "Search data..." → petunjuk singkat untuk user.
                                class:
                                - pl-9 → padding kiri lebih besar, memberi ruang untuk icon search.
                                - pr-4 py-2 → padding kanan dan vertikal.
                                - rounded-full border abu → gaya input.
                                - focus:ring → efek fokus dengan ring warna magenta transparan.
                            --}}
                            <input name="q" value="{{ $q ?? '' }}" type="text"
                                placeholder="Search data..."
                                class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">

                            {{-- 
                                Icon search:
                                - posisi absolute di dalam input.
                                - left-3 top-2.5 → posisi relatif pada input.
                                - w-4 h-4 → ukuran icon.
                                - opacity-60 → sedikit transparan (tidak terlalu mencolok).
                            --}}
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                        </div>

                        {{-- 
                            Tombol submit pencarian:
                            - "Search" → teks jelas.
                            - bg-[#B9257F] text-white → warna utama.
                            - rounded-full → gaya pill.
                            - text-sm font-medium → typografi tombol.
                        --}}
                        <button
                            class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium">
                            Search
                        </button>
                    </form>

                    {{-- 
                        Tombol "Tambah Akun" versi desktop:
                        - hidden sm:block → hanya tampil di layar ≥ sm.
                        - sm:ml-auto → dorong ke sisi kanan di layout flex.
                    --}}
                    <div class="hidden sm:block sm:ml-auto">
                        {{-- 
                            Tombol tambah akun:
                            - href ke route create dengan tab aktif.
                            - id="btnTambahAkun" → bisa digunakan JS untuk tracking/behavior khusus.
                            - flex items-center gap-2 → icon + teks sejajar.
                            - bg magenta, text putih, shadow, dan efek hover.
                        --}}
                        <a href="{{ route('dinkes.data-master.create', ['tab' => $tab]) }}" id="btnTambahAkun"
                            class="flex items-center gap-2 bg-[#B9257F] text-white px-5 py-2 rounded-full text-sm font-medium shadow-md hover:bg-[#a31f70] transition">
                            <span class="text-lg font-bold">+</span> Tambah Akun
                        </a>
                    </div>
                </section>

                {{-- ========================================= --}}
                {{-- TOAST PASSWORD BARU (SETELAH RESET)     --}}
                {{-- ========================================= --}}
                {{-- 
                    Ditampilkan jika ada session('new_password') dan session('pw_user_id'):
                    - artinya barusan ada proses reset password untuk user tertentu.
                    - toast ini menampilkan password baru dan bisa di-copy.
                --}}
                @if (session('new_password') && session('pw_user_id'))
                    {{-- 
                        pwToast:
                        - mb-3 → jarak dengan konten di bawah.
                        - flex items-start gap-3 → icon + teks + tombol close.
                        - border-amber-300 bg-amber-50 text-amber-800 → tema kuning (informasi penting).
                        - shadow → tampak mengambang.
                        - transition-all duration-300 → animasi halus ketika muncul/hilang.
                        data-*:
                        - data-timeout="5000" → durasi auto-hide (5 detik), dikendalikan JS.
                        - data-user-id → ID user yang di-reset.
                        - data-password → password baru (untuk diisi ke section detail).
                    --}}
                    <div id="pwToast"
                        class="mb-3 flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800 shadow transition-all duration-300"
                        data-timeout="5000" role="status" data-user-id="{{ session('pw_user_id') }}"
                        data-password="{{ session('new_password') }}">
                        {{-- Icon gembok / keamanan --}}
                        <div class="mt-0.5">🔐</div>

                        {{-- Isi toast --}}
                        <div class="flex-1">
                            {{-- Judul kecil di dalam toast --}}
                            <div class="font-semibold">Password baru telah dibuat</div>

                            {{-- Baris yang menampilkan nilai password + tombol salin --}}
                            <div class="mt-1">
                                {{-- 
                                    Span password:
                                    - inline-block → bisa diberi padding dan border.
                                    - font-mono → font monospaced (mudah dibaca karakter per karakter).
                                    - px-2 py-1 rounded bg-white border → gaya chip.
                                    - select-all → memudahkan user untuk blok semua teks password dengan sekali klik.
                                --}}
                                <span
                                    class="inline-block font-mono px-2 py-1 rounded bg-white border border-amber-200 select-all"
                                    id="pwValue">
                                    {{ session('new_password') }}
                                </span>

                                {{-- 
                                    Tombol "Salin":
                                    - dikendalikan oleh JS (pwCopyBtn) untuk menyalin password ke clipboard.
                                --}}
                                <button type="button" id="pwCopyBtn"
                                    class="ml-2 inline-flex items-center h-7 px-2 rounded border border-amber-300 hover:bg-amber-100">
                                    Salin
                                </button>
                            </div>

                            {{-- Info tambahan mengenai perilaku toast ini --}}
                            <p class="mt-1 text-xs opacity-80">
                                Simpan password ini dengan aman. Kotak ini akan hilang otomatis,
                                tetapi password tetap tersimpan di browser ini dan dapat dilihat pada halaman detail
                                akun.
                            </p>
                        </div>

                        {{-- Tombol X untuk menutup toast secara manual --}}
                        <button type="button" id="pwToastClose" class="opacity-60 hover:opacity-100">✕</button>
                    </div>
                @endif

                {{-- ====================================== --}}
                {{-- LIST MODE KARTU (VERSI MOBILE / md:UP) --}}
                {{-- ====================================== --}}
                {{-- 
                    Section untuk tampilan kartu (card) di mobile:
                    - md:hidden → hanya tampil di layar < md.
                    - space-y-3 → jarak antar card.
                    - id="dataMasterCards" → bisa diakses/diatur oleh JS jika perlu.
                --}}
                <section class="md:hidden space-y-3" id="dataMasterCards">
                    {{-- 
                        @forelse → looping koleksi $accounts (paginasi) sebagai daftar akun.
                        - $index → index per halaman, digunakan untuk penomoran.
                    --}}
                    @forelse ($accounts as $index => $acc)
                        {{-- 
                            Satu card akun:
                            - bg-white → card putih.
                            - rounded-2xl shadow p-4 → visual card modern.
                        --}}
                        <article class="bg-white rounded-2xl shadow p-4">
                            {{-- 
                                Baris atas card:
                                - flex items-start justify-between gap-3 → info di kiri, menu aksi di kanan.
                            --}}
                            <div class="flex items-start justify-between gap-3">
                                {{-- Kolom kiri: informasi dasar akun --}}
                                <div>
                                    {{-- 
                                        Nomor urut:
                                        - Menggunakan $accounts->firstItem() + $index agar nomor tetap konsisten meski paginasi.
                                    --}}
                                    <div class="text-xs text-[#7C7C7C]">
                                        #{{ $accounts->firstItem() + $index }}
                                    </div>

                                    {{-- Nama akun --}}
                                    <h3 class="font-semibold text-base leading-snug">
                                        {{ $acc->name }}
                                    </h3>

                                    {{-- Email akun (break-all supaya rapi di layar sempit) --}}
                                    <div class="text-xs text-[#7C7C7C] break-all">
                                        {{ $acc->email }}
                                    </div>
                                </div>

                                {{-- 
                                    Kolom kanan: menu aksi vertikal:
                                    - Reset Password
                                    - Detail
                                    - Update
                                    - Delete
                                    Setiap aksi didesain sebagai tombol 1 baris penuh di mobile.
                                --}}
                                <div class="flex flex-col gap-2">
                                    {{-- Form: Reset Password --}}
                                    <form method="POST"
                                        action="{{ route('dinkes.data-master.reset', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                        {{-- data-confirm → atribut yang akan dibaca JS global untuk menampilkan konfirmasi sebelum submit. --}}
                                        data-confirm="Reset password untuk {{ $acc->name }}?">
                                        @csrf
                                        {{-- Tombol reset password (tampilan kecil) --}}
                                        <button
                                            class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">
                                            Reset Password
                                        </button>
                                    </form>

                                    {{-- Link: Detail akun --}}
                                    <a href="{{ route('dinkes.data-master.show', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                        class="h-8 flex items-center justify-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">
                                        Detail
                                    </a>

                                    {{-- Link: Update akun --}}
                                    <a href="{{ route('dinkes.data-master.edit', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                        class="h-8 flex items-center justify-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5] w-full">
                                        Update
                                    </a>

                                    {{-- Form: Delete akun --}}
                                    <form method="POST"
                                        action="{{ route('dinkes.data-master.destroy', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                        data-confirm="Hapus akun {{ $acc->name }}? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        {{-- Tombol delete: teks merah dan latar merah muda saat hover --}}
                                        <button
                                            class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs text-[#E20D0D] hover:bg-[#FFF0F0] w-full">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        {{-- Pesan ketika tidak ada data sama sekali (mode kartu) --}}
                        <div class="text-center text-[#7C7C7C] py-6">
                            Data tidak ditemukan.
                        </div>
                    @endforelse

                    {{-- 
                        Paginasi mode kartu:
                        - Hanya ditampilkan jika koleksi $accounts memiliki lebih dari 1 halaman.
                    --}}
                    @if ($accounts->hasPages())
                        <div class="pt-2">
                            {{ $accounts->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- ========================================= --}}
                {{-- TABEL (DESKTOP & TABLET, md:BLOCK)       --}}
                {{-- ========================================= --}}
                {{-- 
                    Section kontainer utama tabel:
                    - id="dataMasterContent" → untuk JS jika ingin memanipulasi tampilan.
                    - hidden md:block → disembunyikan di mobile, muncul di layar ≥ md.
                --}}
                <section id="dataMasterContent" class="hidden md:block">
                    {{-- 
                        Card tabel:
                        - bg-white, rounded-2xl, shadow-md → gaya card utama.
                        - overflow-hidden → sudut tabel ikut rounded.
                    --}}
                    <section class="bg-white rounded-2xl shadow-md overflow-hidden">
                        {{-- 
                            Wrapper overflow-x-auto:
                            - jika tabel terlalu lebar, di layar kecil (tablet) bisa di-scroll horizontal.
                        --}}
                        <div class="overflow-x-auto">
                            {{-- 
                                Tabel data akun:
                                - min-w-[760px] → minimal lebar 760px agar kolom tidak terlalu sempit.
                                - w-full → menggunakan lebar 100% container.
                                - text-sm text-left → gaya teks tabel.
                            --}}
                            <table class="min-w-[760px] w-full text-sm text-left">
                                {{-- THEAD: header kolom tabel --}}
                                <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                    <tr>
                                        {{-- Kolom nomor urut --}}
                                        <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                        {{-- Kolom Nama --}}
                                        <th class="px-4 py-3 font-semibold">Nama</th>
                                        {{-- Kolom E-mail --}}
                                        <th class="px-4 py-3 font-semibold">E-mail</th>
                                        {{-- 
                                            Kolom Aksi:
                                            - text-center → judul kolom rata tengah.
                                            - w-[420px] → lebar tetap cukup besar untuk tombol-tombol aksi.
                                        --}}
                                        <th class="pl-4 pr-3 py-3 font-semibold text-center w-[420px]">Aksi</th>
                                    </tr>
                                </thead>

                                {{-- TBODY: isi tabel --}}
                                <tbody id="tableBody">
                                    {{-- 
                                        Loop setiap akun dalam $accounts:
                                        - $index → index per halaman (0,1,2,...).
                                    --}}
                                    @forelse ($accounts as $index => $acc)
                                        {{-- 
                                            Satu baris data:
                                            - border-b abu → garis pemisah baris.
                                            - hover:bg-[#F9F9F9] → highlight saat kursor berada di baris.
                                        --}}
                                        <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                            {{-- Nomor urut global (menggunakan firstItem untuk paginasi) --}}
                                            <td class="pl-6 pr-4 py-3">
                                                {{ $accounts->firstItem() + $index }}
                                            </td>

                                            {{-- Nama akun --}}
                                            <td class="px-4 py-3">
                                                {{ $acc->name }}
                                            </td>

                                            {{-- Email akun --}}
                                            <td class="px-4 py-3">
                                                {{ $acc->email }}
                                            </td>

                                            {{-- Kolom aksi: Reset, Detail, Update, Delete --}}
                                            <td class="pl-4 pr-3 py-3">
                                                {{-- 
                                                    Flex container tombol:
                                                    - justify-end → semua tombol dirapatkan ke kanan.
                                                    - flex-wrap gap-2 → jika layar tidak cukup lebar, tombol bisa turun ke baris bawah.
                                                --}}
                                                <div class="flex justify-end flex-wrap gap-2">
                                                    {{-- Form Reset Password --}}
                                                    <form method="POST"
                                                        action="{{ route('dinkes.data-master.reset', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                        data-confirm="Reset password untuk {{ $acc->name }}?">
                                                        @csrf
                                                        {{-- Tombol Reset --}}
                                                        <button
                                                            class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                            Reset Password
                                                        </button>
                                                    </form>

                                                    {{-- Link Detail Akun --}}
                                                    <a href="{{ route('dinkes.data-master.show', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                        class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                        Detail
                                                    </a>

                                                    {{-- Link Update Akun --}}
                                                    <a href="{{ route('dinkes.data-master.edit', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                        class="h-8 flex items-center border border-[#D9D9D9] rounded-md px-3 text-xs hover:bg-[#F5F5F5]">
                                                        Update
                                                    </a>

                                                    {{-- Form Delete Akun --}}
                                                    <form method="POST"
                                                        action="{{ route('dinkes.data-master.destroy', ['user' => $acc->id, 'tab' => $tab, 'q' => $q ?? '']) }}"
                                                        data-confirm="Hapus akun {{ $acc->name }}? Tindakan ini tidak dapat dibatalkan.">
                                                        @csrf
                                                        @method('DELETE')
                                                        {{-- Tombol Delete --}}
                                                        <button
                                                            class="h-8 border border-[#D9D9D9] rounded-md px-3 text-xs text-[#E20D0D] hover:bg-[#FFF0F0]">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- 
                                            Jika tidak ada data sama sekali:
                                            - tampilkan satu baris dengan pesan "Data tidak ditemukan."
                                        --}}
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-[#7C7C7C]">
                                                Data tidak ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- 
                            Bagian bawah tabel: info paginasi + navigasi halaman.
                            Hanya ditampilkan jika total data > 0.
                        --}}
                        @if ($accounts->total() > 0)
                            @php
                                // $from → nomor data pertama di halaman sekarang.
                                $from = $accounts->firstItem() ?? 0;
                                // $to → nomor data terakhir di halaman sekarang.
                                $to = $accounts->lastItem() ?? 0;
                                // $tot → total seluruh data di query.
                                $tot = $accounts->total();
                            @endphp
                            {{-- 
                                Footer paginasi:
                                - px-4 sm:px-6 → padding horizontal responsif.
                                - py-3 sm:py-4 → padding vertikal.
                                - flex flex-col sm:flex-row → di mobile bertumpuk, di desktop sejajar.
                                - sm:items-center sm:justify-between → align dan beri jarak.
                                - text-xs sm:text-sm → ukuran teks.
                            --}}
                            <div
                                class="px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
                                {{-- Informasi "Menampilkan X–Y dari Z data" --}}
                                <div class="text-[#7C7C7C]">
                                    Menampilkan
                                    <span class="font-medium text-[#000000cc]">{{ $from }}</span>–<span
                                        class="font-medium text-[#000000cc]">{{ $to }}</span>
                                    dari
                                    <span class="font-medium text-[#000000cc]">{{ $tot }}</span>
                                    data
                                </div>

                                {{-- Komponen pagination Laravel standar --}}
                                <div>
                                    {{ $accounts->onEachSide(1)->links() }}
                                </div>
                            </div>
                        @endif
                    </section>
                </section>

            </div>

            {{-- ============== --}}
            {{-- FOOTER GLOBAL --}}
            {{-- ============== --}}
            {{-- 
                Footer halaman:
                - text-center → teks rata tengah.
                - text-[11px] sm:text-xs → ukuran kecil.
                - text-[#7C7C7C] → warna abu-abu.
                - py-4 sm:py-6 → padding vertikal.
                Isi: informasi hak cipta dan nama sistem (DeLISA).
            --}}
            <footer class="text-center text-[11px] sm:text-xs text-[#7C7C7C] py-4 sm:py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
