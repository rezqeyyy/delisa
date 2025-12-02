{{-- 
    File: show.blade.php
    Fungsi: Halaman detail data skrining pasien untuk Bidan
    Menampilkan informasi lengkap hasil skrining preeklampsia
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan – Skrining</title>
    
    {{-- 
        Load asset menggunakan Vite
        Termasuk script imt.js untuk kalkulasi IMT otomatis
    --}}
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/pasien/imt.js', 
        'resources/js/dropdown.js', 
        'resources/js/pasien/sidebar-toggle.js'
        ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- Container dengan Alpine.js untuk sidebar toggle --}}
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        {{-- Component sidebar bidan --}}
        <x-bidan.sidebar />

        {{-- Main content area --}}
        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            {{-- Alert success jika ada pesan dari session --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            
            {{-- Header dengan tombol back dan judul --}}
            <div class="mb-6 flex items-center">
                {{-- Tombol back ke halaman index --}}
                <a href="{{ route('bidan.skrining') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-gray-800">Data Detail Pasien</h1>
            </div>

            {{-- SECTION 1: Informasi Pasien dan Data Kehamilan --}}
            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Informasi Pasien dan Data Kehamilan</h2>

                {{-- 
                    Hidden input untuk kalkulasi IMT oleh JavaScript
                    Script imt.js akan membaca value ini
                --}}
                <input type="hidden" id="tinggi_badan" value="{{ optional($skrining->kondisiKesehatan)->tinggi_badan ?? '' }}">
                <input type="hidden" id="berat_badan" value="{{ optional($skrining->kondisiKesehatan)->berat_badan_saat_hamil ?? '' }}">

                {{-- Tabel informasi pasien dengan layout 3 kolom --}}
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        {{-- Header tabel dengan background pink --}}
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        {{-- Tanggal Pemeriksaan --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Tanggal Pemeriksaan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->created_at)->format('d F Y') }}</div>

                        <div class="p-4 text-sm font-semibold">Tempat Pemeriksaan</div>
                        <div class="sm:col-span-2 p-4 text-sm">
                            @php($pkm = optional($skrining->puskesmas))
                            @if($pkm && (bool) $pkm->is_mandiri)
                                <div class="font-medium">{{ $pkm->nama_puskesmas ?? '-' }}</div>
                                <div class="text-xs text-[#6B7280]">Bidan Mandiri — Kec. {{ $pkm->kecamatan ?? '-' }}</div>
                            @else
                                {{ $pkm->nama_puskesmas ?? '-' }}
                            @endif
                        </div>

                        {{-- Nama Pasien dari relasi user --}}
                        <div class="p-4 text-sm font-semibold">Nama</div>
                        <div class="sm:col-span-2 p-4 text-sm">{{ optional(optional($skrining->pasien)->user)->name ?? '-' }}</div>

                        {{-- NIK Pasien --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">NIK</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->pasien)->nik ?? '-' }}</div>

                        {{-- Data GPA (Gravida, Para, Abortus) --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kehamilan ke (G)</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_kehamilan ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Persalinan (P)</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_persalinan ?? '-' }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Abortus (A)</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->riwayatKehamilanGpa)->total_abortus ?? '-' }}</div>

                        {{-- Usia Kehamilan dalam minggu --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Usia Kehamilan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->usia_kehamilan ? optional($skrining->kondisiKesehatan)->usia_kehamilan . ' Minggu' : '-' }}</div>

                        {{-- Taksiran Persalinan (HPL) --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Taksiran Persalinan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan ? \Carbon\Carbon::parse(optional($skrining->kondisiKesehatan)->tanggal_perkiraan_persalinan)->format('d F Y') : '-' }}</div>

                        {{-- 
                            IMT (Indeks Massa Tubuh)
                            Input readonly yang akan diisi otomatis oleh imt.js
                        --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Indeks Masa Tubuh (IMT)</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            <input id="imt_result" type="text" readonly class="w-full rounded-md border px-3 py-2 text-sm" value="{{ optional($skrining->kondisiKesehatan)->imt !== null ? number_format(optional($skrining->kondisiKesehatan)->imt, 2) : '' }}" placeholder="Akan terisi otomatis oleh sistem" />
                        </div>

                        {{-- Status IMT (Normal, Kurus, Gemuk, dll) --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Status IMT</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            <span id="imt_category" class="{{ optional($skrining->kondisiKesehatan)->status_imt ? '' : 'text-gray-400' }}">{{ optional($skrining->kondisiKesehatan)->status_imt ?? '-' }}</span>
                        </div>

                        {{-- Anjuran Kenaikan Berat Badan --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Anjuran Kenaikan BB</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->anjuran_kenaikan_bb ?? '-' }}</div>

                        {{-- 
                            Tekanan Darah (Systolic/Diastolic)
                            Format: SDP/DBP mmHg
                        --}}
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

                        {{-- 
                            MAP (Mean Arterial Pressure)
                            Rumus: DBP + 1/3(SDP - DBP)
                        --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Mean Arterial Pressure (MAP)</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ optional($skrining->kondisiKesehatan)->map !== null ? number_format(optional($skrining->kondisiKesehatan)->map, 2) . ' mmHg' : '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Hasil Skrining dan Rekomendasi --}}
            <div class="mt-8 rounded-2xl bg-white p-6 shadow">
                <h2 class="mb-4 text-xl font-semibold text-gray-800">Hasil Skrining dan Rekomendasi</h2>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        {{-- Header tabel --}}
                        <div class="border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Informasi</div>
                        <div class="sm:col-span-2 border-b border-gray-200 p-4 text-sm bg-pink-50 font-semibold">Data</div>

                        {{-- Jumlah Risiko Sedang dan Tinggi --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Resiko Sedang</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->jumlah_resiko_sedang ?? 0 }}</div>

                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Jumlah Resiko Tinggi</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->jumlah_resiko_tinggi ?? 0 }}</div>

                        {{-- 
                            Pemicu Risiko Sedang
                            Tampilkan sebagai unordered list jika ada data
                        --}}
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

                        {{-- Pemicu Risiko Tinggi --}}
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

                        {{-- Riwayat Penyakit Pasien --}}
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

                        {{-- Riwayat Penyakit Keluarga --}}
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

                        {{-- Kesimpulan Skrining --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Kesimpulan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ $skrining->kesimpulan ?? '-' }}</div>

                        {{-- 
                            Rekomendasi berdasarkan kesimpulan
                            Logic:
                            - "Beresiko" → rujuk ke RS
                            - "Normal/Aman" → jaga kesehatan
                            - "Waspada" → pantau berkala
                        --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Rekomendasi</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">
                            @if(!empty($rujukanAccepted) && $rujukanAccepted)
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">Diterima RS</span>
                                </div>
                                <p>Rujukan telah diterima oleh {{ $rujukanRsName ?? 'Rumah Sakit' }}. Ikuti instruksi dari rumah sakit untuk pemeriksaan lanjutan.</p>
                            @else
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
                            @endif
                        </div>

                        {{-- Catatan (placeholder) --}}
                        <div class="border-t border-gray-200 p-4 text-sm font-semibold">Catatan</div>
                        <div class="sm:col-span-2 border-t border-gray-200 p-4 text-sm">{{ 'Belum ada catatan.' }}</div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-6 flex items-center justify-end gap-4">
                    {{-- Tombol Kembali ke list --}}
                    <a href="{{ route('bidan.skrining') }}" class="rounded-lg bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">Kembali</a>
                    
                    {{-- 
                        Form untuk mark "Sudah Diperiksa"
                        Button disabled jika sudah tindak lanjut
                    --}}
                    <form action="{{ route('bidan.skrining.followUp', $skrining->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg bg-green-500 px-6 py-3 text-sm font-medium text-white hover:bg-green-600 {{ $skrining->tindak_lanjut ? 'cursor-not-allowed opacity-60' : '' }}" {{ $skrining->tindak_lanjut ? 'disabled' : '' }}>
                            {{ $skrining->tindak_lanjut ? 'Telah Diperiksa' : 'Sudah Diperiksa' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok – DeLISA
            </footer>
        </main>        
    </div>
</body>
</html>