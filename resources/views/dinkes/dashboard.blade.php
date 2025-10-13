<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DINKES – Dashboard</title>
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/dropdown.js', // ⬅️ tambahkan ini
    ])
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 h-full w-[260px] bg-white shadow-lg flex flex-col justify-between rounded-r-2xl">
            <div>
                <div class="flex items-center gap-3 p-6 border-b border-[#CAC7C7]">
                    <!-- Logo dari ZIP -->
                    <img src="{{ asset('images/logo_fulltext 2.png') }}" alt="DeLISA"
                        class="w-28 h-auto object-contain">
                </div>

                <nav class="mt-6 space-y-1">
                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3 bg-[#B9257F] text-white rounded-r-full font-medium">
                        <img src="{{ asset('icons/Group 36729.svg') }}" class="w-4 h-4" alt="Dashboard">
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('dinkes.data-master') }}"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Group 36805.svg') }}" class="w-4 h-4" alt="Data Master">
                        <span>Data Master</span>
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="w-4 h-4"
                            alt="Akun Baru">
                        <span>Akun Baru</span>
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3 text-[#4B4B4B] hover:bg-[#F5F5F5] rounded-r-full transition">
                        <img src="{{ asset('icons/Iconly/Regular/Light/2 User.svg') }}" class="w-4 h-4"
                            alt="Pasien Nifas">
                        <span>Pasien Nifas</span>
                    </a>
                </nav>
            </div>

            <div class="p-6 border-t border-[#CAC7C7] text-sm text-[#7C7C7C]">
                <p class="uppercase text-xs font-semibold mb-1">Account</p>
            </div>
        </aside>

        <!-- Konten utama -->
        <main class="ml-[260px] flex-1 p-8 space-y-8">
            <!-- Header (disesuaikan seperti figma) -->
            <div class="flex items-center justify-between bg-white px-5 py-4 rounded-2xl shadow-md">
                <!-- Search -->
                <div class="relative w-[520px] max-w-[58%]">
                    <span class="absolute inset-y-0 left-3 flex items-center">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Search.svg') }}" class="w-4 h-4 opacity-60"
                            alt="Search">
                    </span>
                    <input type="text" placeholder="Search.."
                        class="w-full pl-9 pr-4 py-2 border border-[#E6E6E6] rounded-full text-sm outline-none focus:ring-1 focus:ring-[#B9257F]/30 focus:border-[#B9257F]/40" />
                </div>

                <!-- Right cluster -->
                <div class="flex items-center gap-3">
                    <!-- Icon 1: Lokasi (bubble magenta) -->
                    <button
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#B9257F] shadow-[0_1px_0_#EDEDED]">
                        <img src="{{ asset('icons/lightbulb.svg') }}" class="w-4 h-4 invert-[1] brightness-0"
                            alt="mode terang">
                    </button>

                    <!-- Icon 2: Edit (bubble abu-abu) -->
                    <button class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#EFEFEF]">
                        <img src="{{ asset('icons/lightbulb-off.svg') }}" class="w-4 h-4 opacity-80" alt="mode gelap">
                    </button>

                    <!-- Icon 3: Settings (bubble putih ber-border) -->
                    <button
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Setting.svg') }}" class="w-4 h-4 opacity-90"
                            alt="Setting">
                    </button>

                    <!-- Notification + dot -->
                    <button
                        class="relative w-10 h-10 rounded-lg flex items-center justify-center bg-white border border-[#E5E5E5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Notification.svg') }}" class="w-4 h-4 opacity-90"
                            alt="Notif">
                        <span
                            class="absolute top-1.5 right-1.5 inline-block w-2.5 h-2.5 bg-[#B9257F] rounded-full ring-2 ring-white"></span>
                    </button>

                    <!-- Profile dropdown -->
                    <div id="profileWrapper" class="relative">
                        <button id="profileBtn"
                            class="flex items-center gap-3 pl-2 pr-3 py-1.5 bg-white border border-[#E5E5E5] rounded-full hover:bg-[#F8F8F8]">
                            <img src="{{ asset('images/4e350a7a-ceec-4c5e-b445-e40cc38fa39b 1.png') }}"
                                class="w-8 h-8 rounded-full object-cover" alt="Admin" />
                            <div class="leading-tight pr-1 text-left">
                                <p class="text-[13px] font-semibold text-[#1D1D1D]">Nama Dinkes</p>
                                <p class="text-[11px] text-[#7C7C7C] -mt-0.5">email Dinkes</p>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Down 2.svg') }}"
                                class="w-4 h-4 opacity-70" alt="More" />
                        </button>

                        <!-- Dropdown menu -->
                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-[#E9E9E9] overflow-hidden z-20">
                            <div class="px-4 py-3 border-b border-[#F0F0F0]">
                                <p class="text-sm font-medium text-[#1D1D1D]">Nama Dinkes</p>
                                <p class="text-xs text-[#7C7C7C] truncate">email Dinkes</p>
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

            <!-- GRID KPI -->
            <section class="grid grid-cols-12 gap-6">
                <!-- KIRI ATAS -->
                <div
                    class="col-span-12 lg:col-span-7 lg:col-start-1 lg:row-start-1 bg-white rounded-2xl p-5 shadow-md h-[260px] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                    <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="w-3.5 h-3.5"
                                        alt="Lokasi">
                                </span>
                                <h2 class="font-semibold text-lg">Daerah Asal Pasien</h2>
                            </div>
                            <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                                class="w-3.5 h-3.5 opacity-60" alt="Go">
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-[#CAC7C7] text-center">
                            <div>
                                <p class="text-sm text-[#7C7C7C]">Depok</p>
                                <h1 class="text-5xl font-bold">0</h1>
                            </div>
                            <div>
                                <p class="text-sm text-[#7C7C7C]">Non Depok</p>
                                <h1 class="text-5xl font-bold">0</h1>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN ATAS -->
                <div
                    class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-1 bg-white rounded-2xl p-5 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Sharp/Light/Group 36721.svg') }}"
                                    class="w-3.5 h-3.5" alt="Resiko">
                            </span>
                            <h2 class="font-semibold text-lg">Resiko Eklampsia</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>
                    <p class="text-sm flex justify-between">Pasien Normal <span>0</span></p>
                    <p class="text-sm flex justify-between">Pasien Beresiko Eklampsia <span>0</span></p>
                </div>

                <!-- KIRI BAWAH: row-span-2 -->
                <div
                    class="col-span-12 lg:col-span-7 lg:col-start-1 lg:row-start-2 lg:row-span-2 bg-white rounded-2xl p-5 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Regular/Light/3 User.svg') }}" class="w-3.5 h-3.5"
                                    alt="Nifas">
                            </span>
                            <h2 class="font-semibold text-lg">Data Pasien Nifas</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm border-b border-[#CAC7C7] pb-2">
                            <span>Total Pasien Nifas</span>
                            <span>0</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2">
                            <span>Sudah KF1</span>
                            <span>0</span>
                        </div>
                    </div>
                </div>

                <!-- KANAN TENGAH -->
                <div
                    class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-2 bg-white rounded-2xl p-5 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Regular/Light/Graph.svg') }}" class="w-3.5 h-3.5"
                                    alt="Hadir">
                            </span>
                            <h2 class="font-semibold text-lg">Pasien Hadir</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>
                    <p class="text-sm flex justify-between">Pasien Hadir <span>0</span></p>
                    <p class="text-sm flex justify-between">Pasien Tidak Hadir <span>0</span></p>
                </div>

                <!-- KANAN BAWAH -->
                <div
                    class="col-span-12 lg:col-span-5 lg:col-start-8 lg:row-start-3 bg-white rounded-2xl p-5 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                                <img src="{{ asset('icons/Iconly/Sharp/Outline/Activity.svg') }}" class="w-3.5 h-3.5"
                                    alt="Pemantauan">
                            </span>
                            <h2 class="font-semibold text-lg">Pemantauan</h2>
                        </div>
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Arrow - Right.svg') }}"
                            class="w-3.5 h-3.5 opacity-60" alt="Go">
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="border border-[#CAC7C7] rounded-lg p-2">
                            <p class="text-sm text-[#7C7C7C]">Sehat</p>
                            <p class="text-2xl font-bold">0</p>
                        </div>
                        <div class="border border-[#CAC7C7] rounded-lg p-2">
                            <p class="text-sm text-[#7C7C7C]">Total Dirujuk</p>
                            <p class="text-2xl font-bold">0</p>
                        </div>
                        <div class="border border-[#CAC7C7] rounded-lg p-2">
                            <p class="text-sm text-[#7C7C7C]">Meninggal</p>
                            <p class="text-2xl font-bold">0</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Chart -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                        <img src="{{ asset('icons/Iconly/Sharp/Light/Location.svg') }}" class="w-3.5 h-3.5"
                            alt="">
                    </span>
                    <h2 class="font-semibold">Daerah Asal Pasien</h2>
                </div>
                <div class="grid grid-cols-12 gap-3 h-56 items-end">
                    <div class="h-14 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-24 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-36 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-28 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-52 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-16 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-32 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-12 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-16 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-40 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-14 bg-[#B9257F] rounded-xl"></div>
                    <div class="h-20 bg-[#B9257F] rounded-xl"></div>
                </div>
                <div class="grid grid-cols-12 text-center text-xs text-[#7C7C7C] mt-3">
                    <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>Mei</span><span>Jun</span><span>Jul</span><span>Agu</span><span>Sep</span><span>Okt</span><span>Nov</span><span>Des</span>
                </div>
            </section>

            <!-- Table -->
            <section class="bg-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F5F5F5]">
                            <img src="{{ asset('icons/Iconly/Regular/Light/Message.svg') }}" class="w-3.5 h-3.5"
                                alt="">
                        </span>
                        <h2 class="font-semibold">Data Pasien Pre Eklampsia</h2>
                    </div>
                    <button class="border border-[#CAC7C7] rounded-full px-4 py-1 text-sm">Filter</button>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-[#7C7C7C] border-b border-[#CAC7C7]">
                        <tr>
                            <th class="py-2 text-left">No</th>
                            <th class="text-left">Pasien</th>
                            <th class="text-left">NIK</th>
                            <th class="text-left">Umur</th>
                            <th class="text-left">Usia Kehamilan</th>
                            <th class="text-left">Tanggal</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Resiko</th>
                            <th class="text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#CAC7C7]">
                        <tr>
                            <td class="py-3">01</td>
                            <td>Almira R</td>
                            <td>3201•••8899</td>
                            <td>26</td>
                            <td>28 Minggu</td>
                            <td>12/09/2025</td>
                            <td><span class="px-3 py-1 bg-[#39E93F33] text-[#39E93F] rounded-full">Hadir</span></td>
                            <td><span class="px-3 py-1 bg-[#E2D30D33] text-[#E2D30D] rounded-full">Sedang</span></td>
                            <td><button
                                    class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">Detail</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3">02</td>
                            <td>Nadia P</td>
                            <td>3204•••1122</td>
                            <td>30</td>
                            <td>16 Minggu</td>
                            <td>10/09/2025</td>
                            <td><span class="px-3 py-1 bg-[#E20D0D33] text-[#E20D0D] rounded-full">Mangkir</span></td>
                            <td><span class="px-3 py-1 bg-[#E20D0D33] text-[#E20D0D] rounded-full">Tinggi</span></td>
                            <td><button
                                    class="border border-[#CAC7C7] rounded-md px-3 py-1 hover:bg-[#F5F5F5]">Detail</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <footer class="text-center text-xs text-[#7C7C7C] py-6">
                © 2025 Dinas Kesehatan Kota Depok — DeLISA
            </footer>
        </main>
    </div>

</body>

</html>
