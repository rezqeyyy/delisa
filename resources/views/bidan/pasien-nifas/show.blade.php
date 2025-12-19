<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Detail Pasien Nifas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/bidan/sidebar-toggle.js', 'resources/js/bidan/delete-confirm.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-bidan.sidebar />
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
            @php
                $statusType = $pasienNifas->status_type ?? 'normal';
                $statusDisplay = $pasienNifas->status_display ?? 'Tidak Berisiko';
                $badgeClass = match ($statusType) {
                    'beresiko' => 'bg-red-100 text-red-700 border-red-200',
                    'waspada' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                };
                $anakPertama = $anakPasien->first();
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="mb-6 flex items-center gap-3">
                    <a href="{{ route('bidan.pasien-nifas') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h1>
                        <p class="text-l text-[#7C7C7C]">
                            Informasi singkat dan riwayat KF pasien
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $badgeClass }}">
                        <span class="text-xs font-semibold">{{ $statusDisplay }}</span>
                    </div>
                    <!-- <a href="{{ route('bidan.pasien-nifas.anak.create', $pasienNifas->id) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        <span>Tambah Data Anak</span>
                    </a> -->
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Pasien</h2>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        {{-- Nama --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Nama</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $pasienNifas->pasien->user->name ?? 'N/A' }}
                        </div>

                        {{-- NIK --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">NIK</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $pasienNifas->pasien->nik ?? 'N/A' }}
                        </div>

                        {{-- No. Telepon --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">No. Telepon</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ optional(optional($pasienNifas->pasien)->user)->phone ?? optional($pasienNifas->pasien)->no_telepon ?? '-' }}
                        </div>

                        {{-- Tanggal Melahirkan --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Melahirkan</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $pasienNifas->tanggal_mulai_nifas ? \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Status KF -->
                <div class="lg:col-span-2 flex flex-col gap-6">
                     <!-- Status KF Card -->
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 flex-1">
                        <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Status Kunjungan Nifas (KF)</h2>

                        @if (!is_null($deathKe ?? null))
                            <div
                                class="bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm rounded-2xl px-4 py-3 mb-4">
                                <div class="font-semibold mb-1">
                                    Perhatian: Pasien tercatat meninggal/wafat pada KF{{ $deathKe }}.
                                </div>
                                <div>
                                    Seluruh kunjungan nifas setelah KF{{ $deathKe }} tidak dapat dilakukan lagi. Tombol
                                    pencatatan KF di atas KF{{ $deathKe }}
                                    akan dinonaktifkan secara otomatis.
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                            @foreach ([1, 2, 3, 4] as $jk)
                                @php
                                    $isDone = isset($kfDoneByJenis[$jk]);
                                    $deathKeVal = $deathKe ?? null;
                                    $isBlockedByDeath = !is_null($deathKeVal) && $jk > (int) $deathKeVal;

                                    // ✅ GATE dari model (ini yang menentukan "MENUNGGU")
                                    $statusKf = $pasienNifas->getKfStatus((int) $jk); // selesai | belum_mulai | dalam_periode | terlambat
                                    $canDoKf = $pasienNifas->canDoKf((int) $jk); // true kalau dalam_periode/terlambat
                                    $mulaiKf = $pasienNifas->getKfMulai((int) $jk);
                                @endphp


                                <div class="rounded-2xl border
                                    @if ($isBlockedByDeath) bg-gray-50 border-gray-300
                                    @elseif($isDone)
                                        bg-emerald-50 border-emerald-200
                                    @else
                                        bg-white border-[#E5E5E5] @endif 
                                    p-4">

                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold">KF{{ $jk }}</h3>
                                        <span
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs
                                    @if ($isDone) bg-emerald-100 text-emerald-700 border border-emerald-200
                                    @elseif($isBlockedByDeath)
                                        bg-gray-200 text-gray-600 border border-gray-300
                                    @else
                                        bg-gray-100 text-gray-600 border border-gray-200 @endif
                                ">
                                            @if ($isDone)
                                                ✓
                                            @elseif($isBlockedByDeath)
                                                ×
                                            @else
                                                ?
                                            @endif
                                        </span>
                                    </div>

                                    <div class="mt-2 text-xs text-[#1D1D1D]">
                                        @if ($isDone && isset($kfDoneByJenis[$jk]->last_date))
                                            Selesai:
                                            {{ \Carbon\Carbon::parse($kfDoneByJenis[$jk]->last_date)->format('d/m/Y') }}
                                        @elseif($isBlockedByDeath)
                                            Tidak dapat dilakukan karena pasien tercatat meninggal/wafat pada
                                            KF{{ $deathKeVal }}.
                                        @else
                                            Status: Belum dicatat
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        @if ($firstAnakId)
                                            @if ($isDone)
                                                {{-- DATA ADA: Tampilkan tombol LIHAT HASIL --}}
                                                <a href="{{ route('bidan.pasien-nifas.kf-anak.form', ['id' => $pasienNifas->id, 'anakId' => $firstAnakId, 'jenisKf' => $jk]) }}"
                                                    class="inline-flex items-center rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-100 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat Hasil
                                                </a>

                                            @elseif ($isBlockedByDeath)
                                                {{-- KASUS MENINGGAL: Disable --}}
                                                <button type="button"
                                                    class="inline-flex items-center rounded-full bg-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 cursor-not-allowed"
                                                    disabled>
                                                    KF{{ $jk }} Terhenti
                                                </button>

                                            @else
                                                {{-- BELUM ADA DATA: Disable (Tunggu Puskesmas) --}}
                                                <button type="button"
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-400 border border-gray-200 cursor-not-allowed"
                                                    disabled
                                                    title="Data belum diinput oleh Puskesmas">
                                                    Belum ada data Puskesmas
                                                </button>
                                            @endif
                                        @else
                                            <button type="button"
                                                class="inline-flex items-center rounded-full bg-gray-200 px-4 py-2 text-xs font-semibold text-gray-600"
                                                disabled>
                                                Data anak belum disinkron
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Timeline & Info -->
                <div class="flex flex-col gap-6">
                    <!-- Info Periode KF -->
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 flex-1">
                        <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Informasi Periode KF</h2>
                        <div class="space-y-3">
                            <div>
                                <h4 class="font-medium text-[#1D1D1D] text-sm">KF1</h4>
                                <p class="text-xs text-[#7C7C7C]">6 jam - 2 hari setelah melahirkan</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#1D1D1D] text-sm">KF2</h4>
                                <p class="text-xs text-[#7C7C7C]">Hari ke-3 - ke-7 setelah melahirkan</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#1D1D1D] text-sm">KF3</h4>
                                <p class="text-xs text-[#7C7C7C]">Hari ke-8 - ke-28 setelah melahirkan</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#1D1D1D] text-sm">KF4</h4>
                                <p class="text-xs text-[#7C7C7C]">Hari ke-29 - ke-42 setelah melahirkan</p>
                            </div>
                        </div>
                    </div>
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