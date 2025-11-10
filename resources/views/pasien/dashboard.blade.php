<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Dashboard</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js', 
        'resources/js/pasien/puskesmas-picker.js', 
        'resources/js/pasien/sidebar-toggle.js', 
        'resources/js/pasien/list-filter.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-pasien.sidebar />
            
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center gap-3 flex-nowrap">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-3 flex items-center">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                        </span>
                        <input type="text" placeholder="Search..."
                            class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-none justify-end">
                    <a href="{{ route('pasien.profile.edit') }}" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
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
                                    {{ auth()->user()->name ?? 'Nama Pasien' }}</p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Nama Pasien' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout.pasien') }}">
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

            <!-- List Skrining -->
            <section class="bg-white rounded-2xl shadow-md p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between md:flex-wrap gap-3 max-w-full">
                    <div class="flex items-center gap-3 flex-none">
                        <div class="leading-tight">
                            <h2 class="text-xl font-semibold text-[#1D1D1D]">List Skrining</h2>
                            <p class="text-xs text-[#B9257F]">Jika Status Eklampsia masih kosong, berarti skrining belum selesai terisi</p>
                        </div>
                    </div>

                    <!-- Grup aksi (dropdown + ajukan) selalu bersama -->
                    <div class="flex items-center gap-2 w-full md:w-auto min-w-0 flex-wrap md:justify-end">
                        <!-- Dropdown status -->
                        <form id="skriningFilterForm" action="{{ route('pasien.dashboard') }}" method="GET"
                            class="w-full md:w-[220px]">
                            <div class="relative w-full">
                                @php $currentStatus = $status ?? ''; @endphp
                                <select id="statusSelect" name="status"
                                        class="w-full pl-3 pr-9 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                    <option value="" {{ $currentStatus === '' ? 'selected' : '' }}>Cari Berdasarkan Status</option>
                                    <option value="" {{ ($status ?? '') === '' ? 'selected' : '' }}>Semua</option>
                                    <option value="Normal" {{ $currentStatus === 'Normal' ? 'selected' : '' }}>Tidak Berisiko</option>
                                    <option value="Waspada" {{ $currentStatus === 'Waspada' ? 'selected' : '' }}>Waspada</option>
                                    <option value="Berisiko" {{ $currentStatus === 'Berisiko' ? 'selected' : '' }}>Berisiko</option>
                                </select>
                            </div>
                        </form>

                        <!-- Tombol Ajukan Skrining -->
                        @php
                            $ajukanUrl = \Illuminate\Support\Facades\Route::has('pasien.data-diri')
                                ? route('pasien.data-diri')
                                : '#';
                        @endphp
                        <a href="{{ $ajukanUrl }}"
                        id="btnAjukanSkrining"
                        data-start-url="{{ route('pasien.data-diri') }}"
                        data-search-url="{{ route('pasien.puskesmas.search') }}"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 h-9 rounded-full bg-[#B9257F] text-white text-sm font-semibold shadow hover:bg-[#a31f70] w-full md:w-[220px]">
                            <span class="text-base leading-none">+</span>
                            <span class="leading-none">Ajukan Skrining</span>
                        </a>
                    </div>
                </div>

                <!-- Tabel daftar skrining -->
                <div class="overflow-x-auto mt-4 md:mt-6">
                    <table class="w-full table-auto border-separate
                        sm:border-spacing-x-[12px] sm:border-spacing-y-[6px]
                        md:border-spacing-x-[20px] md:border-spacing-y-[8px]
                        lg:border-spacing-x-[24px] lg:border-spacing-y-[10px]">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 md:px-6 md:py-3 text-left whitespace-nowrap">Nama Pasien</th>
                                <th class="px-3 py-2 md:px-6 md:py-3 text-left whitespace-nowrap">Tanggal Pengisian</th>
                                <th class="px-3 py-2 md:px-6 md:py-3 text-left whitespace-nowrap">Alamat</th>
                                <th class="px-3 py-2 md:px-6 md:py-3 text-left whitespace-nowrap">Kesimpulan</th>
                                <th class="px-3 py-2 md:px-6 md:py-3 text-left whitespace-nowrap">View Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($skrinings as $skrining)
                            @php
                                $nama     = optional(optional($skrining->pasien)->user)->name ?? '-';
                                $tanggal  = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
                                $alamat   = optional(optional($skrining->pasien)->user)->address ?? '-';
                                $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
                                $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);
                                $conclusion = $skrining->conclusion_display ?? ($skrining->kesimpulan ?? 'Normal');
                                $cls = $skrining->badge_class ?? 'bg-[#2EDB58] text-white';
                                $editUrl = route('pasien.skrining.edit', $skrining->id);
                                $viewUrl = route('pasien.skrining.show', $skrining->id);
                                @endphp
                                <tr class="align-middle">
                                    <td class="px-3 py-3 md:px-6 md:py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-3">
                                            <span class="font-medium text-[#1D1D1D]">{{ $nama }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 md:px-6 md:py-4 text-[#1D1D1D]">{{ $tanggal }}</td>
                                    <td class="px-3 py-3 md:px-6 md:py-4 text-[#1D1D1D]">{{ $alamat }}</td>
                                    <td class="px-3 py-3 md:px-6 md:py-4">
                                        <span class="inline-flex items-center rounded-full px-4 h-8 text-sm font-semibold leading-none whitespace-nowrap {{ $cls }}">
                                            {{ $conclusion }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 md:px-6 md:py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $editUrl }}" class="px-4 py-1.5 rounded-full bg-white border border-[#E5E5E5] hover:bg-[#F0F0F0]">
                                                Edit
                                            </a>
                                            <a href="{{ $viewUrl }}" class="px-4 py-1.5 rounded-full bg-white border border-[#E5E5E5] hover:bg-[#F0F0F0]">
                                                View
                                            </a>

                                            <form method="POST"
                                                action="{{ route('pasien.skrining.destroy', $skrining->id) }}"
                                                onsubmit="return confirm('Yakin hapus data skrining?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border px-4 py-2 text-red-600 hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-[#7C7C7C]">
                                        Belum ada data skrining.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($skrinings) && method_exists($skrinings, 'hasPages') && $skrinings->hasPages())
                    <div class="mt-4">
                        {{ $skrinings->links() }}
                    </div>
                @endif
            </section>

            <!-- Ringkasan Total Skrining & Resiko Preeklamsia -->           
            <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h2 class="font-semibold text-[#1D1D1D] mb-3">Total Skrining</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[#1D1D1D]">Sudah Selesai</span>
                            <span class="font-semibold tabular-nums">{{ $totalSelesai ?? 0 }}</span>
                        </div>
                        <div class="border-t border-[#E9E9E9]"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#1D1D1D]">Belum diisi</span>
                            <span class="font-semibold tabular-nums">{{ $totalBelum ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-md">
                    <h2 class="font-semibold text-[#1D1D1D] mb-3">Risiko Preeklamsia</h2>
                    <div class="rounded-xl {{ $riskBoxClass ?? 'bg-[#E9E9E9] text-[#1D1D1D]' }} p-6 text-center">
                        <span class="text-lg font-semibold">
                            {{ $riskPreeklampsia ? $riskPreeklampsia : 'Belum ada' }}
                        </span>
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
