<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Detail Pasien Nifas</title>
    @vite(['resources/css/app.css','resources/js/app.js','resources/js/dropdown.js','resources/js/bidan/sidebar-toggle.js'])
</head>
<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    <x-bidan.sidebar />
    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
        @php
            $statusType = $pasienNifas->status_type ?? 'normal';
            $statusDisplay = $pasienNifas->status_display ?? 'Tidak Berisiko';
            $badgeClass = match($statusType) {
                'beresiko' => 'bg-red-100 text-red-700 border-red-200',
                'waspada' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-emerald-100 text-emerald-700 border-emerald-200'
            };
            $anakPertama = $anakPasien->first();
        @endphp

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="mb-6 flex items-center gap-3">
                <a href="{{ route('bidan.pasien-nifas') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                        Data Pasien Nifas — {{ $pasienNifas->pasien->user->name ?? 'N/A' }}
                    </h1>
                    <p class="text-xs text-[#7C7C7C] mt-1">Ringkasan nifas ibu, riwayat persalinan, dan kondisi bayi</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $badgeClass }}">
                    <span class="text-xs font-semibold">{{ $statusDisplay }}</span>
                </div>
                <a href="{{ route('bidan.pasien-nifas.anak.create', $pasienNifas->id) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    <span>Tambah Data Anak</span>
                </a>
            </div>
        </div>

        <section class="bg-white rounded-3xl p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Data Pasien</h2>
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
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->user->name ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="px-4 sm:px-6 py-3 font-semibold">NIK</div>
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->nik ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="px-4 sm:px-6 py-3 font-semibold">Alamat</div>
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                            {{ $pasienNifas->pasien->PWilayah ?? '-' }}, {{ $pasienNifas->pasien->PKecamatan ?? '-' }},
                            {{ $pasienNifas->pasien->PKabupaten ?? '-' }}, {{ $pasienNifas->pasien->PProvinsi ?? '-' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="px-4 sm:px-6 py-3 font-semibold">Nomor Telepon</div>
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $pasienNifas->pasien->user->phone ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Ringkasan Data Anak</h2>
            </div>
            <div class="bg-white rounded-1xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-2 text-xs sm:text-sm font-semibold bg-[#FDECF5]">
                    <div class="px-4 sm:px-6 py-3">Informasi</div>
                    <div class="px-4 sm:px-6 py-3">Data</div>
                </div>
                <div class="text-xs sm:text-sm">
                    <div class="grid grid-cols-2">
                        <div class="px-4 sm:px-6 py-3 font-semibold">Total Anak Terdaftar</div>
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $anakPasien->count() }}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="px-4 sm:px-6 py-3 font-semibold">Usia Kehamilan Anak Pertama</div>
                        <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">{{ $anakPertama?->usia_kehamilan_saat_lahir ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-4 sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Detail Anak</h2>
                <a href="{{ route('bidan.pasien-nifas.kf-form', ['id' => $pasienNifas->id, 'jenisKf' => 1]) }}" class="text-xs text-[#B9257F]">Catat KF</a>
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
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $anak->berat_lahir_anak }}</td>
                                <td class="px-4 py-2">{{ $anak->panjang_lahir_anak }}</td>
                                <td class="px-4 py-2">{{ $anak->lingkar_kepala_anak }}</td>
                                <td class="px-4 py-2">{{ $anak->kondisi_ibu ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                                        <a href="{{ route('bidan.pasien-nifas.anak.edit', ['id' => $pasienNifas->id, 'anakId' => $anak->id]) }}" class="px-3 py-1.5 rounded-full border text-xs border-[#E5E5E5]">Edit</a>
                                        <form action="{{ route('bidan.pasien-nifas.anak.destroy', ['id' => $pasienNifas->id, 'anakId' => $anak->id]) }}" method="POST" onsubmit="return confirm('Hapus data anak ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-4 text-center text-[#7C7C7C]">Belum ada data anak.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-3xl p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Riwayat Kunjungan Nifas (KF)</h2>
            </div>
            <div class="bg-white rounded-1xl shadow-sm overflow-hidden">
                <table class="w-full text-xs sm:text-sm">
                    <thead class="bg-[#FFF7FC]">
                        <tr class="text-left">
                            <th class="px-4 py-2 font-semibold">KF</th>
                            <th class="px-4 py-2 font-semibold">Tanggal</th>
                            <th class="px-4 py-2 font-semibold">Anak</th>
                            <th class="px-4 py-2 font-semibold">SBP/DBP</th>
                            <th class="px-4 py-2 font-semibold">MAP</th>
                            <th class="px-4 py-2 font-semibold">Kesimpulan</th>
                            <th class="px-4 py-2 font-semibold">Keadaan Umum</th>
                            <th class="px-4 py-2 font-semibold">Tanda Bahaya</th>
                            <th class="px-4 py-2 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="min-w-full text-xs sm:text-sm">
                        @forelse($kfList as $kf)
                            <tr>
                                <td class="px-4 py-2">KF{{ $kf->kunjungan_nifas_ke }}</td>
                                <td class="px-4 py-2">{{ $kf->tanggal_kunjungan ? \Carbon\Carbon::parse($kf->tanggal_kunjungan)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-2">{{ optional($kf->anak)->nama_anak ? ('Anak ke-' . ($kf->anak->anak_ke ?? '-') . ' — ' . $kf->anak->nama_anak) : '-' }}</td>
                                <td class="px-4 py-2">{{ $kf->sbp }} / {{ $kf->dbp }} mmHg</td>
                                <td class="px-4 py-2">{{ $kf->map !== null ? number_format($kf->map, 0) : '-' }}</td>
                                <td class="px-4 py-2">{{ $kf->kesimpulan_pantauan ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $kf->keadaan_umum ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $kf->tanda_bahaya ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <form action="{{ route('bidan.pasien-nifas.kf.destroy', ['id' => $pasienNifas->id, 'kfId' => $kf->id]) }}" method="POST" onsubmit="return confirm('Hapus data KF ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-full border border-red-200 text-red-700 hover:bg-red-50 text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-4 text-center text-[#7C7C7C]">Belum ada kunjungan nifas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>