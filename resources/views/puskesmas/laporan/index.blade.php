@extends('layouts.puskesmas')

@section('title', 'Laporan - Puskesmas')

@section('content')
<div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
    </header>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 flex-1">
        <!-- Section Header -->
        <div class="flex items-center gap-2 mb-6 p-3 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7V7h14v14z" />
            </svg>
            <div>
                <h2 class="font-semibold text-lg">Buat Laporan Kesehatan Ibu</h2>
                <p class="text-xs text-gray-500">Pilih periode dan jenis laporan untuk menghasilkan data</p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Jenis Laporan -->
            <div>
                <label for="jenis_laporan" class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
                <select id="jenis_laporan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                    <option>Skrining Ibu Hamil</option>
                    <option>Pasien Nifas</option>
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
            </div>

            <!-- Tanggal Selesai -->
            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
            </div>

            <!-- Tombol Generate -->
            <div class="flex items-end">
                <button class="w-full bg-pink-600 hover:bg-pink-700 text-white font-medium py-2 px-4 rounded-md shadow-sm text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Generate Laporan
                </button>
            </div>
        </div>

        <!-- Preview Area (Placeholder) -->
        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[200px]">
            <div class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">Hasil laporan akan muncul di sini setelah Anda mengklik “Generate Laporan”</p>
                    <p class="text-xs mt-1">Laporan dapat diunduh dalam format PDF atau Excel</p>
                </div>
            </div>
        </div>

        <!-- Optional: Recent Reports (bisa ditambahkan nanti) -->

        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Laporan Terakhir</h3>
            <div class="text-xs text-gray-500 space-y-1">
                <div class="flex justify-between">
                    <span>Laporan Skrining Ibu Hamil (01–31 Okt 2025)</span>
                    <a href="#" class="text-pink-600 hover:underline">Unduh</a>
                </div>
                <div class="flex justify-between">
                    <span>Laporan Pasien Nifas (01–31 Okt 2025)</span>
                    <a href="#" class="text-pink-600 hover:underline">Unduh</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection