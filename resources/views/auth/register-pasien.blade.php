<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pasien - DeLISA</title>
    @vite('resources/css/app.css')
    <style>
        /* Mengimpor font Poppins dari Google Fonts agar visual teks 100% cocok dengan desain modern */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl flex bg-white shadow-2xl overflow-hidden rounded-2xl">
            
            <div class="hidden lg:block lg:w-1/2">
                <img class="h-full w-full object-cover" src="{{ asset('images/gradient-bg v2.png') }}" alt="DeLISA Panel">
            </div>

            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
                <div class="w-full max-w-md mx-auto">
                    
                    <div>
                        <p class="text-7xl font-bold text-[#D91A8B]">*</p>
                        <h1 class="text-3xl font-bold text-gray-900 mt-4">Registrasi Pasien</h1>
                        <p class="text-gray-600 mt-1">Daftar dengan Nomor Induk Kependudukan (NIK) dan Nama Lengkap</p>

                        <form action="{{ route('pasien.register.store') }}" method="POST" class="mt-8 space-y-5">
                            @csrf
                            <div>
                                <label for="nik" class="block text-sm font-medium text-gray-700">Nomor Induk
                                    Kependudukan (NIK)</label>
                                <input type="text" name="nik" id="nik" maxlength="16"
                                    placeholder="16 digit NIK" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                            </div>
                            <div>
                                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama
                                    Lengkap</label>
                                <input type="text" name="nama_lengkap" id="nama_lengkap"
                                    placeholder="Nama sesuai KTP" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-lg text-sm font-medium text-white bg-[#D91A8B] hover:bg-[#c4177c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D91A8B]">
                                    Register
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 flex justify-end text-sm">
                            <a href="{{ route('pasien.login') }}" class="font-medium text-gray-600 hover:text-gray-900">
                                Sudah punya akun? <span class="text-[#D91A8B] font-semibold">Klik disini</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>