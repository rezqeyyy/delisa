<?php

namespace App\Http\Controllers\Dinkes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Excel .xlsx
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PasienNifasController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $rows = $this->buildPasienNifasQuery($q)
            ->paginate(10)
            ->withQueryString();

        return view('dinkes.pasien-nifas.pasien-nifas', [
            'rows' => $rows,
            'q'    => $q,
        ]);
    }

    /**
     * Query dasar untuk pasien nifas (dipakai index & export).
     */
    private function buildPasienNifasQuery(?string $q = '')
    {
        $q = trim($q ?? '');

        $query = DB::table('pasiens as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
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

        // hanya pasien yang benar-benar nifas
        $query->where(function ($w) {
            $w->whereNotNull('pnb.id')
                ->orWhereNotNull('pnr.id');
        });

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('u.name', 'ILIKE', "%{$q}%")
                    ->orWhere('p.nik', 'ILIKE', "%{$q}%");
            });
        }

        return $query->orderBy('u.name');
    }


    /**
     * Tampilkan detail satu pasien nifas untuk Dinkes.
     */
    public function show($nifasId)
    {
        // 1) Data utama pasien + penanggung nifas (berdasarkan ID nifas)
        $pasien = DB::table('pasiens as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('pasien_nifas_bidan as pnb', 'pnb.pasien_id', '=', 'p.id')
            ->leftJoin('pasien_nifas_rs as pnr', 'pnr.pasien_id', '=', 'p.id')
            ->leftJoin('bidans as b', 'b.id', '=', 'pnb.bidan_id')
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 'b.puskesmas_id')
            ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'pnr.rs_id')
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
            ->where(function ($q) use ($nifasId) {
                $q->where('pnb.id', $nifasId)
                    ->orWhere('pnr.id', $nifasId);
            })
            ->first();

        abort_unless($pasien, 404);

        // Normalisasi tanggal lahir & mulai nifas
        $pasien->tanggal_lahir_formatted = $pasien->tanggal_lahir
            ? Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y')
            : null;

        $tanggalMulaiNifas = $pasien->tanggal_mulai_nifas_bidan ?? $pasien->tanggal_mulai_nifas_rs;
        $pasien->tanggal_mulai_nifas_formatted = $tanggalMulaiNifas
            ? Carbon::parse($tanggalMulaiNifas)->translatedFormat('d F Y')
            : null;

        // 2) Data anak nifas: nifas_id = ID nifas (bukan ID pasien)
        $anakList = DB::table('anak_pasien')
            ->where('nifas_id', $nifasId)
            ->orderBy('anak_ke')
            ->get();

        // 3) Riwayat penyakit nifas (diambil dari tabel anak_pasien)
        $riwayatPenyakitRaw = DB::table('anak_pasien')
            ->where('nifas_id', $nifasId)
            ->get(['riwayat_penyakit', 'keterangan_masalah_lain']);

        // Ubah menjadi collection berisi object { nama_penyakit, keterangan_penyakit_lain }
        $riwayatPenyakit = collect($riwayatPenyakitRaw)
            ->flatMap(function ($row) {
                $list = $row->riwayat_penyakit;

                // Kalau dari DB bentuknya masih string JSON → decode dulu
                if (is_string($list)) {
                    $decoded = json_decode($list, true);
                    $list = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }

                if (!is_array($list)) {
                    return [];
                }

                // Setiap item di array dianggap nama penyakit
                return collect($list)->map(function ($nama) use ($row) {
                    return (object) [
                        'nama_penyakit'           => $nama,
                        'keterangan_penyakit_lain' => $row->keterangan_masalah_lain,
                    ];
                });
            })
            // Optional: kalau mau hilangkan duplikat penyakit
            ->unique('nama_penyakit')
            ->values();


        // 4) Kunjungan nifas (KF)
        $kunjunganNifas = DB::table('kf')
            ->leftJoin('anak_pasien as a', 'a.id', '=', 'kf.id_anak')
            ->where('kf.id_nifas', $nifasId)
            ->orderBy('kf.tanggal_kunjungan')
            ->selectRaw('
            kf.*,
            a.nama_anak,
            a.anak_ke
        ')
            ->get();

        return view('dinkes.pasien-nifas.show', [
            'pasien'          => $pasien,
            'anakList'        => $anakList,
            'riwayatPenyakit' => $riwayatPenyakit,
            'kunjunganNifas'  => $kunjunganNifas,
        ]);
    }


    /**
     * EXPORT: Unduh semua data pasien Nifas (sesuai filter) dalam bentuk .xlsx
     * dengan styling mirip export data pasien PE.
     */
    public function export(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $rows = $this->buildPasienNifasQuery($q)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // ========== 1. Judul ==========
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Laporan Data Pasien Nifas');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Baris 2 kosong
        $sheet->getRowDimension(2)->setRowHeight(5);

        // ========== 2. Header ==========
        $headerRow = 3;
        $headers   = [
            'A' => 'ID Pasien',
            'B' => 'Nama Lengkap',
            'C' => 'NIK',
            'D' => 'Role Penanggung',
            'E' => 'Tempat Lahir',
            'F' => 'Tanggal Lahir',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        $headerRange = 'A' . $headerRow . ':F' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()
            ->setBold(true)
            ->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);

        // NIK sebagai text
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

        // ========== 3. Data ==========
        $rowIndex = $headerRow + 1;

        foreach ($rows as $row) {
            $tglLahir = $row->tanggal_lahir
                ? Carbon::parse($row->tanggal_lahir)->format('d-m-Y')
                : '';

            $sheet->setCellValue('A' . $rowIndex, $row->pasien_id);
            $sheet->setCellValue('B' . $rowIndex, $row->name);
            $sheet->setCellValueExplicit(
                'C' . $rowIndex,
                $row->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValue('D' . $rowIndex, $row->role_penanggung);
            $sheet->setCellValue('E' . $rowIndex, $row->tempat_lahir);
            $sheet->setCellValue('F' . $rowIndex, $tglLahir);

            $rowIndex++;
        }

        $lastDataRow = $rowIndex - 1;
        if ($lastDataRow < $headerRow) {
            $lastDataRow = $headerRow;
        }

        $tableRange = 'A' . $headerRow . ':F' . $lastDataRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        for ($r = $headerRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        // Freeze header
        $sheet->freezePane('A' . ($headerRow + 1));

        // Nama file mengikuti pola exportPe
        $fileName = 'data-pasien-nifas-' . now()->format('Y-m-d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function destroy($pasienId)
    {
        DB::transaction(function () use ($pasienId) {
            // Hapus keanggotaan nifas di kedua sumber (bidan / rs) jika ada
            DB::table('pasien_nifas_bidan')->where('pasien_id', $pasienId)->delete();
            DB::table('pasien_nifas_rs')->where('pasien_id', $pasienId)->delete();

            // Opsional: juga bisa bersihkan data turunan nifas lain jika bergantung
            // (mis. rujukan_nifas/kf/anak_pasien) – abaikan bila tidak diperlukan.
        });

        return back()->with('success', 'Pasien dihapus dari daftar nifas.');
    }
}
