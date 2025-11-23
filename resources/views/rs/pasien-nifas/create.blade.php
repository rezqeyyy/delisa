@extends('layouts.rs')

@section('title', 'Tambah Pasien Nifas')

@section('content')
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    {{-- Sidebar Rumah Sakit --}}
    <x-rs.sidebar />

    {{-- Main Content --}}
    <main
        class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('rs.pasien-nifas.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                    <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </span>
                    <span>Kembali</span>
                </a>
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D]">
                        Tambah Data Pasien Nifas
                    </h1>
                    <p class="text-xs text-[#7C7C7C]">
                        Lengkapi data identitas dan alamat pasien nifas di rumah sakit ini
                    </p>
                </div>
            </div>
        </div>

        {{-- Alert error global (jika ada) --}}
        @if(session('error'))
            <div
                class="flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-800">
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

        {{-- Kartu Form Tambah Pasien Nifas --}}
        <section class="bg-white rounded-2xl border border-[#E9E9E9] p-3 sm:p-5 space-y-4">
            <div class="border-b border-[#F0F0F0] pb-3 mb-2">
                <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">
                    Form Tambah Pasien Nifas
                </h2>
                <p class="text-xs text-[#7C7C7C] mt-1">
                    Data ini akan digunakan sebagai basis pemantauan nifas dan data anak yang akan ditambahkan
                </p>
            </div>

            <form id="formTambahPasien" method="POST" action="{{ route('rs.pasien-nifas.store') }}" class="space-y-5">
                @csrf

                {{-- Nama & NIK --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="nama_pasien" class="block text-[11px] font-semibold text-[#666666]">
                            Nama Pasien <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="nama_pasien"
                            name="nama_pasien"
                            class="block w-full rounded-lg border @error('nama_pasien') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Nama lengkap pasien"
                            value="{{ old('nama_pasien') }}"
                            required>
                        @error('nama_pasien')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="nik" class="block text-[11px] font-semibold text-[#666666]">
                            NIK <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            maxlength="16"
                            class="block w-full rounded-lg border @error('nik') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Masukkan NIK 16 digit"
                            value="{{ old('nik') }}"
                            required>
                        @error('nik')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- No Telepon --}}
                <div class="space-y-1.5">
                    <label for="no_telepon" class="block text-[11px] font-semibold text-[#666666]">
                        Nomor Telepon <span class="text-pink-600">*</span>
                    </label>
                    <input
                        type="text"
                        id="no_telepon"
                        name="no_telepon"
                        class="block w-full rounded-lg border @error('no_telepon') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                        placeholder="08xxxxxxxxxx"
                        value="{{ old('no_telepon') }}"
                        required>
                    @error('no_telepon')
                        <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-[#9B9B9B]">
                        Nomor ini akan disimpan pada akun user pasien (users.phone)
                    </p>
                </div>

                {{-- Provinsi & Kota --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="provinsi" class="block text-[11px] font-semibold text-[#666666]">
                            Provinsi <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="provinsi"
                            name="provinsi"
                            class="block w-full rounded-lg border @error('provinsi') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Contoh: Jawa Barat"
                            value="{{ old('provinsi') }}"
                            required>
                        @error('provinsi')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="kota" class="block text-[11px] font-semibold text-[#666666]">
                            Kota/Kabupaten <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="kota"
                            name="kota"
                            class="block w-full rounded-lg border @error('kota') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Contoh: Kota Depok"
                            value="{{ old('kota') }}"
                            required>
                        @error('kota')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Kecamatan & Kelurahan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="kecamatan" class="block text-[11px] font-semibold text-[#666666]">
                            Kecamatan <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="kecamatan"
                            name="kecamatan"
                            class="block w-full rounded-lg border @error('kecamatan') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Contoh: Beji"
                            value="{{ old('kecamatan') }}"
                            required>
                        @error('kecamatan')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="kelurahan" class="block text-[11px] font-semibold text-[#666666]">
                            Kelurahan <span class="text-pink-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="kelurahan"
                            name="kelurahan"
                            class="block w-full rounded-lg border @error('kelurahan') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                            placeholder="Contoh: Pondok Cina"
                            value="{{ old('kelurahan') }}"
                            required>
                        @error('kelurahan')
                            <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Domisili --}}
                <div class="space-y-1.5">
                    <label for="domisili" class="block text-[11px] font-semibold text-[#666666]">
                        Domisili <span class="text-pink-600">*</span>
                    </label>
                    <textarea
                        id="domisili"
                        name="domisili"
                        rows="4"
                        class="block w-full rounded-lg border @error('domisili') @else border-[#E5E5E5] @enderror bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] shadow-sm focus:border-[#E91E8C] focus:ring-1 focus:ring-[#E91E8C]/30"
                        placeholder="Contoh: Jl. Margonda Raya No. xx, RT xx / RW xx"
                        required>{{ old('domisili') }}</textarea>
                    @error('domisili')
                        <p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3 pt-3 border-t border-[#F0F0F0] mt-2">
                    <a href="{{ route('rs.pasien-nifas.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                        <span>Kembali</span>
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-[#C2185B]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        <span>Tambah Data Pasien</span>
                    </button>
                </div>
            </form>
        </section>

        <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </main>
</div>
@endsection

@push('scripts')
<script>
// Validasi NIK hanya angka
const nikInput = document.getElementById('nik');
if (nikInput) {
    nikInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
}

// Validasi No Telepon hanya angka
const telpInput = document.getElementById('no_telepon');
if (telpInput) {
    telpInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
}
</script>
@endpush
