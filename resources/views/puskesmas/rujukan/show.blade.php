<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Rujukan #{{ $rujukan->id ?? '' }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .info-card-header {
            padding: 1.25rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: 3px solid;
            display: flex;
            align-items: center;
        }
        .info-card-header i {
            margin-right: 0.75rem;
            font-size: 1.3rem;
        }
        .info-card-body {
            padding: 1.5rem;
        }
        .info-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            width: 40%;
            flex-shrink: 0;
        }
        .info-value {
            color: #212529;
            flex-grow: 1;
        }
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-menunggu {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .btn-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .catatan-box {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 1.25rem;
            border-radius: 6px;
            min-height: 80px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .info-card { box-shadow: none; border: 1px solid #dee2e6; }
            .header-section { background: #667eea !important; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
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
        <div class="info-card">
            <div class="info-card-header" style="background-color: #e7f3ff; color: #004085; border-color: #004085;">
                <i class="bi bi-clipboard-data"></i>
                Informasi Rujukan
            </div>
            <div class="info-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">ID Rujukan:</div>
                            <div class="info-value"><strong>#{{ $rujukan->id }}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <span class="badge-status {{ $rujukan->done_status ? 'status-selesai' : 'status-menunggu' }}">
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
                        <div class="info-row">
                            <div class="info-label">Tanggal Rujukan:</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($rujukan->created_at)->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ID Skrining:</div>
                            <div class="info-value">#{{ $rujukan->skrining_id }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Pasien -->
        <div class="info-card">
            <div class="info-card-header" style="background-color: #d4edda; color: #155724; border-color: #155724;">
                <i class="bi bi-person-fill"></i>
                Data Pasien
            </div>
            <div class="info-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Nama Lengkap:</div>
                            <div class="info-value"><strong>{{ $rujukan->nama_pasien ?? 'Tidak tersedia' }}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">NIK:</div>
                            <div class="info-value">
                                <code>{{ $rujukan->nik ?? '-' }}</code>
                            </div>
                        </div>
                        @if(isset($rujukan->tanggal_lahir))
                        <div class="info-row">
                            <div class="info-label">Tanggal Lahir:</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d F Y') }}
                                <small class="text-muted">({{ \Carbon\Carbon::parse($rujukan->tanggal_lahir)->age }} tahun)</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Alamat:</div>
                            <div class="info-value">{{ $rujukan->alamat ?? 'Tidak tersedia' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">No. Telepon:</div>
                            <div class="info-value">
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
        <div class="info-card">
            <div class="info-card-header" style="background-color: #f3e5f5; color: #6a1b9a; border-color: #6a1b9a;">
                <i class="bi bi-hospital-fill"></i>
                Rumah Sakit Tujuan
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div class="info-label">Nama Rumah Sakit:</div>
                    <div class="info-value"><strong>{{ $rujukan->nama_rs ?? 'Tidak tersedia' }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat:</div>
                    <div class="info-value">{{ $rujukan->alamat_rs ?? 'Tidak tersedia' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Telepon:</div>
                    <div class="info-value">
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
        <div class="info-card">
            <div class="info-card-header" style="background-color: #fff3e0; color: #e65100; border-color: #e65100;">
                <i class="bi bi-clipboard-check-fill"></i>
                Hasil Skrining
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div class="info-label">Kesimpulan:</div>
                    <div class="info-value">{{ $rujukan->kesimpulan }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Catatan Rujukan -->
        <div class="info-card">
            <div class="info-card-header" style="background-color: #e8eaf6; color: #3949ab; border-color: #3949ab;">
                <i class="bi bi-pencil-square"></i>
                Catatan Rujukan
            </div>
            <div class="info-card-body">
                <div class="catatan-box">
                    @if(isset($rujukan->catatan_rujukan) && !empty($rujukan->catatan_rujukan))
                        <p class="mb-0" style="white-space: pre-line;">{{ $rujukan->catatan_rujukan }}</p>
                    @else
                        <p class="mb-0 text-muted fst-italic">Tidak ada catatan tambahan</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex flex-wrap gap-3 mt-4 no-print">
            <a href="{{ route('puskesmas.rujukan.index') }}" class="btn btn-secondary btn-custom">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            
            @if(!$rujukan->done_status)
            <button onclick="tandaiSelesai()" class="btn btn-success btn-custom">
                <i class="bi bi-check-circle"></i> Tandai Selesai
            </button>
            @endif
            
            <button onclick="window.print()" class="btn btn-primary btn-custom">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>

        @else
        <!-- Data Tidak Ditemukan -->
        <div class="text-center py-5">
            <div class="info-card" style="max-width: 500px; margin: 0 auto;">
                <div class="info-card-body">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Data Tidak Ditemukan</h3>
                    <p class="text-muted">Data rujukan yang Anda cari tidak tersedia</p>
                    <a href="{{ route('puskesmas.rujukan.index') }}" class="btn btn-primary btn-custom mt-3">
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @if(isset($rujukan) && !$rujukan->done_status)
    <script>
    function tandaiSelesai() {
        if(!confirm('Apakah Anda yakin ingin menandai rujukan ini sebagai selesai?')) {
            return;
        }
        
        fetch('/puskesmas/rujukan/{{ $rujukan->id }}/update-status', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ done_status: true })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('✓ ' + data.message);
                location.reload();
            } else {
                alert('✗ ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('✗ Gagal menghubungi server');
        });
    }
    </script>
    @endif
</body>
</html>