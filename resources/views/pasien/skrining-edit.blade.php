<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Edit Skrining</title>
    @vite('resources/css/app.css')
    <style>
        /* Mengimpor font Poppins dari Google Fonts agar visual teks 100% cocok dengan desain modern */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-6">
        <h1 class="text-xl font-semibold text-[#1D1D1D]">Edit Skrining</h1>
        <p class="text-sm text-[#7C7C7C] mt-2">
        Form edit belum disiapkan. Silakan kembali ke dashboard.
        </p>
        <a href="{{ route('pasien.dashboard') }}" class="mt-6 inline-block px-4 py-2 rounded-lg bg-[#B9257F] text-white text-sm">
        Kembali ke Dashboard
        </a>
    </div>
</body>
</html>