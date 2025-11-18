@extends('layouts.rs')

@section('title', 'Riwayat Pasien')

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
            <div class="header-left">
                <a href="{{ route('rs.pasien-nifas.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="page-title">Riwayat Pasien</h2>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Content -->
        <div class="skrining-content">
            <!-- Informasi Pasien dan Data Kehamilan -->
            <div class="dashboard-card">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">Informasi Pasien dan Data Kehamilan</h3>
                </div>
                
                <div class="card-body p-0">
                    <div class="info-table">
                        <div class="info-row header-row">
                            <div class="info-cell">Informasi</div>
                            <div class="info-cell">Data</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Tanggal Pemeriksaan</div>
                            <div class="info-cell value">{{ $pasienNifas->created_at ? $pasienNifas->created_at->format('d F Y') : '-' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Nama</div>
                            <div class="info-cell value">{{ $pasienNifas->pasien->user->name ?? '-' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">NIK</div>
                            <div class="info-cell value">{{ $pasienNifas->pasien->nik ?? '-' }}</div>
                        </div>

                        @if($anakPasien)
                        <div class="info-row">
                            <div class="info-cell label">Kehamilan ke (G)</div>
                            <div class="info-cell value">{{ $anakPasien->anak_ke ?? '1' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Jumlah Persalinan (P)</div>
                            <div class="info-cell value">{{ $pasienNifas->anakPasien->count() }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Jumlah Abortus (A)</div>
                            <div class="info-cell value">0</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Usia Kehamilan</div>
                            <div class="info-cell value">{{ $anakPasien->usia_kehamilan_saat_lahir ?? '0' }} Minggu</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Tanggal Persalinan</div>
                            <div class="info-cell value">{{ $anakPasien->tanggal_lahir ? \Carbon\Carbon::parse($anakPasien->tanggal_lahir)->format('d F Y') : '-' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Indeks Massa Tubuh (IMT)</div>
                            <div class="info-cell value">25.76</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Status IMT</div>
                            <div class="info-cell value">Normal (IMT 18.5 - 25)</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Anjuran Kenaikan BB</div>
                            <div class="info-cell value">11.5 - 16 kg</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Tensi Tekanan Darah</div>
                            <div class="info-cell value">90/120 mmHg</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Mean Arterial Pressure (MAP)</div>
                            <div class="info-cell value">100.00 mmHg</div>
                        </div>
                        @else
                        <div class="info-row">
                            <div class="info-cell label" colspan="2" style="text-align: center; color: #999;">
                                Data anak belum ditambahkan
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($anakPasien)
            <!-- Hasil Skrining dan Rekomendasi -->
            <div class="dashboard-card">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">Hasil Skrining dan Rekomendasi</h3>
                </div>
                
                <div class="card-body p-0">
                    <div class="info-table">
                        <div class="info-row header-row">
                            <div class="info-cell">Informasi</div>
                            <div class="info-cell">Data</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Jumlah Resiko Sedang</div>
                            <div class="info-cell value">0</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Jumlah Resiko Tinggi</div>
                            <div class="info-cell value">0</div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Kesimpulan</div>
                            <div class="info-cell value">
                                <span class="badge badge-normal">Tidak Beresiko Pre Eklampsia</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-cell label">Rekomendasi</div>
                            <div class="info-cell value">Belum ada catatan</div>
                        </div>

                        <div class="info-row full-width">
                            <div class="info-cell label">Catatan</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell value" colspan="2">
                                <div class="catatan-box">
                                    <p>{{ $anakPasien->keterangan_masalah_lain ?? 'Tidak ada catatan tambahan.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Button Actions -->
            <div class="form-actions">
                <a href="{{ route('rs.pasien-nifas.index') }}" class="btn btn-secondary">
                    Kembali
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

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

/* Alert */
.alert {
    margin: 1.5rem 2rem 0;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
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
}

.card-body {
    padding: 1.5rem;
}

.card-body.p-0 {
    padding: 0;
}

/* Info Table */
.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-row {
    display: grid;
    grid-template-columns: 40% 60%;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row.header-row {
    background: #fafafa;
}

.info-row.header-row .info-cell {
    font-weight: 600;
    color: #1a1a1a;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.3px;
}

.info-row.full-width {
    grid-template-columns: 1fr;
}

.info-row.full-width + .info-row {
    grid-template-columns: 1fr;
}

.info-cell {
    padding: 1rem 1.5rem;
    font-size: 0.8125rem;
}

.info-cell.label {
    color: #666;
    font-weight: 500;
    background: #fafafa;
}

.info-cell.value {
    color: #1a1a1a;
    font-weight: 500;
}

/* Badge */
.badge {
    padding: 0.35rem 0.875rem;
    border-radius: 20px;
    font-size: 0.6875rem;
    font-weight: 600;
    display: inline-block;
    text-align: center;
}

.badge-normal {
    background: #10B981;
    color: white;
}

.badge-berisiko {
    background: #EF4444;
    color: white;
}

.badge-waspada {
    background: #F59E0B;
    color: white;
}

/* Catatan Box */
.catatan-box {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e8e8e8;
}

.catatan-box p {
    margin: 0;
    font-size: 0.8125rem;
    color: #666;
    line-height: 1.6;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-start;
}

.btn {
    padding: 0.75rem 2rem;
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
        padding: 1rem 1.25rem 2rem;
    }
    
    .info-row {
        grid-template-columns: 1fr;
    }
    
    .info-cell.label {
        border-bottom: 1px solid #f0f0f0;
    }
}
</style>
@endpush

@endsection