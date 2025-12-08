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
use App\Models\PasienNifasRs;
use App\Models\KfKunjungan;   // ✅ Pemantauan KF pakai tabel kf_kunjungans
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

        // ===================== GPA =====================
        /**
         * Ambil data riwayat GPA (Gravida-Para-Abortus) terakhir:
         * - Satu baris terakhir dari tabel riwayat_kehamilan_gpas untuk pasien ini.
         */
        $gpa = RiwayatKehamilanGpa::query()
            ->where('pasien_id', $pasienId)
            ->orderByDesc('created_at')
            ->first();

                // ===================== Ringkasan KF via kf_kunjungans =====================
        /**
         * Sesuai ketentuan terbaru:
         * - Fokus per pasien: apakah KF1–KF4 PERNAH dilakukan, dan jika ya
         *   kesimpulan terakhirnya apa (Sehat/Dirujuk/Meninggal/dll).
         *
         * Langkah:
         * 1. Ambil semua episode nifas RS milik pasien ini (pasien_nifas_rs).
         * 2. Ambil seluruh kf_kunjungans milik episode tersebut.
         * 3. Untuk tiap jenis_kf (KF1..KF4), ambil 1 catatan terakhir (latest)
         *    sebagai status KF-n untuk pasien ini.
         * 4. (Opsional) Tetap hitung agregat kesimpulan_pantauan untuk keperluan lain.
         */

        // 1) Ambil semua episode nifas RS untuk pasien ini
        $episodeNifasRsIds = PasienNifasRs::query()
            ->where('pasien_id', $pasienId)
            ->pluck('id')
            ->all();

        // Default: jika tidak ada nifas RS, ringkasan KF kosong.
        $kfSummary  = collect();
        $kfPantauan = collect();

        if (!empty($episodeNifasRsIds)) {
            // 2) Ambil seluruh kf_kunjungans milik pasien ini
            $rawKf = KfKunjungan::query()
                ->from('kf_kunjungans as kk')
                ->whereIn('kk.pasien_nifas_id', $episodeNifasRsIds)
                ->orderBy('kk.created_at', 'desc')
                ->get();

            // Cek KF terbaru: jika kesimpulan = Meninggal/Wafat → jangan kirim ringkasan KF ke view
            $latestKf = $rawKf->first();

            $isMeninggal = $latestKf
                && in_array(
                    strtolower(trim((string) $latestKf->kesimpulan_pantauan)),
                    ['meninggal', 'wafat'],
                    true
                );

            if ($isMeninggal) {
                // Pasien sudah meninggal → ringkasan KF dikosongkan (tidak dikirim ke Blade)
                $kfSummary  = collect();
                $kfPantauan = collect();
            } else {
                // 3) Susun status per KF1..KF4 (ambil catatan terbaru per jenis_kf)
                $byKe = [];

                foreach ($rawKf as $row) {
                    // contoh isi: 'KF1', 'kf 2', '2', dll → ambil digit pertamanya
                    $jenis = strtolower(trim($row->jenis_kf));
                    $ke    = null;

                    if (preg_match('/(\d+)/', $jenis, $m)) {
                        $ke = (int) $m[1];
                    }

                    if ($ke === null || $ke < 1 || $ke > 4) {
                        continue;
                    }

                    // Jika belum pernah di-set, gunakan record ini sebagai "latest" untuk KF tersebut
                    if (! array_key_exists($ke, $byKe)) {
                        $byKe[$ke] = (object) [
                            'ke'         => $ke,
                            'done'       => true,
                            'kesimpulan' => $row->kesimpulan_pantauan, // bisa 'Sehat', 'Dirujuk', 'Meninggal', dst
                        ];
                    }
                }

                // Pastikan KF1–KF4 selalu ada (kalau belum pernah dilakukan → done = false)
                $kfSummary = collect();
                foreach ([1, 2, 3, 4] as $ke) {
                    if (! isset($byKe[$ke])) {
                        $kfSummary->push((object) [
                            'ke'         => $ke,
                            'done'       => false,
                            'kesimpulan' => null,
                        ]);
                    } else {
                        $kfSummary->push($byKe[$ke]);
                    }
                }

                // 4) Ringkasan kesimpulan pantauan (masih dihitung kalau nanti mau dipakai tempat lain)
                $kfPantauan = KfKunjungan::query()
                    ->from('kf_kunjungans as kk')
                    ->whereIn('kk.pasien_nifas_id', $episodeNifasRsIds)
                    ->selectRaw('kk.kesimpulan_pantauan, COUNT(*)::int as total')
                    ->groupBy('kk.kesimpulan_pantauan')
                    ->pluck('total', 'kesimpulan_pantauan');
            }
        }



        // ===================== Rujukan RS + ringkasan tindakan dokter =====================
        /**
         * Ambil riwayat rujukan RS:
         * - Header: tabel rujukan_rs (rr) + rumah_sakits (rs) → nama RS, status, pasien_datang, dsb.
         * - Detail klinis & tindakan dokter: tabel riwayat_rujukans
         *   (tanggal_datang, tekanan_darah, anjuran_kontrol, kunjungan_berikutnya, tindakan, catatan).
         *
         * Dinkes butuh:
         * - Riwayat rujukan pasien dari PKM ke RS.
         * - Status pasien rujukan (datang/tidak datang).
         * - Terapi/anjuran lanjutan & tindakan dokter.
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

        // Tambahkan ringkasan tindakan dokter & terapi (mengambil log terakhir dari riwayat_rujukans)
        if ($rujukan->isNotEmpty()) {
            $rujukanIds = $rujukan->pluck('id')->all();

            $riwayat = DB::table('riwayat_rujukans as rw')
                ->whereIn('rw.rujukan_id', $rujukanIds)
                ->orderBy('rw.created_at') // nanti ambil last() per rujukan
                ->get()
                ->groupBy('rujukan_id');

            $rujukan->transform(function ($item) use ($riwayat) {
                $last = optional($riwayat->get($item->id))->last();

                $item->rw_tanggal_datang        = $last->tanggal_datang        ?? null;
                $item->rw_tekanan_darah        = $last->tekanan_darah         ?? null;
                $item->rw_anjuran_kontrol      = $last->anjuran_kontrol       ?? null;
                $item->rw_kunjungan_berikutnya = $last->kunjungan_berikutnya ?? null;
                $item->rw_tindakan             = $last->tindakan              ?? null;
                $item->rw_catatan              = $last->catatan               ?? null;

                return $item;
            });
        }

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
         * - kfSummary        → ringkasan kunjungan nifas KF1–KF4 (dari kf_kunjungans)
         * - kfPantauan       → ringkasan kesimpulan pantauan nifas (Sehat/Dirujuk/Meninggal, dst)
         * - rujukan          → 5 rujukan RS terakhir + ringkasan tindakan dokter (riwayat_rujukans)
         * - riwayatPenyakit  → array nama penyakit yang dicentang (kecuali 'lainnya')
         * - penyakitLainnya  → string jawaban_lainnya jika pasien mengisi penyakit lain.
         */
        return view('dinkes.pasien.pasien-show', [
            'pasien'          => $pasien,
            'skrining'        => $skrining,      // punya ->tanggal & ->tanggal_waktu
            'kondisi'         => $kondisi,
            'gpa'             => $gpa,
            'kfSummary'       => $kfSummary,
            'kfPantauan'      => $kfPantauan,
            'rujukan'         => $rujukan,
            'riwayatPenyakit' => $riwayatPenyakit,
            'penyakitLainnya' => $penyakitLainnya,
        ]);
    }
}
