<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas — Detail Skrining</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/pasien/imt.js', 
        'resources/js/puskesmas/sidebar-toggle.js',
        'resources/js/puskesmas/rujukan-picker.js'
        ])
        
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 rounded-md bg-blue-100 p-4 text-blue-700">
                    {{ session('status') }}
                </div>
            @endif
            
            <div class="mb-6 flex items-center">
                <a href="{{ route('puskesmas.skrining') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-gray-800">Detail Skrining Pasien</h1>
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
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan ? \Carbon\Carbon::parse(optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan)->format('d F Y') : '-' }}
                        </div>

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
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            {{ optional($skrining->kondisiKesehatan)->map !== null ? number_format(optional($skrining->kondisiKesehatan)->map, 2) . ' mmHg' : '-' }}
                        </div>
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
                            @php($causesMod = $sebabSedang ?? [])
                            @if(!empty($causesMod))
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($causesMod as $c)
                                        <li>{{ $c }}</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Pemicu Risiko Tinggi</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            @php($causesHigh = $sebabTinggi ?? [])
                            @if(!empty($causesHigh))
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($causesHigh as $c)
                                        <li>{{ $c }}</li>
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

                {{-- Bagian bawah: indikator verifikasi di kiri, tombol aksi di kanan --}}
                <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Indikator status verifikasi skrining (pojok kiri bawah) --}}
                    <div class="flex items-center gap-2 text-xs">
                        @if($skrining->checked_status)
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="lucide lucide-check-circle h-5 w-5 text-emerald-600">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="9 12 11 14 15 10" />
                            </svg>
                            <span class="text-emerald-700 font-medium">
                                Skrining sudah diverifikasi
                            </span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="lucide lucide-check-circle h-5 w-5 text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="9 12 11 14 15 10" />
                            </svg>
                            <span class="text-gray-500">
                                Belum diverifikasi
                            </span>
                        @endif
                    </div>

                    {{-- Tombol aksi di pojok kanan bawah --}}
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('puskesmas.skrining') }}"
                           class="rounded-lg bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">
                            Kembali
                        </a>

                        @if(!$skrining->checked_status)
                            <form action="{{ route('puskesmas.skrining.verify', $skrining->id) }}"
                                  method="POST"
                                  class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-medium text-white hover:bg-emerald-700">
                                    Verifikasi Skrining
                                </button>
                            </form>
                        @endif

                        {{-- Hanya tampil jika pasien BERISIKO preeklampsia --}}
                        @if($isBerisiko)
                            <button id="btnAjukanRujukan"
                                    data-submit-url="{{ route('puskesmas.skrining.rujuk', $skrining->id) }}"
                                    data-search-url="{{ route('puskesmas.rs.search') }}"
                                    data-csrf="{{ csrf_token() }}"
                                    type="button"
                                    class="rounded-lg bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a31f70] {{ $hasReferral ? 'cursor-not-allowed opacity-60' : '' }}"
                                    {{ $hasReferral ? 'disabled' : '' }}>
                                Ajukan Rujukan
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>        
    </div>
</body>
</html>
