<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Detail Pasien Nifas</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/dinkes/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    <div class="flex flex-col min-h-screen">
        <x-dinkes.sidebar />

        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8">
            <div class="flex-1 max-w-6xl mx-auto space-y-6 lg:space-y-7">

                {{-- HEADER ATAS (gaya mirip RS) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dinkes.pasien-nifas') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M15 18l-6-6 6-6" />
                                </svg>
                            </span>
                            <span>Kembali</span>
                        </a>

                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate">
                                Data Pasien Nifas — {{ $pasien->name ?? 'N/A' }}
                            </h1>
                            <p class="text-xs text-[#7C7C7C]">
                                Ringkasan nifas ibu, data anak, dan pemantauannya.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- =========================
                     1. INFORMASI PASIEN & NIFAS
                   ========================== --}}
                <section class="bg-[#F3F3F3] rounded-3xl p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                            Informasi Pasien dan Data Nifas
                        </h2>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-[#ECECEC] overflow-hidden">
                        {{-- Header bar --}}
                        <div
                            class="grid grid-cols-2 text-[11px] sm:text-xs font-semibold text-[#7C7C7C] bg-[#FAFAFA] border-b border-[#F0F0F0]">
                            <div class="px-4 sm:px-6 py-3 border-r border-[#F0F0F0]">
                                Informasi
                            </div>
                            <div class="px-4 sm:px-6 py-3">
                                Data
                            </div>
                        </div>

                        {{-- Baris-baris informasi --}}
                        <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                            {{-- Tanggal Mulai Nifas --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Tanggal Mulai Nifas
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->tanggal_mulai_nifas_formatted ?? '—' }}
                                </div>
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Nama Lengkap
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->name ?? '—' }}
                                </div>
                            </div>

                            {{-- NIK --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    NIK
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D] break-all">
                                    {{ $pasien->nik ?? '—' }}
                                </div>
                            </div>

                            {{-- Tempat, Tanggal Lahir --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Tempat, Tanggal Lahir
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    @php
                                        $ttl = [];
                                        if ($pasien->tempat_lahir) {
                                            $ttl[] = $pasien->tempat_lahir;
                                        }
                                        if ($pasien->tanggal_lahir_formatted) {
                                            $ttl[] = $pasien->tanggal_lahir_formatted;
                                        }
                                    @endphp
                                    {{ implode(', ', $ttl) ?: '—' }}
                                </div>
                            </div>

                            {{-- Penanggung Nifas --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Penanggung Nifas
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->role_penanggung ?? '—' }}
                                </div>
                            </div>

                            {{-- Fasilitas Penanggung --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Fasilitas Kesehatan Penanggung
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    @if ($pasien->role_penanggung === 'Bidan')
                                        {{ $pasien->nama_puskesmas ?? '—' }}
                                    @elseif ($pasien->role_penanggung === 'Rumah Sakit')
                                        {{ $pasien->nama_rs ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>

                            {{-- Kontak --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Kontak
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->phone ?? '—' }}
                                </div>
                            </div>

                            {{-- Alamat --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Alamat
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D] leading-relaxed">
                                    {{ $pasien->address ?? '—' }}
                                    @php
                                        $lokasi = [];
                                        if ($pasien->PKecamatan) {
                                            $lokasi[] = $pasien->PKecamatan;
                                        }
                                        if ($pasien->PKabupaten) {
                                            $lokasi[] = $pasien->PKabupaten;
                                        }
                                        if ($pasien->PProvinsi) {
                                            $lokasi[] = $pasien->PProvinsi;
                                        }
                                    @endphp
                                    @if (!empty($lokasi))
                                        <span class="block text-[#7C7C7C] mt-0.5">
                                            {{ implode(', ', $lokasi) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div> {{-- end divide --}}
                    </div> {{-- end card --}}
                </section>

                {{-- =========================
                     2. DATA ANAK (TABEL)
                   ========================== --}}
                <section class="bg-white rounded-2xl border border-[#ECECEC] shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-3 sm:space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm sm:text-base font-semibold">
                            Data Anak
                        </h2>
                        <span class="text-[11px] sm:text-xs text-[#9B9B9B]">
                            Total: <span class="font-medium text-[#000000CC]">{{ $anakList->count() }}</span> anak
                        </span>
                    </div>

                    @if ($anakList->isEmpty())
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada data anak yang tercatat untuk nifas ini.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-[700px] w-full text-xs sm:text-sm text-left border-collapse">
                                <thead class="bg-[#F7F7F7] text-[#7C7C7C] border-b border-[#E5E5E5]">
                                    <tr>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Anak ke-</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Nama Anak</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tanggal Lahir</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Jenis Kelamin</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Berat Lahir (kg)</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Panjang (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($anakList as $anak)
                                        <tr class="border-b border-[#F0F0F0] hover:bg-[#FAFAFA] transition">
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->anak_ke }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->nama_anak }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->tanggal_lahir
                                                    ? \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d F Y')
                                                    : '—' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->jenis_kelamin }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->berat_lahir_anak }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->panjang_lahir_anak }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- =========================
                     3. KUNJUNGAN NIFAS (KF)
                   ========================== --}}
                <section class="bg-white rounded-2xl border border-[#ECECEC] shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-3 sm:space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm sm:text-base font-semibold">
                            Kunjungan Nifas (KF)
                        </h2>
                        <span class="text-[11px] sm:text-xs text-[#9B9B9B]">
                            Total kunjungan:
                            <span class="font-medium text-[#000000CC]">{{ $kunjunganNifas->count() }}</span>
                        </span>
                    </div>

                    @if ($kunjunganNifas->isEmpty())
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada data kunjungan nifas yang tercatat.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-[760px] w-full text-xs sm:text-sm text-left border-collapse">
                                <thead class="bg-[#F7F7F7] text-[#7C7C7C] border-b border-[#E5E5E5]">
                                    <tr>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Kunjungan ke-</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tanggal Kunjungan</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Anak</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tekanan Darah (SBP/DBP)</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">MAP</th>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Kesimpulan Pantauan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kunjunganNifas as $kf)
                                        <tr class="border-b border-[#F0F0F0] hover:bg-[#FAFAFA] transition">
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->kunjungan_nifas_ke }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->tanggal_kunjungan
                                                    ? \Carbon\Carbon::parse($kf->tanggal_kunjungan)->translatedFormat('d F Y')
                                                    : '—' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                @php
                                                    $labelAnak = [];
                                                    if ($kf->anak_ke) {
                                                        $labelAnak[] = 'Anak ke-' . $kf->anak_ke;
                                                    }
                                                    if ($kf->nama_anak) {
                                                        $labelAnak[] = $kf->nama_anak;
                                                    }
                                                @endphp
                                                {{ implode(' – ', $labelAnak) ?: '—' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->sbp }}/{{ $kf->dbp }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->map }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->kesimpulan_pantauan }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- =========================
                     4. RIWAYAT PENYAKIT NIFAS
                   ========================== --}}
                <section class="bg-white rounded-2xl border border-[#ECECEC] shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-3 sm:space-y-4 mb-4">
                    <h2 class="text-sm sm:text-base font-semibold">
                        Riwayat Penyakit Nifas
                    </h2>

                    @if ($riwayatPenyakit->isEmpty())
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada riwayat penyakit nifas yang tercatat.
                        </p>
                    @else
                        <div class="space-y-2.5 text-xs sm:text-sm">
                            @foreach ($riwayatPenyakit as $rp)
                                <div class="border border-[#E5E5E5] rounded-xl px-3 py-2.5 bg-[#FCFCFC]">
                                    <div class="font-medium text-[#171717]">
                                        {{ $rp->nama_penyakit ?? 'Penyakit tidak diketahui' }}
                                    </div>
                                    @if ($rp->keterangan_penyakit_lain)
                                        <div class="text-[#7C7C7C] mt-0.5 leading-relaxed">
                                            {{ $rp->keterangan_penyakit_lain }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <footer class="text-center text-[11px] sm:text-xs text-[#9B9B9B] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
