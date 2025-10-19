<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bidan — Dashboard</title>
  @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 min-h-screen p-8">
  <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Bidan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold text-lg mb-4 text-gray-700">Daerah Asal Pasien</h3>
                        <div class="flex justify-around text-center">
                            <div>
                                <div class="text-5xl font-bold text-gray-900">{{ $daerahAsal->depok ?? 0 }}</div>
                                <div class="text-gray-500">Depok</div>
                            </div>
                            <div>
                                <div class="text-5xl font-bold text-gray-900">{{ $daerahAsal->non_depok ?? 0 }}</div>
                                <div class="text-gray-500">Non Depok</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold text-lg mb-4 text-gray-700">Data Pasien Nifas</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Pasien Nifas</span>
                                <span class="font-bold text-xl text-gray-900">{{ $totalNifas ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Sudah KF1</span>
                                <span class="font-bold text-xl text-gray-900">{{ $sudahKf1 ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold text-lg mb-4 text-gray-700">Resiko Eklampsia</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pasien Normal</span>
                                <span class="font-bold text-xl text-gray-900">{{ $resiko->normal ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pasien Beresiko Eklampsia</span>
                                <span class="font-bold text-xl text-gray-900">{{ $resiko->beresiko ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold text-lg mb-4 text-gray-700">Pasien Hadir</h3>
                         <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pasien Hadir</span>
                                <span class="font-bold text-xl text-gray-900">{{ $pasienHadir ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pasien Tidak Hadir</span>
                                <span class="font-bold text-xl text-gray-900">{{ $pasienTidakHadir ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold text-lg mb-4 text-gray-700">Pemantauan (Nifas)</h3>
                        <div class="flex justify-around text-center">
                            <div>
                                <div class="text-3xl font-bold text-green-600">{{ $pemantauanSehat ?? 0 }}</div>
                                <div class="text-gray-500">Sehat</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-yellow-600">{{ $pemantauanDirujuk ?? 0 }}</div>
                                <div class="text-gray-500">Dirujuk</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-red-600">{{ $pemantauanMeninggal ?? 0 }}</div>
                                <div class="text-gray-500">Meninggal</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <div class="mt-8 bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-lg text-gray-700">Data Pasien Pre Eklampsia (Terbaru)</h3>
                    <a href="#" class="text-sm text-pink-600 hover:text-pink-800 font-medium">View All &rarr;</a>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pasien (NIK)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl. Skrining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat (Kec)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Telp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kesimpulan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pasienTerbaru as $skrining)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $skrining->pasien->nik ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $skrining->pasien->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $skrining->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $skrining->pasien->PKecamatan ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $skrining->pasien->user->phone ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badgeColor = 'bg-gray-100 text-gray-800'; // Default
                                        if ($skrining->kesimpulan == 'Beresiko') $badgeColor = 'bg-red-100 text-red-800';
                                        if ($skrining->kesimpulan == 'Aman') $badgeColor = 'bg-green-100 text-green-800';
                                        if ($skrining->kesimpulan == 'Waspada') $badgeColor = 'bg-yellow-100 text-yellow-800';
                                        if ($skrining->kesimpulan == 'Normal') $badgeColor = 'bg-blue-100 text-blue-800';
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                                        {{ $skrining->kesimpulan ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="px-3 py-1 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada data pasien skrining.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
  </x-app-layout>
</body>
</html>
