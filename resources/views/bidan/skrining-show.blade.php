<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bidan — Detail Skrining Pasien</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        
        <x-bidan.sidebar />

        <div class="flex-1 flex flex-col lg:pl-64">
            
            <header class="sticky top-0 z-10 flex h-20 items-center justify-between border-b bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex items-center">
                    <a href="{{ route('bidan.skrining') }}" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="ml-3 text-2xl font-semibold text-gray-800">Data Detail Pasien</h1>
                </div>

                <div class="flex items-center gap-4">
                    <button class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=F472B6&background=FCE7F3" alt="Avatar" class="h-10 w-10 rounded-full border-2 border-pink-100">
                            <div class="text-left hidden md:block">
                                <div class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->role->nama_role }}</div>
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" x-transition>
                            <a href="{{ route('bidan.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();"
                                   class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                    Log Out
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-8 rounded-lg bg-white p-6 shadow-lg">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">Informasi Pasien dan Data Kehamilan</h3>
                    <div class="divide-y divide-gray-200">
                        @php
                        $dataRow = function ($informasi, $data, $deskripsi = null) {
                            echo '<div class="flex flex-wrap items-center justify-between py-4">';
                            echo '  <dt class="text-sm font-medium text-gray-600">';
                            echo $informasi;
                            if ($deskripsi) {
                                echo '<p class="mt-1 text-xs text-gray-400">' . $deskripsi . '</p>';
                            }
                            echo '  </dt>';
                            echo '  <dd class="mt-1 text-sm font-semibold text-gray-900 sm:mt-0">' . ($data ?? 'N/A') . '</dd>';
                            echo '</div>';
                        };
                        @endphp

                        {{ $dataRow('Tanggal Pemeriksaan', $skrining->created_at->format('d F Y')) }}
                        {{ $dataRow('Nama', $skrining->pasien->user->name ?? 'N/A') }}
                        {{ $dataRow('NIK', $skrining->pasien->nik ?? 'N/A') }}
                        {{ $dataRow('Kehamilan ke (G)', $skrining->riwayatKehamilanGpa->total_kehamilan ?? 'N/A') }}
                        {{ $dataRow('Jumlah Persalinan (P)', $skrining->riwayatKehamilanGpa->total_persalinan ?? 'N/A') }}
                        {{ $dataRow('Jumlah Abortus (A)', $skrining->riwayatKehamilanGpa->total_abortus ?? 'N/A') }}
                        {{ $dataRow('Usia Kehamilan', ($skrining->kondisiKesehatan->usia_kehamilan ?? 'N/A') . ' Minggu') }}
                        {{ $dataRow('Taksiran Persalinan', $skrining->kondisiKesehatan ? Carbon::parse($skrining->kondisiKesehatan->tanggal_perkiraan_persalinan)->format('d F Y') : 'N/A') }}
                        {{ $dataRow('Indeks Masa Tubuh (IMT)', $skrining->kondisiKesehatan->imt ?? 'N/A') }}
                        {{ $dataRow('Status IMT', $skrining->kondisiKesehatan->status_imt ?? 'N/A') }}
                        {{ $dataRow('Anjuran Kenaikan BB', $skrining->kondisiKesehatan->anjuran_kenaikan_bb ?? 'N/A') }}
                        {{ $dataRow('Tensi/Tekanan Darah', ($skrining->kondisiKesehatan->sdp ?? 'N/A') . '/' . ($skrining->kondisiKesehatan->dbp ?? 'N/A') . ' mmHg') }}
                        {{ $dataRow('Mean Arterial Pressure (MAP)', $skrining->kondisiKesehatan->map ?? 'N/A', 'Tekanan ukuran rata-rata di arteri selama satu siklus jantung') }}
                    </div>
                </div>

                <div class="mb-8 rounded-lg bg-white p-6 shadow-lg">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">Hasil Skrining dan Rekomendasi</h3>
                    <div class="divide-y divide-gray-200">
                        {{ $dataRow('Jumlah Resiko Sedang', $skrining->jumlah_resiko_sedang ?? 0) }}
                        {{ $dataRow('Jumlah Resiko Tinggi', $skrining->jumlah_resiko_tinggi ?? 0) }}
                        {{ $dataRow('Kesimpulan', $skrining->kesimpulan ?? 'N/A') }}
                        
                        @php
                            $rekomendasi = 'Belum ada rekomendasi.';
                            if ($skrining->kesimpulan == 'Beresiko') {
                                $rekomendasi = 'Waspada Pre Eklampsia. Disarankan untuk segera dirujuk ke Rumah Sakit atau Dokter. Kenali tanda-tanda bahaya dalam kehamilan seperti sakit kepala hebat, pandangan kabur, dan nyeri ulu hati. Jika mengalami tanda bahaya, segera ke fasilitas kesehatan.';
                            } elseif ($skrining->kesimpulan == 'Normal' || $skrining->kesimpulan == 'Aman') {
                                $rekomendasi = 'Kondisi normal, tetap jaga kesehatan dan pola makan. Lakukan pemeriksaan rutin.';
                            } elseif ($skrining->kesimpulan == 'Waspada') {
                                $rekomendasi = 'Pantau kondisi secara berkala. Kenali tanda-tanda bahaya dan segera hubungi fasilitas kesehatan jika muncul.';
                            }
                        @endphp
                        {{ $dataRow('Rekomendasi', $rekomendasi) }}

                        {{ $dataRow('Catatan', 'Belum ada catatan.') }}
                    </div>
                </div>

                <div class->"flex items-center justify-end gap-4">
                    <a href="{{ route('bidan.skrining') }}"
                       class="rounded-lg bg-gray-200 px-6 py-3 text-sm font-medium text-gray-800 hover:bg-gray-300">
                        Kembali
                    </a>

                    <form action="{{ route('bidan.skrining.followUp', $skrining->id) }}" method="POST">
                        @csrf
                        <button type"submit"
                                class="rounded-lg px-6 py-3 text-sm font-medium text-white shadow-md
                                       {{ $skrining->tindak_lanjut
                                          ? 'bg-gray-400 cursor-not-allowed' 
                                          : 'bg-green-500 hover:bg-green-600' }}"
                                {{ $skrining->tindak_lanjut ? 'disabled' : '' }}>
                            {{ $skrining->tindak_lanjut ? 'Telah Diperiksa' : 'Sudah Diperiksa' }}
                        </button>
                    </form>
                </div>

            </main>
        </div>
    </div>
</body>
</html>