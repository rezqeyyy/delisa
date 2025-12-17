<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Pasien NIFAS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/bidan/sidebar-toggle.js', 'resources/js/bidan/delete-confirm.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-bidan.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1D1D1D]">Pasien Nifas</h1>
                    <p class="text-[#7C7C7C] mt-1">Data pasien nifas pada bidan ini</p>
                </div>
            </div>

            {{-- Alert session (tetap bidan, tidak mengubah fungsi) --}}
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Konten Pasien Nifas -->
            <div class="flex-1 flex flex-col">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Total Pasien Nifas</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $totalPasienNifas ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Sudah KFI</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-lg text-[#1D1D1D]">Belum KFI</h3>
                            <div class="w-8 h-8 rounded-lg bg-[#F5F5F5] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1D1D1D]">{{ $belumKFI ?? 0 }}</div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 flex-1">
                    <!-- Header Table -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-[#1D1D1D]">List Pasien Nifas</h2>
                        <div class="flex gap-2">
                            <div class="relative">
                                <form method="GET" action="{{ route('bidan.pasien-nifas') }}">
                                    <div class="relative">
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Cari nama pasien..."
                                            class="pl-9 pr-4 py-2 border border-[#D9D9D9] rounded-full text-sm w-64
                                                   focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                        <span class="absolute inset-y-0 left-3 flex items-center">
                                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}"
                                                class="w-4 h-4 opacity-60" alt="Search">
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[1250px]">
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-4 py-3 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Nama Pasien</th>
                                    <th class="px-4 py-3 font-semibold">NIK</th>
                                    <th class="px-4 py-3 font-semibold">No Telp</th>
                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal Mulai Nifas</th>

                                    <th class="px-4 py-3 font-semibold">Alamat</th>
                                    <th class="px-4 py-3 font-semibold">Asal Data</th>
                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Status KF</th>
                                    <th class="px-4 py-3 font-semibold">Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse($pasienNifas as $pasien)
                                    @php
                                        $no =
                                            method_exists($pasienNifas, 'firstItem') && $pasienNifas->firstItem()
                                                ? $pasienNifas->firstItem() + $loop->index
                                                : $loop->iteration;

                                        $asalLabel = $pasien->asal_data_label ?? 'BIDAN';

                                        // ✅ Ambil model episode bidan supaya bisa pakai getKfStatus/canDoKf
                                        $episodeBidan = \App\Models\PasienNifasBidan::find($pasien->id);

                                        // Default jika record tidak ketemu (harusnya nggak kejadian)
                                        $nextKe = 1;
                                        $statusLabel = 'Perlu KF';
                                        $statusClass = 'bg-amber-100 text-amber-800';

                                        if ($episodeBidan) {
                                            // Tentukan "KF berikutnya" = KF pertama yang belum selesai
                                            $nextKe = 1;
                                            for ($k = 1; $k <= 4; $k++) {
                                                if (!$episodeBidan->isKfSelesai($k)) {
                                                    $nextKe = $k;
                                                    break;
                                                }
                                                if ($k === 4) {
                                                    $nextKe = 4;
                                                } // fallback
                                            }

                                            // Jika semua selesai
                                            $allDone =
                                                $episodeBidan->isKfSelesai(1) &&
                                                $episodeBidan->isKfSelesai(2) &&
                                                $episodeBidan->isKfSelesai(3) &&
                                                $episodeBidan->isKfSelesai(4);

                                            if ($allDone) {
                                                $statusLabel = 'Semua KF Selesai';
                                                $statusClass = 'bg-emerald-100 text-emerald-800';
                                            } else {
                                                $st = $episodeBidan->getKfStatus($nextKe); // selesai|belum_mulai|dalam_periode|terlambat

                                                if ($st === 'belum_mulai') {
                                                    $mulai = $episodeBidan->getKfMulai($nextKe);
                                                    $statusLabel = $mulai
                                                        ? 'Menunggu jadwal KF' .
                                                            $nextKe .
                                                            ' (' .
                                                            $mulai->format('d/m/Y H:i') .
                                                            ')'
                                                        : 'Menunggu jadwal KF' . $nextKe;
                                                    $statusClass = 'bg-gray-100 text-gray-700';
                                                } elseif ($st === 'dalam_periode') {
                                                    $statusLabel = 'Dalam periode KF' . $nextKe;
                                                    $statusClass = 'bg-amber-100 text-amber-800';
                                                } elseif ($st === 'terlambat') {
                                                    $statusLabel = 'Terlambat KF' . $nextKe;
                                                    $statusClass = 'bg-red-100 text-red-800';
                                                } else {
                                                    // fallback
                                                    $statusLabel = 'Perlu KF';
                                                    $statusClass = 'bg-amber-100 text-amber-800';
                                                }
                                            }
                                        }

                                        // ✅ tanggal mulai nifas tampilkan pakai datetime (biar konsisten dengan gate)
                                        $tglMulai = !empty($pasien->tanggal)
                                            ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y H:i')
                                            : '-';
                                    @endphp


                                    <tr class="hover:bg-[#FAFAFA]">
                                        <!-- NO -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $no }}
                                        </td>

                                        <!-- NAMA PASIEN -->
                                        <td class="px-4 py-3 font-medium text-[#1D1D1D]">
                                            {{ $pasien->nama_pasien ?? '-' }}
                                        </td>

                                        <!-- NIK -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $pasien->nik ?? '-' }}
                                        </td>

                                        <!-- NO TELP -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $pasien->telp ?? '-' }}
                                        </td>

                                        <!-- TANGGAL MULAI NIFAS -->
                                        <td class="px-4 py-3 text-[#7C7C7C]">
                                            {{ $tglMulai }}
                                        </td>

                                        <!-- ALAMAT -->
                                        <td class="px-4 py-3 text-[#7C7C7C] max-w-xs truncate">
                                            {{ $pasien->alamat ?? ($pasien->kelurahan ?? '-') }}
                                        </td>

                                        <!-- ASAL DATA -->
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {{ $asalLabel }}
                                            </span>
                                        </td>

                                        <!-- STATUS KF -->
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <!-- ACTION -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">

                                                <!-- Tombol Detail -->
                                                @if (Route::has('bidan.pasien-nifas.detail'))
                                                    <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                                        class="p-1.5 rounded-lg border border-[#D9D9D9] hover:bg-[#F5F5F5] transition-colors"
                                                        title="Detail Pasien">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4 text-[#7C7C7C]" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="Route detail belum tersedia">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4 text-gray-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </span>
                                                @endif

                                                <!-- Tombol KF1-KF4 (logic tetap max_ke/next_ke, style mengikuti puskesmas) -->
                                                @foreach ([1, 2, 3, 4] as $jk)
                                                    @php
                                                        $isDone = $episodeBidan
                                                            ? $episodeBidan->isKfSelesai($jk)
                                                            : false;

                                                        // KF yang boleh diisi = hanya "nextKe" dan statusnya bukan belum_mulai
                                                        $isNext = $jk === (int) $nextKe && !$isDone;

                                                        $canDo = $episodeBidan ? $episodeBidan->canDoKf($jk) : false; // true hanya untuk dalam_periode/terlambat
                                                        $st = $episodeBidan ? $episodeBidan->getKfStatus($jk) : null;

                                                        if ($isDone) {
                                                            $btnClass = 'border-green-300 bg-green-50 cursor-default';
                                                            $textClass = 'text-green-600';
                                                            $title = "KF{$jk} Sudah Selesai";
                                                        } elseif ($isNext && $canDo) {
                                                            $btnClass =
                                                                'border-amber-300 bg-amber-50 hover:bg-amber-100';
                                                            $textClass = 'text-amber-600';
                                                            $title = "Isi KF{$jk} (melalui detail)";
                                                        } else {
                                                            $btnClass = 'border-gray-300 bg-gray-50 cursor-not-allowed';
                                                            $textClass = 'text-gray-400';

                                                            if ($isNext && $st === 'belum_mulai') {
                                                                $mulai = $episodeBidan
                                                                    ? $episodeBidan->getKfMulai($jk)
                                                                    : null;
                                                                $title = $mulai
                                                                    ? "KF{$jk} masih MENUNGGU. Mulai: " .
                                                                        $mulai->format('d/m/Y H:i')
                                                                    : "KF{$jk} masih MENUNGGU";
                                                            } else {
                                                                $title = "KF{$jk} Belum Tersedia";
                                                            }
                                                        }
                                                    @endphp

                                                    @if ($isDone)
                                                        <span class="p-1.5 rounded-lg border {{ $btnClass }}"
                                                            title="{{ $title }}">
                                                            <span
                                                                class="text-xs font-bold {{ $textClass }}">✓</span>
                                                        </span>
                                                    @elseif($isNext && $canDo)
                                                        @if (Route::has('bidan.pasien-nifas.detail'))
                                                            <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                                                class="p-1.5 rounded-lg border {{ $btnClass }} transition-colors"
                                                                title="{{ $title }}">
                                                                <span
                                                                    class="text-xs font-bold {{ $textClass }}">KF{{ $jk }}</span>
                                                            </a>
                                                        @else
                                                            <span
                                                                class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                                title="Route detail belum tersedia">
                                                                <span
                                                                    class="text-xs font-bold text-gray-400">KF{{ $jk }}</span>
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="p-1.5 rounded-lg border {{ $btnClass }}"
                                                            title="{{ $title }}">
                                                            <span
                                                                class="text-xs font-bold {{ $textClass }}">KF{{ $jk }}</span>
                                                        </span>
                                                    @endif
                                                @endforeach


                                                <!-- Tombol Hapus (tetap delete-confirm.js bidan) -->
                                                <!-- @if (Route::has('bidan.pasien-nifas.destroy'))
                                                    <form
                                                        action="{{ route('bidan.pasien-nifas.destroy', $pasien->id) }}"
                                                        method="POST" class="inline js-delete-skrining-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="js-delete-skrining-btn p-1.5 rounded-lg border border-red-300 bg-red-50 hover:bg-red-100 transition-colors"
                                                            title="Hapus Pasien">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="w-4 h-4 text-red-600" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span
                                                        class="p-1.5 rounded-lg border border-gray-300 bg-gray-50 cursor-not-allowed"
                                                        title="Route hapus belum tersedia">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4 text-gray-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                                        </svg>
                                                    </span>
                                                @endif -->
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-6 text-center text-[#7C7C7C]">
                                            <div class="flex flex-col items-center justify-center py-8">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-12 h-12 text-[#D9D9D9] mb-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <p class="text-sm">Belum ada data pasien nifas</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($pasienNifas instanceof \Illuminate\Pagination\LengthAwarePaginator && $pasienNifas->hasPages())
                        <div class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-[#7C7C7C]">
                                Menampilkan {{ $pasienNifas->firstItem() ?? 0 }} - {{ $pasienNifas->lastItem() ?? 0 }}
                                dari {{ $pasienNifas->total() }} data pasien
                            </div>
                            <div class="flex gap-1">
                                @if ($pasienNifas->onFirstPage())
                                    <span
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Sebelumnya</span>
                                @else
                                    <a href="{{ $pasienNifas->previousPageUrl() }}"
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Sebelumnya</a>
                                @endif

                                @if ($pasienNifas->hasMorePages())
                                    <a href="{{ $pasienNifas->nextPageUrl() }}"
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#1D1D1D] text-sm hover:bg-[#F5F5F5]">Selanjutnya</a>
                                @else
                                    <span
                                        class="px-3 py-1 rounded-lg border border-[#E9E9E9] text-[#7C7C7C] text-sm">Selanjutnya</span>
                                @endif
                            </div>
                        </div>
                    @endif
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
