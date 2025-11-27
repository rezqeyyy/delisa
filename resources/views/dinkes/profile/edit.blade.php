{{-- Deklarasi tipe dokumen HTML sebagai HTML5 --}}
<!DOCTYPE html>
{{-- Atur bahasa utama dokumen menjadi Bahasa Indonesia --}}
<html lang="id">

<head>
    {{-- Set encoding karakter dokumen menjadi UTF-8 (mendukung hampir semua karakter) --}}
    <meta charset="UTF-8">
    {{-- Supaya layout responsif mengikuti lebar layar perangkat --}}
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    {{-- Judul halaman yang tampil di tab browser --}}
    <title>DINKES – Edit Profil</title>
    {{-- Memuat file CSS & JS menggunakan Vite (asset bundler Laravel) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dinkes/dinkes-profile.js', 'resources/js/dinkes/sidebar-toggle.js'])
</head>

{{-- Body utama halaman, dengan background abu dan font Poppins --}}
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    {{-- Wrapper utama flex agar sidebar dan konten sejajar secara horizontal --}}
    <div class="flex">
        {{-- Komponen Blade untuk sidebar khusus Dinkes --}}
        <x-dinkes.sidebar />

        {{-- Area konten utama, bergeser ke kanan saat layar md ke atas karena ada sidebar --}}
        <main class="ml-0 md:ml-[260px] w-full min-h-screen p-4 sm:p-6 lg:p-7 flex flex-col">
            {{-- Bagian header halaman profil --}}
            <!-- Header -->
            {{-- Baris fleksibel untuk judul halaman dengan sedikit jarak bawah --}}
            <div class="flex items-center gap-3 mb-4 sm:mb-6">
                {{-- Judul halaman: Profile Edit User --}}
                <h1 class="text-2xl sm:text-3xl font-semibold">Profile Edit User</h1>
            </div>

            {{-- Jika terdapat pesan sukses di session (misalnya setelah update profil berhasil) --}}
            @if (session('success'))
                {{-- Kotak notifikasi sukses dengan warna hijau --}}
                <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3 border border-green-200">
                    {{-- Tampilkan isi pesan sukses dari session --}}
                    {{ session('success') }}
                </div>
            @endif

            {{-- Jika terdapat error validasi apapun pada request sebelumnya --}}
            @if ($errors->any())
                {{-- Kotak notifikasi error dengan warna merah muda --}}
                <div class="mb-4 rounded-lg bg-rose-50 text-rose-800 px-4 py-3 border border-rose-200">
                    {{-- List bullet dari semua error yang dikembalikan validator --}}
                    <ul class="list-disc list-inside space-y-1">
                        {{-- Loop setiap pesan error dan tampilkan sebagai <li> --}}
                        @foreach ($errors->all() as $error)
                            {{-- Satu baris error validasi --}}
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Section utama yang akan berisi kartu form profil --}}
            <section class="flex-1">
                {{-- Kartu putih di tengah layar untuk form profil, dengan padding dan maksimal lebar tertentu --}}
                <div
                    class="bg-white rounded-2xl shadow-md px-4 sm:px-8 md:px-12 py-8 sm:py-12 max-w-xl sm:max-w-2xl md:max-w-3xl mx-auto">
                    {{-- Tombol Kembali ke dashboard Dinkes --}}
                    <!-- Tombol Kembali -->
                    {{-- Link yang mengarahkan kembali ke route dinkes.dashboard --}}
                    <a href="{{ route('dinkes.dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white hover:bg-gray-50 active:bg-gray-100 px-3 py-2 sm:px-4 sm:py-2 shadow-sm transition">
                        {{-- Ikon panah kiri menggunakan SVG, menandakan aksi kembali --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="w-4 h-4 sm:w-5 sm:h-5">
                            {{-- Path yang membentuk panah kiri --}}
                            <path
                                d="M14.707 5.293a1 1 0 0 1 0 1.414L10.414 11H20a1 1 0 1 1 0 2h-9.586l4.293 4.293a1 1 0 0 1-1.414 1.414l-6-6a1 1 0 0 1 0-1.414l6-6a1 1 0 0 1 1.414 0Z" />
                        </svg>
                        {{-- Teks Kembali di samping ikon --}}
                        <span class="text-sm sm:text-base font-medium">Kembali</span>
                    </a>

                    {{-- Bagian Avatar (foto profil) --}}
                    {{-- Container untuk avatar, diratakan ke tengah --}}
                    <div class="w-full flex justify-center mb-6">
                        {{-- Elemen fallback avatar berupa lingkaran dengan ikon user, muncul jika belum ada foto --}}
                        <div id="avatarFallback"
                            class="w-28 h-28 sm:w-32 sm:h-32 rounded-full ring-4 ring-pink-100 bg-pink-50 flex items-center justify-center shadow">
                            {{-- Ikon user generic (SVG) di tengah lingkaran --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                class="w-12 h-12 sm:w-14 sm:h-14 text-pink-500" fill="currentColor" aria-hidden="true">
                                {{-- Path membentuk kepala dan badan user --}}
                                <path
                                    d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z" />
                            </svg>
                        </div>
                        {{-- Gambar avatar aktual user, akan ditampilkan jika user punya foto --}}
                        <img id="avatarPreview"
                            src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . optional($user->updated_at)->timestamp : '' }}"
                            data-has-src="{{ $user->photo ? '1' : '0' }}" alt="Avatar"
                            class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover ring-4 ring-pink-100 shadow hidden" />
                    </div>

                    {{-- Aksi terkait foto profil (unggah dan hapus) --}}
                    {{-- Baris tombol untuk mengunggah/hapus foto, responsif antara kolom dan baris --}}
                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3 mb-8">
                        {{-- Label yang berperan sebagai tombol untuk memilih file foto (terkait input#photo) --}}
                        <label for="photo"
                            class="cursor-pointer inline-flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-full shadow">
                            {{-- Ikon upload (SVG) di tombol Unggah Foto --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                {{-- Path membentuk ikon panah ke atas dan garis bawah (unggah) --}}
                                <path d="M5 20h14v-2H5v2Zm7-16-4 4h3v6h2V8h3l-4-4Z" />
                            </svg>
                            {{-- Teks pada tombol untuk mengunggah foto --}}
                            Unggah Foto
                        </label>
                        {{-- Input file untuk memilih foto, disembunyikan karena dikontrol lewat label di atas --}}
                        <input id="photo" name="photo" type="file" accept=".svg,image/*" class="hidden"
                            form="profileForm" />

                        {{-- Tombol untuk menghapus foto profil yang ada --}}
                        <button id="btnRemovePhoto" type="button"
                            class="inline-flex items-center justify-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-full">
                            {{-- Ikon tempat sampah (hapus) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                {{-- Path menggambar ikon tempat sampah --}}
                                <path
                                    d="M6 7h12v2H6V7Zm2 3h8l-.8 9.6A2 2 0 0 1 13.22 22h-2.44a2 2 0 0 1-1.98-1.4L8 10ZM9 4h6l1 2H8l1-2Z" />
                            </svg>
                            {{-- Teks tombol hapus foto --}}
                            Hapus Foto
                        </button>

                        {{-- Form tersembunyi untuk request hapus foto ke server --}}
                        <form id="removePhotoForm" action="{{ route('dinkes.profile.photo.destroy') }}" method="POST"
                            class="hidden">
                            {{-- Token CSRF sebagai proteksi form --}}
                            @csrf
                            {{-- Method spoofing: gunakan HTTP DELETE untuk route --}}
                            @method('DELETE')
                        </form>
                    </div>

                    {{-- Form utama untuk update profil (nama + password + foto) --}}
                    {{-- Form dengan id profileForm, submit ke route dinkes.profile.update --}}
                    <form id="profileForm" action="{{ route('dinkes.profile.update') }}" method="POST"
                        enctype="multipart/form-data" class="space-y-5">
                        {{-- Token CSRF untuk keamanan form --}}
                        @csrf
                        {{-- Spoof method PUT (karena update resource) --}}
                        @method('PUT')

                        {{-- Field input nama user --}}
                        <div>
                            {{-- Label untuk field nama --}}
                            <label class="block text-sm font-medium mb-2">Nama</label>
                            {{-- Input text untuk nama, diisi dengan old('name') atau fallback ke $user->name --}}
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none"
                                required>
                        </div>

                        {{-- Field input password lama (untuk verifikasi sebelum ganti password) --}}
                        <div>
                            {{-- Label untuk password lama --}}
                            <label class="block text-sm font-medium mb-2">Masukkan Password Lama</label>
                            {{-- Input password untuk memasukkan password lama, boleh kosong jika tidak ganti password --}}
                            <input type="password" name="old_password" placeholder="Masukkan Password Lama"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none">
                        </div>

                        {{-- Field input password baru --}}
                        <div>
                            {{-- Label untuk password baru --}}
                            <label class="block text-sm font-medium mb-2">Password</label>
                            {{-- Input password untuk password baru (optional, tergantung user) --}}
                            <input type="password" name="password" placeholder="Masukkan Password"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none">
                            {{-- Keterangan kecil mengenai aturan pengisian field password --}}
                            <p class="text-xs text-gray-500 mt-1 py-3">
                                • Kosongkan kedua field password jika hanya ganti nama/foto. <br>
                                • Jika ingin ganti password, isi <b>keduanya</b> dan password lama harus benar.
                            </p>
                        </div>

                        {{-- Bagian tombol submit untuk menyimpan perubahan --}}
                        <div class="flex justify-end pt-2">
                            {{-- Tombol submit dengan ikon centang --}}
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-6 sm:px-7 py-3 rounded-full shadow">
                                {{-- Ikon centang (berhasil/konfirmasi) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    {{-- Path membentuk tanda centang --}}
                                    <path d="M21 7L9 19l-5.5-5.5 1.42-1.42L9 16.17 19.59 5.59 21 7z" />
                                </svg>
                                {{-- Teks tombol simpan perubahan --}}
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- Footer di bagian paling bawah halaman --}}
            <footer class="mt-auto text-center text-xs text-[#7C7C7C] py-6">
                {{-- Teks copyright sederhana --}}
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>
</body>

</html>
