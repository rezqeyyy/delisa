<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Detail Pasien</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000CC]">
    <div class="flex min-h-screen">
        <x-dinkes.sidebar />

        <main class="ml-[260px] w-full p-8 space-y-6">
            <!-- Breadcrumb + Aksi -->
            <div class="flex items-center justify-between">
                <div class="text-sm text-[#7C7C7C]">
                    <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">Dashboard</a>
                    <span class="mx-2">/</span>
                    <span class="text-[#1D1D1D] font-medium">Detail Pasien</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dinkes.dashboard') }}"
                        class="px-3 py-1.5 rounded-md border border-[#E0E0E0] hover:bg-white">Kembali</a>
                </div>
            </div>

            <!-- Header Identitas -->
            <section class="bg-white rounded-2xl shadow-md p-5">
                <div class="flex items-center gap-4">
                    @if ($pasien->photo)
                        <img src="{{ Storage::url($pasien->photo) . '?t=' . optional($pasien->updated_at)->timestamp }}"
                            class="w-16 h-16 rounded-full object-cover" alt="{{ $pasien->name }}">
                    @else
                        <div class="w-16 h-16 rounded-full bg-pink-50 ring-2 ring-pink-100 grid place-items-center">
                            <span class="text-[#B9257F] font-bold">{{ strtoupper(substr($pasien->name, 0, 1)) }}</span>
                        </div>
                    @endif

                    <div>
                        <h1 class="text-xl font-semibold">{{ $pasien->name }}</h1>
                        <div class="text-xs text-[#7C7C7C] mt-1">
                            NIK: <span class="tabular-nums">{{ $pasien->nik }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5 text-sm">
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">Umur</div>
                        <div class="font-semibold tabular-nums">
                            {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '—' }}
                        </div>
                    </div>
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">Wilayah</div>
                        <div class="font-semibold">
                            {{ $pasien->PKecamatan ?? '—' }}, {{ $pasien->PKabupaten ?? '—' }},
                            {{ $pasien->PProvinsi ?? '—' }}
                        </div>
                    </div>
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">No. JKN</div>
                        <div class="font-semibold tabular-nums">{{ $pasien->no_jkn ?? '—' }}</div>
                    </div>
                    <div class="bg-[#FAFAFA] rounded-xl p-3">
                        <div class="text-[#7C7C7C]">Kontak</div>
                        <div class="font-semibold">{{ $pasien->phone ?? '—' }}</div>
                    </div>
                </div>
            </section>

            <!-- Ringkasan Status -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-md p-5">
                    <h2 class="font-semibold mb-3">Status Skrining Terbaru</h2>
                    @if ($skrining)
                        @php
                            $risk =
                                ($skrining->jumlah_resiko_tinggi ?? 0) > 0
                                    ? 'Tinggi'
                                    : (($skrining->jumlah_resiko_sedang ?? 0) > 0
                                        ? 'Sedang'
                                        : 'Normal');
                            $riskColor = ['Normal' => '#39E93F', 'Sedang' => '#E2D30D', 'Tinggi' => '#E20D0D'][$risk];
                        @endphp
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-[#7C7C7C]">Tanggal</div>
                                <div class="font-semibold">
                                    {{ optional($skrining->created_at)->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-sm text-[#7C7C7C] mb-1" >Status</div>
                                <span class="px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $skrining->checked_status ? '#39E93F33' : '#E20D0D33' }};
                                         color: {{ $skrining->checked_status ? '#39E93F' : '#E20D0D' }};">
                                    {{ $skrining->checked_status ? 'Hadir' : 'Mangkir' }}
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="text-sm text-[#7C7C7C] mb-1">Risiko</div>
                                <span class="px-2.5 py-1 rounded-full text-sm"
                                    style="background: {{ $riskColor }}33; color: {{ $riskColor }};">{{ $risk }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-[#7C7C7C]">Belum ada data skrining.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl shadow-md p-5">
                    <h2 class="font-semibold mb-3">Ringkasan GPA</h2>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">G</div>
                            <div class="text-xl font-bold tabular-nums">{{ $gpa->total_kehamilan ?? '0' }}</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">P</div>
                            <div class="text-xl font-bold tabular-nums">{{ $gpa->total_persalinan ?? '0' }}</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-xs text-[#7C7C7C]">A</div>
                            <div class="text-xl font-bold tabular-nums">{{ $gpa->total_abortus ?? '0' }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-5">
                    <h2 class="font-semibold mb-3">Ringkasan KF</h2>
                    @php
                        $byKe = collect($kfSummary)->keyBy('ke');
                    @endphp
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ([1, 2, 3, 4] as $ke)
                            <div class="bg-[#FAFAFA] rounded-xl p-3 text-center">
                                <div class="text-xs text-[#7C7C7C]">KF{{ $ke }}</div>
                                <div class="text-lg font-semibold tabular-nums">{{ $byKe[$ke]->total ?? 0 }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                        <div>Sehat: <span class="font-semibold tabular-nums">{{ $kfPantauan['Sehat'] ?? 0 }}</span>
                        </div>
                        <div>Dirujuk: <span class="font-semibold tabular-nums">{{ $kfPantauan['Dirujuk'] ?? 0 }}</span>
                        </div>
                        <div>Meninggal: <span
                                class="font-semibold tabular-nums">{{ $kfPantauan['Meninggal'] ?? 0 }}</span></div>
                    </div>
                </div>
            </section>

            <!-- Kondisi Kesehatan Terbaru -->
            <section class="bg-white rounded-2xl shadow-md p-5">
                <h2 class="font-semibold mb-4">Kondisi Kesehatan Terbaru</h2>
                @if ($kondisi)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tinggi Badan</div>
                            <div class="font-semibold tabular-nums">{{ $kondisi->tinggi_badan }} cm</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Berat Badan</div>
                            <div class="font-semibold tabular-nums">{{ $kondisi->berat_badan_saat_hamil }} kg</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">IMT</div>
                            <div class="font-semibold tabular-nums">{{ $kondisi->imt }}</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Usia Kehamilan</div>
                            <div class="font-semibold tabular-nums">{{ $kondisi->usia_kehamilan }} Minggu</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">HPHT</div>
                            <div class="font-semibold">{{ \Carbon\Carbon::parse($kondisi->hpht)->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">TPP</div>
                            <div class="font-semibold">
                                {{ \Carbon\Carbon::parse($kondisi->tanggal_perkiraan_persalinan)->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Protein Urine</div>
                            <div class="font-semibold">{{ $kondisi->pemeriksaan_protein_urine }}</div>
                        </div>
                        <div class="bg-[#FAFAFA] rounded-xl p-3">
                            <div class="text-[#7C7C7C]">Tanggal Skrining</div>
                            <div class="font-semibold">
                                {{ \Carbon\Carbon::parse($kondisi->tanggal_skrining)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-[#7C7C7C]">Belum ada catatan kondisi kesehatan.</p>
                @endif
            </section>

            <!-- Riwayat Kehamilan -->
            <section class="bg-white rounded-2xl shadow-md p-5">
                <h2 class="font-semibold mb-4">Riwayat Kehamilan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[#7C7C7C] border-b border-[#E5E5E5]">
                            <tr>
                                <th class="py-2 text-left">Tahun</th>
                                <th class="text-left">Kehamilan</th>
                                <th class="text-left">Pengalaman</th>
                                <th class="text-left">Jenis Persalinan</th>
                                <th class="text-left">Penolong</th>
                                <th class="text-left">Komplikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0F0F0]">
                            @forelse($riwayatKehamilan as $r)
                                <tr>
                                    <td class="py-2 tabular-nums">{{ $r->tahun_kehamilan }}</td>
                                    <td class="tabular-nums">{{ $r->kehamilan }}</td>
                                    <td>{{ $r->pengalaman_kehamilan }}</td>
                                    <td>{{ $r->jenis_persalinan ?? '—' }}</td>
                                    <td>{{ $r->penolong_persalinan ?? '—' }}</td>
                                    <td>{{ $r->komplikasi ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-3 text-[#7C7C7C]">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Rujukan RS -->
            <section class="bg-white rounded-2xl shadow-md p-5">
                <h2 class="font-semibold mb-4">Riwayat Rujukan RS</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @forelse($rujukan as $r)
                        <div class="rounded-xl border border-[#EFEFEF] p-4">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">{{ $r->rs_nama ?? 'Rumah Sakit' }}</div>
                                <div class="text-xs text-[#7C7C7C]">
                                    {{ optional($r->created_at)->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-sm mt-1">Status:
                                <span class="px-2 py-0.5 rounded-full text-xs"
                                    style="background: {{ $r->done_status ? '#39E93F33' : '#FFF0E6' }};
                                         color: {{ $r->done_status ? '#39E93F' : '#B86700' }};">
                                    {{ $r->done_status ? 'Selesai' : 'Proses' }}
                                </span>
                            </div>
                            <div class="text-sm mt-1 text-[#7C7C7C]">
                                Catatan: {{ $r->catatan_rujukan ?? '—' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#7C7C7C]">Belum ada riwayat rujukan.</p>
                    @endforelse
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
