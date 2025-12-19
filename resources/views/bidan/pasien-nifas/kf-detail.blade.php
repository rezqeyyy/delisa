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

            <div class="flex items-center gap-3">
                <a href="{{ route('bidan.pasien-nifas.detail', $pasienNifas->id) }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-[#1D1D1D]">Hasil Pemeriksaan KF {{ $jenisKf }}</h1>
                    <p class="text-l text-[#7C7C7C]">
                        Data diambil dari inputan Puskesmas/RS
                    </p>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="flex justify-between items-center">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Pasien</h2>

                    @php
                        $kes = strtolower(trim($existingKf->kesimpulan_pantauan ?? ''));

                        if (in_array($kes, ['meninggal', 'wafat'])) {
                            $badgeText  = 'Meninggal / Wafat';
                            $badgeClass = 'bg-red-100 text-red-700';
                        } elseif ($kes === 'sehat') {
                            $badgeText  = 'Sehat';
                            $badgeClass = 'bg-blue-100 text-blue-700';
                        } elseif ($kes === 'dirujuk') {
                            $badgeText  = 'Dirujuk';
                            $badgeClass = 'bg-amber-100 text-amber-800';
                        } elseif ($kes !== '') {
                            $badgeText  = $existingKf->kesimpulan_pantauan; // fallback tampilkan value as-is
                            $badgeClass = 'bg-amber-100 text-amber-800';
                        } else {
                            $badgeText  = 'Belum ada kesimpulan';
                            $badgeClass = 'bg-gray-100 text-gray-600';
                        }
                    @endphp

                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                        {{ $badgeText }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Kunjungan</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($existingKf->tanggal_kunjungan)->format('d F Y') }} 
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tekanan Darah</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $existingKf->sbp }}/{{ $existingKf->dbp }} mmHg
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">MAP</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $existingKf->map }} mmHg
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Keadaan Umum</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $existingKf->keadaan_umum ?? '-' }}
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanda Bahaya</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            @if($existingKf->tanda_bahaya)
                                <span class="text-red-600 font-medium">{{ $existingKf->tanda_bahaya }}</span>
                            @else
                                <span class="text-gray-400">Tidak ada</span>
                            @endif
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kesimpulan</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $existingKf->kesimpulan_pantauan ?? '-' }}
                        </div>
                    </div>
                </div>                

                <div class="mt-6 flex items-center justify-end gap-4">
                    {{-- Tombol Kembali ke list --}}
                    <a href="{{ route('bidan.skrining') }}"
                        class="rounded-lg border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                        Kembali
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>

        </main>
    </div>
</body>
</html>
