@extends('layouts.rs')

@section('title', 'Dashboard')

@section('content')

{{-- Success/Error Message --}}
@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    {{ session('error') }}
</div>
@endif

@if(session('info'))
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    {{ session('info') }}
</div>
@endif

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
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="menu-item logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                    </button>
                </form>
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
                    <div class="user-avatar">{{ substr(Auth::user()->name ?? 'R', 0, 1) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ Auth::user()->name ?? 'Rumah Sakit' }}</div>
                        <div class="user-email">{{ Auth::user()->email ?? 'rs@delisa.com' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="row g-3">
                <!-- Card Data Pasien Rujukan -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-hospital-user"></i>
                                </div>
                                <span>Data Pasien Rujukan</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <span>Setelah Melahirkan</span>
                                    <strong>{{ $pasienSetelahMelahirkan ?? 0 }}</strong>
                                </div>
                                <div class="info-item">
                                    <span>Beresiko</span>
                                    <strong>{{ $pasienRujukanBeresiko ?? 0 }}</strong>
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

                <!-- Card Data Pasien -->
                <div class="col-lg-6">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="icon-wrapper">
                                    <i class="fas fa-user-injured"></i>
                                </div>
                                <span>Data Pasien</span>
                            </div>
                            <button class="btn-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <span>Rujukan</span>
                                    <strong>{{ $pasienRujukan ?? 0 }}</strong>
                                </div>
                                <div class="info-item">
                                    <span>Non Rujukan</span>
                                    <strong>{{ $pasienNonRujukan ?? 0 }}</strong>
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

                <!-- Combined Card: Data Pasien Nifas & Pemantauan -->
                <div class="col-12">
                    <div class="row g-3">
                        <!-- Card Data Pasien Nifas -->
                        <div class="col-lg-6">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-baby"></i>
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

                        <!-- Card Pemantauan -->
                        <div class="col-lg-6">
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
                                        <div class="stat-item-small">
                                            <div class="stat-label">Sehat</div>
                                            <div class="stat-value-small">{{ $pemantauanSehat }}</div>
                                        </div>
                                        <div class="stat-divider-small"></div>
                                        <div class="stat-item-small">
                                            <div class="stat-label">Total Dirujuk</div>
                                            <div class="stat-value-small">{{ $pemantauanDirujuk }}</div>
                                        </div>
                                        <div class="stat-divider-small"></div>
                                        <div class="stat-item-small">
                                            <div class="stat-label">Meninggal</div>
                                            <div class="stat-value-small">{{ $pemantauanMeninggal }}</div>
                                        </div>
                                    </div>
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
                                <button class="btn-filter" onclick="toggleFilters()">
                                    <i class="fas fa-filter"></i>
                                    Filter
                                </button>
                                <button class="btn-action-green" onclick="alert('Fitur proses akan segera ditambahkan')">
                                    <i class="fas fa-check"></i>
                                    Proses
                                </button>
                                <button class="btn-action-pink" onclick="alert('Fitur tambah akun akan segera ditambahkan')">
                                    <i class="fas fa-plus"></i>
                                    Tambah Akun
                                </button>
                                <button class="btn-action-blue" onclick="location.reload()">
                                    <i class="fas fa-sync"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>

                        <!-- Filter Panel -->
                        <div id="filterPanel" class="filter-panel" style="display: none;">
                            <div class="filter-content">
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="nik">
                                        <i class="fas fa-id-card"></i>
                                        <span>NIK Pasien</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="nama">
                                        <i class="fas fa-user"></i>
                                        <span>Nama Pasien</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="tanggal">
                                        <i class="fas fa-calendar"></i>
                                        <span>Tanggal</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="alamat">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Alamat</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="telp">
                                        <i class="fas fa-phone"></i>
                                        <span>No Telp</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="kesimpulan">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Kesimpulan</span>
                                    </label>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">
                                        <input type="checkbox" name="filter[]" value="detail">
                                        <i class="fas fa-eye"></i>
                                        <span>View Detail</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th data-col="checkbox">
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th data-col="nik"><i class="fas fa-id-card"></i> NIK Pasien</th>
                                            <th data-col="nama"><i class="fas fa-user"></i> Nama Pasien</th>
                                            <th data-col="tanggal"><i class="fas fa-calendar"></i> Tanggal</th>
                                            <th data-col="alamat"><i class="fas fa-map-marker-alt"></i> Alamat</th>
                                            <th data-col="telp"><i class="fas fa-phone"></i> No Telp</th>
                                            <th data-col="kesimpulan"><i class="fas fa-exclamation-triangle"></i> Kesimpulan</th>
                                            <th data-col="detail"><i class="fas fa-cog"></i> Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pasienPreEklampsia as $pasien)
                                        <tr>
                                            <td data-col="checkbox">
                                                <input type="checkbox" name="selected[]" value="{{ $pasien['id'] }}">
                                            </td>
                                            <td data-col="nik">{{ $pasien['id_pasien'] }}</td>
                                            <td data-col="nama">
                                                <div class="user-cell">
                                                    <i class="fas fa-user-circle"></i>
                                                    <span>{{ $pasien['nama'] }}</span>
                                                </div>
                                            </td>
                                            <td data-col="tanggal">{{ $pasien['tanggal'] }}</td>
                                            <td data-col="alamat">{{ $pasien['status'] }}</td>
                                            <td data-col="telp">{{ $pasien['no_telp'] }}</td>
                                            <td data-col="kesimpulan">
                                                @if($pasien['klasifikasi'] == 'Beresiko')
                                                    <span class="badge badge-danger">Beresiko</span>
                                                @elseif($pasien['klasifikasi'] == 'Aman')
                                                    <span class="badge badge-success">Aman</span>
                                                @elseif($pasien['klasifikasi'] == 'Menengah')
                                                    <span class="badge badge-warning">Waspada</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $pasien['klasifikasi'] }}</span>
                                                @endif
                                            </td>
                                            <td data-col="detail">
                                                <div class="action-buttons">
                                                    <a href="{{ route('rs.pasien.show', $pasien['id']) }}" class="btn-view">
                                                        <i class="fas fa-eye"></i>
                                                        View
                                                    </a>
                                                    <form method="POST" action="{{ route('rs.dashboard.proses-nifas', $pasien['id']) }}" 
                                                        onsubmit="return confirm('Apakah Anda yakin ingin memproses pasien ini ke data nifas?')" 
                                                        style="display: inline; margin: 0;">
                                                        @csrf
                                                        <button type="submit" class="btn-proses-nifas">
                                                            <i class="fas fa-check-circle"></i>
                                                            Proses
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
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
    display: flex;
    flex-direction: column;
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

