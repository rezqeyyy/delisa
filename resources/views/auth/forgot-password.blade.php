<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - DeLISA</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl flex bg-white shadow-2xl overflow-hidden rounded-2xl">

            <div class="hidden lg:block lg:w-1/2">
                <img class="h-full w-full object-cover" src="{{ asset('images/gradient-bg v2.png') }}" alt="DeLISA Panel">
            </div>

            <div class="w-full lg:w-1/2 flex p-8 sm:p-12">
                <div class="w-full max-w-md mx-auto flex flex-col justify-between">
                    <div>
                        <p class="text-7xl font-bold text-[#D91A8B]">*</p>
                        <h1 class="text-3xl font-bold text-gray-900 mt-4">Lupa Password</h1>
                        <p class="text-gray-600 mt-1">Masukan Email Akun Anda</p>

                        <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" placeholder="akun@gmail.com" required
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                        class="w-full flex justify-center py-3 px-4 rounded-md shadow-lg text-sm font-medium text-white bg-[#D91A8B] hover:bg-[#c4177c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#D91A8B]">
                                    Selanjutnya
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 flex justify-between text-sm">
                        <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-gray-900">
                            Kembali ke <span class="text-[#D91A8B] font-semibold">Login</span>
                        </a>
                        <button id="openRoleModal"
                            class="font-medium text-gray-600 hover:text-gray-900">
                            Belum punya akun? <span class="text-[#D91A8B] font-semibold">Ajukan disini</span>
                        </button>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
    <!-- Modal Pilihan Role -->
    <div id="roleModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" id="roleModalBackdrop"></div>
        <div class="relative mx-auto mt-24 w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900">Pilih Jenis Pengajuan Akun</h2>
                <p class="text-gray-600 mt-1">Silakan pilih role yang sesuai.</p>

                <div class="mt-6 space-y-3">
                    <a href="{{ route('puskesmas.register') }}"
                       class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B] text-[#D91A8B] hover:bg-[#fdf1f7] font-medium">
                        Puskesmas
                    </a>
                    <a href="{{ route('rs.register') }}"
                       class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B] text-[#D91A8B] hover:bg-[#fdf1f7] font-medium">
                        Rumah Sakit
                    </a>
                    <a href="{{ route('bidanMandiri.register') }}"
                       class="block w-full text-center py-3 px-4 rounded-md border border-[#D91A8B] text-[#D91A8B] hover:bg-[#fdf1f7] font-medium">
                        Bidan Mandiri
                    </a>
                </div>

                <div class="mt-6 flex justify-end">
                    <button id="closeRoleModal" class="px-4 py-2 text-[#D91A8B] font-semibold hover:text-gray-900">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const openBtn = document.getElementById('openRoleModal');
        const modal = document.getElementById('roleModal');
        const closeBtn = document.getElementById('closeRoleModal');
        const backdrop = document.getElementById('roleModalBackdrop');

        openBtn?.addEventListener('click', () => modal.classList.remove('hidden'));
        closeBtn?.addEventListener('click', () => modal.classList.add('hidden'));
        backdrop?.addEventListener('click', () => modal.classList.add('hidden'));
    </script>
</body>

</html>
