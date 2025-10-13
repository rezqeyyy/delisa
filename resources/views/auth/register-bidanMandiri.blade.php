<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Akun Bidan Mandiri - DeLISA</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-4xl bg-white shadow-2xl rounded-2xl p-8">
            <p class="text-7xl font-bold text-[#D91A8B]">*</p>
            <h1 class="text-3xl font-bold text-[#D91A8B]">Pengajuan Akun Bidan Mandiri</h1>
            <p class="text-gray-600 mt-1">Pengajuan Akun ke Dinkes Depok</p>

            <form action="#" method="POST" class="mt-8 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap PIC</label>
                        <input type="text" placeholder="Nama anda" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No Telepon</label>
                        <input type="text" placeholder="Masukan Nomor Telp Anda" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" placeholder="Masukan Email Anda" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" placeholder="Masukan Password" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor Izin Praktek</label>
                    <input type="text" placeholder="Masukan Izin Praktek" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Klinik / Praktek</label>
                    <input type="text" placeholder="Nama Klinik" class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Wilayah Kerja</label>
                    <select class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]">
                        <option value="">Pilih Wilayah Kerja</option>
                        <option value="kecamatan-a">Kecamatan A</option>
                        <option value="kecamatan-b">Kecamatan B</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea rows="4" placeholder="Masukan Alamat" class="mt-1 w-full px-4 py-3 rounded-lg border border-[#D91A8B] focus:outline-none focus:ring-[#D91A8B] focus:border-[#D91A8B]"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-full bg-[#D91A8B] text-white font-semibold hover:bg-[#c4177c]">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>