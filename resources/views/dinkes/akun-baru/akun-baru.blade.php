<!DOCTYPE html>
<html lang="id">
    <!-- 
        lang="id" → Menandakan bahasa utama dokumen ini adalah Bahasa Indonesia.
        Ini membantu mesin pencari dan pembaca layar (screen reader).
    -->

<head>
    <!-- 
        Menentukan format encoding karakter untuk halaman ini.
        UTF-8 mendukung hampir semua karakter (huruf, angka, simbol, emoji, dll).
    -->
    <meta charset="UTF-8">

    <!-- 
        Mengatur viewport agar tampilan responsif di layar HP, tablet, dan desktop.
        - width=device-width → lebar halaman mengikuti lebar layar perangkat.
        - initial-scale=1.0 → zoom awal halaman = 100%.
    -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 
        Judul halaman yang muncul di tab browser.
        Di sini menunjukkan bahwa halaman ini milik modul DINKES dan khusus untuk "Pengajuan Akun".
    -->
    <title>DINKES – Pengajuan Akun</title>

    {{-- 
        @vite(...) → Perintah untuk memuat asset yang di-bundling oleh Vite (build tools bawaan Laravel modern).
        Di sini kita memuat:
        - resources/css/app.css 
            → file CSS utama (biasanya berisi Tailwind + CSS tambahan proyek).
        - resources/js/app.js 
            → file JavaScript utama untuk inisialisasi global (Alpine, event global, dsb).
        - resources/js/dropdown.js 
            → file JS khusus untuk menangani dropdown (jika ada menu dropdown di layout).
        - resources/js/dinkes/sidebar-toggle.js 
            → file JS khusus modul Dinkes untuk mengatur buka-tutup (toggle) sidebar.
        Catatan: sesuai aturan proyek, kita tidak memakai inline script, tapi semua JS masuk ke resource ini.
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    {{-- 
        Tag <body> adalah wadah utama seluruh konten yang terlihat di halaman.
        class yang dipakai:
        - bg-[#F5F5F5] → warna latar (background) abu-abu muda.
        - font-[Poppins] → menggunakan font Poppins (di-set di level global Tailwind).
        - text-[#000000cc] → warna teks hitam dengan sedikit transparansi (opacity).
    --}}

    {{-- 
        <div class="flex flex-col min-h-screen">
        - flex → menggunakan Flexbox sebagai layouting utama.
        - flex-col → mengatur anak-anaknya (sidebar + main content) disusun vertikal.
        - min-h-screen → tinggi minimal memenuhi tinggi layar (100vh).
        Div ini menjadi "container" besar seluruh layout halaman.
    --}}
    <div class="flex flex-col min-h-screen">
        {{-- 
            <x-dinkes.sidebar /> adalah komponen Blade khusus:
            - Komponen ini berisi tampilan sidebar untuk role Dinas Kesehatan.
            - Keuntungan: kode sidebar bisa dipakai ulang di banyak halaman Dinkes.
        --}}
        <x-dinkes.sidebar />

        {{-- 
            <main ...> → area utama konten halaman (di luar sidebar).
            class yang dipakai:
            - ml-0 → margin kiri 0 untuk semua ukuran layar.
            - md:ml-[260px] → ketika layar >= md, beri margin kiri 260px
              supaya main content tidak ketimpa oleh sidebar (yang fixed di kiri).
            - flex-1 → main akan mengisi ruang kosong yang tersisa dalam flex container utama.
            - min-h-screen → tinggi minimal juga satu layar penuh, membantu footer di bawah.
            - flex flex-col → isi main disusun secara vertikal (konten utama + footer).
            - p-4 sm:p-6 lg:p-8 → padding (jarak dalam) responsif, makin besar di layar lebih lebar.
            - space-y-6 → memberi jarak vertikal antar anak langsung di dalam <main>.
        --}}
        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- 
                <div class="flex-1"> → pembungkus isi utama (tanpa footer).
                - flex-1 di sini membuat bagian ini memanjang sehingga 
                  footer bisa berada di bagian bawah halaman secara natural.
            --}}
            <div class="flex-1">
                {{-- 
                    HEADER HALAMAN
                    Bagian ini menampilkan judul besar halaman dan subjudulnya.
                --}}
                <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    {{-- 
                        flex → header memakai Flexbox.
                        - flex-col → di HP (layar kecil), elemen di-stack ke bawah.
                        - sm:flex-row → mulai ukuran "small" ke atas, elemen disusun horizontal.
                        - sm:items-end → di layar lebar, item di-align ke bawah (bottom).
                        - sm:justify-between → di layar lebar, judul di kiri dan mungkin aksi di kanan.
                        - gap-3 → jarak antar elemen di dalam header.
                    --}}
                    <div>
                        {{-- 
                            h1 → Judul utama halaman.
                            class:
                            - text-[22px] sm:text-[28px] → ukuran font lebih besar di layar lebar.
                            - font-bold → teks tebal.
                            - leading-tight → jarak antar baris teks rapat.
                            - text-[#000000] → warna teks hitam solid.
                        --}}
                        <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000000]">
                            Daftar Pengajuan Akun
                        </h1>

                        {{-- 
                            p → Subjudul yang menjelaskan fungsi halaman.
                            class:
                            - text-xs sm:text-sm → ukuran font kecil, sedikit lebih besar di layar lebar.
                            - text-[#7C7C7C] → warna abu-abu agar tidak mendominasi judul.
                        --}}
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Kelola Detail Pengajuan Akun Anda
                        </p>
                    </div>
                </header>

                {{-- 
                    BAGIAN FLASH MESSAGE
                    - Bagian ini hanya tampil kalau ada pesan sementara (flash) di session dengan key 'ok'.
                    - Biasanya dipakai untuk memberitahu hasil aksi: approve / reject / membuat pengajuan.
                --}}
                @if (session('ok'))
                    {{-- 
                        <div> ini adalah tampilan kotak notifikasi berwarna hijau.
                        class:
                        - mt-4 → jarak atas dari header.
                        - rounded-lg → sudut membulat.
                        - border border-green-300 → ada garis border hijau.
                        - bg-green-50 → latar hijau sangat muda.
                        - p-3 → padding di dalam kotak.
                        - text-sm → font ukuran kecil.
                        - text-green-700 → teks hijau agak gelap.
                    --}}
                    <div class="mt-4 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                        {{-- 
                            Menampilkan isi pesan flash 'ok'.
                            Nilainya di-set dari controller dengan ->with('ok', 'pesan...')
                        --}}
                        {{ session('ok') }}
                    </div>
                @endif

                {{-- 
                    SECTION: FORM PENCARIAN
                    - Fungsinya untuk mencari pengajuan akun berdasarkan nama atau email.
                    - Hasil pencarian di-handle di controller dengan membaca parameter 'q' pada query string.
                --}}
                <section class="flex flex-col sm:flex-row sm:items-center gap-3 mt-4 sm:mt-6 mb-4 sm:mb-6">
                    {{-- 
                        <form> ini mengirim request GET ke route 'dinkes.akun-baru'.
                        - method="GET" → query akan muncul di URL (misalnya ?q=...).
                        - class flex ... → membuat input dan tombol sejajar.
                    --}}
                    <form action="{{ route('dinkes.akun-baru') }}" method="GET"
                        class="flex w-full sm:w-auto items-center gap-3">
                        {{-- 
                            <div class="relative ..."> → container untuk input dan ikon search.
                            - relative → agar icon search bisa di-posisikan absolute di dalamnya.
                            - w-full → lebar penuh di HP.
                            - sm:w-[360px] → lebar tetap 360px di layar lebih besar.
                        --}}
                        <div class="relative w-full sm:w-[360px]">
                            {{-- 
                                Input teks pencarian.
                                - name="q" → nama ini yang dibaca di controller untuk filter.
                                - value="{{ $q ?? '' }}" → jika sebelumnya sudah mencari, nilai input akan tetap muncul.
                                class:
                                - w-full → lebar 100% dari parent.
                                - pl-9 → padding kiri lebih besar untuk memberi ruang icon search.
                                - pr-4 py-2 → padding kanan & vertikal.
                                - rounded-full → bentuk input oval / pil.
                                - border border-[#D9D9D9] → garis border abu-abu.
                                - text-sm → ukuran font kecil.
                                - focus:outline-none → hilangkan outline default saat fokus.
                                - focus:ring-1 focus:ring-[#B9257F]/40 → saat fokus ada ring (border glow) warna pink lembut.
                            --}}
                            <input type="text" name="q" value="{{ $q ?? '' }}"
                                placeholder="Cari nama atau email..."
                                class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">

                            {{-- 
                                <img> icon kaca pembesar (search).
                                - src diambil dari folder public/icons/...
                                - class: posisi absolute di kiri input.
                                  left-3 top-2.5 → posisi sekitar tengah vertikal input.
                                  w-4 h-4 → ukuran icon 16x16px.
                                  opacity-60 → icon agak transparan.
                                - alt="search" → deskripsi alternatif untuk aksesibilitas.
                            --}}
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                class="absolute left-3 top-2.5 w-4 h-4 opacity-60" alt="search">
                        </div>

                        {{-- 
                            Tombol submit pencarian.
                            class:
                            - px-5 py-2 → padding horizontal dan vertikal.
                            - rounded-full → sudut membulat penuh.
                            - bg-[#B9257F] → warna latar pink magenta.
                            - text-white → teks putih.
                            - text-sm font-medium → ukuran kecil tapi tebal.
                            - hover:bg-[#a31f70] → warna sedikit lebih gelap saat di-hover.
                            - transition → animasi halus pada perubahan warna.
                        --}}
                        <button
                            class="px-5 py-2 rounded-full bg-[#B9257F] text-white text-sm font-medium hover:bg-[#a31f70] transition">
                            Search
                        </button>
                    </form>
                </section>

                {{-- 
                    LIST MODE KARTU (CARD) UNTUK MOBILE
                    - Section ini hanya tampil di ukuran layar kecil (HP).
                    - class "md:hidden" → di layar >= md, bagian ini disembunyikan.
                    - "space-y-3" → antar card ada jarak vertikal 12px.
                --}}
                <section class="md:hidden space-y-3">
                    {{-- 
                        @forelse: Looping melalui koleksi $requests (hasil paginasi dari controller).
                        - $index → indeks awal dari 0 per halaman.
                        - $req → satu objek user (pengajuan akun pending) per iterasi.
                        Jika tidak ada data sama sekali, otomatis masuk cabang @empty.
                    --}}
                    @forelse ($requests as $index => $req)
                        {{-- 
                            <article> ini mewakili satu pengajuan akun (1 card).
                            class:
                            - bg-white → latar putih.
                            - rounded-2xl → sudut bundar lebih besar.
                            - shadow → ada bayangan lembut (card effect).
                            - p-4 → padding di dalam card.
                        --}}
                        <article class="bg-white rounded-2xl shadow p-4">
                            {{-- 
                                Wrapper isi card:
                                - flex → menata bagian info pemohon dan tombol di sebelahnya.
                                - items-start → sejajarkan di bagian atas.
                                - justify-between → jarak maksimal antara info dan tombol.
                                - gap-3 → jarak antara kolom kiri dan kanan.
                            --}}
                            <div class="flex items-start justify-between gap-3">
                                {{-- 
                                    <div class="min-w-0"> → kolom kiri berisi informasi.
                                    - min-w-0 penting di flex container untuk mengizinkan teks terpotong (truncate).
                                --}}
                                <div class="min-w-0">
                                    {{-- 
                                        Menampilkan nomor urut global:
                                        - text-xs → font sangat kecil.
                                        - text-[#7C7C7C] → abu-abu.
                                        Isi:
                                        $requests->firstItem() → angka urutan pertama di halaman ini (bisa 1, 11, 21, dst).
                                        + $index → ditambah indeks loop untuk dapat nomor berurutan per baris.
                                    --}}
                                    <div class="text-xs text-[#7C7C7C]">
                                        #{{ $requests->firstItem() + $index }}
                                    </div>

                                    {{-- 
                                        Menampilkan nama pemohon akun.
                                        - h3 → heading kecil.
                                        - font-semibold → agak tebal.
                                        - text-base → ukuran sedang.
                                        - leading-snug → jarak antar baris cukup rapat.
                                        - truncate → jika terlalu panjang, diberi "..." di ujung.
                                    --}}
                                    <h3 class="font-semibold text-base leading-snug truncate">
                                        {{ $req->name }}
                                    </h3>

                                    {{-- 
                                        Menampilkan email pemohon.
                                        - text-xs → kecil.
                                        - text-[#7C7C7C] → abu-abu.
                                        - break-all → jika email terlalu panjang, bisa dipotong di mana saja,
                                          supaya tidak memaksa card melebar keluar layar.
                                    --}}
                                    <div class="text-xs text-[#7C7C7C] break-all">
                                        {{ $req->email }}
                                    </div>

                                    {{-- 
                                        Menampilkan role yang diajukan.
                                        - "Role:" → label statis.
                                        - {{ $req->role->nama_role ?? '-' }} → jika relasi role ada, ambil nama_role,
                                          kalau tidak ada, tampilkan tanda "-".
                                    --}}
                                    <div class="mt-1 text-xs">
                                        <span class="text-[#7C7C7C]">Role:</span>
                                        {{ $req->role->nama_role ?? '-' }}
                                    </div>
                                </div>

                                {{-- 
                                    Kolom kanan: tombol aksi Terima dan Tolak.
                                    - flex flex-col → tombol ditumpuk vertikal.
                                    - gap-2 → jarak antar tombol.
                                --}}
                                <div class="flex flex-col gap-2">
                                    {{-- 
                                        FORM TERIMA PENGAJUAN AKUN
                                        - action route('dinkes.akun-baru.approve', $req->id)
                                          → arahkan ke method approve di AkunBaruController dengan parameter id user.
                                        - method="POST" → menggunakan HTTP POST.
                                        - data-confirm="..." → atribut HTML khusus (custom) yang biasanya dibaca oleh JS global
                                          untuk memunculkan dialog konfirmasi sebelum form disubmit.
                                    --}}
                                    <form action="{{ route('dinkes.akun-baru.approve', $req->id) }}" method="POST"
                                        {{-- 
                                            Komentar di dalam tag form, menjelaskan fungsi data-confirm:
                                            - digunakan oleh JavaScript untuk menampilkan konfirmasi.
                                        --}}
                                        data-confirm="Terima pengajuan akun {{ $req->name }}?">
                                        {{-- @csrf → token keamanan untuk mencegah CSRF (Cross-Site Request Forgery). --}}
                                        @csrf
                                        {{-- 
                                            Tombol TERIMA:
                                            - px-3 py-1 → ukuran tombol kecil.
                                            - rounded-full → sudut membulat.
                                            - bg-[#A3E4D7] → warna hijau muda.
                                            - text-[#007965] → teks hijau tua.
                                            - text-xs font-medium → teks kecil, agak tebal.
                                            - hover:opacity-90 → saat di-hover sedikit lebih gelap.
                                            - transition → perubahan efek lebih halus.
                                            - w-full → lebar penuh di kolomnya.
                                        --}}
                                        <button
                                            class="px-3 py-1 rounded-full bg-[#A3E4D7] text-[#007965] text-xs font-medium hover:opacity-90 transition w-full">
                                            ✓ Terima
                                        </button>
                                    </form>

                                    {{-- 
                                        FORM TOLAK PENGAJUAN AKUN
                                        - action route('dinkes.akun-baru.reject', $req->id)
                                          → arahkan ke method reject di AkunBaruController dengan parameter id user.
                                        - method="POST" → tetap POST, nanti diubah menjadi DELETE dengan @method('DELETE').
                                    --}}
                                    <form action="{{ route('dinkes.akun-baru.reject', $req->id) }}" method="POST"
                                        data-confirm="Tolak pengajuan akun {{ $req->name }}?">
                                        {{-- Token keamanan CSRF. --}}
                                        @csrf
                                        {{-- 
                                            @method('DELETE') 
                                            → Menginstruksikan Laravel bahwa request ini sebenarnya
                                              ingin diperlakukan sebagai HTTP DELETE.
                                            → Penting untuk route yang didefinisikan dengan method DELETE.
                                        --}}
                                        @method('DELETE')
                                        {{-- 
                                            Tombol TOLAK:
                                            - bg-[#B9257F] → warna pink magenta pekat.
                                            - text-white → teks putih.
                                            - hover:bg-[#a31f70] → warna sedikit lebih gelap saat di-hover.
                                            - Properti lain mirip tombol Terima, hanya beda warna dan label.
                                        --}}
                                        <button
                                            class="px-3 py-1 rounded-full bg-[#B9257F] text-white text-xs font-medium hover:bg-[#a31f70] transition w-full">
                                            ✕ Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        {{-- 
                            Bagian ini muncul jika @forelse tidak menemukan data sama sekali.
                            - Menampilkan teks informasi "Tidak ada pengajuan akun."
                            - Ditata di tengah (text-center) dengan padding vertikal 24px (py-6).
                        --}}
                        <div class="text-center text-[#7C7C7C] py-6">
                            Tidak ada pengajuan akun.
                        </div>
                    @endforelse

                    {{-- 
                        PAGINASI UNTUK MOBILE
                        - $requests->hasPages() → true jika jumlah data lebih dari satu halaman.
                        - Jika iya, tampilkan link paginasi Laravel (<< < 1 2 3 > >>).
                    --}}
                    @if ($requests->hasPages())
                        {{-- 
                            <div class="pt-2"> → beri jarak atas kecil antara daftar card dan paginasi.
                        --}}
                        <div class="pt-2">
                            {{-- 
                                $requests->onEachSide(1)->links()
                                → Membuat komponen paginasi dengan 1 halaman di kiri dan kanan halaman aktif.
                                → Blade akan merender HTML untuk link paginasi otomatis.
                            --}}
                            {{ $requests->onEachSide(1)->links() }}
                        </div>
                    @endif
                </section>

                {{-- 
                    TABEL PENGAJUAN (VERSI DESKTOP & TABLET)
                    - Section ini kebalikan dari versi mobile:
                      * hidden md:block → disembunyikan di layar kecil, tampil di layar ≥ md.
                    - Menyajikan data pengajuan dalam bentuk tabel yang lebih lebar.
                --}}
                <section class="hidden md:block bg-white rounded-2xl shadow-md overflow-hidden">
                    {{-- 
                        <div class="overflow-x-auto">
                        - Membuat kontainer tabel bisa di-scroll ke samping jika lebar tabel melebihi lebar layar (tablet).
                    --}}
                    <div class="overflow-x-auto">
                        {{-- 
                            <table> → menampilkan data pengajuan dalam format tabel.
                            class:
                            - min-w-[760px] → tabel punya lebar minimal 760px.
                            - w-full → lebar tabel mengikuti lebar kontainer jika lebih besar.
                            - text-sm → ukuran font kecil.
                            - text-left → isi tabel rata kiri.
                            - border-collapse → garis border tabel akan digabung (tidak terpisah).
                        --}}
                        <table class="min-w-[760px] w-full text-sm text-left border-collapse">
                            {{-- 
                                THEAD → bagian kepala tabel (judul kolom).
                                class:
                                - bg-[#F5F5F5] → latar abu-abu muda.
                                - text-[#7C7C7C] → teks abu-abu.
                                - border-b border-[#D9D9D9] → garis bawah tabel dengan warna abu.
                            --}}
                            <thead class="bg-[#F5F5F5] text-[#7C7C7C] border-b border-[#D9D9D9]">
                                <tr>
                                    {{-- 
                                        Kolom judul "No" → nomor urut.
                                        - pl-6 pr-4 py-3 → padding kiri-besar, kanan-sedang, atas-bawah.
                                        - font-semibold → teks agak tebal.
                                    --}}
                                    <th class="pl-6 pr-4 py-3 font-semibold">No</th>
                                    {{-- Kolom judul "Nama". --}}
                                    <th class="px-4 py-3 font-semibold">Nama</th>
                                    {{-- Kolom judul "E-mail". --}}
                                    <th class="px-4 py-3 font-semibold">E-mail</th>
                                    {{-- Kolom judul "Role". --}}
                                    <th class="px-4 py-3 font-semibold">Role</th>
                                    {{-- 
                                        Kolom judul "Aksi".
                                        - text-center → isi header berada di tengah.
                                        - w-[220px] → lebar kolom diatur sekitar 220px (untuk muat dua tombol).
                                    --}}
                                    <th class="px-4 py-3 font-semibold text-center w-[220px]">Aksi</th>
                                </tr>
                            </thead>
                            {{-- 
                                TBODY → badan tabel, berisi baris-baris data pengajuan.
                            --}}
                            <tbody>
                                {{-- 
                                    Loop setiap pengajuan akun sama seperti di versi card:
                                    - @forelse menjaga fallback ketika data kosong.
                                --}}
                                @forelse ($requests as $index => $req)
                                    {{-- 
                                        Setiap <tr> mewakili satu pengajuan akun.
                                        class:
                                        - border-b border-[#E9E9E9] → garis pemisah antar baris.
                                        - hover:bg-[#F9F9F9] → latar berubah abu muda ketika kursor di atas baris.
                                        - transition → membuat efek hover halus.
                                    --}}
                                    <tr class="border-b border-[#E9E9E9] hover:bg-[#F9F9F9] transition">
                                        {{-- 
                                            Kolom nomor urut global.
                                            - pl-6 pr-4 py-3 → padding seimbang.
                                            Isi:
                                            $requests->firstItem() + $index → sama seperti di card mobile.
                                        --}}
                                        <td class="pl-6 pr-4 py-3">
                                            {{ $requests->firstItem() + $index }}
                                        </td>

                                        {{-- 
                                            Kolom Nama pemohon akun.
                                            - px-4 py-3 → padding standar.
                                        --}}
                                        <td class="px-4 py-3">
                                            {{ $req->name }}
                                        </td>

                                        {{-- Kolom E-mail pemohon akun. --}}
                                        <td class="px-4 py-3">
                                            {{ $req->email }}
                                        </td>

                                        {{-- 
                                            Kolom Role pemohon (nama_role dari relasi role).
                                            Jika relasi tidak ada, tampilkan "-".
                                        --}}
                                        <td class="px-4 py-3">
                                            {{ $req->role->nama_role ?? '-' }}
                                        </td>

                                        {{-- 
                                            Kolom Aksi: berisi tombol Terima dan Tolak.
                                        --}}
                                        <td class="px-4 py-3">
                                            {{-- 
                                                <div> ini memposisikan kedua tombol di tengah.
                                                class:
                                                - flex → menggunakan Flexbox.
                                                - justify-center → posisikan di tengah horizontal.
                                                - flex-wrap → jika layar sempit, tombol boleh turun ke baris berikutnya.
                                                - gap-2 → jarak antar tombol.
                                            --}}
                                            <div class="flex justify-center flex-wrap gap-2">
                                                {{-- 
                                                    FORM TERIMA PENGAJUAN AKUN (versi tabel).
                                                    - action sama: menuju route approve dengan id user.
                                                    - method="POST".
                                                    - data-confirm → untuk konfirmasi sebelum submit.
                                                --}}
                                                <form action="{{ route('dinkes.akun-baru.approve', $req->id) }}"
                                                    method="POST"
                                                    data-confirm="Terima pengajuan akun {{ $req->name }}?">
                                                    @csrf
                                                    {{-- 
                                                        Tombol Terima (versi tabel, ukurannya sedikit lebih tinggi).
                                                        - px-3 py-3 → padding lebih besar sehingga tombol tampak bulat penuh.
                                                        - Properti warna sama seperti versi card.
                                                    --}}
                                                    <button
                                                        class="px-3 py-3 rounded-full bg-[#A3E4D7] text-[#007965] text-xs font-medium hover:opacity-90 transition">
                                                        ✓ Terima
                                                    </button>
                                                </form>

                                                {{-- 
                                                    FORM TOLAK PENGAJUAN AKUN (versi tabel).
                                                    - action: route reject dengan id user.
                                                    - method POST + @method('DELETE') untuk menandai sebagai DELETE.
                                                --}}
                                                <form action="{{ route('dinkes.akun-baru.reject', $req->id) }}"
                                                    method="POST"
                                                    data-confirm="Tolak pengajuan akun {{ $req->name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    {{-- 
                                                        Tombol Tolak dengan warna merah/pink.
                                                        class sama dengan tombol Terima, hanya beda warna dan label.
                                                    --}}
                                                    <button
                                                        class="px-3 py-3 rounded-full bg-[#B9257F] text-white text-xs font-medium hover:bg-[#a31f70] transition">
                                                        ✕ Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- 
                                        Baris fallback ketika tidak ada data di tabel.
                                        - colspan="5" → menyatukan 5 kolom menjadi satu lebar penuh.
                                        - text-center → teks di tengah.
                                        - text-[#7C7C7C] → warna abu-abu.
                                    --}}
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-[#7C7C7C]">
                                            Tidak ada pengajuan akun.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- 
                        PAGINASI + INFORMASI RANGE DATA (VERSI TABEL)
                        - Hanya ditampilkan kalau total data > 0.
                        - Menjelaskan data ke berapa sampai ke berapa yang sedang ditampilkan.
                    --}}
                    @if ($requests->total() > 0)
                        @php
                            // Mengambil angka urutan pertama di halaman ini.
                            $from = $requests->firstItem() ?? 0;
                            // Mengambil angka urutan terakhir di halaman ini.
                            $to = $requests->lastItem() ?? 0;
                            // Mengambil total seluruh data yang ada di database (sesuai filter).
                            $tot = $requests->total();
                        @endphp

                        {{-- 
                            <div> ini berisi teks "Menampilkan X–Y dari Z data" dan komponen paginasi.
                            class:
                            - px-4 sm:px-6 → padding horizontal responsif.
                            - py-3 sm:py-4 → padding vertikal responsif.
                            - flex flex-col sm:flex-row → di HP disusun vertikal, di layar lebar horizontal.
                            - sm:items-center sm:justify-between → di layar lebar teks kiri dan paginasi kanan.
                            - gap-3 → jarak antar bagian.
                            - text-xs sm:text-sm → font sedikit lebih besar di layar lebar.
                        --}}
                        <div
                            class="px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
                            {{-- 
                                Bagian teks informasi paging:
                                "Menampilkan X–Y dari Z data".
                            --}}
                            <div class="text-[#7C7C7C]">
                                Menampilkan
                                <span class="font-medium text-[#000000cc]">{{ $from }}</span>–<span
                                    class="font-medium text-[#000000cc]">{{ $to }}</span>
                                dari
                                <span class="font-medium text-[#000000cc]">{{ $tot }}</span>
                                data
                            </div>

                            {{-- 
                                Bagian komponen paginasi Laravel.
                                onEachSide(1) → menunjukkan 1 halaman di kiri dan kanan halaman aktif.
                            --}}
                            <div>
                                {{ $requests->onEachSide(1)->links() }}
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            {{-- 
                FOOTER HALAMAN
                - Diletakkan di luar div.flex-1 agar selalu muncul di bawah konten.
                - text-center → teks di tengah.
                - text-xs → ukuran font kecil.
                - text-[#7C7C7C] → warna abu-abu.
                - py-6 → padding atas-bawah cukup besar agar tidak terlalu mepet.
            --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
