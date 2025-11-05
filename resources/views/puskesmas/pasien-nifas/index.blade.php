@extends('layouts.puskesmas')

@section('title', 'List Pasien Nifas - Puskesmas')

@section('content')
<div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">List Pasien Nifas</h1>
    </header>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 flex-1">
        <!-- Data Pasien Nifas Header -->
        <div class="flex items-center gap-2 mb-4 p-3 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10v-2a3 3 0 00-5.356-1.857M7 3h10v3M7 6h10v3" />
            </svg>
            <div>
                <h2 class="font-semibold text-lg">Data Pasien Nifas</h2>
                <p class="text-xs text-gray-500">Data pasien yang sedang nifas pada puskesmas ini</p>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <!-- Checkbox Select All -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded text-pink-600 focus:ring-pink-500">
                        </th>
                        <!-- Nama Pasien -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>
                                Nama Pasien
                            </div>
                        </th>
                        <!-- Tanggal -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tanggal
                            </div>
                        </th>
                        <!-- Alamat -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Alamat
                            </div>
                        </th>
                        <!-- No Telp -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z" />
                                </svg>
                                No Telp
                            </div>
                        </th>
                        <!-- Pengingat -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pengingat
                            </div>
                        </th>
                        <!-- Action -->
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-.426 1.038-.426 1.464 0l1.596 1.596m-7.5 7.5l1.596 1.596m0 0a11.25 11.25 0 1015.912-15.912L16.5 16.5M12 12l4.5 4.5" />
                                </svg>
                                Action
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @for($i = 0; $i < 8; $i++)
                    <tr class="hover:bg-gray-50 transition">
                        <!-- Checkbox per baris -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <input type="checkbox" class="rounded text-pink-600 focus:ring-pink-500">
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm flex items-center">
                            <div class="w-8 h-8 bg-gray-300 rounded-full mr-2 flex items-center justify-center text-xs">A</div>
                            Asep Dadang
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">01/01/2025</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">Beji</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">0000000000</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $status = ['Aman', 'Terlambat', 'Waspadai'][$i % 3];
                                $color = match($status) {
                                    'Aman' => 'bg-green-500 text-white',
                                    'Terlambat' => 'bg-red-500 text-white',
                                    'Waspadai' => 'bg-yellow-500 text-black',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm space-x-1">
                            <button class="px-2 py-1 border border-gray-300 rounded text-xs hover:bg-gray-100">M1</button>
                            <button class="px-2 py-1 border border-gray-300 rounded text-xs hover:bg-gray-100">M2</button>
                            <button class="px-2 py-1 border border-gray-300 rounded text-xs hover:bg-gray-100">M3</button>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
            <!-- Informasi jumlah data -->
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium">1</span> sampai <span class="font-medium">8</span> dari <span class="font-medium">24</span> pasien
            </div>

            <!-- Navigation Pagination -->
            <nav class="flex items-center space-x-1" aria-label="Pagination">
                <!-- Previous Button -->
                <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </a>

                <!-- Page Numbers -->
                <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">1</a>
                <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">2</a>
                <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">3</a>
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700">...</span>
                <a href="#" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">8</a>

                <!-- Next Button -->
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
@endsection