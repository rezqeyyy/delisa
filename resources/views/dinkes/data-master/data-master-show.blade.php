<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DINKES – Detail Akun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dinkes/sidebar-toggle.js', 'resources/js/dinkes/data-master-show.js'])
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="ml-0 md:ml-[400px] p-4 sm:p-6 lg:p-8 max-w-3xl sm:max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3">
            <h1 class="text-xl sm:text-2xl font-bold">Detail Akun ({{ ucfirst($tab) }})</h1>
            <a href="{{ route('dinkes.data-master', ['tab' => $tab]) }}"
                class="px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">
                ← Kembali
            </a>
        </div>

        {{-- Kartu detail akun --}}
        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 space-y-3">
            <div>
                <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama</span>
                <div class="font-medium break-words">{{ $data->name }}</div>
            </div>

            <div>
                <span class="text-[#7C7C7C] text-xs sm:text-sm">Email</span>
                <div class="font-medium break-all">{{ $data->email }}</div>
            </div>

            {{-- Tambahan: No Telepon --}}
            @if (!empty($data->phone))
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">No. Telepon</span>
                    <div class="font-medium break-words">{{ $data->phone }}</div>
                </div>
            @endif

            {{-- Tambahan: Alamat (address user) --}}
            @if (!empty($data->address))
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat Akun</span>
                    <div class="font-medium break-words">{{ $data->address }}</div>
                </div>
            @endif

            {{-- Row password --}}
            <div id="dmPasswordSection" data-user-id="{{ $data->id }}"
                @if (session('new_password') && session('pw_user_id') == $data->id) data-init-password="{{ session('new_password') }}" @endif>
                <span class="text-[#7C7C7C] text-xs sm:text-sm">
                    Password (hasil reset otomatis)
                </span>

                <div class="mt-1">
                    {{-- Bagian yang menampilkan password jika ada --}}
                    <div id="dmPasswordValueWrapper" class="hidden">
                        <div class="font-mono text-sm sm:text-base break-all bg-[#F5F5F5] rounded-xl px-3 py-2"
                            id="dmPasswordValue"></div>

                        <p id="dmPasswordInfo" class="mt-1 text-[11px] sm:text-xs text-[#7C7C7C]"></p>
                    </div>

                    {{-- Pesan default kalau belum pernah ada reset otomatis --}}
                    <div id="dmPasswordEmptyInfo" class="text-xs sm:text-sm text-[#7C7C7C]">
                        Password hanya akan ditampilkan jika Anda melakukan
                        <span class="font-semibold">Reset Password</span>.
                        Saat ini, setiap reset akan mengatur password ke
                        <span class="font-semibold">12345678</span>.

                    </div>
                </div>

                {{-- Flag: kalau baru saja reset MANUAL untuk user ini, hapus password acak di browser --}}
                @if (session('pw_user_id_clear') && session('pw_user_id_clear') == $data->id)
                    <div id="dmPwClearFlag" data-clear="1"></div>
                @endif
            </div>

            {{-- DETAIL PER ROLE --}}
            @if ($tab === 'rs')
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama RS</span>
                    <div class="font-medium">{{ $data->nama }}</div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                        <div class="font-medium">{{ $data->kecamatan }}</div>
                    </div>
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Kelurahan</span>
                        <div class="font-medium">{{ $data->kelurahan }}</div>
                    </div>
                </div>
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>
            @elseif($tab === 'puskesmas')
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nama Puskesmas</span>
                    <div class="font-medium">{{ $data->nama_puskesmas ?? $data->nama }}</div>
                </div>

                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Kecamatan</span>
                    <div class="font-medium">{{ $data->kecamatan }}</div>
                </div>

                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat</span>
                    <div class="font-medium">{{ $data->lokasi }}</div>
                </div>
            @else
                {{-- BIDAN --}}
                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Nomor Izin Praktek</span>
                    <div class="font-medium break-words">
                        {{ $data->nomor_izin_praktek ?? '—' }}
                    </div>
                </div>

                <div>
                    <span class="text-[#7C7C7C] text-xs sm:text-sm">Puskesmas</span>
                    <div class="font-medium break-words">
                        {{ $data->nama_puskesmas ?? '—' }}
                    </div>
                </div>

                {{-- Untuk bidan, alamat utama bisa dari users.address jika ada --}}
                @if (!empty($data->address))
                    <div>
                        <span class="text-[#7C7C7C] text-xs sm:text-sm">Alamat Praktik / Domisili</span>
                        <div class="font-medium break-words">{{ $data->address }}</div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Card Reset Password --}}
        <div class="mt-4 sm:mt-6 bg-white rounded-2xl shadow p-4 sm:p-6">
            <h2 class="text-sm sm:text-base font-semibold mb-2">Reset Password Akun</h2>
            <p class="text-[11px] sm:text-xs text-[#7C7C7C] mb-3">
                Saat ini, setiap reset akan mengatur password ke
                <span class="font-semibold">12345678</span>.
            </p>

            <form action="{{ route('dinkes.data-master.reset', ['user' => $data->id, 'tab' => $tab]) }}" method="POST"
                class="space-y-3">
                @csrf

                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#1677FF] text-white hover:bg-[#125FCC] transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>

</html>
