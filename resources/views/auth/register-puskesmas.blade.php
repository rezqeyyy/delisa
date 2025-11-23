<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Akun Puskesmas - DeLISA</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-4xl bg-white shadow-2xl rounded-2xl p-8">
            <x-back-link />

            <p class="text-7xl font-bold text-[#D91A8B]">*</p>
            <h1 class="text-3xl font-bold text-[#D91A8B]">Pengajuan Akun Puskesmas</h1>
            <p class="text-gray-600 mt-1">Pengajuan Akun ke Dinkes Depok</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-300 bg-red-50 text-red-700 p-3 text-sm">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('puskesmas.register.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap PIC</label>
                        <input name="pic_name" type="text" value="{{ old('pic_name') }}" placeholder="Nama anda"
                            class="mt-1 w-full px-4 py-3 rounded-full border @error('pic_name')  @else border-[#D91A8B] @enderror focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon PIC</label>
                        <input name="phone" type="text" value="{{ old('phone') }}"
                            placeholder="Masukan Nomor Telp"
                            class="mt-1 w-full px-4 py-3 rounded-full border @error('phone') @else border-[#D91A8B] @enderror focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email PIC</label>
                        <input name="email" type="email" value="{{ old('email') }}"
                            placeholder="Masukan Email Anda"
                            class="mt-1 w-full px-4 py-3 rounded-full border @error('email') @else border-[#D91A8B] @enderror focus:outline-none">
                    </div>

                    {{-- NAMA PUSKESMAS: sekarang jadi dropdown, sekaligus mewakili kecamatan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Puskesmas / Kecamatan
                        </label>
                        <select name="nama"
                            class="mt-1 w-full px-4 py-3 rounded-full border @error('nama') @else border-[#D91A8B] @enderror focus:outline-none bg-white">
                            <option value="">-- Pilih Puskesmas Kecamatan di Kota Depok --</option>
                            @foreach ($kecamatanOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('nama') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('nama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input name="password" type="password" placeholder="Masukan Password"
                        class="mt-1 w-full px-4 py-3 rounded-full border @error('password') @else border-[#D91A8B] @enderror focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="lokasi" rows="4" placeholder="Isi Alamat"
                        class="mt-1 w-full px-4 py-3 rounded-lg border @error('lokasi') @else border-[#D91A8B] @enderror focus:outline-none">{{ old('lokasi') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 rounded-full bg-[#D91A8B] text-white font-semibold hover:bg-[#c4177c]">
                        SUBMIT
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>
