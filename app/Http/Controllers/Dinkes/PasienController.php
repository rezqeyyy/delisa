<?php

// Namespace: controller ini berada di modul Dinkes (khusus fitur Dinas Kesehatan).
namespace App\Http\Controllers\Dinkes;

// Import base Controller Laravel.
use App\Http\Controllers\Controller;

// Import DB facade untuk query langsung ke database (query builder / raw).
use Illuminate\Support\Facades\DB;

// Import model-model Eloquent yang terkait pasien & nifas.
use App\Models\Pasien;
use App\Models\Skrining;
use App\Models\KondisiKesehatan;
use App\Models\RiwayatKehamilanGpa;
use App\Models\RiwayatKehamilan;
use App\Models\PasienNifasBidan;
use App\Models\PasienNifasRs;
use App\Models\Kf;
use App\Models\RujukanRs;

class PasienController extends Controller
{
    /**
     * Method show:
     * - Menampilkan detail lengkap 1 pasien untuk Dinkes.
     * - Parameter $pasienId diambil dari route (ID di tabel pasiens).
     */
    public function show($pasienId)
    {
        // ===================== Identitas & alamat =====================
        /**
         * Ambil data identitas pasien:
         * - Join tabel pasiens (p) dengan users (u) melalui p.user_id = u.id
         * - Left join roles (r) untuk mengambil nama_role user (jika ada).
         * - Mengambil semua kolom dari pasiens (p.*) + sebagian kolom user + nama_role dari roles.
         */
        $pasien = Pasien::query()
            ->from('pasiens as p')
            // Join ke tabel users untuk ambil name, email, phone, dll.
            ->join('users as u', 'u.id', '=', 'p.user_id')
            // Left join roles untuk mengambil nama role (misalnya 'pasien').
            ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            // selectRaw agar bisa ambil kombinasi kolom bebas.
            ->selectRaw("
                p.*,
                u.name, u.email, u.photo, u.phone, u.address,
                r.nama_role
            ")
            // Filter berdasarkan id pasien yang dikirim dari route.
            ->where('p.id', $pasienId)
            // Ambil satu baris pertama (atau null jika tidak ada).
            ->first();

        // Jika pasien tidak ditemukan → kembalikan HTTP 404.
        abort_unless($pasien, 404);

        // ===================== Skrining TERBARU + tanggal terformat =====================
        // Catatan: alias 'tanggal' dan 'tanggal_waktu' agar langsung kebaca di blade.
        /**
         * Ambil skrining terbaru milik pasien:
         * - Tabel skrinings (s) di-filter berdasarkan pasien_id.
         * - Left join ke puskesmas (pk) untuk nama_puskesmas.
         * - Diurutkan berdasarkan created_at DESC sehingga yang terbaru di atas.
         * - Gunakan COALESCE(created_at, updated_at) untuk formatting tanggal,
         *   lalu di-format via to_char menjadi string tanggal dan tanggal+jam.
         */
        $skrining = Skrining::query()
            ->from('skrinings as s')
            // Left join ke puskesmas karena skrining bisa berasal dari puskesmas tertentu.
            ->leftJoin('puskesmas as pk', 'pk.id', '=', 's.puskesmas_id')
            // Filter semua skrining yang dimiliki pasien ini.
            ->where('s.pasien_id', $pasienId)
            // Urutkan dari skrining paling baru ke paling lama.
            ->orderByDesc('s.created_at')
            // Pilih semua kolom dari s.*, plus nama puskesmas, plus 2 kolom tanggal terformat.
            ->selectRaw("
                s.*,
                pk.nama_puskesmas as puskesmas_nama,
                to_char(COALESCE(s.created_at, s.updated_at), 'DD/MM/YYYY')         as tanggal,
                to_char(COALESCE(s.created_at, s.updated_at), 'DD/MM/YYYY HH24:MI') as tanggal_waktu
            ")
            // Ambil satu skrining paling baru.
            ->first();

        // ===================== Kondisi kesehatan terbaru (jika ada) =====================
        /**
         * Jika ada skrining terbaru, ambil kondisi kesehatan terkait skrining tersebut:
         * - Tabel kondisi_kesehatans berisi data tambahan (IMT, tekanan darah, dsb).
         * - Diurutkan desc by created_at kalau saja ada beberapa entri, ambil paling baru.
         * - Jika belum pernah diisi, $kondisi akan bernilai null.
         */
        $kondisi = $skrining
            ? KondisiKesehatan::query()
                ->where('skrining_id', $skrining->id)
                ->orderByDesc('created_at')
                ->first()
            : null;

        // ===================== GPA & riwayat kehamilan =====================
        /**
         * Ambil data riwayat GPA (Gravida-Para-Abortus) terakhir:
         * - Satu baris terakhir dari tabel riwayat_kehamilan_gpas untuk pasien ini.
         */
        $gpa = RiwayatKehamilanGpa::query()
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->first();

        /**
         * Ambil riwayat kehamilan (bisa lebih dari satu):
         * - Tabel riwayat_kehamilans menyimpan episode kehamilan sebelumnya.
         * - Diurutkan desc berdasarkan created_at.
         * - Dibatasi 10 entri terakhir agar tidak terlalu panjang di UI.
         */
        $riwayatKehamilan = RiwayatKehamilan::query()
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ===================== Episode Nifas milik pasien =====================
        /**
         * Mendefinisikan query subselect untuk mengambil semua ID episode nifas
         * milik pasien, dari dua sumber:
         * - pasien_nifas_bidan
         * - pasien_nifas_rs
         *
         * Keduanya di-union sehingga menjadi satu set ID episode (id dari masing-masing tabel).
         * Query ini nanti dipakai di whereIn pada tabel kf.
         */
        $episodeIdsQuery = PasienNifasBidan::query()
            // Ambil kolom id episode nifas dari bidan
            ->where('pasien_id', $pasienId)
            ->select('id')
            // Union dengan id episode dari RS
            ->union(
                PasienNifasRs::query()
                    ->where('pasien_id', $pasienId)
                    ->select('id')
            );

        // ========= Ringkasan KF (kunjungan nifas) =========
        /**
         * Hitung ringkasan kunjungan nifas (KF) berdasarkan id_nifas:
         * - Tabel kf menyimpan catatan kunjungan nifas per episode.
         * - whereIn('k.id_nifas', $episodeIdsQuery) memastikan hanya KF milik pasien ini.
         * - selectRaw: ambil kunjungan_nifas_ke sebagai integer (ke), dan COUNT(*) sebagai total.
         * - groupBy ke → hasil: per KF-1, KF-2, KF-3, KF-4 berapa total kunjungannya.
         */
        $kfSummary = Kf::query()
            ->from('kf as k')
            // Filter episode yang id_nifas-nya termasuk dalam episode pasien ini
            ->whereIn('k.id_nifas', $episodeIdsQuery)
            // Ambil nomor kunjungan dan jumlahnya
            ->selectRaw('k.kunjungan_nifas_ke::int as ke, COUNT(*)::int as total')
            // Kelompokkan berdasarkan nomor kunjungan nifas
            ->groupBy('ke')
            // Urutkan ascending per ke (1, 2, 3, 4)
            ->orderBy('ke')
            // Ambil semua hasil
            ->get();

        // ========= Ringkasan kesimpulan pantauan =========
        /**
         * Hitung ringkasan kesimpulan pantauan nifas:
         * - misalnya: Sehat, Dirujuk, Meninggal.
         * - groupBy k.kesimpulan_pantauan dan hitung jumlahnya.
         * - pluck('total','kesimpulan_pantauan') → associative array:
         *   [
         *       'Sehat'    => 10,
         *       'Dirujuk'  => 2,
         *       'Meninggal'=> 1,
         *   ]
         */
        $kfPantauan = Kf::query()
            ->from('kf as k')
            ->whereIn('k.id_nifas', $episodeIdsQuery)
            ->selectRaw("k.kesimpulan_pantauan, COUNT(*)::int as total")
            ->groupBy('k.kesimpulan_pantauan')
            ->pluck('total', 'kesimpulan_pantauan');

        // ===================== Rujukan RS terakhir =====================
        /**
         * Ambil riwayat rujukan RS:
         * - Tabel rujukan_rs (rr) di-leftJoin dengan rumah_sakits (rs) untuk nama RS.
         * - Filter berdasarkan pasien_id.
         * - Urutkan terbaru, batasi 5 record terakhir.
         */
        $rujukan = RujukanRs::query()
            ->from('rujukan_rs as rr')
            // join detail RS (nama rumah sakit)
            ->leftJoin('rumah_sakits as rs', 'rs.id', '=', 'rr.rs_id')
            // Pilih semua kolom rujukan + nama RS sebagai rs_nama
            ->selectRaw('rr.*, rs.nama as rs_nama')
            // Hanya rujukan milik pasien ini
            ->where('rr.pasien_id', $pasienId)
            // Yang terbaru di atas
            ->orderByDesc('rr.created_at')
            // Batasi 5 rujukan terakhir
            ->limit(5)
            // Ambil collection
            ->get();

        // ===================== Riwayat Penyakit (skrining terbaru) =====================
        /**
         * Dua variabel untuk riwayat penyakit:
         * - $riwayatPenyakit: array daftar nama penyakit "kategori utama"
         *   (Hipertensi, Diabetes, Asma, dsb) yang bernilai true.
         * - $penyakitLainnya: string isi jawaban_lainnya jika ada (untuk "Lainnya").
         */
        $riwayatPenyakit = [];
        $penyakitLainnya = null;

        // Hanya diproses jika pasien sudah pernah skrining
        if ($skrining) {
            /**
             * Mapping kode penyakit di UI => nama_pertanyaan di tabel kuisioner_pasiens.
             * Ini HARUS konsisten dengan controller lain (misalnya controller pasien)
             * agar filter / pengolahan data tetap sinkron.
             */
            $map = [
                'hipertensi'  => 'Hipertensi',
                'alergi'      => 'Alergi',
                'tiroid'      => 'Tiroid',
                'tb'          => 'TB',
                'jantung'     => 'Jantung',
                'hepatitis_b' => 'Hepatitis B',
                'jiwa'        => 'Jiwa',
                'autoimun'    => 'Autoimun',
                'sifilis'     => 'Sifilis',
                'diabetes'    => 'Diabetes',
                'asma'        => 'Asma',
                'lainnya'     => 'Lainnya',
            ];

            /**
             * Ambil kuisioner yang relevan:
             * - Tabel kuisioner_pasiens menyimpan master pertanyaan.
             * - status_soal = 'individu' → hanya ambil pertanyaan individu (riwayat penyakit).
             * - whereIn nama_pertanyaan pakai daftar map di atas.
             * - Hasil di-keyBy nama_pertanyaan sehingga mudah diakses:
             *   $kuisioner['Hipertensi']->id, dst.
             */
            $kuisioner = DB::table('kuisioner_pasiens')
                ->where('status_soal', 'individu')
                ->whereIn('nama_pertanyaan', array_values($map))
                ->get(['id', 'nama_pertanyaan'])
                ->keyBy('nama_pertanyaan');

            /**
             * Ambil jawaban untuk skrining tersebut:
             * - Tabel jawaban_kuisioners menyimpan jawaban per skrining.
             * - Filter berdasarkan skrining_id = $skrining->id.
             * - whereIn kuisioner_id sesuai kuisioner yang sudah diambil.
             * - Hasil di-keyBy kuisioner_id agar lookup lebih cepat.
             */
            $jawaban = DB::table('jawaban_kuisioners')
                ->where('skrining_id', $skrining->id)
                ->whereIn('kuisioner_id', $kuisioner->pluck('id')->all())
                ->get(['kuisioner_id', 'jawaban', 'jawaban_lainnya'])
                ->keyBy('kuisioner_id');

            /**
             * Loop semua kode penyakit yang kita definisikan di $map:
             * - $code = kode internal, mis: 'hipertensi'.
             * - $nama = teks pertanyaan di DB, mis: 'Hipertensi'.
             */
            foreach ($map as $code => $nama) {
                // Ambil id kuisioner untuk pertanyaan ini, jika ada.
                $qid = optional($kuisioner->get($nama))->id;

                // Ambil baris jawaban untuk kuisioner_id tersebut.
                $row = $qid ? $jawaban->get($qid) : null;

                // Jika ada row jawaban dan jawaban = true (berarti punya riwayat penyakit ini)
                if ($row && $row->jawaban) {
                    // Jika penyakit 'lainnya', taruh di $penyakitLainnya, bukan di list array utama.
                    if ($code === 'lainnya') {
                        $penyakitLainnya = $row->jawaban_lainnya;
                    } else {
                        // Kalau bukan 'lainnya', tambahkan nama penyakit ke array riwayatPenyakit.
                        $riwayatPenyakit[] = $nama;
                    }
                }
            }
        }

        /**
         * Return view detail pasien untuk Dinkes.
         * Variabel yang dikirim ke blade:
         * - pasien           → data identitas + user + role
         * - skrining         → skrining terbaru (punya ->tanggal & ->tanggal_waktu)
         * - kondisi          → kondisi_kesehatans terbaru (jika ada)
         * - gpa              → data GPA terakhir
         * - riwayatKehamilan → riwayat 10 kehamilan terakhir
         * - kfSummary        → ringkasan kunjungan nifas (jumlah KF 1,2,3,4)
         * - kfPantauan       → ringkasan kesimpulan pantauan (Sehat, Dirujuk, Meninggal, dst) dalam bentuk map
         * - rujukan          → 5 rujukan RS terakhir
         * - riwayatPenyakit  → array nama penyakit yang dicentang (kecuali 'lainnya')
         * - penyakitLainnya  → string jawaban_lainnya jika pasien mengisi penyakit lain.
         */
        return view('dinkes.pasien.pasien-show', [
            'pasien'            => $pasien,
            'skrining'          => $skrining,      // punya ->tanggal & ->tanggal_waktu
            'kondisi'           => $kondisi,
            'gpa'               => $gpa,
            'riwayatKehamilan'  => $riwayatKehamilan,
            'kfSummary'         => $kfSummary,
            'kfPantauan'        => $kfPantauan,
            'rujukan'           => $rujukan,
            'riwayatPenyakit'   => $riwayatPenyakit,
            'penyakitLainnya'   => $penyakitLainnya,
        ]);
    }
}
