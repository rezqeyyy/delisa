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
            
            <div class="mb-6 flex items-center">
                <a href="{{ route('rs.dashboard') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-gray-800">Detail Skrining Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})</h1>
            </div>

            @if(!$skrining)
                <div class="rounded-2xl bg-white p-6 shadow text-center">
                    <p class="text-gray-500">Belum ada data skrining untuk pasien ini.</p>
                </div>
            @else
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

                            <div class="p-4 text-sm font-semibold">Tempat Pemeriksaan</div>
                            <div class="sm:col-span-2 p-4 text-sm">
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

                            <div class="p-4 text-sm font-semibold">Nama</div>
                            <div class="sm:col-span-2 p-4 text-sm">{{ optional(optional($skrining->pasien)->user)->name ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">NIK</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->pasien)->nik ?? '-' }}</div>

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Golongan Darah</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->pasien)->golongan_darah ?? '-' }}</div>

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
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Kontak dan Alamat</h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3">
                            <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                            {{-- No. Telepon --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">No. Telepon</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional(optional($skrining->pasien)->user)->phone ?? optional($skrining->pasien)->no_telepon ?? '-' }}
                            </div>

                            {{-- No. JKN --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">No. JKN</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->no_jkn ?? '-' }}
                            </div>

                            {{-- Provinsi --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Provinsi</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->PProvinsi ?? '-' }}
                            </div>

                            {{-- Kabupaten/Kota --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kabupaten/Kota</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->PKabupaten ?? '-' }}
                            </div>

                            {{-- Kecamatan --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kecamatan</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->PKecamatan ?? '-' }}
                            </div>

                            {{-- Kelurahan/Wilayah --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kelurahan/Wilayah</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->PWilayah ?? '-' }}
                            </div>

                            {{-- RT / RW --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">RT / RW</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                RT {{ optional($skrining->pasien)->rt ?? '-' }} / RW {{ optional($skrining->pasien)->rw ?? '-' }}
                            </div>

                            {{-- Kode Pos --}}
                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kode Pos</div>
                            <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm font-medium text-gray-900">
                                {{ optional($skrining->pasien)->kode_pos ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Hasil Skrining</h2>
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

                            <div class="border-t border-gray-200 p-4 text-sm font-semibold">Catatan</div>
                            <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ 'Belum ada catatan.' }}</div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('rs.skrining.index') }}"
                           class="rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                            Kembali
                        </a>
                    </div>
                </div>
            @endif

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4 print-hidden">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>
</html>