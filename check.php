<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PasienNifasRs;
use App\Models\KfKunjungan;

echo "=== CEK DATA PASIEN NIFAS UNTUK TESTING ===\n\n";

// 1. Cek total pasien
$totalPasien = PasienNifasRs::count();
echo "1. ✅ Total Pasien Nifas: " . $totalPasien . "\n";

if ($totalPasien == 0) {
    echo "\n❌ TIDAK ADA DATA PASIEN NIFAS!\n";
    echo "   Anda perlu buat data pasien nifas dulu melalui:\n";
    echo "   - RS: Input data pasien nifas\n";
    echo "   - Atau melalui seeder/database\n";
    exit;
}

// 2. Ambil data pertama
$pasien = PasienNifasRs::with(['pasien.user', 'rs'])->first();

echo "2. ✅ Data Pasien Pertama:\n";
echo "   - ID: " . $pasien->id . "\n";
echo "   - Nama: " . ($pasien->pasien->user->name ?? 'N/A') . "\n";
echo "   - RS: " . ($pasien->rs->nama ?? 'N/A') . "\n";
echo "   - Tanggal Melahirkan: " . ($pasien->tanggal_melahirkan ? $pasien->tanggal_melahirkan->format('d/m/Y') : '-') . "\n";

// 3. Cek KF yang sudah ada
echo "\n3. ✅ Status KF Pasien:\n";
for ($i = 1; $i <= 3; $i++) {
    $selesai = $pasien->isKfSelesai($i) ? '✅ SUDAH' : '❌ BELUM';
    $status = $pasien->getKfStatus($i);
    echo "   - KF{$i}: {$selesai} (Status: {$status})\n";
}

// 4. Cek data KF di tabel baru
$kfCount = KfKunjungan::where('pasien_nifas_id', $pasien->id)->count();
echo "\n4. ✅ Data KF di tabel baru (kf_kunjungans): " . $kfCount . "\n";

// 5. Cek foreign keys
echo "\n5. ✅ Foreign Keys di pasien_nifas_rs:\n";
echo "   - kf1_id: " . ($pasien->kf1_id ? '✅ ADA' : '❌ KOSONG') . "\n";
echo "   - kf2_id: " . ($pasien->kf2_id ? '✅ ADA' : '❌ KOSONG') . "\n";
echo "   - kf3_id: " . ($pasien->kf3_id ? '✅ ADA' : '❌ KOSONG') . "\n";

// 6. Instruksi testing
echo "\n🎯 INSTRUKSI TESTING:\n";
echo "===========================================\n";
if ($pasien->isKfSelesai(1)) {
    echo "1. KF1 sudah ada, test KF2 atau KF3\n";
} else {
    echo "1. KF1 belum ada, bisa test KF1\n";
}

echo "2. Buka browser: http://localhost/delisa\n";
echo "3. Login sebagai PUSKESMAS\n";
echo "4. Akses: Menu Pasien Nifas\n";
echo "5. Pilih pasien dengan ID: " . $pasien->id . "\n";
echo "6. Klik tombol 'Catat KF1/KF2/KF3'\n";
echo "7. Isi form baru dan submit\n";
echo "\n🔍 VERIFIKASI SETELAH SUBMIT:\n";
echo "- Data harus tersimpan di tabel kf_kunjungans\n";
echo "- Foreign key (kf1_id/kf2_id/kf3_id) harus terupdate\n";
echo "- Kolom lama (kf1_tanggal, kf1_catatan) juga terupdate\n";