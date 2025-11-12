@extends('layouts.rs')

@section('title', 'List Skrining Ibu Hamil')

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
            <h2 class="page-title">List Skrining Ibu Hamil</h2>
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

        <!-- Content -->
        <div class="skrining-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">
                        <div class="icon-wrapper">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <span>Data Pasien Ibu Hamil</span>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-badge"></i> NIK Pasien</th>
                                    <th><i class="fas fa-user"></i> Nama Pasien</th>
                                    <th><i class="fas fa-baby"></i> Kehamilan</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Alamat</th>
                                    <th><i class="fas fa-phone"></i> No Telp</th>
                                    <th><i class="fas fa-exclamation-triangle"></i> Hasil</th>
                                    <th><i class="fas fa-eye"></i> View Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($skrinings as $skrining)
                                <tr>
                                    <td>{{ $skrining->pasien->nik ?? 'N/A' }}</td>
                                    <td>
                                        <div class="user-cell">
                                            <i class="fas fa-user-circle"></i>
                                            <span>{{ $skrining->pasien->nama ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $skrining->riwayatKehamilanGpa->kehamilan ?? 'N/A' }}</td>
                                    <td>{{ $skrining->pasien->PKabupaten ?? 'N/A' }}</td>
                                    <td>{{ $skrining->pasien->no_jkn ?? 'N/A' }}</td>
                                    <td>
                                        @if($skrining->status_pre_eklampsia == 'Beresiko' || $skrining->kesimpulan == 'Beresiko')
                                            <span class="badge badge-danger">Beresiko</span>
                                        @elseif($skrining->status_pre_eklampsia == 'Aman' || $skrining->kesimpulan == 'Aman')
                                            <span class="badge badge-success">Aman</span>
                                        @elseif($skrining->status_pre_eklampsia == 'Menengah' || $skrining->kesimpulan == 'Menengah')
                                            <span class="badge badge-warning">Menengah</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $skrining->kesimpulan ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('rs.skrining.show', $skrining->id) }}" class="btn-view">
                                            <i class="fas fa-eye"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Tidak ada data skrining</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if($skrinings->hasPages())
                <div class="card-footer">
                    <div class="pagination-wrapper">
                        {{ $skrinings->links() }}
                    </div>
                </div>
                @endif
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

/* Sidebar - Sama seperti Dashboard */
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

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin: 0;
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

/* Profile Dropdown - SESUAI TEMPLATE DINKES */
.profile-wrapper {
    position: relative;
}

.profile-btn {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    background: white;
    border: 1px solid #e8e8e8;
    border-radius: 999px;
    padding: 0.25rem 0.75rem 0.25rem 0.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.profile-btn:hover {
    background: #f8f9fa;
}

.user-avatar {
    width: 32px;
    height: 32px;
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
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.2;
}

.user-name {
    font-weight: 600;
    font-size: 0.8125rem;
    color: #1D1D1D;
}

.user-email {
    font-size: 0.6875rem;
    color: #7C7C7C;
    margin-top: -0.125rem;
}

.profile-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    min-width: 220px;
    overflow: hidden;
    z-index: 9999;
    border: 1px solid #E9E9E9;
}

.profile-menu.hidden {
    display: none;
}

.profile-menu-header {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #F0F0F0;
}

.profile-menu-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1D1D1D;
    margin: 0 0 0.25rem 0;
}

.profile-menu-email {
    font-size: 0.75rem;
    color: #7C7C7C;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-menu-logout {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #333;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.profile-menu-logout:hover {
    background: #F9F9F9;
}

/* Content */
.skrining-content {
    padding: 1.25rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    overflow: hidden;
    border: 1px solid #f0f0f0;
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

.card-body {
    padding: 1.25rem;
}

.card-body.p-0 {
    padding: 0;
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
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #666;
    text-decoration: none;
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

/* Pagination */
.card-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #f0f0f0;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
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
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 576px) {
    .skrining-content {
        padding: 0.875rem;
    }
    
    .card-body {
        padding: 1rem;
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
// Profile Dropdown Toggle - SESUAI TEMPLATE DINKES
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    
    if (profileBtn && profileMenu) {
        // Toggle dropdown saat klik button
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });
        
        // Close dropdown saat klik di luar
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
        
        // Prevent dropdown dari close saat klik di dalam menu
        profileMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
@endpush

@endsection