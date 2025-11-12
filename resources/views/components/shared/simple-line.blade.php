{{-- resources/views/components/shared/simple-line.blade.php --}}
@props([
    'labels' => [],     // <— TANGKAP props yang dikirim dari view
    'series' => [],     // <— TANGKAP props yang dikirim dari view
    'class'  => '',
])

{{-- Placeholder sederhana dulu; nanti bisa diganti renderer SVG/JS --}}
<div {{ $attributes->merge([
    'class' => "w-full h-px bg-[#E5E5E5] $class"
]) }}></div>
