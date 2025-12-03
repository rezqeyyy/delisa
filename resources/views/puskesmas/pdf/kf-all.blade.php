<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 25px; 
            padding-bottom: 15px;
            border-bottom: 2px solid #B9257F;
        }
        
        .header h1 { 
            color: #B9257F; 
            font-size: 22px; 
            margin-bottom: 5px; 
        }
        
        .header .subtitle { 
            color: #666; 
            font-size: 14px; 
        }
        
        .section { 
            margin-bottom: 20px; 
        }
        
        .section-title { 
            color: #B9257F; 
            font-size: 16px; 
            border-bottom: 1px solid #B9257F;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .info-grid { 
            display: grid; 
            grid-template-columns: 120px 1fr; 
            gap: 8px; 
            margin-bottom: 10px;
        }
        
        .info-label { 
            font-weight: bold; 
            color: #555; 
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            page-break-inside: auto;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .kf-table {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .kf-table th {
            background-color: #e9ecef;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-sehat { background-color: #d4edda; color: #155724; }
        .status-dirujuk { background-color: #fff3cd; color: #856404; }
        .status-meninggal { background-color: #f8d7da; color: #721c24; }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 10px; 
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-line {
            width: 300px;
            border-top: 1px solid #333;
            margin: 40px 0 5px auto;
            text-align: center;
            padding-top: 5px;
        }
        
        .note-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #B9257F;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-3 { margin-bottom: 15px; }
        .mt-3 { margin-top: 15px; }
        .bold { font-weight: bold; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DeLISA - Digital Prada Pin (Skinpads)</h1>
        <div class="subtitle">Laporan Lengkap Kunjungan Nifas</div>
        <div style="font-size: 11px; color: #888;">
            Dicetak: {{ $tanggal_cetak }}
        </div>
    </div>
    
    <!-- Data Pasien -->
    <div class="section">
        <div class="section-title">Data Pasien</div>
        <div class="info-grid">
            <div class="info-label">Nama Pasien:</div>
            <div>{{ $pasienNifas->pasien->user->name ?? 'N/A' }}</div>
            
            <div class="info-label">NIK:</div>
            <div>{{ $pasienNifas->pasien->nik ?? 'N/A' }}</div>
            
            <div class="info-label">Rumah Sakit:</div>
            <div>{{ $pasienNifas->rs->nama ?? 'N/A' }}</div>
            
            <div class="info-label">Tanggal Mulai Nifas:</div>
            <div>
                @if($pasienNifas->tanggal_mulai_nifas)
                    {{ \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->format('d/m/Y') }}
                @else
                    Belum diisi
                @endif
            </div>
            
            <div class="info-label">Tanggal Melahirkan:</div>
            <div>
                @if($pasienNifas->tanggal_melahirkan)
                    {{ \Carbon\Carbon::parse($pasienNifas->tanggal_melahirkan)->format('d/m/Y') }}
                @else
                    Belum diisi
                @endif
            </div>
            
            <div class="info-label">Alamat:</div>
            <div>{{ $pasienNifas->pasien->PKecamatan ?? $pasienNifas->pasien->PKabupaten ?? 'N/A' }}</div>
        </div>
    </div>
    
    <!-- Ringkasan KF -->
    <div class="section">
        <div class="section-title">Ringkasan Kunjungan Nifas (KF)</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="15%">Jenis KF</th>
                    <th width="20%">Tanggal Kunjungan</th>
                    <th width="15%">Status</th>
                    <th width="25%">Kesimpulan</th>
                    <th width="25%">Catatan Singkat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kfKunjungan as $kf)
                <tr>
                    <td class="bold">KF{{ $kf->jenis_kf }}</td>
                    <td>{{ \Carbon\Carbon::parse($kf->tanggal_kunjungan)->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($kf->kesimpulan_pantauan == 'Sehat')
                            <span style="color: green;">●</span> Selesai
                        @else
                            <span style="color: orange;">●</span> Selesai
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($kf->kesimpulan_pantauan) }}">
                            {{ $kf->kesimpulan_pantauan }}
                        </span>
                    </td>
                    <td>
                        @if($kf->catatan)
                            {{ Str::limit($kf->catatan, 50) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Detail Setiap KF -->
    <div class="section">
        <div class="section-title">Detail Kunjungan</div>
        
        @foreach($kfKunjungan as $kf)
        <div class="kf-table {{ !$loop->first ? 'page-break' : '' }}">
            <h3 style="color: #B9257F; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px;">
                KF{{ $kf->jenis_kf }}
            </h3>
            
            <table class="table">
                <tr>
                    <th width="25%">Tanggal Kunjungan</th>
                    <td>{{ \Carbon\Carbon::parse($kf->tanggal_kunjungan)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Kesimpulan</th>
                    <td>
                        <span class="status-badge status-{{ strtolower($kf->kesimpulan_pantauan) }}">
                            {{ $kf->kesimpulan_pantauan }}
                        </span>
                    </td>
                </tr>
                
                @if($kf->sbp || $kf->dbp || $kf->map)
                <tr>
                    <th>Tekanan Darah</th>
                    <td>
                        @if($kf->sbp && $kf->dbp)
                            {{ $kf->sbp }}/{{ $kf->dbp }} mmHg
                            @if($kf->map)
                                (MAP: {{ $kf->map }} mmHg)
                            @endif
                        @else
                            Tidak diukur
                        @endif
                    </td>
                </tr>
                @endif
            </table>
            
            @if($kf->keadaan_umum)
            <div style="margin-top: 10px;">
                <div style="font-weight: bold; color: #555; margin-bottom: 5px;">Keadaan Umum:</div>
                <div class="note-box">{{ $kf->keadaan_umum }}</div>
            </div>
            @endif
            
            @if($kf->tanda_bahaya)
            <div style="margin-top: 10px;">
                <div style="font-weight: bold; color: #d9534f; margin-bottom: 5px;">Tanda Bahaya:</div>
                <div style="background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; border-radius: 4px;">
                    {{ $kf->tanda_bahaya }}
                </div>
            </div>
            @endif
            
            @if($kf->catatan)
            <div style="margin-top: 10px;">
                <div style="font-weight: bold; color: #555; margin-bottom: 5px;">Catatan Tambahan:</div>
                <div style="background-color: #e7f3ff; padding: 10px; border-left: 4px solid #3498db; border-radius: 4px;">
                    {{ $kf->catatan }}
                </div>
            </div>
            @endif
            
            <hr style="margin: 20px 0; border: none; border-top: 1px dashed #ddd;">
        </div>
        @endforeach
    </div>
    
    <!-- Informasi Periode KF -->
    <div class="section">
        <div class="section-title">Informasi Periode KF</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="20%">Jenis KF</th>
                    <th width="40%">Periode Ideal</th>
                    <th width="40%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>KF1</strong></td>
                    <td>6 jam - 2 hari setelah melahirkan</td>
                    <td>Kunjungan pertama setelah persalinan</td>
                </tr>
                <tr>
                    <td><strong>KF2</strong></td>
                    <td>Hari ke-3 - ke-7 setelah melahirkan</td>
                    <td>Minggu pertama pasca persalinan</td>
                </tr>
                <tr>
                    <td><strong>KF3</strong></td>
                    <td>Hari ke-8 - ke-28 setelah melahirkan</td>
                    <td>Minggu ke-2 sampai ke-4 pasca persalinan</td>
                </tr>
                <tr>
                    <td><strong>KF4</strong></td>
                    <td>Hari ke-29 - ke-42 setelah melahirkan</td>
                    <td>Minggu ke-5 sampai ke-6 pasca persalinan</td>
                </tr>
            </tbody>
        </table>
        <div style="font-size: 11px; color: #666; font-style: italic; margin-top: 10px;">
            * Periode berdasarkan pedoman Kementerian Kesehatan RI
        </div>
    </div>
    
    <!-- Tanda Tangan -->
    <div class="signature">
        <div class="signature-line">
            Petugas Kesehatan yang Menangani
        </div>
        <div style="margin-top: 30px; font-size: 11px;">
            Nama & Stempel Puskesmas
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div>Dokumen ini dicetak secara elektronik dari Sistem DeLISA</div>
        <div>© {{ date('Y') }} Dinas Kesehatan Kota Depok — DeLISA</div>
        <div style="margin-top: 5px; font-size: 9px;">
            ID Dokumen: ALLKF_{{ $pasienNifas->id }}_{{ now()->format('YmdHis') }}
        </div>
    </div>
</body>
</html>