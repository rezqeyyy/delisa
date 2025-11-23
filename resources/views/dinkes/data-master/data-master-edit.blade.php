<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Edit Akun</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dinkes/sidebar-toggle.js',
        'resources/js/dinkes/data-master-form.js',
    ])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    @php
        $currentNamaPuskesmas = old('nama', $data->nama ?? ($data->nama_puskesmas ?? ($data->kecamatan ?? null)));
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-dinkes.sidebar />

        {{-- Main --}}
        <main class="ml-0 md:ml-[260px] flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            {{-- Header --}}
            <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1 class="text-[22px] sm:text-[28px] font-bold leading-tight text-[#000]">
                        Edit Akun ({{ ucfirst($tab) }})
                    </h1>
                    <p class="text-xs sm:text-sm text-[#7C7C7C]">
                        Update detail informasi akun yang dipilih
                    </p>
                </div>
                <a href="{{ route('dinkes.data-master', ['tab' => $tab]) }}"
                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">
                    ← Kembali ke Data Master
                </a>
            </header>

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-3 rounded-lg border border-red-300 bg-red-50 p-3 sm:p-4 text-xs sm:text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Card --}}
            <section class="bg-[#FFF0F5] rounded-2xl p-4 sm:p-6 lg:p-8">
                <form method="POST"
                    action="{{ route('dinkes.data-master.update', ['user' => $data->id, 'tab' => $tab]) }}"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm"
                    @if ($tab === 'rs') data-rs-kelurahan-map='@json($rsKelurahanByKecamatan ?? [])' @endif>
                    @csrf
                    @method('PUT')

                    {{-- Common fields --}}
                    <div>
                        <label>Nama</label>
                        <input name="name" value="{{ old('name', $data->name) }}" required
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $data->email) }}" required
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>
                    <div>
                        <label>No Telepon</label>
                        <input name="phone" type="number" value="{{ old('phone', $data->phone) }}"
                            class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                    </div>

                    @if ($tab === 'rs')
                        {{-- ====== RS ====== --}}
                        <div>
                            <label>Nama Rumah Sakit</label>
                            <input name="nama" value="{{ old('nama', $data->nama) }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        {{-- Kecamatan: dropdown --}}
                        <div>
                            <label>Kecamatan</label>
                            <select name="kecamatan" id="rsKecamatanEdit" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach ($kecamatanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('kecamatan', $data->kecamatan) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelurahan: dropdown (di-filter via JS) --}}
                        <div>
                            <label>Kelurahan</label>
                            <select name="kelurahan" id="rsKelurahanEdit" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kelurahan --</option>
                                @foreach ($kelurahanOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('kelurahan', $data->kelurahan) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi', $data->lokasi) }}</textarea>
                        </div>

                    @elseif ($tab === 'puskesmas')
                        {{-- ====== Puskesmas ====== --}}
                        @php
                            $selectedNama = old('nama', $currentNamaPuskesmas);
                        @endphp

                        <div>
                            <label>Nama Puskesmas / Kecamatan</label>
                            <select name="nama" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach ($kecamatanOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedNama === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <textarea name="lokasi" rows="3"
                                class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi', $data->lokasi) }}</textarea>
                        </div>

                    @else
                        {{-- ====== BIDAN ====== --}}
                        <div>
                            <label>Nomor Izin Praktek</label>
                            <input name="nomor_izin_praktek"
                                value="{{ old('nomor_izin_praktek', $data->nomor_izin_praktek) }}" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>

                        <div>
                            <label>Puskesmas</label>
                            <select name="puskesmas_id" required
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1 bg-white">
                                <option value="">-- Pilih Puskesmas --</option>
                                @foreach ($puskesmasList as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('puskesmas_id', $data->puskesmas_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_puskesmas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label>Alamat</label>
                            <input name="address" value="{{ old('address', $data->address) }}"
                                class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <button class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>

</html>
