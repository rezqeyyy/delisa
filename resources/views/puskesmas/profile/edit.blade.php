<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Puskesmas – Edit Profil</title>
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/js/dropdown.js',
        'resources/js/puskesmas/sidebar-toggle.js',
        'resources/js/puskesmas/form-search.js'])
</head>

<body class="bg-[#FFF7FC] min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen" x-data="{ openSidebar: false }">
        
        <x-puskesmas.sidebar />

        <main class="flex-1 w-full xl:ml-[260px] p-4 sm:p-6 lg:p-8 space-y-6 max-w-none min-w-0 overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center gap-3 flex-nowrap">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-3 flex items-center">
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
                        </span>
                        <input type="text" placeholder="Search..." 
                        id="dashboardSearch" {{-- ID penting untuk JS --}}
                        data-form-search="true" {{-- Attribute khusus untuk form pages --}}
                        class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-none justify-end">
                    <a href="{{ route('puskesmas.profile.edit') }}" class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
                    </a>

                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn" class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            
                            @if (Auth::user()?->photo)
                                <img src="{{ Storage::url(Auth::user()->photo) . '?t=' . optional(Auth::user()->updated_at)->timestamp }}"
                                    class="w-8 h-8 rounded-full object-cover" alt="{{ Auth::user()->name }}">
                            @else
                                <span
                                    class="w-8 h-8 rounded-full bg-pink-50 ring-2 ring-pink-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="w-4 h-4 text-pink-500" fill="currentColor" aria-hidden="true">
                                        <path
                                            d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                                    </svg>
                                </span>
                            @endif

                            <div class="leading-tight pr-1 text-left">
                                <p class="text-[13px] font-semibold text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Puskesmas' }}
                                </p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">
                                    {{ auth()->user()->name ?? 'Puskesmas' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-[#F9F9F9] flex items-center gap-2">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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

            <section class="flex-1">
                <div class="bg-white rounded-2xl border border-[#E9E9E9] p-6 max-w-xl mx-auto">
                    <!-- Tombol Kembali -->
                    <a href="{{ route('puskesmas.dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-[#E9E9E9] bg-white hover:bg-gray-50 px-4 py-2 shadow-sm transition mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="w-4 h-4">
                            <path
                                d="M14.707 5.293a1 1 0 0 1 0 1.414L10.414 11H20a1 1 0 1 1 0 2h-9.586l4.293 4.293a1 1 0 0 1-1.414 1.414l-6-6a1 1 0 0 1 0-1.414l6-6a1 1 0 0 1 1.414 0Z" />
                        </svg>
                        <span class="text-sm font-medium">Kembali ke Dashboard</span>
                    </a>

                    <h1 class="text-2xl font-bold text-[#1D1D1D] mb-2">Edit Profile Puskesmas</h1>
                    <p class="text-[#7C7C7C] mb-6">Kelola informasi profil Anda</p>

                    {{-- Avatar --}}
                    <div class="w-full flex justify-center mb-6">
                        <div id="avatarFallback"
                            class="w-28 h-28 rounded-full ring-4 ring-pink-100 bg-pink-50 flex items-center justify-center shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                class="w-12 h-12 text-pink-500" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                            </svg>
                        </div>
                        <img id="avatarPreview"
                            src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . optional($user->updated_at)->timestamp : '' }}"
                            data-has-src="{{ $user->photo ? '1' : '0' }}" alt="Avatar"
                            class="w-28 h-28 rounded-full object-cover ring-4 ring-pink-100 shadow hidden" />
                    </div>

                    {{-- Aksi foto --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3 mb-8">
                        <label for="photo"
                            class="cursor-pointer inline-flex items-center justify-center gap-2 bg-[#B9257F] hover:bg-[#A02070] text-white px-4 py-2 rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M5 20h14v-2H5v2Zm7-16-4 4h3v6h2V8h3l-4-4Z" />
                            </svg>
                            Unggah Foto
                        </label>
                        <input id="photo" name="photo" type="file" accept=".svg,image/*" class="hidden"
                            form="profileForm" />

                        <button id="btnRemovePhoto" type="button"
                            class="inline-flex items-center justify-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M6 7h12v2H6V7Zm2 3h8l-.8 9.6A2 2 0 0 1 13.22 22h-2.44a2 2 0 0 1-1.98-1.4L8 10ZM9 4h6l1 2H8l1-2Z" />
                            </svg>
                            Hapus Foto
                        </button>

                        <form id="removePhotoForm" action="{{ route('puskesmas.profile.photo.destroy') }}" method="POST"
                            class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                    {{-- Form profil --}}
                    <form id="profileForm" action="{{ route('puskesmas.profile.update') }}" method="POST"
                        enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium mb-2 text-[#1D1D1D]">Nama Puskesmas</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-xl border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/40 focus:outline-none"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-[#1D1D1D]">Email</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full rounded-xl border border-[#E9E9E9] bg-gray-50 px-4 py-2.5 text-gray-500">
                            <p class="text-xs text-[#7C7C7C] mt-1">Email tidak dapat diubah</p>
                        </div>

                        <div class="border-t border-[#E9E9E9] pt-6">
                            <h3 class="text-lg font-semibold text-[#1D1D1D] mb-4">Ubah Password</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2 text-[#1D1D1D]">Password Lama</label>
                                    <input type="password" name="old_password" placeholder="Masukkan password lama"
                                        class="w-full rounded-xl border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/40 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2 text-[#1D1D1D]">Password Baru</label>
                                    <input type="password" name="password" placeholder="Masukkan password baru"
                                        class="w-full rounded-xl border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/40 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2 text-[#1D1D1D]">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation" placeholder="Konfirmasi password baru"
                                        class="w-full rounded-xl border border-[#E9E9E9] px-4 py-2.5 focus:ring-2 focus:ring-[#B9257F]/40 focus:outline-none">
                                </div>
                            </div>

                            <p class="text-xs text-[#7C7C7C] mt-3">
                                • Kosongkan field password jika hanya ingin mengubah nama atau foto profil.<br>
                                • Isi semua field password hanya jika ingin mengubah password.
                            </p>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-[#B9257F] hover:bg-[#A02070] text-white px-6 py-3 rounded-full shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M21 7L9 19l-5.5-5.5 1.42-1.42L9 16.17 19.59 5.59 21 7z" />
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const photoInput = document.getElementById('photo');
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarFallback = document.getElementById('avatarFallback');
            const btnRemovePhoto = document.getElementById('btnRemovePhoto');
            const removePhotoForm = document.getElementById('removePhotoForm');
            const hasPhoto = avatarPreview.getAttribute('data-has-src') === '1';

            // Tampilkan avatar jika ada foto
            if (hasPhoto) {
                avatarPreview.classList.remove('hidden');
                avatarFallback.classList.add('hidden');
            }

            // Preview foto baru
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        avatarPreview.classList.remove('hidden');
                        avatarFallback.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Hapus foto
            btnRemovePhoto.addEventListener('click', function() {
                if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
                    removePhotoForm.submit();
                }
            });
        });
    </script>
</body>

</html>