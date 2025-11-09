<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Analytics Variabel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex min-h-screen">
        <x-dinkes.sidebar />

        <main class="w-full lg:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Breadcrumb + back -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs sm:text-sm text-[#7C7C7C]">
                    <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">Dashboard</a>
                    <span class="mx-1 sm:mx-2">/</span>
                    <a href="{{ route('dinkes.analytics', request()->query()) }}" class="hover:underline">Analytics</a>
                    <span class="mx-1 sm:mx-2">/</span>
                    <span class="text-[#1D1D1D] font-medium">Variabel: {{ $meta['label'] ?? $key }}</span>
                </div>
                <a href="{{ route('dinkes.analytics', request()->query()) }}"
                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-md border border-[#E0E0E0] bg-white hover:bg-[#FAFAFA] text-sm">
                    Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
                <!-- Kiri -->
                <div class="lg:col-span-7 bg-white rounded-2xl p-4 sm:p-5 shadow">
                    <h3 class="font-semibold mb-3">Distribusi & outcome per bucket</h3>

                    @if (($meta['type'] ?? '') === 'num')
                        @if (count($dist ?? []))
                            <div class="space-y-3">
                                @foreach ($dist as $b)
                                    <div>
                                        <div class="flex items-center justify-between text-xs sm:text-sm">
                                            <span class="truncate"
                                                title="{{ $b['label'] }}">{{ $b['label'] }}</span>
                                            <span class="tabular-nums whitespace-nowrap">{{ $b['rate'] }}%
                                                (n={{ $b['n'] }})</span>
                                        </div>
                                        <div class="h-3 bg-[#F1F1F1] rounded">
                                            <div class="h-3 bg-[#B9257F] rounded" style="width: {{ $b['rate'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-[#7C7C7C]">Belum ada data.</p>
                        @endif
                    @else
                        <p class="text-sm text-[#7C7C7C]">Variabel kategori — lihat tabel 2×2 di panel kanan.</p>
                    @endif
                </div>

                <!-- Kanan -->
                <div class="lg:col-span-5 bg-white rounded-2xl p-4 sm:p-5 shadow">
                    <h3 class="font-semibold mb-3">Ringkasan</h3>

                    @if (($meta['type'] ?? '') === 'num')
                        <ul class="text-sm space-y-1">
                            <li>Populasi (sesuai filter): <span
                                    class="tabular-nums">{{ $summary['n_total'] ?? 0 }}</span></li>
                            <li>Outcome rate keseluruhan: <span
                                    class="tabular-nums">{{ $summary['overall_rate'] ?? 0 }}%</span></li>
                            @if ($summary['best_bucket'] ?? null)
                                <li>Bucket tertinggi: <b>{{ $summary['best_bucket'] }}</b>
                                    ({{ $summary['best_rate'] }}%)</li>
                            @endif
                        </ul>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-[480px] w-full text-sm">
                                <thead class="text-[#7C7C7C] border-b">
                                    <tr>
                                        <th class="py-2"></th>
                                        <th class="text-left">Outcome=1</th>
                                        <th class="text-left">Outcome=0</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr>
                                        <td class="py-2">Positif</td>
                                        <td class="tabular-nums">{{ $table2x2['a'] }}</td>
                                        <td class="tabular-nums">{{ $table2x2['b'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">Negatif</td>
                                        <td class="tabular-nums">{{ $table2x2['c'] }}</td>
                                        <td class="tabular-nums">{{ $table2x2['d'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-sm mt-3">
                            @if ($table2x2['or'])
                                OR = <b class="tabular-nums">{{ round($table2x2['or'], 2) }}</b>
                                (95% CI: <span
                                    class="tabular-nums">{{ round($table2x2['lo'], 2) }}–{{ round($table2x2['hi'], 2) }}</span>)
                            @else
                                OR tidak terdefinisi (beberapa sel nol).
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>

</html>
