<!DOCTYPE html>
<html lang="id">
    {{-- 
        Atribut lang="id" → menandakan bahwa bahasa utama halaman ini adalah Bahasa Indonesia.
        Ini membantu browser dan alat bantu aksesibilitas (screen reader) memahami konteks bahasa.
    --}}
<head>
    {{-- 
        Menentukan encoding karakter dokumen.
        UTF-8 mendukung karakter latin, simbol, dan berbagai aksara lain.
    --}}
    <meta charset="UTF-8">

    {{-- 
        Mengatur viewport agar tampilan responsif di berbagai perangkat:
        - width=device-width → lebar konten mengikuti lebar layar perangkat.
        - initial-scale=1.0 → tingkat zoom awal = 100%.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 
        Judul halaman yang tampil di tab browser.
        Di sini menjelaskan bahwa halaman menampilkan "Detail Akun" untuk modul DINKES.
    --}}
    <title>DINKES – Detail Akun</title>

    {{-- 
        Memuat asset yang di-bundling Vite:
        - resources/css/app.css                 → stylesheet utama (Tailwind + custom).
        - resources/js/app.js                   → JavaScript global proyek.
        - resources/js/dinkes/sidebar-toggle.js → logika buka/tutup sidebar Dinkes.
        - resources/js/dinkes/data-master-show.js → skrip khusus halaman detail (misalnya untuk password reset info).
        Sesuai guideline, tidak menggunakan inline script.
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dinkes/sidebar-toggle.js', 'resources/js/dinkes/data-master-show.js'])
</head>

{{-- 
    <body> sebagai wadah utama konten HTML.
    class:
    - bg-[#F5F5F5] → latar belakang abu-abu muda.
    - font-[Poppins] → font utama menggunakan Poppins (di-setup di CSS).
    - text-[#000000cc] → warna teks hitam dengan sedikit transparansi (lebih soft).
--}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    {{-- 
        Container utama halaman:
        - ml-0 → margin kiri 0 di layar kecil.
        - md:ml-[400px] → di layar ≥ md, halaman digeser 400px ke kanan (ruang untuk sidebar Dinkes).
        - p-4 sm:p-6 lg:p-8 → padding responsif di sekeliling konten.
        - max-w-3xl sm:max-w-4xl → lebar maksimum konten agar tidak terlalu melebar di layar besar.
        - mx-auto → konten dipusatkan secara horizontal.
        Catatan: sidebar sendiri dirender melalui layout/komponen lain, di luar div ini.
    --}}
    <div class="ml-0 md:ml-[400px] p-4 sm:p-6 lg:p-8 max-w-3xl sm:max-w-4xl mx-auto">
        {{-- ===================== --}}
        {{-- HEADER DETAIL AKUN   --}}
        {{-- ===================== --}}
        {{-- 
            Bagian header atas:
            - mb-4 sm:mb-6 → jarak bawah header terhadap konten berikutnya.
            - flex items-center justify-between → judul di kiri, tombol kembali di kanan.
            - gap-3 → jarak antara kedua elemen jika stack.
        --}}
        <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3">
            {{-- 
                Judul halaman detail.
                - text-xl sm:text-2xl → ukuran tulisan responsif.
                - font-bold → teks tebal.
                Isi: "Detail Akun" dan jenis tab (rs / puskesmas / bidan) dalam bentuk huruf kapital di awal (ucfirst).
            --}}
            <h1 class="text-xl sm:text-2xl font-bold">Detail Akun ({{ ucfirst($tab) }})</h1>

            {{-- 
                Tombol/link kembali ke halaman Data Master:
                - href mengarah ke route data master dengan parameter tab aktif saat ini.
                - px-3 sm:px-4 py-2 → padding responsif.
                - rounded-full → sudut bulat penuh (tombol pill).
                - bg-white → latar putih.
                - border abu → garis pinggir abu-abu terang.
                - text-xs sm:text-sm → ukuran teks kecil.
            --}}
            <a href="{{ route('dinkes.data-master', ['tab' => $tab]) }}"
                class="px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">
                ← Kembali
            </a>
        </div>

        {{-- ======================== --}}
        {{-- KARTU DETAIL DATA AKUN  --}}
        {{-- ======================== --}}
        {{-- 
            Card utama yang berisi semua informasi akun:
            - bg-white → latar putih (card).
            - rounded-2xl → sudut cukup besar (tampilan modern).
            - shadow → sedikit bayangan (elevasi).
            - p-4 sm:p-6 → padding internal card (responsif).
            - space-y-3 → jarak vertikal antar blok informasi di dalam card.
        --}}
        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-3">
            {{-- Field: Nama user / PIC --}}
            <div>
                {{-- Label kecil untuk nama --}}
                <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama</span>
                {{-- 
                    Nilai nama:
                    - font-medium → sedikit tebal.
                    - break-words → jika nama panjang, akan dipotong di tengah kata untuk menghindari overflow.
                --}}
                <div class="font-medium break-words">{{ $data->name }}</div>
            </div>

            {{-- Field: Email user / PIC --}}
            <div>
                <span class="text-[#7C7C7C] text-xs sm:text-sm">Email</span>
                {{-- 
                    Email bisa cukup panjang dan mengandung karakter khusus:
                    - break-all → teks akan pecah di mana saja jika panjang (menghindari scroll horizontal).
                --}}
                <div class="font-medium break-all">{{ $data->email }}</div>
            </div>

            {{-- 
                Field tambahan: Nomor Telepon.
                Ditampilkan hanya jika $data->phone terisi (tidak kosong).
                !empty(...) → memastikan nilai bukan null / string kosong.
            --}}
            @if (!empty($data->phone))
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">No. Telepon</span>
                    <div class="font-medium break-words">{{ $data->phone }}</div>
                </div>
            @endif

            {{-- 
                Field tambahan: Alamat Akun (address).
                Berasal dari kolom users.address jika diisi.
            --}}
            @if (!empty($data->address))
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat Akun</span>
                    <div class="font-medium break-words">{{ $data->address }}</div>
                </div>
            @endif

            {{-- ============================ --}}
            {{-- BAGIAN INFORMASI PASSWORD   --}}
            {{-- ============================ --}}
            {{-- 
                dmPasswordSection:
                - id="dmPasswordSection" → dipakai oleh JS (data-master-show.js) untuk mengakses blok ini.
                - data-user-id → menyimpan ID user terkait (untuk korelasi di sisi front-end).
                Tambahan:
                - Jika session('new_password') ada dan pw_user_id sesuai, maka data-init-password diisi password baru
                  (agar langsung tampil di UI setelah reset).
            --}}
            <div id="dmPasswordSection" data-user-id="{{ $data->id }}"
                @if (session('new_password') && session('pw_user_id') == $data->id) data-init-password="{{ session('new_password') }}" @endif>
                {{-- Label bagian password --}}
                <span class="text-[#7C7C7C] text-xs sm:text-sm">
                    Password (hasil reset otomatis)
                </span>

                <div class="mt-1">
                    {{-- 
                        Wrapper yang akan MENAMPILKAN password (jika ada, misalnya setelah reset):
                        - id="dmPasswordValueWrapper" → target manipulasi JS.
                        - class="hidden" → default disembunyikan, akan dibuka oleh JS kalau diperlukan.
                    --}}
                    <div id="dmPasswordValueWrapper" class="hidden">
                        {{-- 
                            Tempat teks password ditampilkan:
                            - font-mono → font monospaced (seperti kode) agar karakter mudah dibaca.
                            - text-sm sm:text-base → ukuran teks responsif.
                            - break-all → karakter dipecah jika terlalu panjang.
                            - bg-[#F5F5F5] → latar abu-abu muda.
                            - rounded-xl px-3 py-2 → tampilan seperti chip kode.
                            - id="dmPasswordValue" → diisi via JS.
                        --}}
                        <div class="font-mono text-sm sm:text-base break-all bg-[#F5F5F5] rounded-xl px-3 py-2"
                            id="dmPasswordValue"></div>

                        {{-- 
                            dmPasswordInfo:
                            - Paragraf penjelasan tambahan terkait waktu reset atau informasi lain.
                            - Diisi dan dimodifikasi via JavaScript.
                            - text-[11px] sm:text-xs → teks informatif kecil.
                        --}}
                        <p id="dmPasswordInfo" class="mt-1 text-[11px] sm:text-xs text-[#7C7C7C]"></p>
                    </div>

                    {{-- 
                        Informasi default jika belum pernah ada reset otomatis:
                        - id="dmPasswordEmptyInfo" → blok info default.
                        - Berisi penjelasan bahwa password hanya muncul di layar setelah aksi reset dilakukan.
                    --}}
                    <div id="dmPasswordEmptyInfo" class="text-xs sm:text-sm text-[#7C7C7C]">
                        Password hanya akan ditampilkan jika Anda melakukan
                        <span class="font-semibold">Reset Password</span>.
                        Saat ini, setiap reset akan mengatur password ke
                        <span class="font-semibold">12345678</span>.

                    </div>
                </div>

                {{-- 
                    Flag tambahan:
                    Jika session('pw_user_id_clear') ada dan sama dengan user ini,
                    maka dibuat elemen <div> kecil dengan id="dmPwClearFlag" dan attribute data-clear="1".
                    Fungsinya: memberi tahu JS bahwa password acak sebelumnya perlu dihapus dari tampilan browser,
                    misalnya setelah dilakukan perubahan tertentu agar tidak tertinggal.
                --}}
                @if (session('pw_user_id_clear') && session('pw_user_id_clear') == $data->id)
                    <div id="dmPwClearFlag" data-clear="1"></div>
                @endif
            </div>

            {{-- ========================= --}}
            {{-- DETAIL SPESIFIK PER ROLE --}}
            {{-- ========================= --}}

            {{-- 
                Jika tab = 'rs', berarti data yang ditampilkan adalah Rumah Sakit.
                Field-field spesifik RS: nama RS, kecamatan, kelurahan, alamat.
            --}}
            @if ($tab === 'rs')
                {{-- Nama Rumah Sakit --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama RS</span>
                    <div class="font-medium">{{ $data->nama }}</div>
                </div>

                {{-- 
                    Grid 2 kolom untuk menampilkan kecamatan dan kelurahan secara berdampingan:
                    - sm:grid-cols-2 → dua kolom di layar ≥ sm.
                    - gap-3 → jarak antar kolom.
                --}}
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                        <div class="font-medium">{{ $data->kecamatan }}</div>
                    </div>
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Kelurahan</span>
                        <div class="font-medium">{{ $data->kelurahan }}</div>
                    </div>
                </div>

                {{-- Alamat Rumah Sakit --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>

            {{-- Jika tab = 'puskesmas' --}}
            @elseif($tab === 'puskesmas')
                {{-- Nama Puskesmas --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama Puskesmas</span>
                    {{-- 
                        Menggunakan $data->nama_puskesmas jika ada, jika tidak fallback ke $data->nama.
                        Ini meng-cover variasi alias/kolom di query join.
                    --}}
                    <div class="font-medium">{{ $data->nama_puskesmas ?? $data->nama }}</div>
                </div>

                {{-- Kecamatan Puskesmas --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                    <div class="font-medium">{{ $data->kecamatan }}</div>
                </div>

                {{-- Alamat Puskesmas --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>

            {{-- Jika bukan rs dan bukan puskesmas, maka dianggap BIDAN --}}
            @else
                {{-- ====== DETAIL BIDAN ====== --}}

                {{-- Nomor Izin Praktek Bidan --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nomor Izin Praktek</span>
                    {{-- 
                        Menampilkan nomor izin praktek jika ada,
                        jika tidak ada tampilkan '—' sebagai placeholder.
                        break-words → jika nomor panjang tetap rapi dipecah.
                    --}}
                    <div class="font-medium break-words">
                        {{ $data->nomor_izin_praktek ?? '—' }}
                    </div>
                </div>

                {{-- Puskesmas tempat Bidan bernaung --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Puskesmas</span>
                    {{-- 
                        nama_puskesmas berasal dari join ke tabel puskesmas.
                        Jika tidak ada data terkait, tampilkan '—'.
                    --}}
                    <div class="font-medium break-words">
                        {{ $data->nama_puskesmas ?? '—' }}
                    </div>
                </div>

                {{-- 
                    Untuk bidan, alamat praktik/domisili bisa diambil dari kolom users.address.
                    Karena di atas sudah ada blok "Alamat Akun", di sini memberi label lebih spesifik.
                    Hanya ditampilkan jika address tidak kosong.
                --}}
                @if (!empty($data->address))
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat Praktik / Domisili</span>
                        <div class="font-medium break-words">{{ $data->address }}</div>
                    </div>
                @endif
            @endif
        </div>

        {{-- ===================== --}}
        {{-- CARD RESET PASSWORD  --}}
        {{-- ===================== --}}
        {{-- 
            Card terpisah khusus untuk aksi reset password:
            - mt-4 sm:mt-6 → jarak atas dari card detail sebelumnya.
            - bg-white, rounded-2xl, shadow, p-4 sm:p-6 → tampilan card seragam dengan atas.
        --}}
        <div class="mt-4 sm:mt-6 bg-white rounded-2xl shadow p-4 sm:p-6">
            {{-- Judul kecil card reset password --}}
            <h2 class="text-sm sm:text-base font-semibold mb-2">Reset Password Akun</h2>

            {{-- 
                Penjelasan singkat:
                - Menjelaskan bahwa setiap reset saat ini akan mengatur password ke "12345678".
                - text-[11px] sm:text-xs → ukuran font kecil, cocok untuk teks informatif.
            --}}
            <p class="text-[11px] sm:text-xs text-[#7C7C7C] mb-3">
                Saat ini, setiap reset akan mengatur password ke
                <span class="font-semibold">12345678</span>.
            </p>

            {{-- 
                Form untuk memicu proses reset password:
                - action → route('dinkes.data-master.reset', ['user' => $data->id, 'tab' => $tab])
                  diarahkan ke DataMasterController@resetPassword.
                - method="POST" → operasi perubahan (mutasi data) di server.
                - class="space-y-3" → jarak vertikal antar elemen form (kalau nanti ditambah).
            --}}
            <form action="{{ route('dinkes.data-master.reset', ['user' => $data->id, 'tab' => $tab]) }}" method="POST"
                class="space-y-3">
                {{-- Token CSRF untuk keamanan (anti CSRF attack). --}}
                @csrf

                {{-- 
                    Tombol submit reset:
                    - inline-flex items-center justify-center → isi tombol sejajar dan dipusatkan.
                    - px-4 py-2 → padding dalam tombol.
                    - rounded-full → tombol bulat penuh.
                    - text-xs sm:text-sm → ukuran tulisan kecil.
                    - font-medium → sedikit tebal.
                    - bg-[#1677FF] text-white → warna tombol biru dengan teks putih (aksi penting).
                    - hover:bg-[#125FCC] → efek hover sedikit lebih gelap.
                    - transition → animasi perubahan warna lebih halus.
                --}}
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#1677FF] text-white hover:bg-[#125FCC] transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>

</html>
