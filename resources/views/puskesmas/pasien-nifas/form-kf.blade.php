<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat KF{{ $jenisKf }} - Puskesmas</title>
    
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
        'resources/js/puskesmas/sidebar-toggle.js'
    ])
</head>
<body class="bg-[#FFF7FC] min-h-screen">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8">
            
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('puskesmas.pasien-nifas.show', $pasienNifas->id) }}" 
                   class="inline-flex items-center text-sm text-[#B9257F] hover:text-[#9D1B6A]">
                    ← Kembali ke Detail Pasien
                </a>
            </div>

            <!-- Form -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6">
                    <h1 class="text-2xl font-bold text-[#1D1D1D] mb-2">Catat KF{{ $jenisKf }}</h1>
                    <p class="text-[#7C7C7C] mb-6">Untuk: {{ $pasienNifas->pasien?->user?->name ?? 'N/A' }}</p>
                    
                <form action="{{ route('puskesmas.pasien-nifas.catat-kf', ['id' => $pasienNifas->id, 'jenisKf' => $jenisKf]) }}" 
                    method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Input hidden untuk jenis KF jika diperlukan -->
                    <input type="hidden" name="jenis_kf" value="{{ $jenisKf }}">
                    
                    <!-- Tanggal Kunjungan -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                            Tanggal Kunjungan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                            name="tanggal_kunjungan" 
                            value="{{ old('tanggal_kunjungan', date('Y-m-d')) }}"
                            max="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20"
                            required>
                        @error('tanggal_kunjungan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tekanan Darah -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- SBP -->
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                SBP (mmHg)
                            </label>
                            <input type="number" 
                                name="sbp" 
                                id="sbp"
                                value="{{ old('sbp') }}"
                                min="50" max="300"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20 sbp-input"
                                placeholder="120">
                            @error('sbp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- DBP -->
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                DBP (mmHg)
                            </label>
                            <input type="number" 
                                name="dbp" 
                                id="dbp"
                                value="{{ old('dbp') }}"
                                min="30" max="200"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20 dbp-input"
                                placeholder="80">
                            @error('dbp')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- MAP -->
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                                MAP (mmHg)
                            </label>
                            <input type="number" 
                                name="map" 
                                id="map"
                                value="{{ old('map') }}"
                                min="40" max="250"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20 map-input"
                                placeholder="Akan terisi otomatis"
                                readonly>
                            @error('map')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">MAP = (SBP + 2×DBP) ÷ 3</p>
                            <!-- Status MAP -->
                            <div id="map-status" class="mt-1 text-xs font-medium hidden">
                                <span id="map-status-text"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keadaan Umum -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                            Keadaan Umum Ibu
                        </label>
                        <textarea name="keadaan_umum" 
                                rows="3"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20"
                                placeholder="Deskripsikan keadaan umum ibu nifas">{{ old('keadaan_umum') }}</textarea>
                        @error('keadaan_umum')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tanda Bahaya -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                            Tanda Bahaya yang Ditemukan
                        </label>
                        <textarea name="tanda_bahaya" 
                                rows="3"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20"
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
                            <!-- Sehat -->
                            <label class="flex items-center p-4 border border-[#E9E9E9] rounded-xl hover:border-[#B9257F]/50 cursor-pointer">
                                <input type="radio" 
                                    name="kesimpulan_pantauan" 
                                    value="Sehat" 
                                    {{ old('kesimpulan_pantauan', 'Sehat') == 'Sehat' ? 'checked' : '' }}
                                    class="mr-3 text-[#B9257F] focus:ring-[#B9257F]"
                                    required>
                                <div>
                                    <div class="font-medium text-green-600">Sehat</div>
                                    <div class="text-xs text-gray-500">Kondisi ibu baik</div>
                                </div>
                            </label>
                            
                            <!-- Dirujuk -->
                            <label class="flex items-center p-4 border border-[#E9E9E9] rounded-xl hover:border-[#B9257F]/50 cursor-pointer">
                                <input type="radio" 
                                    name="kesimpulan_pantauan" 
                                    value="Dirujuk" 
                                    {{ old('kesimpulan_pantauan') == 'Dirujuk' ? 'checked' : '' }}
                                    class="mr-3 text-[#B9257F] focus:ring-[#B9257F]">
                                <div>
                                    <div class="font-medium text-amber-600">Dirujuk</div>
                                    <div class="text-xs text-gray-500">Perlu dirujuk ke RS</div>
                                </div>
                            </label>
                            
                            <!-- Meninggal -->
                            <label class="flex items-center p-4 border border-[#E9E9E9] rounded-xl hover:border-[#B9257F]/50 cursor-pointer">
                                <input type="radio" 
                                    name="kesimpulan_pantauan" 
                                    value="Meninggal" 
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
                    
                    <!-- Catatan Tambahan -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1D1D1D] mb-2">
                            Catatan Tambahan
                        </label>
                        <textarea name="catatan" 
                                rows="4"
                                class="w-full px-4 py-3 border border-[#E9E9E9] rounded-xl focus:border-[#B9257F] focus:ring focus:ring-[#B9257F]/20"
                                placeholder="Catatan tambahan jika diperlukan">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tombol -->
                    <div class="flex gap-3">
                        <a href="{{ route('puskesmas.pasien-nifas.show', $pasienNifas->id) }}" 
                        class="px-6 py-3 border border-[#E9E9E9] text-[#7C7C7C] rounded-xl hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-[#B9257F] text-white rounded-xl hover:bg-[#9D1B6A] transition-colors">
                            Simpan KF{{ $jenisKf }}
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto calculate MAP function
        function calculateMAP() {
            const sbpInput = document.getElementById('sbp');
            const dbpInput = document.getElementById('dbp');
            const mapInput = document.getElementById('map');
            const mapStatus = document.getElementById('map-status');
            const mapStatusText = document.getElementById('map-status-text');
            
            if (!sbpInput || !dbpInput || !mapInput) return;
            
            const sbp = parseFloat(sbpInput.value);
            const dbp = parseFloat(dbpInput.value);
            
            // Validasi input
            if (isNaN(sbp) || isNaN(dbp)) {
                mapInput.value = '';
                mapStatus.classList.add('hidden');
                return;
            }
            
            if (sbp <= 0 || dbp <= 0) {
                mapInput.value = '';
                mapStatus.classList.add('hidden');
                return;
            }
            
            if (sbp < dbp) {
                mapInput.value = 'Error: SBP < DBP';
                mapStatusText.textContent = 'SBP harus lebih tinggi dari DBP';
                mapStatus.classList.remove('hidden');
                mapStatus.classList.add('text-red-600');
                return;
            }
            
            // Hitung MAP
            const map = (sbp + (2 * dbp)) / 3;
            const roundedMap = Math.round(map * 100) / 100; // 2 decimal places
            
            // Tampilkan hasil
            mapInput.value = roundedMap.toFixed(2);
            
            // Tentukan status tekanan darah
            let statusText = '';
            let statusColor = '';
            
            if (roundedMap < 70) {
                statusText = 'Hipotensi (Tekanan darah rendah)';
                statusColor = 'text-red-600';
            } else if (roundedMap >= 70 && roundedMap <= 100) {
                statusText = 'Normal';
                statusColor = 'text-green-600';
            } else if (roundedMap > 100 && roundedMap <= 110) {
                statusText = 'Pra-Hipertensi (Perlu pemantauan)';
                statusColor = 'text-amber-600';
            } else {
                statusText = 'Hipertensi (Tekanan darah tinggi)';
                statusColor = 'text-red-600';
            }
            
            // Tampilkan status
            mapStatusText.textContent = statusText;
            mapStatus.classList.remove('hidden');
            mapStatus.className = 'mt-1 text-xs font-medium ' + statusColor;
        }
        
        // Event listeners untuk perhitungan otomatis
        document.addEventListener('DOMContentLoaded', function() {
            const sbpInput = document.getElementById('sbp');
            const dbpInput = document.getElementById('dbp');
            
            if (sbpInput && dbpInput) {
                // Gunakan event input untuk real-time calculation
                sbpInput.addEventListener('input', calculateMAP);
                dbpInput.addEventListener('input', calculateMAP);
                
                // Juga gunakan event change untuk backup
                sbpInput.addEventListener('change', calculateMAP);
                dbpInput.addEventListener('change', calculateMAP);
                
                // Hitung saat halaman dimuat jika ada nilai
                if (sbpInput.value || dbpInput.value) {
                    calculateMAP();
                }
            }
            
            // Set max date to today
            const dateInput = document.querySelector('input[name="tanggal_kunjungan"]');
            if (dateInput) {
                dateInput.max = new Date().toISOString().split('T')[0];
            }
        });
        
        // Debounce function untuk optimasi (opsional)
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Versi dengan debounce jika diperlukan
        const debouncedCalculateMAP = debounce(calculateMAP, 300);
        
        // Alternatif: tambahkan event listeners dengan debounce
        document.addEventListener('DOMContentLoaded', function() {
            const sbpInput = document.getElementById('sbp');
            const dbpInput = document.getElementById('dbp');
            
            if (sbpInput && dbpInput) {
                sbpInput.addEventListener('input', debouncedCalculateMAP);
                dbpInput.addEventListener('input', debouncedCalculateMAP);
            }
        });
    </script>
</body>
</html>