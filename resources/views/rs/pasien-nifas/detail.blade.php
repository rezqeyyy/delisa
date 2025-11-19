@extends('layouts.rs')

@section('title', 'Detail Pasien Nifas')

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
                <h2 class="page-title">Detail Pasien Nifas - {{ $pasienNifas->pasien->user->name ?? 'N/A' }}</h2>
            </div>
            <a href="{{ route('rs.pasien-nifas.show', $pasienNifas->id) }}" class="btn-edit">
                <i class="fas fa-plus"></i>
                Tambah Data Anak
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
            <!-- Informasi Pasien -->
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
                                <span class="info-value">{{ $pasienNifas->pasien->user->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">NIK</span>
                                <span class="info-value">{{ $pasienNifas->pasien->nik ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">No. Telepon</span>
                                <span class="info-value">{{ $pasienNifas->pasien->no_telepon ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Alamat Lengkap</span>
                                <span class="info-value">
                                    {{ $pasienNifas->pasien->PWilayah ?? '-' }}, 
                                    {{ $pasienNifas->pasien->PKecamatan ?? '-' }}, 
                                    {{ $pasienNifas->pasien->PKabupaten ?? '-' }}, 
                                    {{ $pasienNifas->pasien->PProvinsi ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Tanggal Mulai Nifas</span>
                                <span class="info-value">{{ $pasienNifas->tanggal_mulai_nifas ? \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->format('d F Y') : '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Jumlah Persalinan (P)</span>
                                <span class="info-value">{{ $pasienNifas->anakPasien->count() }} anak</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Status Pre-Eklampsia</span>
                                <span class="info-value">
                                    <span class="badge badge-preeklampsia">
                                        <i class="fas fa-heartbeat"></i>
                                        Pre-Eklampsia
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($pasienNifas->anakPasien->count() > 0)
            <!-- Data Anak (Loop untuk semua anak) -->
            @foreach($pasienNifas->anakPasien as $index => $anak)
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-baby"></i>
                        Data Anak ke-{{ $anak->anak_ke }} ({{ $anak->nama_anak ?? 'Anak ke-'.$anak->anak_ke }})
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="info-table">
                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Nama Anak</span>
                                <span class="info-value">{{ $anak->nama_anak ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Jenis Kelamin</span>
                                <span class="info-value">
                                    @if($anak->jenis_kelamin == 'Laki-laki')
                                        <span class="badge badge-info">
                                            <i class="fas fa-mars"></i> Laki-laki
                                        </span>
                                    @else
                                        <span class="badge badge-pink">
                                            <i class="fas fa-venus"></i> Perempuan
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Tanggal Lahir</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d F Y') }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Usia Kehamilan Saat Lahir</span>
                                <span class="info-value">{{ $anak->usia_kehamilan_saat_lahir }} minggu</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Berat Lahir</span>
                                <span class="info-value">{{ $anak->berat_lahir_anak }} kg</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Panjang Lahir</span>
                                <span class="info-value">{{ $anak->panjang_lahir_anak }} cm</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Lingkar Kepala</span>
                                <span class="info-value">{{ $anak->lingkar_kepala_anak }} cm</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Memiliki Buku KIA</span>
                                <span class="info-value">
                                    @if($anak->memiliki_buku_kia)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Ya
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Tidak
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Buku KIA Bayi Kecil</span>
                                <span class="info-value">
                                    @if($anak->buku_kia_bayi_kecil)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Ya
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Tidak
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">IMD (Inisiasi Menyusui Dini)</span>
                                <span class="info-value">
                                    @if($anak->imd)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Ya
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Tidak
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($anak->riwayat_penyakit && count($anak->riwayat_penyakit) > 0)
                        <div class="info-row full-width">
                            <div class="info-col">
                                <span class="info-key">Riwayat Penyakit/Komplikasi</span>
                                <div class="penyakit-list">
                                    @foreach($anak->riwayat_penyakit as $penyakit)
                                        <span class="penyakit-item">
                                            <i class="fas fa-circle"></i>
                                            {{ $penyakit }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($anak->keterangan_masalah_lain)
                        <div class="info-row full-width">
                            <div class="info-col">
                                <span class="info-key">Keterangan Masalah Lain</span>
                                <div class="catatan-box">
                                    <p>{{ $anak->keterangan_masalah_lain }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Status Kondisi Per Anak -->
                        <div class="info-row">
                            <div class="info-col">
                                <span class="info-key">Status Kondisi</span>
                                <span class="info-value">
                                    @php
                                        $beratLahir = $anak->berat_lahir_anak;
                                        $usiaKehamilan = (int) filter_var($anak->usia_kehamilan_saat_lahir, FILTER_SANITIZE_NUMBER_INT);
                                        $isBeratRendah = $beratLahir < 2.5;
                                        $isPreterm = $usiaKehamilan < 37;
                                        $adaRiwayat = $anak->riwayat_penyakit && count($anak->riwayat_penyakit) > 0;
                                        
                                        if ($isBeratRendah || $isPreterm || $adaRiwayat) {
                                            $statusClass = 'badge-warning';
                                            $statusText = 'Perlu Perhatian Khusus';
                                        } else {
                                            $statusClass = 'badge-success';
                                            $statusText = 'Kondisi Baik';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Kesimpulan & Rekomendasi Keseluruhan -->
            <div class="dashboard-card mb-4">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">
                        <i class="fas fa-file-medical-alt"></i>
                        Kesimpulan & Rekomendasi Keseluruhan
                    </h3>
                </div>
                
                <div class="card-body">
                    

                    <div class="divider"></div>

                    <!-- Rekomendasi Otomatis -->
                    <div class="rekomendasi-auto">
                        <h4 class="rekomendasi-title">
                            <i class="fas fa-robot"></i>
                            Rekomendasi 
                        </h4>
                        <div class="catatan-box rekomendasi">
                            @php
                                $totalAnak = $pasienNifas->anakPasien->count();
                                $anakBBLR = $pasienNifas->anakPasien->filter(fn($a) => $a->berat_lahir_anak < 2.5)->count();
                                $anakPreterm = $pasienNifas->anakPasien->filter(function($a) {
                                    $usia = (int) filter_var($a->usia_kehamilan_saat_lahir, FILTER_SANITIZE_NUMBER_INT);
                                    return $usia < 37;
                                })->count();
                                $anakRiwayat = $pasienNifas->anakPasien->filter(fn($a) => $a->riwayat_penyakit && count($a->riwayat_penyakit) > 0)->count();
                            @endphp

                            @if($anakBBLR > 0)
                                <p><i class="fas fa-exclamation-circle"></i> <strong>BBLR (Berat Badan Lahir Rendah)</strong> - {{ $anakBBLR }} dari {{ $totalAnak }} anak mengalami BBLR. Pastikan ASI eksklusif dan pemantauan pertumbuhan rutin.</p>
                            @endif

                            @if($anakPreterm > 0)
                                <p><i class="fas fa-exclamation-circle"></i> <strong>Kelahiran Prematur</strong> - {{ $anakPreterm }} dari {{ $totalAnak }} anak lahir prematur. Perlu pemeriksaan kesehatan berkala dan pemantauan perkembangan.</p>
                            @endif

                            @if($anakRiwayat > 0)
                                <p><i class="fas fa-exclamation-circle"></i> <strong>Riwayat Komplikasi Ibu</strong> - {{ $anakRiwayat }} dari {{ $totalAnak }} anak memiliki riwayat komplikasi. Lakukan kontrol kesehatan secara teratur.</p>
                            @endif

                            @if($anakBBLR == 0 && $anakPreterm == 0 && $anakRiwayat == 0)
                                <p><i class="fas fa-check-circle"></i> <strong>Kondisi Baik</strong> - Semua anak dalam kondisi baik. Lanjutkan perawatan rutin, ASI eksklusif, dan imunisasi sesuai jadwal.</p>
                            @endif

                            <p><i class="fas fa-info-circle"></i> <strong>Saran Umum:</strong> Pastikan ibu mendapatkan nutrisi yang cukup, istirahat yang memadai, dan dukungan psikologis. Jadwalkan kontrol rutin untuk memantau kesehatan ibu dan bayi.</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-baby fa-3x"></i>
                        <p>Belum ada data anak untuk pasien ini</p>
                        <a href="{{ route('rs.pasien-nifas.show', $pasienNifas->id) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tambah Data Anak
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Button Actions -->
            <div class="form-actions">
                <a href="{{ route('rs.pasien-nifas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                @if($pasienNifas->anakPasien->count() > 0)
                <button class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Cetak Data
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ... CSS yang sama seperti sebelumnya ... */
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
    padding: 1.5rem;
}

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

.badge-preeklampsia {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
}

.badge-info {
    background: #DBEAFE;
    color: #1E40AF;
}

.badge-pink {
    background: #FCE7F3;
    color: #DB2777;
}

.badge i {
    font-size: 0.75rem;
}

.penyakit-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.penyakit-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e8e8e8;
    font-size: 0.8125rem;
    color: #666;
}

.penyakit-item i {
    font-size: 0.5rem;
    color: #e91e8c;
}

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

.catatan-box.rekomendasi p {
    margin-bottom: 0.75rem;
}

.catatan-box.rekomendasi p:last-child {
    margin-bottom: 0;
}

.catatan-box.rekomendasi i {
    margin-right: 0.5rem;
}

.full-width .info-key {
    flex: 0 0 100% !important;
    border-bottom: 1px solid #f0f0f0;
}

.full-width .info-value {
    padding-top: 0 !important;
}

/* Form Elements */
.form-group-full {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #666;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    font-size: 0.8125rem;
    transition: all 0.2s ease;
    background: white;
    color: #333;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #e91e8c;
    box-shadow: 0 0 0 3px rgba(233, 30, 140, 0.1);
}

textarea.form-input {
    resize: vertical;
}

.form-hint {
    margin-top: 0.375rem;
    font-size: 0.6875rem;
    color: #888;
}

.form-actions-inline {
    margin-top: 1rem;
}

.divider {
    height: 1px;
    background: #e8e8e8;
    margin: 2rem 0;
}

/* Rekomendasi Auto */
.rekomendasi-auto {
    margin-top: 2rem;
}

.rekomendasi-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rekomendasi-title i {
    color: #3B82F6;
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

.btn-success {
    background: #10B981;
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
}

/* Print Styles */
@media print {
    .sidebar,
    .btn-back,
    .btn-edit,
    .form-actions,
    .form-group-full,
    .form-actions-inline,
    .divider:last-of-type {
        display: none !important;
    }

    .main-content {
        width: 100%;
    }

    .dashboard-card {
        box-shadow: none;
        border: 1px solid #e8e8e8;
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }

    .content-header {
        position: relative;
        border-bottom: 2px solid #333;
    }

    .page-title {
        font-size: 1.25rem;
    }

    body {
        background: white;
    }

    .alert {
        display: none;
    }

    .rekomendasi-auto {
        display: block !important;
    }
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
        gap: 0.75rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .penyakit-list {
        gap: 0.375rem;
    }

    .page-title {
        font-size: 1.125rem;
    }
}

@media (max-width: 576px) {
    .card-title-simple {
        font-size: 0.875rem;
    }

    .info-col > .info-key,
    .info-col > .info-value {
        padding: 0.75rem 1rem;
        font-size: 0.75rem;
    }

    .badge {
        font-size: 0.625rem;
        padding: 0.3rem 0.7rem;
    }

    .penyakit-item {
        font-size: 0.75rem;
        padding: 0.4rem 0.75rem;
    }

    .catatan-box p {
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Auto-hide alert setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>
@endpush

@endsection