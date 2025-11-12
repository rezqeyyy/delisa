<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pasien Nifas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e91e8c;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #e91e8c;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #e91e8c;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #28a745;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #dc3545;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #ffc107;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DeLISA</h1>
        <p>Deteksi Dini Pre Eklampsia</p>
        <p><strong>Data Pasien Nifas</strong></p>
        <p>Tanggal: {{ date('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Pasien</th>
                <th>Nama Pasien</th>
                <th>Tanggal</th>
                <th>Alamat</th>
                <th>No Telp</th>
                <th>Penanganan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pasienNifas as $index => $pasien)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $pasien->id }}</td>
                <td>{{ $pasien->pasien->nik ?? 'Asep Dadang' }}</td>
                <td>{{ \Carbon\Carbon::parse($pasien->tanggal_mulai_nifas)->format('d/m/Y') }}</td>
                <td>{{ $pasien->rs->PProvinsi ?? 'Beji' }}</td>
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
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ $pasienNifas->count() }} pasien</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>