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
            <div class="header-left">
                <a href="{{ route('rs.skrining.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="page-title">Riwayat Pasien ({{ $skrining->pasien->user->name ?? 'N/A' }})</h2>
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
            <form action="{{ route('rs.skrining.update', $skrining->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Pengecekan Ulang Data Pasien -->
                <div class="dashboard-card mb-4">
                    <div class="card-header-simple">
                        <h3 class="card-title-simple">Pengecekan Ulang Data Pasien</h3>
                    </div>
                    
                    <div class="card-body">
                        <div class="form-row">
                            <!-- Pasien Datang -->
                            <div class="form-col">
                                <label class="form-label">Pasien Datang?</label>
                                <select name="pasien_datang" class="form-input">
                                    <option value="">Pilih Data Ya/Tidak</option>
                                    <option value="1" {{ old('pasien_datang', $rujukan->pasien_datang) == 1 ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('pasien_datang', $rujukan->pasien_datang) == 0 ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>

                            <!-- Riwayat Tekanan Darah -->
                            <div class="form-col">
                                <label class="form-label">Riwayat Tekanan Darah Pasien</label>
                                <input type="text" 
                                    name="riwayat_tekanan_darah" 
                                    class="form-input" 
                                    placeholder="Masukkan Data..."
                                    value="{{ old('riwayat_tekanan_darah', $rujukan->riwayat_tekanan_darah) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Perlu Pemeriksaan Berkutnya -->
                            <div class="form-col">
                                <label class="form-label">Perlu Pemeriksaan Berkutnya?</label>
                                <select name="perlu_pemeriksaan_lanjut" class="form-input">
                                    <option value="">Pilih Data Ya/Tidak</option>
                                    <option value="1" {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 1 ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('perlu_pemeriksaan_lanjut', $rujukan->perlu_pemeriksaan_lanjut) == 0 ? 'selected' : '' }}>Tidak</option>
                                </select>
                                <p class="form-hint">* Jika pasien tidak datang maka hanya isi kedatangan dan opsi pemeriksaan ulang</p>
                            </div>

                            <!-- Hasil Pemeriksaan Protein Urin -->
                            <div class="form-col">
                                <label class="form-label">Hasil Pemeriksaan Protein Urin</label>
                                <input type="text" 
                                    name="hasil_protein_urin" 
                                    class="form-input" 
                                    placeholder="Masukkan Data..."
                                    value="{{ old('hasil_protein_urin', $rujukan->hasil_protein_urin) }}">
                            </div>
                        </div>

                        <!-- Tindakan Medis -->
                        <div class="form-group">
                            <label class="form-label">Pilih Tindakan</label>
                            <select class="form-input">
                                <option>Pilih Data</option>
                                <option>Rawat Inap</option>
                                <option>Rawat Jalan</option>
                                <option>Observasi</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Resep Obat -->
                <div class="dashboard-card mb-4">
                    <div class="card-header-flex">
                        <h3 class="card-title-simple">Resep Obat</h3>
                        <button type="button" class="btn-tambah" onclick="alert('Fitur tambah obat')">
                            <i class="fas fa-plus"></i>
                            Tambah Obat
                        </button>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="obat-table">
                                <thead>
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>Dosis</th>
                                        <th>Digunakan</th>
                                    </tr>
                                </thead>
                                <tbody id="obatTableBody">
                                    @php
                                        $obatOptions = [
                                            'Kalsium 1000 - 1500mg',
                                            'Simvastatin 10mg',
                                            'Amlodipine 5mg'
                                        ];
                                        
                                        $existingObat = $resepObats->pluck('resep_obat')->toArray();
                                    @endphp

                                    @foreach($obatOptions as $index => $obat)
                                        @php
                                            $isChecked = in_array($obat, $existingObat);
                                            $resep = $resepObats->where('resep_obat', $obat)->first();
                                        @endphp
                                        <tr class="obat-row {{ $isChecked ? 'active' : '' }}" data-index="{{ $index }}">
                                            <td>
                                                <input type="hidden" 
                                                    name="resep_obat[{{ $index }}]" 
                                                    value="{{ $obat }}">
                                                <span>{{ $obat }}</span>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                    name="dosis[{{ $index }}]" 
                                                    class="form-input-inline" 
                                                    placeholder="Masukkan Data..."
                                                    value="{{ $resep->dosis ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                    name="penggunaan[{{ $index }}]" 
                                                    class="form-input-inline" 
                                                    placeholder="Masukkan Data..."
                                                    value="{{ $resep->penggunaan ?? '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="form-actions">
                    <a href="{{ route('rs.skrining.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline" onclick="alert('Simpan Data')">
                            Simpan Data
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Lihat Data Selanjutnya
                        </button>
                    </div>
                </div>
            </form>
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

/* Sidebar - sama seperti index */
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
    margin: 1.5rem 2rem;
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
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.card-header-simple {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.card-header-flex {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title-simple {
    font-size: 1rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.btn-tambah {
    background: #e91e8c;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-tambah:hover {
    background: #c2185b;
}

.card-body {
    padding: 1.5rem;
}

.card-body.p-0 {
    padding: 0;
}

/* Form */
.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.form-col {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 0;
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
}

.form-input:focus {
    outline: none;
    border-color: #e91e8c;
    box-shadow: 0 0 0 3px rgba(233, 30, 140, 0.1);
}

.form-input::placeholder {
    color: #999;
}

.form-hint {
    margin-top: 0.375rem;
    font-size: 0.6875rem;
    color: #888;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.obat-table {
    width: 100%;
    border-collapse: collapse;
}

.obat-table thead {
    background: #fafafa;
}

.obat-table th {
    padding: 0.875rem 1.25rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.75rem;
    color: #888;
    border-bottom: 1px solid #e8e8e8;
    text-transform: uppercase;
}

.obat-table td {
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.8125rem;
}

.obat-table tbody tr {
    transition: all 0.2s ease;
}

.obat-table tbody tr:hover {
    background: #fafafa;
}

.form-input-inline {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    font-size: 0.8125rem;
}

.form-input-inline:focus {
    outline: none;
    border-color: #e91e8c;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-group {
    display: flex;
    gap: 0.75rem;
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

.btn-outline {
    background: white;
    color: #666;
    border: 1px solid #e8e8e8;
}

.btn-outline:hover {
    background: #f8f9fa;
}

.btn-primary {
    background: #e91e8c;
    color: white;
}

.btn-primary:hover {
    background: #c2185b;
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
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-group {
        width: 100%;
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@endsection