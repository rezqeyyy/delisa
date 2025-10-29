<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kondisi Kesehatan Pasien - Delisa Skrining</title>
    
    <!-- Memuat stylesheet utama via Vite -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/pasien/imt.js', 
        'resources/js/pasien/map.js', 
        'resources/js/pasien/usia-kehamilan.js',
        'resources/js/pasien/perkiraan-persalinan.js'
        ])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    
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
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Kondisi Kesehatan Pasien</h1>
            </div>

            @php
                $stepCurrent = 3;
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
                :current="3" 
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
                Form ini diisi untuk data kesehatan ibu
            </p>

            <form>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                    <!-- Kolom kiri -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" min="0" step="0.1" inputmode="numeric" name="tinggi_badan" id="tinggi_badan"
                                    class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                    placeholder="0">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">Cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Berat Badan Sebelum Hamil Saat Ini</label>
                            <div class="relative">
                                <input type="number" min="0" step="0.01" inputmode="decimal" name="berat_badan_saat_hamil" id="berat_badan"
                                    class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                    placeholder="0">
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">Kg</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Indeks Masa Tubuh (IMT)</label>
                            <input type="text" id="imt_result" disabled
                                class="mt-2 w-full rounded-full border px-5 py-3 text-sm bg-[#F8FAFB] text-[#B9257F] border-[#B9257F]"
                                value="Akan terisi otomatis oleh sistem">
                            <p id="imt_category" class="mt-2 text-sm font-medium hidden"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Tekanan Darah (SDP/DBP)</label>
                            <div class="mt-2 flex items-center gap-4">
                                <div class="relative flex-1">
                                    <input type="number" min="0" inputmode="numeric" name="sdp" id="sdp"
                                           class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                           placeholder="Sistolik">
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">mmHg</span>
                                </div>
                                <span class="text-[#1D1D1D]">/</span>
                                <div class="relative flex-1">
                                    <input type="number" min="0" inputmode="numeric" name="dbp" id="dbp"
                                           class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                           placeholder="Diastolik">
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">mmHg</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Mean Arterial Pressure (MAP)</label>
                            <input type="text" id="map_result" disabled
                                   class="mt-2 w-full rounded-full border border-[#B9257F] bg-[#F8FAFB] px-5 py-3 text-sm text-[#B9257F]"
                                   value="Akan terisi otomatis oleh sistem">
                            <input type="hidden" name="map" id="map_hidden" value="">
                            <p class="mt-2 text-xs text-[#B9257F]">
                                Note: Mean Arterial Pressure (MAP) adalah tekanan darah rata-rata di arteri selama satu siklus jantung.
                            </p>
                        </div>

                    </div>

                    <!-- Kolom kanan -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Pemeriksaan Protein Urine</label>
                            <select name="pemeriksaan_protein_urine"
                                    class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#B9257F]">
                                <option value="Negatif">Negatif</option>
                                <option value="Positif 1">Positif 1</option>
                                <option value="Positif 2">Positif 2</option>
                                <option value="Positif 3">Positif 3</option>
                                <option value="Belum dilakukan Pemeriksaan">Belum dilakukan Pemeriksaan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">HPHT (Hari Pertama Haid Terakhir)</label>
                            <input type="date" name="hpht" id="hpht"
                                   class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#B9257F]" placeholder="dd/mm/yyyy">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Tanggal Skrining</label>
                            <input type="date" name="tanggal_skrining" id="tanggal_skrining"
                                   class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#B9257F]" placeholder="dd/mm/yyyy">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Usia Kehamilan (Minggu)</label>
                            <input type="text" id="usia_kehamilan_result" disabled
                                   class="mt-2 w-full rounded-full border border-[#B9257F] bg-[#F8FAFB] px-5 py-3 text-sm text-black"
                                   value="Akan terisi otomatis oleh sistem">
                            <input type="hidden" name="usia_kehamilan_minggu" id="usia_kehamilan_hidden" value="">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D]">Tanggal Perkiraan Persalinan</label>
                            <input type="date" disabled name="tanggal_perkiraan_persalinan" id="tpp_result"
                                   class="mt-2 w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#B9257F]">
                            <input type="hidden" name="tanggal_perkiraan_persalinan" id="tpp_hidden" value="">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('pasien.riwayat-kehamilan') }}"
                        class="rounded-full bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Kembali
                    </a>
                    <a href="{{ route('pasien.riwayat-penyakit-pasien') }}"
                        class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                        Lanjut
                    </a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>