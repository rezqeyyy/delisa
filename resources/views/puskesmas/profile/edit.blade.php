@extends('layouts.puskesmas')

@section('title', 'Edit Profil — Puskesmas')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-7 flex flex-col">
    <div class="flex items-center gap-3 mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Edit Profil</h1>
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
        <div class="bg-white rounded-2xl shadow-md px-4 sm:px-8 md:px-12 py-8 sm:py-12 max-w-xl sm:max-w-2xl md:max-w-3xl mx-auto">
            {{-- Avatar --}}
            <div class="w-full flex justify-center mb-6">
                <div id="avatarFallback"
                     class="w-28 h-28 sm:w-32 sm:h-32 rounded-full ring-4 ring-pink-100 bg-pink-50 flex items-center justify-center shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-12 h-12 sm:w-14 sm:h-14 text-pink-500" fill="currentColor" aria-hidden="true">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                    </svg>
                </div>
                <img id="avatarPreview"
                     src="{{ $user->photo ? Storage::url($user->photo) . '?t=' . optional($user->updated_at)->timestamp : '' }}"
                     data-has-src="{{ $user->photo ? '1' : '0' }}" alt="Avatar"
                     class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover ring-4 ring-pink-100 shadow hidden"/>
            </div>

            {{-- Aksi foto: upload + hapus --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3 mb-8">
                <label for="photo"
                       class="cursor-pointer inline-flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-full shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 20h14v-2H5v2Zm7-16-4 4h3v6h2V8h3l-4-4Z"/>
                    </svg>
                    Unggah Foto
                </label>
                <input id="photo" name="photo" type="file" accept=".svg,image/*" class="hidden" form="profileForm"/>

                <!-- Opsi Hapus Foto (jika diperlukan) -->
                @if($user->photo)
                <button id="btnRemovePhoto" type="button"
                        class="inline-flex items-center justify-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-full"
                        onclick="document.getElementById('removePhotoForm').submit();">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 7h12v2H6V7Zm2 3h8l-.8 9.6A2 2 0 0 1 13.22 22h-2.44a2 2 0 0 1-1.98-1.4L8 10ZM9 4h6l1 2H8l1-2Z"/>
                    </svg>
                    Hapus Foto
                </button>
                <form id="removePhotoForm" action="{{ route('puskesmas.profile.photo.destroy') }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>

            {{-- Form profil --}}
            <form id="profileForm" action="{{ route('puskesmas.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Password Lama</label>
                    <input type="password" name="old_password" placeholder="Masukkan Password Lama"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Password Baru</label>
                    <input type="password" name="password" placeholder="Biarkan kosong jika tidak ganti password"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:ring-2 focus:ring-pink-200 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">
                        • Kosongkan jika hanya ganti nama/foto.<br>
                        • Jika ganti password, isi <b>password lama</b> dan <b>password baru</b>.
                    </p>
                </div>

                <div class="flex justify-end pt-2">
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
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fallback = document.getElementById('avatarFallback');
    const preview = document.getElementById('avatarPreview');
    const photoInput = document.getElementById('photo');
    const btnRemove = document.getElementById('btnRemovePhoto');

    if (preview.dataset.hasSrc === '1') {
        preview.classList.remove('hidden');
        fallback.classList.add('hidden');
    }

    photoInput?.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                fallback.classList.add('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
@endpush