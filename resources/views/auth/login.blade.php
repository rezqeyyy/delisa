<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Deklarasi karakter yang dipakai (UTF-8 standar web modern) -->
    <meta charset="UTF-8">

    <!-- Supaya tampilan responsive mengikuti lebar device (HP, tablet, desktop) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Judul halaman, tampil di tab browser -->
    <title>Login Petugas - DeLISA</title>

    <!-- 
        Memuat asset via Vite:
        - resources/css/app.css  → Tailwind & styling global
        - resources/js/app.js    → bootstrap JS global (misal Alpine, dsb jika ada)
        - resources/js/petugas/modal-role-petugas.js → JS khusus modal pemilihan role
    -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/petugas/modal-role-petugas.js'])

</head>

<!-- Background abu-abu muda untuk membedakan card putih -->
<body class="bg-gray-100">
    <!-- 
        Wrapper global:
        - min-h-screen: tinggi minimum 100vh (full screen)
        - flex + items-center + justify-center: card berada di tengah layar
        - p-4: padding sekitar di layar kecil
    -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- 
            Card utama:
            - max-w-5xl: lebar maksimal 5xl
            - flex: 2 kolom (gambar + form)
            - bg-white: card putih
            - shadow-2xl: bayangan besar
            - overflow-hidden: sudut rounded tidak bocor
            - rounded-2xl: sudut membulat besar
        -->
        <div class="w-full max-w-5xl flex bg-white shadow-2xl overflow-hidden rounded-2xl">

            <!-- 
                Kolom kiri: gambar dekorasi.
                - hidden lg:block: disembunyikan di layar kecil, tampil mulai breakpoint lg
                - lg:w-1/2: di layar besar, lebar 50%
            -->
            <div class="hidden lg:block lg:w-1/2">
                <!-- 
                    Gambar background:
                    - object-cover: gambar mengisi kontainer dan crop bila perlu
                    - h-full w-full: penuh lebar & tinggi kolom
                    - asset(): ambil file dari public/images
                -->
                <img
                    class="h-full w-full object-cover"
                    src="{{ asset('images/gradient-bg v2.png') }}"
                    alt="DeLISA Panel"
                >
            </div>

            <!-- 
                Kolom kanan: form login petugas.
                - w-full: di mobile ambil full width
                - lg:w-1/2: di layar besar setengah lebar
                - flex + items-center + justify-center: konten di tengah kolom
                - p-8 sm:p-12: padding berbeda sesuai breakpoint
            -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
                <!-- max-w-md: batasi lebar form agar tidak terlalu lebar -->
                <div class="w-full max-w-md">

                    <!-- 
                        Dekorasi: bintang besar warna pink
                        - text-7xl: font super besar
                        - font-bold: tebal
                        - text-[#D91A8B]: warna utama DeLISA
                    -->
                    <p class="text-7xl font-bold text-[#D91A8B]">*</p>

                    <!-- Judul utama form -->
                    <h1 class="text-3xl font-bold text-gray-900 mt-4">
                        Login Petugas Delisa
                    </h1>

                    <!-- Subjudul berupa instruksi singkat -->
                    <p class="text-gray-600 mt-1">
                        Masukan Email dan Password
                    </p>

                    <!-- 
                        BLOK ERROR VALIDATION:
                        - $errors->any(): true jika ada error validasi dari backend
                        - Ditampilkan sebagai box merah di atas form
                    -->
                    @if ($errors->any())
                        <div class="mt-4 p-4 rounded-md bg-red-50 border border-red-200 text-red-700">
                            @foreach ($errors->all() as $error)
                                <p class="text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- 
                        FORM LOGIN PETUGAS
                        - action: route('login') → AuthenticatedSessionController@store (login petugas)
                        - method: POST (aman untuk kredensial)
                        - class: spacing antar field pakai space-y-5
                    -->
                    <form
                        action="{{ route('login') }}"
                        method="POST"
                        class="mt-8 space-y-5"
                    >
                        <!-- CSRF token Laravel untuk proteksi form POST -->
                        @csrf

                        <!-- =========================
                             FIELD: EMAIL
                             ========================= -->
                        <div>
                            <!-- Label email -->
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email
                            </label>

                            <!-- Input email 
                                 - type="email": HTML akan validasi format email dasar
                                 - placeholder: contoh format email
                            -->
                            <input
                                type="email"
                                name="email"
                                id="email"
                                placeholder="akun@gmail.com"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300
                                       rounded-md shadow-sm placeholder-gray-400
                                       focus:outline-none focus:ring-[#D91A8B]
                                       focus:border-[#D91A8B]"
                            >
                        </div>

                        <!-- =========================
                             FIELD: PASSWORD
                             ========================= -->
                        <div>
                            <!-- Label password -->
                            <label
                                for="password"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Password
                            </label>

                            <!-- Input password 
                                 - type="password": teks disembunyikan
                                 - placeholder: teks panduan
                            -->
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Password Anda"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300
                                       rounded-md shadow-sm placeholder-gray-400
                                       focus:outline-none focus:ring-[#D91A8B]
                                       focus:border-[#D91A8B]"
                            >
                        </div>

                        <!-- =========================
                             TOMBOL LOGIN
                             ========================= -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent
                                       rounded-md shadow-lg text-sm font-medium text-white
                                       bg-[#D91A8B] hover:bg-[#c4177c]
                                       focus:outline-none focus:ring-2 focus:ring-offset-2
                                       focus:ring-[#D91A8B]"
                            >
                                Login
                            </button>
                        </div>

                        <!-- =========================
                             PEMISAH "Atau"
                             Garis horizontal + label di tengah
                             ========================= -->
                        <div class="relative flex items-center justify-center">
                            <!-- Garis abu-abu tipis melintang -->
                            <span class="absolute inset-x-0 h-px bg-gray-300"></span>
                            <!-- Teks 'Atau' dengan background putih agar garis terpotong di tengah -->
                            <span class="relative bg-white px-4 text-sm text-gray-500">
                                Atau
                            </span>
                        </div>

                        <!-- =========================
                             TOMBOL "Login Pasien"
                             Mengarahkan ke route login pasien (tanpa password)
                             ========================= -->
                        <div>
                            <a
                                href="{{ route('pasien.login') }}"
                                class="w-full inline-flex justify-center py-3 px-4 border border-gray-300
                                       rounded-md shadow-sm text-sm font-medium text-gray-700
                                       bg-gray-200 hover:bg-gray-300
                                       focus:outline-none focus:ring-2 focus:ring-offset-2
                                       focus:ring-gray-400"
                            >
                                Login Pasien
                            </a>
                        </div>
                    </form>

                    <!-- ==================================================
                         SECTION BAWAH FORM: AJUKAN AKUN BARU
                         ================================================== -->
                    <div class="mt-8 flex justify-between items-center text-sm">
                        <!-- 
                            Link "Lupa password?" sebelumnya pernah ada,
                            tapi sekarang di-comment (pakai Blade comment {{-- ... --}}),
                            sehingga tidak muncul di HTML output.
                        -->
                        {{-- 
                        <a href="{{ route('password.request') }}" class="font-medium text-gray-600 hover:text-gray-900">
                            Lupa password? <span class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">Klik
                                disini</span>
                        </a> 
                        --}}

                        <!-- 
                            Tombol untuk membuka modal pemilihan role pengajuan akun:
                            - id="openRoleModal": digunakan di modal-role-petugas.js
                              untuk men-trigger pembukaan modal.
                        -->
                        <button
                            id="openRoleModal"
                            class="font-medium text-gray-600 hover:text-gray-900"
                        >
                            Belum punya akun?
                            <span class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">
                                Ajukan disini
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ==================================================
         MODAL PILIHAN ROLE PENGAJUAN AKUN
         - Ditampilkan saat klik "Ajukan disini"
         - Dikendalikan oleh JS: resources/js/petugas/modal-role-petugas.js
         ================================================== -->
    <div id="roleModal" class="fixed inset-0 z-50 hidden">
        <!-- 
            BACKDROP (overlay gelap)
            - absolute inset-0: menutupi seluruh layar
            - bg-black/40: hitam dengan opacity 40%
            - id="roleModalBackdrop": bisa dipakai untuk menutup modal jika di-klik
        -->
        <div class="absolute inset-0 bg-black/40" id="roleModalBackdrop"></div>

        <!-- 
            CARD MODAL
            - relative + mx-auto + mt-24: posisikan di tengah atas
            - max-w-md: lebar maksimal 1 kolom
            - rounded-2xl: sudut membulat
            - bg-white + shadow-xl: card putih mengambang
        -->
        <div class="relative mx-auto mt-24 w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="p-6">
                <!-- Judul modal -->
                <h2 class="text-xl font-bold text-gray-900">
                    Pilih Jenis Pengajuan Akun
                </h2>

                <!-- Subjudul penjelasan -->
                <p class="text-gray-600 mt-1">
                    Silakan pilih role yang sesuai.
                </p>

                <!-- 
                    Tiga pilihan role:
                    - Puskesmas
                    - Rumah Sakit
                    - Bidan Mandiri
                    Masing-masing adalah link ke halaman form registrasi terkait.
                -->
                <div class="mt-6 space-y-3">
                    <!-- Link ke form registrasi Puskesmas -->
                    <a
                        href="{{ route('puskesmas.register') }}"
                        class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B]
                               text-[#D91A8B] hover:bg-[#fdf1f7] font-medium"
                    >
                        Puskesmas
                    </a>

                    <!-- Link ke form registrasi Rumah Sakit -->
                    <a
                        href="{{ route('rs.register') }}"
                        class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B]
                               text-[#D91A8B] hover:bg-[#fdf1f7] font-medium"
                    >
                        Rumah Sakit
                    </a>

                    <!-- Link ke form registrasi Bidan Mandiri -->
                    <a
                        href="{{ route('bidanMandiri.register') }}"
                        class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B]
                               text-[#D91A8B] hover:bg-[#fdf1f7] font-medium"
                    >
                        Bidan Mandiri
                    </a>
                </div>

                <!-- Tombol "Tutup" untuk menutup modal -->
                <div class="mt-6 flex justify-end">
                    <button
                        id="closeRoleModal"
                        class="px-4 py-2 text-[#D91A8B] font-semibold hover:text-gray-900"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
