@extends('layouts.rs')

@section('title', 'Hasil Pemeriksaan Pasien')

@section('content')
<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">D</div>
                <div class="logo-text">
                    <h3>DeLISA</h3>
                    <small>Deteksi Dini Pre Eklampsia</small>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-section">
                <span class="menu-label">HOME</span>
                <a href="{{ route('rs.dashboard') }}" class="menu-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('rs.skrining.index') }}" class="menu-item active">
                    <i class="fas fa-file-alt"></i>
                    <span>Skrining</span>
                </a>
                <a href="{{ route('rs.pasien-nifas.index') }}" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Pasien Nifas</span>
                </a>
            </div>

            <div class="menu-section mt-4">
                <span class="menu-label">ACCOUNT</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="content-header">
            <div class="header-left">
                <a href="{{ route('rs.skrining.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="page-title">Hasil Pemeriksaan Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})</h2>
            </div>
            <a href="{{ route('rs.skrining.edit', $skrining->id) }}" class="btn-edit">
                <i class="fas fa-edit"></i>
                Edit Data
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Content -->
        <div class="skrining-content">
            <!-- Data Pasien -->
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-user-circle"></i>
                        Informasi Pasien
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="info-table">
                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Nama Lengkap</span>
                                <span class="info-value">{{ $skrining->pasien->user->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">NIK</span>
                                <span class="info-value">{{ $skrining->pasien->nik ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Tanggal Pemeriksaan Awal</span>
                                <span class="info-value">{{ $skrining->created_at ? $skrining->created_at->format('d F Y, H:i') : '-' }} WIB</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Usia Kehamilan</span>
                                <span class="info-value">{{ $skrining->kondisiKesehatan->usia_kehamilan ?? '-' }} minggu</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Status Awal</span>
                                <span class="info-value">
                                    @php
                                        $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                        $badgeClass = match(strtolower($conclusion)) {
                                            'berisiko', 'beresiko' => 'badge-berisiko',
                                            'normal', 'aman' => 'badge-normal',
                                            'waspada', 'menengah' => 'badge-waspada',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hasil Pemeriksaan di Rumah Sakit -->
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-hospital"></i>
                        Hasil Pemeriksaan di Rumah Sakit
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($rujukan)
                    <div class="info-table">
                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Pasien Datang</span>
                                <span class="info-value">
                                    @if($rujukan->pasien_datang === 1)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Ya
                                        </span>
                                    @elseif($rujukan->pasien_datang === 0)
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i> Tidak
                                        </span>
                                    @else
                                        <span class="text-muted">Belum diisi</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Riwayat Tekanan Darah</span>
                                <span class="info-value">{{ $rujukan->riwayat_tekanan_darah ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Hasil Pemeriksaan Protein Urin</span>
                                <span class="info-value">{{ $rujukan->hasil_protein_urin ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Perlu Pemeriksaan Lanjutan</span>
                                <span class="info-value">
                                    @if($rujukan->perlu_pemeriksaan_lanjut === 1)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Ya
                                        </span>
                                    @elseif($rujukan->perlu_pemeriksaan_lanjut === 0)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Tidak
                                        </span>
                                    @else
                                        <span class="text-muted">Belum diisi</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($rujukan->catatan_rujukan)
                        <div class="info-row full-width">
                            <div class="info-col">
                                <span class="info-key">Catatan Tambahan</span>
                                <div class="catatan-box">
                                    <p>{{ $rujukan->catatan_rujukan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check fa-3x"></i>
                        <p>Belum ada data pemeriksaan dari rumah sakit</p>
                        <a href="{{ route('rs.skrining.edit', $skrining->id) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tambah Data Pemeriksaan
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Resep Obat -->
            @if($rujukan && $resepObats->count() > 0)
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-pills"></i>
                        Resep Obat
                    </h3>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="obat-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Obat</th>
                                    <th>Dosis</th>
                                    <th>Cara Penggunaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resepObats as $index => $resep)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $resep->resep_obat }}</strong>
                                    </td>
                                    <td>{{ $resep->dosis ?? '-' }}</td>
                                    <td>{{ $resep->penggunaan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Kesimpulan dan Rekomendasi Awal -->
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-file-medical-alt"></i>
                        Kesimpulan Skrining Awal (dari Puskesmas)
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="info-table">
                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Jumlah Resiko Sedang</span>
                                <span class="info-value">{{ $skrining->jumlah_resiko_sedang ?? '0' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Jumlah Resiko Tinggi</span>
                                <span class="info-value">{{ $skrining->jumlah_resiko_tinggi ?? '0' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Kesimpulan</span>
                                <span class="info-value">
                                    @php
                                        $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                        $badgeClass = match(strtolower($conclusion)) {
                                            'berisiko', 'beresiko' => 'badge-berisiko',
                                            'normal', 'aman' => 'badge-normal',
                                            'waspada', 'menengah' => 'badge-waspada',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($conclusion) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Rekomendasi Awal</span>
                                <span class="info-value">{{ $skrining->rekomendasi ?? '-' }}</span>
                            </div>
                        </div>

                        @if($skrining->catatan)
                        <div class="info-row full-width">
                            <div class="info-col">
                                <span class="info-key">Catatan dari Puskesmas</span>
                                <div class="catatan-box">
                                    <p>{{ $skrining->catatan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Button Actions -->
            <div class="form-actions">
                <a href="{{ route('rs.skrining.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('rs.skrining.edit', $skrining->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Edit Data Pemeriksaan
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #fafafa;
    font-size: 14px;
}

.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 220px;
    background: white;
    border-right: 1px solid #e8e8e8;
    padding: 1.5rem 1rem;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-header {
    margin-bottom: 2rem;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
}

.logo-text h3 {
    color: #e91e8c;
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.logo-text small {
    color: #888;
    font-size: 0.625rem;
    display: block;
    line-height: 1.2;
}

.menu-label {
    font-size: 0.625rem;
    color: #999;
    font-weight: 700;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.875rem;
    color: #666;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 0.375rem;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 0.875rem;
}

.menu-item:hover {
    background: #f8f8f8;
    color: #333;
}

.menu-item.active {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
}

.menu-item i {
    font-size: 1rem;
    width: 18px;
}

.mt-4 {
    margin-top: 1.5rem;
}

/* Main Content */
.main-content {
    flex: 1;
    background: #fafafa;
    min-height: 100vh;
}

.content-header {
    background: white;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e8e8e8;
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-back {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 1px solid #e8e8e8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: #e8e8e8;
}

.btn-edit {
    background: #e91e8c;
    color: white;
    border: none;
    padding: 0.625rem 1.125rem;
    border-radius: 8px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-edit:hover {
    background: #c2185b;
    transform: translateY(-1px);
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

/* Alert */
.alert {
    margin: 1.5rem 2rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert i {
    font-size: 1.125rem;
}

/* Content */
.skrining-content {
    padding: 1.5rem 2rem 3rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.card-header-simple {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.card-title-simple {
    font-size: 1rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.card-title-simple i {
    color: #e91e8c;
}

.card-body {
    padding: 0;
}

.card-body.p-0 {
    padding: 0;
}

/* Info Table */
.info-table {
    width: 100%;
}

.info-row {
    display: flex;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row.full-width .info-col {
    flex-direction: column;
    align-items: flex-start;
}

.info-col {
    display: flex;
    width: 100%;
}

.info-col > .info-key {
    flex: 0 0 35%;
    padding: 1rem 1.5rem;
    font-size: 0.8125rem;
    color: #666;
    background: #fafafa;
    font-weight: 600;
}

.info-col > .info-value {
    flex: 1;
    padding: 1rem 1.5rem;
    font-size: 0.8125rem;
    color: #1a1a1a;
    font-weight: 500;
}

/* Badge */
.badge {
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    text-align: center;
}

.badge-berisiko {
    background: #FEE2E2;
    color: #DC2626;
}

.badge-normal {
    background: #D1FAE5;
    color: #059669;
}

.badge-waspada {
    background: #FEF3C7;
    color: #D97706;
}

.badge-secondary {
    background: #f8f9fa;
    color: #6c757d;
}

.badge-success {
    background: #D1FAE5;
    color: #059669;
}

.badge-danger {
    background: #FEE2E2;
    color: #DC2626;
}

.badge-warning {
    background: #FEF3C7;
    color: #D97706;
}

.badge i {
    font-size: 0.75rem;
}

/* Catatan Box */
.catatan-box {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e8e8e8;
    margin-top: 0.5rem;
    width: 100%;
}

.catatan-box p {
    margin: 0;
    font-size: 0.8125rem;
    color: #666;
    line-height: 1.6;
}

.full-width .info-key {
    flex: 0 0 100% !important;
    border-bottom: 1px solid #f0f0f0;
}

.full-width .info-value {
    padding-top: 0 !important;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state i {
    color: #d0d0d0;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #888;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

/* Table Obat */
.table-responsive {
    overflow-x: auto;
}

.obat-table {
    width: 100%;
    border-collapse: collapse;
}

.obat-table thead {
    background: #fafafa;
}

.obat-table th {
    padding: 0.875rem 1.25rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.75rem;
    color: #888;
    border-bottom: 1px solid #e8e8e8;
    text-transform: uppercase;
}

.obat-table td {
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.8125rem;
    color: #333;
}

.obat-table tbody tr:last-child td {
    border-bottom: none;
}

.obat-table tbody tr:hover {
    background: #fafafa;
}

/* Text utilities */
.text-muted {
    color: #888;
    font-style: italic;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-secondary {
    background: white;
    color: #666;
    border: 1px solid #e8e8e8;
}

.btn-secondary:hover {
    background: #f8f9fa;
}

.btn-primary {
    background: #e91e8c;
    color: white;
}

.btn-primary:hover {
    background: #c2185b;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-wrapper {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .content-header {
        padding: 1rem 1.25rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .skrining-content {
        padding: 1rem 1.25rem 2rem;
    }
    
    .info-col {
        flex-direction: column;
    }
    
    .info-col > .info-key {
        flex: 0 0 auto;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-col > .info-value {
        padding-top: 0.75rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@endsection