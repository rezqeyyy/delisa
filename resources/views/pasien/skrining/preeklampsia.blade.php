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
                @if ($errors->any())
                    <div class="mt-4 p-4 rounded-md bg-red-50 border border-red-200 text-red-700">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                
                @csrf
                <input type="hidden" name="skrining_id" value="{{ request('skrining_id') }}">
                <div class="mt-6">
                    <h2 class="text-xl font-semibold text-[#B9257F]">Mohon isi informasi terkait dengan Preeklampsia</h2>
                    <p class="mt-2 text-sm text-[#1D1D1D]">Pilih jawaban yang sesuai</p>

                    <div class="space-y-4 mt-6">
                        @php
                            $moderate = [
                                1 => 'Apakah kehamilan ini adalah kehamilan kedua/lebih tetapi bukan dengan suami pertama (Pernikahan kedua atau lebih)',
                                2 => 'Apakah kehamilan ini dengan Teknologi Reproduksi Berbantu (Bayi tabung, Obat induksi ovulasi)',
                                3 => 'Umur ≥ 35 tahun',
                                4 => 'Apakah ini termasuk ke kehamilan pertama',
                                5 => 'Apakah kehamilan ini berjarak 10 tahun dari kehamilan sebelumnya',
                                6 => 'Apakah ibu kandung atau saudara perempuan anda memiliki riwayat pre-eklampsia',
                                7 => 'Apakah memiliki riwayat obesitas sebelum hamil (IMT > 30Kg/m2)',
                            ];
                            $high = [
                                8  => 'Apakah anda memiliki riwayat pre-eklampsia pada kehamilan/persalinan sebelumnya',
                                9  => 'Apakah kehamilan anda saat ini adalah kehamilan kembar',
                                10 => 'Apakah anda memiliki diabetes dalam masa kehamilan',
                                11 => 'Apakah anda memiliki tekanan darah (Tensi) di atas 130/90 mHg',
                                12 => 'Apakah anda memiliki penyakit ginjal',
                                13 => 'Apakah anda memiliki penyakit autoimun, SLE',
                                14 => 'Apakah anda memiliki penyakit Anti Phospholipid Syndrome',
                            ];
                        @endphp

                        @foreach ($moderate as $num => $text)
                            <div class="rounded-3xl bg-[#FEF08A] p-4">
                                <div class="font-medium mb-3">{{ $num }}. {{ $text }}</div>
                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pertanyaan{{ $num }}" value="ya" class="hidden peer" {{ in_array($num, [3,4,7]) ? 'disabled' : '' }}
                                            {{ !empty($answers) && ($answers['pertanyaan'.$num] ?? false) ? 'checked' : '' }}>
                                        <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                        <span class="text-sm">Ya</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pertanyaan{{ $num }}" value="tidak" class="hidden peer" {{ in_array($num, [3,4,7]) ? 'disabled' : '' }}
                                            {{ !empty($answers) ? (($answers['pertanyaan'.$num] ?? false) ? '' : 'checked') : 'checked' }}>
                                        <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                        <span class="text-sm">Tidak</span>
                                    </label>
                                </div>

                                @if(in_array($num, [3,4,7]))
                                    <p class="mt-2 text-xs text-[#B9257F]">*Terisi otomatis.</p>
                                @endif
                        
                            </div>
                        @endforeach

                        @foreach ($high as $num => $text)
                            <div class="rounded-3xl bg-[#FFB6B6] p-4">
                                <div class="font-medium mb-3">{{ $num }}. {{ $text }}</div>
                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pertanyaan{{ $num }}" value="ya" class="hidden peer" {{ $num === 11 ? 'disabled' : '' }}
                                            {{ !empty($answers) && ($answers['pertanyaan'.$num] ?? false) ? 'checked' : '' }}>
                                        <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                        <span class="text-sm">Ya</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pertanyaan{{ $num }}" value="tidak" class="hidden peer" {{ $num === 11 ? 'disabled' : '' }}
                                            {{ !empty($answers) ? (($answers['pertanyaan'.$num] ?? false) ? '' : 'checked') : 'checked' }}>
                                        <span class="w-5 h-5 rounded-full border border-gray-400 inline-block mr-2 peer-checked:bg-[#B9257F] peer-checked:border-[#B9257F]"></span>
                                        <span class="text-sm">Tidak</span>
                                    </label>
                                </div>

                                @if(in_array($num, [11]))
                                    <p class="mt-2 text-xs text-[#B9257F]">*Terisi otomatis.</p>
                                @endif

                            </div>
                        @endforeach
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