<!DOCTYPE html>
<html>
<head>
    <title>Rekap Arsip Dokumen</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN REKAP ARSIP DOKUMEN</h2>
        <p>Periode: {{ date('d/m/Y', strtotime($start)) }} s/d {{ date('d/m/Y', strtotime($end)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nomor Dokumen</th>
                <th>Judul Dokumen</th>
                <th>Kategori</th>
                <th>Versi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $key => $doc)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $doc->created_at->format('d/m/Y') }}</td>
                <td>{{ $doc->doc_number ?? '-' }}</td>
                <td>{{ $doc->title }}</td>
                <td>{{ $doc->category->name }}</td>
                <td>v{{ $doc->latestVersion->version_number }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>