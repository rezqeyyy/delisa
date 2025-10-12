<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lupa Password — DeLISA</title>
    @vite('resources/css/app.css')
    <style>
        html,
        body {
            height: 100%;
            background-color: #f3f4f6;
            /* bg-gray-100 */
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <!-- KUNCI TINGGI DI SINI -->
    <div class="w-full max-w-5xl flex items-stretch bg-white shadow-2xl overflow-hidden rounded-2xl"
        style="height: 620px;"> <!-- <- sesuaikan angka -->

        <!-- PANEL KIRI (template, hanya ikut tinggi) -->
        <div class="hidden lg:block relative lg:w-1/2 bg-white rounded-none h-full">
            <div class="absolute inset-0 p-10 flex items-center justify-center">
                <img class="h-full w-full object-contain" src="{{ asset('images/gradient-bg v2.png') }}"
                    alt="DeLISA Panel">
            </div>
        </div>

        <!-- PANEL KANAN (ikut tinggi + tengah vertikal) -->
        <div class="w-full lg:w-1/2 h-full grid place-content-center p-8 sm:p-12">
            <div class="w-full max-w-md transform -translate-y-">
                <p class="text-7xl font-bold text-[#D91A8B] leading-none">*</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mt-4"><b>Lupa Password</b></h1>
                <p class="text-sm text-gray-500 mt-1">Masukan Email</p>

                <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">e-mail</label>
                        <input type="email" id="email" name="email" placeholder="akun@gmail.com" required
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm
                     placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 rounded-md shadow-lg text-sm font-medium text-white
                     bg-[#D91A8B] hover:bg-[#c4177c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D91A8B]">
                            Selanjutnya
                        </button>
                    </div>
                </form>

                <div class="mt-6 flex justify-between text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-gray-900">
                        Kembali ke <span class="text-[#D91A8B] font-semibold">Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="font-medium text-gray-600 hover:text-gray-900">
                        Belum Punya Akun? <span class="text-[#D91A8B] font-semibold">Ajukan disini</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</body>


</html>
