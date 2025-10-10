<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Delisa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="container p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-2">Lupa Password</h1>
        <p class="text-gray-600 text-center mb-6 text-sm">Masukkan email Anda untuk reset password</p>
        
        <form>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" placeholder="akun@gmail.com" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button 
                type="button"
                onclick="alert('Link reset password telah dikirim ke email Anda!')"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors mb-4"
            >
                Reset Password
            </button>
        </form>
        
        <div class="text-center">
            <a href="/login" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>