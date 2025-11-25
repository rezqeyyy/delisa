<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Skrining</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/pasien/imt.js', 
        'resources/js/dropdown.js', 
        'resources/js/pasien/sidebar-toggle.js'
        ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-bidan.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
             @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            
                <div class="mb-6 flex items-center">
                    <a href="{{ route('bidan.skrining') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="ml-3 text-3xl font-bold text-gray-800">Data Detail Pasien</h1>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Pasien dan Data Kehamilan</h2>

                    <input type="hidden" id="tinggi_badan" value="{{ optional($skrining->kondisiKesehatan)->tinggi_badan ?? '' }}">
                    <input type="hidden" id="berat_badan" value="{{ optional($skrining->kondisiKesehatan)->berat_badan_saat_hamil ?? '' }}">

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Pemeriksaan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->created_at)->format('d F Y') }}</div>

                            <div class="p-4 text-sm font-semibold">Nama</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ optional(optional($skrining->pasien)->user)->name ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">NIK</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->pasien)->nik ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kehamilan ke (G)</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_kehamilan ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Persalinan (P)</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_persalinan ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Abortus (A)</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_abortus ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Usia Kehamilan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->usia_kehamilan ? optional($skrining->kondisiKesehatan)->usia_kehamilan . ' Minggu' : '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Taksiran Persalinan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan ? \Carbon\Carbon::parse(optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan)->format('d F Y') : '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Indeks Masa Tubuh (IMT)</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                <input id="imt_result" type="text" readonly class="w-full rounded-md border px-3 py-2 text-sm" value="{{ optional($skrining->kondisiKesehatan)->imt !== null ? number_format(optional($skrining->kondisiKesehatan)->imt, 2) : '' }}" placeholder="Akan terisi otomatis oleh sistem" />
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Status IMT</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                <span id="imt_category" class="{{ optional($skrining->kondisiKesehatan)->status_imt ? '' : 'text-gray-400' }}">{{ optional($skrining->kondisiKesehatan)->status_imt ?? '-' }}</span>
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Anjuran Kenaikan BB</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->anjuran_kenaikan_bb ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tensi/Tekanan Darah</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @php($s = optional($skrining->kondisiKesehatan)->sdp)
                                @php($d = optional($skrining->kondisiKesehatan)->dbp)
                                @if($s && $d)
                                    {{ $s }}/{{ $d }} mmHg
                                @else
                                    -
                                @endif
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Mean Arterial Pressure (MAP)</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->map !== null ? number_format(optional($skrining->kondisiKesehatan)->map, 2) . ' mmHg' : '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Hasil Skrining dan Rekomendasi</h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Resiko Sedang</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->jumlah_resiko_sedang ?? 0 }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Resiko Tinggi</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->jumlah_resiko_tinggi ?? 0 }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Pemicu Risiko Sedang</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @if(!empty($sebabSedang))
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($sebabSedang as $s)
                                            <li>{{ $s }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Pemicu Risiko Tinggi</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @if(!empty($sebabTinggi))
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($sebabTinggi as $s)
                                            <li>{{ $s }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Riwayat Penyakit Pasien</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @if(!empty($riwayatPenyakitPasien))
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($riwayatPenyakitPasien as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Riwayat Penyakit Keluarga</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @if(!empty($riwayatPenyakitKeluarga))
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($riwayatPenyakitKeluarga as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kesimpulan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->kesimpulan ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Rekomendasi</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                                @php($k = trim($skrining->kesimpulan ?? ''))
                                @php($rek = 'Belum ada rekomendasi.')
                                @if($k === 'Beresiko')
                                    @php($rek = 'Waspada Pre Eklampsia. Disarankan untuk segera dirujuk ke Rumah Sakit atau Dokter. Kenali tanda-tanda bahaya dalam kehamilan seperti sakit kepala hebat, pandangan kabur, dan nyeri ulu hati. Jika mengalami tanda bahaya, segera ke fasilitas kesehatan.')
                                @elseif($k === 'Normal' || $k === 'Aman')
                                    @php($rek = 'Kondisi normal, tetap jaga kesehatan dan pola makan. Lakukan pemeriksaan rutin.')
                                @elseif($k === 'Waspada')
                                    @php($rek = 'Pantau kondisi secara berkala. Kenali tanda-tanda bahaya dan segera hubungi fasilitas kesehatan jika muncul.')
                                @endif
                                {{ $rek }}
                            </div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Catatan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ 'Belum ada catatan.' }}</div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('bidan.skrining') }}" class="rounded-lg bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">Kembali</a>
                        <form action="{{ route('bidan.skrining.followUp', $skrining->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-lg bg-green-500 px-6 py-3 text-sm font-medium text-white hover:bg-green-600 {{ $skrining->tindak_lanjut ? 'cursor-not-allowed opacity-60' : '' }}" {{ $skrining->tindak_lanjut ? 'disabled' : '' }}>
                                {{ $skrining->tindak_lanjut ? 'Telah Diperiksa' : 'Sudah Diperiksa' }}
                            </button>
                        </form>
                    </div>
                </div>
               


            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>        
    </div>
</body>
</html>
                
               