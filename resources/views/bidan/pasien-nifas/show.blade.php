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
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                            Data Pasien Nifas — {{ $pasienNifas->pasien->user->name ?? 'N/A' }}
                        </h1>
                        <p class="text-xs text-[#7C7C7C] mt-1">Ringkasan nifas ibu, riwayat persalinan, dan kondisi bayi
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

            <section class="bg-white rounded-3xl p-4 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Ringkasan Data Pasien</h2>
                </div>
                <div class="bg-white rounded-1xl shadow-sm overflow-hidden">
                    <div class="grid grid-cols-2 text-xs sm:text-sm font-semibold bg-[#FDECF5] ">
                        <div class="px-4 sm:px-6 py-3">Informasi</div>
                        <div class="px-4 sm:px-6 py-3">Data</div>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 font-semibold">Tanggal Mulai Nifas</div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $pasienNifas->tanggal_mulai_nifas ? \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 font-semibold">Nama</div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->user->name ?? '-' }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 font-semibold">NIK</div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->nik ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 font-semibold">Nomor Telepon</div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->user->phone ?? '-' }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-6 py-3 font-semibold">Alamat</div>
                            <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                {{ $pasienNifas->pasien->PProvinsi ?? '-' }},
                                {{ $pasienNifas->pasien->PKabupaten ?? '-' }},
                                {{ $pasienNifas->pasien->PKecamatan ?? '-' }},
                                {{ $pasienNifas->pasien->PWilayah ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-3xl p-4 sm:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Detail Anak</h2>
                </div>
                <div class="bg-white rounded-1xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs sm:text-sm">
                            <thead class="bg-[#FFF7FC]">
                                <tr class="text-left">
                                    <th class="px-4 py-2 font-semibold">Anak Ke</th>
                                    <th class="px-4 py-2 font-semibold">Nama</th>
                                    <th class="px-4 py-2 font-semibold">Jenis Kelamin</th>
                                    <th class="px-4 py-2 font-semibold">Tanggal Lahir</th>
                                    <th class="px-4 py-2 font-semibold">Berat (gram)</th>
                                    <th class="px-4 py-2 font-semibold">Panjang (cm)</th>
                                    <th class="px-4 py-2 font-semibold">Lingkar Kepala (cm)</th>
                                    <th class="px-4 py-2 font-semibold">Kondisi Ibu</th>
                                    <th class="px-4 py-2 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="min-w-full text-xs sm:text-sm">
                                @forelse($anakPasien as $anak)
                                    <tr>
                                        <td class="px-4 py-2">{{ $anak->anak_ke }}</td>
                                        <td class="px-4 py-2">{{ $anak->nama_anak }}</td>
                                        <td class="px-4 py-2">{{ $anak->jenis_kelamin }}</td>
                                        <td class="px-4 py-2">
                                            {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2">{{ $anak->berat_lahir_anak }}</td>
                                        <td class="px-4 py-2">{{ $anak->panjang_lahir_anak }}</td>
                                        <td class="px-4 py-2">{{ $anak->lingkar_kepala_anak }}</td>
                                        <td class="px-4 py-2">{{ $anak->kondisi_ibu ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                                <a href="{{ route('bidan.pasien-nifas.anak.edit', ['id' => $pasienNifas->id, 'anakId' => $anak->id]) }}"
                                                    class="px-3 py-1.5 rounded-full border text-xs border-[#E5E5E5]">Edit</a>
                                                <form
                                                    action="{{ route('bidan.pasien-nifas.anak.destroy', ['id' => $pasienNifas->id, 'anakId' => $anak->id]) }}"
                                                    method="POST" class="inline js-delete-skrining-form"
                                                    data-delete-title="Hapus Data Anak?"
                                                    data-delete-desc="Tindakan ini tidak bisa dibatalkan.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="js-delete-skrining-btn px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-4 text-center text-[#7C7C7C]">Belum ada data
                                            anak.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </section>

            <section class="bg-white rounded-3xl p-4 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Status Kunjungan Nifas (KF)</h2>
                </div>
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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


                        <div
                            class="rounded-2xl border
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
                                    {{ \Carbon\Carbon::parse($kfDoneByJenis[$jk]->last_date)->format('d/m/Y H:i') }}
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

            </section>

            <section class="bg-white rounded-3xl p-4 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Timeline KF</h2>
                </div>
                <div class="space-y-3">
                    @foreach ([1, 2, 3, 4] as $jk)
                        <div class="flex items-center justify-between rounded-xl border bg-white px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                <span class="text-sm font-semibold">KF{{ $jk }}</span>
                            </div>
                            <div class="text-xs text-[#1D1D1D]">
                                {{ isset($kfDoneByJenis[$jk]) && $kfDoneByJenis[$jk]->last_date ? \Carbon\Carbon::parse($kfDoneByJenis[$jk]->last_date)->format('d/m/Y H:i') : '-' }}
                            </div>
                            <div>
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ isset($kfDoneByJenis[$jk]) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">{{ isset($kfDoneByJenis[$jk]) ? 'Selesai' : 'Belum' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</body>

</html>
