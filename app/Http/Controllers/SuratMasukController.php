<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\DisposisiSurat;
use Illuminate\Support\Facades\Log;

class SuratMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = SuratMasuk::orderBy('created_at', 'desc');

    // 🔍 Filter Judul
    if ($request->filled('judul')) {
        $query->where('judul', 'like', '%' . $request->judul . '%');
    }

    // 🔍 Filter Pengirim
    if ($request->filled('pengirim')) {
        $query->where('pengirim', 'like', '%' . $request->pengirim . '%');
    }

    // ⚡ Filter Prioritas
    if ($request->filled('prioritas')) {
        $query->where('prioritas', strtolower($request->prioritas));
    }

    // 📅 Filter Tanggal Mulai
    if ($request->filled('tanggal_mulai')) {
        $query->whereDate('tanggal_terima', '>=', $request->tanggal_mulai);
    }

    // 📅 Filter Tanggal Selesai
    if ($request->filled('tanggal_selesai')) {
        $query->whereDate('tanggal_terima', '<=', $request->tanggal_selesai);
    }

    $suratMasuk = $query
        ->paginate(10)
        ->appends($request->query());

    $notifDisposisi = DisposisiSurat::where('user_tujuan_id', auth()->id())
        ->where('status_baca', 'belum')
        ->count();

    return view('surat-masuk.index', compact('suratMasuk', 'notifDisposisi'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('surat-masuk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:100|unique:surat_masuk,nomor_surat',
            'tanggal_terima' => 'required|date',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string',
        ]);

        // ===== LAMPIRAN =====
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            $storageFolder = 'surat_masuk_lampiran';
            $dok_surat = "{$storageFolder}/{$filename}";

            Storage::disk('public')->put(
                $dok_surat,
                file_get_contents($file)
            );

            // Simpan filename saja
            $validatedData['lampiran'] = $filename;
        }

        $validatedData['id_pengguna'] = Auth::id();

        $suratMasuk = SuratMasuk::create($validatedData);

        // 🔔 WA Notification
        $this->sendWhatsAppNotification($suratMasuk);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat Masuk berhasil ditambahkan dan notifikasi dikirim.');
    }


    /**
     * Kirim notifikasi WA ke semua pengguna.
     */
    private function sendWhatsAppNotification($suratMasuk)
    {
        // === 1. Logika Salam Otomatis ===
        $hour = now()->format('H');
        if ($hour >= 5 && $hour < 12) {
            $salam = 'Selamat Pagi';
        } elseif ($hour >= 12 && $hour < 17) {
            $salam = 'Selamat Siang';
        } elseif ($hour >= 17 && $hour < 20) {
            $salam = 'Selamat Sore';
        } else {
            $salam = 'Selamat Malam';
        }

        // === 2. Icon Prioritas ===
        $iconPrioritas = match ($suratMasuk->prioritas) {
            'tinggi' => '🔴',
            'sedang' => '🟡',
            default  => '🟢',
        };

        // === 3. URL Aplikasi ===
        $appUrl   = env('APP_URL'); 
        $suratUrl = $appUrl . 'surat-masuk' ; 

        // === 4. Konten Pesan ===
        $baseMessage =
            "📩 *SURAT MASUK BARU*\n" .
            "━━━━━━━━━━━━━━━━━━\n\n" .
            "📝 *Judul:* {$suratMasuk->judul}\n" .
            "👤 *Pengirim:* {$suratMasuk->pengirim}\n" .
            "📅 *Tanggal Terima:* {$suratMasuk->tanggal_terima}\n" .
            "⚠️ *Prioritas:* {$iconPrioritas} " . ucfirst($suratMasuk->prioritas) . "\n\n" .
            "Silakan cek detail surat melalui sistem Baratala.\n" .
            "🔗 {$suratUrl}\n\n" .
            "_Notifikasi otomatis dari Sistem Baratala_";

        // === 5. Kirim ke Direktur (Personal) ===
        $direktur = \App\Models\Pengguna::where('role', 'direktur')->first();
        if ($direktur && $direktur->no_hp) {
            $personalMessage = "👋 *{$salam}, {$direktur->nama}*\n\n" . $baseMessage;
            \App\Helpers\WhatsAppHelper::send($direktur->no_hp, $personalMessage);
        } else {
            Log::warning('Notifikasi Personal Gagal: Nomor WhatsApp Direktur tidak ditemukan');
        }

        // === 6. Kirim ke Grup WhatsApp ===
        $groupId = env('WA_GROUP_ID');
        if (!empty($groupId)) {
            \App\Helpers\WhatsAppHelper::send($groupId, $baseMessage);
        } else {
            Log::warning('Notifikasi Grup Gagal: ID Grup belum diatur');
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(SuratMasuk $suratMasuk)
    {
        return view('surat-masuk.show', compact('suratMasuk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $suratMasuk = SuratMasuk::findOrFail($id);
        return view('surat-masuk.edit', compact('suratMasuk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $suratMasuk = SuratMasuk::findOrFail($id);
        // 1. Validasi data
        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:100|unique:surat_masuk,nomor_surat,' . $suratMasuk->id,
            'tanggal_terima' => 'required|date',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // File opsional
            'keterangan' => 'nullable|string',
        ]);

        // 2. Proses update lampiran
        if ($request->hasFile('lampiran')) {
            // Hapus lampiran lama jika ada
            if ($suratMasuk->lampiran) {
                Storage::disk('public')->delete($suratMasuk->lampiran);
            }

            // Simpan file baru
            $filePath = $request->file('lampiran')->store('surat_masuk_lampiran', 'public');
            $validatedData['lampiran'] = $filePath;
        }
        // Jika tidak ada file baru dan Anda ingin memberikan opsi hapus file lama,
        // Anda mungkin perlu menambahkan checkbox di form edit (diasumsikan tidak ada)

        // 3. Update database
        $suratMasuk->update($validatedData);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat Masuk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $suratMasuk = SuratMasuk::findOrFail($id);

        // Hapus lampiran fisik (jika ada) sebelum menghapus record database
        if ($suratMasuk->lampiran) {
            Storage::disk('public')->delete($suratMasuk->lampiran);
        }

        $suratMasuk->delete();

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat Masuk berhasil dihapus.');
    }
}
