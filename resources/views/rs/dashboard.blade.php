@extends('layouts.rs')

@section('title', 'Dashboard')

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
                <a href="{{ route('rs.dashboard') }}" class="menu-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('rs.skrining.index') }}" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Skrining</span>
                </a>
                <a href="#" class="menu-item">
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
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            <div class="header-actions">
                <button class="icon-btn"><i class="fas fa-cog"></i></button>
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <div class="user-info">
                    <div class="user-avatar">{{ substr(Auth::user()->name ?? 'H', 0, 1) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ Auth::user()->name ?? 'Nama Bidan' }}</div>
                        <div class="user-email">{{ Auth::user()->email ?? 'email Bidan' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="row g-4">
                <!-- Card Daerah Asal Pasien -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Daerah Asal Pasien</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="stats-row">
                                <div class="stat-item">
                                    <div class="stat-label">Depok</div>
                                    <div class="stat-value">{{ $pasienDepok }}</div>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <div class="stat-label">Non Depok</div>
                                    <div class="stat-value">{{ $pasienNonDepok }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Resiko Eklampsia -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Resiko Eklampsia</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <span>Pasien Normal</span>
                                    <strong>{{ $pasienNormal }}</strong>
                                </div>
                                <div class="info-item">
                                    <span>Pasien Beresiko Eklampsia</span>
                                    <strong>{{ $pasienBeresikoEklampsia }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Pasien Hadir -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-clock"></i>
                                <span>Pasien Hadir</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <span>Pasien Hadir</span>
                                    <strong>{{ $pasienHadir }}</strong>
                                </div>
                                <div class="info-item">
                                    <span>Pasien Tidak Hadir</span>
                                    <strong>{{ $pasienTidakHadir }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Data Pasien Nifas -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-users"></i>
                                <span>Data Pasien Nifas</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <span>Total Pasien Nifas</span>
                                    <strong>{{ $totalPasienNifas }}</strong>
                                </div>
                                <div class="info-item">
                                    <span>Sudah KF1</span>
                                    <strong>{{ $sudahKF1 }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Pemantauan -->
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-chart-line"></i>
                                <span>Pemantauan</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="stats-row-three">
                                <div class="stat-item">
                                    <div class="stat-label">Sehat</div>
                                    <div class="stat-value">{{ $pemantauanSehat }}</div>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <div class="stat-label">Total Dirujuk</div>
                                    <div class="stat-value">{{ $pemantauanDirujuk }}</div>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <div class="stat-label">Meninggal</div>
                                    <div class="stat-value">{{ $pemantauanMeninggal }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Data Pasien Pre Eklampsia -->
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-file-alt"></i>
                                <span>Data Pasien Pre Eklampsia</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Tambah Akun
                                </button>
                                <button class="btn-secondary">
                                    <i class="fas fa-sync"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-id-badge"></i> ID Pasien</th>
                                            <th><i class="fas fa-user"></i> Nama Pasien</th>
                                            <th><i class="fas fa-calendar"></i> Tanggal</th>
                                            <th><i class="fas fa-map-marker-alt"></i> Alamat</th>
                                            <th><i class="fas fa-phone"></i> No Telp</th>
                                            <th><i class="fas fa-exclamation-triangle"></i> Klasifikasi</th>
                                            <th><i class="fas fa-eye"></i> View Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pasienPreEklampsia as $pasien)
                                        <tr>
                                            <td>{{ $pasien['id_pasien'] }}</td>
                                            <td>
                                                <div class="user-cell">
                                                    <i class="fas fa-user-circle"></i>
                                                    <span>{{ $pasien['nama'] }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $pasien['tanggal'] }}</td>
                                            <td>{{ $pasien['status'] }}</td>
                                            <td>{{ $pasien['no_telp'] }}</td>
                                            <td>
                                                @if($pasien['klasifikasi'] == 'Beresiko')
                                                    <span class="badge badge-danger">Beresiko</span>
                                                @elseif($pasien['klasifikasi'] == 'Aman')
                                                    <span class="badge badge-success">Aman</span>
                                                @elseif($pasien['klasifikasi'] == 'Menengah')
                                                    <span class="badge badge-warning">Menengah</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $pasien['klasifikasi'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn-view">
                                                    <i class="fas fa-eye"></i>
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
}

.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background: white;
    border-right: 1px solid #e0e0e0;
    padding: 2rem 1.5rem;
}

.sidebar-header {
    margin-bottom: 3rem;
}

.logo {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.logo-text h3 {
    color: #e91e8c;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
}

.logo-text small {
    color: #999;
    font-size: 0.75rem;
}

.menu-label {
    font-size: 0.7rem;
    color: #999;
    font-weight: 600;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 1rem;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    color: #666;
    text-decoration: none;
    border-radius: 12px;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
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
    font-size: 1.2rem;
    width: 24px;
}

/* Main Content */
.main-content {
    flex: 1;
    background: #fafafa;
}

.content-header {
    background: white;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f5f5f5;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    width: 300px;
}

.search-box i {
    color: #999;
}

.search-box input {
    border: none;
    background: none;
    outline: none;
    width: 100%;
    font-size: 0.9rem;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #f5f5f5;
    border-radius: 8px;
    cursor: pointer;
    color: #666;
    transition: all 0.3s ease;
}

.icon-btn:hover {
    background: #e0e0e0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-left: 1rem;
    border-left: 1px solid #e0e0e0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #333;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #333;
}

.user-email {
    font-size: 0.75rem;
    color: #999;
}

/* Dashboard Content */
.dashboard-content {
    padding: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.card-header {
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
}

.card-title i {
    color: #666;
    font-size: 1.25rem;
}

.btn-arrow {
    width: 36px;
    height: 36px;
    border: 1px solid #e0e0e0;
    background: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-arrow:hover {
    background: #f5f5f5;
    transform: translateX(4px);
}

.card-body {
    padding: 2rem;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 2rem;
    align-items: center;
}

.stats-row-three {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    gap: 2rem;
    align-items: center;
}

.stat-item {
    text-align: center;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
}

.stat-value {
    font-size: 4rem;
    font-weight: bold;
    color: #333;
}

.stat-divider {
    width: 1px;
    height: 80px;
    background: #e0e0e0;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item span {
    color: #666;
    font-size: 0.95rem;
}

.info-item strong {
    font-size: 1.1rem;
    color: #333;
}

/* Card Actions */
.card-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(233, 30, 140, 0.3);
}

.btn-secondary {
    background: white;
    color: #666;
    border: 1px solid #e0e0e0;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #f5f5f5;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #fafafa;
}

.data-table th {
    padding: 1.25rem 1.5rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    color: #666;
    border-bottom: 1px solid #e0e0e0;
}

.data-table td {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    font-size: 0.9rem;
}

.data-table tbody tr:hover {
    background: #fafafa;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-cell i {
    font-size: 1.5rem;
    color: #999;
}

/* Badges */
.badge {
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.badge-danger {
    background: #ffebee;
    color: #c62828;
}

.badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-warning {
    background: #fff8e1;
    color: #f57f17;
}

.badge-secondary {
    background: #f5f5f5;
    color: #666;
}

.btn-view {
    background: white;
    border: 1px solid #e0e0e0;
    padding: 0.5rem 1.25rem;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #666;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #f5f5f5;
    border-color: #d0d0d0;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        display: none;
    }
    
    .stats-row,
    .stats-row-three {
        grid-template-columns: 1fr;
    }
    
    .stat-divider {
        display: none;
    }
}
</style>
@endpush

@endsection