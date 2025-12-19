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
    <div class="flex min-h-screen" x-data="{ openSidebar: false, modalOpen: false, targetUrl: null, postUrl: null, isLoading: false, filterModalOpen: false, confirmView(){ this.isLoading = true; fetch(this.postUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).finally(() => { window.location.href = this.targetUrl; }); } }">
        
        {{-- Component sidebar bidan --}}
        <x-bidan.sidebar />

        {{-- Main content area dengan responsive padding --}}
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            {{-- Header halaman --}}
            <header class="mb-6">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-[#1D1D1D]">List Skrining Ibu Hamil</h1>
            </header>

            {{-- Section tabel data skrining --}}
            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    {{-- Header section dengan icon dan deskripsi --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Ibu Hamil</h2>
                                <p class="text-gray-600">Data pasien yang melakukan pengecekan pada puskesmas ini</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 md:gap-3 w-full md:w-auto">
                            <form method="GET" action="{{ route('bidan.skrining') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                                <input type="text" name="q" value="{{ request('q','') }}" placeholder="Cari nama atau NIK..." class="border rounded-md px-3 py-1 text-sm w-full md:w-48" />
                                <button type="submit" class="px-3 py-1 rounded-md border text-sm w-full md:w-auto">Cari</button>
                                <button type="button" @click="filterModalOpen = true" class="px-3 py-1 rounded-md border text-sm w-full md:w-auto">Filter</button>
                            </form>
                            <a href="{{ route('bidan.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium w-full md:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" /><path d="M9 15h6" /><path d="M12 18V12" /></svg>
                                Download Excel
                            </a>
                            <a href="{{ route('bidan.export.pdf', request()->query()) }}" class="inline-flex items-center gap-2 px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium w-full md:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" /><path d="M9 15h6" /><path d="M12 18V12" /></svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                    <br>
                    <div x-show="filterModalOpen"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                         style="display: none;">
                        <div @click.away="filterModalOpen = false"
                             class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"
                             x-data="{ 
                                statusNormal: {{ request('status')==='normal' ? 'true' : 'false' }}, 
                                statusRisk: {{ request('status')==='risk' ? 'true' : 'false' }}, 
                                fromDate: '{{ request('from') }}', 
                                toDate: '{{ request('to') }}' 
                             }">
                            <h3 class="text-xl font-semibold text-[#1D1D1D]">Filter Data</h3>
                            <form method="GET" action="{{ route('bidan.skrining') }}" class="mt-5 space-y-6">
                                <input type="hidden" name="q" value="{{ request('q','') }}" />
                                <input type="hidden" name="status" :value="statusRisk && !statusNormal ? 'risk' : (!statusRisk && statusNormal ? 'normal' : '')" />

                                <div class="space-y-3">
                                    <p class="text-sm font-medium text-[#1D1D1D]">Status Pasien</p>
                                    <div class="flex items-center gap-6">
                                        <label class="inline-flex items-center gap-2"><input type="checkbox" x-model="statusNormal" class="border rounded"> <span>Normal</span></label>
                                        <label class="inline-flex items-center gap-2"><input type="checkbox" x-model="statusRisk" class="border rounded"> <span>Beresiko</span></label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-sm font-medium text-[#1D1D1D]">Periode</p>
                                    <div class="flex items-center gap-3">
                                        <input type="date" name="from" x-model="fromDate" placeholder="dd/mm/yyyy" class="border border-[#D9D9D9] rounded-lg px-3 py-2 text-sm w-full" />
                                        <span class="text-[#7C7C7C] text-xs">s/d</span>
                                        <input type="date" name="to" x-model="toDate" placeholder="dd/mm/yyyy" class="border border-[#D9D9D9] rounded-lg px-3 py-2 text-sm w-full" />
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="statusNormal=false; statusRisk=false; fromDate=''; toDate=''; window.location.href='{{ route('bidan.skrining') }}'" class="px-4 py-2 rounded-md bg-white border border-[#D9D9D9] text-[#1D1D1D] hover:bg-gray-50">Reset</button>
                                    <button type="button" @click="filterModalOpen=false" class="px-4 py-2 rounded-md bg-white border border-[#D9D9D9] text-[#1D1D1D] hover:bg-gray-50">Batal</button>
                                    <button type="submit" class="px-4 py-2 rounded-md bg-[#B9257F] hover:bg-[#a31f70] text-white">Terapkan Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
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
                                        {{-- Nomor urut mengikuti halaman (offset berdasarkan firstItem) --}}
                                        <td class="px-3 py-3 font-medium tabular-nums">{{ ($skrinings->firstItem() ?? 1) + $loop->index }}</td>
                                        
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
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ ($skrining->badge_variant==='risk') ? 'bg-[#E20D0D] text-white' : 'bg-[#39E93F] text-white' }}">
                                                {{ $skrining->conclusion_display ?? 'Normal' }}
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
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-xs text-[#7C7C7C]">
                            Menampilkan 
                            {{ $skrinings->firstItem() }}–{{ $skrinings->lastItem() }} 
                            dari 
                            {{ $skrinings->total() }} 
                            data
                        </div>
                        <nav class="inline-flex items-center rounded-md border border-[#D9D9D9] overflow-hidden">
                            <a href="{{ $skrinings->previousPageUrl() ?? '#' }}" class="px-3 py-1 text-sm text-[#1D1D1D] {{ $skrinings->onFirstPage() ? 'opacity-40 pointer-events-none' : 'hover:bg-gray-100' }}">Previous</a>
                            @php
                                $current = $skrinings->currentPage();
                                $last    = $skrinings->lastPage();
                                $start   = max(1, min($current - 1, $last - 2));
                                $end     = min($last, $start + 2);
                            @endphp
                            @for ($page = $start; $page <= $end; $page++)
                                <a href="{{ $skrinings->url($page) }}" class="px-3 py-1 text-sm border-l border-[#D9D9D9] {{ $page === $current ? 'bg-[#B9257F] text-white' : 'text-[#1D1D1D] hover:bg-gray-100' }}">{{ $page }}</a>
                            @endfor
                            <a href="{{ $skrinings->nextPageUrl() ?? '#' }}" class="px-3 py-1 text-sm border-l border-[#D9D9D9] text-[#1D1D1D] {{ $skrinings->hasMorePages() ? 'hover:bg-gray-100' : 'opacity-40 pointer-events-none' }}">Next</a>
                        </nav>
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