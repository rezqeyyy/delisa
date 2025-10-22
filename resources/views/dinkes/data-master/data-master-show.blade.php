<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DINKES – Detail Akun</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  </head>
<body class="bg-[#F5F5F5] font-[Poppins] text-[#000000cc]">
<div class="p-8 max-w-4xl mx-auto">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Detail Akun ({{ ucfirst($tab) }})</h1>
    <a href="{{ route('dinkes.data-master',['tab'=>$tab]) }}"
       class="px-4 py-2 rounded-full bg-white border border-[#D9D9D9] text-sm">← Kembali</a>
  </div>

  <div class="bg-white rounded-2xl shadow p-6 space-y-3">
    <div><span class="text-[#7C7C7C] text-sm">Nama</span><div class="font-medium">{{ $data->name }}</div></div>
    <div><span class="text-[#7C7C7C] text-sm">Email</span><div class="font-medium">{{ $data->email }}</div></div>
    @if($tab==='rs')
      <div><span class="text-[#7C7C7C] text-sm">Nama RS</span><div class="font-medium">{{ $data->nama }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Kecamatan</span><div class="font-medium">{{ $data->kecamatan }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Kelurahan</span><div class="font-medium">{{ $data->kelurahan }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Lokasi</span><div class="font-medium">{{ $data->lokasi }}</div></div>
    @elseif($tab==='puskesmas')
      <div><span class="text-[#7C7C7C] text-sm">Nama Puskesmas</span><div class="font-medium">{{ $data->nama_puskesmas ?? $data->nama }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Kecamatan</span><div class="font-medium">{{ $data->kecamatan }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Mandiri</span><div class="font-medium">{{ !empty($data->is_mandiri)?'Ya':'Tidak' }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Lokasi</span><div class="font-medium">{{ $data->lokasi }}</div></div>
    @else
      <div><span class="text-[#7C7C7C] text-sm">No Izin Praktek</span><div class="font-medium">{{ $data->nomor_izin_praktek }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Puskesmas</span><div class="font-medium">{{ $data->nama_puskesmas ?? '-' }}</div></div>
      <div><span class="text-[#7C7C7C] text-sm">Alamat</span><div class="font-medium">{{ $data->address }}</div></div>
    @endif
  </div>
</div>
</body>
</html>
