<!DOCTYPE html>
<html lang="en">

<head>
    <!-- ============================================
         Meta dasar dokumen HTML
         ============================================ -->
    <meta charset="UTF-8">

    <!-- Membuat layout responsif mengikuti ukuran device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Judul tab browser -->
    <title>Login Pasien - DeLISA</title>

    <!-- Memuat TailwindCSS melalui Vite (WAJIB untuk Laravel + Vite) -->
    @vite('resources/css/app.css')

</head>

<body class="bg-gray-100">
    <!-- ===================================================
         Container fullscreen (min-h-screen)
         Men-center-kan konten dengan flex
         =================================================== -->
    <div class="min-h-screen flex items-center justify-center p-4">

        <!-- ===================================================
             CARD BESAR (max-w-5xl) dengan layout 2 kolom
             Kiri: gambar (hanya tampil di layar besar, lg)
             Kanan: form login
             =================================================== -->
        <div class="w-full max-w-5xl flex bg-white shadow-2xl overflow-hidden rounded-2xl">

            <!-- ============================================
                 KIRI: GAMBAR BACKGROUND (hidden di mobile)
                 lg:block → hanya tampil layar ≥ 1024px
                 ============================================ -->
            <div class="hidden lg:block lg:w-1/2">
                <!-- 
                    object-cover → gambar memenuhi kontainer
                    asset('images/...') → mengambil file public/images
                -->
                <img
                    class="h-full w-full object-cover"
                    src="{{ asset('images/gradient-bg v2.png') }}"
                    alt="DeLISA Panel"
                >
            </div>

            <!-- ============================================
                 KANAN: FORM LOGIN
                 Wadah dengan padding + max-w-md
                 ============================================ -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
                <div class="w-full max-w-md">

                    <!-- Elemen dekorasi '*' berwarna pink -->
                    <p class="text-6xl text-[#D91A8B]">*</p>

                    <!-- Judul -->
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">
                        Login Pasien
                    </h1>

                    <!-- Subjudul -->
                    <p class="text-gray-600 mt-1">
                        Masukkan Nomor Induk Kependudukan (NIK) dan Nama Lengkap Anda untuk masuk
                    </p>

                    <!-- ===================================================
                         Flash message "ok" (misalnya setelah daftar)
                         =================================================== -->
                    @if (session('ok'))
                        <div class="mt-4 rounded-lg border border-green-300 bg-green-50 text-green-700 p-3 text-sm">
                            {{ session('ok') }}
                        </div>
                    @endif

                    <!-- ===================================================
                         FORM LOGIN PASIEN
                         method="POST"
                         action route('pasien.login.store') → AuthController
                         =================================================== -->
                    <form action="{{ route('pasien.login.store') }}"
                          method="POST"
                          class="mt-8 space-y-5">

                        @csrf  <!-- Token keamanan POST -->

                        <!-- ========================
                             INPUT: NIK
                             ======================== -->
                        <div>
                            <label for="nik"
                                   class="block text-sm font-medium text-gray-700">
                                Nomor Induk Kependudukan (NIK)
                            </label>

                            <input
                                type="text"
                                name="nik"
                                id="nik"
                                maxlength="16"
                                placeholder="0000000000000000"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300
                                       rounded-xl shadow-sm placeholder-gray-400
                                       focus:outline-none focus:ring-[#D91A8B]
                                       focus:border-[#D91A8B]"
                            >
                        </div>

                        <!-- ========================
                             INPUT: NAMA LENGKAP
                             ======================== -->
                        <div>
                            <label for="nama_lengkap"
                                   class="block text-sm font-medium text-gray-700">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                name="nama_lengkap"
                                id="nama_lengkap"
                                placeholder="Nama Lengkap Anda"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300
                                       rounded-xl shadow-sm placeholder-gray-400
                                       focus:outline-none focus:ring-[#D91A8B]
                                       focus:border-[#D91A8B]"
                            >
                        </div>

                        <!-- ========================
                             TOMBOL LOGIN
                             ======================== -->
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent
                                       rounded-2xl text-sm font-medium text-white bg-[#D91A8B]
                                       hover:bg-[#c4177c]
                                       focus:outline-none focus:ring-2 focus:ring-offset-2
                                       focus:ring-[#D91A8B]
                                       shadow-[0_10px_20px_-10px_rgba(217,26,139,0.6)]">
                                Login Pasien
                            </button>
                        </div>
                    </form>

                    <!-- ===================================================
                         FOOTER LINK: Login admin & Register pasien
                         =================================================== -->
                    <div class="mt-8 flex justify-between items-center text-sm">

                        <!-- Link ke login admin -->
                        <p class="text-gray-600">
                            Login Admin?
                            <a href="{{ route('login') }}"
                               class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">
                                Klik disini
                            </a>
                        </p>

                        <!-- Link ke register pasien -->
                        <p class="text-gray-600">
                            Belum Punya Akun?
                            <a href="{{ route('pasien.register') }}"
                               class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">
                                Daftar disini
                            </a>
                        </p>

                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>
