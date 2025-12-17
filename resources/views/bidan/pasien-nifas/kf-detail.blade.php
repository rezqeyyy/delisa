<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Hasil KF {{ $jenisKf }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/bidan/sidebar-toggle.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-bidan.sidebar />
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
            
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('bidan.pasien-nifas.detail', $pasienNifas->id) }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                        Hasil Pemeriksaan KF {{ $jenisKf }}
                    </h1>
                    <p class="text-xs text-[#7C7C7C] mt-1">
                        Data diambil dari inputan Puskesmas/RS
                    </p>
                </div>
            </div>

            <section class="bg-white rounded-3xl p-4 sm:p-6 shadow-sm">
                <div class="mb-4 pb-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-base font-semibold text-[#1D1D1D]">Detail Kunjungan</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        Selesai
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="text-sm text-gray-500 font-medium">Tanggal Kunjungan</div>
                        <div class="text-sm text-[#1D1D1D] sm:col-span-2 font-semibold">
                            {{ \Carbon\Carbon::parse($existingKf->tanggal_kunjungan)->format('d F Y, H:i') }} WIB
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="text-sm text-gray-500 font-medium">Tekanan Darah</div>
                        <div class="text-sm text-[#1D1D1D] sm:col-span-2">
                            <span class="font-semibold text-lg">{{ $existingKf->sbp }}/{{ $existingKf->dbp }}</span> mmHg
                            <span class="text-gray-400 text-xs ml-2">(MAP: {{ $existingKf->map }})</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="text-sm text-gray-500 font-medium">Keadaan Umum</div>
                        <div class="text-sm text-[#1D1D1D] sm:col-span-2">
                            {{ $existingKf->keadaan_umum ?? '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="text-sm text-gray-500 font-medium">Tanda Bahaya/Penyulit</div>
                        <div class="text-sm text-[#1D1D1D] sm:col-span-2">
                            @if($existingKf->tanda_bahaya)
                                <span class="text-red-600 font-medium">{{ $existingKf->tanda_bahaya }}</span>
                            @else
                                <span class="text-gray-400">Tidak ada</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center bg-gray-50 p-3 rounded-lg mt-2">
                        <div class="text-sm text-gray-500 font-medium">Kesimpulan Akhir</div>
                        <div class="text-sm sm:col-span-2">
                            @if(strtolower($existingKf->kesimpulan_pantauan) == 'sehat')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Ibu & Bayi Sehat
                                </span>
                            @elseif(in_array(strtolower($existingKf->kesimpulan_pantauan), ['meninggal', 'wafat']))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Meninggal / Wafat
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    {{ $existingKf->kesimpulan_pantauan }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($existingKf->catatan)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-sm text-gray-500 font-medium mb-1">Catatan Tambahan</div>
                        <p class="text-sm text-gray-700 italic">
                            "{{ $existingKf->catatan }}"
                        </p>
                    </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-end">
                    <a href="{{ route('bidan.pasien-nifas.detail', $pasienNifas->id) }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-300 transition">
                        Kembali
                    </a>
                </div>
            </section>

        </main>
    </div>
</body>
</html>