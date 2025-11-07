<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Analytics</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/analytics-explorer.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex min-h-screen">
        <x-dinkes.sidebar />

        <main class="ml-[260px] w-full p-8 space-y-6">
            <!-- Breadcrumb + Aksi -->
            <div class="flex items-center justify-between">
                @if (request('kec'))
                    <div class="text-sm">
                        <span class="px-2 py-1 rounded-full bg-[#F5F5F5]">
                            Kec: <b>{{ request('kec') }}</b>
                            <a class="ml-2 text-[#B9257F]"
                                href="{{ route('dinkes.analytics',collect(request()->query())->except('kec')->all()) }}">×
                                hapus</a>
                        </span>
                    </div>
                @endif

                <div class="text-sm text-[#7C7C7C]">
                    <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">Dashboard</a>
                    <span class="mx-2">/</span>
                    <span class="text-[#1D1D1D] font-medium">Analytics</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dinkes.analytics.export', request()->query()) }}"
                        class="px-3 py-1.5 rounded-md bg-[#B9257F] text-white hover:opacity-95">
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Ringkasan -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-md p-5">
                    <div class="text-[#7C7C7C] text-sm">Total baris (sesudah filter)</div>
                    <div class="text-2xl font-semibold tabular-nums mt-1">{{ $total }}</div>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-5">
                    <div class="text-[#7C7C7C] text-sm">Outcome rate</div>
                    <div class="text-2xl font-semibold mt-1">{{ $rateY }}%</div>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-5">
                    <div class="text-[#7C7C7C] text-sm">Outcome dianalisis</div>
                    <div class="text-base mt-1">
                        @php
                            $labelOutcome = match ($filters['outcome']) {
                                'dirujuk' => 'Dirujuk RS',
                                'meninggal' => 'Meninggal',
                                default => 'Pre-eklampsia (resiko sedang/tinggi)',
                            };
                        @endphp
                        <span class="font-medium">{{ $labelOutcome }}</span>
                    </div>
                </div>
            </section>

            <!-- Explorer -->
            <x-dinkes.analytics-explorer :filters="$filters" :top="$top" :total="$total" :rateY="$rateY" />
            {{-- TREN BULANAN --}}
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Tren 12 Bulan</h2>
                </div>
                @php
                    $months = $trend->map(fn($r) => \Carbon\Carbon::parse($r->bulan)->isoFormat('MMM YY'))->toArray();
                    $seriesTotal = $trend->pluck('total')->toArray();
                    $seriesPE = $trend->pluck('y_pe')->toArray();
                    $seriesRujuk = $trend->pluck('y_rujuk')->toArray();
                @endphp
                <div class="-mx-2">
                    {{-- pakai gaya SVG bar seperti chart kamu di halaman lain --}}
                    <x-shared.simple-line :labels="$months" :series="[
                        ['name' => 'Total', 'data' => $seriesTotal, 'color' => '#1F2937'],
                        ['name' => 'PE', 'data' => $seriesPE, 'color' => '#B9257F'],
                        ['name' => 'Dirujuk', 'data' => $seriesRujuk, 'color' => '#F59E0B'],
                    ]" />
                </div>
            </section>

            {{-- PETA KECAMATAN (Choropleth versi kartu) --}}
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2 class="font-semibold">Peta Kecamatan (berdasar rate %)</h2>
                    </div>
                    <span class="text-xs text-[#7C7C7C]">Klik untuk filter kecamatan</span>
                </div>

                @php
                    $maxRate = max(1, $byKec->max('rate'));
                @endphp
                <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($byKec as $k)
                        @php
                            $alpha = 0.2 + 0.8 * ($k->rate / $maxRate);
                            $bg = "rgba(185,37,127,{$alpha})"; // #B9257F
                            // siapkan query toggle: klik lagi = hapus filter
                            $qs = request()->query();
                            if (request('kec') === ($k->kec ?? null)) {
                                unset($qs['kec']);
                            } else {
                                $qs['kec'] = $k->kec;
                            }
                            $active = request('kec') === ($k->kec ?? null);
                        @endphp

                        <a href="{{ route('dinkes.analytics', $qs) }}"
                            class="rounded-xl border p-3 hover:shadow {{ $active ? 'ring-2 ring-[#B9257F] border-[#B9257F] bg-[#FFF5FB]' : 'border-[#E9E9E9] bg-white' }}">
                            <div class="font-medium">{{ $k->kec ?? '—' }}</div>
                            <div class="text-xs text-[#6B7280]">n={{ $k->n }}</div>
                            <div class="mt-1 text-lg font-bold tabular-nums">{{ $k->rate }}%</div>
                        </a>

                    @empty
                        <p class="text-sm text-[#7C7C7C]">Belum ada data.</p>
                    @endforelse
                </div>
            </section>

            {{-- DATA QUALITY --}}
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Kualitas Data</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-3">
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-sm text-[#7C7C7C]">Tanpa IMT</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $dq['missing_imt'] }}%</div>
                    </div>
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-sm text-[#7C7C7C]">Tanpa Tekanan Darah</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $dq['missing_bp'] }}%</div>
                    </div>
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-sm text-[#7C7C7C]">Tanpa Protein Urine</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $dq['missing_prot'] }}%</div>
                    </div>
                </div>
            </section>

            {{-- COHORT COMPARE (A: filter sekarang vs B: semua kecamatan) --}}
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Cohort Compare</h2>
                </div>
                <div class="grid md:grid-cols-4 gap-3 items-end">
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-xs text-[#7C7C7C] mb-1">A (Filter sekarang)</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $cohort['A_rate'] }}%</div>
                    </div>
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-xs text-[#7C7C7C] mb-1">B (Semua kecamatan)</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $cohort['B_rate'] }}%</div>
                    </div>
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-xs text-[#7C7C7C] mb-1">Risk Difference</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $cohort['rd'] }} pp</div>
                    </div>
                    <div class="rounded-xl bg-[#FAFAFA] p-4">
                        <div class="text-xs text-[#7C7C7C] mb-1">Risk Ratio</div>
                        <div class="text-2xl font-bold tabular-nums">{{ $cohort['rr'] ?? '—' }}</div>
                    </div>
                </div>
            </section>


        </main>
    </div>
</body>

</html>
