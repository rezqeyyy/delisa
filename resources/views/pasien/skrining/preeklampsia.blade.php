<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre Eklampsia - Delisa Skrining</title>
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
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Preeklampsia</h1>
            </div>

            @php
                $stepCurrent = 6;
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
                :current="6" 
                :urls="[
                    route('pasien.data-diri'),
                    route('pasien.riwayat-kehamilan-gpa'),
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

            <form>
                @csrf
                <div class="mt-6">
                    <h2 class="text-xl font-semibold text-[#B9257F]">Mohon isi informasi terkait dengan Preeklampsia</h2>
                    <p class="mt-2 text-sm text-[#1D1D1D]">Pilih jawaban yang sesuai</p>

                    <div class="space-y-4 mt-6">
                        <!-- Pertanyaan 1 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">1. Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan1" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan1" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 2 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">2. Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan2" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan2" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 3 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">
                                3. Umur ≥ 35 tahun
                                @if(isset($preFill['pertanyaan3']))
                                    <span class="ml-2 text-xs rounded-full bg-gray-200 text-gray-700 px-2 py-1">Otomatis</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan3" value="ya" class="hidden peer"
                                        {{ (isset($preFill['pertanyaan3']) && $preFill['pertanyaan3'] === 'ya') ? 'checked' : '' }}
                                        {{ isset($preFill['pertanyaan3']) ? 'disabled' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan3" value="tidak" class="hidden peer"
                                        {{ (isset($preFill['pertanyaan3']) && $preFill['pertanyaan3'] === 'tidak') ? 'checked' : '' }}
                                        {{ isset($preFill['pertanyaan3']) ? 'disabled' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                            @if(isset($preFill['pertanyaan3']))
                                <input type="hidden" name="pertanyaan3" value="{{ $preFill['pertanyaan3'] }}">
                            @endif
                        </div>

                        <!-- Pertanyaan 4 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">4. Apakah ini termasuk kehamilan pertama</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan4" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan4" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 5 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">5. Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan5" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan5" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 6 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">6. Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan6" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan6" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 7 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">7. Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30kg/m2)</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan7" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan7" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 8 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">8. Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan8" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan8" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 9 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">9. Apakah kehamilan anda saat ini adalah kehamilan kembar</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan9" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan9" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 10 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">10. Apakah anda memiliki diabetes dalam masa kehamilan</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan10" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan10" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 11 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">11. Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan11" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan11" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 12 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">12. Apakah anda memiliki penyakit ginjal</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan12" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F]  peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan12" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 13 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">13. Apakah anda memiliki penyakit autoimun, SLE</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan13" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan13" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 14 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">14. Apakah anda memiliki penyakit phospholid syndrome</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan14" value="ya" class="hidden peer">
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan14" value="tidak" class="hidden peer" checked>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-[#B9257F]">Catatan Tambahan</h3>
                        <textarea
                            name="catatan"
                            rows="4"
                            class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-[#1D1D1D] focus:border-[#B9257F] focus:outline-none focus:ring-1 focus:ring-[#B9257F]"
                            placeholder="Tuliskan catatan tambahan jika ada..."></textarea>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('pasien.riwayat-penyakit-keluarga') }}" class="px-6 py-3 rounded-full bg-[#F2F2F2] text-[#1D1D1D] font-medium">
                            Kembali
                        </a>
                        <button type="submit"
                            class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>