<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DINKES – Analytics Variabel</title>
  @vite(['resources/css/app.css','resources/js/app.js','resources/js/dropdown.js'])
</head>
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
<div class="flex min-h-screen">
  <x-dinkes.sidebar />

  <main class="ml-[260px] w-full p-8 space-y-6">
    <div class="flex items-center justify-between mb-5">
      <div class="text-sm text-[#7C7C7C]">
        <a href="{{ route('dinkes.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('dinkes.analytics', request()->query()) }}" class="hover:underline">Analytics</a>
        <span class="mx-2">/</span>
        <span class="text-[#1D1D1D] font-medium">Variabel: {{ $meta['label'] ?? $key }}</span>
      </div>
      <a href="{{ route('dinkes.analytics', request()->query()) }}"
         class="px-3 py-1.5 rounded-md border border-[#E0E0E0] hover:bg-white">Kembali</a>
    </div>

    <div class="grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-7 bg-white rounded-2xl p-5 shadow">
        <h3 class="font-semibold mb-3">Distribusi & outcome per bucket</h3>
        @if(($meta['type'] ?? '')==='num')
          @if(count($dist ?? []))
            <div class="space-y-3">
              @foreach($dist as $b)
                <div>
                  <div class="flex items-center justify-between text-sm">
                    <span>{{ $b['label'] }}</span>
                    <span class="tabular-nums">{{ $b['rate'] }}% (n={{ $b['n'] }})</span>
                  </div>
                  <div class="h-3 bg-[#F1F1F1] rounded">
                    <div class="h-3 bg-[#B9257F] rounded" style="width: {{ $b['rate'] }}%"></div>
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

      <div class="col-span-12 lg:col-span-5 bg-white rounded-2xl p-5 shadow">
        <h3 class="font-semibold mb-3">Ringkasan</h3>
        @if(($meta['type'] ?? '')==='num')
          <ul class="text-sm space-y-1">
            <li>Populasi (sesuai filter): <span class="tabular-nums">{{ $summary['n_total'] ?? 0 }}</span></li>
            <li>Outcome rate keseluruhan: <span class="tabular-nums">{{ $summary['overall_rate'] ?? 0 }}%</span></li>
            @if(($summary['best_bucket'] ?? null))
              <li>Bucket tertinggi: <b>{{ $summary['best_bucket'] }}</b> ({{ $summary['best_rate'] }}%)</li>
            @endif
          </ul>
        @else
          <table class="w-full text-sm">
            <thead><tr><th></th><th>Outcome=1</th><th>Outcome=0</th></tr></thead>
            <tbody class="divide-y">
              <tr><td>Positif</td><td class="tabular-nums">{{ $table2x2['a'] }}</td><td class="tabular-nums">{{ $table2x2['b'] }}</td></tr>
              <tr><td>Negatif</td><td class="tabular-nums">{{ $table2x2['c'] }}</td><td class="tabular-nums">{{ $table2x2['d'] }}</td></tr>
            </tbody>
          </table>
          <div class="text-sm mt-3">
            @if($table2x2['or'])
              OR = <b class="tabular-nums">{{ round($table2x2['or'],2) }}</b>
              (95% CI: <span class="tabular-nums">{{ round($table2x2['lo'],2) }}–{{ round($table2x2['hi'],2) }}</span>)
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
