<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\HealthScreening;
use App\Models\WellnessProgram;
use App\Models\HealthTracker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Demo Warga
        $warga = User::create([
            'name' => 'Rina Kartika (Demo Warga)',
            'email' => 'warga@nusacare.id',
            'password' => Hash::make('password123'),
            'role' => 'warga',
            'health_category' => 'Ibu Hamil (Trimester 2)',
            'kelurahan' => 'Sungai Miai',
        ]);

        // 2. Akun Demo Tenaga Medis / Puskesmas
        User::create([
            'name' => 'dr. Ahmad Fauzi (Puskesmas)',
            'email' => 'medis@nusacare.id',
            'password' => Hash::make('password123'),
            'role' => 'medis',
            'health_category' => 'Tenaga Medis',
            'kelurahan' => 'Sungai Miai',
        ]);

        // 3. Data 7 Hari Health & Mind Tracker
        for ($i = 6; $i >= 0; $i--) {
            HealthTracker::create([
                'user_id' => $warga->id,
                'log_date' => now()->subDays($i)->toDateString(),
                'weight' => 56 + (6 - $i) * 0.1,
                'blood_pressure' => $i === 1 ? '130/85' : '118/78',
                'blood_sugar' => 98 + ($i % 3) * 4,
                'stress_score' => $i === 1 ? 6 : 3,
                'mood_note' => $i === 0 ? 'Tidur cukup nyenyak, jalan pagi 20 menit.' : 'Aktivitas rutin harian.',
            ]);
        }

        // 4. Program Kesehatan & Timeline AI
        WellnessProgram::create([
            'user_id' => $warga->id,
            'title' => 'Program Kebugaran & Nutrisi Ibu Hamil 7 Hari',
            'duration_days' => 7,
            'ai_timeline' => [
                ['day' => 1, 'time' => '06:30', 'activity' => 'Jalan kaki santai 20 menit & berjemur pagi', 'category' => 'Olahraga', 'done' => true],
                ['day' => 1, 'time' => '12:00', 'activity' => 'Makan siang tinggi zat besi (bayam, telur rebus, ikan)', 'category' => 'Nutrisi', 'done' => true],
                ['day' => 1, 'time' => '20:30', 'activity' => 'Latihan pernapasan relaksasi 10 menit sebelum tidur', 'category' => 'Mental', 'done' => false],
                ['day' => 2, 'time' => '07:00', 'activity' => 'Senam peregangan ringan trimester 2 (15 menit)', 'category' => 'Olahraga', 'done' => false],
            ],
        ]);

        // 5. Data Skrining & Rujukan Komunitas (Untuk Peta & Portal Medis)
        HealthScreening::create([
            'user_id' => $warga->id,
            'type' => 'triase',
            'input_summary' => 'Pusing ringan sejak pagi dan tekanan darah 130/85 mmHg pada usia kehamilan 22 minggu.',
            'risk_level' => 'waspada',
            'ai_result' => [
                'status_label' => 'Waspada - Perlu Pemantauan Bidan/Dokter',
                'summary' => 'Tekanan darah sedikit meningkat untuk usia kehamilan trimester 2, perlu evaluasi dini risiko preeklamsia ringan.',
                'first_aid' => 'Istirahat berbaring miring ke kiri, kurangi konsumsi garam hari ini, dan perbanyak minum air putih.',
                'recommended_poli' => 'Poli KIA / Kandungan Puskesmas',
            ],
            'referral_code' => 'NSC-2026-001',
            'referral_status' => 'menunggu',
        ]);
    }
}