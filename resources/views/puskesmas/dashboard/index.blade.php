@extends('layouts.puskesmas')

@section('title', 'Dashboard - Puskesmas')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-puskesmas.sidebar />

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-6">
        @vite([
                'resources/js/puskesmas/dropdown.js'
            ])
        <!-- Header -->
        <header class="flex items-center gap-3 justify-between bg-white px-4 sm:px-5 py-3 sm:py-4 rounded-2xl shadow-md mb-6">
            <!-- Left Side: Search Bar -->
            <div class="relative flex-1 max-w-[700px]">
                <span class="absolute inset-y-0 left-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" placeholder="Pencarian"
                    class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
            </div>

            <!-- Right Side: Icons & Profile Dropdown -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Icon Setting -->
                <a href="{{ route('puskesmas.profile.edit') }}"
                class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5] hover:bg-[#F8F8F8]"
                title="Edit Profil">
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                </a>

                <!-- Icon Notifikasi -->
                <button class="relative w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5] hover:bg-[#F8F8F8]">
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" class="w-4 h-4 opacity-90" alt="Notifikasi">
                    <span class="absolute top-1.5 right-1.5 inline-block w-2.5 h-2.5 bg-[#B9257F] rounded-full ring-2 ring-white"></span>
                </button>

                <!-- Profile Dropdown -->
                <div id="profileWrapper" class="relative">
                    <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8] transition">
                        @if (Auth::user()?->photo)
                            <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                        @else
                            <span class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                                </svg>
                            </span>
                        @endif
                        <div class="leading-tight pr-1 text-left hidden xs:block">
                            <p class="text-[13px] font-semibold text-[#1D1D1D] truncate max-w-[140px] sm:max-w-[200px]">
                                {{ auth()->user()->name ?? 'Nama Bidan' }}
                            </p>
                            <p class="text-[11px] text-[#7C7C7C] -mt-0.5 truncate max-w-[140px] sm:max-w-[200px]">
                                {{ auth()->user()->email ?? 'email@bidan.id' }}
                            </p>
                        </div>
                        <!-- Caret icon -->
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                            class="hidden sm:block w-4 h-4 opacity-70" alt="More" />
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileMenu"
                        class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                        <div class="px-4 py-3 border-b border-[#F0F0F0]">
                            <p class="text-sm font-medium text-[#1D1D1D]">{{ auth()->user()->name ?? 'Nama Bidan' }}</p>
                            <p class="text-xs text-[#7C7C7C] truncate">{{ auth()->user()->email ?? 'email@bidan.id' }}</p>
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
        </header>
        <!-- Main Dashboard Grid: Left Column + Right Column -->
        <div class="flex flex-col md:flex-row gap-6 mb-6">

        <!-- LEFT COLUMN -->
        <div class="flex-1 space-y-6">
                <!-- Daerah Asal Pasien -->
        <div class="bg-white rounded-xl shadow-sm p-5 h-[217px]" style="width: 550px;">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                    <!-- Ikon Lokasi dari Figma -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Daerah Asal Pasien
                </h3>
                <!-- Ikon Panah ke Kanan Atas -->
            <button class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
            </div>
                    <!-- Content with Exact Pixel Dimensions -->
                    <div class="flex justify-between items-center" style="height: 189px;">
                        <!-- Depok Column -->
                        <div class="text-center" style="width: 301px; height: 189px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div class="text-gray-500 text-xs mb-1" style="width: 65px; height: 46px; display: flex; align-items: center; justify-content: center;">Depok</div>
                            <!-- Ganti 0 dengan variabel dari controller -->
                            <div class="text-[5rem] font-black leading-none tracking-tight">{{ $depokCount ?? 0 }}</div>
                        </div>

                        <!-- Vertical Line -->
                        <div class="border-l border-gray-300" style="width: 11px; height: 46px;"></div>

                        <!-- Non Depok Column -->
                        <div class="text-center" style="width: 301px; height: 189px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div class="text-gray-500 text-xs mb-1" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">Non Depok</div>
                            <!-- Ganti 0 dengan variabel dari controller -->
                            <div class="text-[5rem] font-black leading-none tracking-tight">{{ $nonDepokCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Data Pasien Nifas -->
                <div class="bg-white rounded-xl shadow-sm p-5 h-[163px]" style="width: 550px;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                            <!-- Ikon Orang dari Figma -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-5-5.7V4a4 4 0 00-8 0v10.3A12.02 12.02 0 0012 21z"></path>
                            </svg>
                            Data Pasien Nifas
                        </h3>
                        <!-- ❌ HAPUS tombol export di sini! -->
                    </div>
                <!-- Row 1: Total Pasien Nifas -->
                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                    <!-- Ganti 0 dengan variabel dari controller -->
                    <span class="text-gray-600 text-xs">Total Pasien Nifas</span>
                    <span class="font-medium text-sm">{{ $totalNifas ?? 0 }}</span>
                </div>

                <!-- Row 2: Sudah KFI -->
                <div class="flex justify-between items-center py-2">
                    <!-- Ganti 0 dengan variabel dari controller -->
                    <span class="text-gray-600 text-xs">Sudah KFI</span>
                    <span class="font-medium text-sm">{{ $sudahKFI ?? 0 }}</span>
                </div>
            </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="flex-1 space-y-6">
                <!-- Resiko Eklampsia -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                            <!-- Ikon Jam Pasir dari Figma -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v6M12 18v6M4.93 4.93l1.41 1.41M18.66 18.66l1.41 1.41M12 6v12M6 12H4M18 12h2M12 18v6M4.93 19.07l1.41-1.41M18.66 5.34l1.41-1.41"></path>
                            </svg>
                            Resiko Eklampsia
                        </h3>
                        <!-- Ikon Panah ke Kanan Atas -->
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                    </div>
                    <div class="space-y-2">
                        <!-- Ganti 0 dengan variabel dari controller -->
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Normal</span>
                            <span class="font-medium text-sm">{{ $normalEklampsia ?? 0 }}</span>
                        </div>
                        <!-- Ganti 0 dengan variabel dari controller -->
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Beresiko Eklampsia</span>
                            <span class="font-medium text-sm">{{ $beresikoEklampsia ?? 0 }}</span>
                        </div>
                        <!-- Tambahkan juga Pasien Waspadai jika perlu -->
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Waspadai</span>
                            <span class="font-medium text-sm">{{ $waspadaiEklampsia ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pasien Hadir -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                            <!-- Ikon Stetoskop dari Figma -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 6v6m0 0v6m0-6h6m-6 6h-6"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                            Pasien Hadir
                        </h3>
                        <!-- Ikon Panah ke Kanan Atas -->
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                    </div>
                    <div class="space-y-2">
                        <!-- Ganti 0 dengan variabel dari controller -->
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Hadir</span>
                            <span class="font-medium text-sm">{{ $hadir ?? 0 }}</span>
                        </div>
                        <!-- Ganti 0 dengan variabel dari controller -->
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Tidak Hadir</span>
                            <span class="font-medium text-sm">{{ $tidakHadir ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pemantauan -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                            <!-- Ikon Grafik dari Figma -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8v8m-4-4v8m-4-4v8"></path>
                                <rect x="2" y="2" width="20" height="20" rx="2" ry="2"></rect>
                            </svg>
                            Pemantauan
                        </h3>
                        <!-- Ikon Panah ke Kanan Atas -->
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-center">
                            <!-- Ganti 0 dengan variabel dari controller -->
                            <div class="text-gray-500 text-xs">Sehat</div>
                            <div class="text-xl font-bold">{{ $sehat ?? 0 }}</div>
                        </div>
                        <div class="border-l border-gray-300 mx-4"></div>
                        <div class="text-center">
                            <!-- Ganti 0 dengan variabel dari controller -->
                            <div class="text-gray-500 text-xs">Total Dirujuk</div>
                            <div class="text-xl font-bold">{{ $dirujuk ?? 0 }}</div>
                        </div>
                        <div class="border-l border-gray-300 mx-4"></div>
                        <div class="text-center">
                            <!-- Ganti 0 dengan variabel dari controller -->
                            <div class="text-gray-500 text-xs">Meninggal</div>
                            <div class="text-xl font-bold">{{ $meninggal ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Pasien Pre Eklampsia Table -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10v-2a3 3 0 00-5.356-1.857M7 3h10v3M7 6h10v3" />
                    </svg>
                    Data Pasien Pre Eklampsia
                </h3>
                <a href="#" class="text-sm text-gray-600 hover:text-gray-800 flex items-center">
                    View All
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID PASIEN</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAMA PASIEN</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TANGGAL</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ALAMAT</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NO TELEPON</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KESIMPULAN</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIEW DETAIL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- Ganti @for dengan @forelse atau @foreach -->
                        @if($preEklampsiaData && $preEklampsiaData->count() > 0)
                            @foreach($preEklampsiaData as $skrining)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">#{{ $skrining->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full mr-2 flex items-center justify-center text-xs">
                                        {{ substr($skrining->pasien->nama_pasien ?? 'N/A', 0, 1) }}
                                    </div>
                                    {{ $skrining->pasien->nama_pasien ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $skrining->pasien->PKabupaten ?? 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $skrining->pasien->user->phone ?? 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $status = $skrining->kesimpulan;
                                        $colorClass = [
                                            'Beresiko' => 'bg-red-500 text-white',
                                            'Aman' => 'bg-green-500 text-white',
                                            'Waspada' => 'bg-yellow-500 text-black' // Sesuaikan dengan nilai yang ada
                                        ][$status] ?? 'bg-gray-500 text-white';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <a href="{{ route('puskesmas.skrining.show', $skrining->id) }}" class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-100">View</a>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-center text-sm text-gray-500">Tidak ada data pasien berisiko pre-eklampsia.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
                <!-- Pagination Footer (Sama seperti halaman Skrining) -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">1</span> sampai <span class="font-medium">{{ $preEklampsiaData->count() }}</span> dari <span class="font-medium">{{ $preEklampsiaData->total() ?? 0 }}</span> pasien
                    </div>

                    <nav class="flex items-center space-x-1" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>

                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">1</a>
                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">2</a>
                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">3</a>
                        <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700">...</span>
                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">8</a>

                        <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </nav>
                </div>
        </div>
    </div>
</div>
@endsection