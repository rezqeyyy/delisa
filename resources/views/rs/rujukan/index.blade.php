<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Sakit — Penerimaan Rujukan</title>
    @vite(['resources/css/app.css','resources/js/app.js','resources/js/dropdown.js','resources/js/rs/sidebar-toggle.js'])

</head>
<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
<div class="flex min-h-screen" x-data="{ openSidebar: false }">
    <x-rs.sidebar />
    <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
        <section class="space-y-4">
            <h1 class="text-2xl font-semibold text-[#1D1D1D]">Penerimaan Rujukan</h1>
            <div class="bg-white rounded-2xl border border-[#E9E9E9] p-2 sm:p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 grid place-items-center rounded-full bg-[#F5F5F5]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-[#1D1D1D]" fill="currentColor"><path d="M6 2a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V4a2 2 0 0 0-2-2H6Zm2 5h8v2H8V7Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-semibold text-[#1D1D1D]">Rujukan dari Puskesmas</h2>
                            <p class="text-xs text-[#7C7C7C]">Menampilkan skrining pasien yang diajukan puskesmas untuk rumah sakit ini</p>
                        </div>
                    </div>
                </div>
                <br>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[#7C7C7C]">
                        <tr class="text-left">
                            <th class="px-3 py-2">NIK Pasien</th>
                            <th class="px-3 py-2">Nama Pasien</th>
                            <th class="px-3 py-2">Tanggal Skrining</th>
                            <th class="px-3 py-2">Alamat</th>
                            <th class="px-3 py-2">No Telp</th>
                            <th class="px-3 py-2">Resiko</th>
                            <th class="px-3 py-2">Aksi</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E9E9E9]">
                        @forelse($skrinings as $r)
                            @php($skr = $r->skrining)
                            @php($pas = optional($skr)->pasien)
                            @php($usr = optional($pas)->user)
                            @php($raw = strtolower(trim($skr->kesimpulan ?? $skr->status_pre_eklampsia ?? '')))
                            @php($isHigh = ($skr->jumlah_resiko_tinggi ?? 0) > 0 || in_array($raw, ['berisiko','beresiko','risiko tinggi','tinggi']))
                            @php($isWarn = ($skr->jumlah_resiko_sedang ?? 0) > 0 || in_array($raw, ['waspada','menengah','sedang','risiko sedang']))
                            @php($display = $isHigh ? 'Beresiko' : ($isWarn ? 'Waspada' : 'Aman'))
                            <tr>
                                <td class="px-3 py-3 font-medium text-[#1D1D1D]">{{ $pas->nik ?? '-' }}</td>
                                <td class="px-3 py-3">{{ $usr->name ?? '-' }}</td>
                                <td class="px-3 py-3">{{ optional($skr->created_at)->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-3 py-3">{{ $pas->PKecamatan ?? $pas->PWilayah ?? '-' }}</td>
                                <td class="px-3 py-3">{{ $usr->phone ?? $pas->no_telepon ?? '-' }}</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $isHigh ? 'bg-[#E20D0D] text-white' : ($isWarn ? 'bg-[#FFC400] text-[#1D1D1D]' : 'bg-[#39E93F] text-white') }}">
                                        {{ $display }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        @if(!$r->done_status)
                                            <form method="POST" action="{{ route('rs.penerimaan-rujukan.accept', $skr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-4 py-1 rounded-full bg-[#2EDB58] text-white text-xs">Terima</button>
                                            </form>
                                            <form method="POST" action="{{ route('rs.penerimaan-rujukan.reject', $skr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-4 py-1 rounded-full bg-[#E53935] text-white text-xs">Tolak</button>
                                            </form>
                                        @else
                                            <span class="px-4 py-1 rounded-full bg-[#B3E5FC] text-[#1D1D1D] text-xs">Sudah diterima</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-[#7C7C7C]">Belum ada rujukan dari Puskesmas untuk RS ini.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex items-center justify-between text-sm text-[#7C7C7C]">
                    <div>Halaman {{ $skrinings->currentPage() }} dari {{ $skrinings->lastPage() }}</div>
                    <div>{{ $skrinings->onEachSide(1)->links() }}</div>
                </div>
            </div>
        </section>
        <footer class="text-center text-xs text-[#7C7C7C] py-6">© 2025 Dinas Kesehatan Kota Depok — DeLISA</footer>
    </main>
</div>
</body>
</html>