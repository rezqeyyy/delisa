{{-- 
    File: index.blade.php
    Fungsi: Halaman daftar skrining ibu hamil untuk Bidan
    Menampilkan list semua data skrining preeklampsia pasien
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan – Skrining</title>
    
    {{-- Load asset CSS dan JS menggunakan Vite --}}
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js', 
        'resources/js/pasien/sidebar-toggle.js'
        ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- 
        Container utama dengan Alpine.js
        Data properties:
        - openSidebar: untuk toggle sidebar mobile
        - modalOpen: status modal konfirmasi
        - targetUrl: URL tujuan setelah konfirmasi
        - postUrl: URL untuk mark as viewed
        - isLoading: status loading saat fetch
        - confirmView(): fungsi untuk konfirmasi view detail
    --}}
    <div class="flex min-h-screen" x-data="{ openSidebar: false, modalOpen: false, targetUrl: null, postUrl: null, isLoading: false, confirmView(){ this.isLoading = true; fetch(this.postUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).finally(() => { window.location.href = this.targetUrl; }); } }">
        
        {{-- Component sidebar bidan --}}
        <x-bidan.sidebar />

        {{-- Main content area dengan responsive padding --}}
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            {{-- Header halaman --}}
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">List Skrining Ibu Hamil</h1>
            </header>

            {{-- Section tabel data skrining --}}
            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    {{-- Header section dengan icon dan deskripsi --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            {{-- Icon clipboard --}}
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Pre Eklampsia</h2>
                                <p class="text-xs text-[#7C7C7C]">Daftar pasien skrining preeklampsia terbaru</p>
                            </div>
                        </div>
                    </div>
                    <br>
                    
                    {{-- Wrapper tabel dengan horizontal scroll untuk responsif --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            {{-- Header tabel dengan background pink --}}
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
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
                                {{-- 
                                    Loop data skrining
                                    @forelse: loop dengan fallback jika data kosong
                                    ($skrinings ?? []): gunakan array kosong jika $skrinings null
                                --}}
                                @forelse(($skrinings ?? []) as $skrining)
                                    <tr>
                                        {{-- Nomor urut otomatis dari $loop->iteration --}}
                                        <td class="px-3 py-3 font-medium tabular-nums">{{ $loop->iteration }}</td>
                                        
                                        {{-- 
                                            PHP block untuk mengolah data
                                            - optional(): helper Laravel untuk akses property aman (tidak error jika null)
                                            - ?? '-': tampilkan '-' jika data null
                                        --}}
                                        @php
                                            $nama = optional(optional($skrining->pasien)->user)->name ?? '-';
                                            $nik = optional($skrining->pasien)->nik ?? '-';
                                            $tanggal = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
                                            $alamat = optional($skrining->pasien)->PKecamatan ?? optional($skrining->pasien)->PWilayah ?? '-';
                                            $telp = optional(optional($skrining->pasien)->user)->phone ?? '-';
                                            $conclusion = $skrining->conclusion_display ?? ($skrining->kesimpulan ?? 'Normal');
                                            $cls = $skrining->badge_class ?? 'bg-[#2EDB58] text-white';
                                        @endphp
                                        
                                        {{-- Tampilkan data pasien --}}
                                        <td class="px-3 py-3">{{ $nama }}</td>
                                        <td class="px-3 py-3 tabular-nums">{{ $nik }}</td>
                                        <td class="px-3 py-3">{{ $tanggal }}</td>
                                        <td class="px-3 py-3">{{ $alamat }}</td>
                                        <td class="px-3 py-3">{{ $telp }}</td>
                                        
                                        {{-- 
                                            Badge kesimpulan dengan warna dinamis
                                            - risk (merah): bg-[#E20D0D]
                                            - warn (kuning): bg-[#FFC400]
                                            - normal (hijau): bg-[#39E93F]
                                        --}}
                                        <td class="px-3 py-3">
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ ($skrining->badge_variant==='risk') ? 'bg-[#E20D0D] text-white' : (($skrining->badge_variant==='warn') ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                                {{ $skrining->conclusion_display ?? ($skrining->kesimpulan ?? 'Normal') }}
                                            </span>
                                        </td>
                                        
                                        {{-- 
                                            Tombol view detail
                                            @click: set modal data dan buka modal
                                            - targetUrl: halaman show detail
                                            - postUrl: endpoint untuk mark as viewed
                                        --}}
                                        <td class="px-3 py-3">
                                            <button @click="modalOpen=true; targetUrl='{{ route('bidan.skrining.show', $skrining->id) }}'; postUrl='{{ route('bidan.skrining.markAsViewed', $skrining->id) }}'" class="px-4 py-1.5 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">View</button>
                                        </td>
                                    </tr>
                                {{-- Tampilan jika data kosong --}}
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">Belum ada data skrining yang lengkap.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- 
                Modal konfirmasi view detail
                x-show: tampilkan modal jika modalOpen true
                x-transition: animasi fade in/out
                fixed inset-0: full screen overlay
            --}}
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                 style="display: none;">
                {{-- 
                    Modal box dengan animasi scale
                    @click.away: tutup modal jika klik di luar box
                --}}
                <div @click.away="modalOpen = false"
                     x-show="modalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl text-center">
                    
                    {{-- Konten modal --}}
                    <h3 class="text-2xl font-bold text-gray-800">Ingin Melihat Detail Data Pasien?</h3>
                    <p class="mt-2 text-gray-600">Pilih "Ya" untuk melihat dan "Batal" untuk membatalkan</p>
                    
                    {{-- Tombol aksi modal --}}
                    <div class="mt-8 flex justify-center gap-4">
                        {{-- 
                            Tombol Ya (konfirmasi)
                            @click: jalankan confirmView()
                            :disabled: disable button saat loading
                            x-show: toggle text loading
                        --}}
                        <button @click="confirmView()"
                                :disabled="isLoading"
                                class="rounded-lg bg-green-500 px-8 py-3 text-base font-medium text-white shadow-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 disabled:bg-gray-400">
                            <span x-show="!isLoading">Ya</span>
                            <span x-show="isLoading">Loading...</span>
                        </button>
                        
                        {{-- Tombol Batal - tutup modal --}}
                        <button @click="modalOpen = false"
                                class="rounded-lg bg-red-500 px-8 py-3 text-base font-medium text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
                            Batal
                        </button>                        
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok – DeLISA
            </footer>
        </main>
    </div>
</body>
</html>