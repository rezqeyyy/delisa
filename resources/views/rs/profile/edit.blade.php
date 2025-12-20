<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>RS – Edit Profil</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/rs/sidebar-toggle.js',
        'resources/js/rs/rs-profile.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <x-rs.sidebar />

    <div class="lg:ml-[260px] mx-auto max-w-8xl px-3 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('rs.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-2xl font-semibold text-[#1D1D1D]">Edit Profil RS</h1>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 text-rose-800 px-4 py-3 border border-rose-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl bg-white p-6 shadow">

            {{-- AVATAR --}}
            <div class="w-full flex justify-center mb-6">
                <div class="relative w-32 h-32 sm:w-36 sm:h-36">

                    {{-- Fallback --}}
                    <div id="avatarFallback"
                        class="absolute inset-0 rounded-full ring-4 ring-pink-100 bg-pink-50 flex items-center justify-center shadow {{ $user->photo ? 'hidden' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            class="w-12 h-12 sm:w-14 sm:h-14 text-pink-500" fill="currentColor" aria-hidden="true">
                            <path
                                d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                        </svg>
                    </div>

                    {{-- Foto --}}
                    <img id="avatarPreview"
                        src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . optional($user->updated_at)->timestamp : '' }}"
                        data-has-src="{{ $user->photo ? '1' : '0' }}"
                        alt="Avatar"
                        class="absolute inset-0 w-full h-full rounded-full object-cover ring-4 ring-pink-100 shadow bg-white {{ $user->photo ? '' : 'hidden' }}" />
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3 mb-8">
                <label for="photoInput"
                    class="cursor-pointer inline-flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-full shadow transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 20h14v-2H5v2Zm7-16-4 4h3v6h2V8h3l-4-4Z"/>
                    </svg>
                    Unggah Foto
                </label>

                <input id="photoInput" name="photo" type="file" accept=".svg,image/*" class="hidden" form="profileForm"/>

                <button id="btnRemovePhoto" type="button"
                    class="inline-flex items-center justify-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-full transition-colors {{ $user->photo ? '' : 'hidden' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 7h12v2H6V7Zm2 3h8l-.8 9.6A2 2 0 0 1 13.22 22h-2.44a2 2 0 0 1-1.98-1.4L8 10ZM9 4h6l1 2H8l1-2Z"/>
                    </svg>
                    Hapus Foto
                </button>

                <form id="removePhotoForm" action="{{ route('rs.profile.photo.destroy') }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            {{-- FORM --}}
            <form id="profileForm" action="{{ route('rs.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full rounded-lg border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-pink-300 focus:outline-none"
                        required>
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <h3 class="text-lg font-semibold text-[#1D1D1D] mb-4">Ubah Password</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Password Lama</label>
                            <input type="password" name="old_password" placeholder="Masukkan Password Lama"
                                class="w-full rounded-lg border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-pink-300 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Password Baru</label>
                            <input type="password" name="password" placeholder="Masukkan Password Baru"
                                class="w-full rounded-lg border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-pink-300 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1D1D1D] mb-2">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" placeholder="Konfirmasi Password Baru"
                                class="w-full rounded-lg border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-pink-300 focus:outline-none">
                        </div>

                        <p class="text-xs text-[#7C7C7C]">
                            • Kosongkan semua field password jika hanya ganti nama/foto. <br>
                            • Jika ingin ganti password, isi <b>password lama</b> + <b>password baru</b> + <b>konfirmasi</b>.
                        </p>
                    </div>
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-6 sm:px-7 py-3 rounded-full shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21 7L9 19l-5.5-5.5 1.42-1.42L9 16.17 19.59 5.59 21 7z"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <footer class="text-center text-xs text-[#7C7C7C] py-6">
            © 2025 Dinas Kesehatan Kota Depok — DeLISA
        </footer>
    </div>
</body>
</html>
