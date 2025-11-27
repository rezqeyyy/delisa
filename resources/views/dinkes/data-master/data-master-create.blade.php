<!DOCTYPE html>
<html lang="id">
    {{-- 
        lang="id" → Menandakan bahwa bahasa utama isi halaman ini adalah Bahasa Indonesia.
        Ini membantu browser dan alat bantu (screen reader) memahami konten.
    --}}
<head>
    {{-- 
        Menentukan karakter encoding dokumen.
        UTF-8 mendukung huruf latin, simbol, serta karakter khusus lain.
    --}}
    <meta charset="UTF-8" />

    {{-- 
        Mengatur viewport agar tampilan responsif di berbagai ukuran layar.
        - width=device-width → lebar konten mengikuti lebar layar perangkat.
        - initial-scale=1.0 → level zoom awal halaman = 100%.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- 
        Judul halaman yang tampil di tab browser.
        Di sini menggambarkan bahwa halaman ini milik modul DINKES dan untuk "Tambah Akun".
    --}}
    <title>DINKES – Tambah Akun</title>

    {{-- 
        Memuat asset yang di-bundling oleh Vite.
        - resources/css/app.css                    → stylesheet utama (Tailwind + custom).
        - resources/js/app.js                      → skrip JS global proyek.
        - resources/js/dinkes/sidebar-toggle.js    → skrip untuk buka/tutup sidebar Dinkes.
        - resources/js/dinkes/data-master-form.js  → skrip khusus halaman data master (misalnya interaksi form).
        Sesuai guideline: tidak pakai inline script, semua logika JS masuk ke file resource.
    --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dinkes/sidebar-toggle.js',
        'resources/js/dinkes/data-master-form.js',
    ])
</head>

