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
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            <div class="header-actions">
                <button class="icon-btn"><i class="fas fa-cog"></i></button>
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <div class="user-info">
                    <!-- Profile dropdown -->
                    <div id="profileWrapper" class="profile-wrapper">
                        <button id="profileBtn" class="profile-btn">
                            <div class="user-avatar">{{ substr(Auth::user()->name ?? 'H', 0, 1) }}</div>
                            
                            <div class="user-details">
                                <div class="user-name">{{ Auth::user()->name ?? 'Nama Bidan' }}</div>
                                <div class="user-email">{{ Auth::user()->email ?? 'email Bidan' }}</div>
                            </div>
                            
                            <i class="fas fa-chevron-down" style="font-size: 0.875rem; color: #666; opacity: 0.7;"></i>
                        </button>

                        <div id="profileMenu" class="profile-menu hidden">
                            <div class="profile-menu-header">
                                <p class="profile-menu-name">{{ Auth::user()->name ?? 'Nama Bidan' }}</p>
                                <p class="profile-menu-email">{{ Auth::user()->email ?? 'email Bidan' }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="profile-menu-logout">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="row g-3">
                <!-- Card Daerah Asal Pasien -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
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
                                <div class="icon-wrapper">
                                    <i class="fas fa-file-medical"></i>
                                </div>
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

                <!-- Card Data Pasien Nifas -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
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

                <!-- Card Pasien Hadir -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
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

                <!-- Card Pemantauan -->
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
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
                                <div class="icon-wrapper">
                                    <i class="fas fa-file-medical-alt"></i>
                                </div>
                                <span>Data Pasien Pre Eklampsia</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-primary" onclick="alert('Fitur akan segera ditambahkan')">
                                    <i class="fas fa-plus"></i>
                                    Tambah Akun
                                </button>
                                <button class="btn-secondary" onclick="location.reload()">
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
                                            <th><i class="fas fa-exclamation-triangle"></i> Kesimpulan</th>
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
                                                    <span class="badge badge-success">Sehat</span>
                                                @elseif($pasien['klasifikasi'] == 'Menengah')
                                                    <span class="badge badge-warning">Waspada</span>
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
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p class="mb-0">Tidak ada data pasien</p>
                                            </td>
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
}

.content-header {
    background: white;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e8e8e8;
    position: sticky;
    top: 0;
    z-index: 100;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    background: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    width: 280px;
    border: 1px solid #e8e8e8;
}

.search-box i {
    color: #999;
    font-size: 0.875rem;
}

.search-box input {
    border: none;
    background: none;
    outline: none;
    width: 100%;
    font-size: 0.875rem;
    color: #333;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.icon-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.icon-btn:hover {
    background: #e8e8e8;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding-left: 0.75rem;
    border-left: 1px solid #e8e8e8;
}

.dropdown-wrapper {
    position: relative;
    margin-left: 0.5rem;
}

.btn-dropdown {
    width: 36px;
    height: 36px;
    border: 1px solid #e8e8e8;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: #666;
    font-size: 0.875rem;
}

.btn-dropdown:hover {
    background: #f8f9fa;
    border-color: #e91e8c;
    color: #e91e8c;
}

.btn-dropdown.active {
    background: #e91e8c;
    border-color: #e91e8c;
    color: white;
}

.btn-dropdown i {
    transition: transform 0.2s ease;
}

.btn-dropdown.active i {
    transform: rotate(180deg);
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #333;
    font-size: 0.875rem;
}

.user-name {
    font-weight: 600;
    font-size: 0.8125rem;
    color: #333;
    line-height: 1.2;
}

.user-email {
    font-size: 0.6875rem;
    color: #888;
    line-height: 1.2;
}

/* Dropdown Menu */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.2s ease;
    z-index: 9999;
    overflow: hidden;
    display: none;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    display: block;
}

.dropdown-header {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.dropdown-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 0.25rem 0;
}

.dropdown-email {
    font-size: 0.75rem;
    color: #7c7c7c;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #333;
}

.dropdown-item.logout-item {
    color: #dc3545;
}

.dropout-item.logout-item:hover {
    background: #fff5f5;
}

.dropdown-item i {
    font-size: 0.875rem;
    width: 16px;
}

