<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemeriksaan Pasien - DELISA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

    {{-- Print styles --}}
    <style>
        @media print {
            .print-hidden {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            main {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <div class="print-hidden">
            <x-rs.sidebar />
        </div>

        <main class="flex-1 w-full xl:ml-[260px] bg-[#FFF7FC] max-w-none min-w-0 overflow-y-auto print:ml-0">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

                <div class="mb-6 flex items-center">
                    <a href="{{ route('rs.skrining.index') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="min-w-0">
                        <h1 class="ml-3 text-3xl font-bold text-gray-800">Hasil Pemeriksaan Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})</h1>
                        <p class="ml-3 text-xs text-[#7C7C7C]">
                            Ringkasan hasil skrining puskesmas dan pemeriksaan di RS
                        </p>
                    </div>
                </div>

                {{-- Print Header (only visible when printing) --}}
                <div class="hidden print:block mb-6">
                    <div class="text-center border-b-2 border-[#E91E8C] pb-4 mb-4">
                        <h1 class="text-xl font-bold text-[#1D1D1D]">HASIL PEMERIKSAAN PASIEN</h1>
                        <p class="text-sm text-[#7C7C7C]">Sistem DeLISA - Dinas Kesehatan Kota Depok</p>
                        <p class="text-xs text-[#9CA3AF] mt-1">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>

                {{-- Alert sukses --}}
                @if (session('success'))
                    <div
                        class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800 print-hidden">
                        <span class="mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] mb-4">Informasi Pasien</h2>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            {{-- Nama --}}
                            <div class="p-4 text-sm font-semibold">Nama Lengkap</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ optional(optional($skrining->pasien)->user)->name ?? '-' }}</div>

                            {{-- NIK --}}
                            <div class="p-4 text-sm font-semibold">NIK</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ $skrining->pasien->nik ?? '-' }}</div>

                            {{-- Tanggal pemeriksaan awal --}}
                            <div class="p-4 text-sm font-semibold">Tanggal Pemeriksaan Awal</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                @if ($skrining->created_at)
                                    {{ $skrining->created_at->format('d F Y, H:i') }} WIB
                                @else
                                    -
                                @endif
                            </div>

                            {{-- Usia kehamilan --}}
                            <div class="p-4 text-sm font-semibold">Usia Kehamilan</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                {{ $skrining->kondisiKesehatan->usia_kehamilan ?? '-' }} minggu
                            </div>

                            {{-- Status awal --}}
                            <div class="p-4 text-sm font-semibold">Status Awal</div>
                            <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                                @php
                                    $conclusion =
                                        $skrining->kesimpulan ?? ($skrining->status_pre_eklampsia ?? 'Normal');
                                    $badgeClass = match (strtolower($conclusion)) {
                                        'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                        'normal', 'aman' => 'bg-[#D1FAE5] text-[#059669]',
                                        'waspada', 'menengah' => 'bg-[#FEF3C7] text-[#D97706]',
                                        default => 'bg-[#F5F5F5] text-[#6B7280]',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <span>{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>                    
                        </div>                        
                    </div>
                </div>

                {{-- Kartu: Hasil Pemeriksaan di Rumah Sakit --}}
                @php
                    $kk = $skrining->kondisiKesehatan;
                    $sistol = $kk->sdp ?? null;
                    $diastol = $kk->dbp ?? null;
                    $proteinUrine = $kk->pemeriksaan_protein_urine ?? null;
                @endphp

                <div class="rounded-2xl bg-white p-6 shadow">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] mb-4">Hasil Pemeriksaan di Rumah Sakit</h2>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            {{-- Pasien Datang --}}
                            <div class="p-4 text-sm font-semibold">Pasien Datang</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                @if ($rujukan->pasien_datang == 1)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="m9 12 2 2 4-4" />
                                        </svg>
                                        <span>Ya</span>
                                    </span>
                                @elseif($rujukan->pasien_datang == 0)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#FEE2E2] text-[#DC2626] px-3 py-1 text-[11px] font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="m15 9-6 6" />
                                            <path d="m9 9 6 6" />
                                        </svg>
                                        <span>Tidak</span>
                                    </span>
                                @else
                                    <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                @endif
                            </div>

                            {{-- Riwayat Tekanan Darah (diambil dari hasil skrining) --}}
                            <div class="p-4 text-sm font-semibold">Riwayat Tekanan Darah</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                {{ $sistol }}/{{ $diastol }} mmHg
                            </div>

                            {{-- Hasil Protein Urin (diambil dari hasil skrining) --}}
                            <div class="p-4 text-sm font-semibold">Hasil Pemeriksaan Protein Urin</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                @if ($proteinUrine)
                                    {{ $proteinUrine }}
                                @else
                                    <span class="text-[#9CA3AF] italic">
                                        Belum ada data pemeriksaan protein urin dari hasil skrining
                                    </span>
                                @endif
                            </div>

                            {{-- Perlu Pemeriksaan Lanjutan --}}
                            <div class="p-4 text-sm font-semibold">Perlu Pemeriksaan Lanjutan</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                @if ($rujukan->perlu_pemeriksaan_lanjut == 1)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 text-[11px] font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path
                                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                            <line x1="12" y1="9" x2="12"
                                                y2="13" />
                                            <line x1="12" y1="17" x2="12.01"
                                                y2="17" />
                                        </svg>
                                        <span>Ya</span>
                                    </span>
                                @elseif($rujukan->perlu_pemeriksaan_lanjut == 0)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 print:hidden"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <polyline points="20,6 9,17 4,12" />
                                        </svg>
                                        <span>Tidak</span>
                                    </span>
                                @else
                                    <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                @endif
                            </div>      
                            
                            {{-- Tindakan + Anjuran Kontrol + Kunjungan Berikutnya --}}
                            @if ($riwayatRujukan)

                                {{-- Tindakan --}}
                                <div class="p-4 text-sm font-semibold">Tindakan</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    @if ($riwayatRujukan->tindakan)
                                        {{ $riwayatRujukan->tindakan }}
                                    @else
                                        <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                    @endif
                                </div>

                                {{-- Anjuran Kontrol --}}
                                <div class="p-4 text-sm font-semibold">Tindakan</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    @if ($riwayatRujukan->anjuran_kontrol)
                                        {{ $riwayatRujukan->anjuran_kontrol }}
                                    @else
                                        <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                    @endif
                                </div>

                                {{-- Kunjungan Berikutnya --}}
                                <div class="p-4 text-sm font-semibold">Kunjungan Berikutnya</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    @if ($riwayatRujukan->kunjungan_berikutnya)
                                        {{ $riwayatRujukan->kunjungan_berikutnya }}
                                    @else
                                        <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Catatan Riwayat Rujukan --}}
                            @if ($riwayatRujukan && $riwayatRujukan->catatan)
                                <div class="p-4 text-sm font-semibold">Catatan Riwayat Rujukan</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    {{ $riwayatRujukan->catatan }}
                                </div>
                            @endif

                            {{-- Catatan Tambahan (jika ada) --}}
                            @if ($rujukan->catatan_rujukan)
                                <div class="p-4 text-sm font-semibold">Catatan Tambahan</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    {{ $rujukan->catatan_rujukan }}
                                </div>
                            @endif
                        </div>      
                                          
                    </div>
                </div>

                {{-- Kartu: Resep Obat --}}
                @if ($rujukan && $resepObats->count() > 0)
                    <div class="rounded-2xl bg-white p-6 shadow">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] mb-4">Resep Obat</h2>

                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <div class="px-4 sm:px-5 py-4">
                                <div class="overflow-x-auto rounded-xl border border-[#E5E5E5]">
                                    <table class="min-w-full text-xs sm:text-sm">
                                        <thead class="bg-[#FAFAFA] text-[#6B7280]">
                                            <tr class="bg-pink-50"> 
                                                <th
                                                    class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                    No
                                                </th>
                                                <th
                                                    class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                    Nama Obat
                                                </th>
                                                <th
                                                    class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                    Dosis
                                                </th>
                                                <th
                                                    class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                    Cara Penggunaan
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#F3F3F3] bg-white">
                                            @foreach ($resepObats as $index => $resep)
                                                <tr class="hover:bg-[#FAFAFA]">
                                                    <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                        <span class="font-semibold">{{ $resep->resep_obat }}</span>
                                                    </td>
                                                    <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                        {{ $resep->dosis ?? '-' }}
                                                    </td>
                                                    <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                        {{ $resep->penggunaan ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif            
               

                {{-- Kartu: Kesimpulan Skrining Awal --}}
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] mb-4">Kesimpulan Skrining Awal</h2>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            {{-- Jumlah risiko sedang --}}
                            <div class="p-4 text-sm font-semibold">Jumlah Risiko Sedang</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ $skrining->jumlah_resiko_sedang ?? '0' }}</div>

                            {{-- Jumlah risiko tinggi --}}
                            <div class="p-4 text-sm font-semibold">Jumlah Risiko Tinggi</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ $skrining->jumlah_resiko_tinggi ?? '0' }}</div>

                            {{-- Kesimpulan --}}
                            <div class="p-4 text-sm font-semibold">Kesimpulan</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                @php
                                    $conclusion =
                                        $skrining->kesimpulan ?? ($skrining->status_pre_eklampsia ?? 'Normal');
                                    $badgeClass2 = match (strtolower($conclusion)) {
                                        'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                        'normal', 'aman' => 'bg-[#D1FAE5] text-[#059669]',
                                        'waspada', 'menengah' => 'bg-[#FEF3C7] text-[#D97706]',
                                        default => 'bg-[#F5F5F5] text-[#6B7280]',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass2 }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <span>{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>

                            {{-- Rekomendasi --}}
                            <div class="p-4 text-sm font-semibold">Rekomendasi</div>
                            <div class="sm:col-span-2 p-4 text-sm">
                                {{ $skrining->rekomendasi ?? '-' }}
                            </div>

                            {{-- Catatan --}}
                            @if ($skrining->catatan)
                                <div class="p-4 text-sm font-semibold">Catatan</div>
                                <div class="sm:col-span-2 p-4 text-sm">
                                    {{ $skrining->catatan }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Print Footer --}}
                <div class="hidden print:block mt-8 pt-4 border-t border-gray-300">
                    <div class="flex justify-between items-end">
                        <div class="text-xs text-[#7C7C7C]">
                            <p>Dokumen ini dicetak dari sistem DeLISA</p>
                            <p>© 2025 Dinas Kesehatan Kota Depok</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-[#7C7C7C] mb-16">Depok, {{ now()->format('d F Y') }}</p>
                            <p class="text-xs font-semibold text-[#1D1D1D]">Petugas RS</p>
                            <p class="text-xs text-[#7C7C7C]">(_______________________)</p>
                        </div>
                    </div>
                </div>

                {{-- Aksi bawah --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2 print-hidden">
                    <a href="{{ route('rs.skrining.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                        <span>Kembali ke List</span>
                    </a>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        {{-- Tombol Cetak PDF --}}
                        <a href="{{ route('rs.skrining.exportPdf', $skrining->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-[#DC2626] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#DC2626] hover:bg-[#FEE2E2] w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14,2 14,8 20,8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10,9 9,9 8,9" />
                            </svg>
                            <span>Unduh PDF</span>
                        </a>

                        <a href="{{ route('rs.skrining.edit', $skrining->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            <span>Edit Data Pemeriksaan</span>
                        </a>
                    </div>
                </div>

                <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print-hidden">
                    © 2025 Dinas Kesehatan Kota Depok — DeLISA
                </footer>
            </div>
        </main>
    </div>
</body>

</html>
