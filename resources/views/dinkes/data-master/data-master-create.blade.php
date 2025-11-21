<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Tambah Akun</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dinkes/sidebar-toggle.js',
        'resources/js/dinkes/data-master-form.js',
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-dinkes.sidebar />

        <main class="ml-0 md:ml-[260px] flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            <header>
                <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">List Daftar Akun</h1>
                <p class="text-xs sm:text-sm text-[#7C7C7C]">Manage the Details of Your Menu Account</p>
            </header>

            {{-- Flash / Errors --}}
            @if (session('ok'))
                <div class="flash-alert mb-3 flex items-start gap-3 rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-700 transition-opacity duration-500"
                    role="alert" data-flash data-timeout="3500">
                    <span class="mt-0.5">✅</span>
                    <div class="flex-1">{{ session('ok') }}</div>
                    <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-alert mb-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 transition-opacity duration-500"
                    role="alert" data-flash data-timeout="4000">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5">⚠️</span>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="flash-close opacity-60 hover:opacity-100">✕</button>
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <section class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('dinkes.data-master', ['tab' => 'bidan']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'bidan' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Bidan PKM
                </a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'rs']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'rs' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Rumah Sakit
                </a>
                <a href="{{ route('dinkes.data-master', ['tab' => 'puskesmas']) }}"
                    class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium {{ $tab === 'puskesmas' ? 'bg-[#B9257F] text-white' : 'bg-white border border-[#D9D9D9] text-[#4B4B4B]' }}">
                    Puskesmas
                </a>
            </section>

            {{-- Forms --}}
            <section class="bg-[#FFF0F5] p-4 sm:p-6 lg:p-8 rounded-2xl">
                {{-- RS --}}
                @if ($tab === 'rs')
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Tambah Data Rumah Sakit</h2>
                    <form method="POST" action="{{ route('dinkes.data-master.store-rs') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm"
                        data-rs-kelurahan-map='@json($rsKelurahanByKecamatan ?? [])'>
                        @csrf
                        <div>
                            <label>Nama Lengkap PIC</label>
                            <input name="pic_name" value="{{ old('pic_name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Nomor Telepon PIC</label>
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Email PIC</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Nama Rumah Sakit</label>
                            <input name="nama" value="{{ old('nama') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Password</label>
                            <input type="password" placeholder="password harus berisi 8 karakter" name="password"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Kecamatan: dropdown --}}
                        <div>
                            <label>Kecamatan</label>
                            <select name="kecamatan" id="rsKecamatanCreate" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach ($rsKecamatanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('kecamatan') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelurahan: dropdown (akan di-filter via JS) --}}
                        <div>
                            <label>Kelurahan</label>
                            <select name="kelurahan" id="rsKelurahanCreate" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kelurahan --</option>
                                @foreach ($rsKelurahanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('kelurahan') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>

                    {{-- Puskesmas --}}
                @elseif ($tab === 'puskesmas')
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Tambah Data Puskesmas</h2>
                    <form method="POST" action="{{ route('dinkes.data-master.store-puskesmas') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm">
                        @csrf

                        <div>
                            <label>Nama Lengkap PIC</label>
                            <input name="pic_name" value="{{ old('pic_name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        <div>
                            <label>Nomor Telepon PIC</label>
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        <div>
                            <label>Email PIC</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Nama Puskesmas / Kecamatan --}}
                        <div>
                            <label>Nama Puskesmas / Kecamatan</label>
                            <select name="nama" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach ($kecamatanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('nama') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Password</label>
                            <input type="password" name="password" placeholder="password harus berisi 8 karakter"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Alamat --}}
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>

                    {{-- Bidan --}}
                @else
                    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Tambah Akun Bidan PKM</h2>
                    <form method="POST" action="{{ route('dinkes.data-master.store-bidan') }}"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm">
                        @csrf
                        <div>
                            <label>Nama Lengkap</label>
                            <input name="name" value="{{ old('name') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Nomor Izin Praktek</label>
                            <input name="nomor_izin_praktek" type="number" value="{{ old('nomor_izin_praktek') }}"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>No Telepon</label>
                            <input name="phone" type="number" value="{{ old('phone') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Password</label>
                            <input type="password" name="password" placeholder="password harus berisi 8 karakter"
                                required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div>
                            <label>Pilih Puskesmas</label>
                            <select name="puskesmas_id" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                                <option value="">-- Pilih --</option>
                                @foreach ($puskesmasList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('puskesmas_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_puskesmas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <input name="address" value="{{ old('address') }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                        <div class="md:col-span-2">
                            <button
                                class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SUBMIT</button>
                        </div>
                    </form>
                @endif
            </section>
        </main>
    </div>
</body>

</html>
