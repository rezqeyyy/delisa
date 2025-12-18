<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Detail Skrining</title>
    @vite(['resources/css/app.css', 'resources/js/pasien/imt.js', 'resources/js/pasien/sidebar-toggle.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen">
    <x-pasien.sidebar />
     
    <div class="lg:ml-[260px] mx-auto max-w-8xl px-3 sm:px-6 lg:px-8 py-6 lg:py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center">            
            <a href="{{ route('pasien.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Riwayat Pasien</h1>
        </div>

        <!-- Kartu: Informasi Pasien dan Data Kehamilan -->
        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold text-[#1D1D1D]">Informasi Pasien dan Data Kehamilan</h2>

            <input type="hidden" id="tinggi_badan" value="{{ $tinggi ?? '' }}">
            <input type="hidden" id="berat_badan" value="{{ $berat ?? '' }}">

            <div class="overflow-hidden rounded-xl border border-[#EFEFEF]">
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">Informasi</div>
                    <div class="sm:col-span-2 border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">Data</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Tanggal Pemeriksaan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') : '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Tempat Pemeriksaan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @php($pkm = optional($skrining->puskesmas))
                        @if($pkm && (bool) $pkm->is_mandiri)
                            <div class="font-medium">{{ $pkm->nama_puskesmas ?? '-' }}</div>
                            <div class="text-xs text-[#6B7280]">Bidan Mandiri — Kec. {{ $pkm->kecamatan ?? '-' }}</div>
                        @else
                            @php($name = $pkm->nama_puskesmas ?? null)
                            <div class="font-medium">{{ $name ? (\Illuminate\Support\Str::startsWith(strtolower($name), 'puskesmas') ? $name : 'Puskesmas ' . $name) : '-' }}</div>
                            <div class="text-xs text-[#6B7280]">Kec. {{ $pkm->kecamatan ?? '-' }}</div>
                        @endif
                    </div>
                    
                    <div class="p-4 text-l font-semibold">Nama</div>
                    <div class="sm:col-span-2 p-4 text-l">{{ $nama }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">NIK</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $nik }}</div>  

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Kehamilan ke (G)</div>                 
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $gravida }}</div>   

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Jumlah Persalinan (P)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $para }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Jumlah Abortus (A)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $abortus }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Usia Kehamilan</div>           
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $usiaKehamilan !== '-' ? $usiaKehamilan . ' Minggu' : '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Taksiran Persalinan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        {{ $taksiranPersalinan ? \Carbon\Carbon::parse($taksiranPersalinan)->translatedFormat('d F Y') : '-' }}
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Indeks Masa Tubuh (IMT)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        <input
                            id="imt_result"
                            type="text"
                            readonly
                            class="w-full rounded-md border px-3 py-2 text-l"
                            value="{{ $imt !== null ? number_format($imt, 2) : '' }}"
                            placeholder="Akan terisi otomatis oleh sistem"
                        />
                        <div id="imt_category" class="mt-1 text-xs text-[#6B6B6B] hidden"></div>
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Anjuran Kenaikan BB</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $anjuranBb ?? '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Tensi/Tekanan Darah</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @if($sistol && $diastol)
                            {{ $sistol }}/{{ $diastol }} mmHg
                        @else
                            -
                        @endif
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">
                        Mean Arterial Pressure (MAP)                        
                    </div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $map !== null ? number_format($map, 2) . ' mmHg' : '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Pemeriksaan Protein Urine</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $proteinUrine ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Kartu: Hasil Skrining dan Rekomendasi -->
        <div class="mt-8 rounded-2xl bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold text-[#1D1D1D]">Hasil Skrining dan Rekomendasi</h2>

            <div class="overflow-hidden rounded-xl border border-[#EFEFEF]">
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">Informasi</div>
                    <div class="sm:col-span-2 border-b border-[#EFEFEF] p-4 text-l bg-[#FFF7FC] font-semibold">Data</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Jumlah Risiko Sedang</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $resikoSedang }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Jumlah Risiko Tinggi</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $resikoTinggi }}</div>

                    <!-- ===== Pemicu Risiko Sedang ===== -->
                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Pemicu Risiko Sedang</div>     
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @if (count($sebabSedang))
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($sebabSedang as $s)
                                    <li>{{ $s }}</li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </div>

                    <!-- ===== Pemicu Risiko Tinggi ===== -->
                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Pemicu Risiko Tinggi</div>     
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @if (count($sebabTinggi))
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($sebabTinggi as $s)
                                    <li>{{ $s }}</li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Kesimpulan</div>        
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">{{ $kesimpulan }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Rekomendasi</div>  
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @if ($statusRujukanLabel)
                            <div class="mb-2">
                                <span class="inline-flex items-center rounded-full px-3 h-8 text-xs font-semibold {{ $statusRujukanClass }}">{{ $statusRujukanLabel }}</span>
                            </div>
                        @endif
                        <p>{{ $rekomendasi }}</p>
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-l font-semibold">Catatan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-l">
                        @if (trim($kesimpulan) === 'Skrining belum selesai')
                            Skrining belum dapat disimpulkan sebelum semua data wajib diisi.
                        @else
                            {{ $catatan ?? 'Belum ada catatan.' }}
                        @endif
                    </div>
                </div>
            </div>
            <br>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('pasien.dashboard') }}"
                    class="rounded-lg border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>