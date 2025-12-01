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

            <section class="space-y-4">
                <h1 class="text-2xl font-semibold text-[#1D1D1D]">List Skrining Ibu Hamil</h1>
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
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Ibu Hamil</h2>
                                <p class="text-xs text-[#7C7C7C]">Data pasien yang melakukan pengecekan pada rumah sakit
                                    ini</p>
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
                         @click.self="showFilter = false">
                        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                             @click.stop>
                            <div class="sticky top-0 bg-white border-b border-[#E9E9E9] px-6 py-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-[#1D1D1D]">Filter Data Pasien</h3>
                                <button @click="showFilter = false" class="text-[#7C7C7C] hover:text-[#1D1D1D]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form action="{{ route('rs.skrining.index') }}" method="GET" class="p-6 space-y-4">
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
                                        <option value="Waspada" {{ request('risiko') === 'Waspada' ? 'selected' : '' }}>Waspada</option>
                                        <option value="Tidak Berisiko" {{ request('risiko') === 'Tidak Berisiko' ? 'selected' : '' }}>Tidak Berisiko</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="flex items-center gap-3 pt-4">
                                    <button type="submit"
                                        class="flex-1 px-6 py-2.5 bg-pink-500 text-white rounded-full font-semibold hover:bg-pink-600 transition">
                                        Terapkan Filter
                                    </button>
                                    <a href="{{ route('rs.skrining.index') }}"
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
                                    <a href="{{ route('rs.skrining.index', array_merge(request()->except('nik'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('nama'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Nama: {{ request('nama') }}
                                    <a href="{{ route('rs.skrining.index', array_merge(request()->except('nama'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('tanggal_dari') || request('tanggal_sampai'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Tanggal: {{ request('tanggal_dari') ?? '...' }} s/d {{ request('tanggal_sampai') ?? '...' }}
                                    <a href="{{ route('rs.skrining.index', array_merge(request()->except(['tanggal_dari', 'tanggal_sampai']))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            @if(request('risiko'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">
                                    Risiko: {{ request('risiko') }}
                                    <a href="{{ route('rs.skrining.index', array_merge(request()->except('risiko'))) }}" class="hover:text-pink-900">×</a>
                                </span>
                            @endif
                            <a href="{{ route('rs.skrining.index') }}" class="text-xs text-pink-600 hover:text-pink-800 font-semibold">
                                Hapus semua filter
                            </a>
                        </div>
                    @endif

                    <br>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-[#7C7C7C]">
                                <tr class="text-left">
                                    <th class="px-3 py-2 w-10"><input type="checkbox" class="rounded"></th>
                                    <th class="px-3 py-2">NIK Pasien</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">Kedatangan</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Resiko</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse($skrinings as $r)
                                    @php($skr = $r->skrining)
                                    @php($pas = optional($skr)->pasien)
                                    @php($usr = optional($pas)->user)
                                    @php($kesimpulan = $r->kesimpulan ?? 'Tidak Berisiko')
                                    @php($isHigh = $kesimpulan === 'Beresiko')
                                    @php($isWarn = $kesimpulan === 'Waspada')
                                    <tr>
                                        <td class="px-3 py-3"><input type="checkbox" class="rounded"></td>
                                        <td class="px-3 py-3 font-medium text-[#1D1D1D]">{{ $pas->nik ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $usr->name ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ optional($skr->created_at)->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-3 py-3">{{ $pas->PKecamatan ?? ($pas->PWilayah ?? '-') }}</td>
                                        <td class="px-3 py-3">{{ $usr->phone ?? ($pas->no_telepon ?? '-') }}</td>
                                        <td class="px-3 py-3">
                                            <span
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isHigh ? 'bg-[#E20D0D] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                                {{ $kesimpulan }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('rs.skrining.edit', $skr->id) }}"
                                                    class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-gray-50 transition">Edit</a>
                                                <a href="{{ route('rs.skrining.show', $skr->id) }}"
                                                    class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs hover:bg-gray-50 transition">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">
                                            @if(request()->hasAny(['nik', 'nama', 'tanggal_dari', 'tanggal_sampai', 'risiko']))
                                                Tidak ada data yang sesuai dengan filter yang dipilih.
                                            @else
                                                Belum ada skrining yang tercatat di RS ini.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-[#7C7C7C]">
                        <div>
                            Halaman {{ $skrinings->currentPage() }} dari {{ $skrinings->lastPage() }}
                        </div>
                        <div>
                            {{ $skrinings->onEachSide(1)->links() }}
                        </div>
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