<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien - DELISA</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print-hidden">
                <div class="flex items-center gap-3">
                    <a href="{{ route('rs.dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                        <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </span>
                        <span>Kembali</span>
                    </a>
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                            Detail Pasien
                        </h1>
                        <p class="text-xs text-[#7C7C7C]">
                            Informasi lengkap data pasien
                        </p>
                    </div>
                </div>
            </div>

            {{-- Profile Card --}}
            <div class="bg-gradient-to-r from-[#E91E8C] to-[#C2185B] rounded-2xl p-5 sm:p-6 shadow-lg">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center text-2xl sm:text-3xl font-bold text-[#E91E8C]">
                        {{ substr($pasien->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div class="text-center sm:text-left">
                        <h2 class="text-xl sm:text-2xl font-bold text-white">
                            {{ $pasien->user->name ?? 'Nama Pasien' }}
                        </h2>
                        <p class="text-white/90 text-sm">
                            NIK: {{ $pasien->nik ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Data Cards Container --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                
                {{-- Data Pribadi --}}
                <section class="bg-white rounded-2xl border border-[#E9E9E9] overflow-hidden">
                    <div class="flex items-center gap-3 px-4 sm:px-5 py-3 bg-[#FAFAFA] border-b border-[#F0F0F0]">
                        <span class="w-8 h-8 rounded-full bg-[#FCE7F3] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#E91E8C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <h3 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Data Pribadi</h3>
                    </div>
                    <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Nama Lengkap</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->user->name ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">NIK</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->nik ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Tempat Lahir</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->tempat_lahir ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Tanggal Lahir</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                @if($pasien->tanggal_lahir)
                                    {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Status Perkawinan</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->status_perkawinan ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Pekerjaan</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->pekerjaan ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Pendidikan</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->pendidikan ?? '-' }}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Golongan Darah</div>
                            <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->golongan_darah ?? '-' }}</div>
                        </div>
                    </div>
                </section>

                {{-- Kontak & Alamat --}}
                <div class="space-y-4 sm:space-y-6">
                    <section class="bg-white rounded-2xl border border-[#E9E9E9] overflow-hidden">
                        <div class="flex items-center gap-3 px-4 sm:px-5 py-3 bg-[#FAFAFA] border-b border-[#F0F0F0]">
                            <span class="w-8 h-8 rounded-full bg-[#FCE7F3] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#E91E8C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </span>
                            <h3 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Kontak & Alamat</h3>
                        </div>
                        <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">No. Telepon</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->no_telepon ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">No. JKN</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->no_jkn ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Provinsi</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PProvinsi ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Kabupaten/Kota</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PKabupaten ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Kecamatan</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PKecamatan ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Kelurahan/Wilayah</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PWilayah ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">RT / RW</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                    {{ $pasien->rt ? 'RT ' . $pasien->rt : '-' }} / 
                                    {{ $pasien->rw ? 'RW ' . $pasien->rw : '-' }}
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Kode Pos</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->kode_pos ?? '-' }}</div>
                            </div>
                        </div>
                    </section>

                    {{-- Data Pelayanan --}}
                    <section class="bg-white rounded-2xl border border-[#E9E9E9] overflow-hidden">
                        <div class="flex items-center gap-3 px-4 sm:px-5 py-3 bg-[#FAFAFA] border-b border-[#F0F0F0]">
                            <span class="w-8 h-8 rounded-full bg-[#FCE7F3] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#E91E8C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9,22 9,12 15,12 15,22"/>
                                </svg>
                            </span>
                            <h3 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">Data Pelayanan</h3>
                        </div>
                        <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Pelayanan</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PPelayanan ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Karakteristik</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->PKarakteristik ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2">
                                <div class="px-4 sm:px-5 py-3 text-[#7C7C7C]">Pembiayaan Kesehatan</div>
                                <div class="px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">{{ $pasien->pembiayaan_kesehatan ?? '-' }}</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row justify-center items-center gap-3 pt-4 border-t border-[#E9E9E9] print-hidden">
                <a href="{{ route('rs.dashboard') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print-hidden">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>