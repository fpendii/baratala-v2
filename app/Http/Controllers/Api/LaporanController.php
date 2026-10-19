<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil bulan & tahun dari request, default-nya bulan & tahun sekarang
        $userId = $request->query('user_id');
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        $data = DB::table('laporan_lapangan')->where('id_user', $userId)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // 1. LOG AWAL: Cek data apa saja yang masuk dari Flutter
        Log::info('--- PROSES SIMPAN LAPORAN DIMULAI ---');
        Log::info('Data Request:', $request->all());

        // Cek apakah ada file foto yang terdeteksi
        if ($request->hasFile('foto')) {
            Log::info('File Foto Terdeteksi: ' . $request->file('foto')->getClientOriginalName());
            Log::info('Ukuran Foto: ' . ($request->file('foto')->getSize() / 1024) . ' KB');
        } else {
            Log::warning('File Foto TIDAK Terdeteksi!');
        }

        try {
            // 2. Validasi
            $request->validate([
                'nama_kegiatan' => 'required|string',
                'latitude'      => 'required',
                'longitude'     => 'required',
                'alamat'        => 'required',
                'foto'          => 'required|image|mimes:jpeg,png,jpg|max:5120', // Saya naikkan ke 5MB agar aman
            ]);

            Log::info('Validasi Berhasil.');

            // 3. Simpan foto
            $path = $request->file('foto')->store('laporan', 'public');
            
            // Ambil nama filenya saja untuk disimpan ke DB
            $filename = basename($path);
            Log::info('Foto berhasil disimpan di: ' . $path);

            // 4. Masukkan ke database
            DB::table('laporan_lapangan')->insert([
                'id_user'       => $request->user_id,
                'nama_kegiatan' => $request->nama_kegiatan,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'alamat_lokasi' => $request->alamat,
                'foto'          => $filename, // Simpan nama filenya saja
                'created_at'    => Carbon::now('Asia/Makassar'),
                'updated_at'    => Carbon::now('Asia/Makassar'),
            ]);

            // Setelah data berhasil masuk DB
            $this->sendWhatsAppNotification([
                'user_id'        => $request->user_id,
                'nama_kegiatan'  => $request->nama_kegiatan,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'alamat_lokasi'  => $request->alamat,
            ]);


            Log::info('Data Berhasil Masuk Database.');
            Log::info('--- PROSES SELESAI ---');

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan lapangan berhasil tersimpan!'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $v) {
            // Log jika validasi gagal (misal: format foto salah)
            Log::error('Validasi Gagal:', $v->errors());
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi Gagal',
                'errors' => $v->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log jika terjadi error sistem atau database
            Log::error('Error Sistem Store Laporan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendWhatsAppNotification($data)
    {
        $appUrl = env('APP_URL');

        // Ambil nama user dari database
        $user = DB::table('pengguna')->where('id', $data['user_id'])->first();
        $namaUser = $user->nama ?? 'User';

        $message =
            "📢 *LAPORAN LAPANGAN BARU*\n" .
            "━━━━━━━━━━━━━━━━━━\n\n" .
            "👤 *Di Upload Oleh:* {$namaUser}\n" .
            "📝 *Kegiatan:* {$data['nama_kegiatan']}\n" .
            "📍 *Alamat:* {$data['alamat_lokasi']}\n" .
            "🗺️ *Maps:* https://maps.google.com/?q={$data['latitude']},{$data['longitude']}\n" .
            "📅 *Waktu:* " . now()->format('d/m/Y H:i') . " WIB\n\n" .
            "Silakan cek detail laporan pada sistem.\n" .
            "🔗 {$appUrl}\n\n" .
            "_Notifikasi otomatis dari Sistem Laporan Lapangan_\n";

        try {
            $groupId = env('WA_GROUP_ID');
            \App\Helpers\WhatsAppHelper::send($groupId, $message);
        } catch (\Exception $e) {
            Log::error('Error WA Notification: ' . $e->getMessage());
        }
    }

}
