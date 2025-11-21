<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Akun Rumah Sakit - DeLISA</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dinkes/data-master-form.js', {{-- dipakai untuk filter kecamatan → kelurahan --}}
    ])
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">

        <div class="w-full max-w-4xl bg-white shadow-2xl rounded-2xl p-8">
            <x-back-link />

            <p class="text-7xl font-bold text-[#D91A8B]">*</p>
            <h1 class="text-3xl font-bold text-[#D91A8B]">Pengajuan Akun Rumah Sakit</h1>
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

            <form
                action="{{ route('rs.register.store') }}"
                method="POST"
                class="mt-6 space-y-5"
                data-rs-kelurahan-map='@json($rsKelurahanByKecamatan ?? [])'
                data-old-kecamatan="{{ old('kecamatan') }}"
                data-old-kelurahan="{{ old('kelurahan') }}"
            >
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Lengkap PIC
                        </label>
                        <input
                            name="pic_name"
                            type="text"
                            value="{{ old('pic_name') }}"
                            placeholder="Nama anda"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nomor Telepon PIC
                        </label>
                        <input
                            name="phone"
                            type="text"
                            value="{{ old('phone') }}"
                            placeholder="Masukan Nomor Telp"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Email PIC
                        </label>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="Masukan Email Anda"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Rumah Sakit
                        </label>
                        <input
                            name="nama"
                            type="text"
                            value="{{ old('nama') }}"
                            placeholder="Masukan Nama Rumah Sakit"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input
                        name="password"
                        type="password"
                        placeholder="Masukan Password"
                        class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] focus:outline-none"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- KECAMATAN (dropdown, master Depok) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Kecamatan
                        </label>
                        <select
                            name="kecamatan"
                            id="rsKecamatanCreate"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] bg-white focus:outline-none"
                        >
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach ($rsKecamatanOptions as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ old('kecamatan') === $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- KELURAHAN (akan diisi JS berdasarkan kecamatan) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Kelurahan
                        </label>
                        <select
                            name="kelurahan"
                            id="rsKelurahanCreate"
                            class="mt-1 w-full px-4 py-3 rounded-full border border-[#D91A8B] bg-white focus:outline-none"
                        >
                            <option value="">-- Pilih Kelurahan --</option>
                            {{-- opsi lain akan di-generate oleh data-master-form.js --}}
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Alamat
                    </label>
                    <textarea
                        name="lokasi"
                        rows="4"
                        placeholder="Isi Alamat"
                        class="mt-1 w-full px-4 py-3 rounded-lg border border-[#D91A8B] focus:outline-none"
                    >{{ old('lokasi') }}</textarea>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3 rounded-full bg-[#D91A8B] text-white font-semibold hover:bg-[#c4177c]"
                    >
                        SUBMIT
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
