<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Analytics</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/analytics-explorer.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex min-h-screen">
        <x-dinkes.sidebar />

        <main class="w-full lg:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Breadcrumb + Aksi -->
            <div class="space-y-3">
                <!-- Baris 1: breadcrumb kiri, tombol kanan -->
                <div class="flex items-center justify-between">
                    <nav class="text-xs sm:text-sm text-[#7C7C7C]">
                        <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">Dashboard</a>
                        <span class="mx-1 sm:mx-2">/</span>
                        <span class="text-[#1D1D1D] font-medium">Analytics</span>
                    </nav>
                    <a href="{{ route('dinkes.analytics.export', request()->query()) }}"
                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-[#B9257F] text-white hover:opacity-95 text-sm">
                        Export CSV
                    </a>
                </div>

                <!-- Baris 2: chip filter kecamatan (kalau ada) -->
                @if (request('kec'))
                    <div class="text-xs sm:text-sm">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-[#F5F5F5]">
                            <span class="hidden xs:inline">Kecamatan:</span> <b>{{ request('kec') }}</b>
                            <a class="ml-1 text-[#B9257F]"
                                href="{{ route('dinkes.analytics',collect(request()->query())->except('kec')->all()) }}">×
                                hapus</a>
                        </span>
                    </div>
                @endif
            </div>


            <!-- Ringkasan -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <div class="text-[#7C7C7C] text-sm">Total baris (sesudah filter)</div>
                    <div class="text-2xl font-semibold tabular-nums mt-1">{{ $total }}</div>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <div class="text-[#7C7C7C] text-sm">Outcome rate</div>
                    <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $rateY }}%</div>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5">
                    <div class="text-[#7C7C7C] text-sm">Outcome dianalisis</div>
                    <div class="text-sm sm:text-base mt-1">
                        @php
                            $labelOutcome = match ($filters['outcome']) {
                                'dirujuk' => 'Dirujuk RS',
                                'meninggal' => 'Meninggal',
                                default => 'Pre-eklampsia (risiko sedang/tinggi)',
                            };
                        @endphp
                        <span class="font-medium">{{ $labelOutcome }}</span>
                    </div>
                </div>
            </section>

            <!-- Explorer -->
            <x-dinkes.analytics-explorer :filters="$filters" :top="$top" :total="$total" :rateY="$rateY" />

            {{-- TREN BULANAN --}}
            <section class="bg-white rounded-2xl p-4 sm:p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Tren 12 Bulan</h2>
                </div>
                @php
                    $months = $trend->map(fn($r) => \Carbon\Carbon::parse($r->bulan)->isoFormat('MMM YY'))->toArray();
                    $seriesTotal = $trend->pluck('total')->toArray();
                    $seriesPE = $trend->pluck('y_pe')->toArray();
                    $seriesRujuk = $trend->pluck('y_rujuk')->toArray();
                @endphp
                <div class="-mx-2 sm:mx-0 overflow-x-auto">
                    <div class="min-w-[720px]">
                        <x-shared.simple-line :labels="$months" :series="[
                            ['name' => 'Total', 'data' => $seriesTotal, 'color' => '#1F2937'],
                            ['name' => 'PE', 'data' => $seriesPE, 'color' => '#B9257F'],
                            ['name' => 'Dirujuk', 'data' => $seriesRujuk, 'color' => '#F59E0B'],
                        ]" />
                    </div>
                </div>
            </section>

            {{-- PETA KECAMATAN (Choropleth versi kartu) --}}
            <section class="bg-white rounded-2xl p-4 sm:p-5 shadow-md">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
                    <h2 class="font-semibold">Peta Kecamatan (berdasar rate %)</h2>
                    <span class="text-xs text-[#7C7C7C]">Ketuk kartu untuk toggle filter kecamatan</span>
                </div>

                @php $maxRate = max(1, $byKec->max('rate')); @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($byKec as $k)
                        @php
                            $qs = request()->query();
                            $active = request('kec') === ($k->kec ?? null);
                            if ($active) {
                                unset($qs['kec']);
                            } else {
                                $qs['kec'] = $k->kec;
                            }
                        @endphp

                        <a href="{{ route('dinkes.analytics', $qs) }}"
                            class="rounded-xl border p-3 transition hover:shadow {{ $active ? 'ring-2 ring-[#B9257F] border-[#B9257F] bg-[#FFF5FB]' : 'border-[#E9E9E9] bg-white' }}">
                            <div class="font-medium truncate">{{ $k->kec ?? '—' }}</div>
                            <div class="text-xs text-[#6B7280]">n=<span class="tabular-nums">{{ $k->n }}</span>
                            </div>
                            <div class="mt-1 text-lg font-bold tabular-nums">{{ $k->rate }}%</div>
                        </a>
                    @empty
                        <p class="text-sm text-[#7C7C7C]">Belum ada data.</p>
                    @endforelse
                </div>
            </section>

            {{-- DATA QUALITY --}}
            <section class="bg-white rounded-2xl p-4 sm:p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Kualitas Data</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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

            {{-- COHORT COMPARE --}}
            <section class="bg-white rounded-2xl p-4 sm:p-5 shadow-md">
                <div class="flex items-center gap-2 mb-3">
                    <h2 class="font-semibold">Cohort Compare</h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-stretch">
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
