@extends('layouts.rs')

@section('title', 'Riwayat Pasien')

@section('content')
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    {{-- Sidebar RS --}}
    <x-rs.sidebar />

    {{-- Main Content --}}
    <main class="flex-1 w-full xl:ml-[260px] bg-[#FAFAFA] max-w-none min-w-0 overflow-y-auto">
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

            {{-- Header --}}
            <div class="flex items-center gap-3">
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
            @if(session('success'))
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
                                <input type="text"
                                       name="riwayat_tekanan_darah"
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
                                <input type="text"
                                       name="hasil_protein_urin"
                                       placeholder="Masukkan hasil pemeriksaan urin..."
                                       value="{{ old('hasil_protein_urin', $rujukan->hasil_protein_urin) }}"
                                       class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                            </div>
                        </div>

                        {{-- Tindakan Medis (sementara dummy select) --}}
                        <div class="space-y-1">
                            <label class="text-[11px] font-semibold text-[#7C7C7C]">
                                Pilih Tindakan
                            </label>
                            <select class="w-full rounded-xl border border-[#E5E5E5] bg-white px-3 py-2 text-xs sm:text-sm text-[#1D1D1D] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                <option>Pilih Data</option>
                                <option>Rawat Inap</option>
                                <option>Rawat Jalan</option>
                                <option>Observasi</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Kartu: Resep Obat --}}
                <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA] flex items-center justify-between gap-2">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D]">
                            Resep Obat
                        </h2>
                        <button type="button"
                                onclick="alert('Fitur tambah obat')"
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
                                    @php
                                        $obatOptions = [
                                            'Kalsium 1000 - 1500mg',
                                            'Simvastatin 10mg',
                                            'Amlodipine 5mg'
                                        ];
                                        $existingObat = $resepObats->pluck('resep_obat')->toArray();
                                    @endphp

                                    @foreach($obatOptions as $index => $obat)
                                        @php
                                            $isChecked = in_array($obat, $existingObat);
                                            $resep = $resepObats->where('resep_obat', $obat)->first();
                                        @endphp
                                        <tr class="{{ $isChecked ? 'bg-[#F9FAFB]' : 'bg-white' }} hover:bg-[#FAFAFA]">
                                            <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                <input type="hidden"
                                                       name="resep_obat[{{ $index }}]"
                                                       value="{{ $obat }}">
                                                <span class="font-medium">{{ $obat }}</span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 align-top">
                                                <input type="text"
                                                       name="dosis[{{ $index }}]"
                                                       placeholder="Masukkan dosis..."
                                                       value="{{ $resep->dosis ?? '' }}"
                                                       class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 align-top">
                                                <input type="text"
                                                       name="penggunaan[{{ $index }}]"
                                                       placeholder="Cara penggunaan..."
                                                       value="{{ $resep->penggunaan ?? '' }}"
                                                       class="w-full rounded-lg border border-[#E5E5E5] bg-white px-2.5 py-1.5 text-xs sm:text-sm text-[#1D1D1D] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#E91E8C]/30 focus:border-[#E91E8C]">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                {{-- Tombol Aksi --}}
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                    <a href="{{ route('rs.skrining.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                        <span>Kembali</span>
                    </a>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                            <span>Simpan Data</span>
                        </button>
                        {{-- <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                            <span>Lihat Data Selanjutnya</span>
                        </button> --}}
                    </div>
                </div>

                <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                    © 2025 Dinas Kesehatan Kota Depok — DeLISA
                </footer>
            </form>
        </div>
    </main>
</div>
@endsection
