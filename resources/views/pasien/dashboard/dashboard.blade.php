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
        'resources/js/pasien/list-filter.js',
        'resources/js/pasien/delete-confirm.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-pasien.sidebar />
            
        <main class="flex-1 w-full lg:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <div class="flex items-center gap-3 flex-nowrap">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="flex items-center">
                        <h1 class="text-3xl font-bold text-[#1D1D1D]">Dashboard</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-none justify-end">
                    {{-- Tombol shortcut (ikon pengaturan) → menuju Edit Profil Pasien (route 'pasien.profile.edit') --}}
                    <a href="{{ route('pasien.profile.edit') }}" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    {{-- Tombol Profil + Dropdown --}}
                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn" class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            
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


                            {{-- Teks nama disembunyikan di mobile (hidden sm:block) --}}
                            <div class="leading-tight pr-1 text-left hidden sm:block">
                                {{-- Nama user, fallback ke 'Nama Pasien' --}}
                                <p class="text-[13px] font-semibold text-[#1D1D1D] truncate max-w-[140px] sm:max-w-[200px]">
                                    {{ auth()->user()->name ?? 'Nama Pasien' }}
                                </p>
                            </div>

                            {{-- Ikon caret (panah bawah) untuk menandakan dropdown --}}
                            <!-- caret disembunyikan di mobile -->
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}" class="sm:block w-4 h-4 opacity-70" alt="More" />

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
                            <p class="text-xs text-[#B9257F]">*Selesaikan skrining sebelum membuat skrining baru</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto min-w-0 flex-wrap md:justify-start">
                        <form id="skriningFilterForm" action="{{ route('pasien.dashboard') }}" method="GET" class="w-full md:w-auto">
                            <div class="relative">
                                @php $currentStatus = $status ?? ''; @endphp
                                <select id="statusSelect" name="status"
                                        class="w-full pl-3 pr-9 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                                    <option value="" {{ $currentStatus === '' ? 'selected' : '' }}>Cari Berdasarkan Status</option>
                                    <option value="Tidak berisiko preeklampsia" {{ $currentStatus === 'Tidak berisiko preeklampsia' ? 'selected' : '' }}>Normal</option>
                                    <option value="Berisiko preeklampsia" {{ $currentStatus === 'Berisiko preeklampsia' ? 'selected' : '' }}>Berisiko</option>
                                    <option value="Skrining belum selesai" {{ $currentStatus === 'Skrining belum selesai' ? 'selected' : '' }}>Skrining belum selesai</option>
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
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 h-9 rounded-full bg-[#B9257F] text-white text-sm font-semibold shadow hover:bg-[#a31f70] w-full md:w-[220px] md:ml-3 flex-none">
                            <span class="leading-none">Tambah Skrining</span>
                        </a>
                    </div>
                </div>
                <br>
                <!-- Tabel daftar skrining -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                            <tr class="align-middle">
                                <th class="px-3 py-2">No.</th>
                                <th class="px-3 py-2">NIK</th>
                                <th class="px-3 py-2">No Telp</th>
                                <th class="px-3 py-2">Tanggal Lahir</th>
                                <th class="px-3 py-2">Alamat</th>
                                <th class="px-3 py-2">Kesimpulan</th>
                                <th class="px-3 py-2">Status Verifikasi</th>
                                <th class="px-3 py-2">View Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($skrinings as $skrining)
                            @php
                                $tanggal  = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
                                $nik      = optional($skrining->pasien)->nik ?? '-';
                                $alamat   = optional(optional($skrining->pasien)->user)->address ?? '-';
                                $alamatDisplay = $skrining->pasien->PKecamatan ?? $alamat;
                                $phone    = optional(optional($skrining->pasien)->user)->phone ?? '-';
                                $resikoSedang = (int)($skrining->jumlah_resiko_sedang ?? 0);
                                $resikoTinggi = (int)($skrining->jumlah_resiko_tinggi ?? 0);
                                $kesimpulanRaw = $skrining->conclusion_display ?? ($skrining->kesimpulan ?? '');
                                $normConc = strtolower(trim($kesimpulanRaw));
                                $conclusion = $normConc === 'tidak berisiko preeklampsia' ? 'Normal' : (in_array($normConc, ['berisiko preeklampsia','beresiko preeklampsia','beresiko']) ? 'Berisiko' : ($kesimpulanRaw ?: 'Normal'));
                                $cls = $skrining->badge_class ?? 'bg-[#2EDB58] text-white';
                                $editUrl = route('pasien.skrining.edit', $skrining->id);
                                $viewUrl = route('pasien.skrining.show', $skrining->id);
                                @endphp
                                <tr class="text-center">
                                    <td class="px-3 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-3">{{ $nik }}</td>
                                    <td class="px-3 py-3 text-[#1D1D1D]">{{ $phone }}</td>
                                    <td class="px-3 py-3 text-[#1D1D1D]">{{ $tanggal }}</td>
                                    <td class="px-3 py-3 text-[#1D1D1D]">{{ $alamatDisplay }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center justify-center rounded-full px-4 h-8 text-sm font-medium leading-none whitespace-nowrap {{ $cls }}">
                                            {{ $conclusion }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if(!empty($skrining->is_verified))
                                            <span class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full border border-green-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Terverifikasi
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 md:px-6 md:py-4">
                                        <div class="flex justify-center items-center gap-2">
                                            @if(empty($skrining->has_referral) && empty($skrining->is_verified))
                                                <a href="{{ $editUrl }}" class="px-4 py-1.5 rounded-full bg-white border border-[#E5E5E5] hover:bg-[#F0F0F0]">Edit</a>
                                            @else
                                                <span class="px-4 py-1.5 rounded-full bg-[#F2F2F2] border border-[#E5E5E5] text-[#7C7C7C]">Edit</span>
                                            @endif

                                            <a href="{{ $viewUrl }}" class="px-4 py-1.5 rounded-full bg-white border border-[#E5E5E5] hover:bg-[#F0F0F0]">View</a>

                                            @if(empty($skrining->has_referral) && empty($skrining->is_verified))
                                            <form method="POST" action="{{ route('pasien.skrining.destroy', $skrining->id) }}" class="js-delete-skrining-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="js-delete-skrining-btn rounded-full border px-4 py-2 text-red-600 hover:bg-red-50">Hapus</button>
                                            </form>
                                            @else
                                                <span class="px-4 py-1.5 rounded-full bg-[#F2F2F2] border border-[#E5E5E5] text-[#7C7C7C]">Hapus</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-[#7C7C7C]">
                                        Belum ada data skrining.
                                    </td>
                                </tr>                                
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-[#7C7C7C]">
                        @php
                            $first = $skrinings->firstItem() ?? 0;
                            $last  = $skrinings->lastItem() ?? $skrinings->count();
                            $total = $skrinings->total() ?? $skrinings->count();
                        @endphp
                        Menampilkan {{ $first }}–{{ $last }} dari {{ $total }} data
                    </div>
                    @if(isset($skrinings) && method_exists($skrinings, 'hasPages') && $skrinings->hasPages())
                        <div>
                            {{ $skrinings->links() }}
                        </div>
                    @endif
                </div>
            </section>

            <!-- Ringkasan Total Skrining & Resiko Preeklamsia -->           
            <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-md flex flex-col h-full">
                    <h2 class="text-xl font-semibold text-[#1D1D1D] mb-3">Total Skrining</h2>
                    <div class="text-base flex-1 flex flex-col justify-center space-y-3">
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

                <div class="bg-white rounded-2xl p-4 shadow-md">
                    <h2 class="text-xl font-semibold text-[#1D1D1D] mb-3">Risiko Preeklamsia</h2>
                    <div class="rounded-xl {{ $riskBoxClass ?? 'bg-[#E9E9E9] text-[#1D1D1D]' }} p-4 text-center">
                        @php
                            $riskRaw = $riskPreeklampsia ?? '';
                            $riskNorm = strtolower(trim($riskRaw));
                            $riskDisplay = $riskNorm === 'tidak berisiko preeklampsia' ? 'Normal' : (in_array($riskNorm, ['berisiko preeklampsia','beresiko preeklampsia','beresiko']) ? 'Berisiko' : ($riskRaw ?: 'Belum ada'));
                        @endphp
                        <span class="text-lg font-semibold">
                            {{ $riskDisplay }}
                        </span>
                    </div>

                    @php $riskLower = strtolower($riskPreeklampsia ?? ''); @endphp
                    @if(in_array($riskLower, ['berisiko preeklampsia','berisiko','beresiko','risiko tinggi','resiko tinggi']))
                        <p class="text-xs text-red-600 mt-3">*Segera Menuju ke RS di Bawah Untuk Penanganan Lebih Lanjut</p>
                        <div class="rounded-xl bg-[#E9E9E9] text-[#1D1D1D] p-4 text-center mt-2">
                            <span class="font-medium">
                                {{ ($referralAccepted ?? false) && ($referralHospital ?? null) ? $referralHospital : 'Tunggu Hasil Rujukan' }}
                            </span>
                        </div>
                    @endif
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>

    </div>
</body>
</html>