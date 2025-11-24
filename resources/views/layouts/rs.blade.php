<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rumah Sakit — DeLISA')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/rs/sidebar-toggle.js',
        'resources/js/rs/skrinning-edit.js'
    ])

    {{-- Slot untuk style tambahan per-halaman --}}
    @stack('styles')
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    {{-- 
        Layout ini sengaja dibuat simpel:
        - Hanya kerangka HTML + Vite assets
        - Isi utama diambil dari @section('content') di masing-masing view
        - Sidebar / header boleh ditulis di view, atau nanti bisa dipindah ke layout/komponen
    --}}
    @yield('content')

    {{-- Slot untuk script tambahan per-halaman --}}
    @stack('scripts')
</body>

</html>
