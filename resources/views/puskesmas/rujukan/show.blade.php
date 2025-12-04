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

    <!-- Bootstrap 5 CSS (masih dipakai untuk grid, container, dan btn) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fa] font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] print:bg-white">

    <!-- Header -->
    <div class="header-section bg-gradient-to-br from-[#667eea] to-[#764ba2] text-white py-8 mb-8 shadow-md print:bg-[#667eea]">
        <div class="container">
            <h1 class="mb-2">
                <i class="bi bi-file-medical-fill"></i>
                Detail Rujukan
            </h1>
            <p class="mb-0 opacity-75">Informasi lengkap rujukan pasien</p>
        </div>
    </div>

    <div class="container pb-5">
        @if(isset($rujukan) && $rujukan)
        
        <!-- Informasi Rujukan -->
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] border-b-[#004085] bg-[#e7f3ff] text-[#004085]">
                <i class="bi bi-clipboard-data mr-3 text-[1.3rem]"></i>
                Informasi Rujukan
            </div>
            <div class="info-card-body p-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">ID Rujukan:</div>
                            <div class="info-value text-[#212529] flex-1">
                                <strong>#{{ $rujukan->id }}</strong>
                            </div>
                        </div>
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Status:</div>
                            <div class="info-value text-[#212529] flex-1">
                                <span class="badge-status inline-block px-4 py-2 rounded-full text-[0.9rem] font-semibold
                                    {{ $rujukan->done_status 
                                        ? 'status-selesai bg-[#d4edda] text-[#155724] border border-[#c3e6cb]'
                                        : 'status-menunggu bg-[#fff3cd] text-[#856404] border border-[#ffeaa7]' }}">
                                    @if($rujukan->done_status)
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    @else
                                        <i class="bi bi-clock-fill"></i> Menunggu
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
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

        <!-- Data Pasien -->
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#d4edda] text-[#155724] border-b-[#155724]">
                <i class="bi bi-person-fill mr-3 text-[1.3rem]"></i>
                Data Pasien
            </div>
            <div class="info-card-body p-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Nama Lengkap:</div>
                            <div class="info-value text-[#212529] flex-1">
                                <strong>{{ $rujukan->nama_pasien ?? 'Tidak tersedia' }}</strong>
                            </div>
                        </div>
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">NIK:</div>
                            <div class="info-value text-[#212529] flex-1">
                                <code>{{ $rujukan->nik ?? '-' }}</code>
                            </div>
                        </div>
                        @if(isset($rujukan->tanggal_lahir))
                        <div class="info-row flex py-3 border-b border-b-[#f0f0f0] last:border-b-0">
                            <div class="info-label font-semibold text-[#495057] w-[40%] shrink-0">Tanggal Lahir:</div>
                            <div class="info-value text-[#212529] flex-1">
                                {{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d F Y') }}
                                <small class="text-muted">
                                    ({{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->age }} tahun)
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
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
                                    <a href="tel:{{ $rujukan->no_telepon }}" class="text-decoration-none">
                                        <i class="bi bi-telephone-fill"></i> {{ $rujukan->no_telepon }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Rumah Sakit -->
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#f3e5f5] text-[#6a1b9a] border-b-[#6a1b9a]">
                <i class="bi bi-hospital-fill mr-3 text-[1.3rem]"></i>
                Rumah Sakit Tujuan
            </div>
            <div class="info-card-body p-6">
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
                            <a href="tel:{{ $rujukan->telepon_rs }}" class="text-decoration-none">
                                <i class="bi bi-telephone-fill"></i> {{ $rujukan->telepon_rs }}
                            </a>
                        @else
                            <span class="text-muted fst-italic">Nomor telepon belum tersedia</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Hasil Skrining (jika ada) -->
        @if(isset($rujukan->kesimpulan))
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#fff3e0] text-[#e65100] border-b-[#e65100]">
                <i class="bi bi-clipboard-check-fill mr-3 text-[1.3rem]"></i>
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

        <!-- Tindak Lanjut dari Rumah Sakit -->
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#e0f7fa] text-[#006064] border-b-[#006064]">
                <i class="bi bi-arrow-repeat mr-3 text-[1.3rem]"></i>
                Tindak Lanjut dari Rumah Sakit
            </div>
            <div class="info-card-body p-6">
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
                            <span class="text-muted fst-italic">
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
                            <span class="text-muted fst-italic">
                                Belum ada keterangan kunjungan berikutnya dari RS
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan Rujukan -->
        <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden print:shadow-none print:border print:border-[#dee2e6]">
            <div class="info-card-header flex items-center p-5 font-semibold text-[1.1rem] border-b-[3px] bg-[#e8eaf6] text-[#3949ab] border-b-[#3949ab]">
                <i class="bi bi-pencil-square mr-3 text-[1.3rem]"></i>
                Catatan Rujukan
            </div>
            <div class="info-card-body p-6">
                <div class="catatan-box bg-[#f8f9fa] border-l-4 border-l-[#6c757d] p-5 rounded-md min-h-[80px]">
                    @if(isset($rujukan->catatan_rujukan) && !empty($rujukan->catatan_rujukan))
                        <p class="mb-0" style="white-space: pre-line;">{{ $rujukan->catatan_rujukan }}</p>
                    @else
                        <p class="mb-0 text-muted fst-italic">Tidak ada catatan tambahan</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex flex-wrap gap-3 mt-4 no-print print:hidden">
            <a href="{{ route('puskesmas.rujukan.index') }}" 
               class="btn btn-secondary btn-custom px-6 py-3 rounded-lg font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            
            @if(!$rujukan->done_status)
            <button 
                id="btnTandaiSelesai"
                type="button"
                data-update-url="{{ route('puskesmas.rujukan.update-status', $rujukan->id) }}"
                class="btn btn-success btn-custom px-6 py-3 rounded-lg font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="bi bi-check-circle"></i> Tandai Selesai
            </button>
            @endif
            
            <button 
                type="button"
                id="btnCetakRujukan"
                class="btn btn-primary btn-custom px-6 py-3 rounded-lg font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>

        @else
        <!-- Data Tidak Ditemukan -->
        <div class="text-center py-5">
            <div class="info-card bg-white rounded-xl shadow mb-6 overflow-hidden mx-auto max-w-[500px] print:shadow-none print:border print:border-[#dee2e6]">
                <div class="info-card-body p-6">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Data Tidak Ditemukan</h3>
                    <p class="text-muted">Data rujukan yang Anda cari tidak tersedia</p>
                    <a href="{{ route('puskesmas.rujukan.index') }}" 
                       class="btn btn-primary btn-custom mt-3 px-6 py-3 rounded-lg font-medium transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
