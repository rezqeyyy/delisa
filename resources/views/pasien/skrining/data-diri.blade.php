<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Diri — Delisa Skrining</title>
    <!-- Memuat stylesheet utama via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/pasien/wilayah.js'])

    <style>
        /* Mengimpor font Poppins dari Google Fonts agar visual teks 100% cocok dengan desain modern */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }

        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        <x-pasien.sidebar class="hidden xl:flex z-30" />

        <!-- Sidebar overlay (mobile) -->
        <x-pasien.sidebar
            x-cloak
            x-show="openSidebar"
            class="xl:hidden z-50 transform"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        />
        <!-- Background overlay untuk menutup -->
        <div
            x-cloak
            x-show="openSidebar"
            class="fixed inset-0 z-40 bg-black/40 xl:hidden"
            @click="openSidebar = false">
        </div>

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <!-- Header dengan back icon -->
            <div class="flex items-center">
                <a href="{{ route('pasien.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Isi Data Skrining</h1>
            </div>

            <!-- Stepper buletan -->
            @php
                $stepCurrent = 1;
                $stepItems = [
                    'Data Diri Pasien',
                    'Riwayat Kehamilan & Persalinan',
                    'Kondisi Kesehatan Pasien',
                    'Riwayat Penyakit Pasien',
                    'Riwayat Penyakit Keluarga',
                    'Pre Eklampsia',
                ];
            @endphp

            <x-pasien.stepper 
                :current="$stepCurrent" 
                :urls="[
                    route('pasien.data-diri', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-kehamilan-gpa', ['skrining_id' => request('skrining_id')]),
                    route('pasien.kondisi-kesehatan-pasien', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-penyakit-pasien', ['skrining_id' => request('skrining_id')]),
                    route('pasien.riwayat-penyakit-keluarga', ['skrining_id' => request('skrining_id')]),
                    route('pasien.preeklampsia', ['skrining_id' => request('skrining_id')]),
                ]" 
            />
            <!-- Judul langkah aktif untuk layar kecil -->
            <div class="mt-4 md:hidden">
                <h2 class="text-base font-semibold text-[#1D1D1D]">
                    {{ $stepItems[$stepCurrent - 1] }}
                </h2>
            </div>
            <p class="mt-6 text-sm text-[#B9257F]">
                * Langsung lanjut ke halaman selanjutnya jika tidak ada perubahan pada data diri pasien
            </p>

            <!-- Form Data Diri: binding 'bayar' untuk mengontrol tampilan field JKN -->
            @php
                $pasien = optional(Auth::user())->pasien;
                $statusPerkawinan = old('status_perkawinan', optional($pasien)->status_perkawinan);
            @endphp
            <form x-data="{ bayar: '{{ old('pembiayaan_kesehatan', optional(optional(Auth::user())->pasien)->pembiayaan_kesehatan) }}' }" action="{{ route('pasien.data-diri.store') }}" method="POST">
                @csrf
                <input type="hidden" name="skrining_id" value="{{ request('skrining_id') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Identitas dasar pasien -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Pasien (tidak dapat diubah)</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] bg-gray-200 px-4 py-2 text-sm"
                                value="{{ Auth::user()->name }}" disabled>
                        <div class="mt-1 text-xs text-[#B9257F]">* Nama tidak dapat diubah.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIK (tidak dapat diubah)</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] bg-gray-200 px-4 py-2 text-sm"
                                value="{{ optional($pasien)->nik }}" disabled>
                        <div class="mt-1 text-xs text-[#B9257F]">* NIK tidak dapat diubah.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                        <input name="tempat_lahir" type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                value="{{ old('tempat_lahir', optional($pasien)->tempat_lahir) }}" placeholder="Contoh: Depok">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input name="tanggal_lahir" type="date" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                value="{{ old('tanggal_lahir', optional($pasien)->tanggal_lahir) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input name="phone" type="text"
                            class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                            value="{{ old('phone', Auth::user()->phone) }}"
                            placeholder="08xxxxxxxxxx">
                    </div>

                    <!-- Status Perkawinan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Perkawinan</label>
                        <select name="status_perkawinan" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih status</option>
                            <option value="1" {{ (string)$statusPerkawinan === '1' ? 'selected' : '' }}>Menikah</option>
                            <option value="0" {{ (string)$statusPerkawinan === '0' ? 'selected' : '' }}>Belum Menikah</option>
                        </select>
                    </div>

                    <!-- Golongan Darah -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Golongan Darah</label>
                        @php $gol = old('golongan_darah', optional($pasien)->golongan_darah); @endphp
                        <select name="golongan_darah" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih golongan darah</option>
                            @foreach(['A','B','AB','O'] as $opt)
                                <option value="{{ $opt }}" {{ $gol === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pembiayaan Kesehatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pembiayaan Kesehatan</label>
                        @php $biaya = old('pembiayaan_kesehatan', optional($pasien)->pembiayaan_kesehatan); @endphp
                        <select name="pembiayaan_kesehatan" x-model="bayar"
                                class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih pembiayaan</option>
                            <option value="Pribadi" {{ $biaya === 'Pribadi' ? 'selected' : '' }}>Pribadi</option>
                            <option value="BPJS Kesehatan" {{ $biaya === 'BPJS Kesehatan' ? 'selected' : '' }}>
                                BPJS Kesehatan (JKN)
                            </option>
                            <option value="Asuransi Lain" {{ $biaya === 'Asuransi Lain' ? 'selected' : '' }}>Asuransi Lainnya</option>
                        </select>
                    </div>

                    <!-- Pendidikan Terakhir -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pendidikan Terakhir</label>
                        @php $pend = old('pendidikan', optional($pasien)->pendidikan); @endphp
                        <select name="pendidikan" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih pendidikan</option>
                            @foreach(['Tidak Sekolah','SD','SMP','SMA','D3','S1','S2','S3'] as $opt)
                                <option value="{{ $opt }}" {{ $pend === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pekerjaan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pekerjaan</label>
                        @php $pekerjaan = old('pekerjaan', optional($pasien)->pekerjaan); @endphp
                        <select name="pekerjaan" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih pekerjaan</option>
                            @foreach(['Ibu Rumah tangga','Pegawai Swasta','ASN/PNS/TNI/Polri','Wiraswasta','Pekerjaan Lainnya'] as $opt)
                                <option value="{{ $opt }}" {{ $pekerjaan === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Domisili (Alamat)</label>
                        <input name="address" type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                value="{{ old('address', Auth::user()->address) }}" placeholder="Alamat tempat tinggal">
                    </div>
                </div>

                <!-- Grid dropdown wilayah: tambahkan id + data-old -->
                <div id="wilayah-wrapper"
                    class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6"
                    data-prov="{{ old('PProvinsi', optional($pasien)->PProvinsi) }}"
                    data-kab="{{ old('PKabupaten', optional($pasien)->PKabupaten) }}"
                    data-kec="{{ old('PKecamatan', optional($pasien)->PKecamatan) }}"
                    data-kel="{{ old('PWilayah',  optional($pasien)->PWilayah) }}"
                    data-url-provinces="{{ url('/wilayah/provinces') }}"
                    data-url-regencies="{{ url('/wilayah/regencies') }}"
                    data-url-districts="{{ url('/wilayah/districts') }}"
                    data-url-villages="{{ url('/wilayah/villages') }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provinsi</label>
                        <select id="provinsi" name="PProvinsi"
                                class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm">
                            <option value="">Pilih Provinsi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kota/Kabupaten</label>
                        <select id="kabupaten" name="PKabupaten"
                                class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                disabled>
                            <option value="">Pilih Kota/Kabupaten</option>
                        </select>
                        <div class="mt-1 text-xs text-[#B9257F]">* Pilih Provinsi dahulu.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kecamatan</label>
                        <select id="kecamatan" name="PKecamatan"
                                class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                disabled>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                        <div class="mt-1 text-xs text-[#B9257F]">* Pilih Kota/Kabupaten dahulu.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelurahan</label>
                        <select id="kelurahan" name="PWilayah"
                                class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                                disabled>
                            <option value="">Pilih Kelurahan</option>
                        </select>
                        <div class="mt-1 text-xs text-[#B9257F]">* Pilih Kecamatan dahulu.</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">RT</label>
                        <input name="rt" type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                            value="{{ old('rt', optional($pasien)->rt) }}" placeholder="000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">RW</label>
                        <input name="rw" type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                            value="{{ old('rw', optional($pasien)->rw) }}" placeholder="000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Pos</label>
                        <input name="kode_pos" type="text" class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                            value="{{ old('kode_pos', optional($pasien)->kode_pos) }}" placeholder="00000">
                    </div>
                    <div x-cloak x-show="bayar === 'BPJS Kesehatan'">
                        <label class="block text-sm font-medium text-gray-700">Nomor Kartu JKN</label>
                        <input name="no_jkn" type="text"
                            class="mt-2 w-full rounded-xl border border-[#B9257F] px-4 py-2 text-sm"
                            value="{{ old('no_jkn', optional($pasien)->no_jkn) }}"
                            placeholder="Masukkan nomor kartu JKN"
                            x-bind:disabled="bayar !== 'BPJS Kesehatan'">
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('pasien.dashboard') }}"
                        class="rounded-full bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Kembali
                    </a>
                    <button type="submit"
                        class="rounded-full bg-[#B9257F] px-6 py-3 text-sm font-medium text-white hover:bg-[#a51f73]">
                        Lanjut
                    </button>
                </div>
            </form>

        </main>
    </div>
</body>
</html>