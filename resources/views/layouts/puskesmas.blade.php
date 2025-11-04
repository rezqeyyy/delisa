<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - @yield('title', 'Puskesmas')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex min-h-screen">
        <x-puskesmas.sidebar />
        <main class="ml-[260px] p-6 w-full">
            @yield('content')
        </main>
    </div>
</body>
</html>