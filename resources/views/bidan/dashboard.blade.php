<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Dashboard</title>

    {{-- Memuat file CSS dan JS utama untuk tampilan dashboard Bidan via Vite --}}
    {{-- File JS dropdown.js mengatur interaksi dropdown (profil, notifikasi, dsb.) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- Wrapper utama halaman dashboard Bidan.
         x-data="{ openSidebar: false }" → state Alpine.js untuk buka/tutup sidebar di layar kecil --}}
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        {{-- Komponen Blade sidebar Bidan.
             Berisi menu navigasi ke halaman lain: Dashboard, Data Pasien, Skrining, Nifas, dll.
             Klik menu di sidebar → pindah halaman sesuai route yang didefinisikan di komponen tersebut. --}}
        <x-bidan.sidebar />

        {{-- Area konten utama dashboard (di samping kanan sidebar). --}}
        <main
            class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto overflow-x-hidden">
            {{-- HEADER ATAS: Judul + tombol-tombol kecil di kanan --}}
            <header class="flex flex-col gap-4 mb-2 sm:mb-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-[#1D1D1D]">Dashboard Bidan</h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Ringkasan pemantauan pasien preeklampsia & nifas yang ditangani Bidan.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 flex-none justify-end">
                        {{-- Tombol shortcut (ikon grafik) → menuju Dashboard Puskesmas (route 'puskesmas.dashboard') --}}
                        <a href="{{ route('puskesmas.dashboard') }}"
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Chart.svg') }}" alt="Dashboard Puskesmas"
                                class="w-5 h-5">
                        </a>

                        {{-- Notifikasi (saat ini hanya tampilan ikon, belum ada dropdown notifikasi) --}}
                        <button
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" alt="Notifikasi"
                                class="w-5 h-5">
                        </button>

                        {{-- Tombol Profil + Dropdown --}}
                        <div id="profileWrapper" class="relative">
                            <button id="profileBtn"
                                class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">

                                {{-- Foto profil user jika ada (disimpan di storage) --}}
                                @if (Auth::user()?->photo)
                                    <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                        class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                                @else
                                    {{-- Avatar default jika user belum upload foto --}}
                                    <span
                                        class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                            <path
                                                d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                        </svg>
                                    </span>
                                @endif

                                <div class="leading-tight pr-1 text-left">
                                    <p class="text-[13px] font-semibold text-[#1D1D1D]">
                                        {{ auth()->user()->name ?? 'Puskesmas' }}
                                    </p>
                                </div>
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                    class="w-4 h-4 opacity-70" alt="More" />
                            </button>

                            {{-- Dropdown profil (muncul saat button profile diklik, di-handle oleh JS dropdown.js) --}}
                            <div id="profileDropdown"
                                class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                                <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                    <p class="text-sm font-medium text-[#1D1D1D]">
                                        {{ auth()->user()->name ?? 'Puskesmas' }}
                                    </p>
                                </div>
                                {{-- Form logout.
                                     method="POST" ke route('logout') → akan memproses logout Laravel standar
                                     dan mengalihkan kembali ke halaman login sesuai konfigurasi auth. --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-[#F9F9F9] flex items-center gap-2">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- SECTION KARTU STATISTIK ATAS --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Kartu Daerah Asal Pasien --}}
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 lg:row-span-2 flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        {{-- Kartu ringkasan: menampilkan jumlah pasien skrining berdasarkan asal kota (Depok / Non Depok).
                             Angka diambil dari variabel $daerahAsal yang disiapkan di DashboardController. --}}
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Daerah Asal Pasien</h3>
                        <button class="w-9 h-9 rounded-lg border border-[#E9E9E9] bg-white grid place-items-center">
                            {{-- Tombol kanan atas kartu (saat ini hanya ikon panah, dekoratif / bisa dipakai nanti untuk menu lain) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-1 items-center justify-center text-center divide-x divide-[#E9E9E9]">
                        <div class="flex-1 px-4">
                            <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Depok</div>
                            <br>
                            <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">
                                {{-- Jumlah pasien dari Depok --}}
                                {{ $daerahAsal->depok ?? 0 }}</div>
                        </div>
                        <div class="flex-1 px-4">
                            <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Non Depok</div>
                            <br>
                            <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">
                                {{-- Jumlah pasien dari luar Depok --}}
                                {{ $daerahAsal->non_depok ?? 0 }}</div>
                        </div>
                    </div>

                    {{-- Footer teks tambahan kartu ini --}}
                    <p class="mt-4 text-xs text-[#7C7C7C]">
                        Data berdasarkan pasien yang pernah melakukan skrining preeklampsia di puskesmas ini.
                    </p>
                </div>

                {{-- Kartu Pasien Hadir / Tidak Hadir --}}
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            {{-- Kartu "Pasien Hadir": menampilkan jumlah pasien yang menjalani skrining hari ini vs. tidak hadir.
                                 Variabel: $pasienHadir dan $pasienTidakHadir. --}}
                            <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">Pasien Hadir</h2>
                            <p class="text-xs text-[#7C7C7C]">Rekap kehadiran skrining hari ini</p>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FFF0F7]">
                            <span class="w-2 h-2 rounded-full bg-[#E91E63]"></span>
                            <span class="text-[11px] font-medium text-[#E91E63]">Hari Ini</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-[#7C7C7C] mb-1">Jumlah Hadir</div>
                            {{-- Jumlah pasien yang skriningnya diperbarui hari ini --}}
                            <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pasienHadir ?? 0 }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-[#7C7C7C] mb-1">Tidak Hadir / Tidak Tercatat</div>
                            {{-- Total skrining - yang hadir --}}
                            <div class="text-lg font-semibold text-[#FF3B30]">{{ $pasienTidakHadir ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Data Pasien Nifas --}}
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        {{-- Kartu ringkasan nifas: total pasien nifas dan yang sudah melakukan Kunjungan Nifas pertama (KFI).
                             Data berasal dari variabel $totalNifas dan $sudahKFI (DashboardController). --}}
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien Nifas</h3>
                        <button class="w-9 h-9 rounded-lg border border-[#E9E9E9] bg-white grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Total Pasien Nifas</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $totalNifas ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Sudah KFI</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                {{-- Kartu Pemantauan Nifas --}}
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        {{-- Kartu status pemantauan nifas: menampilkan jumlah pasien dengan kesimpulan pantauan Sehat / Dirujuk / Meninggal.
                             Diambil dari variabel $pemantauanSehat, $pemantauanDirujuk, $pemantauanMeninggal. --}}
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Pemantauan</h3>
                        <button class="w-9 h-9 rounded-lg border border-[#E9E9E9] bg-white grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Sehat</span>
                            <span class="font-bold text-[#39E93F]">{{ $pemantauanSehat ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Dirujuk</span>
                            <span class="font-bold text-[#FFC400]">{{ $pemantauanDirujuk ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Meninggal</span>
                            <span class="font-bold text-[#FF3B30]">{{ $pemantauanMeninggal ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION DATA PRE EKLAMSIA TERBARU (TABEL) --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                <div
                    class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        {{-- Section tabel: menampilkan daftar 5 pasien skrining preeklampsia terbaru.
                             Data berasal dari collection $pasienTerbaru (hasil query di DashboardController). --}}
                        <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">Data Pasien Pre Eklampsia</h2>
                        <p class="text-xs text-[#7C7C7C]">Daftar pasien skrining preeklampsia terbaru</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Tombol "Lihat Semua" → membuka halaman daftar Skrining Bidan
                             route('bidan.skrining') biasanya mengarah ke SkriningController@index. --}}
                        <a href="{{ route('bidan.skrining') }}"
                            class="px-5 py-2 rounded-full border border-[#D9D9D9] bg-white text-[#1D1D1D] font-semibold flex items-center gap-2">
                            <span>Lihat Semua</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Tabel daftar pasien skrining --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[#7C7C7C]">
                            <tr class="text-left">
                                <th class="px-3 py-2">No</th>
                                <th class="px-3 py-2">Nama Pasien</th>
                                <th class="px-3 py-2">NIK</th>
                                <th class="px-3 py-2">Tanggal Skrining</th>
                                <th class="px-3 py-2">Alamat</th>
                                <th class="px-3 py-2">No Telp</th>
                                <th class="px-3 py-2">Kesimpulan</th>
                                <th class="px-3 py-2">View Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E9E9E9]">
                            {{-- Looping semua skrining terbaru (maks 5 data) --}}
                            @forelse(($pasienTerbaru ?? []) as $skrining)
                                <tr>
                                    {{-- Nomor urut baris --}}
                                    <td class="px-3 py-3 font-medium tabular-nums">{{ $loop->iteration }}</td>
                                    {{-- Nama pasien → dari relasi pasien->user --}}
                                    <td class="px-3 py-3">{{ $skrining->pasien->user->name ?? '-' }}</td>
                                    {{-- NIK pasien --}}
                                    <td class="px-3 py-3 tabular-nums">{{ $skrining->pasien->nik ?? '-' }}</td>
                                    {{-- Tanggal skrining (format d/m/Y) --}}
                                    <td class="px-3 py-3">
                                        {{ optional($skrining->created_at)->format('d/m/Y') ?? '-' }}</td>
                                    {{-- Alamat (kecamatan) --}}
                                    <td class="px-3 py-3">{{ $skrining->pasien->PKecamatan ?? '-' }}</td>
                                    {{-- Nomor telepon pasien --}}
                                    <td class="px-3 py-3">{{ $skrining->pasien->user->phone ?? '-' }}</td>
                                    {{-- Badge kesimpulan skrining (warna beda sesuai resiko) --}}
                                    <td class="px-3 py-3">
                                        @php($label = strtolower(trim($skrining->kesimpulan ?? '')))
                                        @php($isRisk = in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']))
                                        @php($isWarn = in_array($label, ['waspada', 'menengah', 'sedang', 'risiko sedang']))
                                        @php($display = $isRisk ? 'Beresiko' : (
                                            $isWarn ? 'Waspada' : ($label === 'aman' ? 'Aman' : $skrining->kesimpulan ?? '-')
                                        ))
                                        <span
                                            class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isRisk ? 'bg-[#FF3B30] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                            {{ $display }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        {{-- Tombol View → membuka halaman detail skrining pasien tersebut
                                             route('bidan.skrining.show', $skrining->id)
                                             biasanya mengarah ke SkriningController@show untuk role Bidan. --}}
                                        <a href="{{ route('bidan.skrining.show', $skrining->id) }}"
                                            class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                {{-- Jika belum ada data skrining preeklampsia --}}
                                <tr>
                                    <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">
                                        Belum ada data pasien pre eklampsia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- FOOTER --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
