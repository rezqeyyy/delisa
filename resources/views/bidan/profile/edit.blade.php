<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Edit Profil</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js',
        'resources/js/bidan/sidebar-toggle.js'
    ])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <x-bidan.sidebar />
    
    <div class="lg:ml-[260px] mx-auto max-w-8xl px-3 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="mb-6 flex items-center">            
            <a href="{{ route('bidan.dashboard') }}" class="text-[#1D1D1D] hover:text-[#000]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="ml-3 text-3xl font-bold text-[#1D1D1D]">Edit Profil Bidan</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3 border border-green-200">
                {{ session('status') }}
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
            
            {{-- BAGIAN AVATAR / FOTO PROFIL --}}
            {{-- Menggunakan Relative Positioning agar gambar menumpuk dengan rapi --}}
            <div class="w-full flex justify-center mb-6">
                <div class="relative w-32 h-32 sm:w-36 sm:h-36">
                    
                    {{-- 1. Layer Paling Bawah: Fallback Icon (Selalu ada di belakang) --}}
                    <div class="absolute inset-0 rounded-full ring-4 ring-pink-100 bg-pink-50 flex items-center justify-center shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            class="w-12 h-12 sm:w-14 sm:h-14 text-pink-500" fill="currentColor" aria-hidden="true">
                            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                        </svg>
                    </div>

                    {{-- 2. Layer Atas: Foto Profil / Preview --}}
                    {{-- Defaultnya 'hidden' jika tidak ada foto. JS akan menghapus class hidden ini. --}}
                    <img id="avatarPreview"
                        src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . time() : '#' }}"
                        alt="Avatar"
                        class="absolute inset-0 w-full h-full rounded-full object-cover ring-4 ring-pink-100 shadow bg-white {{ $user->photo ? '' : 'hidden' }}"/>
                </div>
            </div>

            {{-- BAGIAN TOMBOL UPLOAD & HAPUS --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3">
                <label for="photoInput" class="cursor-pointer inline-flex items-center justify-center gap-2 bg-[#B9257F] hover:bg-[#a31f70] text-white px-4 py-2 rounded-full shadow transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20h14v-2H5v2Zm7-16-4 4h3v6h2V8h3l-4-4Z"/></svg>
                    Unggah Foto
                </label>
                
                {{-- Input File: ID diganti jadi 'photoInput' untuk menghindari konflik nama --}}
                <input id="photoInput" name="photo" type="file" accept="image/*" class="hidden" form="profileForm" />

                @if($user->photo)
                    <form action="{{ route('bidan.profile.photo.destroy') }}" method="POST" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-full transition-colors" onclick="return confirm('Yakin ingin menghapus foto profil?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 7h12v2H6V7Zm2 3h8l-.8 9.6A2 2 0 0 1 13.22 22h-2.44a2 2 0 0 1-1.98-1.4L8 10ZM9 4h6l1 2H8l1-2Z"/></svg>
                            Hapus Foto
                        </button>
                    </form>
                @endif
            </div>

            {{-- FORM DATA UTAMA --}}
            <form id="profileForm" action="{{ route('bidan.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5 mt-6">
                @csrf
                @method('PUT')
                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <label class="block text-sm font-medium mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none transition-shadow" required>
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <label class="block text-sm font-medium mb-2">Nomor Izin Praktek</label>
                    <input type="text" name="nomor_izin_praktek" value="{{ old('nomor_izin_praktek', $bidan->nomor_izin_praktek) }}" class="w-full rounded-xl border border-[#D9D9D9] bg-[#F5F5F5] px-4 py-2.5 focus:outline-none cursor-not-allowed text-gray-500" readonly required>
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <label class="block text-sm font-medium mb-2">Password Baru</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-[#D9D9D9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/30 focus:outline-none transition-shadow" placeholder="Kosongkan jika tidak ingin mengganti password">
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto">
                    <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border border-[#D9D9D9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/30 focus:outline-none transition-shadow">
                </div>

                <div class="w-full max-w-xl md:max-w-5xl mx-auto flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 bg-[#B9257F] hover:bg-[#a31f70] text-white px-6 sm:px-7 py-3 rounded-full shadow transition-all hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21 7L9 19l-5.5-5.5 1.42-1.42L9 16.17 19.59 5.59 21 7z"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <footer class="mt-auto text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    {{-- SCRIPT JAVASCRIPT PREVIEW --}}
    <script>
        // Jalankan script setelah halaman siap
        document.addEventListener('DOMContentLoaded', () => {
            const photoInput = document.getElementById('photoInput');
            const avatarPreview = document.getElementById('avatarPreview');

            // Cek apakah elemen input ada
            if (photoInput) {
                photoInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    
                    // Pastikan file dipilih dan merupakan gambar
                    if (file) {
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            // 1. Masukkan data gambar ke src
                            avatarPreview.src = e.target.result;
                            
                            // 2. Paksa elemen gambar untuk tampil (hapus hidden, set display block)
                            avatarPreview.classList.remove('hidden');
                            avatarPreview.style.display = 'block'; 
                        }

                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>