<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login DeLiSA</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4">

    <div class="flex bg-white rounded-2xl shadow-2xl overflow-hidden max-w-4xl w-full" style="height: 620px;">

        {{-- BAGIAN KIRI: HANYA MENAMPILKAN GAMBAR --}}
        <div class="w-1/2 hidden md:block">
            <img src="{{ asset('images/panel-kiri.png') }}" alt="Panel Login DeLiSA" class="w-full h-full object-cover">
        </div>

        {{-- BAGIAN KANAN: FORM LOGIN --}}
        <div class="w-1/2 p-12 flex flex-col justify-center">
            <div class="mb-8">
                <div class="text-[#be2983] text-5xl font-bold mb-3">*</div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Login Delisa</h1>
                <p class="text-gray-600 text-sm">Masukan Email dan Password</p>
            </div>

            <form action="#" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="akun@gmail.com"
                           class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password Anda"
                           class="w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                </div>
                <div class="mb-6">
                    <button type="submit"
                            class="w-full bg-[#be2983] hover:bg-[#a32470] text-white font-bold py-3 px-4 rounded-md shadow-lg transition duration-200">
                        Login
                    </button>
                </div>
            </form>

            <div class="text-center text-gray-500 my-4 text-sm font-semibold">Atau</div>

            <div class="mb-6">
                <button type="button"
                        class="w-full bg-[#8c8c8c] hover:bg-[#7a7a7a] text-white font-bold py-3 px-4 rounded-md shadow-lg transition duration-200">
                    Login Pasien
                </button>
            </div>

            <div class="flex justify-between text-sm mt-4">
                <a href="#" class="text-purple-600 hover:underline">Lupa Password? <span class="font-bold">Klik disini</span></a>
                <a href="#" class="text-purple-600 hover:underline">Belum Punya Akun? <span class="font-bold">Ajukan disini</span></a>
            </div>
        </div>
    </div>

</body>
</html>