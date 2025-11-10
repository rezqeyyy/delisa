<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre Eklampsia - Delisa Skrining</title>
    @vite([
        'resources/css/app.css', 
        'resources/js/pasien/sidebar-toggle.js'
        ])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

            <form method="POST" action="{{ route('pasien.preeklampsia.store') }}">
                @csrf
                <input type="hidden" name="skrining_id" value="{{ request('skrining_id') }}">
                <div class="mt-6">
                    <h2 class="text-xl font-semibold text-[#B9257F]">Mohon isi informasi terkait dengan Preeklampsia</h2>
                    <p class="mt-2 text-sm text-[#1D1D1D]">Pilih jawaban yang sesuai</p>

                    <div class="space-y-4 mt-6">
                        <!-- Pertanyaan 1 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">1. Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan1" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan1'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan1" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan1'] ?? false) ? '' : 'checked') : 'checked' }}>
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
                                    <input type="radio" name="pertanyaan2" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan2'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan2" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan2'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>
                                       
                        <!-- Pertanyaan 3 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">3. Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan3" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan3'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan3" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan3'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 4 -->
                        <div class="rounded-3xl bg-[#FEF08A] p-4">
                            <div class="font-medium mb-3">4. Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan4" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan4'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan4" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan4'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                    
                        <!-- Pertanyaan 5 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">5. Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan5" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan5'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan5" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan5'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 6 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">6. Apakah kehamilan anda saat ini adalah kehamilan kembar</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan6" value="ya" class="hidden peer"
                                        {{ !empty($answers) && ($answers['pertanyaan6'] ?? false) ? 'checked' : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan6" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan6'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <!-- Pertanyaan 7 -->
                        <div class="rounded-3xl bg-[#FFB6B6] p-4">
                            <div class="font-medium mb-3">7. Apakah anda memiliki diabetes dalam masa kehamilan</div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan7" value="ya" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan7'] ?? false) ? 'checked' : '') : '' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Ya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pertanyaan7" value="tidak" class="hidden peer"
                                        {{ !empty($answers) ? (($answers['pertanyaan7'] ?? false) ? '' : 'checked') : 'checked' }}>
                                    <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                    <span class="text-sm">Tidak</span>
                                </label>
                            </div>
                        </div>
                        
                    <div>

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
                                class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a21b73]">
                            Simpan Hasil
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
</html>