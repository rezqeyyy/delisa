<?php

/**
 * Controller Dinkes untuk:
 * - Menampilkan daftar pasien nifas (index).
 * - Menampilkan detail satu pasien nifas (show).
 * - Mengekspor laporan pasien nifas ke file .xlsx (export).
 * - Menghapus status nifas pasien dari daftar nifas (destroy).
 */

namespace App\Http\Controllers\Dinkes;

// Base controller Laravel
use App\Http\Controllers\Controller;

// Request untuk akses query string & input form
use Illuminate\Http\Request;

// DB facade untuk query builder & transaksi database
use Illuminate\Support\Facades\DB;

// Carbon untuk manipulasi tanggal (format, parsing, dll)
use Carbon\Carbon;

// ==========================
//  Library untuk Excel .xlsx
// ==========================

// Spreadsheet: representasi workbook Excel di memory
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// Writer Xlsx: untuk menyimpan Spreadsheet ke format .xlsx
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Fill: pengaturan warna/fill background sel
use PhpOffice\PhpSpreadsheet\Style\Fill;
// Border: pengaturan border tabel
use PhpOffice\PhpSpreadsheet\Style\Border;
// Alignment: pengaturan alignment text (horizontal & vertical)
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// ==========================
//  Eloquent Models
// ==========================

use App\Models\Pasien;
use App\Models\PasienNifasBidan;
use App\Models\PasienNifasRs;
use App\Models\AnakPasien;
use App\Models\Kf;

class PasienNifasController extends Controller
{
    /**
     * Halaman index: daftar pasien nifas untuk Dinkes.
     *
     * - Mendukung pencarian nama/NIK via query string 'q'.
     * - Menggunakan query builder yang sama dengan export (buildPasienNifasQuery).
     */
    public function index(Request $request)
    {
        // Ambil parameter 'q' dari query string, default string kosong, lalu trim spasi.
        $q = trim($request->get('q', ''));

        // Bangun query dasar pasien nifas (join pasiens, users, pasien_nifas_bidan, pasien_nifas_rs)
        // lalu paginate 10 baris per halaman dan pertahankan query string (withQueryString).
        $rows = $this->buildPasienNifasQuery($q)
            ->paginate(10)
            ->withQueryString();

        // Render view daftar pasien nifas di Dinkes, kirim rows & nilai q untuk form pencarian.
        return view('dinkes.pasien-nifas.pasien-nifas', [
            'rows' => $rows,
            'q'    => $q,
        ]);
    }

