<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Dashboard</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        
        <x-bidan.sidebar />

        <div class="flex-1 flex flex-col lg:pl-64"> <header class="sticky top-0 z-10 flex h-20 items-center justify-between border-b bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex-1">
                    <div class="relative w-full max-w-md">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="search" placeholder="Search..." class="w-full rounded-full border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-pink-500 focus:ring-pink-500">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=F472B6&background=FCE7F3" alt="Avatar" class="h-10 w-10 rounded-full border-2 border-pink-100">
                            <div class="text-left hidden md:block">
                                <div class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->role->nama_role }}</div>
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" x-transition>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();"
                                   class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                    Log Out
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
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

                </div> 
                
                <div class="mt-8 bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
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

            </main>
        </div>
    </div>
</body>
</html>