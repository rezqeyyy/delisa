<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pasien - DELISA</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] bg-[#FAFAFA] max-w-none min-w-0 overflow-y-auto">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

                {{-- Header --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('rs.skrining.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-3 py-1.5 text-xs sm:text-sm text-[#4B4B4B] hover:bg-[#F8F8F8]">
                        <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </span>
                        <span>Kembali</span>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate">
                            Riwayat Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})
                        </h1>
                        <p class="text-xs text-[#7C7C7C]">
                            Form pemeriksaan lanjutan pasien rujukan preeklampsia
                        </p>
                    </div>
                </div>

                {{-- Alert sukses --}}
                @if (session('success'))
                    <div class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
                        <span class="mt-0.5">
                            <i class="fas fa-check-circle text-sm"></i>
                        </span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('rs.skrining.update', $skrining->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Kartu: Pengecekan Ulang Data Pasien --}}
                    <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                            <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                                Pengecekan Ulang Data Pasien
                            </h2>
                        </div>

                        <div class="px-4 sm:px-5 py-4 space-y-4 text-xs sm:text-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Pasien Datang --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Pasien Datang?
                                    </label>
                                    <select name="pasien_datang"
                                        class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                        <option value="">Pilih Data Ya/Tidak</option>
                                        <option value="1" {{ old('pasien_datang', $rujukan->pasien_datang) == 1 ? 'selected' : '' }}>Ya</option>
                                        <option value="0" {{ old('pasien_datang', $rujukan->pasien_datang) == 0 ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </div>

                                {{-- Riwayat Tekanan Darah --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Riwayat Tekanan Darah Pasien
                                    </label>
                                    <input type="text" name="riwayat_tekanan_darah"
                                        placeholder="Masukkan data riwayat tekanan darah..."
                                        value="{{ old('riwayat_tekanan_darah', $rujukan->riwayat_tekanan_darah) }}"
                                        class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Perlu Pemeriksaan Berikutnya --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Perlu Pemeriksaan Berikutnya?
                                    </label>
                                    <select name="perlu_pemeriksaan_lanjut"
                                        class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                        <option value="">Pilih Data Ya/Tidak</option>
                                        <option value="1" {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 1 ? 'selected' : '' }}>Ya</option>
                                        <option value="0" {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 0 ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                    <p class="text-[10px] text-[#9CA3AF] mt-1">
                                        * Jika pasien tidak datang maka cukup isi kolom kedatangan dan opsi pemeriksaan ulang.
                                    </p>
                                </div>

                                {{-- Hasil Pemeriksaan Protein Urin --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Hasil Pemeriksaan Protein Urin
                                    </label>
                                    <input type="text" name="hasil_protein_urin"
                                        placeholder="Masukkan hasil pemeriksaan urin..."
                                        value="{{ old('hasil_protein_urin', $rujukan->hasil_protein_urin) }}"
                                        class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                </div>
                            </div>

                            {{-- Tindakan Medis --}}
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                    Pilih Tindakan
                                </label>
                                @php
                                    $tindakanOld = old('tindakan', $riwayatRujukan->tindakan ?? '');
                                @endphp
                                <select name="tindakan"
                                    class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                    <option value="">Pilih Data</option>
                                    <option value="Rawat Inap" {{ $tindakanOld === 'Rawat Inap' ? 'selected' : '' }}>Rawat Inap</option>
                                    <option value="Rawat Jalan" {{ $tindakanOld === 'Rawat Jalan' ? 'selected' : '' }}>Rawat Jalan</option>
                                    <option value="Observasi" {{ $tindakanOld === 'Observasi' ? 'selected' : '' }}>Observasi</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    {{-- Kartu: Resep Obat --}}
                    @php
                        $obatOptions = ['Kalsium 1000 - 1500mg', 'Simvastatin 10mg', 'Amlodipine 5mg'];
                        $existingObat = $resepObats->pluck('resep_obat')->toArray();
                        $otherResepObats = $resepObats->whereNotIn('resep_obat', $obatOptions)->values();
                        $nextIndexBase = count($obatOptions) + $otherResepObats->count();
                    @endphp

                    <section id="sectionResepObat"
                        class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden"
                        data-next-index="{{ $nextIndexBase }}">
                        <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA] flex items-center justify-between gap-2">
                            <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                                Resep Obat
                            </h2>
                            <button type="button" id="btnTambahObat"
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#E91E8C] px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-[#C2185B]">
                                <i class="fas fa-plus text-[10px]"></i>
                                <span>Tambah Obat</span>
                            </button>
                        </div>

                        <div class="px-4 sm:px-5 py-4">
                            <div class="overflow-x-auto rounded-xl border border-[#E5E5E5]">
                                <table class="min-w-full text-xs sm:text-sm">
                                    <thead class="bg-[#FAFAFA] text-[#6B7280]">
                                        <tr>
                                            <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Nama Obat
                                            </th>
                                            <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Dosis
                                            </th>
                                            <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Digunakan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="obatTableBody" class="divide-y divide-[#F3F3F3] bg-white">
                                        {{-- 3 obat default --}}
                                        @foreach ($obatOptions as $index => $obat)
                                            @php
                                                $resep = $resepObats->where('resep_obat', $obat)->first();
                                                $oldDosis = old('dosis.' . $index, $resep->dosis ?? '');
                                                $oldPenggunaan = old('penggunaan.' . $index, $resep->penggunaan ?? '');
                                                $isChecked = !is_null($resep);
                                            @endphp
                                            <tr class="{{ $isChecked ? 'bg-[#F9FAFB]' : 'bg-white' }} hover:bg-[#FAFAFA]">
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                    <input type="hidden" name="resep_obat[{{ $index }}]" value="{{ $obat }}">
                                                    <span class="font-medium">{{ $obat }}</span>
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="dosis[{{ $index }}]"
                                                        placeholder="Masukkan dosis..." value="{{ $oldDosis }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="penggunaan[{{ $index }}]"
                                                        placeholder="Cara penggunaan..." value="{{ $oldPenggunaan }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Obat lain yang pernah ditambahkan --}}
                                        @php $startIndex = count($obatOptions); @endphp
                                        @foreach ($otherResepObats as $loopIndex => $resep)
                                            @php
                                                $i = $startIndex + $loopIndex;
                                                $oldNama = old('resep_obat.' . $i, $resep->resep_obat ?? '');
                                                $oldDosis = old('dosis.' . $i, $resep->dosis ?? '');
                                                $oldPenggunaan = old('penggunaan.' . $i, $resep->penggunaan ?? '');
                                            @endphp
                                            <tr class="bg-[#F9FAFB] hover:bg-[#FAFAFA]">
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                    <input type="text" name="resep_obat[{{ $i }}]"
                                                        placeholder="Nama obat..." value="{{ $oldNama }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] font-medium placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="dosis[{{ $i }}]"
                                                        placeholder="Masukkan dosis..." value="{{ $oldDosis }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="penggunaan[{{ $i }}]"
                                                        placeholder="Cara penggunaan..." value="{{ $oldPenggunaan }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Modal Tambah Obat --}}
                        <div id="modalTambahObat" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                                <div class="px-5 py-3 border-b border-[#F0F0F0] flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-[#1D1D1D]">Tambah Obat Baru</h3>
                                    <button type="button" id="btnCloseModal" class="text-[#9CA3AF] hover:text-[#4B4B4B]">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>

                                <div class="px-5 py-4 space-y-3 text-xs sm:text-sm">
                                    <div>
                                        <label class="text-[11px] font-semibold text-[#7C7C7C]">Nama Obat</label>
                                        <input id="inputNamaObat" type="text"
                                            class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] font-semibold text-[#7C7C7C]">Dosis</label>
                                        <input id="inputDosisObat" type="text"
                                            class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] font-semibold text-[#7C7C7C]">Digunakan</label>
                                        <input id="inputGunakanObat" type="text"
                                            class="w-full rounded-xl border border-[#E5E5E5] px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                    </div>
                                </div>

                                <div class="px-5 py-3 border-t border-[#F0F0F0] flex justify-end gap-2">
                                    <button type="button" id="btnCancelModal"
                                        class="rounded-full border border-[#E5E5E5] px-4 py-1.5 text-xs hover:bg-[#F8F8F8]">
                                        Batal
                                    </button>

                                    <button type="button" id="btnSimpanObat"
                                        class="rounded-full bg-[#E91E8C] px-4 py-1.5 text-xs text-white hover:bg-[#C2185B]">
                                        Simpan ke Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                        <a href="{{ route('rs.skrining.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                            <span>Kembali</span>
                        </a>

                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-5 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                    <polyline points="17,21 17,13 7,13 7,21"/>
                                    <polyline points="7,3 7,8 15,8"/>
                                </svg>
                                <span>Simpan Data</span>
                            </button>
                        </div>
                    </div>

                    <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                        © 2025 Dinas Kesehatan Kota Depok — DeLISA
                    </footer>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Modal Tambah Obat
        document.addEventListener('DOMContentLoaded', function() {
            const btnTambahObat = document.getElementById('btnTambahObat');
            const modalTambahObat = document.getElementById('modalTambahObat');
            const btnCloseModal = document.getElementById('btnCloseModal');
            const btnCancelModal = document.getElementById('btnCancelModal');
            const btnSimpanObat = document.getElementById('btnSimpanObat');
            const obatTableBody = document.getElementById('obatTableBody');
            const sectionResepObat = document.getElementById('sectionResepObat');

            // Open modal
            btnTambahObat.addEventListener('click', function() {
                modalTambahObat.classList.remove('hidden');
                modalTambahObat.classList.add('flex');
            });

            // Close modal
            function closeModal() {
                modalTambahObat.classList.add('hidden');
                modalTambahObat.classList.remove('flex');
                document.getElementById('inputNamaObat').value = '';
                document.getElementById('inputDosisObat').value = '';
                document.getElementById('inputGunakanObat').value = '';
            }

            btnCloseModal.addEventListener('click', closeModal);
            btnCancelModal.addEventListener('click', closeModal);

            // Click outside to close
            modalTambahObat.addEventListener('click', function(e) {
                if (e.target === modalTambahObat) {
                    closeModal();
                }
            });

            // Simpan obat baru
            btnSimpanObat.addEventListener('click', function() {
                const namaObat = document.getElementById('inputNamaObat').value.trim();
                const dosisObat = document.getElementById('inputDosisObat').value.trim();
                const gunakanObat = document.getElementById('inputGunakanObat').value.trim();

                if (!namaObat) {
                    alert('Nama obat harus diisi!');
                    return;
                }

                let nextIndex = parseInt(sectionResepObat.dataset.nextIndex) || 0;

                const newRow = document.createElement('tr');
                newRow.className = 'bg-[#F9FAFB] hover:bg-[#FAFAFA]';
                newRow.innerHTML = `
                    <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                        <input type="text" name="resep_obat[${nextIndex}]"
                            value="${namaObat}"
                            class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] font-medium placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                    </td>
                    <td class="px-3 sm:px-4 py-2.5 align-top">
                        <input type="text" name="dosis[${nextIndex}]"
                            value="${dosisObat}"
                            placeholder="Masukkan dosis..."
                            class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                    </td>
                    <td class="px-3 sm:px-4 py-2.5 align-top">
                        <input type="text" name="penggunaan[${nextIndex}]"
                            value="${gunakanObat}"
                            placeholder="Cara penggunaan..."
                            class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                    </td>
                `;

                obatTableBody.appendChild(newRow);
                sectionResepObat.dataset.nextIndex = nextIndex + 1;

                closeModal();
            });
        });
    </script>
</body>
</html>