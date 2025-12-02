<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rujukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-4">Detail Rujukan</h1>
            
            @if(isset($rujukan) && $rujukan)
            <div class="space-y-4">
                <!-- Informasi Dasar -->
                <div>
                    <h2 class="text-lg font-semibold mb-2">Informasi Rujukan</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p><span class="font-medium">ID:</span> {{ $rujukan->id }}</p>
                            <p><span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 rounded {{ $rujukan->done_status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $rujukan->done_status ? 'Selesai' : 'Menunggu' }}
                                </span>
                            </p>
                            <p><span class="font-medium">Tanggal:</span> {{ $rujukan->created_at }}</p>
                        </div>
                        <div>
                            <p><span class="font-medium">Pasien ID:</span> {{ $rujukan->pasien_id }}</p>
                            <p><span class="font-medium">RS ID:</span> {{ $rujukan->rs_id }}</p>
                            <p><span class="font-medium">Skrining ID:</span> {{ $rujukan->skrining_id }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Data Pasien -->
                @if(isset($rujukan->nama_pasien))
                <div>
                    <h2 class="text-lg font-semibold mb-2">Data Pasien</h2>
                    <p><span class="font-medium">Nama:</span> {{ $rujukan->nama_pasien }}</p>
                    <p><span class="font-medium">NIK:</span> {{ $rujukan->nik ?? '-' }}</p>
                    <p><span class="font-medium">Alamat:</span> {{ $rujukan->alamat ?? '-' }}</p>
                    <p><span class="font-medium">Telepon:</span> {{ $rujukan->no_telepon ?? '-' }}</p>
                </div>
                @endif
                
                <!-- Data Rumah Sakit -->
                @if(isset($rujukan->nama_rs))
                <div>
                    <h2 class="text-lg font-semibold mb-2">Rumah Sakit Tujuan</h2>
                    <p><span class="font-medium">Nama:</span> {{ $rujukan->nama_rs }}</p>
                    <p><span class="font-medium">Alamat:</span> {{ $rujukan->alamat_rs ?? '-' }}</p>
                    <p><span class="font-medium">Telepon:</span> {{ $rujukan->telepon_rs ?? '-' }}</p>
                </div>
                @endif
                
                <!-- Catatan -->
                <div>
                    <h2 class="text-lg font-semibold mb-2">Catatan</h2>
                    <div class="bg-gray-100 p-3 rounded">
                        {{ $rujukan->catatan_rujukan ?? 'Tidak ada catatan' }}
                    </div>
                </div>
                
                <!-- Tombol -->
                <div class="pt-4 flex space-x-3">
                    <a href="{{ route('puskesmas.rujukan.index') }}" 
                       class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        ← Kembali
                    </a>
                    
                    @if(!$rujukan->done_status)
                    <button onclick="tandaiSelesai()"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        ✅ Tandai Selesai
                    </button>
                    @endif
                </div>
            </div>
            @else
            <div class="text-red-600">
                <p>Data rujukan tidak ditemukan</p>
            </div>
            @endif
        </div>
    </div>

    @if(isset($rujukan) && !$rujukan->done_status)
    <script>
    function tandaiSelesai() {
        if(confirm('Tandai rujukan ini sebagai selesai?')) {
            fetch('/puskesmas/rujukan/{{ $rujukan->id }}/update-status', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ done_status: true })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    location.reload();
                }
            });
        }
    }
    </script>
    @endif
</body>
</html>