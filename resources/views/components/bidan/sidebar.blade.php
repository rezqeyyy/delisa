<aside class="fixed inset-y-0 left-0 z-10 w-64 flex-col border-r bg-white shadow-lg lg:flex hidden">
    <div class="flex h-20 items-center justify-center flex-shrink-0 px-4">
        <h1 class="text-3xl font-bold text-pink-500">DeLISA</h1>
    </div>

    <nav class="flex-1 overflow-y-auto">
        <div class="space-y-2 px-4 py-4">
            
            <a href="{{ route('bidan.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-3 font-medium
                      {{ request()->routeIs('bidan.dashboard') 
                         ? 'bg-pink-500 text-white shadow-md' 
                         : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('bidan.skrining') }}" class="flex items-center gap-3 rounded-lg px-3 py-3 font-medium
                      {{ request()->routeIs('bidan.skrining') /* INI JADI OTOMATIS AKTIF */
                         ? 'bg-pink-500 text-white shadow-md' 
                         : 'text-gray-600 hover:bg-gray-100' }}">
                
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Skrining</span>
            </a>

            <a href="#" {{-- Ganti '#' dengan route('bidan.laporan') jika sudah ada --}}
               class="flex items-center gap-3 rounded-lg px-3 py-3 font-medium
                      {{ request()->routeIs('bidan.laporan') 
                         ? 'bg-pink-500 text-white shadow-md' 
                         : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Laporan</span>
            </a>

            <a href="#" {{-- Ganti '#' dengan route('bidan.pasien-nifas') jika sudah ada --}}
               class="flex items-center gap-3 rounded-lg px-3 py-3 font-medium
                      {{ request()->routeIs('bidan.pasien-nifas') 
                         ? 'bg-pink-500 text-white shadow-md' 
                         : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Pasien Nifas</span>
            </a>
        </div>
    </nav>
</aside>