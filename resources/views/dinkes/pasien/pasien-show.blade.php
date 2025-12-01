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
            {{-- 
                Baris pertama: breadcrumb dan tombol "Kembali".
                - flex flex-col → di mobile, breadcrumb dan tombol saling tumpuk.
                - sm:flex-row sm:items-center sm:justify-between → di layar ≥ sm, disusun horizontal,
                  center vertikal, dan diberi jarak kiri-kanan.
                - gap-3 → jarak antar elemen.
            --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                {{-- 
                    Breadcrumb navigasi:
                    Menunjukkan posisi halaman sekarang (Dashboard / Detail Pasien).
                --}}
                <nav class="text-xs sm:text-sm text-[#7C7C7C]">
                    {{-- Link ke dashboard Dinkes --}}
                    <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">
                        Dashboard
                    </a>
                    {{-- Separator "/" di antara level breadcrumb --}}
                    <span class="mx-1 sm:mx-2">/</span>
                    {{-- Posisi saat ini (tidak berupa link) --}}
                    <span class="text-[#1D1D1D] font-medium">
                        Detail Pasien
                    </span>
                </nav>

                {{-- 
                    Kontainer tombol aksi di kanan:
                    Sekarang hanya berisi tombol "Kembali", bisa dikembangkan (mis. cetak, export, dll).
                --}}
                <div class="flex gap-2">
                    {{-- 
                        Tombol kembali ke dashboard.
                        - border + bg putih → tampil sebagai tombol sekunder.
                    --}}
                    <a href="{{ route('dinkes.dashboard') }}"
                        class="px-3 py-1.5 rounded-md border border-[#E0E0E0] bg-white/50 hover:bg-white text-sm">
                        Kembali
                    </a>
                </div>
            </div>

            {{-- ===================== --}}
            {{-- HEADER IDENTITAS PASIEN --}}
            {{-- ===================== --}}
            {{-- 
                Section pertama: menampilkan identitas singkat pasien
                (foto/nama/NIK dan beberapa info ringkas seperti umur, wilayah, JKN, kontak).
                - bg-white rounded-2xl shadow-md → gaya card putih modern.
            --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                {{-- 
                    Baris profil:
                    - flex-col → di mobile, foto di atas, teks di bawah.
                    - sm:flex-row sm:items-center → di desktop, foto dan teks sejajar horizontal.
                --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    {{-- 
                        Jika pasien punya photo tersimpan:
                        - gunakan Storage::url($pasien->photo) untuk generate URL public.
                        - tambahkan query ?t=timestamp updated_at agar browser menganggap perubahan foto
                          sebagai file baru (mencegah cache lama).
                    --}}
                    @if ($pasien->photo)
                        <img src="{{ Storage::url($pasien->photo) . '?t=' . optional($pasien->updated_at)->timestamp }}"
                            class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover" alt="{{ $pasien->name }}">
                    @else
                        {{-- 
                            Jika tidak ada foto:
                            - tampilkan avatar inisial huruf pertama nama.
                            - bg-pink-50 + ring-pink-100 → kesan lembut.
                            - grid place-items-center → teks berada tepat di tengah circle.
                        --}}
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-pink-50 ring-2 ring-pink-100 grid place-items-center">
                            <span class="text-[#B9257F] font-bold text-lg">
                                {{-- strtoupper(substr(..., 0, 1)) → ambil huruf pertama nama, jadikan huruf besar --}}
                                {{ strtoupper(substr($pasien->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    {{-- 
                        Kolom kanan: nama dan NIK pasien.
                    --}}
                    <div>
                        {{-- Nama pasien --}}
                        <h1 class="text-lg sm:text-xl font-semibold leading-tight break-words">
                            {{ $pasien->name }}
                        </h1>
                        {{-- NIK pasien, ditulis dengan font tabular-nums agar angka sejajar --}}
                        <div class="text-xs text-[#7C7C7C] mt-1">
                            NIK: <span class="tabular-nums break-all">{{ $pasien->nik }}</span>
                        </div>
                    </div>
                </div>

                {{-- 
                    Grid ringkasan data penting:
                    - Di mobile: 2 kolom.
                    - Di desktop: 4 kolom.
                    Masing-masing item berupa card kecil di atas background abu (#FAFAFA).
                --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-5 text-sm">
                    {{-- Kartu kecil untuk menampilkan umur pasien --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        {{-- Label kecil di atas: teks "Umur" --}}
                        <div class="text-[#7C7C7C]">Umur</div>

                        {{-- Angka umur ditampilkan dengan font sejajar (tabular-nums) --}}
                        <div class="font-semibold tabular-nums">
                            @php
                                // Jika tanggal_lahir ada, hitung umur dengan Carbon->age
                                // Kalau tidak ada tanggal_lahir, set umur = null
                                $umur = $pasien->tanggal_lahir
                                    ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age
                                    : null;
                            @endphp
                            {{-- 
                                Logika tampilan umur:
                                - Jika umur null ATAU umur <= 0 → tampilkan simbol '—' (data tidak valid)
                                - Jika umur >= 1 → tampilkan nilai umur apa adanya
                            --}}
                            {{ is_null($umur) || $umur <= 0 ? '—' : $umur }}
                        </div>
                    </div>

                    {{-- Wilayah --}}
                    <div class="bg-[#FAFAFA] rounded-xl p-3 col-span-2 md:col-span-1">
                        <div class="text-[#7C7C7C]">Wilayah</div>
                        <div class="font-semibold break-words">
                            {{-- 
                                Menampilkan kombinasi:
                                - PKecamatan, PKabupaten, PProvinsi.
                                Jika ada yang null → diganti '—' supaya jelas bahwa data kosong.
                            --}}
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
            {{-- 
                Section berisi 3 card:
                1. Status skrining terbaru.
                2. Ringkasan GPA.
                3. Ringkasan kunjungan nifas (KF).
                - Di mobile: disusun vertikal (1 kolom).
                - Di desktop: 3 kolom sejajar.
            --}}
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                {{-- ========================= --}}
                {{-- 1. Status Skrining Terbaru --}}
                {{-- ========================= --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 relative">
                    <h2 class="font-semibold mb-3">Status Skrining Terbaru</h2>

                    {{-- 
                        Jika skrining punya tanggal_waktu:
                        Tampilkan di pojok kanan atas sebagai "Diinput dd/mm/yyyy HH:MM".
                    --}}
                    @if ($skrining?->tanggal_waktu)
                        <span class="absolute top-5 right-5 text-xs text-[#7C7C7C]">
                            Diinput {{ $skrining->tanggal_waktu }}
                        </span>
                    @endif

                    {{-- 
                        Jika skrining terbaru ada:
                        - Hitung kategori risiko dan warna badge di sisi PHP (Blade).
                    --}}
                    @if ($skrining)
                        @php
                            // Menentukan kategori risiko berdasarkan jumlah_resiko_tinggi/sedang.
                            $risk =
                                ($skrining->jumlah_resiko_tinggi ?? 0) > 0
                                    ? 'Tinggi'
                                    : (($skrining->jumlah_resiko_sedang ?? 0) > 0
                                        ? 'Sedang'
                                        : 'Normal');

                            // Mapping kategori → warna hex utama.
                            $riskColor = [
                                'Normal' => '#39E93F', // hijau
                                'Sedang' => '#E2D30D', // kuning
                                'Tinggi' => '#E20D0D', // merah
                            ][$risk];
                        @endphp

                        {{-- 
                            Grid 3 kolom:
                            - Tempat Skrining
                            - Status (Hadir/Mangkir)
                            - Risiko (Normal/Sedang/Tinggi)
                        --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-5">
                            {{-- Tempat Skrining --}}
                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Tempat Skrining</div>
                                <div class="font-semibold break-words">
                                    {{ $skrining->puskesmas_nama ?? '—' }}
                                </div>
                            </div>

                            {{-- Status kehadiran --}}
                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Status</div>
                                {{-- 
                                    Badge status:
                                    - Jika checked_status true → Hadir (hijau).
                                    - Jika false → Mangkir (merah).
                                    style background + color dikontrol inline agar mudah disesuaikan.
                                --}}
                                <span class="inline-block px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $skrining->checked_status ? '#39E93F33' : '#E20D0D33' }};
                                           color: {{ $skrining->checked_status ? '#39E93F' : '#E20D0D' }};">
                                    {{ $skrining->checked_status ? 'Hadir' : 'Mangkir' }}
                                </span>
                            </div>

                            {{-- Risiko PE --}}
                            <div>
                                <div class="text-sm text-[#7C7C7C] mb-1">Risiko</div>
                                {{-- 
                                    Badge risiko:
                                    - Warna diambil dari $riskColor (plus transparansi 33 untuk background).
                                --}}
                                <span class="inline-block px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $riskColor }}33; color: {{ $riskColor }};">
                                    {{ $risk }}
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Jika belum ada skrining sama sekali --}}
                        <p class="text-sm text-[#7C7C7C] mt-2">
                            Belum ada data skrining.
                        </p>
                    @endif
                </div>

                {{-- ================== --}}
                {{-- 2. RINGKASAN GPA    --}}
                {{-- ================== --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <h2 class="font-semibold mb-3">Ringkasan GPA</h2>

                    {{-- 
                        Grid 3 kolom:
                        - G (Gravida): total kehamilan.
                        - P (Para): total persalinan.
                        - A (Abortus): total abortus.
                        Data berasal dari tabel riwayat_kehamilan_gpas (model RiwayatKehamilanGpa).
                    --}}
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 text-center">
                        {{-- G --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">G</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_kehamilan ?? '0' }}
                            </div>
                        </div>

                        {{-- P --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">P</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_persalinan ?? '0' }}
                            </div>
                        </div>

                        {{-- A --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">A</div>
                            <div class="text-lg sm:text-xl font-bold tabular-nums">
                                {{ $gpa->total_abortus ?? '0' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================== --}}
                {{-- 3. RINGKASAN KF    --}}
                {{-- ================== --}}
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <h2 class="font-semibold mb-3">Ringkasan KF</h2>

                    {{-- 
                        $kfSummary adalah koleksi (collection) ringkasan KF dari controller.
                        Kita buat keyBy('ke') agar bisa diakses dengan $byKe[1], $byKe[2], dst.
                    --}}
                    @php $byKe = collect($kfSummary)->keyBy('ke'); @endphp

                    {{-- 
                        Grid 4 card kecil:
                        - KF1, KF2, KF3, KF4
                        Menampilkan berapa kali kunjungan nifas ke-X dilakukan.
                    --}}
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ([1, 2, 3, 4] as $ke)
                            <div class="bg-[#FAFAFA] rounded-xl p-3 text-center">
                                <div class="text-xs text-[#7C7C7C]">KF{{ $ke }}</div>
                                <div class="text-base sm:text-lg font-semibold tabular-nums">
                                    {{-- 
                                        Jika ada entri untuk ke-N → tampilkan totalnya.
                                        Jika tidak → tampilkan 0.
                                    --}}
                                    {{ $byKe[$ke]->total ?? 0 }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- 
                        Ringkasan kesimpulan pantauan:
                        - $kfPantauan adalah map kesimpulan => total (Sehat, Dirujuk, Meninggal, dll).
                        Di sini ditampilkan tiga kategori utama: Sehat, Dirujuk, Meninggal.
                    --}}
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs sm:text-sm">
                        <div>
                            Sehat:
                            <span class="font-semibold tabular-nums">
                                {{ $kfPantauan['Sehat'] ?? 0 }}
                            </span>
                        </div>
                        <div>
                            Dirujuk:
                            <span class="font-semibold tabular-nums">
                                {{ $kfPantauan['Dirujuk'] ?? 0 }}
                            </span>
                        </div>
                        <div>
                            Meninggal:
                            <span class="font-semibold tabular-nums">
                                {{ $kfPantauan['Meninggal'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================= --}}
            {{-- KONDISI KESEHATAN TERBARU     --}}
            {{-- ============================= --}}
            <section class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                <h2 class="font-semibold mb-4">Kondisi Kesehatan Terbaru</h2>

                {{-- 
                    Jika ada $kondisi (diambil dari tabel kondisi_kesehatans):
                    tampilkan detail tinggi badan, berat badan, IMT, usia kehamilan, HPHT, TPP, dsb.
                --}}
                @if ($kondisi)
                    {{-- 
                        Grid 2 kolom di mobile, 4 kolom di desktop.
                        Masing-masing card kecil untuk tiap parameter.
                    --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 text-sm">
                        {{-- Tinggi Badan --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tinggi Badan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->tinggi_badan }} cm
                            </div>
                        </div>

                        {{-- Berat Badan --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Berat Badan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->berat_badan_saat_hamil }} kg
                            </div>
                        </div>

                        {{-- IMT --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">IMT</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->imt }}
                            </div>
                        </div>

                        {{-- Usia Kehamilan --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Usia Kehamilan</div>
                            <div class="font-semibold tabular-nums">
                                {{ $kondisi->usia_kehamilan }} Minggu
                            </div>
                        </div>

                        {{-- HPHT --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">HPHT</div>
                            <div class="font-semibold">
                                {{-- Format tanggal d/m/Y jika hpht ada, kalau tidak tampil '—' --}}
                                {{ $kondisi->hpht ? \Carbon\Carbon::parse($kondisi->hpht)->format('d/m/Y') : '—' }}
                            </div>
                        </div>

                        {{-- TPP --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">TPP</div>
                            <div class="font-semibold">
                                {{ $kondisi->tanggal_perkiraan_persalinan
                                    ? \Carbon\Carbon::parse($kondisi->tanggal_perkiraan_persalinan)->format('d/m/Y')
                                    : '—' }}
                            </div>
                        </div>

                        {{-- Protein Urine --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Protein Urine</div>
                            <div class="font-semibold">
                                {{ $kondisi->pemeriksaan_protein_urine ?? '—' }}
                            </div>
                        </div>

                        {{-- Tanggal Pemeriksaan --}}
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tanggal Pemeriksaan</div>
                            <div class="font-semibold">
                                {{ $kondisi->tanggal_skrining ? \Carbon\Carbon::parse($kondisi->tanggal_skrining)->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Jika belum ada catatan kondisi kesehatan sama sekali --}}
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

                {{-- 
                    Ditampilkan jika:
                    - Sudah ada skrining, dan
                    - Ada minimal satu penyakit di $riwayatPenyakit atau ada $penyakitLainnya.
                --}}
                @if ($skrining && (count($riwayatPenyakit) || $penyakitLainnya))
                    <div class="space-y-3 text-sm">
                        {{-- 
                            Daftar penyakit yang dipilih (checkbox di form skrining):
                            Ditampilkan sebagai chip/badge kecil.
                        --}}
                        <div class="flex flex-wrap gap-2">
                            @forelse ($riwayatPenyakit as $nama)
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#FFF0F6] text-[#B9257F] text-xs sm:text-sm">
                                    {{ $nama }}
                                </span>
                            @empty
                                {{-- Jika array kosong tapi sebenarnya kondisinya jarang terjadi karena if di atas --}}
                                <p class="text-[#7C7C7C]">
                                    Tidak ada penyakit spesifik yang dicentang.
                                </p>
                            @endforelse
                        </div>

                        {{-- Field penyakit lainnya (free text) --}}
                        @if ($penyakitLainnya)
                            <div class="mt-2">
                                <div class="text-[#7C7C7C] mb-0.5">Penyakit Lainnya</div>
                                <div class="font-semibold break-words">
                                    {{ $penyakitLainnya }}
                                </div>
                            </div>
                        @endif

                        {{-- Catatan sumber data --}}
                        <p class="text-xs text-[#9E9E9E] mt-2">
                            Sumber data: isian riwayat penyakit pada skrining terakhir pasien.
                        </p>
                    </div>

                    {{-- Jika ada skrining tapi tidak ada indikasi riwayat penyakit --}}
                @elseif ($skrining)
                    <p class="text-sm text-[#7C7C7C]">
                        Belum ada riwayat penyakit yang diisi pada skrining terakhir.
                    </p>

                    {{-- Jika belum pernah skrining --}}
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

                {{-- 
                    Grid card rujukan:
                    - 1 kolom di mobile.
                    - 2 kolom di md.
                    - 3 kolom di xl.
                --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                    @forelse($rujukan as $r)
                        <div class="rounded-xl border border-[#EFEFEF] p-4">
                            {{-- Baris atas: nama RS + tanggal rujukan --}}
                            <div class="flex items-center justify-between gap-3">
                                {{-- 
                                    Nama RS:
                                    - truncate + title → jika terlalu panjang, dikasih tooltip title.
                                --}}
                                <div class="font-medium truncate" title="{{ $r->rs_nama ?? 'Rumah Sakit' }}">
                                    {{ $r->rs_nama ?? 'Rumah Sakit' }}
                                </div>

                                {{-- Tanggal dibuatnya rujukan (created_at) --}}
                                <div class="text-xs text-[#7C7C7C] tabular-nums whitespace-nowrap">
                                    {{ optional($r->created_at)->format('d/m/Y') }}
                                </div>
                            </div>

                            {{-- Status rujukan: Selesai atau Proses --}}
                            <div class="text-sm mt-1">
                                Status:
                                <span class="px-2 py-0.5 rounded-full text-xs"
                                    style="background: {{ $r->done_status ? '#39E93F33' : '#FFF0E6' }};
                                           color: {{ $r->done_status ? '#39E93F' : '#B86700' }};">
                                    {{ $r->done_status ? 'Selesai' : 'Proses' }}
                                </span>
                            </div>

                            {{-- Catatan rujukan --}}
                            <div class="text-sm mt-1 text-[#7C7C7C]">
                                Catatan: {{ $r->catatan_rujukan ?? '—' }}
                            </div>
                        </div>
                    @empty
                        {{-- Jika belum ada rujukan sama sekali --}}
                        <p class="text-sm text-[#7C7C7C]">
                            Belum ada riwayat rujukan.
                        </p>
                    @endforelse
                </div>
            </section>

            {{-- FOOTER GLOBAL HALAMAN --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
