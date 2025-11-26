<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Skrining</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js', 
        'resources/js/puskesmas/sidebar-toggle.js'
        ])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            <!-- Header -->
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">List Skrining Ibu Hamil</h1>
            </header>

            <section class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-semibold text-[#1D1D1D]">Data Pasien Pre Eklampsia</h2>
                                <p class="text-xs text-[#7C7C7C]">Daftar pasien skrining preeklampsia terbaru</p>
                            </div>
                        </div>
                        
                        <!-- Tombol Download Excel dan PDF -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('puskesmas.export.excel') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" />
                                    <path d="M9 15h6" />
                                    <path d="M12 18V12" />
                                </svg>
                                Download Excel
                            </a>
                            <a href="{{ route('puskesmas.export.pdf') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" />
                                    <path d="M9 15h6" />
                                    <path d="M12 18V12" />
                                </svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                    <br>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">
                                <tr class="text-left">
                                    <th class="px-3 py-2">No</th>
                                    <th class="px-3 py-2">Nama Pasien</th>
                                    <th class="px-3 py-2">NIK</th>
                                    <th class="px-3 py-2">Tanggal Pengisian</th>
                                    <th class="px-3 py-2">Alamat</th>
                                    <th class="px-3 py-2">No Telp</th>
                                    <th class="px-3 py-2">Kesimpulan</th>
                                    <th class="px-3 py-2">View Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E9E9E9]">
                                @forelse(($skrinings ?? []) as $skrining)
                                    <tr>
                                        <td class="px-3 py-3 font-medium tabular-nums">{{ $loop->iteration }}</td>
                                        @php
                                            $nama = optional(optional($skrining->pasien)->user)->name ?? '-';
                                            $nik = optional($skrining->pasien)->nik ?? '-';
                                            $tanggal = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
                                            $alamat = optional(optional($skrining->pasien)->user)->address ?? '-';
                                            $telp = optional(optional($skrining->pasien)->user)->phone ?? '-';
                                            $conclusion = $skrining->conclusion_display ?? ($skrining->kesimpulan ?? 'Normal');
                                            $cls = $skrining->badge_class ?? 'bg-[#2EDB58] text-white';
                                        @endphp
                                        <td class="px-3 py-3">{{ $nama }}</td>
                                        <td class="px-3 py-3 tabular-nums">{{ $nik }}</td>
                                        <td class="px-3 py-3">{{ $tanggal }}</td>
                                        <td class="px-3 py-3">{{ $alamat }}</td>
                                        <td class="px-3 py-3">{{ $telp }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $cls }}">
                                                {{ $conclusion }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <a href="{{ route('puskesmas.skrining.show', $skrining->id) }}" class="px-4 py-1.5 rounded-full border border-[#D9D9D9] text-[#1D1D1D] text-xs">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">Belum ada data skrining yang lengkap.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>