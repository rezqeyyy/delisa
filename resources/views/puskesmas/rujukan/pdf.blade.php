<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rujukan Pasien</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1D1D1D; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        .section { margin-bottom: 12px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; }
        .table th { background: #f7f7f7; text-align: left; }
        .meta { margin-bottom: 8px; }
        .meta div { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="section">
        <h1>Rujukan Pasien Ibu Hamil</h1>
        <div class="meta">
            <div><strong>Tanggal Rujukan:</strong> {{ optional($rujukan->created_at)->format('d/m/Y') ?? '-' }}</div>
            <div><strong>ID Rujukan:</strong> {{ $rujukan->id }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Data Pasien</h2>
        <table class="table">
            <tr>
                <th>Nama</th>
                <td>{{ $rujukan->nama_pasien ?? '-' }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td>{{ $rujukan->nik ?? '-' }}</td>
            </tr>
            <tr>
                <th>Tanggal Lahir</th>
                <td>{{ isset($rujukan->tanggal_lahir) ? \Carbon\Carbon::parse($rujukan->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $rujukan->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <th>No. Telepon</th>
                <td>{{ $rujukan->no_telepon ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Rumah Sakit Tujuan</h2>
        <table class="table">
            <tr>
                <th>Nama RS</th>
                <td>{{ $rujukan->nama_rs ?? '-' }}</td>
            </tr>
            <tr>
                <th>Alamat RS</th>
                <td>{{ $rujukan->alamat_rs ?? '-' }}</td>
            </tr>
            <tr>
                <th>Telepon RS</th>
                <td>{{ $rujukan->telepon_rs ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Ringkasan Skrining</h2>
        <table class="table">
            <tr>
                <th>Kesimpulan</th>
                <td>{{ $rujukan->kesimpulan ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Catatan Rujukan</h2>
        <table class="table">
            <tr>
                <th>Catatan dari Puskesmas</th>
                <td>{{ $rujukan->catatan_rujukan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Catatan Balasan RS</th>
                <td>{{ $rujukan->catatan ?? 'Belum ada balasan dari RS.' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Anjuran Kontrol & Pemeriksaan Berikutnya</h2>
        <table class="table">
            <tr>
                <th>Anjuran Kontrol</th>
                <td>{{ $rujukan->anjuran_kontrol ?? '-' }}</td>
            </tr>
            <tr>
                <th>Pemeriksaan Berikutnya</th>
                <td>{{ $rujukan->kunjungan_berikutnya ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="table">
            <tr>
                <th>Status</th>
                <td>
                    @if (($rujukan->anjuran_kontrol ?? null) || ($rujukan->kunjungan_berikutnya ?? null))
                        Pasien datang ke RS
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>