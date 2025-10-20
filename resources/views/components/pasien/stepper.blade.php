@props([
    'items' => [
        'Data Diri Pasien',
        'Riwayat Kehamilan & Persalinan',
        'Kondisi Kesehatan Ibu',
        'Kesehatan Pasien',
        'Riwayat Penyakit Keluarga',
        'Pre Eklampsia',
    ],
    'current' => 1,
])

<div class="mt-4 w-full px-1 flex items-center gap-2 md:gap-4 lg:gap-6 flex-nowrap">
    @foreach ($items as $i => $label)
        <div class="flex flex-col items-center shrink-0 min-w-0">
            <div class="w-8 h-8 rounded-full {{ ($i + 1) === $current ? 'bg-[#B9257F] text-white' : 'bg-[#E5E5E5] text-[#1D1D1D]' }} flex items-center justify-center text-1xl font-semibold">
                {{ $i + 1 }}
            </div>
            <div class="mt-3 text-sm text-[#1D1D1D] text-center leading-tight hidden md:block">
                {{ $label }}
            </div>
        </div>

        @if ($i < count($items) - 1)
            <div class="h-1 md:h-2 flex-1 basis-0 min-w-[12px] sm:min-w-[20px] md:min-w-[40px] bg-[#E5E5E5] rounded-full"></div>
        @endif
    @endforeach
</div>