@extends('layouts.rs')

@section('title', 'Tambah Pasien Nifas')

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
            <div class="header-content">
                <a href="{{ route('rs.pasien-nifas.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <h2 class="page-title">Tambah Data Pasien Nifas</h2>
            </div>
        </div>

        <!-- Content -->
        <div class="skrining-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-header-title">Tambah Data Pasien Nifas</h3>
                </div>
                
                <div class="card-body">
                    <form id="formTambahPasien" method="POST" action="{{ route('rs.pasien-nifas.store') }}">
                        @csrf
                        
                        <!-- Nama Pasien & NIK -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_pasien">Nama Pasien <span class="required">*</span></label>
                                <input type="text" 
                                       id="nama_pasien" 
                                       name="nama_pasien" 
                                       class="form-input @error('nama_pasien') is-invalid @enderror" 
                                       placeholder="Nama anda"
                                       value="{{ old('nama_pasien') }}"
                                       required>
                                @error('nama_pasien')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="nik">NIK <span class="required">*</span></label>
                                <input type="text" 
                                       id="nik" 
                                       name="nik" 
                                       class="form-input @error('nik') is-invalid @enderror" 
                                       placeholder="Masukkan NIK 16 digit" 
                                       maxlength="16"
                                       value="{{ old('nik') }}"
                                       required>
                                @error('nik')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Nomor Telepon -->
                        <div class="form-group">
                            <label for="no_telepon">Nomor Telepon <span class="required">*</span></label>
                            <input type="text" 
                                   id="no_telepon" 
                                   name="no_telepon" 
                                   class="form-input @error('no_telepon') is-invalid @enderror" 
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ old('no_telepon') }}"
                                   required>
                            @error('no_telepon')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Provinsi & Kota -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="provinsi">Provinsi <span class="required">*</span></label>
                                <input type="text" 
                                       id="provinsi" 
                                       name="provinsi" 
                                       class="form-input @error('provinsi') is-invalid @enderror" 
                                       placeholder="Pilih Provinsi"
                                       value="{{ old('provinsi') }}"
                                       required>
                                @error('provinsi')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="kota">Kota/Kabupaten <span class="required">*</span></label>
                                <input type="text" 
                                       id="kota" 
                                       name="kota" 
                                       class="form-input @error('kota') is-invalid @enderror" 
                                       placeholder="Pilih Kota Terlebih Dahulu"
                                       value="{{ old('kota') }}"
                                       required>
                                @error('kota')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Kecamatan & Kelurahan -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kecamatan">Kecamatan <span class="required">*</span></label>
                                <input type="text" 
                                       id="kecamatan" 
                                       name="kecamatan" 
                                       class="form-input @error('kecamatan') is-invalid @enderror" 
                                       placeholder="Pilih Kota Terlebih Dahulu"
                                       value="{{ old('kecamatan') }}"
                                       required>
                                @error('kecamatan')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="kelurahan">Kelurahan <span class="required">*</span></label>
                                <input type="text" 
                                       id="kelurahan" 
                                       name="kelurahan" 
                                       class="form-input @error('kelurahan') is-invalid @enderror" 
                                       placeholder="Pilih Kecamatan Terlebih Dahulu"
                                       value="{{ old('kelurahan') }}"
                                       required>
                                @error('kelurahan')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Domisili -->
                        <div class="form-group">
                            <label for="domisili">Domisili <span class="required">*</span></label>
                            <textarea id="domisili" 
                                      name="domisili" 
                                      class="form-textarea @error('domisili') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Jl. xxxxxxx"
                                      required>{{ old('domisili') }}</textarea>
                            @error('domisili')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i>
                                Tambah Data Pasien
                            </button>
                        </div>
                    </form>
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
}

.content-header {
    background: white;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e8e8e8;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-back {
    background: white;
    border: 1px solid #e0e0e0;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    color: #666;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-back:hover {
    background: #f5f5f5;
    border-color: #d0d0d0;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

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
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f0f0f0;
    background: white;
}

.card-header-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.card-body {
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    font-size: 0.875rem;
    color: #333;
    margin-bottom: 0.5rem;
}

.required {
    color: #e91e8c;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    font-family: inherit;
    background: white;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #e91e8c;
    box-shadow: 0 0 0 3px rgba(233, 30, 140, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-input.is-invalid,
.form-textarea.is-invalid {
    border-color: #ef4444;
}

.error-message {
    display: block;
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.375rem;
}

.form-actions {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #f0f0f0;
}

.btn-submit {
    background: linear-gradient(135deg, #e91e8c 0%, #c2185b 100%);
    color: white;
    border: none;
    padding: 0.875rem 3rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-submit:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
    
    .content-header {
        padding: 1rem 1.25rem;
    }
    
    .skrining-content {
        padding: 1rem 1.25rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Validasi NIK hanya angka
document.getElementById('nik').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Validasi No Telepon hanya angka
document.getElementById('no_telepon').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endpush

@endsection