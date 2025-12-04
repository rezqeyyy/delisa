<!DOCTYPE html> 
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Rujukan #{{ $rujukan->id ?? '' }}</title>

    {{-- Tailwind + app JS --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/puskesmas/rujukan-detail.js',
    ])
</head>
<body class="bg-[#f8f9fa] font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] print:bg-white">

    {{-- Header --}}
    <div class="header-section bg-gradient-to-br from-[#667eea] to-[#764ba2] text-white py-8 mb-8 shadow-md print:bg-[#667eea]">
        <div class="max-w-5xl mx-auto px-4">
            <h1 class="mb-2 flex items-center gap-3 text-2xl font-semibold">
                {{-- Ikon file medical (SVG pengganti bootstrap icon) --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     class="w-7 h-7" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <path d="M12 18v-6" />
                    <path d="M9 15h6" />
                </svg>
                <span>Detail Rujukan</span>
            </h1>
            <p class="mb-0 opacity-75 text-sm">Informasi lengkap rujukan pasien</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 pb-5">
        @if(isset($rujukan) && $rujukan)

            {{-- Informasi Rujukan --}}
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] border-b-[#004085] bg-[#e7f3ff] text-[#004085]">
                    {{-- Icon clipboard data --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="8" y="2" width="8" height="4" rx="1" />
                        <path d="M10 2h4" />
                        <path d="M6 6h12v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z" />
                        <path d="M10 11h4" />
                        <path d="M10 15h4" />
                    </svg>
                    Informasi Rujukan
                </div>
                <div class="info-card-body p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">ID Rujukan:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    <strong>#{{ $rujukan->id }}</strong>
                                </div>
                            </div>
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Status:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    <span class="badge-status inline-flex items-center gap-2 px-4 py-2 rounded-full text-[0.9rem] font-semibold
                                        {{ $rujukan->done_status 
                                            ? 'status-selesai bg-[#d4edda] text-[#155724] border border-[#c3e6cb]'
                                            : 'status-menunggu bg-[#fff3cd] text-[#856404] border border-[#ffeaa7]' }}">
                                        @if($rujukan->done_status)
                                            {{-- Icon check-circle --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                 class="w-4 h-4" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <path d="m9 12 2 2 4-4" />
                                            </svg>
                                            <span>Selesai</span>
                                        @else
                                            {{-- Icon clock --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                 class="w-4 h-4" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <path d="M12 6v6l4 2" />
                                            </svg>
                                            <span>Menunggu</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Tanggal Rujukan:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    {{ \Carbon\Carbon::parse($rujukan->created_at)->format('d M Y, H:i') }} WIB
                                </div>
                            </div>
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">ID Skrining:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    #{{ $rujukan->skrining_id }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Pasien --}}
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#d4edda] text-[#155724] border-b-[#155724]">
                    {{-- Icon person --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="7" r="4" />
                        <path d="M5.5 21a6.5 6.5 0 0 1 13 0" />
                    </svg>
                    Data Pasien
                </div>
                <div class="info-card-body p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Nama Lengkap:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    <strong>{{ $rujukan->nama_pasien ?? 'Tidak tersedia' }}</strong>
                                </div>
                            </div>
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">NIK:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    <span class="font-mono text-sm">{{ $rujukan->nik ?? '-' }}</span>
                                </div>
                            </div>
                            @if(isset($rujukan->tanggal_lahir))
                                <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                    <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Tanggal Lahir:</div>
                                    <div class="info-value text-[#212529] flex-1">
                                        {{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d F Y') }}
                                        <small class="text-gray-500">
                                            ({{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->age }} tahun)
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-3">
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Alamat:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    {{ $rujukan->alamat ?? 'Tidak tersedia' }}
                                </div>
                            </div>
                            <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                                <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">No. Telepon:</div>
                                <div class="info-value text-[#212529] flex-1">
                                    @if(isset($rujukan->no_telepon) && !empty($rujukan->no_telepon))
                                        <a href="tel:{{ $rujukan->no_telepon }}" class="text-[#2563eb] hover:underline inline-flex items-center gap-1">
                                            {{-- Icon tel --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                 class="w-4 h-4" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.11 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                            </svg>
                                            {{ $rujukan->no_telepon }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Rumah Sakit --}}
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#f3e5f5] text-[#6a1b9a] border-b-[#6a1b9a]">
                    {{-- Icon hospital --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 22V7a2 2 0 0 1 2-2h5V3a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2h5a2 2 0 0 1 2 2v15" />
                        <path d="M10 14h4" />
                        <path d="M12 12v4" />
                        <path d="M3 22h18" />
                    </svg>
                    Rumah Sakit Tujuan
                </div>
                <div class="info-card-body p-6">
                    <div class="space-y-3">
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Nama Rumah Sakit:</div>
                            <div class="info-value text-[#212529] flex-1">
                                <strong>{{ $rujukan->nama_rs ?? 'Tidak tersedia' }}</strong>
                            </div>
                        </div>
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Alamat:</div>
                            <div class="info-value text-[#212529] flex-1">
                                {{ $rujukan->alamat_rs ?? 'Tidak tersedia' }}
                            </div>
                        </div>
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Telepon:</div>
                            <div class="info-value text-[#212529] flex-1">
                                @if(isset($rujukan->telepon_rs) && !empty($rujukan->telepon_rs))
                                    <a href="tel:{{ $rujukan->telepon_rs }}" class="text-[#2563eb] hover:underline inline-flex items-center gap-1">
                                        {{-- icon tel --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                             class="w-4 h-4" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.11 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                        </svg>
                                        {{ $rujukan->telepon_rs }}
                                    </a>
                                @else
                                    <span class="text-gray-500 italic">Nomor telepon belum tersedia</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hasil Skrining (jika ada) --}}
            @if(isset($rujukan->kesimpulan))
                <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                    <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#fff3e0] text-[#e65100] border-b-[#e65100]">
                        {{-- Icon clipboard check --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="8" y="2" width="8" height="4" rx="1" />
                            <path d="M10 2h4" />
                            <path d="M6 6h12v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z" />
                            <path d="m9 14 2 2 4-4" />
                        </svg>
                        Hasil Skrining
                    </div>
                    <div class="info-card-body p-6">
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Kesimpulan:</div>
                            <div class="info-value text-[#212529] flex-1">
                                {{ $rujukan->kesimpulan }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tindak Lanjut dari Rumah Sakit --}}
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#e0f7fa] text-[#006064] border-b-[#006064]">
                    {{-- Icon repeat --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 1l4 4-4 4" />
                        <path d="M3 11V9a4 4 0 0 1 4-4h14" />
                        <path d="M7 23l-4-4 4-4" />
                        <path d="M21 13v2a4 4 0 0 1-4 4H3" />
                    </svg>
                    Tindak Lanjut dari Rumah Sakit
                </div>
                <div class="info-card-body p-6 space-y-3">
                    <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                        <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Anjuran Kontrol:</div>
                        <div class="info-value text-[#212529] flex-1">
                            @if(isset($rujukan->anjuran_kontrol) && $rujukan->anjuran_kontrol !== null)
                                @if($rujukan->anjuran_kontrol === 'fktp')
                                    Fasilitas Kesehatan Tingkat Pertama (FKTP)
                                @elseif($rujukan->anjuran_kontrol === 'rs')
                                    Rumah Sakit
                                @else
                                    {{ ucfirst($rujukan->anjuran_kontrol) }}
                                @endif
                            @else
                                <span class="text-gray-500 italic">
                                    Belum ada anjuran kontrol dari RS
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                        <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">
                            Pemeriksaan / Kunjungan Berikutnya:
                        </div>
                        <div class="info-value text-[#212529] flex-1">
                            @if(isset($rujukan->kunjungan_berikutnya) && $rujukan->kunjungan_berikutnya !== '')
                                {{ $rujukan->kunjungan_berikutnya }}
                            @else
                                <span class="text-gray-500 italic">
                                    Belum ada keterangan kunjungan berikutnya dari RS
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Pasien Datang (dari RS) --}}
                    <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                        <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">
                            Pasien Datang:
                        </div>
                        <div class="info-value text-[#212529] flex-1">
                            @if(!is_null($rujukan->pasien_datang))
                                @if($rujukan->pasien_datang)
                                    Ya, pasien datang ke RS
                                @else
                                    Tidak, pasien tidak datang ke RS
                                @endif
                            @else
                                <span class="text-gray-500 italic">
                                    Belum ada keterangan kehadiran pasien dari RS
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catatan Rujukan --}}
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#e8eaf6] text-[#3949ab] border-b-[#3949ab]">
                    {{-- Icon pencil --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-6 h-6 mr-3" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                    </svg>
                    Catatan Rujukan
                </div>
                <div class="info-card-body p-6">
                    <div class="catatan-box bg-[#f8f9fa] border-l-4 border-l-[#6c757d] p-5 rounded-md min-h-[80px]">
                        @if(isset($rujukan->catatan_rujukan) && !empty($rujukan->catatan_rujukan))
                            <p class="mb-0 whitespace-pre-line">{{ $rujukan->catatan_rujukan }}</p>
                        @else
                            <p class="mb-0 text-gray-500 italic">Tidak ada catatan tambahan</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-wrap gap-3 mt-4 no-print print:hidden">
                <a href="{{ route('puskesmas.rujukan.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:bg-gray-300">
                    {{-- Icon arrow-left --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                        <path d="M9 12h12" />
                    </svg>
                    Kembali ke Daftar
                </a>
                
                @if(!$rujukan->done_status)
                    <button 
                        id="btnTandaiSelesai"
                        type="button"
                        data-update-url="{{ route('puskesmas.rujukan.update-status', $rujukan->id) }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium bg-emerald-600 text-white transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:bg-emerald-700">
                        {{-- Icon check-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-4 h-4" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        Tandai Selesai
                    </button>
                @endif
                
                <button 
                    type="button"
                    id="btnCetakRujukan"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium bg-indigo-600 text-white transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:bg-indigo-700">
                    {{-- Icon printer --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9V2h12v7" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" rx="2" />
                    </svg>
                    Cetak
                </button>
            </div>

        @else
            {{-- Data Tidak Ditemukan --}}
            <div class="text-center py-5">
                <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden mx-auto max-w-[500px] print:shadow-none print:border print:border-[#dee2e6]">
                    <div class="info-card-body p-6 flex flex-col items-center">
                        {{-- Icon warning triangle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-16 h-16 text-red-500 mb-3" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        <h3 class="mt-3 text-lg font-semibold">Data Tidak Ditemukan</h3>
                        <p class="text-gray-500">Data rujukan yang Anda cari tidak tersedia</p>
                        <a href="{{ route('puskesmas.rujukan.index') }}" 
                           class="inline-flex items-center gap-2 mt-3 px-6 py-3 rounded-lg font-medium bg-indigo-600 text-white transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:bg-indigo-700">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

</body>
</html>
