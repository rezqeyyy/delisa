<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Tambah Pasien Nifas</title>

    {{-- 
        Memuat asset utama:
        - resources/css/app.css → Tailwind + style global
        - resources/js/app.js   → bootstrap JS (Alpine, dsb)
        - resources/js/dropdown.js → behavior dropdown profile di navbar
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- 
        Wrapper utama halaman. 
        x-data="{ openSidebar: false }" → state Alpine untuk toggle sidebar di mobile.
    --}}
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        {{-- Komponen sidebar untuk role Bidan (menu navigasi kiri). --}}
        <x-bidan.sidebar />

        {{-- 
            MAIN CONTENT:
            - flex-1 → area ini mengambil sisa lebar selain sidebar.
            - xl:ml-[260px] → memberi margin kiri saat layar besar, disesuaikan lebar sidebar.
        --}}
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- 
                HEADER ATAS FORM
                - Tombol back ← ke halaman list skrining: route('bidan.skrining')
                  (sesuai file asli kamu: form ini dibuka dari halaman skrining).
            --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="mb-6 flex items-center">
                    {{-- 
                        Link kembali ke halaman list skrining bidan.
                        Lari ke: route('bidan.skrining') → SkriningController@index (role Bidan).
                    --}}
                    <a href="{{ route('bidan.skrining') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="ml-3 text-3xl font-bold text-gray-800">
                        Tambah Data Detail Pasien
                    </h1>
                </div>
            </div>

            {{-- 
                FLASH MESSAGE ERROR (dari PasienNifasController@store → redirect()->back()->with('error', ...))
                - Ditampilkan jika proses simpan gagal (misal: role tidak valid).
            --}}
            @if (session('error'))
                <div class="flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-800">
                    <span class="mt-0.5">
                        {{-- Icon warning kecil --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v5" />
                            <path d="M12 16h.01" />
                        </svg>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- 
                FLASH MESSAGE SUCCESS (dari PasienNifasController@store → redirect()->route('bidan.pasien-nifas')->with('success', ...))
                - Sebenarnya pesan sukses lebih sering ditampilkan di halaman index,
                  tapi di sini tetap disiapkan jika suatu saat kamu redirect balik ke create.
            --}}
            @if (session('success'))
                <div class="flex items-start gap-2 rounded-xl border border-green-100 bg-green-50 px-3 py-2 text-xs sm:text-sm text-green-800">
                    <span class="mt-0.5">
                        {{-- Icon checklist kecil --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- 
                SECTION FORM TAMBAH PASIEN NIFAS
                - Inilah bagian utama untuk mengisi detail pasien nifas.
            --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] p-3 sm:p-5 space-y-4">
                <div class="border-b border-[#F0F0F0] pb-3 mb-2">
                    <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">
                        Form Tambah Pasien Nifas
                    </h2>
                    <p class="text-xs text-[#7C7C7C] mt-1">
                        Isi identitas pasien nifas. Sistem akan mengecek NIK:
                        jika NIK sudah terdaftar, data pasien akan diambil dari tabel <code>pasiens</code> & <code>users</code>.
                    </p>
                </div>

                {{-- 
                    FORM:
                    - method="POST" → mengirim data ke server.
                    - action route('bidan.pasien-nifas.store') 
                      → lari ke PasienNifasController@store.
                    - @csrf → token proteksi CSRF (wajib di form POST Laravel).
                    Alur setelah submit:
                    1) Validasi field di controller.
                    2) Cari pasien berdasarkan NIK:
                       - Jika ada → update data/phone.
                       - Jika belum ada → buat user + pasien baru.
                    3) Buat record nifas di tabel pasien_nifas_bidan.
                    4) Redirect ke route('bidan.pasien-nifas') (halaman index nifas).
                --}}
                <form id="formTambahPasien"
                      method="POST"
                      action="{{ route('bidan.pasien-nifas.store') }}"
                      class="space-y-5">
                    @csrf

                    {{-- GRID KOLOM 2: Nama, NIK, No Telepon --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            {{-- Nama Pasien: dipakai untuk kolom users.name dan pasiens.nama (implisit via relasi) --}}
                            <label for="nama_pasien" class="block text-xs font-medium text-[#666666]">
                                Nama Pasien <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="nama_pasien"
                                name="nama_pasien"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Nama lengkap pasien"
                                value="{{ old('nama_pasien') }}"
                                required>
                            @error('nama_pasien')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            {{-- NIK: kunci utama untuk mengecek apakah pasien sudah pernah dibuat --}}
                            <label for="nik" class="block text-xs font-medium text-[#666666]">
                                NIK <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="nik"
                                name="nik"
                                maxlength="16"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="16 digit NIK"
                                value="{{ old('nik') }}"
                                required>
                            @error('nik')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            {{-- No Telepon pasien: disimpan ke kolom users.phone --}}
                            <label for="no_telepon" class="block text-xs font-medium text-[#666666]">
                                Nomor Telepon <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="no_telepon"
                                name="no_telepon"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Nomor HP aktif"
                                value="{{ old('no_telepon') }}"
                                required>
                            @error('no_telepon')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- GRID KOLOM 2: Alamat (Provinsi, Kota/Kabupaten) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            {{-- Provinsi domisili pasien (disimpan ke kolom PProvinsi di tabel pasiens) --}}
                            <label for="provinsi" class="block text-xs font-medium text-[#666666]">
                                Provinsi <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="provinsi"
                                name="provinsi"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Contoh: Jawa Barat"
                                value="{{ old('provinsi') }}"
                                required>
                            @error('provinsi')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            {{-- Kota/Kabupaten domisili pasien (disimpan ke PKabupaten) --}}
                            <label for="kota" class="block text-xs font-medium text-[#666666]">
                                Kota/Kabupaten <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="kota"
                                name="kota"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Contoh: Kota Depok"
                                value="{{ old('kota') }}"
                                required>
                            @error('kota')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- GRID KOLOM 2: Kecamatan, Kelurahan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            {{-- Kecamatan (disimpan ke PKecamatan) --}}
                            <label for="kecamatan" class="block text-xs font-medium text-[#666666]">
                                Kecamatan <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="kecamatan"
                                name="kecamatan"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Contoh: Beji"
                                value="{{ old('kecamatan') }}"
                                required>
                            @error('kecamatan')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            {{-- Kelurahan (disimpan ke PWilayah) --}}
                            <label for="kelurahan" class="block text-xs font-medium text-[#666666]">
                                Kelurahan <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                id="kelurahan"
                                name="kelurahan"
                                class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Contoh: Beji Timur"
                                value="{{ old('kelurahan') }}"
                                required>
                            @error('kelurahan')
                                <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Kolom Domisili detail (alamat lengkap, keterangan RT/RW, dsb.) --}}
                    <div class="space-y-1.5">
                        <label for="domisili" class="block text-xs font-medium text-[#666666]">
                            Alamat Domisili Lengkap <span class="text-pink-600">*</span>
                        </label>
                        <textarea
                            id="domisili"
                            name="domisili"
                            rows="3"
                            class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm focus:border-pink-500 focus:ring-pink-500"
                            placeholder="Tulis alamat lengkap domisili pasien (jalan, RT/RW, dsb.)"
                            required>{{ old('domisili') }}</textarea>
                        @error('domisili')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FOOTER FORM: tombol kembali & tombol submit --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-[#F0F0F0]">
                        {{-- 
                            Tombol kembali ke halaman index pasien nifas.
                            Lari ke: route('bidan.pasien-nifas') → PasienNifasController@index.
                        --}}
                        <a href="{{ route('bidan.pasien-nifas') }}"
                           class="inline-flex items-center justify-center px-4 h-9 rounded-full border border-[#D9D9D9] text-xs sm:text-sm font-semibold text-[#1D1D1D] bg-white hover:bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                            <span>Kembali</span>
                        </a>

                        {{-- 
                            Tombol submit:
                            - Mengirim form ke route('bidan.pasien-nifas.store').
                            - Setelah berhasil, controller redirect kembali ke index dengan flash message 'success'.
                        --}}
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 h-9 rounded-full bg-[#E91E8C] text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-[#C2185B]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                            <span>Tambah Data Pasien</span>
                        </button>
                    </div>
                </form>
            </section>

            {{-- FOOTER HALAMAN --}}
            <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    {{-- 
        SCRIPT KECIL: membatasi input NIK dan No Telepon hanya angka.
        Catatan: idealnya ini dipindah ke file JS terpisah (misal: resources/js/bidan/pasien-nifas-create.js)
        lalu dimasukkan ke @vite, agar bebas inline script.
    --}}
    <script>
        const nikInput = document.getElementById('nik');
        if (nikInput) {
            nikInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        const telpInput = document.getElementById('no_telepon');
        if (telpInput) {
            telpInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>
