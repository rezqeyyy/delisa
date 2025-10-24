<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Riwayat Penyakit Pasien</title>
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
                <a href="{{ route('pasien.kondisi-kesehatan-pasien') }}" class="text-[#1D1D1D] hover:text-[#000]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Edit Skrining</h1>
            </div>

            @php
                $stepCurrent = 4;
                $stepItems = [
                    'Data Diri Pasien',
                    'Riwayat Kehamilan & Persalinan',
                    'Kondisi Kesehatan Pasien',
                    'Riwayat Penyakit Pasien',
                    'Riwayat Penyakit Keluarga',
                    'Pre Eklampsia',
                ];
            @endphp

            <x-pasien.stepper :current="$stepCurrent" :items="$stepItems" />
            <div class="mt-4 md:hidden">
                <h2 class="text-base font-semibold text-[#1D1D1D]">
                    {{ $stepItems[$stepCurrent - 1] }}
                </h2>
            </div>

            <form>
                @csrf
                <div class="mt-6">
                    <h2 class="text-xl font-semibold text-[#B9257F]">Apakah Ibu memiliki riwayat penyakit?</h2>
                    <p class="mt-2 text-sm text-[#1D1D1D]">Centang penyakit yang pernah diderita</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                        <!-- Hipertensi -->
                        <div class="relative">
                            <input type="checkbox" id="hipertensi" name="penyakit[]" value="hipertensi" class="peer absolute opacity-0 h-0 w-0">
                            <label for="hipertensi" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Hipertensi</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Alergi -->
                        <div class="relative">
                            <input type="checkbox" id="alergi" name="penyakit[]" value="alergi" class="peer absolute opacity-0 h-0 w-0">
                            <label for="alergi" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Alergi</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Tiroid -->
                        <div class="relative">
                            <input type="checkbox" id="tiroid" name="penyakit[]" value="tiroid" class="peer absolute opacity-0 h-0 w-0">
                            <label for="tiroid" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Tiroid</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- TB -->
                        <div class="relative">
                            <input type="checkbox" id="tb" name="penyakit[]" value="tb" class="peer absolute opacity-0 h-0 w-0">
                            <label for="tb" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>TB</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Jantung -->
                        <div class="relative">
                            <input type="checkbox" id="jantung" name="penyakit[]" value="jantung" class="peer absolute opacity-0 h-0 w-0">
                            <label for="jantung" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Jantung</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Hepatitis B -->
                        <div class="relative">
                            <input type="checkbox" id="hepatitis_b" name="penyakit[]" value="hepatitis_b" class="peer absolute opacity-0 h-0 w-0">
                            <label for="hepatitis_b" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Hepatitis B</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Jiwa -->
                        <div class="relative">
                            <input type="checkbox" id="jiwa" name="penyakit[]" value="jiwa" class="peer absolute opacity-0 h-0 w-0">
                            <label for="jiwa" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Jiwa</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Autoimun -->
                        <div class="relative">
                            <input type="checkbox" id="autoimun" name="penyakit[]" value="autoimun" class="peer absolute opacity-0 h-0 w-0">
                            <label for="autoimun" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Autoimun</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Sifilis -->
                        <div class="relative">
                            <input type="checkbox" id="sifilis" name="penyakit[]" value="sifilis" class="peer absolute opacity-0 h-0 w-0">
                            <label for="sifilis" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Sifilis</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Diabetes -->
                        <div class="relative">
                            <input type="checkbox" id="diabetes" name="penyakit[]" value="diabetes" class="peer absolute opacity-0 h-0 w-0">
                            <label for="diabetes" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Diabetes</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Asma -->
                        <div class="relative">
                            <input type="checkbox" id="asma" name="penyakit[]" value="asma" class="peer absolute opacity-0 h-0 w-0">
                            <label for="asma" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Asma</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Lainnya -->
                        <div class="relative">
                            <input type="checkbox" id="lainnya" name="penyakit[]" value="lainnya" class="peer absolute opacity-0 h-0 w-0">
                            <label for="lainnya" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Lainnya</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('pasien.kondisi-kesehatan-pasien') }}" class="px-6 py-3 rounded-full bg-[#F2F2F2] text-[#1D1D1D] font-medium">
                            Kembali
                        </a>
                        <a href="{{ route('pasien.riwayat-penyakit-keluarga') }}"
                            class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                            Lanjut
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>