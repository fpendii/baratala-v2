<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Laporan Lapangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h3>Rekap Laporan Lapangan</h3>
<p>
    Periode: {{ $tgl_mulai }} s/d {{ $tgl_selesai }}
</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Kegiatan</th>
            <th>Alamat</th>
            <th>User</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->nama_kegiatan }}</td>
            <td>{{ $item->alamat_lokasi }}</td>
            <td>{{ $item->nama }}</td>
            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>