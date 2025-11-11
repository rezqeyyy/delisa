<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Detail Akun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="ml-0 md:ml-[400px] p-4 sm:p-6 lg:p-8 max-w-3xl sm:max-w-4xl mx-auto">
        <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-bold">Detail Akun ({{ ucfirst($tab) }})</h1>
            <a href="{{ route('dinkes.data-master', ['tab' => $tab]) }}"
                class="px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">← Kembali</a>
        </div>

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-3">
            <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Nama</span>
                <div class="font-medium break-words">{{ $data->name }}</div>
            </div>
            <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Email</span>
                <div class="font-medium break-all">{{ $data->email }}</div>
            </div>

            @if ($tab === 'rs')
                <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Nama RS</span>
                    <div class="font-medium">{{ $data->nama }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                        <div class="font-medium">{{ $data->kecamatan }}</div>
                    </div>
                    <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Kelurahan</span>
                        <div class="font-medium">{{ $data->kelurahan }}</div>
                    </div>
                </div>
                <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Lokasi</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>
            @elseif($tab === 'puskesmas')
                <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Nama Puskesmas</span>
                    <div class="font-medium">{{ $data->nama_puskesmas ?? $data->nama }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                        <div class="font-medium">{{ $data->kecamatan }}</div>
                    </div>
                    <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Mandiri</span>
                        <div class="font-medium">{{ !empty($data->is_mandiri) ? 'Ya' : 'Tidak' }}</div>
                    </div>
                </div>
                <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Lokasi</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>
            @else
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">No Izin Praktek</span>
                        <div class="font-medium break-words">{{ $data->nomor_izin_praktek }}</div>
                    </div>
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Puskesmas</span>
                        <div class="font-medium break-words">{{ $data->nama_puskesmas ?? '-' }}</div>
                    </div>
                </div>
                <div><span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat</span>
                    <div class="font-medium">{{ $data->address }}</div>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
