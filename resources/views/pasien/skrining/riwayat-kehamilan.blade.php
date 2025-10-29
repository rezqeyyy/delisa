<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kehamilan & Persalinan — Delisa Skrining</title>
    @vite('resources/css/app.css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-pasien.sidebar class="hidden xl:flex z-30" />

        <x-pasien.sidebar
            x-cloak
            x-show="openSidebar"
            class="xl:hidden z-50 transform"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        />
        <div
            x-cloak
            x-show="openSidebar"
            class="fixed inset-0 z-40 bg-black/40 xl:hidden"
            @click="openSidebar = false">
        </div>

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center">
                <a href="{{ route('pasien.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Riwayat Kehamilan & Persalinan</h1>
            </div>

            @php
                $stepCurrent = 2;
                $stepItems = [
                    'Data Diri Pasien',
                    'Riwayat Kehamilan & Persalinan',
                    'Kondisi Kesehatan Pasien',
                    'Riwayat Penyakit Pasien',
                    'Riwayat Penyakit Keluarga',
                    'Pre Eklampsia',
                ];
            @endphp

            <x-pasien.stepper 
                :current="2" 
                :urls="[
                    route('pasien.data-diri'),
                    route('pasien.riwayat-kehamilan'),
                    route('pasien.kondisi-kesehatan-pasien'),
                    route('pasien.riwayat-penyakit-pasien'),
                    route('pasien.riwayat-penyakit-keluarga'),
                    route('pasien.preeklampsia'),
                ]" 
            />

            <div class="mt-4 md:hidden">
                <h2 class="text-base font-semibold text-[#1D1D1D]">
                    {{ $stepItems[$stepCurrent - 1] }}
                </h2>
            </div>

            <p class="mt-2 text-sm text-[#B9257F]">
                Form ini diisi untuk data riwayat kehamilan & persalinan dan kehamilan sebelumnya
            </p>

            <form>
                @csrf
                <div class="space-y-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-[#1D1D1D]">
                            I. Kehamilan saat ini yang keberapa (keguguran dan lahir mati dihitung)
                        </label>
                        <input type="number" min="0" inputmode="numeric"
                               class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                               placeholder="Masukkan jumlah kehamilan" name="total_kehamilan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1D1D1D]">
                            II. Total Persalinan sudah berapa kali (bayi hidup/lahir mati)
                        </label>
                        <input type="number" min="0" inputmode="numeric"
                               class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                               placeholder="Masukkan jumlah persalinan" name="total_persalinan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1D1D1D]">
                            III. Keguguran
                        </label>
                        <input type="number" min="0" inputmode="numeric" value="0"
                               class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                               placeholder="0" name="total_abortus">
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('pasien.data-diri') }}"
                        class="rounded-full bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Kembali
                    </a>
                    <a href="{{ route('pasien.kondisi-kesehatan-pasien') }}"
                        class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                        Lanjut
                    </a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>