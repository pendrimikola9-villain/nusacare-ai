<?php

namespace App\Http\Controllers;

use App\Models\HealthScreening;
use App\Models\HealthTracker;
use App\Models\WellnessProgram;
use App\Services\GeminiHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $trackers = $user->trackers()->orderBy('log_date', 'asc')->take(7)->get();
        $latestTracker = $user->trackers()->latest('log_date')->first();
        $activeProgram = $user->programs()->latest()->first();
        $activeReferral = $user->screenings()->whereNotNull('referral_code')->latest()->first();
        $recentScreenings = $user->screenings()->latest()->take(4)->get();

        return view('warga.dashboard', compact(
            'user',
            'trackers',
            'latestTracker',
            'activeProgram',
            'activeReferral',
            'recentScreenings'
        ));
    }

    public function profil()
    {
        $user = Auth::user();
        $latestTracker = $user->trackers()->latest('log_date')->first();
        $activeReferral = $user->screenings()->whereNotNull('referral_code')->latest()->first();
        $avoidFoods = $user->screenings()
            ->where('type', 'nutriscan')
            ->whereIn('risk_level', ['waspada', 'darurat'])
            ->latest()
            ->take(3)
            ->get();

        return view('warga.profil', compact('user', 'latestTracker', 'activeReferral', 'avoidFoods'));
    }

    public function updateProfil(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'health_category' => ['required', 'string', 'max:100'],
            'kelurahan' => ['required', 'string', 'max:100'],
        ]);

        Auth::user()->update($data);

        return back()->with('success', 'Profil medis & kelurahan domisili berhasil diperbarui! Kartu Sehat dan analisis AI kini menyesuaikan profil terbaru Anda.');
    }

    public function konsultasiPage()
    {
        $user = Auth::user();
        $latestScan = $user->screenings()->latest()->first();
        $chatHistory = session('ai_chat_history', []);

        return view('warga.konsultasi', compact('user', 'latestScan', 'chatHistory'));
    }

    public function konsultasiAi(Request $request, GeminiHealthService $gemini)
    {
        $request->validate([
            'question' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:5120'],
            'audio_base64' => ['nullable', 'string'],
            'audio_mime' => ['nullable', 'string', 'max:50'],
        ]);

        if (!$request->filled('question') && !$request->hasFile('image') && !$request->filled('audio_base64')) {
            return back()->withErrors(['question' => 'Silakan ketik pertanyaan, rekam suara, atau lampirkan foto terlebih dahulu.']);
        }

        $user = Auth::user();
        $latestScan = $user->screenings()->latest()->first();
        $scanContext = $latestScan
            ? ($latestScan->input_summary . ' -> Status: ' . ($latestScan->ai_result['status_label'] ?? $latestScan->risk_level))
            : null;

        $base64Image = null;
        $imageMime = null;
        if ($request->hasFile('image')) {
            $imgFile = $request->file('image');
            $base64Image = base64_encode(file_get_contents($imgFile->getRealPath()));
            $imageMime = $imgFile->getMimeType();
        }

        $base64Audio = $request->input('audio_base64') ?: null;
        $audioMime = null;
        if ($base64Audio) {
            $rawMime = explode(';', $request->input('audio_mime', 'audio/webm'))[0];
            $audioMime = ($rawMime === 'video/webm') ? 'audio/webm' : $rawMime;
        }

        $questionText = $request->input('question', '');
        $answer = $gemini->askFollowUp(
            $questionText,
            $user->health_category,
            $user->kelurahan,
            $scanContext,
            $base64Image,
            $imageMime,
            $base64Audio,
            $audioMime
        );

        $displayQuestion = $questionText ?: 'Pertanyaan melalui rekaman suara / lampiran foto';
        if ($request->hasFile('image')) {
            $displayQuestion .= ' [Foto terlampir]';
        }
        if ($base64Audio) {
            $displayQuestion .= ' [Rekaman suara]';
        }

        $history = session('ai_chat_history', []);
        $history[] = [
            'question' => $displayQuestion,
            'answer' => $answer,
            'time' => now()->format('H:i'),
            'source' => $gemini->lastSource,
            'error_note' => $gemini->lastError,
        ];

        session(['ai_chat_history' => array_slice($history, -8)]);

        return back();
    }

    public function resetKonsultasi()
    {
        session()->forget('ai_chat_history');
        return back()->with('success', 'Riwayat percakapan konsultasi berhasil dibersihkan.');
    }

    public function trackerPage()
    {
        $user = Auth::user();
        $trackers = $user->trackers()->orderBy('log_date', 'asc')->take(14)->get();
        $latest = $trackers->last();
        $activeChallenge = session('ai_tracker_challenge');

        return view('warga.tracker', compact('user', 'trackers', 'latest', 'activeChallenge'));
    }

    public function generateTrackerChallenge(Request $request, GeminiHealthService $gemini)
    {
        $request->validate([
            'focus_goal' => ['required', 'string', 'max:200'],
        ]);

        $user = Auth::user();
        $latest = $user->trackers()->latest('log_date')->first();

        $vitalSummary = $latest
            ? "Berat: " . ($latest->weight ?? '-') . " kg, Tensi: " . ($latest->blood_pressure ?? '-') . " mmHg, Gula Darah: " . ($latest->blood_sugar ?? '-') . " mg/dL, Skor Stres: {$latest->stress_score}/10"
            : null;

        $challenge = $gemini->generateHealthChallenge(
            $request->focus_goal,
            $user->health_category,
            $vitalSummary
        );

        $challenge['focus_goal'] = $request->focus_goal;
        $challenge['source'] = $gemini->lastSource;
        $challenge['error_note'] = $gemini->lastError;

        session(['ai_tracker_challenge' => $challenge]);

        return back()->with('success', 'Tantangan kesehatan personal berhasil dibuat oleh AI Coach!');
    }

    public function storeTracker(Request $request)
    {
        $data = $request->validate([
            'log_date' => ['required', 'date'],
            'weight' => ['nullable', 'numeric'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'blood_sugar' => ['nullable', 'integer'],
            'stress_score' => ['required', 'integer', 'min:1', 'max:10'],
            'mood_note' => ['nullable', 'string', 'max:500'],
        ]);

        HealthTracker::updateOrCreate(
            ['user_id' => Auth::id(), 'log_date' => $data['log_date']],
            $data
        );

        return back()->with('success', 'Catatan Health & Mind Tracker hari ini berhasil disimpan.');
    }

    public function radarHistory()
    {
        $user = Auth::user();
        $screenings = $user->screenings()->latest()->get();

        $radarPoints = [
            ['kelurahan' => 'Sungai Miai', 'lat' => -3.2952, 'lng' => 114.5912, 'cases' => 4, 'disease' => 'ISPA & Demam Ringan', 'level' => 'waspada'],
            ['kelurahan' => 'Alalak Utara', 'lat' => -3.2785, 'lng' => 114.5801, 'cases' => 7, 'disease' => 'Suspek DBD', 'level' => 'darurat'],
            ['kelurahan' => 'Kuin Cerucuk', 'lat' => -3.3091, 'lng' => 114.5734, 'cases' => 2, 'disease' => 'Diare Musiman', 'level' => 'aman'],
            ['kelurahan' => 'Antasan Kecil Timur', 'lat' => -3.3015, 'lng' => 114.6010, 'cases' => 3, 'disease' => 'ISPA Ringan', 'level' => 'waspada'],
        ];

        $faskesDirectory = [
            [
                'name' => 'Puskesmas Sungai Miai',
                'area' => 'Kel. Sungai Miai & Antasan Kecil Timur',
                'phone' => '05113301234',
                'hours' => '08:00 - 14:00 WITA (UGD 24 Jam)',
                'type' => 'Puskesmas Induk',
            ],
            [
                'name' => 'Puskesmas Alalak Tengah',
                'area' => 'Kel. Alalak Utara & Alalak Tengah',
                'phone' => '05113305678',
                'hours' => '08:00 - 14:00 WITA',
                'type' => 'Puskesmas Kecamatan',
            ],
            [
                'name' => 'RSUD Dr. H. Moch. Ansari Saleh',
                'area' => 'Jl. Brigjen H. Hasan Basri, Kayutangi',
                'phone' => '05113300741',
                'hours' => 'IGD & Ambulans 24 Jam',
                'type' => 'RS Rujukan Utama',
            ],
        ];

        $firstAidResult = session('first_aid_result');

        return view('warga.radar', compact('user', 'screenings', 'radarPoints', 'faskesDirectory', 'firstAidResult'));
    }

    public function processFirstAid(Request $request, GeminiHealthService $gemini)
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:600'],
            'image' => ['nullable', 'image', 'max:5120'],
            'audio_base64' => ['nullable', 'string'],
            'audio_mime' => ['nullable', 'string', 'max:50'],
        ]);

        if (!$request->filled('description') && !$request->hasFile('image') && !$request->filled('audio_base64')) {
            return back()->withErrors(['description' => 'Silakan ketik keluhan, rekam suara, atau unggah foto kondisi darurat terlebih dahulu.']);
        }

        $user = Auth::user();
        $base64Image = null;
        $imageMime = null;
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imgFile = $request->file('image');
            $imagePath = $imgFile->store('screenings', 'public');
            $base64Image = base64_encode(file_get_contents($imgFile->getRealPath()));
            $imageMime = $imgFile->getMimeType();
        }

        $base64Audio = $request->input('audio_base64') ?: null;
        $audioMime = null;
        if ($base64Audio) {
            $rawMime = explode(';', $request->input('audio_mime', 'audio/webm'))[0];
            $audioMime = ($rawMime === 'video/webm') ? 'audio/webm' : $rawMime;
        }

        $aiResult = $gemini->analyzeEmergencyFirstAid(
            $user->health_category,
            $user->kelurahan,
            $request->description,
            $base64Image,
            $imageMime,
            $base64Audio,
            $audioMime
        );

        $riskLevel = in_array($aiResult['risk_level'] ?? '', ['aman', 'waspada', 'darurat'])
            ? $aiResult['risk_level']
            : 'waspada';

        $referralCode = null;
        $referralStatus = null;
        if (in_array($riskLevel, ['waspada', 'darurat'])) {
            $nextNumber = HealthScreening::whereNotNull('referral_code')->count() + 1;
            $referralCode = 'NSC-' . date('Y') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $referralStatus = 'menunggu';
        }

        $summaryInput = $request->description
            ?: ($aiResult['detected_complaint'] ?? 'Laporan Pertolongan Pertama (Suara/Foto)');

        HealthScreening::create([
            'user_id' => $user->id,
            'type' => 'triase',
            'input_summary' => '[Darurat P3K] ' . $summaryInput,
            'image_path' => $imagePath,
            'risk_level' => $riskLevel,
            'ai_result' => [
                'status_label' => $aiResult['status_label'] ?? strtoupper($riskLevel),
                'summary' => $aiResult['summary'] ?? '-',
                'first_aid' => implode(' | ', $aiResult['steps'] ?? []),
                'recommended_poli' => $aiResult['recommended_poli'] ?? 'UGD / Poli Umum Puskesmas',
            ],
            'referral_code' => $referralCode,
            'referral_status' => $referralStatus,
        ]);

        $aiResult['referral_code'] = $referralCode;
        $aiResult['source'] = $gemini->lastSource;
        $aiResult['error_note'] = $gemini->lastError;

        return back()
            ->with('first_aid_result', $aiResult)
            ->with('success', $referralCode
                ? "Panduan pertolongan pertama AI siap! Tiket rujukan darurat {$referralCode} otomatis diterbitkan ke Puskesmas."
                : 'Panduan pertolongan pertama AI berhasil diproses.');
    }

    public function medisDashboard()
    {
        $referrals = HealthScreening::with('user')
            ->whereNotNull('referral_code')
            ->orderByRaw("FIELD(risk_level, 'darurat', 'waspada', 'aman')")
            ->latest()
            ->get();

        return view('medis.dashboard', compact('referrals'));
    }

    public function medisDetail(int $id)
    {
        $screening = HealthScreening::with('user')->findOrFail($id);
        $patientTrackers = HealthTracker::where('user_id', $screening->user_id)
            ->orderBy('log_date', 'asc')
            ->take(7)
            ->get();

        return view('medis.detail', compact('screening', 'patientTrackers'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['referral_status' => 'required|in:menunggu,diperiksa,selesai']);
        $screening = HealthScreening::findOrFail($id);
        $screening->update(['referral_status' => $request->referral_status]);

        return back()->with('success', 'Status rujukan pasien berhasil diperbarui.');
    }
}