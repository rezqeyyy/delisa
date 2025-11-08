<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Detail Skrining</title>
    @vite(['resources/css/app.css', 'resources/js/pasien/imt.js'])
    <style>
        /* Mengimpor font Poppins dari Google Fonts agar visual teks 100% cocok dengan desain modern */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-[#F7F7F7] min-h-screen">
    <x-pasien.sidebar class="hidden xl:flex z-30" />
     
    <div class="mx-auto max-w-5xl px-4 py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center">
            <a href="{{ route('pasien.dashboard') }}" class="text-[#1D1D1D] hover:text-black">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="ml-3 text-2xl font-bold text-[#1D1D1D]">Riwayat Pasien</h1>
        </div>

        <!-- Kartu: Informasi Pasien dan Data Kehamilan -->
        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold text-[#1D1D1D]">Informasi Pasien dan Data Kehamilan</h2>

            <input type="hidden" id="tinggi_badan" value="{{ $tinggi ?? '' }}">
            <input type="hidden" id="berat_badan" value="{{ $berat ?? '' }}">

            <div class="overflow-hidden rounded-xl border border-[#EFEFEF]">
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="border-b border-[#EFEFEF] p-4 text-sm text-[#6B6B6B]">Informasi</div>
                    <div class="sm:col-span-2 border-b border-[#EFEFEF] p-4 text-sm text-[#6B6B6B]">Data</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Tanggal Pemeriksaan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') : '-' }}</div>

                    <div class="p-4 text-sm">Nama</div>
                    <div class="sm:col-span-2 p-4 text-sm">{{ $nama }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">NIK</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $nik }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Kehamilan ke (G)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $gravida }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Jumlah Persalinan (P)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $para }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Jumlah Abortus (A)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $abortus }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Usia Kehamilan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $usiaKehamilan !== '-' ? $usiaKehamilan . ' Minggu' : '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Taksiran Persalinan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
                        {{ $taksiranPersalinan ? \Carbon\Carbon::parse($taksiranPersalinan)->translatedFormat('d F Y') : '-' }}
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Indeks Masa Tubuh (IMT)</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
                        <input
                            id="imt_result"
                            type="text"
                            readonly
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            value="{{ $imt !== null ? number_format($imt, 2) : '' }}"
                            placeholder="Akan terisi otomatis oleh sistem"
                        />
                        <div id="imt_category" class="mt-1 text-xs text-[#6B6B6B] hidden"></div>
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Anjuran Kenaikan BB</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $anjuranBb ?? '-' }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Tensi/Tekanan Darah</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
                        @if($sistol && $diastol)
                            {{ $sistol }}/{{ $diastol }} mmHg
                        @else
                            -
                        @endif
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">
                        Mean Arterial Pressure (MAP)
                        <div class="mt-1 text-[10px] text-[#9B9B9B]">dihitung: diastol + (sistol − diastol)/3</div>
                    </div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $map !== null ? number_format($map, 2) . ' mmHg' : '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Kartu: Hasil Skrining dan Rekomendasi -->
        <div class="mt-8 rounded-2xl bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold text-[#1D1D1D]">Hasil Skrining dan Rekomendasi</h2>

            <div class="overflow-hidden rounded-xl border border-[#EFEFEF]">
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="border-b border-[#EFEFEF] p-4 text-sm text-[#6B6B6B]">Informasi</div>
                    <div class="sm:col-span-2 border-b border-[#EFEFEF] p-4 text-sm text-[#6B6B6B]">Data</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Jumlah Risiko Sedang</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $resikoSedang }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Jumlah Risiko Tinggi</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $resikoTinggi }}</div>

                    <!-- ===== Pemicu Risiko Sedang ===== -->
                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Pemicu Risiko Sedang</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
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
                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Pemicu Risiko Tinggi</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
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

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Kesimpulan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">{{ $kesimpulan }}</div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Rekomendasi</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
                        @if (trim($kesimpulan) === 'Skrining belum selesai')
                            Lengkapi Skrining terlebih dahulu.
                        @else
                            {{ $rekomendasi }}
                        @endif
                    </div>

                    <div class="border-t border-[#EFEFEF] p-4 text-sm">Catatan</div>
                    <div class="sm:col-span-2 border-t border-[#EFEFEF] p-4 text-sm">
                        @if (trim($kesimpulan) === 'Skrining belum selesai')
                            Skrining belum dapat disimpulkan sebelum semua data wajib diisi.
                        @else
                            {{ $catatan ?? 'Belum ada catatan.' }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('pasien.dashboard') }}"
                   class="rounded-full bg-[#F2F2F2] px-6 py-3 text-sm font-medium text-[#1D1D1D]">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>