<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Detail Rujukan</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/puskesmas/sidebar-toggle.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex items-center">
                <a href="{{ route('puskesmas.rujukan.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-2xl font-semibold text-[#1D1D1D]">Detail Rujukan</h1>
            </div>

            {{-- 1. Informasi Rujukan --}}
            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Rujukan</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">ID Rujukan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">#{{ $rujukan->id }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Rujukan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ \Carbon\Carbon::parse($rujukan->created_at)->format('d F Y, H:i') }} WIB
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Status</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            @if ($rujukan->done_status == 1)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Selesai
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Menunggu Konfirmasi RS
                                </span>
                            @endif
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">ID Skrining</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">#{{ $rujukan->skrining_id }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Data Pasien --}}
            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Data Pasien</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Nama Lengkap</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm font-bold">
                            {{ $rujukan->nama_pasien ?? '-' }}
                        </div>                        

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">NIK</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $rujukan->nik ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Alamat</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $rujukan->alamat ?? '-' }}
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">No. Telepon</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ $rujukan->no_telepon ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Lahir</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            @if (isset($rujukan->tanggal_lahir))
                                {{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d F Y') }}
                                <span
                                    class="text-gray-500 text-xs">({{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->age }}
                                    tahun)</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Rumah Sakit Tujuan --}}
            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Rumah Sakit Tujuan</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Nama Rumah Sakit</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm font-bold">
                            {{ $rujukan->nama_rs ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Alamat</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ $rujukan->alamat_rs ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Telepon</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm italic">
                            {{ $rujukan->telepon_rs ?? 'Nomor telepon belum tersedia' }}</div>
                    </div>
                </div>
            </div>

            {{-- 4. Hasil Skrining & Tindak Lanjut --}}
            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Hasil Skrining & Tindak Lanjut</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kesimpulan Skrining</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ $rujukan->kesimpulan ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Anjuran Kontrol</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ $rujukan->anjuran_kontrol ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Pemeriksaan Berikutnya</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ $rujukan->kunjungan_berikutnya ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Pasien Datang</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            @if (isset($rujukan->anjuran_kontrol) || isset($rujukan->kunjungan_berikutnya))
                                Ya, pasien datang ke RS
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Catatan RS --}}
            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Catatan Balasan (RS)</h2>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 min-h-[80px]">
                    {{ $rujukan->catatan ?? 'Belum ada balasan dari RS.' }}
                </div>                
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('puskesmas.rujukan.index') }}"
                    class="rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                    Kembali
                </a>
                <a href="#" {{-- {{ route('puskesmas.pdf.kf-all', $rujukan->id) }} --}}
                    class="inline-flex items-center gap-2 px-4 h-10
                        bg-red-600 text-white rounded-full
                        hover:bg-red-700 transition text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/>
                        <path d="M9 15h6"/>
                        <path d="M12 18V12"/>
                    </svg>
                    <span>Download PDF</span>
                </a>
            </div>

        </main>
    </div>
</body>

</html>