.dropdown-divider {
    height: 1px;
    background: #e8e8e8;
    margin: 0.25rem 0;
}

/* Dashboard Content */
.dashboard-content {
    padding: 1.25rem;
}

.row {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1rem;
}

.col-lg-6 {
    grid-column: span 6;
}

.col-12 {
    grid-column: span 12;
}

.g-3 {
    gap: 1rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: all 0.2s ease;
    border: 1px solid #f0f0f0;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.card-header {
    padding: 1rem 1.25rem;
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
    font-size: 0.9375rem;
    color: #333;
}

.icon-wrapper {
    width: 34px;
    height: 34px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.card-title i {
    font-size: 0.9375rem;
}

.btn-arrow {
    width: 30px;
    height: 30px;
    border: 1px solid #e8e8e8;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: #666;
    font-size: 0.8125rem;
}

.btn-arrow:hover {
    background: #f8f9fa;
    border-color: #e91e8c;
    color: #e91e8c;
}

.card-body {
    padding: 1.25rem;
}

.card-body.p-0 {
    padding: 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 1.25rem;
    align-items: center;
}

.stats-row-three {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    gap: 1.25rem;
    align-items: center;
}

.stat-item {
    text-align: center;
}

.stat-label {
    font-size: 0.8125rem;
    color: #888;
    margin-bottom: 0.625rem;
    font-weight: 500;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.stat-divider {
    width: 1px;
    height: 60px;
    background: linear-gradient(to bottom, transparent, #e8e8e8, transparent);
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.875rem;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item span {
    color: #666;
    font-size: 0.8125rem;
    font-weight: 500;
}

.info-item strong {
    font-size: 1rem;
    color: #333;
    font-weight: 700;
}

/* Card Actions */
.card-actions {
    display: flex;
    gap: 0.625rem;
}

.btn-primary {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.5625rem 1.125rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(233, 30, 140, 0.3);
}

.btn-secondary {
    background: white;
    color: #666;
    border: 1px solid #e8e8e8;
    padding: 0.5625rem 1.125rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-secondary:hover {
    background: #f8f9fa;
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
    padding: 0.875rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.6875rem;
    color: #888;
    border-bottom: 1px solid #e8e8e8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.data-table th i {
    margin-right: 0.375rem;
    color: #999;
    font-size: 0.75rem;
}

.data-table td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    font-size: 0.8125rem;
}

.data-table tbody tr {
    transition: all 0.2s ease;
}

.data-table tbody tr:hover {
    background: #fafafa;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-cell i {
    font-size: 1.25rem;
    color: #ddd;
}

/* Badges */
.badge {
    padding: 0.375rem 0.875rem;
    border-radius: 16px;
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-block;
}

.badge-danger {
    background: #fee;
    color: #dc3545;
}

.badge-success {
    background: #e8f5e9;
    color: #28a745;
}

.badge-warning {
    background: #fff8e1;
    color: #ffc107;
}

.badge-secondary {
    background: #f8f9fa;
    color: #6c757d;
}

.btn-view {
    background: white;
    border: 1px solid #e8e8e8;
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #666;
    transition: all 0.2s ease;
    font-weight: 500;
}

.btn-view:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
}

.btn-view i {
    font-size: 0.75rem;
}

.text-center {
    text-align: center;
}

.py-4 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.text-muted {
    color: #999;
}

.mb-0 {
    margin-bottom: 0;
}

.mb-2 {
    margin-bottom: 0.5rem;
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
    
    .stats-row,
    .stats-row-three {
        grid-template-columns: 1fr;
    }
    
    .stat-divider {
        display: none;
    }
    
    .search-box {
        width: 100%;
    }
    
    .content-header {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .card-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .dashboard-content {
        padding: 0.875rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .stat-value {
        font-size: 2rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('dropdownButton');
    const dropdown = document.getElementById('userDropdown');
    
    if (button && dropdown) {
        // Click button to toggle
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
            button.classList.toggle('active');
            console.log('Dropdown toggled!'); // Debug
        });
        
        // Click outside to close
        document.addEventListener('click', function(e) {
            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                button.classList.remove('active');
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    } else {
        console.error('Button or dropdown not found!'); // Debug
    }
});
</script>
@endpush

@endsection