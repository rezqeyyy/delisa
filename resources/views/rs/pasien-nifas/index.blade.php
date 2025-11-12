@extends('layouts.rs')

@section('title', 'List Pasien Nifas')

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
                <a href="{{ route('rs.pasien-nifas.index') }}" class="menu-item active">
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
            <div class="page-title">
                <i class="fas fa-exchange-alt"></i>
                <h1>List Pasien Nifas</h1>
            </div>
            <div class="header-actions">
                <button class="icon-btn"><i class="fas fa-cog"></i></button>
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <div class="user-info" onclick="toggleDropdown()">
                    <div class="user-avatar">{{ substr(Auth::user()->name ?? 'H', 0, 1) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ Auth::user()->name ?? 'Nama Bidan' }}</div>
                        <div class="user-email">{{ Auth::user()->email ?? 'email Bidan' }}</div>
                    </div>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                    
                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <!-- Main Content Area -->
        <div class="page-content">
            <div class="content-card">
                <div class="card-header">
                    <div class="card-title-section">
                        <div class="icon-wrapper">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div>
                            <h2>Data Pasien Nifas</h2>
                            <p class="card-subtitle">Data pasien yang sedang nifas pada puskesmas ini</p>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-primary" onclick="tambahAkun()">
                            <i class="fas fa-plus"></i>
                            Tambah Akun
                        </button>
                        <button class="btn-download" onclick="downloadPDF()">
                            <i class="fas fa-download"></i>
                            Download Data
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-badge"></i> ID Pasien</th>
                                    <th><i class="fas fa-user"></i> Nama Pasien</th>
                                    <th><i class="fas fa-calendar"></i> Tanggal</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Alamat</th>
                                    <th><i class="fas fa-phone"></i> No Telp</th>
                                    <th><i class="fas fa-heartbeat"></i> Penanganan</th>
                                    <th><i class="fas fa-cog"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pasienNifas as $pasien)
                                <tr>
                                    <td>{{ $pasien->id }}</td>
                                    <td>
                                        <div class="user-cell">
                                            <i class="fas fa-user-circle"></i>
                                            <span>{{ $pasien->pasien->nik ?? 'Asep Dadang' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($pasien->tanggal_mulai_nifas)->format('d/m/Y') }}</td>
                                    <td>{{ $pasien->rs->PProvinsi ?? 'N/A' }}</td>
                                    <td>{{ $pasien->pasien->no_jkn ?? '0000000000' }}</td>
                                    <td>
                                        @if($pasien->status_kunjungan == 'Aman')
                                            <span class="badge badge-success">Aman</span>
                                        @elseif($pasien->status_kunjungan == 'Beresiko')
                                            <span class="badge badge-danger">Telat</span>
                                        @elseif($pasien->status_kunjungan == 'Menengah')
                                            <span class="badge badge-warning">Waspada</span>
                                        @else
                                            <span class="badge badge-success">Aman</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('rs.pasien-nifas.show', $pasien->id) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                            Lihat Data Nifas
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0">Tidak ada data pasien nifas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pasienNifas->hasPages())
                    <div class="pagination-wrapper">
                        {{ $pasienNifas->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
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

/* Header */
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
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title i {
    font-size: 1.5rem;
    color: #333;
}

.page-title h1 {
    font-size: 1.5rem;
    font-weight: 700;
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
    position: relative;
    cursor: pointer;
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

.dropdown-icon {
    font-size: 0.75rem;
    color: #666;
    margin-left: 0.25rem;
}

/* Dropdown */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    min-width: 180px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.2s ease;
    z-index: 1000;
    overflow: hidden;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1rem;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #333;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-item.text-danger:hover {
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

/* Page Content */
.page-content {
    padding: 1.5rem;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-wrapper {
    width: 42px;
    height: 42px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    flex-shrink: 0;
}

.icon-wrapper i {
    font-size: 1.125rem;
}

.card-title-section h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 0.25rem 0;
}

.card-subtitle {
    font-size: 0.75rem;
    color: #888;
    margin: 0;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(233, 30, 140, 0.3);
}

.btn-download {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
}

.btn-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(233, 30, 140, 0.3);
}

.card-body {
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
    padding: 0.875rem 1.25rem;
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
    padding: 1rem 1.25rem;
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
    gap: 0.625rem;
}

.user-cell i {
    font-size: 1.375rem;
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

.badge-success {
    background: #d4edda;
    color: #28a745;
}

.badge-danger {
    background: #f8d7da;
    color: #dc3545;
}

.badge-warning {
    background: #fff3cd;
    color: #ffc107;
}

/* Action Button */
.btn-action {
    background: white;
    border: 1px solid #e8e8e8;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #666;
    transition: all 0.2s ease;
    font-weight: 500;
    text-decoration: none;
}

.btn-action:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
    color: #333;
}

.btn-action i {
    font-size: 0.75rem;
}

/* Pagination */
.pagination-wrapper {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 0.375rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.page-item {
    margin: 0;
}

.page-link {
    padding: 0.5rem 0.875rem;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    color: #666;
    text-decoration: none;
    font-size: 0.8125rem;
    transition: all 0.2s ease;
    display: block;
}

.page-link:hover {
    background: #f8f9fa;
    border-color: #d0d0d0;
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border-color: #e91e8c;
}

.page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Utilities */
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
        gap: 1rem;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .card-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-download {
        width: 100%;
        justify-content: center;
    }
    
    .data-table {
        font-size: 0.75rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const userInfo = event.target.closest('.user-info');
    
    if (!userInfo) {
        dropdown.classList.remove('show');
    }
});

function tambahAkun() {
    alert('Fitur Tambah Akun akan segera ditambahkan');
    // Route create belum dibuat
}



function downloadPDF() {
    window.location.href = '{{ route("rs.pasien-nifas.download-pdf") }}';
}
</script>
@endpush

@endsection