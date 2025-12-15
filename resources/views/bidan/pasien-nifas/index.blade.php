<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Pasien NIFAS</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/bidan/sidebar-toggle.js',
        'resources/js/bidan/delete-confirm.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">

    <x-bidan.sidebar />

    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

        <header class="mb-2">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-[#1D1D1D]">List Pasien Nifas</h1>
        </header>

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

        <section class="space-y-4">
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Nifas</h2>
                        <p class="text-xs text-[#7C7C7C]">Data pasien nifas pada puskesmas ini</p>
                    </div>

                    <a href="{{ route('bidan.pasien-nifas.create') }}"
                       class="px-5 py-2 rounded-full bg-[#FF5BAE] text-white font-semibold hover:bg-[#E91E8C] transition-colors">
                        + Tambah Pasien
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-sm border-collapse">

                        <thead class="border-b border-[#EFEFEF] bg-[#FFF7FC] font-semibold">
                        <tr class="text-left text-[#1D1D1D]">
                            <th class="px-4 py-3 font-semibold w-[70px]">No</th>
                            <th class="px-4 py-3 font-semibold">Nama Pasien</th>
                            <th class="px-4 py-3 font-semibold">NIK</th>
                            <th class="px-4 py-3 font-semibold">No Telp</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Mulai NIFAS</th>
                            <th class="px-4 py-3 font-semibold">Alamat</th>
                            <th class="px-4 py-3 font-semibold">Asal Data</th>
                            <th class="px-4 py-3 font-semibold">Status KF</th>
                            <th class="px-4 py-3 font-semibold">Action</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($pasienNifas as $pasien)
                            @php
                                $no = method_exists($pasienNifas, 'firstItem') && $pasienNifas->firstItem()
                                    ? $pasienNifas->firstItem() + $loop->index
                                    : $loop->iteration;

                                $asalLabel = 'RS: Rumah sakit 1'; // sementara (biar match UI). Nanti bisa dibuat dinamis.

                                $statusLabel = $pasien->peringat_label ?? 'Perlu KF';
                                $statusClass = $pasien->badge_class ?? null;

                                if (!$statusClass) {
                                    $state = $pasien->peringat_state ?? 'early';
                                    if ($state === 'late') $statusClass = 'bg-red-100 text-red-800 border border-red-200';
                                    elseif ($state === 'window') $statusClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                                    elseif ($state === 'done') $statusClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                    elseif ($state === 'no_date') $statusClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                                    else $statusClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                                }

                                $maxKe = (int) ($pasien->max_ke ?? 0);
                                $nextKe = (int) ($pasien->next_ke ?? 1);

                                $tglMulai = isset($pasien->tanggal)
                                    ? \Carbon\Carbon::parse($pasien->tanggal)->format('d/m/Y')
                                    : '-';
                            @endphp

                            <tr class="hover:bg-[#FAFAFA] align-middle">
                                <td class="px-4 py-3 font-medium">{{ $no }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pasien->nama_pasien ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pasien->nik ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pasien->telp ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $tglMulai }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pasien->alamat ?? $pasien->kelurahan ?? '-' }}</td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-[#EAF1FF] px-3 py-1 text-xs font-semibold text-[#1A4FD8]">
                                        <span class="w-2 h-2 rounded-full bg-[#1A4FD8]"></span>
                                        {{ $asalLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-2">

                                        {{-- Baris Action utama: tambah anak, view, hapus --}}
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if (Route::has('bidan.pasien-nifas.anak.create'))
                                                <a href="{{ route('bidan.pasien-nifas.anak.create', $pasien->id) }}"
                                                   class="px-3 py-1.5 rounded-full border border-[#E5E5E5] text-xs hover:bg-gray-50">
                                                    Tambah Data Anak
                                                </a>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" disabled>
                                                    Tambah Data Anak
                                                </button>
                                            @endif

                                            @if (Route::has('bidan.pasien-nifas.detail'))
                                                <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                                   class="px-3 py-1.5 rounded-full border border-[#E5E5E5] text-xs hover:bg-gray-50">
                                                    View
                                                </a>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" disabled>
                                                    View
                                                </button>
                                            @endif

                                            @if (Route::has('bidan.pasien-nifas.destroy'))
                                                <form action="{{ route('bidan.pasien-nifas.destroy', $pasien->id) }}"
                                                      method="POST"
                                                      class="inline js-delete-skrining-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                            class="js-delete-skrining-btn px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-500 text-xs" disabled>
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Baris tombol KF1-KF4 (ngisi KF jangan diilangin) --}}
                                        <div class="inline-flex items-center gap-2 flex-wrap">
                                            @foreach([1,2,3,4] as $jk)
                                                @php
                                                    $chipClass = 'bg-white text-gray-500 border-[#E5E5E5]';
                                                    if ($jk <= $maxKe) $chipClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
                                                    elseif ($jk === $nextKe) $chipClass = 'bg-amber-50 border-amber-200 text-amber-800';
                                                @endphp

                                                {{-- Aman: arahkan ke detail dulu (di detail baru masuk form KF per anak) --}}
                                                @if (Route::has('bidan.pasien-nifas.detail'))
                                                    <a href="{{ route('bidan.pasien-nifas.detail', $pasien->id) }}"
                                                       class="px-3 py-1.5 rounded-full border text-xs font-semibold {{ $chipClass }}"
                                                       title="Isi KF{{ $jk }} (melalui detail)">
                                                        KF{{ $jk }}
                                                    </a>
                                                @else
                                                    <span class="px-3 py-1.5 rounded-full border text-xs font-semibold {{ $chipClass }}">
                                                        KF{{ $jk }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-[#7C7C7C]">
                                    <p class="text-sm">Belum ada data pasien nifas</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pasienNifas->hasPages())
                    <div class="mt-6 flex items-center justify-end">
                        <div class="inline-flex items-center gap-2 text-sm">
                            @if($pasienNifas->onFirstPage())
                                <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] text-gray-400 bg-[#FAFAFA]">Previous</span>
                            @else
                                <a href="{{ $pasienNifas->previousPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">Previous</a>
                            @endif

                            @php($last = $pasienNifas->lastPage())
                            @php($current = $pasienNifas->currentPage())

                            @for($i = max(1, $current - 1); $i <= min($last, $current + 1); $i++)
                                @if($i === $current)
                                    <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] bg-[#FAFAFA] font-semibold">{{ $i }}</span>
                                @else
                                    <a href="{{ $pasienNifas->url($i) }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($pasienNifas->hasMorePages())
                                <a href="{{ $pasienNifas->nextPageUrl() }}" class="px-3 py-1 rounded-lg border border-[#E5E5E5] hover:bg-[#FAFAFA]">Next</a>
                            @else
                                <span class="px-3 py-1 rounded-lg border border-[#E5E5E5] text-gray-400 bg-[#FAFAFA]">Next</span>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </section>

        <footer class="text-center text-xs text-[#7C7C7C] py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </main>
</div>
</body>
</html>
