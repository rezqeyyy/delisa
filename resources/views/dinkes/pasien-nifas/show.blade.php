<!DOCTYPE html> {{-- Deklarasi dokumen HTML5 --}}
<html lang="id"> {{-- Bahasa utama halaman: Indonesia --}}

<head>
    <meta charset="UTF-8"> {{-- Set encoding karakter ke UTF-8 --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> {{-- Supaya tampilan responsif di mobile --}}
    <title>DINKES – Detail Pasien Nifas</title> {{-- Judul tab halaman di browser --}}

    {{-- Memuat file CSS & JS lewat Vite --}}
    @vite([
        'resources/css/app.css', {{-- CSS utama aplikasi --}}
        'resources/js/app.js', {{-- JS global aplikasi --}}
        'resources/js/dropdown.js', {{-- Script untuk dropdown umum --}}
        'resources/js/dinkes/sidebar-toggle.js' {{-- Script untuk toggle sidebar Dinkes --}}
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]"> {{-- Warna background, font, dan warna teks utama --}}
    <div class="flex flex-col min-h-screen"> {{-- Layout utama: kolom, tinggi minimal satu layar --}}
        <x-dinkes.sidebar /> {{-- Komponen sidebar Dinkes di sisi kiri --}}

        <main class="ml-0 md:ml-[260px] flex-1 min-h-screen flex flex-col p-4 sm:p-6 lg:p-8"> {{-- Area konten utama --}}
            <div class="flex-1 max-w-6xl mx-auto space-y-6 lg:space-y-7"> {{-- Batasi lebar konten dan beri jarak antar section --}}

                {{-- HEADER ATAS (gaya mirip RS) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"> {{-- Header fleksibel: kolom di mobile, baris di desktop --}}
                    <div class="flex items-center gap-3"> {{-- Bungkus tombol kembali + judul --}}
                        <a href="{{ route('dinkes.pasien-nifas') }}" {{-- Link kembali ke halaman index pasien nifas --}}
                           class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                            {{-- Tombol kembali: tampil seperti pill button --}}
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]"> {{-- Lingkaran kecil untuk ikon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2"> {{-- Ikon panah kiri (SVG) --}}
                                    <path d="M15 18l-6-6 6-6" /> {{-- Bentuk garis panah ke kiri --}}
                                </svg>
                            </span>
                            <span>Kembali</span> {{-- Teks tombol --}}
                        </a>

                        <div class="min-w-0"> {{-- Container judul, min-w-0 agar truncate bisa bekerja --}}
                            <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate"> {{-- Judul utama halaman --}}
                                Data Pasien Nifas — {{ $pasien->name ?? 'N/A' }} {{-- Menampilkan nama pasien, fallback 'N/A' kalau null --}}
                            </h1>
                            <p class="text-xs text-[#7C7C7C]"> {{-- Subjudul penjelas --}}
                                Ringkasan nifas ibu, data anak, dan pemantauannya.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- =========================
                     1. INFORMASI PASIEN & NIFAS
                   ========================== --}}
                <section class="bg-[#F3F3F3] rounded-3xl p-4 sm:p-6"> {{-- Section info pasien + nifas dengan background abu --}}
                    <div class="flex items-center justify-between mb-4"> {{-- Header kecil section --}}
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]"> {{-- Judul section --}}
                            Informasi Pasien dan Data Nifas
                        </h2>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-[#ECECEC] overflow-hidden"> {{-- Kartu putih berisi tabel info --}}
                        {{-- Header bar --}}
                        <div
                            class="grid grid-cols-2 text-[11px] sm:text-xs font-semibold text-[#7C7C7C] bg-[#FAFAFA] border-b border-[#F0F0F0]">
                            {{-- Header kolom kiri --}}
                            <div class="px-4 sm:px-6 py-3 border-r border-[#F0F0F0]">
                                Informasi {{-- Label kolom nama informasi --}}
                            </div>
                            {{-- Header kolom kanan --}}
                            <div class="px-4 sm:px-6 py-3">
                                Data {{-- Label kolom isi data --}}
                            </div>
                        </div>

                        {{-- Baris-baris informasi --}}
                        <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm"> {{-- Setiap baris dipisah garis horizontal --}}

                            {{-- Tanggal Mulai Nifas --}}
                            <div class="grid grid-cols-2"> {{-- Satu baris: label kiri, nilai kanan --}}
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Tanggal Mulai Nifas {{-- Label data --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->tanggal_mulai_nifas_formatted ?? '—' }} {{-- Tanggal mulai nifas yang sudah diformat, atau '—' jika tidak ada --}}
                                </div>
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Nama Lengkap {{-- Label nama --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->name ?? '—' }} {{-- Menampilkan nama pasien, fallback '—' jika null --}}
                                </div>
                            </div>

                            {{-- NIK --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    NIK {{-- Label NIK --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D] break-all">
                                    {{ $pasien->nik ?? '—' }} {{-- NIK pasien, dengan break-all supaya tidak overflow --}}
                                </div>
                            </div>

                            {{-- Tempat, Tanggal Lahir --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Tempat, Tanggal Lahir {{-- Label TTL --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    @php
                                        // Inisialisasi array kosong untuk menyusun teks TTL
                                        $ttl = [];
                                        // Jika pasien punya tempat lahir, tambahkan ke array
                                        if ($pasien->tempat_lahir) {
                                            $ttl[] = $pasien->tempat_lahir;
                                        }
                                        // Jika tanggal lahir sudah diformat, tambahkan juga
                                        if ($pasien->tanggal_lahir_formatted) {
                                            $ttl[] = $pasien->tanggal_lahir_formatted;
                                        }
                                    @endphp
                                    {{ implode(', ', $ttl) ?: '—' }} {{-- Gabungkan array TTL dengan koma, atau tampilkan '—' jika kosong --}}
                                </div>
                            </div>

                            {{-- Penanggung Nifas --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Penanggung Nifas {{-- Label siapa yang menangani nifas --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->role_penanggung ?? '—' }} {{-- Bisa 'Bidan', 'Rumah Sakit', atau 'Puskesmas' (default dari query) --}}
                                </div>
                            </div>

                            {{-- Fasilitas Penanggung --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Fasilitas Kesehatan Penanggung {{-- Nama faskes yang menangani --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    @if ($pasien->role_penanggung === 'Bidan')
                                        {{ $pasien->nama_puskesmas ?? '—' }} {{-- Jika penanggung Bidan, tampilkan nama puskesmas bidan --}}
                                    @elseif ($pasien->role_penanggung === 'Rumah Sakit')
                                        {{ $pasien->nama_rs ?? '—' }} {{-- Jika penanggung RS, tampilkan nama rumah sakit --}}
                                    @else
                                        — {{-- Jika penanggung Puskesmas langsung, di sini belum ditampilkan nama spesifik --}}
                                    @endif
                                </div>
                            </div>

                            {{-- Kontak --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Kontak {{-- Label nomor telefon --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D]">
                                    {{ $pasien->phone ?? '—' }} {{-- Nomor telepon pasien, atau '—' jika kosong --}}
                                </div>
                            </div>

                            {{-- Alamat --}}
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-6 py-3 text-[#4B4B4B] border-r border-[#F5F5F5]">
                                    Alamat {{-- Label alamat --}}
                                </div>
                                <div class="px-4 sm:px-6 py-3 text-[#1D1D1D] leading-relaxed">
                                    {{ $pasien->address ?? '—' }} {{-- Alamat detail dari kolom users.address --}}
                                    @php
                                        // Array untuk menyimpan bagian lokasi: kecamatan, kabupaten, provinsi
                                        $lokasi = [];
                                        // Jika ada kecamatan, tambahkan
                                        if ($pasien->PKecamatan) {
                                            $lokasi[] = $pasien->PKecamatan;
                                        }
                                        // Jika ada kabupaten, tambahkan
                                        if ($pasien->PKabupaten) {
                                            $lokasi[] = $pasien->PKabupaten;
                                        }
                                        // Jika ada provinsi, tambahkan
                                        if ($pasien->PProvinsi) {
                                            $lokasi[] = $pasien->PProvinsi;
                                        }
                                    @endphp
                                    @if (!empty($lokasi)) {{-- Jika ada salah satu bagian lokasi --}}
                                        <span class="block text-[#7C7C7C] mt-0.5">
                                            {{ implode(', ', $lokasi) }} {{-- Tampilkan kecamatan, kabupaten, provinsi dipisah koma --}}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div> {{-- end divide: penutup kumpulan baris informasi --}}
                    </div> {{-- end card: penutup kartu putih --}}
                </section>

                {{-- =========================
                     2. DATA ANAK (TABEL)
                   ========================== --}}
                <section class="bg-white rounded-2xl border border-[#ECECEC] shadow-sm px-4 py-4 sm:px-6 sm:py-5 space-y-3 sm:space-y-4">
                    {{-- Header section Data Anak --}}
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm sm:text-base font-semibold">
                            Data Anak {{-- Judul section data anak --}}
                        </h2>
                        <span class="text-[11px] sm:text-xs text-[#9B9B9B]">
                            Total: <span class="font-medium text-[#000000CC]">{{ $anakList->count() }}</span> anak {{-- Hitung jumlah anak dalam episode nifas ini --}}
                        </span>
                    </div>

                    @if ($anakList->isEmpty()) {{-- Jika belum ada data anak --}}
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada data anak yang tercatat untuk nifas ini.
                        </p>
                    @else {{-- Jika ada data anak --}}
                        <div class="overflow-x-auto"> {{-- Supaya tabel bisa di-scroll horizontal jika kepanjangan --}}
                            <table class="min-w-[700px] w-full text-xs sm:text-sm text-left border-collapse">
                                <thead class="bg-[#F7F7F7] text-[#7C7C7C] border-b border-[#E5E5E5]">
                                    <tr>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Anak ke-</th> {{-- Kolom urutan anak --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Nama Anak</th> {{-- Kolom nama anak --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tanggal Lahir</th> {{-- Kolom tanggal lahir --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Jenis Kelamin</th> {{-- Kolom jenis kelamin --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Berat Lahir (kg)</th> {{-- Kolom berat lahir --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Panjang (cm)</th> {{-- Kolom panjang lahir --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($anakList as $anak) {{-- Loop setiap anak dalam list --}}
                                        <tr class="border-b border-[#F0F0F0] hover:bg-[#FAFAFA] transition"> {{-- Satu baris data anak --}}
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->anak_ke }} {{-- Urutan anak (ke-1, ke-2, dst) --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->nama_anak }} {{-- Nama anak --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->tanggal_lahir
                                                    ? \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d F Y')
                                                    : '—' }} {{-- Tanggal lahir diformat lokal, atau '—' jika null --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->jenis_kelamin }} {{-- Jenis kelamin anak --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->berat_lahir_anak }} {{-- Berat lahir (kg) --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $anak->panjang_lahir_anak }} {{-- Panjang lahir (cm) --}}
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
                    {{-- Header section Kunjungan Nifas --}}
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm sm:text-base font-semibold">
                            Kunjungan Nifas (KF) {{-- Judul section kunjungan nifas --}}
                        </h2>
                        <span class="text-[11px] sm:text-xs text-[#9B9B9B]">
                            Total kunjungan:
                            <span class="font-medium text-[#000000CC]">{{ $kunjunganNifas->count() }}</span> {{-- Jumlah total baris data KF --}}
                        </span>
                    </div>

                    @if ($kunjunganNifas->isEmpty()) {{-- Jika belum ada kunjungan nifas --}}
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada data kunjungan nifas yang tercatat.
                        </p>
                    @else {{-- Jika ada kunjungan nifas --}}
                        <div class="overflow-x-auto"> {{-- Scroll horizontal jika tabel kepanjangan --}}
                            <table class="min-w-[760px] w-full text-xs sm:text-sm text-left border-collapse">
                                <thead class="bg-[#F7F7F7] text-[#7C7C7C] border-b border-[#E5E5E5]">
                                    <tr>
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Kunjungan ke-</th> {{-- Nomor kunjungan nifas --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tanggal Kunjungan</th> {{-- Tanggal kunjungan --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Anak</th> {{-- Identitas anak terkait kunjungan --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Tekanan Darah (SBP/DBP)</th> {{-- Tekanan darah systolic/diastolic --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">MAP</th> {{-- Mean Arterial Pressure --}}
                                        <th class="px-3 sm:px-4 py-2 font-semibold">Kesimpulan Pantauan</th> {{-- Status akhir pantauan --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kunjunganNifas as $kf) {{-- Loop setiap kunjungan nifas --}}
                                        <tr class="border-b border-[#F0F0F0] hover:bg-[#FAFAFA] transition"> {{-- Satu baris data KF --}}
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->kunjungan_nifas_ke }} {{-- Nomor kunjungan (KF1, KF2, dst secara angka) --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->tanggal_kunjungan
                                                    ? \Carbon\Carbon::parse($kf->tanggal_kunjungan)->translatedFormat('d F Y')
                                                    : '—' }} {{-- Tanggal kunjungan diformat, atau '—' --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                @php
                                                    // Siapkan label gabungan untuk anak (Anak ke- + nama)
                                                    $labelAnak = [];
                                                    // Jika ada informasi anak ke-, tambahkan teks "Anak ke-X"
                                                    if ($kf->anak_ke) {
                                                        $labelAnak[] = 'Anak ke-' . $kf->anak_ke;
                                                    }
                                                    // Jika ada nama anak, tambahkan ke label
                                                    if ($kf->nama_anak) {
                                                        $labelAnak[] = $kf->nama_anak;
                                                    }
                                                @endphp
                                                {{ implode(' – ', $labelAnak) ?: '—' }} {{-- Gabungkan label dengan strip, atau '—' jika kosong --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->sbp }}/{{ $kf->dbp }} {{-- Tampilkan SBP/DBP, misal 120/80 --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->map }} {{-- Nilai MAP --}}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2">
                                                {{ $kf->kesimpulan_pantauan }} {{-- Kesimpulan pantauan, misal Sehat/Dirujuk/Meninggal --}}
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
                        Riwayat Penyakit Nifas {{-- Judul section riwayat penyakit nifas --}}
                    </h2>

                    @if ($riwayatPenyakit->isEmpty()) {{-- Jika belum ada riwayat penyakit nifas --}}
                        <p class="text-xs sm:text-sm text-[#9B9B9B]">
                            Belum ada riwayat penyakit nifas yang tercatat.
                        </p>
                    @else {{-- Jika ada riwayat penyakit nifas --}}
                        <div class="space-y-2.5 text-xs sm:text-sm"> {{-- List kartu penyakit dengan jarak antar kartu --}}
                            @foreach ($riwayatPenyakit as $rp) {{-- Loop setiap riwayat penyakit unik --}}
                                <div class="border border-[#E5E5E5] rounded-xl px-3 py-2.5 bg-[#FCFCFC]"> {{-- Kartu penyakit --}}
                                    <div class="font-medium text-[#171717]">
                                        {{ $rp->nama_penyakit ?? 'Penyakit tidak diketahui' }} {{-- Nama penyakit, atau fallback jika null --}}
                                    </div>
                                    @if ($rp->keterangan_penyakit_lain) {{-- Jika ada keterangan tambahan --}}
                                        <div class="text-[#7C7C7C] mt-0.5 leading-relaxed">
                                            {{ $rp->keterangan_penyakit_lain }} {{-- Menampilkan keterangan detail penyakit lain --}}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <footer class="text-center text-[11px] sm:text-xs text-[#9B9B9B] py-6"> {{-- Footer kecil di bawah halaman --}}
                © 2025 Dinas Kesehatan Kota Depok — DeLISA {{-- Teks hak cipta --}}
            </footer>
        </main>
    </div>
</body>

</html>
