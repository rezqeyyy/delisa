<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Delisa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-800">Dashboard Delisa</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Welcome, User!</span>
                        <a 
                            href="/login" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors"
                        >
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white border-2 border-gray-200 rounded-lg p-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4">Selamat Datang di Dashboard Delisa!</h2>
                    <p class="text-gray-600 mb-6">Anda berhasil login ke sistem Delisa.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <h3 class="font-semibold text-blue-800 mb-2">Profile</h3>
                            <p class="text-blue-600 text-sm">Kelola profil pengguna</p>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <h3 class="font-semibold text-green-800 mb-2">Settings</h3>
                            <p class="text-green-600 text-sm">Pengaturan aplikasi</p>
                        </div>
                        
                        <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                            <h3 class="font-semibold text-purple-800 mb-2">Reports</h3>
                            <p class="text-purple-600 text-sm">Laporan sistem</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>