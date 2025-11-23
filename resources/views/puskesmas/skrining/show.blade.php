<!-- resources/views/puskesmas/skrining/show.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Detail Skrining</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/puskesmas/sidebar-toggle.js', 'resources/js/puskesmas/rujukan-picker.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen">
    <x-puskesmas.sidebar />

    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
        <header class="mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Detail Skrining Pasien</h1>
        </header>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-[#E9E9E9] p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-[#1D1D1D] mb-3">Informasi Pasien</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-[#7C7C7C]">Nama</span><span class="font-medium text-[#1D1D1D]">{{ $nama }}</span></div>
                    <div class="flex justify-between"><span class="text-[#7C7C7C]">NIK</span><span class="font-medium text-[#1D1D1D]">{{ $nik }}</span></div>
                    <div class="flex justify-between"><span class="text-[#7C7C7C]">Tanggal Pengisian</span><span class="font-medium text-[#1D1D1D]">{{ $tanggal }}</span></div>
                    <div class="flex justify-between"><span class="text-[#7C7C7C]">Alamat</span><span class="font-medium text-[#1D1D1D]">{{ $alamat }}</span></div>
                    <div class="flex justify-between"><span class="text-[#7C7C7C]">No Telp</span><span class="font-medium text-[#1D1D1D]">{{ $telp }}</span></div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-2">Kesimpulan</h2>
                    <span class="inline-flex px-4 py-2 rounded-full text-sm font-semibold {{ $cls }}">{{ $conclusion }}</span>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-[#1D1D1D] mb-2">Penyebab Risiko</h2>
                    @php($causesHigh = $sebabTinggi ?? [])
                    @php($causesMod  = $sebabSedang ?? [])
                    <div class="space-y-1">
                        @if(!empty($causesHigh) || !empty($causesMod))
                            @foreach($causesHigh as $c)
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full bg-red-600"></span>
                                    <span class="text-sm text-[#1D1D1D]">{{ $c }}</span>
                                </div>
                            @endforeach
                            @foreach($causesMod as $c)
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full bg-yellow-400"></span>
                                    <span class="text-sm text-[#1D1D1D]">{{ $c }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm text-[#7C7C7C]">Tidak ada pemicu risiko.</div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <a href="{{ route('pasien.skrining.show', $skrining->id) }}" class="px-4 py-2 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-sm">Lihat Detail Skrining</a>
                    <button id="btnAjukanRujukan"
                            data-submit-url="{{ route('puskesmas.skrining.rujuk', $skrining->id) }}"
                            data-search-url="{{ route('puskesmas.rs.search') }}"
                            data-csrf="{{ csrf_token() }}"
                            type="button"
                            class="px-4 py-2 rounded-full bg-[#B9257F] text-white text-sm"
                            {{ $hasReferral ? 'disabled' : '' }}>
                        {{ $hasReferral ? 'Sudah Diajukan' : 'Ajukan Rujukan' }}
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-[#1D1D1D] mb-3">Aksi</h2>
                <div class="space-y-2">
                    <a href="{{ route('puskesmas.skrining') }}" class="px-4 py-2 rounded-full bg-[#F2F2F2] text-[#1D1D1D] text-sm">Kembali ke List</a>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>