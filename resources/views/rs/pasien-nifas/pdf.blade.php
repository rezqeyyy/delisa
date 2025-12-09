<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien Nifas - DeLISA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #1D1D1D;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #E91E8C;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #E91E8C;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .header .date {
            font-size: 11px;
            color: #999;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        thead {
            background-color: #E91E8C;
            color: white;
        }

        thead th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #E91E8C;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        tbody tr:nth-child(even) {
            background-color: #fef8fc;
        }

        tbody td {
            padding: 8px;
            font-size: 9px;
            border: 1px solid #e5e5e5;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }

        .status-normal {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-beresiko {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DeLISA</h1>
        <div class="subtitle">Deteksi Dini Pre Eklampsia</div>
        <div class="subtitle" style="font-weight: bold;">Data Pasien Nifas</div>
        <div class="date">Tanggal: {{ date('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">NIK Pasien</th>
                <th style="width: 20%;">Nama Pasien</th>
                <th style="width: 13%;">Tanggal Mulai Nifas</th>
                <th style="width: 22%;">Alamat</th>
                <th style="width: 13%;">No Telp</th>
                <th style="width: 14%;">Status Risiko</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pasienNifas as $pn)
                @php
                    $pas = optional($pn)->pasien;
                    $usr = optional($pas)->user;
                    $statusDisplay = $pn->status_display ?? 'Tidak Berisiko';
                    $statusType = $pn->status_type ?? 'normal';
                @endphp
                <tr>
                    <td>{{ $pas->nik ?? '-' }}</td>
                    <td>{{ $usr->name ?? '-' }}</td>
                    <td class="text-center">
                        {{ $pn->tanggal_mulai_nifas ? \Carbon\Carbon::parse($pn->tanggal_mulai_nifas)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $pas->PKecamatan ?? $pas->PWilayah ?? '-' }}</td>
                    <td class="text-center">{{ $usr->phone ?? '-' }}</td>
                    <td class="text-center">
                        @if($statusType === 'beresiko')
                            <span class="status-badge status-beresiko">Beresiko</span>
                        @else
                            <span class="status-badge status-normal">Tidak Berisiko</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data pasien nifas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Dinas Kesehatan Kota Depok — DeLISA
    </div>
</body>
</html>