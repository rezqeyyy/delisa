<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skrining;
use App\Models\Puskesmas;
use Illuminate\Support\Facades\DB;
use App\Models\RiwayatKehamilanGpa;
use App\Models\KondisiKesehatan;
use Carbon\Carbon;

class SkriningController extends Controller
{
    public function create(Request $request)
    {
        $puskesmasId = (int) $request->query('puskesmas_id');
        $user        = Auth::user();
        $pasienId    = optional($user->pasien)->id;

        // Jika datang dari modal dengan puskesmas terpilih, SELALU buat skrining baru
        if ($puskesmasId && $pasienId && Puskesmas::whereKey($puskesmasId)->exists()) {
            Skrining::create([
                'pasien_id'               => $pasienId,
                'puskesmas_id'            => $puskesmasId,
                'status_pre_eklampsia'    => null,
                'jumlah_resiko_sedang'    => null,
                'jumlah_resiko_tinggi'    => null,
                'kesimpulan'              => null,
                'step_form'               => 1,
                'tindak_lanjut'           => false,
                'checked_status'          => false,
            ]);
        }

        return view('pasien.skrining.data-diri');
    }

    // === TAMBAH: Endpoint alternatif jika nanti mau POST dari modal ===
    public function storePengajuan(Request $request)
    {
        $payload = $request->validate([
            'puskesmas_id' => ['required', 'integer', 'exists:puskesmas,id'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 403);

        Skrining::create([
            'pasien_id'               => $pasienId,
            'puskesmas_id'            => $payload['puskesmas_id'],
            'status_pre_eklampsia'    => null,
            'jumlah_resiko_sedang'    => null,
            'jumlah_resiko_tinggi'    => null,
            'kesimpulan'              => null,
            'step_form'               => 1,
            'tindak_lanjut'           => false,
            'checked_status'          => false,
        ]);

        return redirect()
            ->route('pasien.data-diri', ['puskesmas_id' => $payload['puskesmas_id']])
            ->with('ok', 'Pengajuan skrining dibuat. Silakan isi Data Diri.');
    }

    // === TAMBAH: Simpan Data Diri Pasien ===
    public function storeDataDiri(Request $request)
    {
        $data = $request->validate([
            'tempat_lahir'        => ['nullable', 'string', 'max:150'],
            'tanggal_lahir'       => ['nullable', 'date'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'address'             => ['nullable', 'string', 'max:255'],
            'status_perkawinan'   => ['nullable'], 
            'PKecamatan'          => ['nullable', 'string', 'max:150'],
            'PKabupaten'          => ['nullable', 'string', 'max:150'],
            'PProvinsi'           => ['nullable', 'string', 'max:150'],
            'PWilayah'            => ['nullable', 'string', 'max:150'],
            'rt'                  => ['nullable', 'string'],
            'rw'                  => ['nullable', 'string'],
            'kode_pos'            => ['nullable', 'string', 'max:10'],

            'pekerjaan'           => ['nullable', 'string', 'max:150'],
            'pendidikan'          => ['nullable', 'string', 'max:150'],
            'pembiayaan_kesehatan'=> ['nullable', 'string', 'max:100'],
            'golongan_darah'      => ['nullable', 'string', 'max:3'],

            'no_jkn'              => ['nullable', 'string', 'max:30'],
        ]);

        $user = Auth::user();
        $pasien = $user->pasien;

        DB::transaction(function () use ($user, $pasien, $data) {
            // Pastikan objek tersedia
            abort_unless($user && $pasien, 401);

            // Update users via query Eloquent (hindari error pada instance/save)
            \App\Models\User::query()
                ->whereKey($user->id)
                ->update([
                    'phone'   => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);

            $pasien->update([
                'tempat_lahir'         => $data['tempat_lahir'] ?? null,
                'tanggal_lahir'        => $data['tanggal_lahir'] ?? null,
                'status_perkawinan'    => isset($data['status_perkawinan']) ? (int) $data['status_perkawinan'] : null,
                'PKecamatan'           => $data['PKecamatan'] ?? null,
                'PKabupaten'           => $data['PKabupaten'] ?? null,
                'PProvinsi'            => $data['PProvinsi'] ?? null,
                'PWilayah'             => $data['PWilayah'] ?? null,
                'kode_pos'             => $data['kode_pos'] ?? null,
                'rt'                   => $data['rt'] ?? null,
                'rw'                   => $data['rw'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'pendidikan'           => $data['pendidikan'] ?? null,
                'pembiayaan_kesehatan' => $data['pembiayaan_kesehatan'] ?? null,
                'golongan_darah'       => $data['golongan_darah'] ?? null,
                'no_jkn'               => ($data['pembiayaan_kesehatan'] ?? null) === 'BPJS Kesehatan'
                                          ? ($data['no_jkn'] ?? null)
                                          : null,
            ]);
        });

        return redirect()->route('pasien.riwayat-kehamilan-gpa')->with('ok', 'Data diri berhasil disimpan.');
    }

    public function riwayatKehamilanGpa(Request $request)
    {
        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;

        $skrining = $pasienId
            ? Skrining::where('pasien_id', $pasienId)->latest()->first()
            : null;

        $gpa = $skrining ? $skrining->riwayatKehamilanGpa()->first() : null;

        return view('pasien.skrining.riwayat-kehamilan-gpa', compact('gpa'));
    }

    // TAMBAH: Simpan Riwayat Kehamilan & Persalinan (GPA)
    public function storeRiwayatKehamilanGpa(Request $request)
    {
        $data = $request->validate([
            'total_kehamilan'   => ['nullable', 'integer', 'min:0'],
            'total_persalinan'  => ['nullable', 'integer', 'min:0'],
            'total_abortus'     => ['nullable', 'integer', 'min:0'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 401);

        // Ambil skrining terbaru milik pasien
        $skrining = Skrining::where('pasien_id', $pasienId)
            ->latest()
            ->firstOrFail();

        DB::transaction(function () use ($skrining, $pasienId, $data) {
            RiwayatKehamilanGpa::updateOrCreate(
                [
                    'skrining_id' => $skrining->id,
                    'pasien_id'   => $pasienId,
                ],
                [
                    'total_kehamilan'  => isset($data['total_kehamilan'])  ? (string) $data['total_kehamilan']  : null,
                    'total_persalinan' => isset($data['total_persalinan']) ? (string) $data['total_persalinan'] : null,
                    'total_abortus'    => isset($data['total_abortus'])    ? (string) $data['total_abortus']    : null,
                ]
            );

            // Update step form agar progress lanjut ke langkah berikutnya
            Skrining::query()
                ->whereKey($skrining->id)
                ->update(['step_form' => 2]);
        });

        return redirect()
            ->route('pasien.kondisi-kesehatan-pasien')
            ->with('ok', 'Riwayat kehamilan & persalinan berhasil disimpan.');
    }

    public function kondisiKesehatanPasien(Request $request)
    {
        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;

        $skrining = $pasienId
            ? Skrining::where('pasien_id', $pasienId)->latest()->first()
            : null;

        $kk = $skrining ? $skrining->kondisiKesehatan()->first() : null;

        return view('pasien.skrining.kondisi-kesehatan-pasien', compact('kk'));
    }

    public function storeKondisiKesehatanPasien(Request $request)
    {
        $data = $request->validate([
            'tinggi_badan'               => ['required', 'integer', 'min:1'],
            'berat_badan_saat_hamil'     => ['required', 'numeric', 'min:1'],
            'sdp'                        => ['nullable', 'integer', 'min:0'],
            'dbp'                        => ['nullable', 'integer', 'min:0'],
            'pemeriksaan_protein_urine'  => ['required', 'in:Negatif,Positif 1,Positif 2,Positif 3,Belum dilakukan Pemeriksaan'],
            'hpht'                       => ['required', 'date'],
            'tanggal_skrining'           => ['required', 'date', 'after_or_equal:hpht'],
            // nilai dari JS (opsional); server akan hitung ulang jika tanggal valid
            'usia_kehamilan_minggu'      => ['nullable', 'integer', 'min:0'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 401);

        // Ambil skrining terbaru milik pasien
        $skrining = Skrining::where('pasien_id', $pasienId)->latest()->firstOrFail();

        // Hitung IMT server-side
        $tinggiM = $data['tinggi_badan'] / 100;
        $imt = round($data['berat_badan_saat_hamil'] / ($tinggiM * $tinggiM), 2);

        // Kategorisasi IMT selaras dengan JS
        $kategoriImt = 'Normal';
        if ($imt < 17) {
            $kategoriImt = 'Kurus Berat';
        } elseif ($imt >= 17 && $imt <= 18.4) {
            $kategoriImt = 'Kurus Ringan';
        } elseif ($imt > 25 && $imt <= 27) {
            $kategoriImt = 'Gemuk Ringan';
        } elseif ($imt > 27) {
            $kategoriImt = 'Gemuk Berat';
        }

        // Anjuran kenaikan BB berdasarkan kategori IMT
        $anjuran = match ($kategoriImt) {
            'Kurus Berat', 'Kurus Ringan' => '12.5 - 18 kg',
            'Normal'                      => '11.5 - 16 kg',
            'Gemuk Ringan'                => '7 - 11.5 kg',
            'Gemuk Berat'                 => '5 - 9 kg',
            default                       => 'Tidak Ditentukan',
        };

        // Hitung MAP server-side jika angka ada
        $map = 0.0;
        if (!empty($data['sdp']) && !empty($data['dbp']) && $data['sdp'] > 0 && $data['dbp'] > 0) {
            $map = round((($data['sdp'] + 2 * $data['dbp']) / 3), 2);
        }

        // Usia kehamilan (minggu) dari HPHT dan tanggal skrining
        $hpht = Carbon::parse($data['hpht']);
        $tglSkrining = Carbon::parse($data['tanggal_skrining']);
        $diffDays = $hpht->diffInDays($tglSkrining, false);
        $usiaMinggu = $diffDays >= 0 ? intdiv($diffDays, 7) : 0;

        // Fallback: jika perhitungan gagal, pakai nilai dari input hidden bila ada
        if ($diffDays < 0 && isset($data['usia_kehamilan_minggu'])) {
            $usiaMinggu = (int) $data['usia_kehamilan_minggu'];
        }

        // TPP = HPHT + 280 hari
        $tpp = $hpht->copy()->addDays(280)->toDateString();

        DB::transaction(function () use ($skrining, $data, $imt, $kategoriImt, $anjuran, $map, $usiaMinggu, $tpp) {
            KondisiKesehatan::updateOrCreate(
                ['skrining_id' => $skrining->id],
                [
                    'tinggi_badan'                  => (int) $data['tinggi_badan'],
                    'berat_badan_saat_hamil'        => (float) $data['berat_badan_saat_hamil'],
                    'imt'                           => (float) $imt,
                    'status_imt'                    => $kategoriImt,
                    'hpht'                          => $data['hpht'], // nullable di DB tapi divalidasi required di sini
                    'tanggal_skrining'              => $data['tanggal_skrining'],
                    'usia_kehamilan'                => (int) $usiaMinggu,
                    'tanggal_perkiraan_persalinan'  => $tpp,
                    'anjuran_kenaikan_bb'           => $anjuran,
                    'sdp'                           => (int) ($data['sdp'] ?? 0),
                    'dbp'                           => (int) ($data['dbp'] ?? 0),
                    'map'                           => (float) $map,
                    'pemeriksaan_protein_urine'     => $data['pemeriksaan_protein_urine'],
                ]
            );

            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 3]);
        });

        return redirect()->route('pasien.riwayat-penyakit-pasien')->with('ok', 'Kondisi kesehatan pasien berhasil disimpan.');
    }

    public function riwayatPenyakitPasien(Request $request)
    {
        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;

        $skrining = $pasienId
            ? Skrining::where('pasien_id', $pasienId)->latest()->first()
            : null;

        // Mapping kode -> nama pertanyaan (di kuisioner_pasiens)
        $map = [
            'hipertensi_kronik'          => 'Hipertensi Kronik',
            'ginjal'                     => 'Ginjal',
            'autoimun_sle'               => 'Autoimun, SLE',
            'anti_phospholipid_syndrome' => 'Anti Phospholipid Syndrome',
            'lainnya'                    => 'Lainnya',
        ];

        $selected = [];
        $penyakitLainnya = null;

        if ($skrining) {
            // Ambil kuisioner id untuk item yang relevan
            $kuisioner = DB::table('kuisioner_pasiens')
                ->where('status_soal', 'individu')
                ->whereIn('nama_pertanyaan', array_values($map))
                ->get(['id', 'nama_pertanyaan'])
                ->keyBy('nama_pertanyaan');

            // Ambil jawaban yang terkait skrining & kuisioner tersebut
            $jawaban = DB::table('jawaban_kuisioners')
                ->where('skrining_id', $skrining->id)
                ->whereIn('kuisioner_id', $kuisioner->pluck('id')->all())
                ->get(['kuisioner_id', 'jawaban', 'jawaban_lainnya']);

            // Susun selected berdasarkan jawaban=true
            $byKuisionerId = $jawaban->keyBy('kuisioner_id');

            foreach ($map as $code => $nama) {
                $qid = optional($kuisioner->get($nama))->id;
                if ($qid && optional($byKuisionerId->get($qid))->jawaban) {
                    $selected[] = $code;
                    if ($code === 'lainnya') {
                        $penyakitLainnya = optional($byKuisionerId->get($qid))->jawaban_lainnya;
                    }
                }
            }
        }

        return view('pasien.skrining.riwayat-penyakit-pasien', compact('selected', 'penyakitLainnya'));
    }

    public function storeRiwayatPenyakitPasien(Request $request)
    {
        $data = $request->validate([
            'penyakit'          => ['array'],
            'penyakit.*'        => ['in:hipertensi_kronik,ginjal,autoimun_sle,anti_phospholipid_syndrome,lainnya'],
            'penyakit_lainnya'  => ['nullable', 'string', 'max:255'],
        ]);

        $user     = Auth::user();
        $pasienId = optional($user->pasien)->id;
        abort_unless($pasienId, 401);

        $skrining = Skrining::where('pasien_id', $pasienId)->latest()->firstOrFail();

        // Mapping kode -> [nama_pertanyaan, resiko]
        $map = [
            'hipertensi_kronik'          => ['nama' => 'Hipertensi Kronik',          'resiko' => 'tinggi'],
            'ginjal'                     => ['nama' => 'Ginjal',                     'resiko' => 'tinggi'],
            'autoimun_sle'               => ['nama' => 'Autoimun, SLE',              'resiko' => 'tinggi'],
            'anti_phospholipid_syndrome' => ['nama' => 'Anti Phospholipid Syndrome', 'resiko' => 'tinggi'],
            'lainnya'                    => ['nama' => 'Lainnya',                    'resiko' => 'non-risk'],
        ];

        $dipilih = $data['penyakit'] ?? [];
        $lainnyaText = trim((string)($data['penyakit_lainnya'] ?? ''));

        DB::transaction(function () use ($skrining, $map, $dipilih, $lainnyaText) {
            foreach ($map as $code => $def) {
                // Pastikan kuisioner ada
                $row = DB::table('kuisioner_pasiens')
                    ->where('nama_pertanyaan', $def['nama'])
                    ->where('status_soal', 'individu')
                    ->first();

                $qid = $row?->id ?? DB::table('kuisioner_pasiens')->insertGetId([
                    'nama_pertanyaan' => $def['nama'],
                    'status_soal'     => 'individu',
                    'resiko'          => $def['resiko'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $isSelected = in_array($code, $dipilih, true);

                // Simpan jawaban (upsert by skrining_id + kuisioner_id)
                DB::table('jawaban_kuisioners')->updateOrInsert(
                    ['skrining_id' => $skrining->id, 'kuisioner_id' => $qid],
                    [
                        'jawaban'         => $isSelected,
                        'jawaban_lainnya' => ($code === 'lainnya' && $isSelected) ? $lainnyaText : null,
                        'updated_at'      => now(),
                        'created_at'      => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            // Step form -> 4 (lanjut ke riwayat penyakit keluarga)
            Skrining::query()->whereKey($skrining->id)->update(['step_form' => 4]);
        });

        return redirect()->route('pasien.riwayat-penyakit-keluarga')
            ->with('ok', 'Riwayat penyakit pasien berhasil disimpan.');
    }

    public function preEklampsia(Request $request)
    {
        return view('pasien.skrining.preeklampsia');
    }

    public function show(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-show', compact('skrining'));
    }

    public function edit(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);
        return view('pasien.skrining-edit', compact('skrining'));
    }

    public function destroy(Skrining $skrining)
    {
        $this->authorizeAccess($skrining);

        DB::transaction(function () use ($skrining) {
            // Hapus child tanpa bergantung pada model yang belum ada
            DB::table('jawaban_kuisioners')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilans')->where('skrining_id', $skrining->id)->delete();
            DB::table('riwayat_kehamilan_gpas')->where('skrining_id', $skrining->id)->delete();
            DB::table('kondisi_kesehatans')->where('skrining_id', $skrining->id)->delete();

            $skrining->delete();
        });

        return redirect()->route('pasien.dashboard')->with('ok', 'Skrining berhasil dihapus.');
    }
    
    public function puskesmasSearch(Request $request)
    {
        $q = trim($request->query('q', ''));

        $rows = Puskesmas::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('nama_puskesmas', 'like', "%{$q}%")
                   ->orWhere('kecamatan', 'like', "%{$q}%")
                   ->orWhere('lokasi', 'like', "%{$q}%");
            })
            ->orderBy('nama_puskesmas')
            ->limit(20)
            ->get(['id', 'nama_puskesmas', 'kecamatan']);

        return response()->json($rows);
    }

    private function authorizeAccess(Skrining $skrining): void
    {
        $userPasienId = optional(Auth::user()->pasien)->id;
        abort_unless($skrining->pasien_id === $userPasienId, 403);
    }
}