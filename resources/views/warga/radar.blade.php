@extends('layouts.app')
@section('title', 'Peta Radar Wabah & Rujukan')
@section('header_title', 'Radar Kewaspadaan Wabah & Pertolongan Pertama AI')

@section('content')
<div class="space-y-6">
    <!-- Asisten Pertolongan Pertama Darurat Multimodal (Suara + Foto + Teks) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100 mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold uppercase tracking-wider">Darurat Multimodal AI</span>
                    <span class="text-xs text-slate-400">Suara • Foto • Teks</span>
                </div>
                <h2 class="text-base font-bold text-slate-900 mt-1">Asisten Pertolongan Pertama Cepat (P3K AI)</h2>
                <p class="text-xs text-slate-500">Dalam situasi panik, cukup rekam suara keluhan, potret luka/kondisi fisik, atau ketik singkat untuk panduan pertolongan pertama instan.</p>
            </div>
            <a href="tel:119" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Panggil Darurat 119
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Multimodal -->
            <form action="{{ route('radar.first-aid') }}" method="POST" enctype="multipart/form-data" class="lg:col-span-6 space-y-4">
                @csrf

                <!-- Tombol Situasi Cepat -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Situasi Darurat Cepat (Opsional)</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setQuickEmergency('Demam tinggi mendadak 39°C disertai nyeri sendi dan bintik merah (curiga DBD).')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-[11px] font-semibold transition">
                            + Demam Tinggi / DBD
                        </button>
                        <button type="button" onclick="setQuickEmergency('Ibu hamil mengalami pusing berat, pandangan kabur, dan bengkak pada kaki.')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-[11px] font-semibold transition">
                            + Tanda Bahaya Kehamilan
                        </button>
                        <button type="button" onclick="setQuickEmergency('Tekanan darah melonjak tinggi disertai sakit kepala belakang dan sesak ringan.')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-[11px] font-semibold transition">
                            + Hipertensi Mendadak
                        </button>
                    </div>
                </div>

                <!-- Perekam Suara + Unggah Foto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Rekam Suara Mikrofon -->
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2">
                        <span class="block text-xs font-bold text-slate-800">1. Rekam Suara Keluhan</span>
                        <div class="flex items-center gap-2">
                            <button type="button" id="recordBtn" onclick="toggleRecording()" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition">
                                <span id="recordDot" class="w-2 h-2 rounded-full bg-white"></span>
                                <span id="recordLabel">Mulai Rekam</span>
                            </button>
                        </div>
                        <p id="recordStatus" class="text-[11px] text-slate-500">Klik tombol untuk bicara langsung.</p>
                        <audio id="audioPreview" controls class="hidden w-full h-8 mt-1"></audio>
                        <input type="hidden" id="audioBase64Input" name="audio_base64">
                        <input type="hidden" id="audioMimeInput" name="audio_mime" value="audio/webm">
                    </div>

                    <!-- Ambil / Unggah Foto -->
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2">
                        <span class="block text-xs font-bold text-slate-800">2. Foto Luka / Kondisi Fisik</span>
                        <input type="file" name="image" accept="image/*" capture="environment" class="w-full text-[11px] text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-[11px] text-slate-500">Opsional: foto ruam kulit, luka bakar, atau hasil tensimeter.</p>
                    </div>
                </div>

                <!-- Ketikan Singkat -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">3. Keterangan Singkat (Otomatis terisi saat bicara / ketik manual)</label>
                    <textarea id="emergencyDesc" name="description" rows="2" placeholder="Jelaskan kondisi pasien secara singkat..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Analisis Darurat & Panduan Pertolongan Pertama
                </button>
            </form>

            <!-- Panel Output Hasil Pertolongan Pertama AI -->
            <div class="lg:col-span-6">
                @if($firstAidResult)
                    @php
                        $faRisk = $firstAidResult['risk_level'] ?? 'waspada';
                        $faHeader = match($faRisk) {
                            'darurat' => 'bg-rose-600 text-white',
                            'aman' => 'bg-emerald-600 text-white',
                            default => 'bg-amber-500 text-white',
                        };
                    @endphp
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs h-full flex flex-col justify-between">
                        <div>
                            <div class="{{ $faHeader }} px-5 py-3.5 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider opacity-90">Instruksi Pertolongan Pertama AI</span>
                                    <h3 class="text-sm font-extrabold">{{ $firstAidResult['status_label'] ?? strtoupper($faRisk) }}</h3>
                                </div>
                                @if(!empty($firstAidResult['referral_code']))
                                    <span class="px-2.5 py-1 rounded-lg bg-white/20 font-mono text-xs font-bold whitespace-nowrap">
                                        Tiket: {{ $firstAidResult['referral_code'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-5 space-y-3.5 text-xs">
                                @if(($firstAidResult['source'] ?? '') === 'gemini_live')
                                    <span class="inline-block px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                        Sumber: Gemini Live Multimodal
                                    </span>
                                @elseif(!empty($firstAidResult['error_note']))
                                    <div class="px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium">
                                        Info Sistem: Menggunakan panduan cadangan karena <strong>{{ $firstAidResult['error_note'] }}</strong>.
                                    </div>
                                @endif

                                <p class="text-slate-700 leading-relaxed">{{ $firstAidResult['summary'] ?? '' }}</p>

                                <div>
                                    <span class="block font-bold text-indigo-700 uppercase tracking-wider text-[10px] mb-1.5">Langkah Penanganan Segera:</span>
                                    <ol class="list-decimal pl-4 space-y-1.5 text-slate-800 font-medium">
                                        @foreach(($firstAidResult['steps'] ?? []) as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>

                                @if(!empty($firstAidResult['do_not']))
                                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                                        <strong>PERINGATAN (JANGAN DILAKUKAN):</strong> {{ $firstAidResult['do_not'] }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">Tujuan Rujukan: <strong class="text-slate-800">{{ $firstAidResult['recommended_poli'] ?? 'Puskesmas Terdekat' }}</strong></span>
                            <a href="{{ route('profil') }}" class="font-bold text-indigo-600 hover:underline">Buka Kartu Sehat →</a>
                        </div>
                    </div>
                @else
                    <div class="h-full rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 flex flex-col justify-center text-center space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800">Siap Menerima Input Suara, Foto, atau Teks</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                            Hasil analisis akan menampilkan langkah pertolongan pertama bertahap (1-2-3), larangan medis yang harus dihindari, serta penerbitan tiket rujukan otomatis bila terdeteksi kondisi waspada/darurat.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Peta Leaflet -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Peta Sebaran Klaster Gejala Komunitas</h2>
                <p class="text-xs text-slate-500">Agregasi anonim laporan triase warga per kelurahan untuk deteksi dini DBD, ISPA, dan Diare.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Aman</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Waspada</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Klaster Tinggi</span>
            </div>
        </div>
        <div id="radarMap" class="w-full h-72 rounded-xl border border-slate-200 z-10"></div>
    </div>

    <!-- Direktori Faskes & Tabel Riwayat -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Direktori Faskes Lokal -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3 h-fit">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Direktori Faskes Terdekat</h3>
                <p class="text-xs text-slate-500">Kontak cepat Puskesmas & RS Rujukan.</p>
            </div>
            <div class="space-y-2.5">
                @foreach($faskesDirectory as $faskes)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-indigo-700">{{ $faskes['type'] }}</span>
                        <p class="text-xs font-bold text-slate-900">{{ $faskes['name'] }}</p>
                        <p class="text-[11px] text-slate-500">{{ $faskes['area'] }}</p>
                    </div>
                    <a href="tel:{{ $faskes['phone'] }}" class="px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold hover:bg-indigo-700 transition">
                        Hubungi
                    </a>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Tabel Riwayat & Tiket Rujukan -->
        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Riwayat Skrining, P3K Darurat & Tiket Rujukan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Jenis</th>
                            <th class="py-3 px-3">Ringkasan Input</th>
                            <th class="py-3 px-3">Hasil AI</th>
                            <th class="py-3 px-3">Kode Rujukan</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($screenings as $item)
                        <tr>
                            <td class="py-3 px-3">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="py-3 px-3 uppercase font-semibold text-indigo-700">{{ $item->type }}</td>
                            <td class="py-3 px-3 max-w-xs truncate">{{ $item->input_summary }}</td>
                            <td class="py-3 px-3 font-medium">{{ $item->ai_result['status_label'] ?? $item->risk_level }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $item->referral_code ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @if($item->referral_status)
                                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase font-bold text-[10px]">
                                        {{ $item->referral_status }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Mandiri</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada riwayat skrining.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 1. Peta Leaflet Radar Wabah
const map = L.map('radarMap').setView([-3.2952, 114.5912], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

const points = @json($radarPoints);
points.forEach(p => {
    const color = p.level === 'darurat' ? '#E11D48' : (p.level === 'waspada' ? '#F59E0B' : '#10B981');
    L.circleMarker([p.lat, p.lng], {
        radius: 14,
        fillColor: color,
        color: '#fff',
        weight: 2,
        fillOpacity: 0.75
    }).addTo(map).bindPopup(`<strong>Kelurahan ${p.kelurahan}</strong><br>Dominan: ${p.disease}<br>Laporan Aktif: ${p.cases} warga`);
});

// 2. Helper Tombol Situasi Darurat Cepat
function setQuickEmergency(text) {
    const textarea = document.getElementById('emergencyDesc');
    textarea.value = text;
    textarea.focus();
}

// 3. Perekam Suara Browser (Web Audio MediaRecorder API + Transkrip Otomatis)
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let recognition = null;

async function toggleRecording() {
    const btn = document.getElementById('recordBtn');
    const dot = document.getElementById('recordDot');
    const label = document.getElementById('recordLabel');
    const status = document.getElementById('recordStatus');
    const preview = document.getElementById('audioPreview');
    const base64Input = document.getElementById('audioBase64Input');
    const mimeInput = document.getElementById('audioMimeInput');
    const descBox = document.getElementById('emergencyDesc');

    if (!isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => {
                if (e.data.size > 0) audioChunks.push(e.data);
            };

            mediaRecorder.onstop = () => {
                const mimeType = mediaRecorder.mimeType || 'audio/webm';
                const audioBlob = new Blob(audioChunks, { type: mimeType });
                preview.src = URL.createObjectURL(audioBlob);
                preview.classList.remove('hidden');

                const reader = new FileReader();
                reader.onloadend = () => {
                    const base64String = reader.result.split(',')[1];
                    base64Input.value = base64String;
                    mimeInput.value = mimeType;
                    status.textContent = 'Rekaman suara siap dikirim ke AI.';
                    status.className = 'text-[11px] font-semibold text-emerald-700';
                };
                reader.readAsDataURL(audioBlob);

                stream.getTracks().forEach(track => track.stop());
            };

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.continuous = true;
                recognition.interimResults = true;
                recognition.onresult = event => {
                    let transcript = '';
                    for (let i = 0; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    descBox.value = transcript;
                };
                recognition.start();
            }

            mediaRecorder.start();
            isRecording = true;
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btn.classList.add('bg-rose-600', 'hover:bg-rose-700');
            dot.classList.add('animate-ping');
            label.textContent = 'Hentikan Rekam';
            status.textContent = 'Merekam suara... ceritakan keluhan Anda.';
            status.className = 'text-[11px] font-semibold text-rose-600';
        } catch (err) {
            status.textContent = 'Izin mikrofon ditolak atau perangkat tidak tersedia.';
            status.className = 'text-[11px] text-rose-600';
        }
    } else {
        if (recognition) {
            try { recognition.stop(); } catch (e) {}
        }
        mediaRecorder.stop();
        isRecording = false;
        btn.classList.remove('bg-rose-600', 'hover:bg-rose-700');
        btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
        dot.classList.remove('animate-ping');
        label.textContent = 'Rekam Ulang';
    }
}
</script>
@endpush