@extends('layouts.rs')

@section('title', 'Tambah Data Nifas')

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
                <h2 class="page-title">Data Nifas Pasien</h2>
            </div>
        </div>

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

        <!-- Content -->
        <div class="skrining-content">
            <!-- Info Pasien -->
           
            <!-- List Anak yang Sudah Ada -->
            @if($pasienNifas->anakPasien->count() > 0)
            <div class="dashboard-card">
                <div class="card-header-flex">
                    <h3 class="card-title-simple">Data Anak yang Sudah Terdaftar</h3>
                    <a href="{{ route('rs.pasien-nifas.detail', $pasienNifas->id) }}" class="btn btn-view">
                        <i class="fas fa-eye"></i>
                        Lihat Detail
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="anak-table">
                            <thead>
                                <tr>
                                    <th>Anak Ke</th>
                                    <th>Nama Anak</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Berat Lahir</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pasienNifas->anakPasien as $anak)
                                <tr>
                                    <td>{{ $anak->anak_ke }}</td>
                                    <td>{{ $anak->nama_anak }}</td>
                                    <td>{{ $anak->jenis_kelamin }}</td>
                                    <td>{{ \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d/m/Y') }}</td>
                                    <td>{{ $anak->berat_lahir_anak }} kg</td>
                                    <td><span class="badge badge-success">Terdaftar</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Form Tambah Anak Baru -->
            <div class="dashboard-card">
                <div class="card-header-simple">
                    <h3 class="card-title-simple">Tambah Data Anak {{ $pasienNifas->anakPasien->count() > 0 ? 'Baru' : '' }}</h3>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('rs.pasien-nifas.store-anak', $pasienNifas->id) }}" method="POST" id="formAnakPasien">
                        @csrf
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Anak Ke</label>
                                <input type="number" name="anak_ke" class="form-input" 
                                    placeholder="Masukkan Data..." 
                                    value="{{ old('anak_ke', $pasienNifas->anakPasien->count() + 1) }}" 
                                    required>
                            </div>

                            <div class="form-col">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-input" 
                                    value="{{ old('tanggal_lahir') }}" 
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-input" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            <div class="form-col">
                                <label class="form-label">Usia Kehamilan Saat Lahir (Dalam Minggu)</label>
                                <input type="text" name="usia_kehamilan_saat_lahir" class="form-input" 
                                    placeholder="Masukkan Data..." 
                                    value="{{ old('usia_kehamilan_saat_lahir') }}" 
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Berat Lahir Anak</label>
                                <input type="number" step="0.01" name="berat_lahir_anak" class="form-input" 
                                    placeholder="Pilih Data Ya/Tidak" 
                                    value="{{ old('berat_lahir_anak') }}" 
                                    required>
                            </div>

                            <div class="form-col">
                                <label class="form-label">Panjang Lahir Anak</label>
                                <input type="number" step="0.01" name="panjang_lahir_anak" class="form-input" 
                                    placeholder="Masukkan Data..." 
                                    value="{{ old('panjang_lahir_anak') }}" 
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Lingkar Kepala Anak</label>
                                <input type="number" step="0.01" name="lingkar_kepala_anak" class="form-input" 
                                    placeholder="Masukkan Data..." 
                                    value="{{ old('lingkar_kepala_anak') }}" 
                                    required>
                            </div>

                            <div class="form-col">
                                <label class="form-label">Memiliki Buku KIA</label>
                                <select name="memiliki_buku_kia" class="form-input" required>
                                    <option value="">Pilih Data Ya/Tidak</option>
                                    <option value="1" {{ old('memiliki_buku_kia') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('memiliki_buku_kia') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Memiliki Buku KIA Bayi Kecil</label>
                                <select name="buku_kia_bayi_kecil" class="form-input" required>
                                    <option value="">Pilih Data Ya/Tidak</option>
                                    <option value="1" {{ old('buku_kia_bayi_kecil') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('buku_kia_bayi_kecil') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>

                            <div class="form-col">
                                <label class="form-label">IMD</label>
                                <select name="imd" class="form-input" required>
                                    <option value="">Pilih Data Ya/Tidak</option>
                                    <option value="1" {{ old('imd') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('imd') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="form-group-full">
                            <label class="form-label">Riwayat Penyakit atau Komplikasi Ibu (Penyebab BBLR/Preterm)</label>
                            <div class="checkbox-grid">
                                @foreach(['Hipertensi','Infeksi','KPD','Masalah Plasenta','Inkompetensi Serviks','Masalah Lainnya'] as $item)
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="riwayat_penyakit[]" value="{{ $item }}" 
                                            {{ in_array($item, old('riwayat_penyakit', [])) ? 'checked' : '' }}>
                                        <span>{{ $item }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Keterangan Masalah Lain (Opsional)</label>
                            <textarea name="keterangan_masalah_lain" class="form-input" rows="4" 
                                placeholder="Masukkan Keterangan Lain...">{{ old('keterangan_masalah_lain') }}</textarea>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Button Actions -->
            <div class="form-actions">
                <a href="{{ route('rs.pasien-nifas.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
                <button type="submit" form="formAnakPasien" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Simpan Data
                </button>
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

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
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

.form-group-full {
    margin-bottom: 1.25rem;
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
}

.form-input:focus {
    outline: none;
    border-color: #e91e8c;
    box-shadow: 0 0 0 3px rgba(233, 30, 140, 0.1);
}

.form-input::placeholder {
    color: #999;
}

textarea.form-input {
    resize: none;
    font-family: inherit;
}

/* Divider */
.divider {
    height: 1px;
    background: #f0f0f0;
    margin: 1.5rem 0;
}

/* Checkbox Grid */
.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    font-size: 0.8125rem;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.checkbox-item:hover {
    border-color: #e91e8c;
}

.checkbox-item input[type="checkbox"]:checked + span {
    color: #e91e8c;
    font-weight: 600;
}

.checkbox-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
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
    
    .checkbox-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endpush

@endsection