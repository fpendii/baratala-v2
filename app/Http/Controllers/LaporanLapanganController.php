<?php

namespace App\Http\Controllers;

use App\Models\LaporanLapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanLapanganController extends Controller
{
    public function index(Request $request)
    {
        $query = LaporanLapangan::join(
            'pengguna',
            'laporan_lapangan.id_user',
            '=',
            'pengguna.id'
        );

        // 🔍 Filter berdasarkan user upload
        if ($request->filled('user_id')) {
            $query->where('laporan_lapangan.id_user', $request->user_id);
        }

        // 🔍 Filter pencarian kegiatan / alamat (opsional, sekalian)
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('laporan_lapangan.nama_kegiatan', 'like', '%' . $request->cari . '%')
                    ->orWhere('laporan_lapangan.alamat_lokasi', 'like', '%' . $request->cari . '%');
            });
        }

        // 🔍 Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('laporan_lapangan.created_at', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('laporan_lapangan.created_at', '<=', $request->tanggal_selesai);
        }

        $dataLaporan = $query
            ->select(
                'laporan_lapangan.*',
                'pengguna.nama'
            )
            ->orderBy('laporan_lapangan.created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return view('laporan_lapangan.index', compact('dataLaporan'));
    }


    // app/Http/Controllers/LaporanLapanganController.php

    public function edit($id)
    {
        $laporan = LaporanLapangan::findOrFail($id);

        return view('laporan_lapangan.edit', compact('laporan'));
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanLapangan::findOrFail($id);

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude'      => 'required',
            'longitude'     => 'required',
            'alamat_lokasi' => 'required',
        ]);

        $data = $request->only(['nama_kegiatan', 'latitude', 'longitude', 'alamat_lokasi']);

        // Logika jika user mengganti foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($laporan->foto && file_exists(storage_path('app/public/' . $laporan->foto))) {
                Storage::delete('public/' . $laporan->foto);
            }

            $path = $request->file('foto')->store('laporan', 'public');
            $data['foto'] = $path;
        }

        $laporan->update($data);

        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil diperbarui!');
    }

    

    public function rekap(Request $request)
    {
        $request->validate([
            'tgl_mulai'   => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'user_id'     => 'nullable',
        ]);

        $query = LaporanLapangan::join(
            'pengguna',
            'laporan_lapangan.id_user',
            '=',
            'pengguna.id'
        );

        if ($request->filled('user_id')) {
            $query->where('laporan_lapangan.id_user', $request->user_id);
        }

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('laporan_lapangan.created_at', '>=', $request->tgl_mulai);
        }

        if ($request->filled('tgl_selesai')) {
            $query->whereDate('laporan_lapangan.created_at', '<=', $request->tgl_selesai);
        }

        $data = $query->select(
            'laporan_lapangan.*',
            'pengguna.nama'
        )->orderBy('laporan_lapangan.created_at', 'desc')->get();

        $pdf = Pdf::loadView('laporan_lapangan.rekap_pdf', [
            'data' => $data,
            'tgl_mulai' => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
        ]);

        return $pdf->download(
            'Rekap_Laporan_' . $request->tgl_mulai . '_sd_' . $request->tgl_selesai . '.pdf'
        );
    }
}
