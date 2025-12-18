<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat KF{{ $jenisKf }} - Puskesmas</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/puskesmas/sidebar-toggle.js', 'resources/js/puskesmas/kf-form.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">

                    @if (session('error'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div
                            class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <h1 class="text-2xl font-semibold text-[#1D1D1D] text-center">Formulir Pantauan Kunjungan Nifas (KF{{ $jenisKf }})</h1>
                    <p class="text-[#7C7C7C] mb-6 text-center">{{ $pasienNifas->pasien?->user?->name ?? 'N/A' }}</p>

                    <form
                        action="{{ route('puskesmas.pasien-nifas.catat-kf', ['type' => $type, 'id' => $pasienNifas->id, 'jenisKf' => $jenisKf]) }}"
                        method="POST">
                        @csrf

                        <!-- Tanggal Kunjungan -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                Tanggal Kunjungan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_kunjungan"
                                value="{{ old('tanggal_kunjungan', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                                class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                required>
                            @error('tanggal_kunjungan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tekanan Darah & MAP -->
                        <div class="mb-6 space-y-6">
                            <!-- Tekanan Darah -->
                            <div>
                                <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                    Tekanan Darah <span class="text-red-500">*</span>
                                </label>
                                <hr class="border-gray-200 mb-4">
                                
                                <div class="mt-2 flex items-center gap-4">
                                    <!-- SBP -->
                                    <div class="relative flex-1">
                                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">SBP</label>
                                        <div class="relative">
                                            <input type="number" name="sbp" id="sbp" value="{{ old('sbp') }}"
                                                min="50" max="300"
                                                class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#B9257F] sbp-input"
                                                placeholder="Sistolik" required>
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">mmHg</span>
                                        </div>
                                        @error('sbp')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <span class="text-[#1D1D1D] pt-6">/</span>
                                    
                                    <!-- DBP -->
                                    <div class="relative flex-1">
                                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">DBP</label>
                                        <div class="relative">
                                            <input type="number" name="dbp" id="dbp" value="{{ old('dbp') }}"
                                                min="30" max="200"
                                                class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#B9257F] dbp-input"
                                                placeholder="Diastolik" required>
                                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-[#B9257F] font-medium">mmHg</span>
                                        </div>
                                        @error('dbp')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- MAP -->
                            <div>
                                <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Mean Arterial Pressure (MAP)</label>
                                <input type="number" name="map" id="map" value="{{ old('map') }}"
                                    min="40" max="250"
                                    class="w-full rounded-full border border-[#B9257F] px-5 py-3 text-sm placeholder-[#B9257F] focus:outline-none focus:ring-2 focus:ring-[#B9257F] map-input"
                                    placeholder="Akan terisi otomatis" readonly>
                                
                                @error('map')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div id="map-status" class="mt-1 text-xs font-medium hidden">
                                    <span id="map-status-text"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Keadaan Umum -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Keadaan Umum Ibu</label>
                            <textarea name="keadaan_umum" rows="3"
                                class="w-full rounded-2xl border border-[#B9257F] px-5 py-3 text-sm placeholder-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                placeholder="Deskripsikan keadaan umum ibu nifas">{{ old('keadaan_umum') }}</textarea>
                            @error('keadaan_umum')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanda Bahaya -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Tanda Bahaya yang
                                Ditemukan</label>
                            <textarea name="tanda_bahaya" rows="3"
                                class="w-full rounded-2xl border border-[#B9257F] px-5 py-3 text-sm placeholder-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#B9257F]"
                                placeholder="Tuliskan tanda bahaya jika ada">{{ old('tanda_bahaya') }}</textarea>
                            @error('tanda_bahaya')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kesimpulan Pantauan -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                Kesimpulan Pantauan <span class="text-red-500">*</span>
                            </label>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label
                                    class="flex items-center p-4 border border-[#B9257F] rounded-xl cursor-pointer">
                                    <input type="radio" name="kesimpulan_pantauan" value="Sehat"
                                        {{ old('kesimpulan_pantauan', 'Sehat') == 'Sehat' ? 'checked' : '' }}
                                        class="mr-3 text-[#B9257F] focus:ring-[#B9257F]" required>
                                    <div>
                                        <div class="font-medium text-green-600">Sehat</div>
                                        <div class="text-xs text-gray-500">Kondisi ibu baik</div>
                                    </div>
                                </label>

                                <label
                                    class="flex items-center p-4 border border-[#B9257F] rounded-xl cursor-pointer">
                                    <input type="radio" name="kesimpulan_pantauan" value="Dirujuk"
                                        {{ old('kesimpulan_pantauan') == 'Dirujuk' ? 'checked' : '' }}
                                        class="mr-3 text-[#B9257F] focus:ring-[#B9257F]">
                                    <div>
                                        <div class="font-medium text-amber-600">Dirujuk</div>
                                        <div class="text-xs text-gray-500">Perlu dirujuk ke RS</div>
                                    </div>
                                </label>

                                <label
                                    class="flex items-center p-4 border border-[#B9257F] rounded-xl cursor-pointer">
                                    <input type="radio" name="kesimpulan_pantauan" value="Meninggal"
                                        {{ old('kesimpulan_pantauan') == 'Meninggal' ? 'checked' : '' }}
                                        class="mr-3 text-[#B9257F] focus:ring-[#B9257F]">
                                    <div>
                                        <div class="font-medium text-red-600">Meninggal</div>
                                        <div class="text-xs text-gray-500">Ibu meninggal dunia</div>
                                    </div>
                                </label>
                            </div>

                            @error('kesimpulan_pantauan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 justify-between">
                            <a href="{{ route('puskesmas.pasien-nifas.show', ['type' => $type, 'id' => $pasienNifas->id]) }}"
                                class="px-4 py-2 border border-[#E9E9E9] text-[#7C7C7C] rounded-xl hover:bg-gray-50 transition-colors">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-[#B9257F] text-white rounded-xl hover:bg-[#9D1B6A] transition-colors">
                                Simpan KF{{ $jenisKf }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>