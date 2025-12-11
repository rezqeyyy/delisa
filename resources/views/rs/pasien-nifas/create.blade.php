<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tambah Pasien Nifas - DELISA</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js', 'resources/js/rs/wilayah.js'])

</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="mb-6 flex items-center gap-3">
                    <a href="{{ route('rs.pasien-nifas.index') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                            Tambah Data Pasien Nifas
                        </h1>
                        <p class="text-xs text-[#7C7C7C] mt-1">
                            Lengkapi data identitas dan alamat pasien nifas di rumah sakit ini
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alert --}}
            @if(session('success'))
                <div class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v5" />
                            <path d="M12 16h.01" />
                        </svg>
                    </span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="flex items-start gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs sm:text-sm text-blue-800">
                    <span class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                    </span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            {{-- NIK Check Result Alert --}}
            <div id="nikAlert" class="hidden"></div>

            {{-- Status Risiko Card (muncul setelah cek NIK) --}}
            <div id="statusRisikoCard" class="hidden"></div>

            {{-- Form Tambah Pasien Nifas --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] p-3 sm:p-4 space-y-4">
                <div class="border-b border-[#F0F0F0] pb-3">
                    <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">
                        Form Tambah Pasien Nifas
                    </h2>
                    <p class="text-xs text-[#7C7C7C] mt-1">
                        Data ini akan digunakan sebagai basis pemantauan nifas dan data anak yang akan ditambahkan
                    </p>
                </div>

                <form action="{{ route('rs.pasien-nifas.store') }}"
                      method="POST" id="formPasienNifas" class="space-y-4">
                    @csrf

                    {{-- Baris 1: Nama Pasien & NIK --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Nama Pasien <span class="text-pink-600">*</span>
                            </label>
                            <input
                                type="text"
                                name="nama_pasien"
                                id="nama_pasien"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                placeholder="Nama lengkap pasien"
                                value="{{ old('nama_pasien') }}"
                                required>
                            @error('nama_pasien')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                NIK <span class="text-pink-600">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="tel"
                                    name="nik"
                                    id="nik"
                                    maxlength="16"
                                    inputmode="numeric" pattern="[0-9]*"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                    placeholder="Masukkan NIK 16 digit"
                                    value="{{ old('nik') }}"
                                    required>
                                <button
                                    type="button"
                                    id="btnCekNik"
                                    data-url="{{ route('rs.pasien-nifas.cek-nik') }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#E91E8C] bg-[#E91E8C]/10 px-4 py-2 text-xs sm:text-sm font-semibold text-[#E91E8C] hover:bg-[#E91E8C] hover:text-white transition-colors whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.35-4.35" />
                                    </svg>
                                    <span>Cek</span>
                                </button>
                            </div>
                            <p class="text-[10px] text-[#7C7C7C] mt-1">
                                Klik "Cek" untuk mengisi data otomatis jika pasien sudah terdaftar
                            </p>
                            @error('nik')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Baris 2: Nomor Telepon --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                            Nomor Telepon <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            name="no_telepon"
                            id="no_telepon"
                            class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                            placeholder="08xxxxxxxxxx"
                            value="{{ old('no_telepon') }}"
                            required>
                        <p class="text-[10px] text-[#7C7C7C] mt-1">
                            Nomor ini akan disimpan pada akun user pasien (users.phone)
                        </p>
                        @error('no_telepon')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- BAGIAN YANG DITAMBAHKAN: Letakkan setelah field No Telepon dan sebelum wilayah --}}

                    {{-- Baris: Status Risiko (Hidden by default, muncul jika pasien belum punya skrining) --}}
                    <div id="statusRisikoWrapper" class="">
                        <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                            Status Risiko Pre-Eklampsia <span class="text-pink-600">*</span>
                        </label>
                        
                        <select
                            name="status_risiko_manual"
                            id="status_risiko_manual"
                            class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors">
                            <option value="">Pilih Status Risiko</option>
                            <option value="normal" {{ old('status_risiko_manual') == 'normal' ? 'selected' : '' }}>
                                Tidak Berisiko
                            </option>
                            <option value="beresiko" {{ old('status_risiko_manual') == 'beresiko' ? 'selected' : '' }}>
                                Beresiko
                            </option>
                        </select>
                        
                        <p class="text-[10px] text-[#7C7C7C] mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                            </svg>
                            Pasien ini belum memiliki data skrining. Pilih status risiko secara manual.
                        </p>
                        
                        @error('status_risiko_manual')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Baris 3 & 4: Wilayah (Provinsi → Kota/Kabupaten → Kecamatan → Kelurahan) --}}
                    <div id="wilayah-wrapper"
                         data-url-provinces="{{ route('wilayah.provinces') }}"
                         data-url-regencies="{{ url('/wilayah/regencies') }}"
                         data-url-districts="{{ url('/wilayah/districts') }}"
                         data-url-villages="{{ url('/wilayah/villages') }}"
                         data-prov="{{ old('provinsi') }}"
                         data-kab="{{ old('kota') }}"
                         data-kec="{{ old('kecamatan') }}"
                         data-kel="{{ old('kelurahan') }}"
                         class="space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                    Provinsi <span class="text-pink-600">*</span>
                                </label>
                                <select
                                    name="provinsi"
                                    id="provinsi"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                    required>
                                    <option value="">{{ old('provinsi') ? 'Memuat…' : 'Pilih Provinsi' }}</option>
                                </select>
                                @error('provinsi')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                    Kota/Kabupaten <span class="text-pink-600">*</span>
                                </label>
                                <select
                                    name="kota"
                                    id="kabupaten"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                    required>
                                    <option value="">{{ old('kota') ? 'Memuat…' : 'Pilih Kota/Kabupaten' }}</option>
                                </select>
                                @error('kota')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                    Kecamatan <span class="text-pink-600">*</span>
                                </label>
                                <select
                                    name="kecamatan"
                                    id="kecamatan"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                    required>
                                    <option value="">{{ old('kecamatan') ? 'Memuat…' : 'Pilih Kecamatan' }}</option>
                                </select>
                                @error('kecamatan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                    Kelurahan <span class="text-pink-600">*</span>
                                </label>
                                <select
                                    name="kelurahan"
                                    id="kelurahan"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                                    required>
                                    <option value="">{{ old('kelurahan') ? 'Memuat…' : 'Pilih Kelurahan' }}</option>
                                </select>
                                @error('kelurahan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Baris 5: Alamat --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                            Alamat <span class="text-pink-600">*</span>
                        </label>
                        <textarea
                            name="domisili"
                            id="domisili"
                            rows="3"
                            class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30 transition-colors"
                            placeholder="Contoh: Jl. Margonda Raya"
                            required>{{ old('domisili') }}</textarea>
                        @error('domisili')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- === TAMBAHAN FIELD SESUAI TABEL PASIENS === --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Tempat Lahir
                            </label>
                            <input type="text"
                                   name="tempat_lahir"
                                   id="tempat_lahir"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm shadow-sm focus:border-[#E91E8C]/30"
                                   placeholder="Contoh: Depok"
                                   value="{{ old('tempat_lahir') }}">
                            @error('tempat_lahir')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                                Tanggal Lahir
                            </label>
                            <input type="date"
                                   name="tanggal_lahir"
                                   id="tanggal_lahir"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm shadow-sm focus:border-[#E91E8C]/30"
                                   value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Status Perkawinan --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-[#666666] mb-1">
                            Status Perkawinan
                        </label>
                        <select name="status_perkawinan" id="status_perkawinan"
                                class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm shadow-sm focus:border-[#E91E8C]/30">
                            <option value="">Pilih Status</option>
                            <option value="Belum Menikah" {{ old('status_perkawinan') == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                            <option value="Menikah" {{ old('status_perkawinan') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        </select>
                        @error('status_perkawinan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- RT / RW / Kode Pos --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">RT</label>
                            <input type="text" name="rt" id="rt"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                                   value="{{ old('rt') }}" placeholder="Contoh: 03">
                            @error('rt')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">RW</label>
                            <input type="text" name="rw" id="rw"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                                   value="{{ old('rw') }}" placeholder="Contoh: 02">
                            @error('rw')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">Kode Pos</label>
                            <input type="text" name="kode_pos" id="kode_pos"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                                   value="{{ old('kode_pos') }}" placeholder="Contoh: 16455">
                            @error('kode_pos')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Pekerjaan & Pendidikan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">Pekerjaan</label>
                            <input type="text" name="pekerjaan" id="pekerjaan"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                                   value="{{ old('pekerjaan') }}" placeholder="Contoh: Ibu Rumah Tangga">
                            @error('pekerjaan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">Pendidikan</label>
                            <input type="text" name="pendidikan" id="pendidikan"
                                   class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                                   value="{{ old('pendidikan') }}" placeholder="Contoh: S1">
                            @error('pendidikan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Pembiayaan Kesehatan & Golongan Darah --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">Pembiayaan Kesehatan</label>
                            <select name="pembiayaan_kesehatan" id="pembiayaan_kesehatan"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm">
                                <option value="">Pilih Pembiayaan</option>
                                <option value="Pribadi" {{ old('pembiayaan_kesehatan') == 'Pribadi' ? 'selected' : '' }}>Pribadi</option>
                                <option value="BPJS Kesehatan (JKN)" {{ old('pembiayaan_kesehatan') == 'BPJS Kesehatan (JKN)' ? 'selected' : '' }}>BPJS Kesehatan (JKN)</option>
                                <option value="Asuransi Lainnya" {{ old('pembiayaan_kesehatan') == 'Asuransi Lainnya' ? 'selected' : '' }}>Asuransi Lainnya</option>
                            </select>
                            @error('pembiayaan_kesehatan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[#666666] mb-1">Golongan Darah</label>
                            <select name="golongan_darah" id="golongan_darah"
                                    class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm">
                                <option value="">Pilih Golongan Darah</option>
                                <option value="A" {{ old('golongan_darah') == 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('golongan_darah') == 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('golongan_darah') == 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('golongan_darah') == 'O' ? 'selected' : '' }}>O</option>
                            </select>
                            @error('golongan_darah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- No JKN --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-[#666666] mb-1">No JKN</label>
                        <input type="text" name="no_jkn" id="no_jkn"
                               class="block w-full rounded-lg border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm"
                               value="{{ old('no_jkn') }}" placeholder="Contoh: 1234567890123456">
                        @error('no_jkn')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </form>
            </section>

            {{-- Button Actions --}}
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                <a href="{{ route('rs.pasien-nifas.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    <span>Kembali</span>
                </a>

                <button type="submit" form="formPasienNifas"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-[#C2185B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    <span>Simpan & Lanjutkan</span>
                </button>
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    @vite(['resources/js/rs/cek-nik.js'])
    {{-- gunakan wilayah.js yg sudah ada untuk meng-handle kedua wrapper:
        - wilayah-wrapper (domisili)
        - wilayah-pelayanan-wrapper (pelayanan)
        wilayah.js kemungkinan perlu sedikit modifikasi agar dapat initialize dua wrapper berbeda
        Jika wilayah.js saat ini hanya mencari elemen berdasarkan id provinsi/kabupaten/kecamatan/kelurahan,
        maka gunakan id baru (pprovinsi, pkabupaten, pkecamatan, pwilayah) dan pastikan script juga meng-handle keduanya.
    --}}
</body>
</html>