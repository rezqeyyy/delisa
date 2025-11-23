<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Detail Rujukan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-[#FFF7FC] min-h-screen">
    <div class="flex min-h-screen">
        <x-puskesmas.sidebar />
        
        <main class="flex-1 w-full xl:ml-[260px] p-6">
            <!-- Header dengan tombol kembali -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Rujukan</h1>
                    <p class="text-gray-600">Informasi lengkap rujukan pasien</p>
                </div>
                <a href="{{ route('puskesmas.rujukan.index') }}" 
                   class="px-4 py-2 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-sm hover:bg-gray-50">
                    ← Kembali ke List
                </a>
            </div>

            @if($rujukan)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informasi Pasien -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Informasi Pasien</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Nama Pasien</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->nama_pasien }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">NIK</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->nik }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Tanggal Lahir</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->tanggal_lahir ? \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Alamat</span>
                            <span class="font-medium text-[#1D1D1D] text-right">{{ $rujukan->alamat ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">No Telepon</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->no_telepon ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Rujukan -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Informasi Rujukan</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Rumah Sakit Tujuan</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->nama_rs }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Alamat RS</span>
                            <span class="font-medium text-[#1D1D1D] text-right">{{ $rujukan->alamat_rs ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Telepon RS</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->telepon_rs ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Tanggal Rujukan</span>
                            <span class="font-medium text-[#1D1D1D]">{{ \Carbon\Carbon::parse($rujukan->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Status</span>
                            @php
                                $statusClass = $rujukan->done_status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                $statusText = $rujukan->done_status ? 'Selesai' : 'Menunggu Konfirmasi RS';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Rujukan -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 lg:col-span-2">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Catatan Rujukan</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-[#1D1D1D]">{{ $rujukan->catatan_rujukan ?? 'Tidak ada catatan' }}</p>
                    </div>
                </div>

                <!-- Data Skrining -->
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 lg:col-span-2">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-4">Data Skrining Awal</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Kesimpulan Skrining</span>
                            <span class="font-medium text-[#1D1D1D]">{{ $rujukan->kesimpulan ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#7C7C7C]">Hasil Detail</span>
                            <span class="font-medium text-[#1D1D1D] text-right">{{ $rujukan->hasil_akhir ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 text-center">
                <p class="text-gray-500">Data rujukan tidak ditemukan</p>
            </div>
            @endif
        </main>
    </div>
</body>
</html>