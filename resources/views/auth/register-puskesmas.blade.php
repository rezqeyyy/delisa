<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Akun Puskesmas - DeLISA</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-4xl bg-white shadow-2xl rounded-2xl p-8">
            <p class="text-7xl font-bold text-[#D91A8B]">*</p>
            <h1 class="text-3xl font-bold text-[#D91A8B]">Pengajuan Akun Puskesmas</h1>
            <p class="text-gray-600 mt-1">Pengajuan Akun ke Dinkes Depok</p>

            <form action="#" method="POST" class="mt-8 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap PIC</label>
                        <input type="text" placeholder="Nama anda" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon PIC</label>
                        <input type="text" placeholder="Masukan Nomor Telp" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email PIC</label>
                        <input type="email" placeholder="Masukan Email Anda" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Puskesmas</label>
                        <input type="text" placeholder="Masukan Nama Puskesmas" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" placeholder="Masukan Password" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kecamatan</label>
                        <input type="text" placeholder="Masukan Kecamatan" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelurahan</label>
                        <input type="text" placeholder="Masukan Kelurahan" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea rows="4" placeholder="Isi Alamat" class="mt-1 w-full px-4 py-3 rounded-lg border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-full bg-[#D91A8B] text-white font-semibold hover:bg-[#c4177c]">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>