    /**
     * Query dasar untuk pasien nifas (dipakai di index & export).
     *
     * - Menggabungkan data pasien (pasiens), user (users), dan status nifas
     *   (pasien_nifas_bidan, pasien_nifas_rs).
     * - Menghasilkan kolom:
     *   - pasien_id, name, nik, tempat_lahir, tanggal_lahir
     *   - role_penanggung (Bidan / Rumah Sakit / Puskesmas)
     *   - nifas_id (ID episode nifas, dari pnb.id / pnr.id)
     *   - sumber_nifas ('bidan' / 'rs' / null)
     */
    private function buildPasienNifasQuery(?string $q = '')
    {
        // Normalisasi parameter q: jika null → string kosong, lalu di-trim.
        $q = trim($q ?? '');

        // Mulai query dari tabel pasiens sebagai alias p
        $query = Pasien::query()
            ->from('pasiens as p')
            // Join ke tabel users (u) via p.user_id = u.id
            ->join('users as u', 'u.id', '=', 'p.user_id')
            // Left join ke pasien_nifas_bidan (pnb) via pasien_id
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            // Left join ke pasien_nifas_rs (pnr) via pasien_id
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            // selectRaw untuk membentuk kolom alias seperti pasien_id, role_penanggung, dll
            ->selectRaw(<<<'SQL'
            p.id as pasien_id,
            u.name,
            p.nik,
            p.tempat_lahir,
            p.tanggal_lahir,
            CASE 
                WHEN pnb.id IS NOT NULL THEN 'Bidan'
                WHEN pnr.id IS NOT NULL THEN 'Rumah Sakit'
                ELSE 'Puskesmas'
            END as role_penanggung,
            COALESCE(pnb.id, pnr.id) as nifas_id,
            CASE
                WHEN pnb.id IS NOT NULL THEN 'bidan'
                WHEN pnr.id IS NOT NULL THEN 'rs'
                ELSE NULL
            END as sumber_nifas
        SQL);

        // Filter: hanya pasien yang benar-benar punya episode nifas
        // yaitu jika pnb.id atau pnr.id tidak null.
        $query->where(function ($w) {
            $w->whereNotNull('pnb.id')
                ->orWhereNotNull('pnr.id');
        });

        // Jika q tidak kosong, tambah kondisi pencarian berdasarkan nama atau NIK.
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('u.name', 'ILIKE', "%{$q}%")
                    ->orWhere('p.nik', 'ILIKE', "%{$q}%");
            });
        }

        // Urutkan hasil berdasarkan nama user naik (A-Z).
        return $query->orderBy('u.name');
    }

    /**
     * Tampilkan detail satu pasien nifas untuk Dinkes.
     *
     * Parameter:
     * - $nifasId = ID episode nifas (bukan ID pasien).
     *
     * Langkah:
     * 1) Ambil data pasien & penanggung nifas berdasarkan $nifasId.
     * 2) Ambil data anak-anak pada episode nifas ini.
     * 3) Ambil riwayat penyakit nifas dari tabel anak_pasien (format JSON).
     * 4) Ambil kunjungan nifas (KF) terkait nifas_id ini.
     */
    public function show($nifasId)
    {
        // 1) Data utama pasien + penanggung nifas (berdasarkan ID nifas)
        $pasien = Pasien::query()
            ->from('pasiens as p')
            // Join ke users untuk ambil name/email/phone/address
            ->join('users as u', 'u.id', '=', 'p.user_id')
            // Left join pasien_nifas_bidan & pasien_nifas_rs untuk cek sumber nifas
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            // Left join bidan (b) & puskesmas (pk) untuk detail penanggung bidan
            ->leftJoin('bidans as b', 'b.id', '=', 'pnb.bidan_id')
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'b.puskesmas_id')
            // Left join rumah_sakits (rs) untuk detail penanggung RS
            ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'pnr.rs_id')
            // selectRaw untuk ambil kombinasi kolom dari beberapa tabel, plus case role_penanggung
            ->selectRaw("
            p.id,
            u.name,
            u.email,
            u.phone,
            u.address,
            p.nik,
            p.tempat_lahir,
            p.tanggal_lahir,
            p.\"PKecamatan\" as \"PKecamatan\",
            p.\"PKabupaten\" as \"PKabupaten\",
            p.\"PProvinsi\" as \"PProvinsi\",
            CASE 
                WHEN pnb.id IS NOT NULL THEN 'Bidan'
                WHEN pnr.id IS NOT NULL THEN 'Rumah Sakit'
                ELSE 'Puskesmas'
            END as role_penanggung,
            pnb.tanggal_mulai_nifas as tanggal_mulai_nifas_bidan,
            pnr.tanggal_mulai_nifas as tanggal_mulai_nifas_rs,
            pk.nama_puskesmas,
            rs.nama as nama_rs
        ")
            // Filter: episode nifas bisa berada di pasien_nifas_bidan atau pasien_nifas_rs
            ->where(function ($q) use ($nifasId) {
                $q->where('pnb.id', $nifasId)
                    ->orWhere('pnr.id', $nifasId);
            })
            // Ambil satu baris (atau null).
            ->first();

        // Jika episode nifas tidak ditemukan → 404 Not Found.
        abort_unless($pasien, 404);

        // Normalisasi tanggal lahir & mulai nifas untuk tampilan (format tanggal lokal)
        // Jika tanggal_lahir ada, format ke 'd F Y' (contoh: 05 Januari 2025) dengan translatedFormat.
        $pasien->tanggal_lahir_formatted = $pasien->tanggal_lahir
            ? Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y')
            : null;

        // Ambil tanggal mulai nifas dari bidan atau RS (mana yang tidak null).
        $tanggalMulaiNifas = $pasien->tanggal_mulai_nifas_bidan ?? $pasien->tanggal_mulai_nifas_rs;

        // Format tanggal mulai nifas jika ada.
        $pasien->tanggal_mulai_nifas_formatted = $tanggalMulaiNifas
            ? Carbon::parse($tanggalMulaiNifas)->translatedFormat('d F Y')
            : null;

        // 2) Data anak nifas: nifas_id = ID nifas (bukan ID pasien)
        // Ambil daftar anak yang terkait dengan episode nifas ini dari tabel anak_pasien.
        $anakList = AnakPasien::query()
            ->where('nifas_id', $nifasId)
            ->orderBy('anak_ke')    // urut berdasarkan anak ke-1, ke-2, dst.
            ->get();

        // 3) Riwayat penyakit nifas (diambil dari tabel anak_pasien)
        // Ambil kolom riwayat_penyakit (kemungkinan JSON) dan keterangan_masalah_lain untuk semua anak di episode ini.
        $riwayatPenyakitRaw = AnakPasien::query()
            ->where('nifas_id', $nifasId)
            ->get(['riwayat_penyakit', 'keterangan_masalah_lain']);

        /**
         * Olah riwayat penyakit:
         * - riwayat_penyakit di DB bisa berupa JSON (string) berisi array penyakit.
         * - Untuk setiap anak, decode JSON → array list penyakit.
         * - Setiap item menjadi object { nama_penyakit, keterangan_penyakit_lain }.
         * - Di-collect menjadi collection riwayatPenyakit yang unik per nama_penyakit.
         */
        $riwayatPenyakit = collect($riwayatPenyakitRaw)
            ->flatMap(function ($row) {
                // Ambil isi riwayat_penyakit untuk baris ini.
                $list = $row->riwayat_penyakit;

                // Jika bertipe string, asumsikan JSON dan coba decode.
                if (is_string($list)) {
                    $decoded = json_decode($list, true);
                    // Jika decode sukses (tidak error), gunakan hasil decode. Kalau gagal, pakai array kosong.
                    $list = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }

                // Jika bukan array, berarti datanya tidak valid → kembalikan array kosong.
                if (!is_array($list)) {
                    return [];
                }

                // Setiap item di array list dianggap nama penyakit.
                // Map ke object standar yang juga membawa keterangan_masalah_lain.
                return collect($list)->map(function ($nama) use ($row) {
                    return (object) [
                        'nama_penyakit'            => $nama,
                        'keterangan_penyakit_lain' => $row->keterangan_masalah_lain,
                    ];
                });
            })
            // Hilangkan duplikat berdasarkan nama_penyakit
            ->unique('nama_penyakit')
            // Reset index agar berurutan dari 0,1,2,...
            ->values();

        // 4) Kunjungan nifas (KF)
        // Ambil semua catatan kunjungan nifas dari tabel kf untuk nifas_id ini.
        $kunjunganNifas = Kf::query()
            ->from('kf')
            // Left join ke anak_pasien agar tahu nama anak & anak ke berapa.
            ->leftJoin('anak_pasien as a', 'a.id', '=', 'kf.id_anak')
            // Filter berdasarkan id_nifas
            ->where('kf.id_nifas', $nifasId)
            // Urutkan per tanggal kunjungan (kronologis)
            ->orderBy('kf.tanggal_kunjungan')
            // Pilih semua kolom kf ditambah nama_anak & anak_ke
            ->selectRaw('
            kf.*,
            a.nama_anak,
            a.anak_ke
        ')
            // Ambil collection
            ->get();

        // Kirim semua data ke view detail pasien nifas Dinkes.
        return view('dinkes.pasien-nifas.show', [
            'pasien'          => $pasien,
            'anakList'        => $anakList,
            'riwayatPenyakit' => $riwayatPenyakit,
            'kunjunganNifas'  => $kunjunganNifas,
        ]);
    }

    /**
     * EXPORT:
     * Unduh semua data pasien Nifas (sesuai filter pencarian 'q')
     * dalam bentuk file Excel .xlsx, dengan styling mirip export pasien PE.
     */
    public function export(Request $request)
    {
        // Ambil parameter 'q' dari query string untuk pencarian (nama/NIK).
        $q = trim($request->get('q', ''));

        // Bangun query dasar pasien nifas dengan filter q, lalu ambil semua baris (get).
        $rows = $this->buildPasienNifasQuery($q)->get();

        // Buat objek Spreadsheet baru.
        $spreadsheet = new Spreadsheet();
        // Ambil sheet aktif (default Sheet1).
        $sheet = $spreadsheet->getActiveSheet();

        // ========== 1. Judul ==========
        // Gabung sel A1 sampai F1 untuk judul besar.
        $sheet->mergeCells('A1:F1');
        // Set teks judul laporan.
        $sheet->setCellValue('A1', 'Laporan Data Pasien Nifas');
        // Set font judul: bold + ukuran 14.
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        // Atur alignment judul ke tengah (horizontal & vertikal).
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        // Tinggi baris 1 sedikit lebih besar untuk judul.
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Baris 2 dikosongkan (spasi di antara judul dan header tabel).
        $sheet->getRowDimension(2)->setRowHeight(5);

        // ========== 2. Header ==========
        // Baris header tabel (row ke-3).
        $headerRow = 3;

        // Definisi kolom header: key = kolom Excel, value = teks header.
        $headers = [
            'A' => 'ID Pasien',
            'B' => 'Nama Lengkap',
            'C' => 'NIK',
            'D' => 'Role Penanggung',
            'E' => 'Tempat Lahir',
            'F' => 'Tanggal Lahir',
        ];

        // Isi header di baris ke-3 (A3..F3).
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        // Range header (A3:F3).
        $headerRange = 'A' . $headerRow . ':F' . $headerRow;

        // Style header: bold + warna font putih.
        $sheet->getStyle($headerRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FFFFFFFF');

        // Fill header dengan warna solid (biru tua 4F81BD).
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');

        // Align teks header di tengah secara horizontal.
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set lebar kolom agar isi tabel rapi dan terbaca.
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Format kolom C (NIK) sebagai text (supaya leading zero tidak hilang).
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

        // ========== 3. Data ==========
        // Mulai baris data setelah header (row 4).
        $rowIndex = $headerRow + 1;

        // Loop setiap baris hasil query dan masukkan ke dalam sheet.
        foreach ($rows as $row) {
            // Format tanggal lahir ke 'd-m-Y' jika tidak null.
            $tglLahir = $row->tanggal_lahir
                ? Carbon::parse($row->tanggal_lahir)->format('d-m-Y')
                : '';

            // Isi sel per kolom.
            $sheet->setCellValue('A' . $rowIndex, $row->pasien_id);
            $sheet->setCellValue('B' . $rowIndex, $row->name);
            // NIK sebagai text explicit agar tidak dikonversi ke angka.
            $sheet->setCellValueExplicit(
                'C' . $rowIndex,
                $row->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValue('D' . $rowIndex, $row->role_penanggung);
            $sheet->setCellValue('E' . $rowIndex, $row->tempat_lahir);
            $sheet->setCellValue('F' . $rowIndex, $tglLahir);

            // Naikkan index baris untuk data berikutnya.
            $rowIndex++;
        }

        // Hitung baris terakhir yang berisi data (rowIndex telah melampaui).
        $lastDataRow = $rowIndex - 1;

        // Jika ternyata tidak ada data (lastDataRow < headerRow),
        // set lastDataRow minimal sama dengan headerRow.
        if ($lastDataRow < $headerRow) {
            $lastDataRow = $headerRow;
        }

        // Range seluruh tabel (header + data), untuk diberi border.
        $tableRange = 'A' . $headerRow . ':F' . $lastDataRow;

        // Beri border tipis di seluruh sel pada range tabel.
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Atur tinggi baris untuk semua baris dari header sampai baris terakhir.
        for ($r = $headerRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        // Freeze header: kunci baris di atas supaya header tetap terlihat saat scroll.
        $sheet->freezePane('A' . ($headerRow + 1));

        // Nama file export, pakai pola: data-pasien-nifas-YYYY-mm-dd.xlsx
        $fileName = 'data-pasien-nifas-' . now()->format('Y-m-d') . '.xlsx';

        // Buat writer Xlsx dari Spreadsheet.
        $writer = new Xlsx($spreadsheet);

        // Response streamDownload agar file langsung diunduh di browser.
        return response()->streamDownload(function () use ($writer) {
            // Simpan isi spreadsheet ke output stream (php://output).
            $writer->save('php://output');
        }, $fileName, [
            // Content-Type standar untuk file Excel modern (.xlsx).
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Hapus status nifas pasien dari daftar nifas.
     *
     * - Menghapus entri pasien dari tabel pasien_nifas_bidan & pasien_nifas_rs
     *   berdasarkan pasien_id.
     * - Tidak menghapus data pasien secara keseluruhan, hanya melepas status nifasnya.
     */
    public function destroy($pasienId)
    {
        // Bungkus operasi dalam transaksi untuk menjaga konsistensi data.
        DB::transaction(function () use ($pasienId) {
            // Hapus relasi nifas di sumber bidan jika ada.
            PasienNifasBidan::where('pasien_id', $pasienId)->delete();
            // Hapus relasi nifas di sumber RS jika ada.
            PasienNifasRs::where('pasien_id', $pasienId)->delete();

            // Catatan:
            // Jika ingin, di sini bisa dilanjutkan untuk menghapus/menyesuaikan
            // data turunan lain yang terkait nifas (misalnya kf, anak_pasien, dsb),
            // sesuai kebutuhan desain data. Untuk sekarang, cukup melepas dari daftar nifas.
        });

        // Redirect balik dengan flash message sukses.
        return back()->with('success', 'Pasien dihapus dari daftar nifas.');
    }
}
