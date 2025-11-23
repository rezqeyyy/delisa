@extends('layouts.rs')

@section('title', 'Hasil Pemeriksaan Pasien')

@section('content')
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    {{-- Sidebar RS --}}
    <x-rs.sidebar />

    {{-- Main Content --}}
    <main class="flex-1 w-full xl:ml-[260px] bg-[#FAFAFA] max-w-none min-w-0 overflow-y-auto">
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold text-[#1D1D1D] truncate">
                            Hasil Pemeriksaan Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})
                        </h1>
                        <p class="text-xs text-[#7C7C7C]">
                            Ringkasan hasil skrining puskesmas dan pemeriksaan di RS
                        </p>
                    </div>
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

            {{-- Kartu: Informasi Pasien --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                        <span>Informasi Pasien</span>
                    </h2>
                </div>

                <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                    {{-- Nama --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Nama Lengkap
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            {{ $skrining->pasien->user->name ?? '-' }}
                        </div>
                    </div>

                    {{-- NIK --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            NIK
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            {{ $skrining->pasien->nik ?? '-' }}
                        </div>
                    </div>

                    {{-- Tanggal pemeriksaan awal --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Tanggal Pemeriksaan Awal
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            @if($skrining->created_at)
                                {{ $skrining->created_at->format('d F Y, H:i') }} WIB
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    {{-- Usia kehamilan --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Usia Kehamilan
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            {{ $skrining->kondisiKesehatan->usia_kehamilan ?? '-' }} minggu
                        </div>
                    </div>

                    {{-- Status awal --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Status Awal
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            @php
                                $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                $badgeClass = match(strtolower($conclusion)) {
                                    'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                    'normal', 'aman'       => 'bg-[#D1FAE5] text-[#059669]',
                                    'waspada', 'menengah'  => 'bg-[#FEF3C7] text-[#D97706]',
                                    default                => 'bg-[#F5F5F5] text-[#6B7280]',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                <span>{{ ucfirst($conclusion) }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Kartu: Hasil Pemeriksaan di Rumah Sakit --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                        <span>Hasil Pemeriksaan di Rumah Sakit</span>
                    </h2>
                </div>

                <div class="px-4 sm:px-5 py-4">
                    @if($rujukan)
                        <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                            {{-- Pasien Datang --}}
                            <div class="flex flex-col sm:flex-row">
                                <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                    Pasien Datang
                                </div>
                                <div class="flex-1 px-4 sm:px-5 py-3">
                                    @if($rujukan->pasien_datang === 1)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                            <i class="fas fa-check-circle text-[11px]"></i>
                                            <span>Ya</span>
                                        </span>
                                    @elseif($rujukan->pasien_datang === 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FEE2E2] text-[#DC2626] px-3 py-1 text-[11px] font-semibold">
                                            <i class="fas fa-times-circle text-[11px]"></i>
                                            <span>Tidak</span>
                                        </span>
                                    @else
                                        <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Riwayat Tekanan Darah --}}
                            <div class="flex flex-col sm:flex-row">
                                <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                    Riwayat Tekanan Darah
                                </div>
                                <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                    {{ $rujukan->riwayat_tekanan_darah ?? '-' }}
                                </div>
                            </div>

                            {{-- Hasil Protein Urin --}}
                            <div class="flex flex-col sm:flex-row">
                                <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                    Hasil Pemeriksaan Protein Urin
                                </div>
                                <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                                    {{ $rujukan->hasil_protein_urin ?? '-' }}
                                </div>
                            </div>

                            {{-- Perlu Pemeriksaan Lanjutan --}}
                            <div class="flex flex-col sm:flex-row">
                                <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                    Perlu Pemeriksaan Lanjutan
                                </div>
                                <div class="flex-1 px-4 sm:px-5 py-3">
                                    @if($rujukan->perlu_pemeriksaan_lanjut === 1)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FEF3C7] text-[#D97706] px-3 py-1 text-[11px] font-semibold">
                                            <i class="fas fa-exclamation-triangle text-[11px]"></i>
                                            <span>Ya</span>
                                        </span>
                                    @elseif($rujukan->perlu_pemeriksaan_lanjut === 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D1FAE5] text-[#059669] px-3 py-1 text-[11px] font-semibold">
                                            <i class="fas fa-check text-[11px]"></i>
                                            <span>Tidak</span>
                                        </span>
                                    @else
                                        <span class="text-[#9CA3AF] italic">Belum diisi</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Catatan Tambahan --}}
                            @if($rujukan->catatan_rujukan)
                                <div class="flex flex-col sm:flex-row">
                                    <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                        Catatan Tambahan
                                    </div>
                                    <div class="flex-1 px-4 sm:px-5 py-3">
                                        <div class="rounded-xl border border-[#E5E5E5] bg-[#F9FAFB] px-3 py-2 text-[11px] sm:text-xs text-[#4B4B4B] leading-relaxed">
                                            {{ $rujukan->catatan_rujukan }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8 space-y-3">
                            <div class="mx-auto w-12 h-12 rounded-full bg-[#F5F5F5] flex items-center justify-center text-[#BDBDBD]">
                                <i class="fas fa-clipboard-check text-xl"></i>
                            </div>
                            <p class="text-sm font-semibold text-[#1D1D1D]">
                                Belum ada data pemeriksaan dari rumah sakit
                            </p>
                            <p class="text-xs text-[#7C7C7C] max-w-md mx-auto">
                                Tambahkan data hasil pemeriksaan pasien di rumah sakit untuk melengkapi riwayat klinis preeklampsia.
                            </p>
                            <div class="pt-1">
                                <a href="{{ route('rs.skrining.edit', $skrining->id) }}"
                                   class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B]">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Tambah Data Pemeriksaan</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Kartu: Resep Obat --}}
            @if($rujukan && $resepObats->count() > 0)
                <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                        <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-[#ECFEFF] text-[#0E7490]">
                                <i class="fas fa-pills text-sm"></i>
                            </span>
                            <span>Resep Obat</span>
                        </h2>
                    </div>

                    <div class="px-4 sm:px-5 py-4">
                        <div class="overflow-x-auto rounded-xl border border-[#E5E5E5]">
                            <table class="min-w-full text-xs sm:text-sm">
                                <thead class="bg-[#FAFAFA] text-[#6B7280]">
                                    <tr>
                                        <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]">
                                            No
                                        </th>
                                        <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]>
                                            Nama Obat
                                        </th>
                                        <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]>
                                            Dosis
                                        </th>
                                        <th class="px-3 sm:px-4 py-2.5 text-left font-semibold uppercase tracking-wide text-[10px] sm:text-[11px]>
                                            Cara Penggunaan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F3F3F3] bg-white">
                                    @foreach($resepObats as $index => $resep)
                                        <tr class="hover:bg-[#FAFAFA]">
                                            <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 align-top text-[#1D1D1D]">
                                                <span class="font-semibold">{{ $resep->resep_obat }}</span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                {{ $resep->dosis ?? '-' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 align-top text-[#4B4B4B]">
                                                {{ $resep->penggunaan ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Kartu: Kesimpulan Skrining Awal --}}
            <section class="bg-white rounded-2xl border border-[#E9E9E9] shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-3 border-b border-[#F0F0F0] bg-[#FAFAFA]">
                    <h2 class="text-sm sm:text-base font-semibold text-[#1D1D1D] flex items-center gap-2">
                        <span>Kesimpulan Skrining Awal (dari Puskesmas)</span>
                    </h2>
                </div>

                <div class="divide-y divide-[#F3F3F3] text-xs sm:text-sm">
                    {{-- Jumlah risiko sedang --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Jumlah Risiko Sedang
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            {{ $skrining->jumlah_resiko_sedang ?? '0' }}
                        </div>
                    </div>

                    {{-- Jumlah risiko tinggi --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Jumlah Risiko Tinggi
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D] font-medium">
                            {{ $skrining->jumlah_resiko_tinggi ?? '0' }}
                        </div>
                    </div>

                    {{-- Kesimpulan --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Kesimpulan
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3">
                            @php
                                $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                $badgeClass2 = match(strtolower($conclusion)) {
                                    'berisiko', 'beresiko' => 'bg-[#FEE2E2] text-[#DC2626]',
                                    'normal', 'aman'       => 'bg-[#D1FAE5] text-[#059669]',
                                    'waspada', 'menengah'  => 'bg-[#FEF3C7] text-[#D97706]',
                                    default                => 'bg-[#F5F5F5] text-[#6B7280]',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold {{ $badgeClass2 }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                <span>{{ ucfirst($conclusion) }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- Rekomendasi --}}
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                            Rekomendasi Awal
                        </div>
                        <div class="flex-1 px-4 sm:px-5 py-3 text-[#1D1D1D]">
                            {{ $skrining->rekomendasi ?? '-' }}
                        </div>
                    </div>

                    {{-- Catatan --}}
                    @if($skrining->catatan)
                        <div class="flex flex-col sm:flex-row">
                            <div class="sm:w-1/3 bg-[#FAFAFA] px-4 sm:px-5 py-3 text-[11px] font-semibold text-[#7C7C7C]">
                                Catatan dari Puskesmas
                            </div>
                            <div class="flex-1 px-4 sm:px-5 py-3">
                                <div class="rounded-xl border border-[#E5E5E5] bg-[#F9FAFB] px-3 py-2 text-[11px] sm:text-xs text-[#4B4B4B] leading-relaxed">
                                    {{ $skrining->catatan }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Aksi bawah --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                <a href="{{ route('rs.skrining.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full border border-[#E5E5E5] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#4B4B4B] hover:bg-[#F8F8F8] w-full sm:w-auto">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali ke List</span>
                </a>

                <a href="{{ route('rs.skrining.edit', $skrining->id) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E91E8C] px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-[#C2185B] w-full sm:w-auto">
                    <i class="fas fa-edit text-xs"></i>
                    <span>Edit Data Pemeriksaan</span>
                </a>
            </div>

            <footer class="text-center text-[11px] text-[#7C7C7C] py-4">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </div>
    </main>
</div>
@endsection
