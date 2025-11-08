{{-- resources/views/components/dinkes/analytics-explorer.blade.php --}}
@props([
    'filters' => [],
    'top' => [],
    'total' => 0,
    'rateY' => 0,
])

<section class="bg-white rounded-2xl p-5 shadow-md relative">
    <div class="flex items-center gap-3">
        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-[#F5F5F5]">
            <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}" class="w-3.5 h-3.5" alt="">
        </span>
        <h2 class="font-semibold">Analytic Explorer</h2>

        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-[#F5F5F5] text-[#4B4B4B]">
            Outcome:
            {{ ['pe' => 'Pre-eklampsia', 'dirujuk' => 'Dirujuk', 'meninggal' => 'Meninggal'][$filters['outcome'] ?? 'pe'] }}
        </span>

        <button id="btnAxFilter" type="button" class="ml-auto border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">
            Advanced Filters
        </button>
    </div>

    {{-- Panel filter --}}
    <div id="axFilterPanel"
        class="hidden absolute right-5 top-16 z-20 w-[680px] bg-white border border-[#E5E7EB] rounded-2xl shadow-xl p-4">
        <form method="GET" action="{{ route('dinkes.analytics') }}" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">Periode</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from" value="{{ request('from') }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                    <input type="date" name="to" value="{{ request('to') }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">Wilayah (Kecamatan)</label>
                <input type="text" name="kec" value="{{ request('kec') }}" placeholder="Cth: Pancoran Mas"
                    class="w-full border rounded-md px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">Umur (tahun)</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="age_min" value="{{ request('age_min', 10) }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                    <input type="number" name="age_max" value="{{ request('age_max', 60) }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">IMT</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="imt_min" value="{{ request('imt_min', 10) }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                    <input type="number" name="imt_max" value="{{ request('imt_max', 60) }}"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">Status Hadir</label>
                <select name="hadir" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="hadir" @selected(request('hadir') === 'hadir')>Hadir</option>
                    <option value="mangkir" @selected(request('hadir') === 'mangkir')>Mangkir</option>
                </select>
            </div>

            <div>
                <label class="block text-xs mb-1 text-[#7C7C7C]">Outcome</label>
                <select name="outcome" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="pe" @selected(request('outcome', 'pe') === 'pe')>Pre-eklampsia (sedang/tinggi)</option>
                    <option value="dirujuk" @selected(request('outcome') === 'dirujuk')>Dirujuk</option>
                    <option value="meninggal" @selected(request('outcome') === 'meninggal')>Meninggal</option>
                </select>
            </div>

            <div class="col-span-2 flex items-center justify-end gap-2 pt-2 border-t">
                <a href="{{ route('dinkes.analytics') }}"
                    class="border border-[#CAC7C7] rounded-md px-3 py-2 text-sm">Reset</a>
                <button class="bg-[#B9257F] text-white rounded-md px-4 py-2 text-sm">Terapkan</button>
            </div>
        </form>
    </div>

    {{-- Ringkasan & Top correlations --}}
    <div class="mt-4 grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 bg-[#FAFAFA] rounded-xl p-4">
            <div class="text-sm text-[#7C7C7C]">Populasi (sesuai filter)</div>
            <div class="text-3xl font-bold tabular-nums">{{ $total }}</div>
            <div class="mt-2 text-sm">Outcome rate: <span
                    class="font-semibold tabular-nums">{{ $rateY }}%</span></div>
        </div>

        <div class="col-span-12 lg:col-span-8">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-medium">Top-5 Korelasi</h3>
                <span class="text-xs text-[#7C7C7C]">Klik item untuk detail</span>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                @forelse($top as $it)
                    @php
                        // Normalisasi key agar cocok dengan showVariable():
                        // 'protein_urine' -> 'prot' (alias yang dipakai di controller)
                        $key = ($it['key'] ?? '') === 'protein_urine' ? 'prot' : $it['key'] ?? '';
                        // Sertakan seluruh query agar filter (tanggal, kec, dsb.) tetap aktif di halaman detail
                        $qs = array_merge(request()->query(), ['key' => $key]);
                    @endphp

                    <a href="{{ route('dinkes.analytics.var', $qs) }}"
                        class="rounded-xl border border-[#E9E9E9] p-3 hover:bg-[#F9F9F9]">

                        <div class="text-sm">{{ $it['label'] }}</div>
                        @if (($it['type'] ?? '') === 'numeric')
                            <div class="text-xl font-bold mt-1">r = {{ $it['r'] }}</div>
                            <div class="text-xs text-[#7C7C7C]">Point-biserial</div>
                        @else
                            <div class="text-xl font-bold mt-1">
                                OR = {{ isset($it['or']) && $it['or'] > 0 ? $it['or'] : '—' }}
                            </div>
                            <div class="text-xs text-[#7C7C7C]">Kategori (+) vs outcome</div>
                        @endif

                    </a>
                @empty
                    <div class="text-sm text-[#7C7C7C]">Belum cukup data untuk analisis.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
