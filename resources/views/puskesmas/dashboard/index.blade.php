<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Dashboard</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
        'resources/js/puskesmas/search.js',
        'resources/js/dropdown.js', 
        'resources/js/puskesmas/sidebar-toggle.js'
        ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center gap-3 flex-nowrap">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-3 flex items-center">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                        </span>
                        <input type="text" placeholder="Search..." 
                        id="dashboardSearch"
                        data-search="true"
                        data-target="#patientsTableBody"
                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-none justify-end">
                    <a href="{{ route('puskesmas.profile.edit') }}" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn" class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            
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
                                    {{ auth()->user()->name ?? 'Puskesmas' }}
                                </p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Puskesmas' }}
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

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5 lg:row-span-2 flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Daerah Asal Pasien</h3>
                    </div>
                    <div class="flex flex-1 items-center justify-center text-center divide-x divide-[#E9E9E9]">
                        <div class="flex-1 px-4">
                            <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Depok</div>
                            <br>
                            <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">{{ $asalDepok ?? 0 }}</div>
                        </div>
                        <div class="flex-1 px-4">
                            <div class="text-lg lg:text-xl font-semibold text-[#7C7C7C]">Non Depok</div>
                            <br>
                            <div class="tabular-nums leading-none text-6xl lg:text-7xl font-bold text-[#1D1D1D]">{{ $asalNonDepok ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Resiko Preeklampsia</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Normal</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $resikoNormal ?? 0 }}</span>
                        </div>
                        <hr class="border-[#E9E9E9]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7C7C7C]">Pasien Beresiko Preeklampsia</span>
                            <span class="font-bold text-[#1D1D1D]">{{ $resikoPreeklampsia ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-lg text-[#1D1D1D]">Pasien Hadir</h3>
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
                    <div class="flex items-center justify-between mb-3">
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
                            <span class="font-bold text-[#1D1D1D]">{{ $sudahKFI ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-5">
                    <div class="flex items-center justify-between mb-3">
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

            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Preeklampsia</h2>
                                <p class="text-xs text-[#7C7C7C]">Daftar pasien skrining preeklampsia terbaru</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('puskesmas.skrining.index') }}" class="px-5 py-2 rounded-full border border-[#D9D9D9] bg-white text-[#1D1D1D] font-semibold flex items-center gap-2">
                                View All
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}" class="w-4 h-4 opacity-80" alt="Arrow" />
                            </a>
                        </div>
                    </div>
                    <br>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-3 py-2">No.</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">NIK</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Tanggal Lahir</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">Kesimpulan</th>
                                    <th class="px-3 py-2">View Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]" id="patientsTableBody">
                                @forelse(($pePatients ?? []) as $p)
                                    <tr class="text-left">
                                        <td class="px-3 py-3 font-medium tabular-nums">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-3">{{ $p->nama ?? '-' }}</td>
                                        <td class="px-3 py-3 tabular-nums">{{ $p->nik ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $p->telp ?? '-' }}</td>
                                        <td class="px-3 py-3">{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-3">{{ $p->alamat ?? $p->PKecamatan ?? '-' }}</td>
                                        <td class="px-3 py-3">
                                            @php($label = strtolower(trim($p->kesimpulan ?? '')))
                                            @php($isRisk = in_array($label, ['beresiko','berisiko','risiko tinggi','tinggi']))
                                            @php($isWarn = in_array($label, ['waspada','menengah','sedang','risiko sedang']))
                                            @php($display = $isRisk ? 'Beresiko' : ($isWarn ? 'Waspada' : 'Normal'))
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isRisk ? 'bg-[#E20D0D] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                                {{ $display }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div x-data="{ open:false }" class="relative inline-block">
                                                <button @click="open=!open" class="px-4 py-1 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">View</button>
                                                <div x-show="open" @click.outside="open=false" x-transition class="mt-2 p-3 border border-[#E9E9E9] rounded-lg bg-[#FAFAFA] text-xs text-[#1D1D1D] w-max max-w-[60ch] shadow">
                                                    <div class="font-semibold mb-1">Hasil Akhir</div>
                                                    <div class="mb-2">{{ $p->hasil_akhir ?? '-' }}</div>
                                                    <div class="font-semibold mb-1">Rekomendasi</div>
                                                    <div>{{ $p->rekomendasi ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-3 py-6 text-center text-[#7C7C7C]">Belum ada data pasien preeklampsia.</td>
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



        