{{-- 
    <body> adalah wadah utama seluruh isi tampilan.
    class:
    - bg-[#F5F5F5] → warna latar abu-abu muda.
    - font-[Poppins] → font utama halaman memakai Poppins.
    - text-[#000000cc] → warna teks hitam dengan opacity ~80% (lebih lembut).
--}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">

    {{-- 
        <div class="flex min-h-screen">
        - flex → mengaktifkan Flexbox (untuk tata letak horizontal / vertikal).
        - min-h-screen → tinggi minimal sama dengan tinggi viewport (100vh).
        Di dalamnya akan ada sidebar dan area utama (main).
    --}}
    <div class="flex min-h-screen">
        {{-- 
            Komponen Blade <x-dinkes.sidebar /> menampilkan sidebar khusus modul Dinkes.
            Keuntungannya: kode sidebar tidak perlu diulang di setiap view, cukup panggil komponen.
        --}}
        <x-dinkes.sidebar />

        {{-- 
            <main> adalah konten utama di sebelah kanan sidebar.
            class:
            - ml-0 → margin-left 0px di layar kecil (sidebar biasanya di atas / collapsible).
            - md:ml-[260px] → di layar ≥ md, beri margin kiri 260px agar tidak tertutup sidebar.
            - flex-1 → lebar main mengisi sisa ruang yang tersedia.
            - p-4 sm:p-6 lg:p-8 → padding isi main, semakin besar di layar lebih lebar.
            - space-y-6 → jarak vertikal antar elemen langsung di dalam main (header, alert, tabs, form).
        --}}
        <main class="ml-0 md:ml-[260px] flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- ===================== --}}
            {{-- BAGIAN HEADER HALAMAN --}}
            {{-- ===================== --}}
            <header>
                {{-- 
                    Judul besar halaman:
                    - text-[22px] sm:text-[28px] → ukuran font berbeda sesuai ukuran layar (responsif).
                    - font-bold → teks tebal.
                    - leading-tight → jarak antar baris rapat.
                    - text-[#000] → warna hitam solid.
                --}}
                <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">
                    List Daftar Akun
                </h1>

                {{-- 
                    Subjudul yang menjelaskan fungsi halaman (mengelola menu akun).
                    - text-xs sm:text-sm → font kecil, sedikit lebih besar di layar lebar.
                    - text-[#7C7C7C] → warna abu-abu agar tidak terlalu menonjol.
                --}}
                <p class="text-xs sm:text-sm text-[#7C7C7C]">
                    Manage the Details of Your Menu Account
                </p>
            </header>

            {{-- ====================== --}}
            {{-- FLASH MESSAGE & ERROR --}}
            {{-- ====================== --}}

            {{-- 
                Jika session('ok') ada → tampilkan kotak notifikasi sukses.
                Biasanya di-set di controller dengan: return back()->with('ok', 'Pesan...');
            --}}
            @if (session('ok'))
                {{-- 
                    Div untuk alert sukses:
                    - flash-alert → class marker (bisa dipakai JS untuk animasi / auto-hide).
                    - mb-3 → margin bawah 12px.
                    - flex items-start gap-3 → isi diatur secara horizontal, rata atas dengan jarak antar elemen.
                    - rounded-lg → sudut tumpul.
                    - border border-green-300 → garis pinggir hijau.
                    - bg-green-50 → latar hijau sangat muda.
                    - p-3 → padding 12px di semua sisi.
                    - text-sm → font kecil.
                    - text-green-700 → teks hijau lebih gelap.
                    - transition-opacity duration-500 → efek transparansi halus jika diubah via JS.
                    attribute:
                    - role="alert" → memberi tahu screen reader bahwa ini pesan penting.
                    - data-flash → penanda custom bagi JavaScript untuk flash message.
                    - data-timeout="3500" → durasi dalam ms sebelum auto-hide (jika di-handle di JS).
                --}}
                <div
                    class="flash-alert mb-3 flex items-start gap-3 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700 transition-opacity duration-500"
                    role="alert" data-flash data-timeout="3500">
                    {{-- Ikon / emoji ceklis sebagai penanda sukses. --}}
                    <span class="mt-0.5">✅</span>

                    {{-- 
                        flex-1 → bagian ini diperluas untuk menampung teks.
                        Menampilkan isi pesan sukses dari session('ok').
                    --}}
                    <div class="flex-1">{{ session('ok') }}</div>

                    {{-- 
                        Tombol ✕ untuk menutup alert secara manual.
                        class:
                        - flash-close → marker untuk JS agar bisa menambahkan event "klik untuk tutup".
                        - opacity-60 → agak transparan.
                        - hover:opacity-100 → menjadi lebih jelas saat di-hover.
                    --}}
                    <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                </div>
            @endif

            {{-- 
                Jika ada error validasi (misalnya dari $request->validate di controller),
                maka $errors->any() akan bernilai true dan pesan error ditampilkan di bawah.
            --}}
            @if ($errors->any())
                {{-- 
                    Div untuk alert error (mirip alert sukses tapi warna merah).
                    - border border-red-300, bg-red-50, text-red-700 → tema merah untuk error.
                    - data-timeout="4000" → bisa di-auto-hide setelah 4 detik (jika di-handle di JS).
                --}}
                <div
                    class="flash-alert mb-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 transition-opacity duration-500"
                    role="alert" data-flash data-timeout="4000">
                    {{-- 
                        flex items-start gap-3 → icon, daftar error, dan tombol close disusun horizontal.
                    --}}
                    <div class="flex items-start gap-3">
                        {{-- Emoji peringatan. --}}
                        <span class="mt-0.5">⚠️</span>

                        {{-- 
                            <ul> menampung semua error dalam bentuk list bullet.
                            - list-disc → bullet (titik).
                            - pl-5 → padding kiri agar bullet tidak terlalu mepet.
                        --}}
                        <ul class="list-disc pl-5">
                            {{-- 
                                Loop semua error validasi dan tampilkan satu per baris.
                                $errors->all() mengembalikan array pesan error.
                            --}}
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>

                        {{-- Tombol untuk menutup alert error. --}}
                        <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                    </div>
                </div>
            @endif

            {{-- ============ --}}
            {{-- BAGIAN TABS --}}
            {{-- ============ --}}

            {{-- 
                Section ini menampilkan tab untuk berpindah jenis data master:
                - Bidan PKM
                - Rumah Sakit
                - Puskesmas
                class:
                - flex flex-wrap → tombol tab bisa turun ke baris berikutnya jika layar sempit.
                - items-center → vertikal rata tengah.
                - gap-2 sm:gap-3 → jarak antar tab.
            --}}
            <section class="flex flex-wrap items-center gap-2 sm:gap-3">
                {{-- 
                    Tab "Bidan PKM".
                    - href menuju route('dinkes.data-master', ['tab' => 'bidan']).
                    - class memakai ternary Blade:
                      Jika $tab === 'bidan' → tab aktif (bg pink + text putih).
                      Jika tidak → tab nonaktif (bg putih + border abu).
                --}}
                <a href="{{ route('dinkes.data-master', ['tab' => 'bidan']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'bidan' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Bidan PKM
                </a>

                {{-- Tab "Rumah Sakit" dengan logika penentuan class yang sama. --}}
                <a href="{{ route('dinkes.data-master', ['tab' => 'rs']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'rs' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Rumah Sakit
                </a>

                {{-- Tab "Puskesmas" dengan logika class aktif/nonaktif yang sama. --}}
                <a href="{{ route('dinkes.data-master', ['tab' => 'puskesmas']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'puskesmas' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Puskesmas
                </a>
            </section>

            {{-- ================== --}}
            {{-- BAGIAN FORM UTAMA --}}
            {{-- ================== --}}

            {{-- 
                Section pembungkus semua form (RS / Puskesmas / Bidan).
                class:
                - bg-[#FFF0F5] → latar pink sangat muda.
                - p-4 sm:p-6 lg:p-8 → padding responsif.
                - rounded-2xl → sudut agak besar (tampilan card).
            --}}
            <section class="bg-[#FFF0F5] p-4 sm:p-6 lg:p-8 rounded-2xl">
                {{-- ===================================== --}}
                {{-- FORM: TAMBAH DATA RUMAH SAKIT (tab=rs) --}}
                {{-- ===================================== --}}

                {{-- 
                    Jika tab yang dipilih di URL adalah 'rs', maka tampilkan form RS.
                    Controller: DataMasterController@create mengirimkan variabel $tab.
                --}}
                @if ($tab === 'rs')
                    {{-- Judul form tambah RS. --}}
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">
                        Tambah Data Rumah Sakit
                    </h2>

                    {{-- 
                        Form tambah data RS:
                        - method="POST" → data dikirim sebagai POST request.
                        - action route('dinkes.data-master.store-rs') → diarahkan ke DataMasterController@storeRs.
                        class:
                        - grid grid-cols-1 md:grid-cols-2 → layout grid 1 kolom di HP, 2 kolom di layar lebih besar.
                        - gap-4 sm:gap-6 → jarak antar field.
                        - text-sm → font kecil.
                        attribute:
                        - data-rs-kelurahan-map='@json(...)' → atribut custom yang memuat mapping kecamatan → kelurahan
                          dalam format JSON, digunakan oleh JavaScript (data-master-form.js) untuk filter dropdown kelurahan.
                    --}}
                    <form method="POST" action="{{ route('dinkes.data-master.store-rs') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm"
                        data-rs-kelurahan-map='@json($rsKelurahanByKecamatan ?? [])'>
                        {{-- Token CSRF wajib untuk semua form POST di Laravel. --}}
                        @csrf

                        {{-- Field: Nama Lengkap PIC rumah sakit. --}}
                        <div>
                            <label>Nama Lengkap PIC</label>
                            {{-- 
                                input teks untuk nama PIC.
                                - name="pic_name" → diambil di controller sebagai $request->pic_name.
                                - value="{{ old('pic_name') }}" → jika validasi gagal, nilai lama dikembalikan.
                                - required → wajib diisi.
                                class:
                                - w-full → lebar penuh parent.
                                - border border-pink-400 → border warna pink.
                                - rounded-full → sudut oval.
                                - px-4 py-2 → padding horizontal & vertikal.
                                - mt-1 → jarak atas kecil dari label.
                            --}}
                            <input name="pic_name" value="{{ old('pic_name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Nomor Telepon PIC (boleh kosong). --}}
                        <div>
                            <label>Nomor Telepon PIC</label>
                            {{-- 
                                type="number" → input berupa angka.
                                - name="phone" → diambil di controller sebagai $request->phone.
                                - value="{{ old('phone') }}" → restore nilai lama jika ada error.
                                Tidak ada required → bersifat opsional.
                            --}}
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Email PIC (wajib, format email). --}}
                        <div>
                            <label>Email PIC</label>
                            {{-- 
                                type="email" → browser akan cek format email dasar.
                                - required → wajib diisi.
                            --}}
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Nama Rumah Sakit. --}}
                        <div>
                            <label>Nama Rumah Sakit</label>
                            {{-- 
                                Input teks nama RS, wajib diisi.
                                Akan disimpan di kolom rumah_sakits.nama.
                            --}}
                            <input name="nama" value="{{ old('nama') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Password akun RS. --}}
                        <div>
                            <label>Password</label>
                            {{-- 
                                type="password" → karakter tidak ditampilkan secara jelas.
                                placeholder → memberi info bahwa password minimal harus 8 karakter.
                                required → wajib diisi.
                            --}}
                            <input type="password" placeholder="password harus berisi 8 karakter" name="password"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- ==================== --}}
                        {{-- Dropdown Kecamatan --}}
                        {{-- ==================== --}}
                        <div>
                            <label>Kecamatan</label>
                            {{-- 
                                select name="kecamatan" → nilai akan dikirim sebagai kecamatan RS.
                                id="rsKecamatanCreate" → digunakan oleh JS untuk mendeteksi pilihan kecamatan.
                                required → wajib dipilih.
                                class:
                                - bg-white → latar putih.
                            --}}
                            <select name="kecamatan" id="rsKecamatanCreate" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                {{-- Placeholder option awal (tidak punya value). --}}
                                <option value="">-- Pilih Kecamatan --</option>
                                {{-- 
                                    Loop $rsKecamatanOptions (array kecamatan untuk RS).
                                    - $value → nilai yang akan disimpan ke DB.
                                    - $label → teks yang tampil di dropdown.
                                --}}
                                @foreach ($rsKecamatanOptions as $value => $label)
                                    {{-- 
                                        Jika old('kecamatan') sama dengan value, tandai option ini sebagai selected.
                                        Ini mempertahankan pilihan user ketika validasi form gagal.
                                    --}}
                                    <option value="{{ $value }}"
                                        {{ old('kecamatan') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ==================== --}}
                        {{-- Dropdown Kelurahan --}}
                        {{-- ==================== --}}
                        <div>
                            <label>Kelurahan</label>
                            {{-- 
                                select name="kelurahan" → nilai dikirim sebagai kelurahan RS.
                                id="rsKelurahanCreate" → digunakan JS untuk filter isi berdasar kecamatan.
                            --}}
                            <select name="kelurahan" id="rsKelurahanCreate" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                {{-- Placeholder awal kelurahan. --}}
                                <option value="">-- Pilih Kelurahan --</option>
                                {{-- 
                                    Loop $rsKelurahanOptions (array flat semua kelurahan).
                                    - $value → nama kelurahan (disimpan).
                                    - $label → label lengkap (mis: "Kelurahan (Kec. X)").
                                --}}
                                @foreach ($rsKelurahanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('kelurahan') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Alamat lengkap RS (textarea, bisa multiline). --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            {{-- 
                                textarea untuk menulis alamat / lokasi RS.
                                - rows="3" → tinggi awal 3 baris.
                                - isi default adalah old('lokasi') jika ada.
                                class:
                                - rounded-lg → sudut agak tumpul (tidak full pill).
                            --}}
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi') }}</textarea>
                        </div>

                        {{-- Tombol SUBMIT (span ke dua kolom di layout grid). --}}
                        <div class="md:col-span-2">
                            {{-- 
                                Button submit untuk mengirim form.
                                - w-full → lebar penuh.
                                - bg-[#B9257F] text-white → warna utama pink magenta dengan teks putih.
                                - rounded-full → sudut bulat penuh.
                                - py-3 → tinggi tombol cukup nyaman.
                                - font-semibold → teks tebal.
                            --}}
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>

                    {{-- ================================== --}}
                    {{-- FORM: TAMBAH DATA PUSKESMAS (tab=puskesmas) --}}
                    {{-- ================================== --}}
                @elseif ($tab === 'puskesmas')
                    {{-- Judul form tambah Puskesmas. --}}
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">
                        Tambah Data Puskesmas
                    </h2>

                    {{-- 
                        Form tambah Puskesmas:
                        - action → route('dinkes.data-master.store-puskesmas') → DataMasterController@storePuskesmas.
                    --}}
                    <form method="POST" action="{{ route('dinkes.data-master.store-puskesmas') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm">
                        @csrf

                        {{-- Field: Nama Lengkap PIC Puskesmas. --}}
                        <div>
                            <label>Nama Lengkap PIC</label>
                            <input name="pic_name" value="{{ old('pic_name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Nomor Telepon PIC (opsional). --}}
                        <div>
                            <label>Nomor Telepon PIC</label>
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Email PIC Puskesmas. --}}
                        <div>
                            <label>Email PIC</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Dropdown: Nama Puskesmas / Kecamatan. --}}
                        <div>
                            <label>Nama Puskesmas / Kecamatan</label>
                            {{-- 
                                select name="nama" → di controller akan divalidasi harus salah satu kecamatan Depok.
                                - value diisi dengan key dari $kecamatanOptions.
                            --}}
                            <select name="nama" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                {{-- 
                                    $kecamatanOptions diisi dari availableKecamatanForCreate(),
                                    artinya hanya kecamatan yang BELUM punya akun Puskesmas yang muncul di sini.
                                --}}
                                @foreach ($kecamatanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('nama') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Password akun Puskesmas. --}}
                        <div>
                            <label>Password</label>
                            <input type="password" name="password" placeholder="password harus berisi 8 karakter"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Alamat Puskesmas (textarea). --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi') }}</textarea>
                        </div>

                        {{-- Tombol submit form Puskesmas. --}}
                        <div class="md:col-span-2">
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>

                    {{-- ============================= --}}
                    {{-- FORM: TAMBAH AKUN BIDAN (default/tab=bidan) --}}
                    {{-- ============================= --}}
                @else
                    {{-- Judul form tambah akun Bidan PKM. --}}
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">
                        Tambah Akun Bidan PKM
                    </h2>

                    {{-- 
                        Form tambah Bidan:
                        - action → route('dinkes.data-master.store-bidan') → DataMasterController@storeBidan.
                    --}}
                    <form method="POST" action="{{ route('dinkes.data-master.store-bidan') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm">
                        @csrf

                        {{-- Field: Nama Bidan. --}}
                        <div>
                            <label>Nama</label>
                            <input name="name" value="{{ old('name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Nomor Izin Praktek Bidan. --}}
                        <div>
                            <label>Nomor Izin Praktek</label>
                            <input name="nomor_izin_praktek" type="number" value="{{ old('nomor_izin_praktek') }}"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Email Bidan. --}}
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: No Telepon Bidan (opsional). --}}
                        <div>
                            <label>No Telepon</label>
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Field: Password akun Bidan. --}}
                        <div>
                            <label>Password</label>
                            <input type="password" name="password" placeholder="password harus berisi 8 karakter"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Dropdown: Puskesmas tempat Bidan bernaung. --}}
                        <div>
                            <label>Pilih Puskesmas</label>
                            {{-- 
                                name="puskesmas_id" → di controller akan divalidasi harus exists di tabel puskesmas.
                            --}}
                            <select name="puskesmas_id" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                                {{-- Opsi kosong awal --}}
                                <option value="">-- Pilih --</option>

                                {{-- 
                                    Loop daftar Puskesmas aktif yang dikirim oleh controller.
                                    - $p->id → id puskesmas yang akan dikaitkan dengan bidan.
                                    - $p->nama_puskesmas → nama puskesmas yang ditampilkan.
                                --}}
                                @foreach ($puskesmasList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('puskesmas_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_puskesmas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Alamat Bidan (opsional). --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            {{-- 
                                Input teks 1 baris untuk alamat.
                                Jika ingin multiline, bisa diubah menjadi textarea (secara terencana nanti).
                            --}}
                            <input name="address" value="{{ old('address') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Tombol submit form Bidan. --}}
                        <div class="md:col-span-2">
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>
                @endif
            </section>
        </main>
    </div>
</body>

</html>
