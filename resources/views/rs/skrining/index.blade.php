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
            <h2 class="page-title">List Skrining Ibu Hamil</h2>
        </div>

        <!-- Content -->
        <div class="skrining-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-clipboard-list"></i>
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
                                            <span>{{ $skrining->pasien->nik ?? 'Anep Dadang' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $skrining->riwayatKehamilanGpa->kehamilan ?? 'N/A' }}</td>
                                    <td>{{ $skrining->pasien->PKabupaten ?? 'Baji' }}</td>
                                    <td>{{ $skrining->pasien->no_jkn ?? '0000000000' }}</td>
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
                                    <td colspan="7" class="text-center">Tidak ada data skrining</td>
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
    padding: 2rem;
    border-bottom: 1px solid #e0e0e0;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.skrining-content {
    padding: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
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

.card-body {
    padding: 2rem;
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
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #666;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #f5f5f5;
    border-color: #d0d0d0;
    color: #333;
}

.text-center {
    text-align: center;
}

/* Pagination */
.card-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #f0f0f0;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        display: none;
    }
}
</style>
@endpush

@endsection