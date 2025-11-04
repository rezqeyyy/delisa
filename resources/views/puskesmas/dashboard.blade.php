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

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Daerah Asal Pasien -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.995 1.995 0 01-2.828 0l-4.244-4.244a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Daerah Asal Pasien
                    </h3>
                    <button class="p-1 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6v6m-11-5L21 18" />
                        </svg>
                    </button>
                </div>
                <div class="flex justify-between">
                    <div class="text-center">
                        <div class="text-gray-500 text-xs">Depok</div>
                        <div class="text-2xl font-bold">0</div>
                    </div>
                    <div class="border-l border-gray-300 mx-4"></div>
                    <div class="text-center">
                        <div class="text-gray-500 text-xs">Non Depok</div>
                        <div class="text-2xl font-bold">0</div>
                    </div>
                </div>
            </div>

            <!-- Resiko Eklampsia -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700 flex items-center text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10v-2a3 3 0 00-5.356-1.857M7 3h10v3M7 6h10v3" />
                        </svg>
                        Resiko Eklampsia
                    </h3>
                    <button class="p-1 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6v6m-11-5L21 18" />
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-2c1.1 0 2-.9 2-2v-6h2c1.1 0 2-.9 2-2s-.9-2-2-2h-2V6c0-1.1-.9-2-2-2s-2 .9-2 2v2H7c-1.1 0-2 .9-2 2s.9 2 2 2h2v6c0 1.1.9 2 2 2h2v2c0 1.1.9 2 2 2z" />
                        </svg>
                        Pasien Hadir
                    </h3>
                    <button class="p-1 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6v6m-11-5L21 18" />
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10v-2a3 3 0 00-5.356-1.857M7 3h10v3M7 6h10v3" />
                        </svg>
                        Pemantauan
                    </h3>
                    <button class="p-1 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6v6m-11-5L21 18" />
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID PASIEN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAMA PASIEN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TANGGAL</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ALAMAT</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NO TELEPON</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KASIMPULAN</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIEW DETAIL</th>
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
        </div>
    </div>
</div>
@endsection