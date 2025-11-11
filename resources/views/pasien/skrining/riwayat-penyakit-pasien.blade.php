<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penyakit Pasien - Delisa Skrining</title>

    <!-- Memuat stylesheet utama via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/pasien/sidebar-toggle.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-pasien.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center">
                <a href="{{ route('pasien.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Riwayat Penyakit Pasien</h1>
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

            <x-pasien.stepper 
                :current="4" 
                :urls="[
                    route('pasien.data-diri', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-kehamilan-gpa', ['skrining_id' => request('skrining_id')]),
                    route('pasien.kondisi-kesehatan-pasien', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-penyakit-pasien', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-penyakit-keluarga', ['skrining_id' => request('skrining_id')]),
                    route('pasien.preeklampsia', ['skrining_id' => request('skrining_id')]),
                ]" 
            />


            <div class="mt-4 md:hidden">
                <h2 class="text-base font-semibold text-[#1D1D1D]">
                    {{ $stepItems[$stepCurrent - 1] }}
                </h2>
            </div>

            <form method="POST" action="{{ route('pasien.riwayat-penyakit-pasien.store') }}">
                @csrf
                <input type="hidden" name="skrining_id" value="{{ request('skrining_id') }}">
                <div class="mt-6">
                    <h2 class="text-xl font-semibold text-[#B9257F]">Apakah Ibu memiliki riwayat penyakit?</h2>
                    <p class="mt-2 text-sm text-[#1D1D1D]">Centang penyakit yang pernah diderita</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                        <!-- Hipertensi Kronik -->
                        <div class="relative">
                            <input type="checkbox" id="hipertensi_kronik" name="penyakit[]"
                                value="hipertensi_kronik"
                                {{ in_array('hipertensi_kronik', old('penyakit', $selected ?? [])) ? 'checked' : '' }}
                                class="peer absolute opacity-0 h-0 w-0">
                            <label for="hipertensi_kronik" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Hipertensi Kronik</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Ginjal -->
                        <div class="relative">
                            <input type="checkbox" id="ginjal" name="penyakit[]" value="ginjal"
                                {{ in_array('ginjal', old('penyakit', $selected ?? [])) ? 'checked' : '' }}
                                class="peer absolute opacity-0 h-0 w-0">
                            <label for="ginjal" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Ginjal</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!--  Autoimun, SLE -->
                        <div class="relative">
                            <input type="checkbox" id="autoimun_sle" name="penyakit[]" value="autoimun_sle"
                                {{ in_array('autoimun_sle', old('penyakit', $selected ?? [])) ? 'checked' : '' }}
                                class="peer absolute opacity-0 h-0 w-0">
                            <label for="autoimun_sle" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Autoimun, SLE</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!--  Anti Phospholipid Syndrome -->
                        <div class="relative">
                            <input type="checkbox" id="anti_phospholipid_syndrome" name="penyakit[]" value="anti_phospholipid_syndrome"
                                {{ in_array('anti_phospholipid_syndrome', old('penyakit', $selected ?? [])) ? 'checked' : '' }}
                                class="peer absolute opacity-0 h-0 w-0">
                            <label for="anti_phospholipid_syndrome" class="flex items-center justify-between w-full p-4 rounded-full border border-[#B9257F] cursor-pointer peer-checked:bg-[#B9257F] peer-checked:text-white text-[#1D1D1D] transition-colors">
                                <span>Anti Phospholipid Syndrome</span>
                                <span class="h-5 w-5 rounded-full border border-[#B9257F] flex items-center justify-center peer-checked:bg-white">
                                    <svg class="h-3 w-3 text-[#B9257F] hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </label>
                            <div class="mt-1 text-xs text-[#B9257F]">* Manifestasi Klinis APS Antara Lain: Keguguran Berulang, IUFD, Kelahiran 
                            Prematur</div>
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
                            <div class="mt-2 hidden peer-checked:block">
                                <input type="text" name="penyakit_lainnya" placeholder="Sebutkan penyakit lainnya" class="w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm text-[#1D1D1D] placeholder-[#8C8C8C]" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('pasien.kondisi-kesehatan-pasien') }}" class="px-6 py-3 rounded-full bg-[#F2F2F2] text-[#1D1D1D] font-medium">
                            Kembali
                        </a>
                        <button type="submit" 
                            class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                            Simpan & lanjut
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>