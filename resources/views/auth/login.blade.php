<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pasien - DeLISA</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl flex bg-white shadow-2xl overflow-hidden rounded-2xl">

            <div class="hidden lg:block lg:w-1/2">
                <img class="h-full w-full object-contain" src="{{ asset('images/gradient-bg v2.png') }}" alt="DeLISA Panel">
            </div>

            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
                <div class="w-full max-w-md">
                    <p class="text-6xl text-[#D91A8B]">*</p>

                    <h1 class="text-3xl font-semibold text-gray-900 mt-2">Login Pasien</h1>

                    <p class="text-gray-600 mt-1">Masukkan Nomor Induk Kependudukan (NIK) dan Nama Lengkap Anda untuk
                        masuk</p>

                    <form action="{{ route('pasien.login.store') }}" method="POST" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="nik" class="block text-sm font-medium text-gray-700">Nomor Induk Kependudukan
                                (NIK)</label>
                            <input type="text" name="nik" id="nik" maxlength="16" placeholder="0000000000000000"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                        </div>
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama
                                Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama Lengkap Anda"
                                required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                        </div>
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-lg text-sm font-medium text-white bg-[#D91A8B] hover:bg-[#c4177c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D91A8B]">
                                Login Pasien
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center text-sm space-y-2">
                        <p class="text-gray-600">
                            Login Admin?
                            <a href="{{ route('login') }}"
                                class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">Klik disini</a>
                        </p>
                        <p class="text-gray-600">
                            Belum Punya Akun?
                            <a href="{{ route('pasien.register') }}"
                                class="font-semibold text-[#D91A8B] hover:text-[#c4177c]">Daftar disini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>