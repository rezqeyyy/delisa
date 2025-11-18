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
        </div>

        <!-- Content -->
        <div class="skrining-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">
                        <div class="icon-wrapper">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <span class="title-main">Data Pasien Ibu Hamil</span>
                            <p class="title-subtitle">Data pasien yang melakukan pengecakan pada rumah sakit ini</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-badge"></i> NIK Pasien</th>
                                    <th><i class="fas fa-user"></i> Nama Pasien</th>
                                    <th><i class="fas fa-calendar"></i> Kedatangan</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Alamat</th>
                                    <th><i class="fas fa-phone"></i> No Telp</th>
                                    <th><i class="fas fa-clipboard-check"></i> Resiko</th>
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
                                            <span>{{ $skrining->pasien->user->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $skrining->created_at ? $skrining->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $skrining->pasien->PKabupaten ?? 'N/A' }}</td>
                                    <td>{{ $skrining->pasien->user->phone ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                            $badgeClass = match(strtolower($conclusion)) {
                                                'berisiko', 'beresiko' => 'badge-berisiko',
                                                'normal', 'aman' => 'badge-normal',
                                                'waspada', 'menengah' => 'badge-waspada',
                                                default => 'badge-secondary'
                                            };
                                            $displayText = ucfirst($conclusion);
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $displayText }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('rs.skrining.edit', $skrining->id) }}" class="btn-proses">
                                            <i class="fas fa-user-check"></i>
                                            Proses
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
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e8e8e8;
    position: sticky;
    top: 0;
    z-index: 100;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

/* Content */
.skrining-content {
    padding: 1.5rem 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.card-title {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
}

.icon-wrapper {
    width: 38px;
    height: 38px;
    background: white;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    flex-shrink: 0;
}

.card-title i {
    font-size: 1rem;
}

.title-main {
    font-weight: 600;
    font-size: 1rem;
    color: #1a1a1a;
    display: block;
    margin-bottom: 0.25rem;
}

.title-subtitle {
    font-size: 0.75rem;
    color: #888;
    margin: 0;
    line-height: 1.4;
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
    white-space: nowrap;
}

.data-table th i {
    margin-right: 0.375rem;
    color: #999;
    font-size: 0.75rem;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #f5f5f5;
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
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-block;
    text-align: center;
    min-width: 80px;
}

.badge-berisiko {
    background: #EF4444;
    color: white;
}

.badge-normal {
    background: #10B981;
    color: white;
}

.badge-waspada {
    background: #F59E0B;
    color: white;
}

.badge-secondary {
    background: #f8f9fa;
    color: #6c757d;
}

.btn-proses {
    background: white;
    border: 1px solid #e8e8e8;
    padding: 0.45rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.425rem;
    font-size: 0.75rem;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
    font-weight: 500;
    min-width: 90px;
}

.btn-proses:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
}

.btn-proses i {
    font-size: 0.875rem;
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
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
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
        padding: 1rem 1.25rem;
    }
    
    .skrining-content {
        padding: 1rem 1.25rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.25rem;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .badge {
        padding: 0.35rem 0.75rem;
        font-size: 0.65rem;
        min-width: 70px;
    }
    
    .btn-proses {
        padding: 0.4rem 0.75rem;
        font-size: 0.7rem;
        min-width: 80px;
    }
}
</style>
@endpush

@endsection