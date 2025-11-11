<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DINKES – Edit Akun</title>
  @vite(['resources/css/app.css','resources/js/app.js','resources/js/dinkes/sidebar-toggle.js'])
</head>
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
<div class="ml-0 md:ml-[260px] p-4 sm:p-6 lg:p-8 max-w-4xl sm:max-w-5xl mx-auto">
  <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3">
    <h1 class="text-xl sm:text-2xl font-bold">Edit Akun ({{ ucfirst($tab) }})</h1>
    <a href="{{ route('dinkes.data-master',['tab'=>$tab]) }}"
       class="px-3 sm:px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-xs sm:text-sm">← Kembali</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-3 sm:p-4 text-xs sm:text-sm text-red-700">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-[#FFF0F5] rounded-2xl p-4 sm:p-6 lg:p-8">
    <form method="POST" action="{{ route('dinkes.data-master.update',['user'=>$data->id,'tab'=>$tab]) }}"
          class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 text-sm">
      @csrf @method('PUT')

      {{-- common fields --}}
      <div>
        <label>Nama Lengkap</label>
        <input name="name" value="{{ old('name',$data->name) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
      </div>
      <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email',$data->email) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
      </div>
      <div>
        <label>No Telepon</label>
        <input name="phone" type="number" value="{{ old('phone',$data->phone) }}" class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
      </div>

      @if($tab==='rs')
        <div>
          <label>Nama Rumah Sakit</label>
          <input name="nama" value="{{ old('nama',$data->nama) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div>
          <label>Kecamatan</label>
          <input name="kecamatan" value="{{ old('kecamatan',$data->kecamatan) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div>
          <label>Kelurahan</label>
          <input name="kelurahan" value="{{ old('kelurahan',$data->kelurahan) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div class="md:col-span-2">
          <label>Alamat/Lokasi</label>
          <textarea name="lokasi" rows="3" class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi',$data->lokasi) }}</textarea>
        </div>

      @elseif($tab==='puskesmas')
        <div>
          <label>Nama Puskesmas</label>
          <input name="nama" value="{{ old('nama',$data->nama ?? $data->nama_puskesmas) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div>
          <label>Kecamatan</label>
          <input name="kecamatan" value="{{ old('kecamatan',$data->kecamatan) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div class="md:col-span-2">
          <label>Alamat/Lokasi</label>
          <textarea name="lokasi" rows="3" class="w-full border border-pink-400 rounded-lg px-4 py-2 mt-1">{{ old('lokasi',$data->lokasi) }}</textarea>
        </div>
        <div class="md:col-span-2">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_mandiri" value="1" class="rounded"
                   {{ old('is_mandiri', !empty($data->is_mandiri)) ? 'checked' : '' }}>
            Mandiri
          </label>
        </div>

      @else
        <div>
          <label>No Izin Praktek</label>
          <input name="nomor_izin_praktek" type="number" value="{{ old('nomor_izin_praktek',$data->nomor_izin_praktek) }}" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
        <div>
          <label>Puskesmas</label>
          <select name="puskesmas_id" required class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
            <option value="">-- Pilih --</option>
            @foreach($puskesmasList as $p)
              <option value="{{ $p->id }}" {{ old('puskesmas_id',$data->puskesmas_id)==$p->id ? 'selected' : '' }}>
                {{ $p->nama_puskesmas }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2">
          <label>Alamat</label>
          <input name="address" value="{{ old('address',$data->address) }}" class="w-full border border-pink-400 rounded-full px-4 py-2 mt-1">
        </div>
      @endif

      <div class="md:col-span-2">
        <button class="w-full bg-[#B9257F] text-white rounded-full py-3 font-semibold">SIMPAN PERUBAHAN</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