.sidebar-menu {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.menu-section {
    margin-bottom: 1.5rem;
}

.menu-section:last-child {
    margin-top: auto;
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
    width: 100%;
    text-align: left;
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

/* Logout Button */
.logout-btn {
    background: none;
    border: none;
    cursor: pointer;
}

.logout-btn:hover {
    background: #fff5f5 !important;
    color: #dc3545 !important;
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

.user-details {
    text-align: left;
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
.stats-row-three {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    gap: 0.75rem;
    align-items: center;
}

.stat-item-small {
    text-align: center;
}

.stat-label {
    font-size: 0.75rem;
    color: #888;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stat-value-small {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.stat-divider-small {
    width: 1px;
    height: 40px;
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
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-filter {
    background: white;
    color: #666;
    border: 1px solid #e8e8e8;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-filter:hover {
    background: #f8f9fa;
    border-color: #666;
}

.btn-action-green {
    background: #10B981;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-action-green:hover {
    background: #059669;
}

.btn-action-pink {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-action-pink:hover {
    opacity: 0.9;
}

.btn-action-blue {
    background: #3B82F6;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-action-blue:hover {
    background: #2563EB;
}

/* Filter Panel */
.filter-panel {
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
    transition: all 0.3s ease;
}

.filter-content {
    padding: 1rem 1.25rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}

.filter-group {
    display: flex;
    align-items: center;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.8125rem;
    color: #666;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.filter-label:hover {
    background: white;
}

.filter-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.filter-label i {
    font-size: 0.875rem;
    color: #888;
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

.data-table input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
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

/* Action Buttons Container */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
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
    background: #FEE2E2;
    color: #DC2626;
}

.badge-success {
    background: #D1FAE5;
    color: #059669;
}

.badge-warning {
    background: #FEF3C7;
    color: #D97706;
}

.badge-secondary {
    background: #f8f9fa;
    color: #6c757d;
}

/* Button View */
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
    text-decoration: none;
}

.btn-view:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
}

.btn-view i {
    font-size: 0.75rem;
}

/* Button Proses Nifas */
.btn-proses-nifas {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    border: none;
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: white;
    transition: all 0.2s ease;
    font-weight: 600;
}

.btn-proses-nifas:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-proses-nifas i {
    font-size: 0.75rem;
}

/* Alert Notifications */
.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease;
    min-width: 300px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.alert-success {
    background: #10B981;
    color: white;
}

.alert-danger {
    background: #EF4444;
    color: white;
}

.alert-info {
    background: #3B82F6;
    color: white;
}

.alert i {
    font-size: 1.125rem;
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
    
    .stats-row-three {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-divider-small {
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
        width: 100%;
        justify-content: flex-start;
    }

    .action-buttons {
        flex-direction: column;
        width: 100%;
    }

    .btn-view,
    .btn-proses-nifas {
        width: 100%;
        justify-content: center;
    }

    .alert {
        right: 10px;
        left: 10px;
        top: 10px;
        min-width: auto;
    }

    .filter-content {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .dashboard-content {
        padding: 0.875rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .stat-value-small {
        font-size: 1.25rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
    }

    .card-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn-filter,
    .btn-action-green,
    .btn-action-pink,
    .btn-action-blue {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Select All Checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Filter functionality
    const filterCheckboxes = document.querySelectorAll('.filter-label input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const col = this.value;
            const isChecked = this.checked;
            
            // Toggle column visibility
            const colElements = document.querySelectorAll(`[data-col="${col}"]`);
            colElements.forEach(el => {
                el.style.display = isChecked ? 'none' : '';
            });
        });
    });
});

// Toggle Filter Panel
function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}
</script>
@endpush

@endsection