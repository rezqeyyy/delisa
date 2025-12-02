<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hasil Pemeriksaan Pasien - DELISA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1D1D1D;
            background: #fff;
        }

        .container {
            padding: 20px 30px;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px solid #E91E8C;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #E91E8C;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            color: #7C7C7C;
        }

        .header .subtitle {
            font-size: 10px;
            color: #9CA3AF;
            margin-top: 3px;
        }

        /* Section */
        .section {
            margin-bottom: 20px;
            border: 1px solid #E9E9E9;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #FAFAFA;
            padding: 10px 15px;
            border-bottom: 1px solid #E9E9E9;
        }

        .section-header h2 {
            font-size: 12px;
            font-weight: bold;
            color: #1D1D1D;
        }

        .section-body {
            padding: 0;
        }

        /* Table Info */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #F3F3F3;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table th {
            width: 35%;
            text-align: left;
            padding: 8px 15px;
            background: #FAFAFA;
            font-size: 10px;
            font-weight: 600;
            color: #7C7C7C;
            vertical-align: top;
        }

        .info-table td {
            padding: 8px 15px;
            font-size: 11px;
            color: #1D1D1D;
            vertical-align: top;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-danger {
            background: #FEE2E2;
            color: #DC2626;
        }

        .badge-success {
            background: #D1FAE5;
            color: #059669;
        }

        .badge-warning {
            background: #FEF3C7;
            color: #D97706;
        }

        .badge-default {
            background: #F5F5F5;
            color: #6B7280;
        }

        /* Data Table (Resep Obat) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .data-table thead {
            background: #FAFAFA;
        }

        .data-table th {
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E5E5;
        }

        .data-table td {
            padding: 10px 12px;
            font-size: 11px;
            color: #1D1D1D;
            border-bottom: 1px solid #F3F3F3;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table .font-semibold {
            font-weight: 600;
        }

        /* Catatan Box */
        .catatan-box {
            background: #F9FAFB;
            border: 1px solid #E5E5E5;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 10px;
            color: #4B4B4B;
            line-height: 1.5;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E5E5;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .footer p {
            font-size: 9px;
            color: #7C7C7C;
            margin-bottom: 3px;
        }

        .signature-box {
            margin-top: 10px;
        }

        .signature-box .date {
            font-size: 10px;
            color: #7C7C7C;
            margin-bottom: 50px;
        }

        .signature-box .name {
            font-size: 11px;
            font-weight: 600;
            color: #1D1D1D;
        }

        .signature-box .line {
            font-size: 10px;
            color: #7C7C7C;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #9CA3AF;
            font-style: italic;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>HASIL PEMERIKSAAN PASIEN</h1>
            <p>Sistem DeLISA - Dinas Kesehatan Kota Depok</p>
            @if($rumahSakit)
                <p class="subtitle">{{ $rumahSakit->nama ?? 'Rumah Sakit' }}</p>
            @endif
            <p class="subtitle">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
        </div>

        {{-- Section: Informasi Pasien --}}
        <div class="section">
            <div class="section-header">
                <h2>Informasi Pasien</h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>{{ $skrining->pasien->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td>{{ $skrining->pasien->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pemeriksaan Awal</th>
                        <td>
                            @if($skrining->created_at)
                                {{ $skrining->created_at->format('d F Y, H:i') }} WIB
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Usia Kehamilan</th>
                        <td>{{ $skrining->kondisiKesehatan->usia_kehamilan ?? '-' }} minggu</td>
                    </tr>
                    <tr>
                        <th>Status Awal</th>
                        <td>
                            @php
                                $conclusion = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                $badgeClass = match(strtolower($conclusion)) {
                                    'berisiko', 'beresiko' => 'badge-danger',
                                    'normal', 'aman'       => 'badge-success',
                                    'waspada', 'menengah'  => 'badge-warning',
                                    default                => 'badge-default',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($conclusion) }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Section: Hasil Pemeriksaan di Rumah Sakit --}}
        <div class="section">
            <div class="section-header">
                <h2>Hasil Pemeriksaan di Rumah Sakit</h2>
            </div>
            <div class="section-body">
                @if($rujukan)
                    @php
                        $kk = $skrining->kondisiKesehatan;
                        $sistol = $kk->sdp ?? null;
                        $diastol = $kk->dbp ?? null;
                        $proteinUrine = $kk->pemeriksaan_protein_urine ?? null;
                    @endphp
                    <table class="info-table">
                        <tr>
                            <th>Pasien Datang</th>
                            <td>
                                @if($rujukan->pasien_datang == 1)
                                    <span class="badge badge-success">Ya</span>
                                @elseif($rujukan->pasien_datang == 0)
                                    <span class="badge badge-danger">Tidak</span>
                                @else
                                    <span style="color: #9CA3AF; font-style: italic;">Belum diisi</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Riwayat Tekanan Darah</th>
                            <td>
                                @if($sistol || $diastol)
                                    {{ ($sistol ?? '?') }}/{{ ($diastol ?? '?') }} mmHg
                                @else
                                    <span style="color: #9CA3AF; font-style: italic;">Belum ada data</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Hasil Pemeriksaan Protein Urin</th>
                            <td>
                                @if($proteinUrine)
                                    {{ $proteinUrine }}
                                @else
                                    <span style="color: #9CA3AF; font-style: italic;">Belum ada data</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Perlu Pemeriksaan Lanjutan</th>
                            <td>
                                @if($rujukan->perlu_pemeriksaan_lanjut == 1)
                                    <span class="badge badge-warning">Ya</span>
                                @elseif($rujukan->perlu_pemeriksaan_lanjut == 0)
                                    <span class="badge badge-success">Tidak</span>
                                @else
                                    <span style="color: #9CA3AF; font-style: italic;">Belum diisi</span>
                                @endif
                            </td>
                        </tr>
                        @if($riwayatRujukan && $riwayatRujukan->tindakan)
                            <tr>
                                <th>Tindakan</th>
                                <td>{{ $riwayatRujukan->tindakan }}</td>
                            </tr>
                        @endif
                        @if($riwayatRujukan && $riwayatRujukan->catatan)
                            <tr>
                                <th>Catatan Riwayat Rujukan</th>
                                <td>
                                    <div class="catatan-box">{{ $riwayatRujukan->catatan }}</div>
                                </td>
                            </tr>
                        @endif
                        @if($rujukan->catatan_rujukan)
                            <tr>
                                <th>Catatan Tambahan</th>
                                <td>
                                    <div class="catatan-box">{{ $rujukan->catatan_rujukan }}</div>
                                </td>
                            </tr>
                        @endif
                    </table>
                @else
                    <div class="empty-state">
                        Belum ada data pemeriksaan dari rumah sakit
                    </div>
                @endif
            </div>
        </div>

        {{-- Section: Resep Obat --}}
        @if($rujukan && $resepObats->count() > 0)
            <div class="section">
                <div class="section-header">
                    <h2>Resep Obat</h2>
                </div>
                <div class="section-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">No</th>
                                <th>Nama Obat</th>
                                <th>Dosis</th>
                                <th>Cara Penggunaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resepObats as $index => $resep)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-semibold">{{ $resep->resep_obat }}</td>
                                    <td>{{ $resep->dosis ?? '-' }}</td>
                                    <td>{{ $resep->penggunaan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Section: Kesimpulan Skrining Awal --}}
        <div class="section">
            <div class="section-header">
                <h2>Kesimpulan Skrining Awal</h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                    <tr>
                        <th>Jumlah Risiko Sedang</th>
                        <td>{{ $skrining->jumlah_resiko_sedang ?? '0' }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Risiko Tinggi</th>
                        <td>{{ $skrining->jumlah_resiko_tinggi ?? '0' }}</td>
                    </tr>
                    <tr>
                        <th>Kesimpulan</th>
                        <td>
                            @php
                                $conclusion2 = $skrining->kesimpulan ?? $skrining->status_pre_eklampsia ?? 'Normal';
                                $badgeClass2 = match(strtolower($conclusion2)) {
                                    'berisiko', 'beresiko' => 'badge-danger',
                                    'normal', 'aman'       => 'badge-success',
                                    'waspada', 'menengah'  => 'badge-warning',
                                    default                => 'badge-default',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass2 }}">{{ ucfirst($conclusion2) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Rekomendasi Awal</th>
                        <td>{{ $skrining->rekomendasi ?? '-' }}</td>
                    </tr>
                    @if($skrining->catatan)
                        <tr>
                            <th>Catatan dari Puskesmas</th>
                            <td>
                                <div class="catatan-box">{{ $skrining->catatan }}</div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p>Dokumen ini dicetak dari sistem DeLISA</p>
                    <p>© 2025 Dinas Kesehatan Kota Depok</p>
                </div>
                <div class="footer-right">
                    <div class="signature-box">
                        <p class="date">Depok, {{ now()->format('d F Y') }}</p>
                        <p class="name">Petugas RS</p>
                        <p class="line">(_______________________)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>