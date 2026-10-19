<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanLapangan extends Model
{
    protected $table = 'laporan_lapangan';

    // Agar data bisa diisi lewat $request->all()
    protected $fillable = [
        'id_user', 'nama_kegiatan', 'foto',
        'latitude', 'longitude', 'alamat_lokasi'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_user');
    }
}
