<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiHealthService
{
    protected string $apiKey;
    protected array $models = [
        'gemini-2.5-flash',
        'gemini-2.5-pro',
    ];

    public string $lastSource = 'gemini_live';
    public ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function generateJson(string $systemInstruction, string $userPrompt, ?string $base64Image = null, ?string $mimeType = null): array
    {
        $parts = [
            ['text' => $systemInstruction . "\n\nData & Permintaan Pengguna:\n" . $userPrompt]
        ];

        if ($base64Image && $mimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Image,
                ]
            ];
        }

        if (empty($this->apiKey)) {
            $this->lastSource = 'smart_fallback';
            $this->lastError = 'GEMINI_API_KEY belum diisi di .env';
        } else {
            foreach ($this->models as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::withoutVerifying()
                        ->timeout(30)
                        ->post($url, [
                            'contents' => [['parts' => $parts]],
                            'generationConfig' => [
                                'response_mime_type' => 'application/json',
                                'temperature' => 0.3,
                            ]
                        ]);

                    if ($response->successful()) {
                        $rawText = $response->json('candidates.0.content.parts.0.text') ?? '{}';
                        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
                        $decoded = json_decode($cleanJson, true);

                        if (isset($decoded[0]) && is_array($decoded[0])) {
                            $decoded = $decoded[0];
                        }

                        if (is_array($decoded) && !empty($decoded)) {
                            $this->lastSource = 'gemini_live';
                            $this->lastError = null;
                            return $decoded;
                        }
                    } else {
                        $this->recordApiFailure($response->status());
                    }
                } catch (\Throwable $e) {
                    $this->lastSource = 'smart_fallback';
                    $this->lastError = 'Koneksi jaringan ke Gemini terputus';
                    continue;
                }
            }
        }

        return [
            'item_name' => Str::title(Str::limit($userPrompt, 22, '')),
            'risk_level' => 'waspada',
            'status_label' => 'Waspada - Perlu Pengaturan Porsi',
            'summary' => 'Analisis nutrisi diproses. Hidangan mengandung karbohidrat olahan dan natrium yang perlu disesuaikan porsinya dengan profil kesehatan Anda.',
            'first_aid' => 'Batasi porsi konsumsi, kurangi tambahan saus/garam berlebih, dan imbangi dengan air putih serta sayuran.',
            'recommended_poli' => 'Poli Gizi Puskesmas',
            'timeline' => [
                ['day' => 1, 'time' => '06:30', 'activity' => 'Pemanasan & ' . $userPrompt . ' ringan 20 menit', 'category' => 'Olahraga', 'done' => false],
                ['day' => 1, 'time' => '12:00', 'activity' => 'Makan siang gizi seimbang (protein tanpa lemak + sayur hijau)', 'category' => 'Nutrisi', 'done' => false],
                ['day' => 1, 'time' => '20:30', 'activity' => 'Istirahat cukup & latihan pernapasan relaksasi 10 menit', 'category' => 'Mental', 'done' => false],
                ['day' => 2, 'time' => '06:30', 'activity' => 'Sesi ' . $userPrompt . ' & peregangan otot 25 menit', 'category' => 'Olahraga', 'done' => false],
                ['day' => 2, 'time' => '18:30', 'activity' => 'Makan malam rendah gula & hidrasi air putih 2 liter/hari', 'category' => 'Nutrisi', 'done' => false],
            ],
        ];
    }

    public function analyzeEmergencyFirstAid(
        string $healthCategory,
        string $kelurahan,
        ?string $description = null,
        ?string $base64Image = null,
        ?string $imageMimeType = null,
        ?string $base64Audio = null,
        ?string $audioMimeType = null
    ): array {
        $systemInstruction = "Kamu adalah Asisten Triase & Pertolongan Pertama Darurat NusaCare AI untuk masyarakat Indonesia. "
            . "Profil pengguna: Kategori Kesehatan = {$healthCategory}, Domisili = Kelurahan {$kelurahan}. "
            . "Analisis keluhan dari gabungan teks, foto kondisi fisik/luka, dan/atau rekaman suara pengguna secara akurat dan spesifik sesuai keluhan yang diucapkan atau difoto. "
            . "Kembalikan HANYA objek JSON valid tanpa teks tambahan dengan struktur persis berikut:\n"
            . "{\n"
            . "  \"detected_complaint\": \"Ringkasan spesifik keluhan yang terdengar dari suara/foto/teks\",\n"
            . "  \"risk_level\": \"aman|waspada|darurat\",\n"
            . "  \"status_label\": \"Label singkat status sesuai tingkat bahaya keluhan\",\n"
            . "  \"summary\": \"Penjelasan klinis spesifik sesuai keluhan pengguna (2-3 kalimat)\",\n"
            . "  \"steps\": [\"Langkah 1 pertolongan pertama spesifik\", \"Langkah 2\", \"Langkah 3\"],\n"
            . "  \"do_not\": \"Hal spesifik yang DILARANG dilakukan pada kondisi ini\",\n"
            . "  \"recommended_poli\": \"Poli tujuan Puskesmas / IGD RS\"\n"
            . "}";

        $promptText = $description
            ? "Keterangan teks pengguna: {$description}"
            : "Pengguna mengirimkan rekaman suara dan/atau foto kondisi fisik. Transkripsikan maksud keluhan dari audio/foto tersebut dan berikan pertolongan pertama yang sesuai.";

        $parts = [
            ['text' => $systemInstruction . "\n\n" . $promptText]
        ];

        if ($base64Image && $imageMimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageMimeType,
                    'data' => $base64Image,
                ]
            ];
        }

        if ($base64Audio && $audioMimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $audioMimeType,
                    'data' => $base64Audio,
                ]
            ];
        }

        if (empty($this->apiKey)) {
            $this->lastSource = 'smart_fallback';
            $this->lastError = 'GEMINI_API_KEY belum diisi di .env';
        } else {
            foreach ($this->models as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::withoutVerifying()
                        ->timeout(35)
                        ->post($url, [
                            'contents' => [['parts' => $parts]],
                            'generationConfig' => [
                                'response_mime_type' => 'application/json',
                                'temperature' => 0.2,
                            ]
                        ]);

                    if ($response->successful()) {
                        $rawText = $response->json('candidates.0.content.parts.0.text') ?? '{}';
                        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
                        $decoded = json_decode($cleanJson, true);

                        if (isset($decoded[0]) && is_array($decoded[0])) {
                            $decoded = $decoded[0];
                        }

                        if (is_array($decoded) && !empty($decoded['steps'])) {
                            $this->lastSource = 'gemini_live';
                            $this->lastError = null;
                            return $decoded;
                        }
                    } else {
                        $this->recordApiFailure($response->status());
                        Log::warning('Gemini FirstAid API Error', [
                            'model' => $model,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    $this->lastSource = 'smart_fallback';
                    $this->lastError = 'Timeout / gangguan koneksi ke server Gemini';
                    Log::error('Gemini FirstAid Exception: ' . $e->getMessage());
                    continue;
                }
            }
        }

        $lowerDesc = strtolower($description ?? '');

        if (str_contains($lowerDesc, 'berdarah') || str_contains($lowerDesc, 'luka') || str_contains($lowerDesc, 'tergores') || str_contains($lowerDesc, 'sobek') || str_contains($lowerDesc, 'jatuh')) {
            return [
                'detected_complaint' => $description ?: 'Luka Terbuka / Perdarahan Ringan-Sedang',
                'risk_level' => 'waspada',
                'status_label' => 'Waspada - Hentikan Perdarahan & Bersihkan Luka',
                'summary' => "Luka terbuka pada profil {$healthCategory} perlu segera dibersihkan dan ditekan untuk menghentikan perdarahan serta mencegah infeksi bakteri.",
                'steps' => [
                    'Tekan lembut area luka menggunakan kain bersih atau kasa steril selama 5–10 menit hingga darah berhenti.',
                    'Bilas perlahan dengan air bersih mengalir atau cairan NaCl 0,9% untuk membersihkan kotoran.',
                    'Oleskan antiseptik ringan (povidone-iodine) dan tutup dengan plester atau kasa steril.',
                ],
                'do_not' => 'Jangan menaburkan bubuk kopi, ramuan tidak steril, atau kapas langsung ke luka terbuka. Segera ke IGD jika darah tidak berhenti setelah 15 menit.',
                'recommended_poli' => 'UGD / Poli Umum Puskesmas ' . $kelurahan,
            ];
        }

        if (str_contains($lowerDesc, 'terkilir') || str_contains($lowerDesc, 'keseleo') || str_contains($lowerDesc, 'bengkak') || str_contains($lowerDesc, 'memar')) {
            return [
                'detected_complaint' => $description ?: 'Cedera Terkilir / Keseleo Sendi',
                'risk_level' => 'aman',
                'status_label' => 'Ringan - Terapkan Protokol R.I.C.E',
                'summary' => "Cedera terkilir ringan pada profil {$healthCategory} umumnya ditandai nyeri lokal dan pembengkakan jaringan lunak di sekitar sendi.",
                'steps' => [
                    'Rest: Istirahatkan anggota gerak yang terkilir dan hindari menumpu beban berat.',
                    'Ice: Kompres dingin dengan es terbungkus handuk selama 15–20 menit setiap 2–3 jam.',
                    'Compression & Elevation: Balut ringan dengan perban elastis dan posisikan bagian terkilir lebih tinggi saat berbaring.',
                ],
                'do_not' => 'Jangan memijat keras atau mengurut area yang baru terkilir dalam 48 jam pertama karena dapat memperparah robekan jaringan.',
                'recommended_poli' => 'Poli Umum Puskesmas ' . $kelurahan,
            ];
        }

        if (str_contains($lowerDesc, 'gatal') || str_contains($lowerDesc, 'ruam') || str_contains($lowerDesc, 'kulit') || str_contains($lowerDesc, 'bentol')) {
            return [
                'detected_complaint' => $description ?: 'Gatal-gatal dan Iritasi pada Kulit',
                'risk_level' => 'aman',
                'status_label' => 'Ringan - Perawatan Mandiri & Observasi Kulit',
                'summary' => "Keluhan gatal pada kulit untuk profil {$healthCategory} umumnya dipicu reaksi alergi ringan, iritasi kontak, gigitan serangga, atau kulit kering.",
                'steps' => [
                    'Kompres area kulit yang gatal dengan kain bersih dibasahi air dingin selama 10–15 menit untuk meredakan peradangan.',
                    'Oleskan losion calamine atau pelembap hipoalergenik tanpa pewangi, lalu kenakan pakaian katun yang longgar.',
                    'Cuci bersih area kulit dengan air mengalir dan sabun lembut jika diduga terkena pemicu iritasi atau debu.',
                ],
                'do_not' => 'Jangan menggaruk area yang gatal dengan kuku tajam karena dapat memicu luka lecet dan infeksi bakteri sekunder.',
                'recommended_poli' => 'Poli Umum / Kulit Puskesmas ' . $kelurahan,
            ];
        }

        return [
            'detected_complaint' => $description ? Str::limit($description, 60) : 'Keluhan Kesehatan Multimodal (Suara / Foto)',
            'risk_level' => 'waspada',
            'status_label' => 'Waspada - Lakukan Pertolongan Pertama & Pantau Gejala',
            'summary' => "Keluhan Anda telah dicatat untuk profil {$healthCategory}. Lakukan langkah stabilisasi awal di rumah dan periksakan ke fasilitas kesehatan terdekat apabila gejala tidak membaik.",
            'steps' => [
                'Hentikan aktivitas berat dan posisikan tubuh senyaman mungkin di ruangan dengan sirkulasi udara baik.',
                'Catat waktu munculnya gejala dan pantau perubahan tanda vital (suhu tubuh, tekanan darah, atau pernapasan).',
                'Cukupi kebutuhan cairan air putih apabila pasien sadar penuh dan tidak mengalami mual muntah berat.',
            ],
            'do_not' => 'Jangan mengonsumsi obat keras/antibiotik tanpa resep dokter dan jangan menunda ke IGD bila muncul sesak napas atau nyeri hebat.',
            'recommended_poli' => 'Poli Umum Puskesmas ' . $kelurahan,
        ];
    }

    public function askFollowUp(
        string $question,
        string $healthCategory,
        string $kelurahan,
        ?string $lastScanContext = null,
        ?string $base64Image = null,
        ?string $imageMimeType = null,
        ?string $base64Audio = null,
        ?string $audioMimeType = null
    ): string {
        $contextText = $lastScanContext
            ? "Riwayat pemindaian terakhir pengguna: {$lastScanContext}."
            : "Pengguna belum memiliki riwayat pemindaian terbaru.";

        $systemInstruction = "Kamu adalah asisten kesehatan preventif NusaCare AI yang empatik, akurat, dan komunikatif dalam bahasa Indonesia. "
            . "Profil pasien saat ini: Kategori Kesehatan = {$healthCategory}, Domisili = Kelurahan {$kelurahan}. "
            . "{$contextText} "
            . "Pengguna dapat bertanya melalui teks, foto (misalnya label gizi, obat, atau kondisi kulit), maupun rekaman suara. "
            . "Jawab pertanyaan pengguna secara jelas, aman secara klinis, disesuaikan dengan kategori medisnya (maksimal 2-3 paragraf pendek atau poin praktis), dan ingatkan untuk berkonsultasi ke Puskesmas jika muncul tanda bahaya.";

        $promptText = !empty($question)
            ? "Pertanyaan pengguna: {$question}"
            : "Pengguna mengirimkan rekaman suara dan/atau foto. Dengarkan audio atau amati foto tersebut lalu berikan jawaban konsultasi kesehatan yang relevan.";

        $parts = [
            ['text' => $systemInstruction . "\n\n" . $promptText]
        ];

        if ($base64Image && $imageMimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageMimeType,
                    'data' => $base64Image,
                ]
            ];
        }

        if ($base64Audio && $audioMimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $audioMimeType,
                    'data' => $base64Audio,
                ]
            ];
        }

        if (empty($this->apiKey)) {
            $this->lastSource = 'smart_fallback';
            $this->lastError = 'GEMINI_API_KEY belum diisi di .env';
        } else {
            foreach ($this->models as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::withoutVerifying()
                        ->timeout(30)
                        ->post($url, [
                            'contents' => [['parts' => $parts]],
                            'generationConfig' => [
                                'temperature' => 0.4,
                            ]
                        ]);

                    if ($response->successful()) {
                        $reply = $response->json('candidates.0.content.parts.0.text');
                        if (!empty($reply)) {
                            $this->lastSource = 'gemini_live';
                            $this->lastError = null;
                            return trim($reply);
                        }
                    } else {
                        $this->recordApiFailure($response->status());
                        Log::warning('Gemini Konsultasi API Error', [
                            'model' => $model,
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    $this->lastSource = 'smart_fallback';
                    $this->lastError = 'Timeout / gangguan koneksi ke server Gemini';
                    Log::error('Gemini Konsultasi Exception: ' . $e->getMessage());
                    continue;
                }
            }
        }

        $qLower = strtolower($question);

        if (str_contains($qLower, 'berdarah') || str_contains($qLower, 'luka') || str_contains($qLower, 'tergores') || str_contains($qLower, 'sobek') || str_contains($qLower, 'jatuh')) {
            return "Untuk penanganan awal kondisi **{$question}** pada profil **{$healthCategory}**, lakukan langkah pertolongan pertama luka berikut:\n"
                . "• **Hentikan Perdarahan:** Tekan lembut area luka menggunakan kain bersih atau kasa steril selama 5–10 menit hingga darah berhenti.\n"
                . "• **Bersihkan Luka:** Bilas perlahan menggunakan air bersih mengalir atau larutan infus NaCl 0,9% untuk membersihkan kotoran di sekitar luka.\n"
                . "• **Lindungi Luka:** Oleskan antiseptik ringan (seperti povidone-iodine) dan tutup dengan plester atau kasa steril agar tidak terinfeksi.\n\n"
                . "Hindari menaburkan kopi, bubuk, atau cairan iritan pada luka terbuka. Segera kunjungi **UGD / Poli Umum Puskesmas {$kelurahan}** jika perdarahan tidak berhenti setelah 15 menit penekanan atau luka tampak dalam.";
        }

        if (str_contains($qLower, 'terkilir') || str_contains($qLower, 'keseleo') || str_contains($qLower, 'bengkak') || str_contains($qLower, 'memar')) {
            return "Untuk penanganan awal kondisi **{$question}** pada profil **{$healthCategory}**, terapkan metode **R.I.C.E**:\n"
                . "• **Rest (Istirahatkan):** Hentikan aktivitas berdiri atau berjalan agar sendi tidak terbebani.\n"
                . "• **Ice (Kompres Dingin):** Kompres area yang terkilir dengan es terbungkus handuk selama 15–20 menit setiap 2–3 jam untuk mengurangi bengkak.\n"
                . "• **Compression & Elevation:** Balut ringan dengan perban elastis (jangan terlalu ketat) dan posisikan anggota gerak lebih tinggi dari jantung saat berbaring.\n\n"
                . "Hindari memijat keras area yang baru terkilir dalam 48 jam pertama. Segera periksakan ke **Poli Umum / UGD Puskesmas {$kelurahan}** jika nyeri terasa hebat.";
        }

        if (str_contains($qLower, 'gatal') || str_contains($qLower, 'ruam') || str_contains($qLower, 'kulit') || str_contains($qLower, 'bentol')) {
            return "Untuk keluhan **{$question}** pada profil **{$healthCategory}**, kompres area kulit dengan air dingin bersih selama 10–15 menit, gunakan pakaian katun yang longgar, dan oleskan losion penenang kulit (seperti calamine). Hindari menggaruk agar tidak memicu infeksi, dan segera ke **Puskesmas {$kelurahan}** jika muncul bengkak pada wajah/bibir atau sesak napas.";
        }

        if (str_contains($qLower, 'demam') || str_contains($qLower, 'pusing') || str_contains($qLower, 'sakit kepala') || str_contains($qLower, 'mual') || str_contains($qLower, 'batuk')) {
            return "Untuk keluhan **{$question}** pada profil **{$healthCategory}**, pastikan istirahat cukup, perbanyak minum air putih atau cairan elektrolit, dan lakukan kompres hangat bila suhu tubuh meningkat. Catat perkembangan gejala Anda di menu **Health & Mind Tracker**, dan segera periksakan ke **Puskesmas {$kelurahan}** apabila gejala tidak membaik dalam 24–48 jam.";
        }

        return "Menanggapi pertanyaan Anda mengenai **\"{$question}\"** untuk profil **{$healthCategory}** di Kelurahan **{$kelurahan}**: pastikan menjaga keseimbangan nutrisi harian (cukup protein, serat, dan air putih minimal 2 liter/hari), istirahat teratur, serta memantau tanda vital di menu **Health & Mind Tracker**. Apabila keluhan berlanjut atau memberat, segera lakukan skrining pada menu **AI Nutri & Triase Scan** untuk rujukan ke Puskesmas.";
    }

    public function generateHealthChallenge(
        string $focusGoal,
        string $healthCategory,
        ?string $vitalSummary = null
    ): array {
        $vitalText = $vitalSummary ?: 'Belum ada log vital terbaru.';

        $systemInstruction = "Kamu adalah AI Health & Mind Coach NusaCare AI. "
            . "Profil pengguna: Kategori Kesehatan = {$healthCategory}. Data vital terbaru: {$vitalText}. "
            . "Buat 3 tantangan harian yang praktis, aman, dan memotivasi sesuai fokus tujuan pengguna: '{$focusGoal}'. "
            . "Kembalikan HANYA JSON valid dengan struktur persis berikut:\n"
            . "{\n"
            . "  \"challenge_title\": \"Judul tantangan harian yang singkat & menarik (maksimal 8 kata)\",\n"
            . "  \"coach_insight\": \"Analisis singkat kondisi vital pengguna & alasan pemilihan tantangan ini (2 kalimat)\",\n"
            . "  \"missions\": [\n"
            . "    {\"time\": \"Pagi\", \"task\": \"Misi pagi spesifik & terukur\", \"points\": 30},\n"
            . "    {\"time\": \"Siang\", \"task\": \"Misi siang spesifik & terukur\", \"points\": 40},\n"
            . "    {\"time\": \"Malam\", \"task\": \"Misi malam spesifik & terukur\", \"points\": 30}\n"
            . "  ]\n"
            . "}";

        if (empty($this->apiKey)) {
            $this->lastSource = 'smart_fallback';
            $this->lastError = 'GEMINI_API_KEY belum diisi di .env';
        } else {
            foreach ($this->models as $model) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::withoutVerifying()
                        ->timeout(30)
                        ->post($url, [
                            'contents' => [['parts' => [['text' => $systemInstruction]]]],
                            'generationConfig' => [
                                'response_mime_type' => 'application/json',
                                'temperature' => 0.4,
                            ]
                        ]);

                    if ($response->successful()) {
                        $rawText = $response->json('candidates.0.content.parts.0.text') ?? '{}';
                        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
                        $decoded = json_decode($cleanJson, true);

                        if (isset($decoded[0]) && is_array($decoded[0])) {
                            $decoded = $decoded[0];
                        }

                        if (is_array($decoded) && !empty($decoded['missions'])) {
                            $this->lastSource = 'gemini_live';
                            $this->lastError = null;
                            return $decoded;
                        }
                    } else {
                        $this->recordApiFailure($response->status());
                    }
                } catch (\Throwable $e) {
                    $this->lastSource = 'smart_fallback';
                    $this->lastError = 'Koneksi jaringan ke Gemini terputus';
                    continue;
                }
            }
        }

        $fLower = strtolower($focusGoal);

        if (str_contains($fLower, 'stres') || str_contains($fLower, 'tidur') || str_contains($fLower, 'mental')) {
            return [
                'challenge_title' => 'Tantangan Reset Pikiran & Kualitas Tidur 24 Jam',
                'coach_insight' => "Berdasarkan profil {$healthCategory} dan riwayat skor stres Anda, menurunkan ketegangan saraf sebelum tidur akan membantu menstabilkan tekanan darah serta meningkatkan fokus harian.",
                'missions' => [
                    ['time' => 'Pagi', 'task' => 'Jalan santai atau peregangan ringan selama 15 menit di luar ruangan tanpa membuka ponsel.', 'points' => 30],
                    ['time' => 'Siang', 'task' => 'Latihan pernapasan dalam (tarik 4 detik, tahan 4 detik, embuskan 6 detik) sebanyak 5 siklus setelah makan siang.', 'points' => 40],
                    ['time' => 'Malam', 'task' => 'Hentikan paparan layar gawai 45 menit sebelum jam tidur dan tulis 2 hal yang disyukuri hari ini.', 'points' => 30],
                ],
            ];
        }

        if (str_contains($fLower, 'berat') || str_contains($fLower, 'massa') || str_contains($fLower, 'otot') || str_contains($fLower, 'bugar')) {
            return [
                'challenge_title' => 'Tantangan Surplus Nutrisi & Kekuatan Tubuh Harian',
                'coach_insight' => "Untuk mendukung target {$focusGoal} pada profil {$healthCategory}, kombinasi asupan protein berkualitas dan latihan beban ringan secara konsisten jauh lebih efektif dibanding makan berlebih sekaligus.",
                'missions' => [
                    ['time' => 'Pagi', 'task' => 'Sarapan tinggi protein (telur/tempe/ikan) ditambah 1 gelas air putih penuh 30 menit setelah bangun.', 'points' => 30],
                    ['time' => 'Siang', 'task' => 'Tambahkan 1 porsi camilan sehat padat gizi (pisang, kacang-kacangan, atau susu) di antara jam makan.', 'points' => 40],
                    ['time' => 'Sore', 'task' => 'Lakukan latihan kekuatan tubuh (push-up / squat / plank) 3 set ringan selama 15–20 menit.', 'points' => 30],
                ],
            ];
        }

        return [
            'challenge_title' => 'Tantangan Keseimbangan Vital & Hidrasi Optimal',
            'coach_insight' => "Program ini dirancang khusus untuk profil {$healthCategory} dengan fokus '{$focusGoal}' guna menjaga kestabilan tekanan darah, gula darah, dan kebugaran metabolik.",
            'missions' => [
                ['time' => 'Pagi', 'task' => 'Minum 500 ml air putih setelah bangun tidur dan lakukan pemanasan sendi selama 15 menit.', 'points' => 30],
                ['time' => 'Siang', 'task' => 'Terapkan piring gizi seimbang (½ piring sayur, ¼ protein, ¼ karbohidrat kompleks) tanpa minuman manis kemasan.', 'points' => 40],
                ['time' => 'Malam', 'task' => 'Catat log vital & skor stres Anda di Health & Mind Tracker lalu tidur minimal 7 jam.', 'points' => 30],
            ],
        ];
    }

    protected function recordApiFailure(int $status): void
    {
        $this->lastSource = 'smart_fallback';
        $this->lastError = match (true) {
            $status === 429 => 'Kuota Gemini API Limit (HTTP 429)',
            in_array($status, [400, 401, 403]) => "API Key Tidak Valid / Ditolak (HTTP {$status})",
            default => "Server Gemini Gangguan (HTTP {$status})",
        };
    }
}