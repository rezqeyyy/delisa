<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PasienNifasBidan;
use App\Models\Pasien;
use App\Models\User;

class PasienNifasController extends Controller
{
    public function index()
    {
        $bidan = Auth::user()->bidan;
        if (!$bidan) {
            abort(403, 'Anda tidak memiliki akses sebagai Bidan.');
        }

        $puskesmasId = $bidan->puskesmas_id;

        $pasienNifas = DB::table('pasien_nifas_bidan')
            ->join('pasiens', 'pasien_nifas_bidan.pasien_id', '=', 'pasiens.id')
            ->join('users', 'pasiens.user_id', '=', 'users.id')
            ->select(
                'pasien_nifas_bidan.id',
                'pasien_nifas_bidan.pasien_id',
                'pasien_nifas_bidan.tanggal_mulai_nifas as tanggal',
                'pasien_nifas_bidan.created_at',
                'pasiens.nik',
                'users.name as nama_pasien',
                'users.phone as telp',
                'pasiens.PKecamatan as alamat',
                'pasiens.PWilayah as kelurahan'
            )
            ->where('pasien_nifas_bidan.bidan_id', $puskesmasId)
            ->orderByDesc('pasien_nifas_bidan.tanggal_mulai_nifas')
            ->orderByDesc('pasien_nifas_bidan.created_at')
            ->paginate(10);

        $ids = $pasienNifas->getCollection()->pluck('id')->all();
        $kfDone = DB::table('kf')
            ->selectRaw('id_nifas, MAX(kunjungan_nifas_ke)::int as max_ke')
            ->whereIn('id_nifas', $ids)
            ->groupBy('id_nifas')
            ->get()
            ->keyBy('id_nifas');
        $dueDays = [1=>3,2=>7,3=>14,4=>42];
        $today = Carbon::today();
        $pasienNifas->getCollection()->transform(function ($row) use ($kfDone, $dueDays, $today) {
            $maxKe = optional($kfDone->get($row->id))->max_ke ?? 0;
            $nextKe = min(4, $maxKe + 1);
            $days = $row->tanggal ? Carbon::parse($row->tanggal)->diffInDays($today) : 0;
            $due = $dueDays[$nextKe] ?? 42;
            if ($row->tanggal === null) { $label = 'Aman'; $cls = 'bg-[#2EDB58] text-white'; }
            elseif ($days > $due) { $label = 'Telat'; $cls = 'bg-[#FF3B30] text-white'; }
            elseif ($days >= max(0, $due - 1)) { $label = 'Mepet'; $cls = 'bg-[#FFC400] text-[#1D1D1D]'; }
            else { $label = 'Aman'; $cls = 'bg-[#2EDB58] text-white'; }
            $row->peringat_label = $label;
            $row->badge_class = $cls;
            $row->next_ke = $nextKe;
            return $row;
        });

        $totalPasienNifas = DB::table('pasien_nifas_bidan')
            ->where('bidan_id', $puskesmasId)
            ->count();

        $sudahKFI = 0;
        $belumKFI = $totalPasienNifas - $sudahKFI;

        return view('bidan.pasien-nifas.index', compact(
            'pasienNifas',
            'totalPasienNifas',
            'sudahKFI',
            'belumKFI'
        ));
    }

    public function create()
    {
        return view('bidan.pasien-nifas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'nik'         => 'required|digits:16',
            'no_telepon'  => 'required|string|max:20',
            'provinsi'    => 'required|string|max:100',
            'kota'        => 'required|string|max:100',
            'kecamatan'   => 'required|string|max:100',
            'kelurahan'   => 'required|string|max:100',
            'domisili'    => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $existingPasien = Pasien::with('user')->where('nik', $validated['nik'])->first();
            if ($existingPasien) {
                if ($existingPasien->user) {
                    $existingPasien->user->update(['phone' => $validated['no_telepon']]);
                } else {
                    User::where('id', $existingPasien->user_id)->update(['phone' => $validated['no_telepon']]);
                }

                $existingPasien->update([
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);

                $pasien = $existingPasien;
            } else {
                $role = DB::table('roles')->where('nama_role', 'pasien')->first();
                if (!$role) {
                    throw new \Exception('Role "pasien" tidak ditemukan');
                }

                $baseEmail = $validated['nik'] . '@pasien.delisa.id';
                $email = User::where('email', $baseEmail)->exists()
                    ? ($validated['nik'] . '.' . time() . '@pasien.delisa.id')
                    : $baseEmail;

                $user = User::create([
                    'name'     => $validated['nama_pasien'],
                    'email'    => $email,
                    'password' => bcrypt('password'),
                    'role_id'  => $role->id,
                    'phone'    => $validated['no_telepon'],
                ]);

                $pasien = Pasien::create([
                    'user_id'    => $user->id,
                    'nik'        => $validated['nik'],
                    'PProvinsi'  => $validated['provinsi'],
                    'PKabupaten' => $validated['kota'],
                    'PKecamatan' => $validated['kecamatan'],
                    'PWilayah'   => $validated['kelurahan'],
                ]);
            }

            $bidan = Auth::user()->bidan;
            if (!$bidan) {
                throw new \RuntimeException('Akses Bidan tidak valid');
            }
            $bidanId = $bidan->puskesmas_id;

            $existingNifas = PasienNifasBidan::where('pasien_id', $pasien->id)
                ->where('bidan_id', $bidanId)
                ->first();

            if ($existingNifas) {
                DB::commit();
                return redirect()->route('bidan.pasien-nifas')
                    ->with('info', 'Pasien sudah terdaftar dalam daftar nifas.');
            }

            PasienNifasBidan::create([
                'bidan_id'             => $bidanId,
                'pasien_id'            => $pasien->id,
                'tanggal_mulai_nifas'  => now(),
            ]);

            DB::commit();
            return redirect()->route('bidan.pasien-nifas')
                ->with('success', 'Data pasien nifas berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bidan Store Pasien Nifas: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}