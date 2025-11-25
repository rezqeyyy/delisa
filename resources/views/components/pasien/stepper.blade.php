@props([
    'items' => [
        'Data Diri Pasien',
        'Riwayat Kehamilan & Persalinan',
        'Kondisi Kesehatan Pasien',
        'Riwayat Penyakit Pasien',
        'Riwayat Penyakit Keluarga',
        'Pre Eklampsia',
    ],
    'current' => 1,
    'urls' => [
        route('pasien.data-diri'),
        route('pasien.riwayat-kehamilan-gpa'),
        route('pasien.kondisi-kesehatan-pasien'),
        route('pasien.riwayat-penyakit-pasien'),
        route('pasien.riwayat-penyakit-keluarga'),
        route('pasien.preeklampsia'),
    ],
])

<div class="mt-4 w-full px-1 flex items-center gap-1 sm:gap-2 md:gap-4 lg:gap-6 flex-nowrap overflow-x-auto scroll-smooth snap-x snap-mandatory">
    @foreach ($items as $i => $label)
        @php
            $url = isset($urls[$i]) ? $urls[$i] : '#';
            $isClickable = $url !== '#';
        @endphp
        <div class="flex flex-col items-center shrink-0 min-w-0 snap-start">
            <a href="{{ $url }}" class="{{ $isClickable ? 'cursor-pointer' : 'cursor-default' }}">
                <div class="w-8 h-8 rounded-full {{ ($i + 1) === $current ? 'bg-[#B9257F] text-white' : 'bg-[#E5E5E5] text-[#1D1D1D]' }} flex items-center justify-center text-1xl font-semibold transition-colors duration-200 {{ $isClickable && ($i + 1) !== $current ? 'hover:bg-[#D9A3C6] hover:text-white' : '' }}">
                    {{ $i + 1 }}
                </div>
            </a>
            <a href="{{ $url }}" class="{{ $isClickable ? 'cursor-pointer' : 'cursor-default' }}">
                <div class="mt-3 text-sm text-[#1D1D1D] text-center leading-tight hidden md:block {{ $isClickable ? 'hover:text-[#B9257F]' : '' }} transition-colors duration-200">
                    {{ $label }}
                </div>
            </a>
        </div>

        @if ($i < count($items) - 1)
            <div class="h-1 md:h-2 flex-1 basis-0 min-w-0 bg-[#E5E5E5] rounded-full"></div>
        @endif
    @endforeach
</div>