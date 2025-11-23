<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Data Rujukan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-[#FFF7FC] min-h-screen">
    <div class="flex min-h-screen">
        <x-puskesmas.sidebar />
        
        <main class="flex-1 w-full xl:ml-[260px] p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Data Rujukan</h1>
                <p class="text-gray-600">Daftar semua rujukan yang diajukan dari puskesmas ini</p>
            </div>

            <!-- Table Rujukan -->
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                @if($rujukans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[#7C7C7C] bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama Pasien</th>
                                <th class="px-4 py-3 text-left">NIK</th>
                                <th class="px-4 py-3 text-left">Rumah Sakit Tujuan</th>
                                <th class="px-4 py-3 text-left">Tanggal Rujukan</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E9E9E9]">
                            @foreach($rujukans as $index => $rujukan)
                            <tr>
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $rujukan->nama_pasien }}</td>
                                <td class="px-4 py-3">{{ $rujukan->nik }}</td>
                                <td class="px-4 py-3">{{ $rujukan->nama_rs }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rujukan->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusClass = match($rujukan->done_status) {
                                            true => 'bg-green-100 text-green-800 border border-green-200',
                                            false => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                            default => 'bg-gray-100 text-gray-800 border border-gray-200'
                                        };
                                        $statusText = $rujukan->done_status ? 'Selesai' : 'Menunggu Konfirmasi RS';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('puskesmas.rujukan.show', $rujukan->id) }}" 
                                       class="px-4 py-2 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-sm hover:bg-gray-50 transition-colors">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data rujukan</h3>
                    <p class="text-gray-500 mb-4">Data rujukan akan muncul di sini setelah Anda mengajukan rujukan</p>
                    <a href="{{ route('puskesmas.skrining') }}" 
                       class="inline-flex items-center px-4 py-2 bg-[#B9257F] text-white rounded-lg hover:bg-[#a31f70] transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajukan Rujukan Baru
                    </a>
                </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>