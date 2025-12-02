<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Tambah Data Anak</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/bidan/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    <x-bidan.sidebar />

    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
        <header class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('bidan.pasien-nifas') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold text-[#1D1D1D]">Tambah Data Anak Pasien Nifas</h1>
            </div>
        </header>

        <section class="bg-white rounded-2xl border border-[#E9E9E9] p-4 sm:p-6">
            <div class="border-b border-[#F0F0F0] pb-3">
                <h2 class="text-base sm:text-lg font-semibold text-[#1D1D1D]">Tambah Data Anak</h2>
                <p class="text-xs text-[#7C7C7C] mt-1">Lengkapi data berikut untuk menambah catatan anak pada pasien nifas ini</p>
            </div>
            @if(session('success'))
                <div class="mt-3 flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mt-3 flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-800">
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs sm:text-sm text-red-700">
                    <ul class="list-disc ml-4">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST"
                  action="{{ route('bidan.pasien-nifas.store-anak', $rowId ?? request()->route('id')) }}"
                  class="space-y-5">
                @csrf

                <input type="hidden" name="nifas_id" value="{{ $nifasId ?? '' }}"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Nama Anak</label>
                        <input type="text" name="nama_anak" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('nama_anak') }}" placeholder="Nama lengkap anak">
                        @error('nama_anak')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Anak ke-</label>
                        <input type="number" name="anak_ke" min="1" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('anak_ke') }}" placeholder="Contoh: 1">
                        @error('anak_ke')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('tanggal_lahir') }}">
                        @error('tanggal_lahir')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Jenis Kelamin</label>
                        @php $jk = old('jenis_kelamin'); @endphp
                        <select name="jenis_kelamin" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" {{ $jk === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ $jk === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Usia Kehamilan Saat Lahir (minggu)</label>
                        <input type="number" name="usia_kehamilan_saat_lahir" min="20" max="45" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('usia_kehamilan_saat_lahir') }}" placeholder="Contoh: 38">
                        @error('usia_kehamilan_saat_lahir')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Berat Lahir (gram)</label>
                        <input type="number" name="berat_lahir_anak" min="500" max="6000" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('berat_lahir_anak') }}" placeholder="Contoh: 3000">
                        @error('berat_lahir_anak')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Panjang Lahir (cm)</label>
                        <input type="number" name="panjang_lahir_anak" min="25" max="65" step="0.1" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('panjang_lahir_anak') }}" placeholder="Contoh: 50">
                        @error('panjang_lahir_anak')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Lingkar Kepala (cm)</label>
                        <input type="number" name="lingkar_kepala_anak" min="20" max="45" step="0.1" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" value="{{ old('lingkar_kepala_anak') }}" placeholder="Contoh: 33">
                        @error('lingkar_kepala_anak')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Memiliki Buku KIA</label>
                        @php $kia = old('memiliki_buku_kia'); @endphp
                        <select name="memiliki_buku_kia" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <option value="">Pilih</option>
                            <option value="1" {{ $kia === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ $kia === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('memiliki_buku_kia')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Buku KIA Bayi Kecil</label>
                        @php $kiaKecil = old('buku_kia_bayi_kecil'); @endphp
                        <select name="buku_kia_bayi_kecil" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <option value="">Pilih</option>
                            <option value="1" {{ $kiaKecil === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ $kiaKecil === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('buku_kia_bayi_kecil')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">IMD (Inisiasi Menyusu Dini)</label>
                        @php $imd = old('imd'); @endphp
                        <select name="imd" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <option value="">Pilih</option>
                            <option value="1" {{ $imd === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ $imd === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('imd')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Kondisi Ibu</label>
                        @php $kIbu = old('kondisi_ibu'); @endphp
                        <select name="kondisi_ibu" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <option value="">Pilih</option>
                            <option value="aman" {{ $kIbu === 'aman' ? 'selected' : '' }}>Aman</option>
                            <option value="perlu_tindak_lanjut" {{ $kIbu === 'perlu_tindak_lanjut' ? 'selected' : '' }}>Perlu Tindak Lanjut</option>
                        </select>
                        @error('kondisi_ibu')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Riwayat Penyakit (pisahkan dengan koma)</label>
                        <textarea name="riwayat_penyakit" rows="3" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" placeholder="Contoh: Asma, Alergi">{{ old('riwayat_penyakit') }}</textarea>
                        @error('riwayat_penyakit')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Keterangan Masalah Lain</label>
                        <textarea name="keterangan_masalah_lain" rows="3" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" placeholder="Jika ada masalah lain">{{ old('keterangan_masalah_lain') }}</textarea>
                        @error('keterangan_masalah_lain')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#666666]">Catatan Kondisi Ibu</label>
                    <textarea name="catatan_kondisi_ibu" rows="3" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" placeholder="Catatan tambahan terkait kondisi ibu">{{ old('catatan_kondisi_ibu') }}</textarea>
                    @error('catatan_kondisi_ibu')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('bidan.pasien-nifas') }}" class="px-5 py-2 rounded-full border border-[#D9D9D9] bg-white text-xs sm:text-sm font-semibold text-[#1D1D1D]">Batal</a>
                    <button type="submit" class="px-5 py-2 rounded-full bg-[#FF5BAE] text-white text-xs sm:text-sm font-semibold hover:bg-[#E91E8C]">
                        Simpan Data Anak
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>