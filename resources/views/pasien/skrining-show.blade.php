<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasien — Detail Skrining</title>
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
        <h1 class="text-xl font-semibold text-[#1D1D1D]">Detail Skrining</h1>
        <div class="mt-4 space-y-2 text-sm">
            <div><span class="font-medium">Tanggal Pengisian:</span> {{ optional($skrining->created_at)->format('d/m/Y') }}</div>
            <div><span class="font-medium">Kesimpulan:</span> {{ $skrining->kesimpulan ?? '—' }}</div>
            <div><span class="font-medium">Status Pre-Eklampsia:</span> {{ $skrining->status_pre_eklampsia ?? '—' }}</div>
        </div>
        <a href="{{ route('pasien.dashboard') }}" class="mt-6 inline-block px-4 py-2 rounded-lg bg-[#B9257F] text-white text-sm">
        Kembali ke Dashboard
        </a>
    </div>
</body>
</html>