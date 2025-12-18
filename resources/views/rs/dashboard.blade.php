<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Sakit — Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false, showFilter: false }">

        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center gap-3 flex-nowrap">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-semibold text-[#1D1D1D]">Dashboard Rumah Sakit</h1>
                        <p class="text-xs sm:text-sm text-[#7C7C7C]">
                            Ringkasan pemantauan pasien preeklampsia yang ditangani Rumah Sakit.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-none justify-end">
                    <a href="{{ route('rs.profile.edit') }}"
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90"
                            alt="Setting">
                    </a>

                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">

                            @if (Auth::user()?->photo)
                                <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                    class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                            @else
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
                                    {{ auth()->user()->name ?? 'Rumah Sakit' }}
                                </p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Rumah Sakit' }}
                                </p>
                            </div>
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

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 border border-green-200 text-green-800 px-4 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 rounded-lg bg-blue-100 border border-blue-200 text-blue-800 px-4 py-2 text-sm">
                    {{ session('info') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-100 border border-red-200 text-red-800 px-4 py-2 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Kartu-kartu ringkasan --}}
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien Rujukan</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Setelah Melahirkan</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $rujukanSetelahMelahirkan ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Berisiko</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $rujukanBeresiko ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Resiko Preeklampsia</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Normal</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $resikoNormal ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Berisiko Preeklampsia</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $resikoPreeklampsia ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Rujukan</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $pasienRujukan ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Non Rujukan</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $pasienNonRujukan ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Pasien yang Hadir Pemeriksaan</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Hadir</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $pasienHadir ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Tidak Hadir</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $pasienTidakHadir ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Data Pasien Nifas</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Total Pasien Nifas</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $totalNifas ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Sudah KFI</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $sudahKF1 ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Pemantauan</h3>
                    </div>
                    <div class="flex items-center text-center divide-x-2 divide-[#E9E9E9]">
                        <div class="flex-1 px-4">
                            <div class="text-xs text-[#7C7C7C]">Sehat</div>
                            <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanSehat ?? 0 }}</div>
                        </div>
                        <div class="flex-1 px-4">
                            <div class="text-xs text-[#7C7C7C]">Total Dirujuk</div>
                            <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanDirujuk ?? 0 }}</div>
                        </div>
                        <div class="flex-1 px-4">
                            <div class="text-xs text-[#7C7C7C]">Meninggal</div>
                            <div class="text-3xl font-bold text-[#1D1D1D]">{{ $pemantauanMeninggal ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tabel Pasien Rujukan Pre Eklampsia --}}
            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    class="w-5 h-5 text-[#1D1D1D]" fill="currentColor">
                                    <path
                                        d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Rujukan Preeklampsia</h2>
                                <p class="text-xs text-[#7C7C7C]">
                                    Pilih satu atau lebih untuk memindahkan pasien rujukan
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button @click="showFilter = true"
                                class="px-5 py-2 rounded-full border border-[#E9E9E9] bg-white font-semibold flex items-center gap-2 hover:bg-gray-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M6 12h12M10 18h4" />
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('rs.skrining.index') }}"
                                class="px-5 py-2 rounded-full border border-[#E9E9E9] bg-white font-semibold flex items-center gap-2 hover:bg-gray-50 transition">
                                Lihat Semua
                            </a>
                        </div>
                    </div>

                    <!-- Filter Modal -->
                    <div x-show="showFilter" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                         @click.self="showFilter = false"
                         style="display: none;">
                        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                             @click.stop>
                            <div class="sticky top-0 bg-white border-b border-[#E9E9E9] px-6 py-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-[#1D1D1D]">Filter Data Pasien Preeklampsia</h3>
                                <button @click="showFilter = false" class="text-[#7C7C7C] hover:text-[#1D1D1D]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form action="{{ route('rs.dashboard') }}" method="GET" class="p-6 space-y-4">
                                <!-- NIK -->
                                <div>
                                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">NIK Pasien</label>
                                    <input type="text" name="nik" value="{{ request('nik') }}"
                                        placeholder="Masukkan NIK"
                                        class="w-full px-4 py-2 border border-[#E9E9E9] rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                                </div>

                                <!-- Nama -->
                                <div>
                                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Nama Pasien</label>
                                    <input type="text" name="nama" value="{{ request('nama') }}"
                                        placeholder="Masukkan nama pasien"
                                        class="w-full px-4 py-2 border border-[#E9E9E9] rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                                </div>

                                <!-- Tanggal -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Tanggal Dari</label>
                                        <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                                            class="w-full px-4 py-2 border border-[#E9E9E9] rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Tanggal Sampai</label>
                                        <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                                            class="w-full px-4 py-2 border border-[#E9E9E9] rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                                    </div>
                                </div>

                                <!-- Status Risiko -->
                                <div>
                                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Status Risiko</label>
                                    <select name="risiko" 
                                        class="w-full px-4 py-2 border border-[#E9E9E9] rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                                        <option value="">Semua Status</option>
                                        <option value="Beresiko" {{ request('risiko') === 'Beresiko' ? 'selected' : '' }}>Beresiko</option>
                                        <option value="Tidak Berisiko" {{ request('risiko') === 'Tidak Berisiko' ? 'selected' : '' }}>Tidak Berisiko</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="flex items-center gap-3 pt-4">
                                    <button type="submit"
                                        class="flex-1 px-6 py-2.5 bg-pink-500 text-white rounded-full font-semibold hover:bg-pink-600 transition">
                                        Terapkan Filter
                                    </button>
                                    <a href="{{ route('rs.dashboard') }}"
                                        class="flex-1 px-6 py-2.5 bg-gray-100 text-[#1D1D1D] rounded-full font-semibold hover:bg-gray-200 transition text-center">
                                        Reset Filter
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    @if(request()->hasAny(['nik', 'nama', 'tanggal_dari', 'tanggal_sampai', 'risiko']))
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="text-sm text-[#7C7C7C]">Filter aktif:</span>
                            @if(request('nik'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    NIK: {{ request('nik') }}
                                    <a href="{{ route('rs.dashboard', array_merge(request()->except('nik'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('nama'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Nama: {{ request('nama') }}
                                    <a href="{{ route('rs.dashboard', array_merge(request()->except('nama'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('tanggal_dari') || request('tanggal_sampai'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Tanggal: {{ request('tanggal_dari') ?? '...' }} s/d {{ request('tanggal_sampai') ?? '...' }}
                                    <a href="{{ route('rs.dashboard', array_merge(request()->except(['tanggal_dari', 'tanggal_sampai']))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('risiko'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Risiko: {{ request('risiko') }}
                                    <a href="{{ route('rs.dashboard', array_merge(request()->except('risiko'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            <a href="{{ route('rs.dashboard') }}" class="text-xs text-pink-600 hover:text-pink-800 font-semibold">
                                Hapus semua filter
                            </a>
                        </div>
                    @endif

                    <br>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-3 py-2">No</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">NIK Pasien</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">Kesimpulan</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse(($pePatients ?? []) as $p)
                                    <tr>

                                        <td class="px-3 py-3 font-medium text-[#1D1D1D]">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-3">
                                            {{ $p->nama ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3 font-medium text-[#1D1D1D]">
                                            {{ $p->nik ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ $p->telp ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ $p->tanggal ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ $p->alamat ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            @php($label = strtolower(trim($p->kesimpulan ?? '')))
                                            @php($isRisk = in_array($label, ['beresiko', 'berisiko', 'risiko tinggi', 'tinggi']))
                                            @php($isWarn = in_array($label, ['waspada', 'menengah', 'sedang', 'risiko sedang']))
                                            @php($display = $isRisk ? 'Beresiko' : ($isWarn ? 'Waspada' : 'Normal'))
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isRisk ? 'bg-[#E20D0D] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                                {{ $display }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2">
                                                @if (!empty($p->id))
                                                    <a href="{{ route('rs.pasien.show', $p->id) }}"
                                                        class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-gray-50 transition">
                                                        Data Pasien
                                                    </a>
                                                @endif

                                                @if (!empty($p->process_url))
                                                    <form method="POST" action="{{ $p->process_url }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-gray-50 transition">
                                                            Proses Nifas
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-3 py-6 text-center text-[#7C7C7C]">
                                            @if(request()->hasAny(['nik', 'nama', 'tanggal_dari', 'tanggal_sampai', 'risiko']))
                                                Tidak ada data yang sesuai dengan filter yang dipilih.
                                            @else
                                                Belum ada data pasien preeklampsia.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>

    </div>
</body>

</html>