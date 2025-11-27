<!DOCTYPE html>
<html lang="id">
    {{-- 
        lang="id" → Menandakan bahasa utama halaman ini adalah Bahasa Indonesia.
        Ini membantu browser dan alat bantu aksesibilitas (screen reader).
    --}}
<head>
    {{-- 
        Mengatur karakter encoding dokumen menjadi UTF-8.
        UTF-8 mampu merepresentasikan hampir semua karakter (huruf, simbol, emoji, dll).
    --}}
    <meta charset="UTF-8">

    {{-- 
        Mengatur viewport supaya halaman responsif di berbagai perangkat.
        - width=device-width → lebar konten mengikuti lebar layar perangkat.
        - initial-scale=1.0 → skala awal zoom halaman = 100%.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 
        Judul halaman yang tampil di tab browser.
        Di sini menjelaskan bahwa halaman ini untuk "Edit Akun" di modul DINKES.
    --}}
    <title>DINKES – Edit Akun</title>

    {{-- 
        Memuat asset yang di-bundling dengan Vite:
        - resources/css/app.css                   → CSS global (Tailwind + custom).
        - resources/js/app.js                     → JavaScript global proyek.
        - resources/js/dinkes/sidebar-toggle.js   → logika buka/tutup sidebar Dinkes.
        - resources/js/dinkes/data-master-form.js → logika form data master (misalnya filter kelurahan).
        Semua JS mengikuti guideline: tidak ada inline script, semua lewat resource JS.
    --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dinkes/sidebar-toggle.js',
        'resources/js/dinkes/data-master-form.js',
    ])
</head>

