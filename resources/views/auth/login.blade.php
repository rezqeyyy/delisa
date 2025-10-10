<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login DeLISA</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <div class="hidden lg:block lg:w-1/2">
            <img src="{{ asset('images/gradient-bg.png') }}" alt="DeLISA Gradient Background" class="h-full w-full object-cover">
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
            <div class="w-full max-w-md">
                <div class="mb-8">
                     <svg class="h-10 w-10 text-[#D91A8B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6.095l.875 1.75a.5.5 0 00.447.255h1.856a.5.5 0 01.353.854l-1.5 1.5a.5.5 0 00-.146.553l.5 2.053a.5.5 0 01-.724.529L12 12.835a.5.5 0 00-.582 0l-1.802 1.05a.5.5 0 01-.724-.53l.5-2.052a.5.5 0 00-.146-.553l-1.5-1.5a.5.5 0 01.353-.854h1.856a.5.5 0 00.447-.255L10.5 6.095z" />
                         <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 110-18 9 9 0 010 18z" />
                    </svg>

                    <h1 class="text-3xl font-bold text-gray-900 mt-4">Login Delisa</h1>
                    <p class="text-gray-600 mt-1">Masukan Email dan Password</p>
                </div>

                <form action="#" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">e-mail</label>
                            <input type="email" name="email" id="email" placeholder="akun@gmail.com"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password" placeholder="Password Anda"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-500 focus:border-pink-500">
                        </div>

                        <div>
                            <button type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#D91A8B] hover:bg-[#c4177c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                Login
                            </button>
                        </div>

                        <div class="relative flex items-center justify-center">
                            <span class="absolute inset-x-0 h-px bg-gray-300"></span>
                            <span class="relative bg-gray-50 px-4 text-sm text-gray-500">Atau</span>
                        </div>

                        <div>
                            <button type="button"
                                    class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                                Login Pasien
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-6 flex justify-between text-sm">
                    <a href="#" class="font-medium text-gray-600 hover:text-gray-900">
                        Lupa Password? <span class="text-[#D91A8B] font-semibold">Klik disini</span>
                    </a>
                     <a href="#" class="font-medium text-gray-600 hover:text-gray-900">
                        Belum Punya Akun? <span class="text-[#D91A8B] font-semibold">Ajukan disini</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>