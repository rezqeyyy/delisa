<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien Nifas - Puskesmas</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/puskesmas/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            @php
                /** Samakan nama variabel dengan UI lama */
                $pasienNifas = $data;
                $type = $type ?? 'rs';
            @endphp

            <!-- Back Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('puskesmas.pasien-nifas.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-[#1D1D1D]">Detail Pasien Nifas</h1>
                    <p class="text-l text-[#7C7C7C]">
                        Informasi singkat dan riwayat KF pasien
                    </p>
                </div>
            </div>

            <!-- Alert Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-700">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Header dengan Tombol Download PDF -->
            <div class="flex items-center justify-between mb-6">
                <!-- Tombol Download PDF (akan muncul jika ada KF yang sudah dicatat) -->
                @if ($pasienNifas->kf1_tanggal || $pasienNifas->kf2_tanggal || $pasienNifas->kf3_tanggal || $pasienNifas->kf4_tanggal)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('puskesmas.pasien-nifas.all-kf.pdf', ['type' => $type, 'id' => $pasienNifas->id]) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" />
                                <path d="M9 15h6" />
                                <path d="M12 18V12" />
                            </svg>
                            Download Semua KF
                        </a>
                    </div>
                @endif
            </div>

            @if (!is_null($deathKe ?? null))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <div class="font-semibold mb-1">
                        Perhatian: pasien tercatat meninggal/wafat pada KF{{ $deathKe }}.
                    </div>
                    <div>
                        Seluruh kunjungan nifas setelah KF{{ $deathKe }} tidak dapat dilakukan lagi.
                        Tombol pencatatan KF di atas KF{{ $deathKe }} akan dinonaktifkan secara otomatis.
                    </div>
                </div>
            @endif
            
            <!-- Pasien Info Card -->
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
                            @if ($pasienNifas->tanggal_melahirkan)
                                {{ \Carbon\Carbon::parse($pasienNifas->tanggal_melahirkan)->format('d/m/Y') }}
                            @else
                                Belum diisi
                            @endif
                        </div>

                        {{-- Rumah Sakit --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Rumah Sakit</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                            {{ $pasienNifas->rs->nama ?? 'N/A' }}
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ([1, 2, 3, 4] as $jenisKf)
                                @php
                                    $status = $pasienNifas->getKfStatus($jenisKf);
                                    $badgeColor = $pasienNifas->getKfBadgeColor($jenisKf);
                                    $icon = $pasienNifas->getKfIcon($jenisKf);
                                    $tanggal = $pasienNifas->{"kf{$jenisKf}_tanggal"};
                                    $catatan = $pasienNifas->{"kf{$jenisKf}_catatan"};

                                    $deathKeVal = $deathKe ?? null;
                                    $isBlockedByDeath = !is_null($deathKeVal) && $jenisKf > (int) $deathKeVal;
                                @endphp

                                <div class="border rounded-xl p-4
                                        @if ($status == 'selesai') border-green-200 bg-green-50
                                        @elseif($isBlockedByDeath) border-gray-300 bg-gray-50
                                        @else border-[#E9E9E9] @endif
                                    ">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold text-[#1D1D1D]">KF{{ $jenisKf }}</h3>

                                        <span
                                            class="inline-block px-2 py-1 rounded text-sm font-medium
                                                @if ($badgeColor == 'success') bg-green-100 text-green-800
                                                @elseif($badgeColor == 'warning') bg-amber-100 text-amber-800
                                                @elseif($badgeColor == 'danger') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif
                                            ">
                                            @if ($isBlockedByDeath)
                                                {{-- sengaja kosong seperti UI lama --}}
                                            @else
                                                {{ $icon }}
                                            @endif
                                        </span>
                                    </div>

                                    @if ($status == 'selesai')
                                        <p class="text-sm text-[#7C7C7C] mb-1">
                                            <span class="font-medium">Selesai:</span>
                                            {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y H:i') }}
                                        </p>

                                        @if ($catatan)
                                            <p class="text-sm text-[#7C7C7C] mb-3">
                                                <span class="font-medium">Catatan:</span>
                                                {{ \Illuminate\Support\Str::limit($catatan, 100) }}
                                            </p>
                                        @endif
                                    @else
                                        <p class="text-sm text-[#7C7C7C] mb-1">
                                            <span class="font-medium">Status:</span>

                                            @if ($isBlockedByDeath)
                                                <span class="text-red-600 font-semibold">
                                                    Tidak dapat dilakukan (pasien wafat pada KF{{ $deathKeVal }})
                                                </span>
                                            @elseif($status == 'dalam_periode')
                                                <span class="text-amber-600">Dalam Periode</span>
                                            @elseif($status == 'terlambat')
                                                <span class="text-red-600 font-semibold">TERLAMBAT</span>
                                            @elseif($status == 'belum_mulai')
                                                <span class="text-gray-600">Belum Mulai</span>
                                            @else
                                                <span class="text-gray-400">Tidak Diketahui</span>
                                            @endif
                                        </p>

                                        @php
                                            $deadline = $pasienNifas->getKfDeadline($jenisKf);
                                        @endphp

                                        @if ($deadline && !$isBlockedByDeath)
                                            <p class="text-sm text-[#7C7C7C]">
                                                <span class="font-medium">Deadline:</span>
                                                {{ $deadline->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    @endif

                                    @if (!$pasienNifas->isKfSelesai($jenisKf))
                                        @php
                                            $statusBtn = $pasienNifas->getKfStatus($jenisKf);
                                            $isDisabled = $statusBtn == 'belum_mulai' || $isBlockedByDeath;
                                            $tooltip = '';

                                            if ($statusBtn == 'belum_mulai') {
                                                $mulaiBtn = $pasienNifas->getKfMulai($jenisKf);
                                                $tooltip = $mulaiBtn
                                                    ? 'Dapat dilakukan mulai ' . $mulaiBtn->format('d/m/Y H:i')
                                                    : 'Belum dapat dilakukan';
                                            }

                                            if ($isBlockedByDeath) {
                                                $tooltip = "KF{$jenisKf} tidak dapat dilakukan karena pada KF{$deathKeVal} pasien sudah tercatat meninggal/wafat.";
                                            }
                                        @endphp

                                        <div class="mt-3">
                                            @if ($isDisabled)
                                                <button
                                                    class="inline-block px-3 py-1 text-sm
                                                        @if ($isBlockedByDeath) bg-gray-300 text-gray-600
                                                        @else bg-gray-300 text-gray-500 @endif
                                                        rounded-lg cursor-not-allowed"
                                                    title="{{ $tooltip }}" disabled
                                                >
                                                    @if ($isBlockedByDeath)
                                                        KF{{ $jenisKf }} terkunci
                                                    @else
                                                        Catat KF{{ $jenisKf }}
                                                    @endif
                                                </button>
                                            @else
                                                <a href="{{ route('puskesmas.pasien-nifas.form-kf', ['type' => $type, 'id' => $pasienNifas->id, 'jenisKf' => $jenisKf]) }}"
                                                    class="inline-block px-3 py-1 text-sm bg-[#B9257F] text-white rounded-lg hover:bg-[#9D1B6A] transition-colors">
                                                    Catat KF{{ $jenisKf }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Timeline & Info -->
                <div class="flex flex-col gap-6">
                    <!-- Timeline KF Card -->
                    @if ($pasienNifas->kf1_tanggal || $pasienNifas->kf2_tanggal || $pasienNifas->kf3_tanggal || $pasienNifas->kf4_tanggal)
                        <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                            <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Timeline KF</h2>
                            <div class="space-y-4">
                                @foreach([1,2,3,4] as $k)
                                    @php
                                        $tgl = $pasienNifas->{"kf{$k}_tanggal"};
                                        $cat = $pasienNifas->{"kf{$k}_catatan"};
                                    @endphp

                                    @if($tgl)
                                        <div class="relative pl-8 mb-6 before:content-[''] before:absolute before:left-0 before:top-2 before:w-4 before:h-4 before:rounded-full before:bg-blue-500">
                                            <div class="py-2">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-semibold text-[#1D1D1D]">KF{{ $k }}</h4>
                                                        <p class="text-sm text-[#7C7C7C]">
                                                            {{ \Carbon\Carbon::parse($tgl)->format('d/m/Y H:i') }}
                                                        </p>
                                                    </div>
                                                    <span class="inline-block px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                                                        Selesai
                                                    </span>
                                                </div>

                                                @if($cat)
                                                    <p class="text-sm text-[#7C7C7C] mt-2">{{ $cat }}</p>
                                                @endif

                                                <div class="mt-3">
                                                    <a href="{{ route('puskesmas.pasien-nifas.kf.pdf', ['type' => $type, 'id' => $pasienNifas->id, 'jenisKf' => $k]) }}"
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm border border-[#E9E9E9] rounded-lg hover:bg-[#F5F5F5] transition-colors">
                                                        Download PDF KF{{ $k }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

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