<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Catat KF{{ $jenisKf }}</title>
    @vite(['resources/css/app.css','resources/js/app.js','resources/js/bidan/sidebar-toggle.js','resources/js/bidan/kf-form.js'])
</head>
<body class="bg-[#FFF7FC] min-h-screen">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    <x-bidan.sidebar />
    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8">
        <div class="mb-6">
            <a href="{{ route('bidan.pasien-nifas.detail', $pasienNifas->id) }}" class="inline-flex items-center text-sm text-[#B9257F] hover:text-[#9D1B6A]">← Kembali ke Detail Pasien</a>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                <h1 class="text-2xl font-bold text-[#1D1D1D] mb-2">Catat KF{{ $jenisKf }}</h1>
                <p class="text-[#7C7C7C] mb-6">Untuk: {{ $pasienNifas->pasien?->user?->name ?? 'N/A' }}</p>

                <div class="grid grid-cols-5 gap-2 mb-6 bg-[#1D1D1D] text-white rounded-xl p-3 text-[11px] sm:text-xs font-semibold">
                    <div class="px-2 py-2">6 Jam - 42 Hari Setelah Bersalin</div>
                    <div class="px-2 py-2 text-center {{ (int)$jenisKf === 1 ? 'bg-white/10 rounded-lg' : '' }}">KF 1<br><span class="text-white/70 font-normal">6 - 48 Jam</span></div>
                    <div class="px-2 py-2 text-center {{ (int)$jenisKf === 2 ? 'bg-white/10 rounded-lg' : '' }}">KF 2<br><span class="text-white/70 font-normal">3 - 7 Hari</span></div>
                    <div class="px-2 py-2 text-center {{ (int)$jenisKf === 3 ? 'bg-white/10 rounded-lg' : '' }}">KF 3<br><span class="text-white/70 font-normal">8 - 28 Hari</span></div>
                    <div class="px-2 py-2 text-center {{ (int)$jenisKf === 4 ? 'bg-white/10 rounded-lg' : '' }}">KF 4<br><span class="text-white/70 font-normal">29 - 42 Hari</span></div>
                </div>

                <form action="{{ route('bidan.pasien-nifas.kf.catat', ['id' => $pasienNifas->id, 'jenisKf' => $jenisKf]) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Pilih Anak</label>
                        <select name="id_anak" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" required>
                            <option value="">Pilih</option>
                            @foreach($anakList as $anak)
                                <option value="{{ $anak->id }}" {{ ($selectedAnakId ?? null) === $anak->id ? 'selected' : '' }}>Anak ke-{{ $anak->anak_ke }} — {{ $anak->nama_anak }}</option>
                            @endforeach
                        </select>
                        @error('id_anak')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-[#666666]">Tanggal Kunjungan</label>
                            <input type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan', optional($existingKf)->tanggal_kunjungan ? \Carbon\Carbon::parse($existingKf->tanggal_kunjungan)->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" required>
                            @error('tanggal_kunjungan')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#666666]">Kesimpulan</label>
                            @php($selectedKes = old('kesimpulan_pantauan', optional($existingKf)->kesimpulan_pantauan))
                            <select name="kesimpulan_pantauan" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" required>
                                <option value="">Pilih</option>
                                <option value="Sehat" {{ $selectedKes === 'Sehat' ? 'selected' : '' }}>Sehat</option>
                                <option value="Dirujuk" {{ $selectedKes === 'Dirujuk' ? 'selected' : '' }}>Dirujuk</option>
                                <option value="Meninggal" {{ $selectedKes === 'Meninggal' ? 'selected' : '' }}>Meninggal</option>
                            </select>
                            @error('kesimpulan_pantauan')<p class="text-[11px] text-red-600 mt-0.5">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-[#666666]">SBP (mmHg)</label>
                            <input type="number" id="sbp" name="sbp" value="{{ old('sbp', optional($existingKf)->sbp) }}" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#666666]">DBP (mmHg)</label>
                            <input type="number" id="dbp" name="dbp" value="{{ old('dbp', optional($existingKf)->dbp) }}" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#666666]">MAP (mmHg)</label>
                            <input type="number" id="map" name="map" step="0.01" inputmode="decimal" value="{{ old('map', optional($existingKf)->map) }}" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm">
                            <p class="mt-1 text-[11px] text-[#7C7C7C]">MAP = (SBP + 2×DBP) ÷ 3</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Keadaan Umum Ibu</label>
                        <textarea name="keadaan_umum" rows="3" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" placeholder="Catat keadaan umum ibu">{{ old('keadaan_umum', optional($existingKf)->keadaan_umum) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#666666]">Tanda Bahaya</label>
                        <textarea name="tanda_bahaya" rows="3" class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-sm" placeholder="Tuliskan tanda bahaya jika ada">{{ old('tanda_bahaya', optional($existingKf)->tanda_bahaya) }}</textarea>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('bidan.pasien-nifas.detail', $pasienNifas->id) }}" class="px-5 py-2 rounded-full border border-[#D9D9D9] bg-white text-xs sm:text-sm font-semibold text-[#1D1D1D]">Batal</a>
                        <button type="submit" class="px-5 py-2 rounded-full bg-[#FF5BAE] text-white text-xs sm:text-sm font-semibold hover:bg-[#E91E8C]">Simpan KF</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>