{{-- 
    <body> → elemen utama yang membungkus seluruh konten halaman.
    class:
    - bg-[#F5F5F5] → latar abu-abu muda.
    - font-[Poppins] → font utama menggunakan Poppins.
    - text-[#000000cc] → warna teks hitam dengan opacity sedikit (sekitar 80%).
--}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    @php
        /**
         * $currentNamaPuskesmas:
         * - Digunakan khusus di tab 'puskesmas' untuk menentukan nilai terpilih pada dropdown.
         * - Urutan prioritas:
         *   1. old('nama') → jika form sebelumnya gagal validasi, pakai input lama.
         *   2. $data->nama → jika field 'nama' ada (misal dari join tertentu).
         *   3. $data->nama_puskesmas → jika ada nama_puskesmas.
         *   4. $data->kecamatan → fallback terakhir, pakai kecamatan (misal saat create).
         *   5. null → jika semua di atas tidak ada.
         */
        $currentNamaPuskesmas = old('nama', $data->nama ?? ($data->nama_puskesmas ?? ($data->kecamatan ?? null)));
    @endphp

    {{-- 
        Container utama layout:
        - flex → menggunakan Flexbox.
        - min-h-screen → tinggi minimal 100% tinggi viewport (full layar).
        Di dalamnya ada sidebar di kiri dan main content di kanan.
    --}}
    <div class="flex min-h-screen">
        {{-- 
            Komponen Blade untuk sidebar Dinkes.
            Menampilkan menu navigasi khusus role Dinas Kesehatan.
            Kelebihan: kode sidebar bisa dipakai ulang di banyak halaman (DRY).
        --}}
        <x-dinkes.sidebar />

        {{-- 
            MAIN CONTENT
            - ml-0 → margin kiri 0 di layar kecil.
            - md:ml-[260px] → di layar ≥ md, margin kiri 260px supaya tidak tertutup sidebar.
            - flex-1 → mengisi sisa lebar yang tersedia.
            - p-4 sm:p-6 lg:p-8 → padding responsif (semakin besar di layar lebar).
            - space-y-6 → jarak vertikal antar elemen di dalam main (header, aler, card form).
        --}}
        <main class="ml-0 md:ml-[260px] flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- ===================== --}}
            {{-- HEADER HALAMAN EDIT  --}}
            {{-- ===================== --}}
            <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                {{-- 
                    Container kiri header: menampilkan judul dan deskripsi singkat.
                    - flex-col → susun vertikal di semua ukuran.
                    - sm:flex-row + sm:items-end + sm:justify-between di header utama, bukan di div ini.
                --}}
                <div>
                    {{-- 
                        Judul besar halaman.
                        - text-[22px] sm:text-[28px] → ukuran font berbeda berdasarkan breakpoint.
                        - font-bold → huruf tebal.
                        - leading-tight → jarak antar baris rapat.
                        - text-[#000] → warna hitam solid.
                        Isi:
                        - "Edit Akun" + nama tab dalam huruf awal kapital (ucfirst), misal: (Rs), (Bidan), (Puskesmas).
                    --}}
                    <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">
                        Edit Akun ({{ ucfirst($tab) }})
                    </h1>

                    {{-- 
                        Deskripsi singkat fungsi halaman.
                        - text-xs sm:text-sm → font kecil, sedikit membesar di layar lebar.
                        - text-[#7C7C7C] → warna abu-abu agar tidak mengalahkan judul.
                    --}}
                    <p class="text-xs sm:text-sm text-[#7C7C7C]">
                        Update detail informasi akun yang dipilih
                    </p>
                </div>

                {{-- 
                    Tombol/link untuk kembali ke halaman daftar Data Master:
                    - href menuju route('dinkes.data-master', ['tab' => $tab]) → agar kembali ke tab yang sama.
                    - inline-flex → membuat link terlihat seperti tombol (flex di dalam inline).
                    - items-center justify-center → ikon/text di tengah secara vertikal & horizontal.
                    - px-3 sm:px-4 py-2 → padding horizontal & vertikal responsif.
                    - rounded-full → sudut bulat penuh (pill).
                    - bg-white + border abu → tampilan tombol netral.
                    - text-xs sm:text-sm → ukuran teks responsif.
                --}}
                <a href="{{ route('dinkes.data-master', ['tab' => $tab]) }}"
                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">
                    ← Kembali ke Data Master
                </a>
            </header>

            {{-- ====================== --}}
            {{-- BAGIAN ERROR VALIDASI  --}}
            {{-- ====================== --}}
            @if ($errors->any())
                {{-- 
                    Kotak tampilan error validasi:
                    - mb-3 → margin bawah kecil.
                    - rounded-lg → sudut sedikit tumpul.
                    - border border-red-300 → garis merah muda.
                    - bg-red-50 → latar merah sangat muda.
                    - p-3 sm:p-4 → padding responsif.
                    - text-xs sm:text-sm → ukuran teks responsif.
                    - text-red-700 → warna teks merah lebih gelap.
                --}}
                <div class="mb-3 rounded-lg border border-red-300 bg-red-50 p-3 sm:p-4 text-xs sm:text-sm text-red-700">
                    {{-- 
                        <ul> daftar bullet berisi semua pesan error.
                        - list-disc → bullet point.
                        - pl-5 → padding kiri agar bullet tidak terlalu mepet tepi.
                    --}}
                    <ul class="list-disc pl-5">
                        {{-- Loop setiap pesan error dan tampilkan sebagai <li>. --}}
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- =================== --}}
            {{-- CARD FORM EDIT AKUN --}}
            {{-- =================== --}}
            <section class="bg-[#FFF0F5] rounded-2xl p-4 sm:p-6 lg:p-8">
                {{-- 
                    Form utama untuk mengedit akun.
                    - method="POST" → walau update, tetap memakai POST karena HTML tidak mendukung PUT langsung.
                    - @method('PUT') → Laravel akan menganggapnya sebagai HTTP PUT.
                    - action route('dinkes.data-master.update', ['user' => $data->id, 'tab' => $tab])
                      → mengarah ke DataMasterController@update untuk user tertentu dan tab tertentu.
                    class:
                    - grid grid-cols-1 md:grid-cols-2 → field disusun 1 kolom di HP, 2 kolom di layar lebar.
                    - gap-4 sm:gap-6 → jarak antar field.
                    - text-sm → ukuran teks kecil.
                    attribute tambahan:
                    - jika tab = 'rs', maka ditambah data-rs-kelurahan-map berisi JSON mapping kecamatan → kelurahan
                      yang digunakan JS (data-master-form.js) untuk mengisi dropdown kelurahan dinamis.
                --}}
                <form method="POST"
                    action="{{ route('dinkes.data-master.update', ['user' => $data->id, 'tab' => $tab]) }}"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm"
                    @if ($tab === 'rs') data-rs-kelurahan-map='@json($rsKelurahanByKecamatan ?? [])' @endif>
                    {{-- Token CSRF untuk keamanan form (wajib di semua form POST/PUT/DELETE). --}}
                    @csrf

                    {{-- 
                        @method('PUT') → Menginformasikan ke Laravel bahwa request ini adalah PUT,
                        walaupun di HTML hanya bisa POST. Dipakai untuk operasi update sesuai RESTful practice.
                    --}}
                    @method('PUT')

                    {{-- ================= --}}
                    {{-- COMMON FIELDS    --}}
                    {{-- (dipakai semua tab) --}}
                    {{-- ================= --}}

                    {{-- Field: Nama (nama user/PIC). --}}
                    <div>
                        <label>Nama</label>
                        {{-- 
                            Input teks untuk nama.
                            - name="name" → field akan dikirim sebagai 'name'.
                            - value → prioritas:
                              1. old('name') jika form sebelumnya gagal.
                              2. $data->name (nilai dari DB).
                            - required → wajib diisi.
                        --}}
                        <input name="name" value="{{ old('name', $data->name) }}" required
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>

                    {{-- Field: Email user/PIC. --}}
                    <div>
                        <label>Email</label>
                        {{-- 
                            type="email" → browser akan memvalidasi format email secara dasar.
                            - value mengikuti old('email') atau $data->email.
                            - required → wajib diisi.
                        --}}
                        <input type="email" name="email" value="{{ old('email', $data->email) }}" required
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>

                    {{-- Field: Nomor Telepon (opsional). --}}
                    <div>
                        <label>No Telepon</label>
                        {{-- 
                            type="number" → hanya angka (di level UI).
                            - value mengikuti old('phone') atau $data->phone.
                            Tidak diberikan required → boleh kosong.
                        --}}
                        <input name="phone" type="number" value="{{ old('phone', $data->phone) }}"
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>

                    {{-- ===================================== --}}
                    {{-- CABANG UNTUK TAB = 'rs' (Rumah Sakit) --}}
                    {{-- ===================================== --}}
                    @if ($tab === 'rs')
                        {{-- ====== RS ====== --}}

                        {{-- Field: Nama Rumah Sakit. --}}
                        <div>
                            <label>Nama Rumah Sakit</label>
                            {{-- 
                                Input nama RS yang disimpan di tabel rumah_sakits.nama.
                                - value → old('nama') atau $data->nama.
                                - required → wajib diisi.
                            --}}
                            <input name="nama" value="{{ old('nama', $data->nama) }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- ===================== --}}
                        {{-- Dropdown: Kecamatan --}}
                        {{-- ===================== --}}
                        <div>
                            <label>Kecamatan</label>
                            {{-- 
                                Dropdown kecamatan RS.
                                - name="kecamatan" → akan divalidasi sebagai salah satu kecamatan Depok.
                                - id="rsKecamatanEdit" → digunakan JS untuk meng-update daftar kelurahan.
                                - required → wajib dipilih.
                                class:
                                - bg-white → latar putih.
                            --}}
                            <select name="kecamatan" id="rsKecamatanEdit" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                {{-- Placeholder awal untuk memaksa user memilih. --}}
                                <option value="">-- Pilih Kecamatan --</option>
                                {{-- 
                                    Loop semua opsi kecamatan dari $kecamatanOptions.
                                    - $value → nilai yang disimpan (nama kecamatan).
                                    - $label → label yang ditampilkan (mis: "Kecamatan Beji").
                                --}}
                                @foreach ($kecamatanOptions as $value => $label)
                                    {{-- 
                                        old('kecamatan', $data->kecamatan) → 
                                        jika ada input lama pakai itu, kalau tidak pakai data dari DB.
                                        Jika sama dengan $value, option ini diberi atribut selected.
                                    --}}
                                    <option value="{{ $value }}"
                                        {{ old('kecamatan', $data->kecamatan) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ===================== --}}
                        {{-- Dropdown: Kelurahan --}}
                        {{-- ===================== --}}
                        <div>
                            <label>Kelurahan</label>
                            {{-- 
                                Dropdown kelurahan RS.
                                - name="kelurahan" → nilai disimpan ke kolom kelurahan.
                                - id="rsKelurahanEdit" → dipakai JS untuk filter berdasarkan kecamatan.
                                - required → wajib diisi.
                            --}}
                            <select name="kelurahan" id="rsKelurahanEdit" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                {{-- Option placeholder awal. --}}
                                <option value="">-- Pilih Kelurahan --</option>
                                {{-- 
                                    Loop semua opsi kelurahan dari $kelurahanOptions.
                                    - $value → nama kelurahan yang disimpan.
                                    - $label → label extended (mis: "Beji Timur (Kec. Beji)").
                                --}}
                                @foreach ($kelurahanOptions as $value => $label)
                                    {{-- 
                                        old('kelurahan', $data->kelurahan) → 
                                        menggunakan input lama jika ada, jika tidak pakai nilai dari DB.
                                    --}}
                                    <option value="{{ $value }}"
                                        {{ old('kelurahan', $data->kelurahan) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Alamat RS (textarea). --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            {{-- 
                                textarea untuk lokasi/alamat RS.
                                - rows="3" → tinggi awal tiga baris.
                                - value diisi dengan old('lokasi') atau $data->lokasi.
                                class:
                                - rounded-lg → sudut kotak tapi agak tumpul.
                            --}}
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi', $data->lokasi) }}</textarea>
                        </div>

                    {{-- ======================================== --}}
                    {{-- CABANG UNTUK TAB = 'puskesmas'          --}}
                    {{-- ======================================== --}}
                    @elseif ($tab === 'puskesmas')
                        {{-- ====== Puskesmas ====== --}}

                        @php
                            /**
                             * $selectedNama:
                             * - Dipakai untuk menentukan option mana yang harus dalam keadaan "selected".
                             * - Menggunakan:
                             *   1. old('nama') → prioritas ketika validasi gagal.
                             *   2. $currentNamaPuskesmas → hasil perhitungan di atas (nama/nama_puskesmas/kecamatan).
                             */
                            $selectedNama = old('nama', $currentNamaPuskesmas);
                        @endphp

                        {{-- Dropdown: Nama Puskesmas / Kecamatan. --}}
                        <div>
                            <label>Nama Puskesmas / Kecamatan</label>
                            {{-- 
                                select name="nama" → nilainya akan divalidasi di controller harus salah satu kecamatan Depok.
                                - required → harus dipilih.
                            --}}
                            <select name="nama" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                {{-- 
                                    Loop opsi kecamatan yang masih tersedia / diizinkan.
                                    - $value → nama kecamatan (yang disimpan).
                                    - $label → label tampilan (mis: "Kecamatan Beji").
                                --}}
                                @foreach ($kecamatanOptions as $value => $label)
                                    {{-- 
                                        Jika $selectedNama sama dengan $value, option menjadi selected.
                                        Ini menjaga pilihan user tetap konsisten setelah reload/error.
                                    --}}
                                    <option value="{{ $value }}" {{ $selectedNama === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Alamat Puskesmas. --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            {{-- 
                                textarea lokasi Puskesmas.
                                - value mengambil old('lokasi') atau $data->lokasi.
                            --}}
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi', $data->lokasi) }}</textarea>
                        </div>

                    {{-- ======================================== --}}
                    {{-- CABANG UNTUK TAB LAIN (BIDAN)           --}}
                    {{-- ======================================== --}}
                    @else
                        {{-- ====== BIDAN ====== --}}

                        {{-- Field: Nomor Izin Praktek Bidan. --}}
                        <div>
                            <label>Nomor Izin Praktek</label>
                            {{-- 
                                Input teks untuk nomor izin praktek.
                                - name="nomor_izin_praktek".
                                - value → old('nomor_izin_praktek') atau $data->nomor_izin_praktek.
                                - required → wajib diisi.
                            --}}
                            <input name="nomor_izin_praktek"
                                value="{{ old('nomor_izin_praktek', $data->nomor_izin_praktek) }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Dropdown: Puskesmas tempat Bidan terhubung. --}}
                        <div>
                            <label>Puskesmas</label>
                            {{-- 
                                select name="puskesmas_id" → akan divalidasi harus exists di tabel puskesmas.
                                - required → wajib memilih salah satu Puskesmas.
                            --}}
                            <select name="puskesmas_id" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Puskesmas --</option>
                                {{-- 
                                    Loop daftar Puskesmas yang dikirim dari controller.
                                    - $p->id → nilai puskesmas_id yang disimpan.
                                    - $p->nama_puskesmas → label yang ditampilkan di dropdown.
                                --}}
                                @foreach ($puskesmasList as $p)
                                    {{-- 
                                        old('puskesmas_id', $data->puskesmas_id) → 
                                        memprioritaskan input lama jika ada, kalau tidak pakai data existing.
                                        Jika sama dengan $p->id, maka option ini selected.
                                    --}}
                                    <option value="{{ $p->id }}"
                                        {{ old('puskesmas_id', $data->puskesmas_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_puskesmas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Field: Alamat Bidan. --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            {{-- 
                                Input teks alamat.
                                - name="address" → akan diupdate di kolom users.address.
                                - value → old('address') atau $data->address.
                            --}}
                            <input name="address" value="{{ old('address', $data->address) }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                    @endif

                    {{-- ====================== --}}
                    {{-- TOMBOL SIMPAN PERUBAHAN --}}
                    {{-- ====================== --}}

                    {{-- 
                        Baris terakhir tombol submit:
                        - md:col-span-2 → di layout 2 kolom, tombol span ke seluruh lebar (dua kolom).
                    --}}
                    <div class="md:col-span-2">
                        {{-- 
                            Tombol untuk menyimpan perubahan:
                            - w-full → lebar penuh card.
                            - bg-[#B9257F] → warna latar pink-magenta (warna utama Dinkes).
                            - text-white → teks putih.
                            - rounded-full → sudut bulat.
                            - py-3 → tinggi tombol (padding vertikal).
                            - font-semibold → teks agak tebal.
                            Teks "SIMPAN PERUBAHAN" menjelaskan fungsinya dengan jelas.
                        --}}
                        <button class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>

</html>
