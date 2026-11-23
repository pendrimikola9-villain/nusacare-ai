@extends('layouts.app')
@section('title', 'Asisten Konsultasi AI')
@section('header_title', 'Asisten Konsultasi Kesehatan Preventif AI')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Kolom Kiri: Ringkasan Konteks Medis Pasien -->
    <div class="lg:col-span-4 space-y-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <span class="inline-block px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-100">Konteks Pasien Aktif</span>
            <h2 class="text-sm font-bold text-slate-900">{{ $user->name }}</h2>
            <div class="text-xs text-slate-600 space-y-1">
                <p>Profil Medis: <strong class="text-indigo-700">{{ $user->health_category }}</strong></p>
                <p>Domisili: <strong>Kelurahan {{ $user->kelurahan }}</strong></p>
            </div>
            <a href="{{ route('profil') }}" class="inline-block text-[11px] font-semibold text-indigo-600 hover:underline pt-1">
                Ubah Profil Medis →
            </a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Hasil Pemindaian Terakhir</h3>
            @if($latestScan)
                <p class="text-xs font-semibold text-slate-800">{{ Str::limit($latestScan->input_summary, 110) }}</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                    {{ $latestScan->ai_result['status_label'] ?? $latestScan->risk_level }}
                </span>
                <p class="text-[11px] text-slate-500 leading-relaxed pt-1">
                    {{ Str::limit($latestScan->ai_result['summary'] ?? '', 140) }}
                </p>
            @else
                <p class="text-xs text-slate-500">Belum ada riwayat pemindaian. Jawaban AI akan disesuaikan dengan profil kesehatan Anda.</p>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Ruang Diskusi Interaktif Multimodal AI -->
    <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between min-h-[520px]">
        <div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3.5 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Tanya-Jawab Kesehatan & Gizi (Multimodal)</h2>
                    <p class="text-xs text-slate-500">Kirim pertanyaan lewat teks, rekaman suara langsung, atau foto kemasan/keluhan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-200">Suara • Foto • Teks</span>
                    @if(count($chatHistory) > 0)
                        <form action="{{ route('konsultasi.reset') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-[11px] font-semibold transition">
                                Hapus Chat
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                @forelse($chatHistory as $chat)
                    <div class="space-y-2.5">
                        <div class="flex justify-end">
                            <div class="bg-indigo-600 text-white px-4 py-2.5 rounded-2xl rounded-tr-sm text-xs max-w-md shadow-xs">
                                <p>{{ $chat['question'] }}</p>
                                <span class="block text-[10px] text-indigo-200 mt-1 text-right">{{ $chat['time'] }}</span>
                            </div>
                        </div>
                        <div class="flex justify-start">
                            <div class="bg-slate-50 border border-slate-200 text-slate-800 px-4 py-3 rounded-2xl rounded-tl-sm text-xs max-w-xl leading-relaxed">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">NusaCare AI</span>
                                    @if(($chat['source'] ?? '') === 'gemini_live')
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold">
                                            Gemini Live
                                        </span>
                                    @elseif(!empty($chat['error_note']))
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[9px] font-bold">
                                            Mode Cadangan • {{ $chat['error_note'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="space-y-1.5">
                                    {!! nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong class="font-bold text-slate-900">$1</strong>', e($chat['answer']))) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-1">
                        <p class="text-xs font-semibold text-slate-700">Mulai Konsultasi Preventif Anda</p>
                        <p class="text-xs text-slate-500">Pilih topik cepat, rekam suara pertanyaan, unggah foto, atau ketik langsung di bawah.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Panel Input Multimodal -->
        <div class="space-y-3 pt-4 border-t border-slate-100 mt-4">
            <div class="flex flex-wrap gap-1.5">
                <button type="button" onclick="isiPertanyaan('Apa alternatif makanan/camilan sehat yang aman untuk kondisi saya?')" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-medium transition">
                    + Alternatif makanan sehat?
                </button>
                <button type="button" onclick="isiPertanyaan('Apa langkah pencegahan terbaik berdasarkan hasil scan terakhir saya?')" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-medium transition">
                    + Tindak lanjut hasil scan?
                </button>
                <button type="button" onclick="isiPertanyaan('Tanda bahaya apa yang mengharuskan saya segera ke Puskesmas?')" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-medium transition">
                    + Kapan harus ke Puskesmas?
                </button>
            </div>

            <form action="{{ route('konsultasi.send') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf

                <!-- Kontrol Suara & Foto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <button type="button" id="chatRecordBtn" onclick="toggleChatRecording()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-700 transition">
                                <span id="chatRecordDot" class="w-2 h-2 rounded-full bg-white"></span>
                                <span id="chatRecordLabel">Rekam Suara</span>
                            </button>
                            <span id="chatRecordStatus" class="text-[11px] text-slate-500">Opsional</span>
                        </div>
                        <audio id="chatAudioPreview" controls class="hidden h-7 w-28"></audio>
                        <input type="hidden" id="chatAudioBase64" name="audio_base64">
                        <input type="hidden" id="chatAudioMime" name="audio_mime" value="audio/webm">
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 flex items-center">
                        <input type="file" name="image" accept="image/*" class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>

                <!-- Bar Input Teks & Kirim -->
                <div class="flex gap-2">
                    <input type="text" id="questionInput" name="question" placeholder="Ketik pertanyaan atau bicara lewat tombol Rekam Suara..." class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function isiPertanyaan(teks) {
    const input = document.getElementById('questionInput');
    input.value = teks;
    input.focus();
}

let chatRecorder = null;
let chatChunks = [];
let isChatRecording = false;
let chatRecognition = null;

async function toggleChatRecording() {
    const btn = document.getElementById('chatRecordBtn');
    const dot = document.getElementById('chatRecordDot');
    const label = document.getElementById('chatRecordLabel');
    const status = document.getElementById('chatRecordStatus');
    const preview = document.getElementById('chatAudioPreview');
    const base64Input = document.getElementById('chatAudioBase64');
    const mimeInput = document.getElementById('chatAudioMime');
    const questionInput = document.getElementById('questionInput');

    if (!isChatRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chatRecorder = new MediaRecorder(stream);
            chatChunks = [];

            chatRecorder.ondataavailable = e => {
                if (e.data.size > 0) chatChunks.push(e.data);
            };

            chatRecorder.onstop = () => {
                const mimeType = chatRecorder.mimeType || 'audio/webm';
                const audioBlob = new Blob(chatChunks, { type: mimeType });
                preview.src = URL.createObjectURL(audioBlob);
                preview.classList.remove('hidden');

                const reader = new FileReader();
                reader.onloadend = () => {
                    base64Input.value = reader.result.split(',')[1];
                    mimeInput.value = mimeType;
                    status.textContent = 'Suara siap';
                    status.className = 'text-[11px] font-semibold text-emerald-700';
                };
                reader.readAsDataURL(audioBlob);

                stream.getTracks().forEach(track => track.stop());
            };

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (SpeechRecognition) {
                chatRecognition = new SpeechRecognition();
                chatRecognition.lang = 'id-ID';
                chatRecognition.continuous = true;
                chatRecognition.interimResults = true;
                chatRecognition.onresult = event => {
                    let transcript = '';
                    for (let i = 0; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    questionInput.value = transcript;
                };
                chatRecognition.start();
            }

            chatRecorder.start();
            isChatRecording = true;
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btn.classList.add('bg-rose-600', 'hover:bg-rose-700');
            dot.classList.add('animate-ping');
            label.textContent = 'Stop';
            status.textContent = 'Merekam...';
            status.className = 'text-[11px] font-semibold text-rose-600';
        } catch (err) {
            status.textContent = 'Mikrofon diblokir';
            status.className = 'text-[11px] text-rose-600';
        }
    } else {
        if (chatRecognition) {
            try { chatRecognition.stop(); } catch (e) {}
        }
        chatRecorder.stop();
        isChatRecording = false;
        btn.classList.remove('bg-rose-600', 'hover:bg-rose-700');
        btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
        dot.classList.remove('animate-ping');
        label.textContent = 'Rekam Ulang';
    }
}
</script>
@endpush