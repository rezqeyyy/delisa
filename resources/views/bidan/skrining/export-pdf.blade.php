<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Skrining Ibu Hamil</title>
    <style>
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 { 
            margin: 0; 
            color: #333;
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
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge-risiko {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            display: inline-block;
        }
        .badge-aman {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            display: inline-block;
        }
        .badge-belum {
            background-color: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DATA SKRINING IBU HAMIL</h1>
        <p>Dinas Kesehatan Kota Depok - DeLISA</p>
        <p>Periode: {{ date('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <strong>Total Data:</strong> {{ $skrinings->count() }} pasien<br>
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Nama Pasien</th>
                <th style="width: 15%">NIK</th>
                <th style="width: 10%">Tanggal Pengisian</th>
                <th style="width: 25%">Alamat</th>
                <th style="width: 12%">No Telp</th>
                <th style="width: 18%">Kesimpulan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($skrinings as $skrining)
                @php
                    $nama = optional(optional($skrining->pasien)->user)->name ?? '-';
                    $nik = optional($skrining->pasien)->nik ?? '-';
                    $tanggal = \Carbon\Carbon::parse($skrining->created_at)->format('d/m/Y');
                    $alamat = optional(optional($skrining->pasien)->user)->address ?? '-';
                    $telp = optional(optional($skrining->pasien)->user)->phone ?? '-';
                    
                    $badgeClass = match($skrining->badge_class) {
                        'berisiko' => 'badge-risiko',
                        'tidak-berisiko' => 'badge-aman',
                        default => 'badge-belum'
                    };
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $nama }}</td>
                    <td>{{ $nik }}</td>
                    <td>{{ $tanggal }}</td>
                    <td>{{ $alamat }}</td>
                    <td>{{ $telp }}</td>
                    <td><span class="{{ $badgeClass }}">{{ $skrining->kesimpulan }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} Dinas Kesehatan Kota Depok - DeLISA</p>
        <p>Dokumen ini dicetak secara otomatis dari sistem DeLISA</p>
    </div>
</body>
</html>