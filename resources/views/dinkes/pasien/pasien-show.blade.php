<!DOCTYPE html>
<html lang="id">
{{-- 
        lang="id" → Memberi tahu browser dan alat bantu (screen reader, dll.)
        bahwa bahasa utama dokumen ini adalah Bahasa Indonesia.
    --}}

<head>
    {{-- 
        Menentukan encoding karakter:
        - UTF-8 mendukung hampir semua karakter (huruf latin, angka, simbol, dll)
        sehingga teks Bahasa Indonesia tampil dengan benar.
    --}}
    <meta charset="UTF-8">

    {{-- 
        Viewport untuk tampilan responsif:
        - width=device-width → lebar konten mengikuti lebar layar perangkat.
        - initial-scale=1.0 → scale awal 100% (tidak dizoom).
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 
        Judul halaman yang tampil di tab browser.
        Di sini menunjukkan halaman detail 1 pasien untuk role Dinkes.
    --}}
    <title>DINKES – Detail Pasien</title>

    {{-- 
        Memuat asset yang di-bundling Vite:
        - resources/css/app.css             → CSS utama (Tailwind + custom).
        - resources/js/app.js               → JS global aplikasi (bisa berisi Alpine, event listener umum, dll).
        - resources/js/dinkes/sidebar-toggle.js → JS khusus untuk handle toggle sidebar Dinkes.
        Tidak ada inline script di view, semua logika JS dikumpulkan di file resource.
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

{{-- 
    BODY:
    - bg-[#F5F5F5] → warna background abu-abu muda, membedakan dengan card putih.
    - font-[Poppins] → font utama Poppins (diset di CSS, biasanya di app.css).
    - text-[#000000CC] → teks berwarna hitam dengan sedikit transparansi (0xCC ≈ 80% opacity).
--}}

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    {{-- 
        Wrapper utama:
        - min-h-screen → minimal setinggi tinggi layar (agar footer bisa "nempel" di bawah).
        - flex → menjadikan children dalam layout flex horizontal: sidebar di kiri, main di kanan.
    --}}
    <div class="min-h-screen flex">
        {{-- 
            SIDEBAR:
            Komponen Blade <x-dinkes.sidebar /> berisi menu navigasi untuk Dinkes.
            Diletakkan di dalam flex agar selalu berada di sisi kiri.
            Responsif-nya diatur oleh JS (sidebar-toggle.js) dan CSS di komponen tersebut.
        --}}
        <x-dinkes.sidebar />

        {{-- 
            MAIN CONTENT:
            - w-full → main mengambil lebar penuh sisa setelah sidebar.
            - lg:ml-[260px] → di layar besar, beri margin kiri 260px agar konten tidak ketutup sidebar fixed.
            - p-4 sm:p-6 lg:p-8 → padding responsif.
            - space-y-6 → jarak vertikal antar section di dalam main.
        --}}
        <main class="w-full lg:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- ============================ --}}
            {{-- BREADCRUMB + TOMBOL AKSI TOP --}}
            {{-- ============================ --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                {{-- Breadcrumb navigasi --}}
                <nav class="text-xs sm:text-sm text-[#7C7C7C]">
                    <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">
                        Dashboard
                    </a>
                    <span class="mx-1 sm:mx-2">/</span>
                    <span class="text-[#1D1D1D] font-medium">
                        Detail Pasien
                    </span>
                </nav>

                {{-- Tombol kembali --}}
                <div class="flex gap-2">
                    <a href="{{ route('dinkes.dashboard') }}"
                        class="px-3 py-1.5 rounded-md border border-[#E0E0E0] bg-white/50 hover:bg-white text-sm">
                        Kembali
                    </a>
                </div>
            </div>

            {{-- ===================== --}}
            {{-- HEADER IDENTITAS PASIEN --}}
            {{-- ===================== --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    @if ($pasien->photo)
                        <img src="{{ Storage::url($pasien->photo) . '?t=' . optional($pasien->updated_at)->timestamp }}"
                            class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover" alt="{{ $pasien->name }}">
                    @else
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-pink-50 ring-2 ring-pink-100 grid place-items-center">
                            <span class="text-[#B9257F] font-bold text-lg">
                                {{ strtoupper(substr($pasien->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold leading-tight break-words">
                            {{ $pasien->name }}
                        </h1>
                        <div class="text-xs text-[#7C7C7C] mt-1">
                            NIK: <span class="tabular-nums break-all">{{ $pasien->nik }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-5 text-sm">
                    {{-- Umur --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">Umur</div>
                        <div class="font-semibold tabular-nums">
                            @php
                                $umur = $pasien->tanggal_lahir
                                    ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age
                                    : null;
                            @endphp
                            {{ is_null($umur) || $umur <= 0 ? '—' : $umur }}
                        </div>
                    </div>

                    {{-- Wilayah --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3 col-span-2 md:col-span-1">
                        <div class="text-[#7C7C7C]">Wilayah</div>
                        <div class="font-semibold break-words">
                            {{ $pasien->PKecamatan ?? '—' }}, {{ $pasien->PKabupaten ?? '—' }},
                            {{ $pasien->PProvinsi ?? '—' }}
                        </div>
                    </div>

                    {{-- No. JKN --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">No. JKN</div>
                        <div class="font-semibold tabular-nums break-all">
                            {{ $pasien->no_jkn ?? '—' }}
                        </div>
                    </div>

                    {{-- Kontak --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">Kontak</div>
                        <div class="font-semibold break-all">
                            {{ $pasien->phone ?? '—' }}
                        </div>
                    </div>
                </div>
            </section>

            {{-- ==================== --}}
            {{-- RINGKASAN STATUS & KPI --}}
            {{-- ==================== --}}
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                {{-- 1. Status Skrining Terbaru --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 relative">
                    <h2 class="font-semibold mb-3">Status Skrining Terbaru</h2>

                    @if ($skrining?->tanggal_waktu)
                        <span class="absolute top-5 right-5 text-xs text-[#7C7C7C]">
                            Diinput {{ $skrining->tanggal_waktu }}
                        </span>
                    @endif

                    @if ($skrining)
                        @php
                            $risk =
                                ($skrining->jumlah_resiko_tinggi ?? 0) > 0
                                    ? 'Tinggi'
                                    : (($skrining->jumlah_resiko_sedang ?? 0) > 0
                                        ? 'Sedang'
                                        : 'Normal');

                            $riskColor = [
                                'Normal' => '#39E93F',
                                'Sedang' => '#E2D30D',
                                'Tinggi' => '#E20D0D',
                            ][$risk];
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-5">
                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Tempat Skrining</div>
                                <div class="font-semibold break-words">
                                    {{ $skrining->puskesmas_nama ?? '—' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Status</div>
                                <span class="inline-block px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $skrining->checked_status ? '#39E93F33' : '#E20D0D33' }};
                                           color: {{ $skrining->checked_status ? '#39E93F' : '#E20D0D' }};">
                                    {{ $skrining->checked_status ? 'Hadir' : 'Mangkir' }}
                                </span>
                            </div>

                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Risiko</div>
                                <span class="inline-block px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $riskColor }}33; color: {{ $riskColor }};">
                                    {{ $risk }}
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-[#7C7C7C] mt-2">
                            Belum ada data skrining.
                        </p>
                    @endif
                </div>

                {{-- 2. Ringkasan GPA --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <h2 class="font-semibold mb-3">Ringkasan GPA</h2>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3 text-center">
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">G</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_kehamilan ?? '0' }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">P</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_persalinan ?? '0' }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">A</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_abortus ?? '0' }}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- 3. Ringkasan KF --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <h2 class="font-semibold mb-3">Ringkasan KF</h2>

                    @php
                        // $kfSummary: collection objek {ke, done, kesimpulan}
                        $kfByKe = collect($kfSummary)->keyBy('ke');
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach ([1, 2, 3, 4] as $ke)
                            @php
                                $row = $kfByKe->get($ke);
                                $done = $row && $row->done;

                                // Label mentah dari DB (mis. 'Sehat', 'Dirujuk', 'Meninggal')
                                $rawLabel = $done ? $row->kesimpulan ?? 'Sehat' : null;
                                $statusKey = strtolower(trim($rawLabel ?? ''));

                                // Label yang ditampilkan di UI
                                // Jika status 'meninggal' → tampilkan 'Wafat'
                                $displayLabel = $statusKey === 'meninggal' ? 'Wafat' : $rawLabel ?? '';

                                // Mapping warna berdasarkan kesimpulan pantauan
                                $colorMap = [
                                    'sehat' => ['bg' => '#E6F8EC', 'text' => '#1F7A31'],
                                    'dirujuk' => ['bg' => '#FFF4E5', 'text' => '#B86700'],
                                    'meninggal' => ['bg' => '#FFE4E4', 'text' => '#D11A1A'],
                                ];

                                $colors = $colorMap[$statusKey] ?? ['bg' => '#E9E9E9', 'text' => '#555555'];
                            @endphp

                            <div class="rounded-xl p-3 text-center border border-[#F0F0F0] bg-[#FAFAFA]">
                                <div class="text-xs text-[#7C7C7C] mb-1">KF{{ $ke }}</div>

                                @if ($done)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                        style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
                                        {{ $displayLabel }}
                                    </span>
                                    <div class="mt-1 text-[11px] text-[#7C7C7C]">
                                        Sudah dilakukan
                                    </div>
                                @else
                                    <span class="px-2.5 py-1">
                                        -
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </section>

            {{-- ============================= --}}
            {{-- KONDISI KESEHATAN TERBARU     --}}
            {{-- ============================= --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                <h2 class="font-semibold mb-4">Kondisi Kesehatan Terbaru</h2>

                @if ($kondisi)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 text-sm">
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tinggi Badan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->tinggi_badan }} cm
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Berat Badan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->berat_badan_saat_hamil }} kg
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">IMT</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->imt }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Usia Kehamilan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->usia_kehamilan }} Minggu
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">HPHT</div>
                            <div class="font-semibold">
                                {{ $kondisi->hpht ? \Carbon\Carbon::parse($kondisi->hpht)->format('d/m/Y') : '—' }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">TPP</div>
                            <div class="font-semibold">
                                {{ $kondisi->tanggal_perkiraan_persalinan
                                    ? \Carbon\Carbon::parse($kondisi->tanggal_perkiraan_persalinan)->format('d/m/Y')
                                    : '—' }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Protein Urine</div>
                            <div class="font-semibold">
                                {{ $kondisi->pemeriksaan_protein_urine ?? '—' }}
                            </div>
                        </div>

                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tanggal Pemeriksaan</div>
                            <div class="font-semibold">
                                {{ $kondisi->tanggal_skrining ? \Carbon\Carbon::parse($kondisi->tanggal_skrining)->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-[#7C7C7C]">
                        Belum ada catatan kondisi kesehatan.
                    </p>
                @endif
            </section>

            {{-- ==================== --}}
            {{-- RIWAYAT PENYAKIT     --}}
            {{-- ==================== --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                <h2 class="font-semibold mb-4">Riwayat Penyakit</h2>

                @if ($skrining && (count($riwayatPenyakit) || $penyakitLainnya))
                    <div class="space-y-3 text-sm">
                        <div class="flex flex-wrap gap-2">
                            @forelse ($riwayatPenyakit as $nama)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#FFF0F6] text-[#B9257F] text-xs sm:text-sm">
                                    {{ $nama }}
                                </span>
                            @empty
                                <p class="text-[#7C7C7C]">
                                    Tidak ada penyakit spesifik yang dicentang.
                                </p>
                            @endforelse
                        </div>

                        @if ($penyakitLainnya)
                            <div class="mt-2">
                                <div class="text-[#7C7C7C] mb-0.5">Penyakit Lainnya</div>
                                <div class="font-semibold break-words">
                                    {{ $penyakitLainnya }}
                                </div>
                            </div>
                        @endif

                        <p class="text-xs text-[#9E9E9E] mt-2">
                            Sumber data: isian riwayat penyakit pada skrining terakhir pasien.
                        </p>
                    </div>
                @elseif ($skrining)
                    <p class="text-sm text-[#7C7C7C]">
                        Belum ada riwayat penyakit yang diisi pada skrining terakhir.
                    </p>
                @else
                    <p class="text-sm text-[#7C7C7C]">
                        Belum ada data skrining sehingga riwayat penyakit tidak tersedia.
                    </p>
                @endif
            </section>

            {{-- ===================== --}}
            {{-- RIWAYAT RUJUKAN RS   --}}
            {{-- ===================== --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                <h2 class="font-semibold mb-4">Riwayat Rujukan RS</h2>

                @if ($rujukan->isEmpty())
                    <p class="text-sm text-[#7C7C7C]">
                        Belum ada riwayat rujukan.
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                        @foreach ($rujukan as $r)
                            @php
                                $anjuranLabel = null;
                                if ($r->rw_anjuran_kontrol === 'fktp') {
                                    $anjuranLabel = 'Kontrol ke FKTP (Puskesmas/Klinik)';
                                } elseif ($r->rw_anjuran_kontrol === 'rs') {
                                    $anjuranLabel = 'Kontrol ke Rumah Sakit (RS)';
                                }
                            @endphp

                            <article class="rounded-2xl border border-[#EFEFEF] bg-white p-4 space-y-3 text-sm">
                                {{-- Header: Nama RS + tanggal rujukan --}}
                                <header class="flex items-start justify-between gap-3">
                                    <div class="space-y-0.5">
                                        <div class="text-[11px] uppercase tracking-wide text-[#9E9E9E]">
                                            Rujukan ke
                                        </div>
                                        <h3 class="font-semibold truncate"
                                            title="{{ $r->rs_nama ?? 'Rumah Sakit' }}">
                                            {{ $r->rs_nama ?? 'Rumah Sakit' }}
                                        </h3>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-[11px] text-[#9E9E9E]">Tanggal Rujukan</div>
                                        <div class="text-xs text-[#4B4B4B] tabular-nums">
                                            {{ optional($r->created_at)->format('d/m/Y') ?: '—' }}
                                        </div>
                                    </div>
                                </header>

                                {{-- Status ringkas --}}
                                <div class="grid grid-cols-1 gap-1.5 text-xs sm:text-[13px]">
                                    <div class="flex justify-between gap-2">
                                        <span class="text-[#7C7C7C]">Status Rujukan</span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px]"
                                            style="background: {{ $r->done_status ? '#39E93F33' : '#FFF0E6' }};
                                                   color: {{ $r->done_status ? '#39E93F' : '#B86700' }};">
                                            {{ $r->done_status ? 'Selesai' : 'Proses' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between gap-2">
                                        <span class="text-[#7C7C7C]">Status Kedatangan</span>
                                        @if (is_null($r->pasien_datang))
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[11px] bg-[#E9E9E9] text-[#555555]">
                                                Belum tercatat
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px]"
                                                style="background: {{ $r->pasien_datang ? '#39E93F33' : '#FFE4E4' }};
                                                       color: {{ $r->pasien_datang ? '#1F7A31' : '#D11A1A' }};">
                                                {{ $r->pasien_datang ? 'Datang ke RS' : 'Belum datang' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex justify-between gap-2">
                                        <span class="text-[#7C7C7C]">Perlu Pemeriksaan Lanjut</span>
                                        <span class="font-semibold">
                                            @if (is_null($r->perlu_pemeriksaan_lanjut))
                                                <span class="text-[#7C7C7C]">—</span>
                                            @else
                                                {{ $r->perlu_pemeriksaan_lanjut ? 'Ya' : 'Tidak' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Data klinis dari PKM --}}
                                <div class="pt-2 border-t border-dashed border-[#EFEFEF] space-y-1.5">
                                    <div class="text-[11px] font-semibold text-[#9E9E9E] uppercase">
                                        Data Klinis dari PKM
                                    </div>

                                    <dl class="space-y-0.5 text-xs sm:text-[13px]">
                                        <div class="flex justify-between gap-2">
                                            <dt class="text-[#7C7C7C]">Riwayat TD</dt>
                                            <dd class="font-medium tabular-nums text-right">
                                                {{ $r->riwayat_tekanan_darah ?? '—' }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <dt class="text-[#7C7C7C]">Protein Urin</dt>
                                            <dd class="font-medium text-right">
                                                {{ $r->hasil_protein_urin ?? '—' }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <dt class="text-[#7C7C7C]">Catatan PKM</dt>
                                            <dd class="font-normal text-right text-[#4A4A4A]">
                                                {{ $r->catatan_rujukan ?? '—' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                {{-- Tindakan & anjuran RS --}}
                                <div class="pt-2 border-t border-dashed border-[#EFEFEF] space-y-1.5">
                                    <div class="text-[11px] font-semibold text-[#9E9E9E] uppercase">
                                        Tindakan & Anjuran Dokter RS (log terakhir)
                                    </div>

                                    @if ($r->rw_tanggal_datang || $r->rw_tindakan || $r->rw_anjuran_kontrol || $r->rw_kunjungan_berikutnya || $r->rw_catatan)
                                        <dl class="space-y-0.5 text-xs sm:text-[13px]">
                                            <div class="flex justify-between gap-2">
                                                <dt class="text-[#7C7C7C]">Tanggal Datang RS</dt>
                                                <dd class="font-medium text-right tabular-nums">
                                                    {{ $r->rw_tanggal_datang ? \Carbon\Carbon::parse($r->rw_tanggal_datang)->format('d/m/Y') : '—' }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-2">
                                                <dt class="text-[#7C7C7C]">Tindakan Dokter</dt>
                                                <dd class="font-medium text-right">
                                                    {{ $r->rw_tindakan ?? '—' }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-2">
                                                <dt class="text-[#7C7C7C]">Anjuran Kontrol</dt>
                                                <dd class="font-medium text-right">
                                                    {{ $anjuranLabel ?? ($r->rw_anjuran_kontrol ?? '—') }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-2">
                                                <dt class="text-[#7C7C7C]">Kunjungan Berikutnya</dt>
                                                <dd class="font-medium text-right">
                                                    {{ $r->rw_kunjungan_berikutnya ?? '—' }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-2">
                                                <dt class="text-[#7C7C7C]">Catatan Dokter RS</dt>
                                                <dd class="font-normal text-right text-[#4A4A4A]">
                                                    {{ $r->rw_catatan ?? '—' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    @else
                                        <p class="text-xs text-[#7C7C7C]">
                                            Belum ada catatan tindak lanjut dari dokter RS.
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>


            {{-- FOOTER GLOBAL HALAMAN --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
