@extends('layouts.rs')

@section('title', 'Detail Pasien')

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
                <a href="{{ route('rs.skrining.index') }}" class="menu-item">
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
                <a href="{{ route('rs.dashboard') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <h2 class="page-title">Detail Pasien</h2>
            </div>
        </div>

        <!-- Content -->
        <div class="detail-content">
            <!-- Card Profile -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        {{ substr($pasien->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div class="profile-info">
                        <h3>{{ $pasien->user->name ?? 'Nama Pasien' }}</h3>
                        <p>NIK: {{ $pasien->nik ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Data Pribadi -->
                <div class="col-lg-6">
                    <div class="detail-card">
                        <div class="card-header-detail">
                            <i class="fas fa-user"></i>
                            <h4>Data Pribadi</h4>
                        </div>
                        <div class="card-body-detail">
                            <div class="detail-item">
                                <span class="detail-label">Nama Lengkap</span>
                                <span class="detail-value">{{ $pasien->user->name ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">NIK</span>
                                <span class="detail-value">{{ $pasien->nik ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tempat Lahir</span>
                                <span class="detail-value">{{ $pasien->tempat_lahir ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tanggal Lahir</span>
                                <span class="detail-value">
                                    @if($pasien->tanggal_lahir)
                                        {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Status Perkawinan</span>
                                <span class="detail-value">{{ $pasien->status_perkawinan ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Pekerjaan</span>
                                <span class="detail-value">{{ $pasien->pekerjaan ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Pendidikan</span>
                                <span class="detail-value">{{ $pasien->pendidikan ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Golongan Darah</span>
                                <span class="detail-value">{{ $pasien->golongan_darah ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Kontak & Alamat -->
                <div class="col-lg-6">
                    <div class="detail-card">
                        <div class="card-header-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <h4>Kontak & Alamat</h4>
                        </div>
                        <div class="card-body-detail">
                            <div class="detail-item">
                                <span class="detail-label">No. Telepon</span>
                                <span class="detail-value">{{ $pasien->no_telepon ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">No. JKN</span>
                                <span class="detail-value">{{ $pasien->no_jkn ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Provinsi</span>
                                <span class="detail-value">{{ $pasien->PProvinsi ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Kabupaten/Kota</span>
                                <span class="detail-value">{{ $pasien->PKabupaten ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Kecamatan</span>
                                <span class="detail-value">{{ $pasien->PKecamatan ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Kelurahan/Wilayah</span>
                                <span class="detail-value">{{ $pasien->PWilayah ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">RT / RW</span>
                                <span class="detail-value">
                                    {{ $pasien->rt ? 'RT ' . $pasien->rt : '-' }} / 
                                    {{ $pasien->rw ? 'RW ' . $pasien->rw : '-' }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Kode Pos</span>
                                <span class="detail-value">{{ $pasien->kode_pos ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pelayanan -->
                    <div class="detail-card mt-3">
                        <div class="card-header-detail">
                            <i class="fas fa-hospital"></i>
                            <h4>Data Pelayanan</h4>
                        </div>
                        <div class="card-body-detail">
                            <div class="detail-item">
                                <span class="detail-label">Pelayanan</span>
                                <span class="detail-value">{{ $pasien->PPelayanan ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Karakteristik</span>
                                <span class="detail-value">{{ $pasien->PKarakteristik ?? '-' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Pembiayaan Kesehatan</span>
                                <span class="detail-value">{{ $pasien->pembiayaan_kesehatan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('rs.dashboard') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
                <button onclick="window.print()" class="btn-primary">
                    <i class="fas fa-print"></i>
                    Cetak Data
                </button>
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

/* Sidebar - Copy dari dashboard.blade.php */
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
}

.content-header {
    background: white;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e8e8e8;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-back {
    background: white;
    border: 1px solid #e0e0e0;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    color: #666;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: #f5f5f5;
    border-color: #d0d0d0;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

/* Detail Content */
.detail-content {
    padding: 1.5rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.profile-card {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(233, 30, 140, 0.2);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: #e91e8c;
}

.profile-info h3 {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.profile-info p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    margin: 0;
}

/* Grid */
.row {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1rem;
}

.col-lg-6 {
    grid-column: span 6;
}

.g-3 {
    gap: 1rem;
}

/* Detail Card */
.detail-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.card-header-detail {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-header-detail i {
    color: #e91e8c;
    font-size: 1.125rem;
}

.card-header-detail h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

.card-body-detail {
    padding: 1.5rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.875rem 0;
    border-bottom: 1px solid #f5f5f5;
}

.detail-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-item:first-child {
    padding-top: 0;
}

.detail-label {
    font-size: 0.875rem;
    color: #888;
    font-weight: 500;
    flex: 0 0 45%;
}

.detail-value {
    font-size: 0.875rem;
    color: #333;
    font-weight: 600;
    flex: 1;
    text-align: right;
}

.mt-3 {
    margin-top: 1rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e8e8e8;
}

.btn-primary {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.875rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.9375rem;
    text-decoration: none;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.btn-secondary {
    background: white;
    color: #666;
    border: 1px solid #e8e8e8;
    padding: 0.875rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.9375rem;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #f8f9fa;
}

/* Print Styles */
@media print {
    .sidebar,
    .content-header,
    .action-buttons {
        display: none !important;
    }
    
    .main-content {
        margin: 0;
        padding: 0;
    }
    
    .detail-content {
        padding: 0;
    }
}

/* Responsive */
@media (max-width: 992px) {
    .col-lg-6 {
        grid-column: span 12;
    }
}

@media (max-width: 768px) {
    .dashboard-wrapper {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .detail-content {
        padding: 1rem 1.25rem;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-info h3 {
        font-size: 1.25rem;
    }
    
    .detail-label,
    .detail-value {
        font-size: 0.8125rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@endsection