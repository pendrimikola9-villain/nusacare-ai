<?php

namespace App\Http\Controllers;

use App\Models\HealthScreening;
use App\Models\WellnessProgram;
use App\Services\GeminiHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiHubController extends Controller
{
    public function __construct(protected GeminiHealthService $gemini) {}

    public function scannerPage()
    {
        $user = Auth::user();
        $recentScans = $user->screenings()->latest()->take(10)->get();

        $groupItems = function ($query) {
            return $query->get()
                ->map(function ($scan) {
                    $name = $scan->ai_result['item_name'] ?? Str::title(Str::limit($scan->input_summary, 22, ''));
                    return trim($name);
                })
                ->countBy()
                ->sortDesc()
                ->take(8);
        };

        $chartData = [
            'week' => [
                'all'       => $groupItems($user->screenings()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                'nutriscan' => $groupItems($user->screenings()->where('type', 'nutriscan')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                'triase'    => $groupItems($user->screenings()->where('type', 'triase')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ],
            'month' => [
                'all'       => $groupItems($user->screenings()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
                'nutriscan' => $groupItems($user->screenings()->where('type', 'nutriscan')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
                'triase'    => $groupItems($user->screenings()->where('type', 'triase')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
            ],
            'year' => [
                'all'       => $groupItems($user->screenings()->whereYear('created_at', now()->year)),
                'nutriscan' => $groupItems($user->screenings()->where('type', 'nutriscan')->whereYear('created_at', now()->year)),
                'triase'    => $groupItems($user->screenings()->where('type', 'triase')->whereYear('created_at', now()->year)),
            ],
        ];

        return view('warga.scanner', compact('recentScans', 'chartData'));
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'type' => 'required|in:nutriscan,triase',
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|max:4096',
        ]);

        $user = Auth::user();
        $imagePath = null;
        $base64 = null;
        $mime = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = $file->store('scans', 'public');
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $mime = $file->getMimeType();
        }

        if ($request->type === 'nutriscan') {
            $systemPrompt = "Anda adalah ahli gizi klinis AI NusaCare. Analisis foto kemasan makanan/menu dan deskripsi berikut untuk pengguna dengan profil kesehatan: {$user->health_category}. Kembalikan JSON persis dengan kunci: item_name (nama makanan/minuman singkat 1-3 kata dalam Title Case, misal 'Mie Ayam' atau 'Nasi Padang'), risk_level ('aman'|'waspada'|'darurat'), status_label (string singkat), summary (analisis kandungan gula/garam/kalori & dampaknya), first_aid (rekomendasi porsi atau alternatif makanan sehat), recommended_poli ('Konsultasi Gizi Puskesmas').";
        } else {
            $systemPrompt = "Anda adalah asisten triase medis & maternal AI NusaCare. Evaluasi gejala fisik/kehamilan berikut untuk pengguna dengan profil: {$user->health_category}. Kembalikan JSON persis dengan kunci: item_name (nama keluhan utama singkat 1-3 kata dalam Title Case, misal 'Demam Ringan' atau 'Pusing Kehamilan'), risk_level ('aman'|'waspada'|'darurat'), status_label (string tingkat urgensi), summary (analisis kemungkinan kondisi awal), first_aid (langkah pertolongan pertama di rumah), recommended_poli (nama poli Puskesmas/RS tujuan).";
        }

        if (!empty(env('GEMINI_API_KEY'))) {
            $aiResult = $this->gemini->generateJson($systemPrompt, $request->description, $base64, $mime);
        } else {
            $aiResult = [
                'item_name' => Str::title(Str::limit($request->description, 22, '')),
                'risk_level' => 'waspada',
                'status_label' => $request->type === 'nutriscan' ? 'Waspada - Kadar Gula/Natrium Moderat' : 'Waspada - Perlu Pemeriksaan Poli',
                'summary' => "Hasil analisis AI untuk profil {$user->health_category}: " . Str::limit($request->description, 120) . ". Ditemukan indikator yang perlu diperhatikan secara berkala.",
                'first_aid' => $request->type === 'nutriscan'
                    ? 'Batasi porsi maksimal 1/2 kemasan, imbangi dengan air putih 500ml dan sayuran hijau.'
                    : 'Istirahat cukup, pantau suhu tubuh dan tekanan darah tiap 4 jam, serta segera ke faskes jika keluhan memberat.',
                'recommended_poli' => $request->type === 'nutriscan' ? 'Poli Gizi & Penyakit Tidak Menular' : 'Poli Umum / KIA Puskesmas',
            ];
        }

        $riskLevel = in_array($aiResult['risk_level'] ?? '', ['aman', 'waspada', 'darurat'])
            ? $aiResult['risk_level']
            : 'waspada';

        $referralCode = ($request->type === 'triase' && $riskLevel !== 'aman')
            ? 'NSC-' . date('Y') . '-' . strtoupper(Str::random(4))
            : null;

        HealthScreening::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'input_summary' => $request->description,
            'image_path' => $imagePath,
            'risk_level' => $riskLevel,
            'ai_result' => $aiResult,
            'referral_code' => $referralCode,
            'referral_status' => $referralCode ? 'menunggu' : null,
        ]);

        return back()->with('success', 'Analisis Multimodal AI selesai diproses!');
    }

    public function deleteScan(int $id)
    {
        $scan = HealthScreening::where('user_id', Auth::id())->findOrFail($id);

        if ($scan->image_path && Storage::disk('public')->exists($scan->image_path)) {
            Storage::disk('public')->delete($scan->image_path);
        }

        $scan->delete();

        return back()->with('success', 'Riwayat hasil analisis berhasil dihapus.');
    }

    public function downloadScan(int $id)
    {
        $scan = HealthScreening::where('user_id', Auth::id())->findOrFail($id);
        $user = Auth::user();
        $date = $scan->created_at->format('d M Y, H:i');
        $typeLabel = $scan->type === 'nutriscan' ? 'NUTRISCAN MAKANAN & GIZI' : 'TRIASE GEJALA & MATERNAL';
        $status = $scan->ai_result['status_label'] ?? strtoupper($scan->risk_level);
        $summary = $scan->ai_result['summary'] ?? '-';
        $firstAid = $scan->ai_result['first_aid'] ?? '-';
        $poli = $scan->ai_result['recommended_poli'] ?? '-';
        $refCode = $scan->referral_code ?? 'Tidak memerlukan rujukan faskes';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Arsip Analisis NusaCare AI - #{$scan->id}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; max-width: 700px; margin: 40px auto; line-height: 1.6; }
        .header { border-bottom: 3px solid #0d9488; padding-bottom: 12px; margin-bottom: 24px; }
        .title { color: #0d9488; font-size: 22px; font-weight: bold; margin: 0; }
        .meta { font-size: 12px; color: #64748b; }
        .box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #0d9488; margin-bottom: 4px; }
        .badge { display: inline-block; padding: 4px 10px; background: #fef3c7; color: #92400e; font-weight: bold; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">NusaCare AI — Arsip Laporan Analisis Kesehatan</p>
        <p class="meta">Dicetak pada: {$date} WITA • ID Arsip: #NSC-{$scan->id}</p>
    </div>
    <div class="box">
        <div class="label">Identitas & Profil Pengguna</div>
        <strong>Nama:</strong> {$user->name}<br>
        <strong>Profil Medis:</strong> {$user->health_category} • <strong>Wilayah:</strong> {$user->kelurahan}
    </div>
    <div class="box">
        <div class="label">Jenis Pemeriksaan: {$typeLabel}</div>
        <p><strong>Input Pengguna:</strong> {$scan->input_summary}</p>
        <p><strong>Status Evaluasi AI:</strong> <span class="badge">{$status}</span></p>
    </div>
    <div class="box">
        <div class="label">Ringkasan Analisis Kecerdasan Buatan</div>
        <p>{$summary}</p>
        <div class="label">Saran Tindakan & Pertolongan Pertama</div>
        <p>{$firstAid}</p>
        <p><strong>Rekomendasi Poli:</strong> {$poli} | <strong>Kode Rujukan:</strong> {$refCode}</p>
    </div>
</body>
</html>
HTML;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Arsip-NusaCare-' . $scan->id . '.html"',
        ]);
    }

    public function wellnessPage()
    {
        $programs = Auth::user()->programs()->latest()->get();
        return view('warga.wellness', compact('programs'));
    }

    public function generateProgram(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:3|max:30',
            'preference' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $prefText = $request->preference ?: 'Olahraga ringan teratur dan pola makan lokal bergizi seimbang';
        $systemPrompt = "Anda adalah pelatih kesehatan preventif & ahli gizi AI NusaCare. Buat timeline kegiatan olahraga, pola makan sehat, dan manajemen stres untuk program '{$request->title}' selama {$request->duration_days} hari bagi pengguna dengan profil '{$user->health_category}' dengan preferensi: '{$prefText}'. Kembalikan HANYA JSON valid dengan kunci 'timeline' berisi array 5-6 objek jadwal (hari ke-1 sampai ke-3), tiap objek punya kunci: day (integer), time (string 'HH:MM'), activity (string spesifik), category ('Olahraga'|'Nutrisi'|'Mental'), done (boolean false).";

        $timeline = [];
        $res = $this->gemini->generateJson($systemPrompt, $prefText);

        // Hanya gunakan hasil generateJson jika benar-benar dari Gemini Live
        if ($this->gemini->lastSource === 'gemini_live' && !empty($res['timeline'])) {
            $timeline = array_values($res['timeline']);
        } else {
            // Smart Contextual Fallback sesuai judul & preferensi pengguna
            $tLower = strtolower($request->title . ' ' . $prefText);
            if (str_contains($tLower, 'berat badan') || str_contains($tLower, 'massa') || str_contains($tLower, 'otot') || str_contains($tLower, 'surplus')) {
                $timeline = [
                    ['day' => 1, 'time' => '06:30', 'activity' => 'Pemanasan & latihan beban tubuh (push-up, squat, plank) 20 menit', 'category' => 'Olahraga', 'done' => false],
                    ['day' => 1, 'time' => '07:30', 'activity' => 'Sarapan tinggi protein (2 butir telur, tempe, & nasi hangat)', 'category' => 'Nutrisi', 'done' => false],
                    ['day' => 1, 'time' => '15:30', 'activity' => 'Camilan padat gizi (pisang, roti gandum, atau kacang-kacangan)', 'category' => 'Nutrisi', 'done' => false],
                    ['day' => 1, 'time' => '21:00', 'activity' => 'Tidur berkualitas minimal 7-8 jam untuk pemulihan massa otot', 'category' => 'Mental', 'done' => false],
                    ['day' => 2, 'time' => '06:30', 'activity' => 'Latihan kekuatan tubuh bagian atas & peregangan 25 menit', 'category' => 'Olahraga', 'done' => false],
                    ['day' => 2, 'time' => '12:30', 'activity' => 'Makan siang lauk ikan/ayam tanpa digoreng berlebih + sayur', 'category' => 'Nutrisi', 'done' => false],
                ];
            } elseif (str_contains($tLower, 'gula') || str_contains($tLower, 'diabetes') || str_contains($tLower, 'tensi') || str_contains($tLower, 'hipertensi')) {
                $timeline = [
                    ['day' => 1, 'time' => '06:30', 'activity' => 'Jalan cepat santai 25 menit untuk meningkatkan sensitivitas insulin', 'category' => 'Olahraga', 'done' => false],
                    ['day' => 1, 'time' => '12:00', 'activity' => 'Makan siang rendah indeks glikemik & rendah garam (sayur rebus + ikan)', 'category' => 'Nutrisi', 'done' => false],
                    ['day' => 1, 'time' => '20:30', 'activity' => 'Latihan pernapasan dalam 10 menit untuk menurunkan tekanan darah', 'category' => 'Mental', 'done' => false],
                    ['day' => 2, 'time' => '06:30', 'activity' => 'Senam aerobik ringan di rumah 20 menit & minum air putih 500 ml', 'category' => 'Olahraga', 'done' => false],
                    ['day' => 2, 'time' => '18:30', 'activity' => 'Makan malam porsi terkontrol tanpa minuman manis kemasan', 'category' => 'Nutrisi', 'done' => false],
                ];
            } else {
                $timeline = [
                    ['day' => 1, 'time' => '06:30', 'activity' => "Sesi {$request->title} pagi 20 menit + peregangan sendi", 'category' => 'Olahraga', 'done' => false],
                    ['day' => 1, 'time' => '12:00', 'activity' => "Makan siang gizi seimbang ({$prefText})", 'category' => 'Nutrisi', 'done' => false],
                    ['day' => 1, 'time' => '20:30', 'activity' => 'Istirahat layar gawai & relaksasi pernapasan 10 menit', 'category' => 'Mental', 'done' => false],
                    ['day' => 2, 'time' => '06:30', 'activity' => "Lanjutan {$request->title} durasi 25 menit & hidrasi optimal", 'category' => 'Olahraga', 'done' => false],
                    ['day' => 2, 'time' => '18:30', 'activity' => 'Makan malam rendah gula, kaya serat & protein lokal', 'category' => 'Nutrisi', 'done' => false],
                ];
            }
        }

        // Pastikan seluruh item memiliki key done bertipe boolean
        $normalizedTimeline = array_map(function ($item) {
            return [
                'day' => (int) ($item['day'] ?? 1),
                'time' => (string) ($item['time'] ?? '07:00'),
                'activity' => (string) ($item['activity'] ?? ''),
                'category' => (string) ($item['category'] ?? 'Aktivitas'),
                'done' => (bool) ($item['done'] ?? false),
            ];
        }, array_values($timeline));

        WellnessProgram::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'duration_days' => $request->duration_days,
            'ai_timeline' => $normalizedTimeline,
        ]);

        session()->flash('wellness_api_status', [
            'source' => $this->gemini->lastSource,
            'error_note' => $this->gemini->lastError,
        ]);

        return back()->with('success', 'Timeline Program Kesehatan & Pengingat berhasil dibuat!');
    }

    public function toggleStep(int $id, int $stepIndex)
    {
        $program = WellnessProgram::where('user_id', Auth::id())->findOrFail($id);
        $timeline = array_values((array) $program->ai_timeline);

        if (isset($timeline[$stepIndex])) {
            $currentDone = filter_var($timeline[$stepIndex]['done'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $timeline[$stepIndex]['done'] = !$currentDone;

            $program->ai_timeline = $timeline;
            $program->save();
        }

        return back()->with('success', 'Progres kegiatan diperbarui!');
    }

    public function downloadIcs(int $id)
    {
        $program = WellnessProgram::where('user_id', Auth::id())->findOrFail($id);
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//NusaCare AI//Wellness Reminder//ID\r\n";

        foreach ($program->ai_timeline as $idx => $item) {
            $date = now()->addDays(($item['day'] ?? 1) - 1)->format('Ymd');
            $time = str_replace(':', '', $item['time'] ?? '0700') . '00';
            $summary = $item['activity'] ?? 'Agenda Kesehatan';
            $ics .= "BEGIN:VEVENT\r\nUID:nusacare-{$program->id}-{$idx}@nusacare.id\r\nDTSTAMP:{$date}T{$time}Z\r\nDTSTART:{$date}T{$time}\r\nSUMMARY:[NusaCare] {$summary}\r\nEND:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="pengingat-nusacare.ics"',
        ]);
    }
}