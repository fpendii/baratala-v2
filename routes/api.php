<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/display-laporan/{filename}', function ($filename) {
    // Jalur file di storage/app/public/laporan/
    $path = 'laporan/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        // Jika tidak ada, coba cek log untuk debug
        \Log::error("File tidak ditemukan di: " . $path);
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    return Response::make($file, 200)->header("Content-Type", $type);
});


Route::post('/laporan-simpan', [LaporanController::class, 'store']);
Route::get('/laporan-riwayat', [LaporanController::class, 'index']);

Route::get('/tes-api', function () {
    return response()->json([
        'status'  => 'OK',
        'pesan'   => 'Server Laravel Terhubung!',
        'ip_anda' => request()->ip(),
        'waktu'   => now()->format('H:i:s d-m-Y')
    ]);
});
