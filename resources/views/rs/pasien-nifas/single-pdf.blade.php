<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien Nifas - {{ $pasienNifas->pasien->user->name ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #E91E8C;
        }

        .header h1 {
            font-size: 18px;
            color: #E91E8C;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin: 10px 0;
        }

        .badge-beresiko {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .badge-waspada {
            background-color: #FEF3C7;
            color: #92400E;
            border: 1px solid #FCD34D;
        }

        .badge-normal {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #F3F4F6;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #E91E8C;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th,
        table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #E5E7EB;
        }

        table th {
            background-color: #F9FAFB;
            font-weight: bold;
            font-size: 10px;
            color: #6B7280;
        }

        table td {
            font-size: 11px;
        }

        .anak-card {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            background-color: #F9FAFB;
            page-break-inside: avoid;
        }

        .anak-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #E5E7EB;
        }

        .anak-number {
            width: 30px;
            height: 30px;
            background-color: #FDE2F3;
            border-radius: 50%;

            /* ✅ DomPDF-friendly centering */
            display: inline-block;
            line-height: 25px;
            text-align: center;

            font-weight: bold;
            color: #E91E8C;
            margin-right: 10px;

            /* ❌ buang ini biar nggak geser */
            float: none;
        }


        .anak-info {
            flex: 1;
        }

        .anak-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .anak-detail {
            font-size: 10px;
            color: #6B7280;
        }

        .data-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .data-row {
            display: table-row;
        }

        .data-label {
            display: table-cell;
            width: 40%;
            padding: 6px;
            background-color: #F3F4F6;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #E5E7EB;
        }

        .data-value {
            display: table-cell;
            padding: 6px;
            border: 1px solid #E5E7EB;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            font-size: 9px;
            color: #9CA3AF;
        }

        .kondisi-ibu-box {
            margin-top: 10px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid;
        }

        .kondisi-aman {
            background-color: #ECFDF5;
            border-color: #A7F3D0;
            color: #065F46;
        }

        .kondisi-perlu-tindak-lanjut {
            background-color: #FEE2E2;
            border-color: #FECACA;
            color: #991B1B;
        }

        .tag {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            border-radius: 4px;
            font-size: 9px;
            background-color: #FEF3C7;
            color: #92400E;
        }

        .check-icon {
            color: #10B981;
            font-weight: bold;
        }

        .cross-icon {
            color: #EF4444;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $statusType = $pasienNifas->status_type ?? 'normal';
        $statusDisplay = $pasienNifas->status_display ?? 'Tidak Berisiko';
        $isBeresiko = in_array($statusType, ['beresiko', 'waspada']);

        $badgeClass = match ($statusType) {
            'beresiko' => 'badge-beresiko',
            'waspada' => 'badge-waspada',
            default => 'badge-normal',
        };
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <h1>{{ $pasienNifas->rs->nama ?? 'Rumah Sakit' }}</h1>
        <p>{{ $pasienNifas->rs->alamat ?? '' }}</p>
        <h2 style="margin-top: 15px; font-size: 14px;">DATA PASIEN NIFAS</h2>
        <div class="badge {{ $badgeClass }}">
            Status Risiko: {{ $statusDisplay }}
        </div>
    </div>

    {{-- INFORMASI PASIEN --}}
    <div class="section">
        <div class="section-title">Informasi Pasien dan Data Kehamilan</div>
        <table>
            <tr>
                <td width="35%"><strong>Tanggal Pemeriksaan</strong></td>
                <td>
                    @if ($pasienNifas->tanggal_mulai_nifas)
                        {{ \Carbon\Carbon::parse($pasienNifas->tanggal_mulai_nifas)->translatedFormat('d F Y') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Nama</strong></td>
                <td>{{ $pasienNifas->pasien->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>NIK</strong></td>
                <td>{{ $pasienNifas->pasien->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Status Risiko Pre-Eklampsia</strong></td>
                <td><span class="badge {{ $badgeClass }}">{{ $statusDisplay }}</span></td>
            </tr>
            <tr>
                <td><strong>Usia Kehamilan</strong></td>
                <td>
                    @php $anakPertama = $pasienNifas->anakPasien->first(); @endphp
                    @if ($anakPertama)
                        {{ $anakPertama->usia_kehamilan_saat_lahir }} Minggu
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>
                    {{ $pasienNifas->pasien->PWilayah ?? '-' }},
                    {{ $pasienNifas->pasien->PKecamatan ?? '-' }},
                    {{ $pasienNifas->pasien->PKabupaten ?? '-' }},
                    {{ $pasienNifas->pasien->PProvinsi ?? '-' }}
                </td>
            </tr>
            <tr>
                <td><strong>Nomor Telepon</strong></td>
                <td>{{ $pasienNifas->pasien->user->phone ?? ($pasienNifas->pasien->no_telepon ?? '-') }}</td>
            </tr>
        </table>
    </div>

    {{-- RINGKASAN DATA ANAK --}}
    @php
        $totalAnak = $pasienNifas->anakPasien->count();
        $anakBBLR = $pasienNifas->anakPasien->filter(fn($a) => $a->berat_lahir_anak < 2.5)->count();
        $anakPreterm = $pasienNifas->anakPasien
            ->filter(function ($a) {
                $usia = (int) filter_var($a->usia_kehamilan_saat_lahir, FILTER_SANITIZE_NUMBER_INT);
                return $usia < 37;
            })
            ->count();
        $anakRiwayat = $pasienNifas->anakPasien
            ->filter(fn($a) => $a->riwayat_penyakit && count($a->riwayat_penyakit) > 0)
            ->count();
        $kondisiAman = $pasienNifas->anakPasien->where('kondisi_ibu', 'aman')->count();
        $kondisiPerluTindakLanjut = $pasienNifas->anakPasien->where('kondisi_ibu', 'perlu_tindak_lanjut')->count();
    @endphp

    <div class="section">
        <div class="section-title">Ringkasan Data Anak</div>
        <table>
            <tr>
                <td width="35%"><strong>Jumlah Anak</strong></td>
                <td>{{ $totalAnak }}</td>
            </tr>
            <tr>
                <td><strong>Jumlah BBLR (&lt; 2,5 kg)</strong></td>
                <td>{{ $anakBBLR }}</td>
            </tr>
            <tr>
                <td><strong>Jumlah Prematur (&lt; 37 minggu)</strong></td>
                <td>{{ $anakPreterm }}</td>
            </tr>
            <tr>
                <td><strong>Jumlah dengan Riwayat Komplikasi</strong></td>
                <td>{{ $anakRiwayat }}</td>
            </tr>
            @if ($isBeresiko)
                <tr style="background-color: #FEE2E2;">
                    <td><strong>Kondisi Ibu - Aman</strong></td>
                    <td style="color: #065F46; font-weight: bold;">{{ $kondisiAman }} anak</td>
                </tr>
                <tr style="background-color: #FEE2E2;">
                    <td><strong>Kondisi Ibu - Perlu Tindak Lanjut</strong></td>
                    <td style="color: #991B1B; font-weight: bold;">{{ $kondisiPerluTindakLanjut }} anak</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- DETAIL SETIAP ANAK --}}
    @if ($pasienNifas->anakPasien->count() > 0)
        <div class="section">
            <div class="section-title">Detail Data Anak</div>

            @foreach ($pasienNifas->anakPasien as $anak)
                <div class="anak-card">
                    <div class="anak-header">
                        <div class="anak-number">{{ $anak->anak_ke }}</div>
                        <div class="anak-info">
                            <div class="anak-name">{{ $anak->nama_anak ?? 'Anak ke-' . $anak->anak_ke }}</div>
                            <div class="anak-detail">
                                {{ $anak->jenis_kelamin }} • Lahir
                                {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d F Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="data-grid">
                        <div class="data-row">
                            <div class="data-label">Berat Lahir</div>
                            <div class="data-value">{{ $anak->berat_lahir_anak }} kg</div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Panjang Lahir</div>
                            <div class="data-value">{{ $anak->panjang_lahir_anak }} cm</div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Lingkar Kepala</div>
                            <div class="data-value">{{ $anak->lingkar_kepala_anak }} cm</div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Usia Kehamilan</div>
                            <div class="data-value">{{ $anak->usia_kehamilan_saat_lahir }} minggu</div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Buku KIA</div>
                            <div class="data-value">
                                <span class="{{ $anak->memiliki_buku_kia ? 'check-icon' : 'cross-icon' }}">
                                    {{ $anak->memiliki_buku_kia ? '✓ Ya' : '✗ Tidak' }}
                                </span>
                            </div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Buku KIA Bayi Kecil</div>
                            <div class="data-value">
                                <span class="{{ $anak->buku_kia_bayi_kecil ? 'check-icon' : 'cross-icon' }}">
                                    {{ $anak->buku_kia_bayi_kecil ? '✓ Ya' : '✗ Tidak' }}
                                </span>
                            </div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">IMD</div>
                            <div class="data-value">
                                <span class="{{ $anak->imd ? 'check-icon' : 'cross-icon' }}">
                                    {{ $anak->imd ? '✓ Ya' : '✗ Tidak' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($anak->riwayat_penyakit && count($anak->riwayat_penyakit) > 0)
                        <div style="margin-top: 10px;">
                            <strong style="font-size: 10px;">Riwayat Penyakit/Komplikasi Ibu:</strong><br>
                            @foreach ($anak->riwayat_penyakit as $penyakit)
                                <span class="tag">{{ $penyakit }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($anak->keterangan_masalah_lain)
                        <div style="margin-top: 10px; padding: 8px; background-color: #F9FAFB; border-radius: 4px;">
                            <strong style="font-size: 10px;">Keterangan Masalah Lain:</strong><br>
                            <span style="font-size: 10px;">{{ $anak->keterangan_masalah_lain }}</span>
                        </div>
                    @endif

                    @if ($isBeresiko && $anak->kondisi_ibu)
                        <div
                            class="kondisi-ibu-box {{ $anak->kondisi_ibu === 'aman' ? 'kondisi-aman' : 'kondisi-perlu-tindak-lanjut' }}">
                            <strong>Kondisi Ibu Saat Melahirkan:
                                {{ $anak->kondisi_ibu === 'aman' ? 'Aman' : 'Perlu Tindak Lanjut' }}
                            </strong>
                            @if ($anak->catatan_kondisi_ibu)
                                <br><span style="font-size: 10px;">{{ $anak->catatan_kondisi_ibu }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
        <p style="margin-top: 5px;">© {{ date('Y') }} Dinas Kesehatan Kota Depok — DeLISA</p>
    </div>
</body>

</html>
