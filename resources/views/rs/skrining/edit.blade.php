<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pasien - DELISA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/rs/sidebar-toggle.js', 'resources/js/rs/skrinning-edit.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">

        <x-rs.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] bg-[#FFF7FC] max-w-none min-w-0 overflow-y-auto">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

                {{-- Header --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('rs.skrining.index') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold text-[#1D1D1D]">Detail Skrining Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})</h1>
                        <p class="text-l text-[#7C7C7C]">
                            Form pemeriksaan lanjutan pasien rujukan preeklampsia
                        </p>
                    </div>
                </div>

                {{-- Alert sukses --}}
                @if (session('success'))
                    <div
                        class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs sm:text-sm text-emerald-800">
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
                        <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FFFFFF]">
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
                                        <option value="1"
                                            {{ old('pasien_datang', $rujukan->pasien_datang) == 1 ? 'selected' : '' }}>
                                            Ya</option>
                                        <option value="0"
                                            {{ old('pasien_datang', $rujukan->pasien_datang) == 0 ? 'selected' : '' }}>
                                            Tidak</option>
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
                                        <option value="1"
                                            {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 1 ? 'selected' : '' }}>
                                            Ya</option>
                                        <option value="0"
                                            {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 0 ? 'selected' : '' }}>
                                            Tidak</option>
                                    </select>
                                    <p class="text-[10px] text-[#9CA3AF] mt-1">
                                        * Jika pasien tidak datang maka cukup isi kolom kedatangan dan opsi pemeriksaan
                                        ulang.
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

                            {{-- Anjuran Kontrol & Kunjungan Berikutnya --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Anjuran Kontrol --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Anjuran Kontrol
                                    </label>
                                    @php
                                        $anjuranOld = old(
                                            'anjuran_kontrol',
                                            optional($riwayatRujukan)->anjuran_kontrol ?? '',
                                        );
                                    @endphp
                                    <select name="anjuran_kontrol"
                                        class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                        <option value="">Pilih tujuan kontrol</option>
                                        <option value="fktp" {{ $anjuranOld === 'fktp' ? 'selected' : '' }}>
                                            FKTP (Puskesmas)
                                        </option>
                                        <option value="rs" {{ $anjuranOld === 'rs' ? 'selected' : '' }}>
                                            Rumah Sakit
                                        </option>
                                    </select>
                                    <p class="text-[10px] text-[#9CA3AF] mt-1">
                                        Sesuaikan dengan tujuan kontrol berikutnya yang dianjurkan dokter.
                                    </p>
                                </div>

                                {{-- Kunjungan Berikutnya --}}
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                        Kunjungan Berikutnya
                                    </label>
                                    <input type="text" name="kunjungan_berikutnya"
                                        placeholder="Misal: kontrol 1 minggu lagi di PKM"
                                        value="{{ old('kunjungan_berikutnya', optional($riwayatRujukan)->kunjungan_berikutnya ?? '') }}"
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
                                    <option value="Rawat Inap" {{ $tindakanOld === 'Rawat Inap' ? 'selected' : '' }}>
                                        Rawat Inap</option>
                                    <option value="Rawat Jalan" {{ $tindakanOld === 'Rawat Jalan' ? 'selected' : '' }}>
                                        Rawat Jalan</option>
                                    <option value="Observasi" {{ $tindakanOld === 'Observasi' ? 'selected' : '' }}>
                                        Observasi</option>
                                </select>
                            </div>

                            {{-- Catatan Riwayat Rujukan --}}
                            <div class="space-y-1">
                                <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                    Catatan Riwayat Rujukan
                                </label>
                                <textarea name="catatan" rows="3" placeholder="Masukkan catatan riwayat rujukan..."
                                    class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C] resize-none">{{ old('catatan', $riwayatRujukan->catatan ?? '') }}</textarea>
                            </div>
                        </div>
                    </section>

                    {{-- Kartu: Resep Obat --}}
                    @php
                        $existingObats = $resepObats ?? collect();
                        $nextIndexBase = $existingObats->count();
                    @endphp

                    <section id="sectionResepObat"
                        class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden"
                        data-next-index="{{ $nextIndexBase }}">
                        <div
                            class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FFFFFF] flex items-center justify-between gap-2">
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
                                    <thead class="bg-[#FFF7FC] text-[#6B7280]">
                                        <tr>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Nama Obat
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Dosis
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                                Digunakan
                                            </th>
                                            <th
                                                class="px-3 sm:px-4 py-2.5 text-center font-semibold uppercase tracking-wide text-[10px] sm:text-[11px] w-16">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="obatTableBody" class="divide-y divide-[#F3F3F3] bg-white">
                                        {{-- Existing medicines --}}
                                        @forelse ($existingObats as $index => $resep)
                                            <tr class="bg-white hover:bg-[#FAFAFA] obat-row">
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                    <input type="text" name="resep_obat[{{ $index }}]"
                                                        placeholder="Nama obat..."
                                                        value="{{ old('resep_obat.' . $index, $resep->resep_obat ?? '') }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] font-medium placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="dosis[{{ $index }}]"
                                                        placeholder="Masukkan dosis..."
                                                        value="{{ old('dosis.' . $index, $resep->dosis ?? '') }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top">
                                                    <input type="text" name="penggunaan[{{ $index }}]"
                                                        placeholder="Cara penggunaan..."
                                                        value="{{ old('penggunaan.' . $index, $resep->penggunaan ?? '') }}"
                                                        class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                                </td>
                                                <td class="px-3 sm:px-4 py-2.5 align-top text-center">
                                                    <button type="button" data-action="hapus-obat"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[#DC2626] hover:bg-[#FEE2E2] transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path d="M3 6h18" />
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                            <line x1="10" y1="11" x2="10"
                                                                y2="17" />
                                                            <line x1="14" y1="11" x2="14"
                                                                y2="17" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyRow" class="bg-white">
                                                <td colspan="4"
                                                    class="px-3 sm:px-4 py-8 text-center text-[#9CA3AF]">
                                                    <div class="flex flex-col items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-8 h-8 text-[#E5E5E5]" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path
                                                                d="M10.5 20.5L5.5 15.5L15.5 5.5L20.5 10.5L10.5 20.5Z" />
                                                            <path d="M8.5 12.5L12.5 8.5" />
                                                            <path d="M2 22L5.5 18.5" />
                                                        </svg>
                                                        <span class="text-xs">Belum ada resep obat. Klik "Tambah Obat"
                                                            untuk menambahkan.</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                        <a href="{{ route('rs.skrining.index') }}"
                            class="rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] px-6 py-3 text-sm font-medium text-black">
                            <span>Kembali</span>
                        </a>

                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-5 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17,21 17,13 7,13 7,21" />
                                    <polyline points="7,3 7,8 15,8" />
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
</body>

</html>
