@extends('layouts.puskesmas')

@section('title', 'Dashboard - Puskesmas')

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <x-puskesmas.sidebar />

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-6">
        <!-- Header -->
        <header class="flex justify-between items-center mb-6">
            <!-- Search Bar -->
            <div class="relative w-96">
                <input type="text" placeholder="Search..." class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Right Icons & Profile -->
            <div class="flex items-center space-x-4">
                <button class="p-2 text-gray-600 hover:bg-gray-200 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.919V9H9M15 17H9m12 0a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h7z" />
                    </svg>
                </button>
                <button class="p-2 text-gray-600 hover:bg-gray-200 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-.426 1.038-.426 1.464 0l1.596 1.596m-7.5 7.5l1.596 1.596m0 0a11.25 11.25 0 1015.912-15.912L16.5 16.5M12 12l4.5 4.5" />
                    </svg>
                </button>
                <div class="flex items-center space-x-2">
                    <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-full">
                    <div class="text-right">
                        <div class="text-sm font-medium">Nama Bidan</div>
                        <div class="text-xs text-gray-500">email@bidan.id</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </header>

        <!-- Main Dashboard Grid: Left Column + Right Column -->
        <div class="flex flex-col md:flex-row gap-6 mb-6">

        <!-- LEFT COLUMN -->
        <div class="flex-1 space-y-6">
                <!-- Daerah Asal Pasien -->
        <div class="bg-white rounded-xl shadow-sm p-5 h-[237px]" style="width: 550px;">
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
                            <div class="text-[5rem] font-black leading-none tracking-tight">0</div>
                        </div>

                        <!-- Vertical Line -->
                        <div class="border-l border-gray-300" style="width: 11px; height: 46px;"></div>

                        <!-- Non Depok Column -->
                        <div class="text-center" style="width: 301px; height: 189px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div class="text-gray-500 text-xs mb-1" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">Non Depok</div>
                            <div class="text-[5rem] font-black leading-none tracking-tight">0</div>
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
                    <span class="text-gray-600 text-xs">Total Pasien Nifas</span>
                    <span class="font-medium text-sm">0</span>
                </div>

                <!-- Row 2: Sudah KFI -->
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-600 text-xs">Sudah KFI</span>
                    <span class="font-medium text-sm">0</span>
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
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Normal</span>
                            <span class="font-medium text-sm">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Beresiko Eklampsia</span>
                            <span class="font-medium text-sm">0</span>
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
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Hadir</span>
                            <span class="font-medium text-sm">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 text-xs">Pasien Tidak Hadir</span>
                            <span class="font-medium text-sm">0</span>
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
                            <div class="text-gray-500 text-xs">Sehat</div>
                            <div class="text-xl font-bold">0</div>
                        </div>
                        <div class="border-l border-gray-300 mx-4"></div>
                        <div class="text-center">
                            <div class="text-gray-500 text-xs">Total Dirujuk</div>
                            <div class="text-xl font-bold">0</div>
                        </div>
                        <div class="border-l border-gray-300 mx-4"></div>
                        <div class="text-center">
                            <div class="text-gray-500 text-xs">Meninggal</div>
                            <div class="text-xl font-bold">0</div>
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
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KASIMPULAN</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIEW DETAIL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @for($i = 0; $i < 5; $i++)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">#0000000000000000</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm flex items-center">
                                <div class="w-8 h-8 bg-gray-300 rounded-full mr-2 flex items-center justify-center text-xs">A</div>
                                Asep Dadang
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">01/01/2025</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">Bsj</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">0000000000</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $status = ['Beresiko', 'Aman', 'Waspadai'][$i % 3];
                                    $colorClass = [
                                        'Beresiko' => 'bg-red-500 text-white',
                                        'Aman' => 'bg-green-500 text-white',
                                        'Waspadai' => 'bg-yellow-500 text-black'
                                    ][$status];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <button class="px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-100">View</button>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
                <!-- Pagination Footer (Sama seperti halaman Skrining) -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">1</span> sampai <span class="font-medium">8</span> dari <span class="font-medium">24</span> pasien
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