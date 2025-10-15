<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pasien — Dashboard</title>
 @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dropdown.js'])
</head>

<body class="bg-gray-100 min-h-screen p-8">
  <div class="flex min-h-screen">
    <x-pasien.sidebar />
    
    <main class="flex-1 xl:ml-[260px] p-6 lg:p-8 space-y-6">
      <div class="-mx-6 lg:-mx-8 px-6 lg:px-8 flex flex-wrap items-center gap-3 bg-white py-4 rounded-2xl shadow-md">
        <div class="relative flex-1 min-w-0">
            <span class="absolute inset-y-0 left-3 flex items-center">
                <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60" alt="Search">
            </span>
            <input type="text" placeholder="Search..."
                class="w-full pl-9 pr-4 py-2 rounded-full border border-[#D9D9D9] text-sm focus:outline-none focus:ring-1 focus:ring-[#B9257F]/40">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto justify-end md:justify-start flex-shrink-0">
            <a class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90" alt="Setting">
            </a>

            <button class="relative w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" class="w-4 h-4 opacity-90" alt="Notif">
                <span class="absolute top-1.5 right-1.5 inline-block w-2.5 h-2.5 bg-[#B9257F] rounded-full ring-2 ring-white"></span>
            </button>

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
                            {{ auth()->user()->name ?? 'Nama Pasien' }}</p>
                    </div>
                    <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                        class="w-4 h-4 opacity-70" alt="More" />
                </button>

                <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                    <div class="px-4 py-3 border-b border-[#F0F0F0]">
                        <p class="text-sm font-medium text-[#1D1D1D]">
                            {{ auth()->user()->name ?? 'Nama Pasien' }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout.pasien') }}">
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

      <footer class="text-center text-xs text-[#7C7C7C] py-6">
          © 2025 Dinas Kesehatan Kota Depok — DeLISA
      </footer>
    </main>

  </div>
</body>
</html>
