@extends('layouts.app')
@section('title', 'Health & Mind Tracker')
@section('header_title', 'Health & Mind Tracker Harian')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Input Log Harian -->
    <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
        <h2 class="text-base font-bold text-slate-900 mb-4">Catat Kondisi Hari Ini</h2>
        <form action="{{ route('tracker.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                <input type="date" name="log_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Berat Badan (kg)</label>
                    <input type="number" step="0.1" name="weight" placeholder="56.5" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tensi (mmHg)</label>
                    <input type="text" name="blood_pressure" placeholder="120/80" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Gula Darah Sewaktu (mg/dL)</label>
                <input type="number" name="blood_sugar" placeholder="105" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Stres & Beban Pikiran (1 = Tenang, 10 = Sangat Stres)</label>
                <input type="range" name="stress_score" min="1" max="10" value="3" class="w-full accent-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jurnal Singkat / Catatan Emosi (MindCare)</label>
                <textarea name="mood_note" rows="2" placeholder="Bagaimana perasaan & kualitas tidur Anda hari ini?" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition">Simpan Log Kesehatan</button>
        </form>
    </div>

    <!-- AI Health & Mind Challenge + Riwayat Log -->
    <div class="lg:col-span-8 space-y-6">
        <!-- Panel Generator Tantangan Kesehatan AI -->
        <div class="bg-indigo-950 text-white p-6 rounded-2xl shadow-sm border border-indigo-800/60 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-300">AI Health & Mind Coach</span>
                    <h2 class="text-base font-bold text-white mt-0.5">Tantangan Kesehatan Personal Berbasis AI</h2>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-900/90 border border-indigo-700/70 text-[11px] text-indigo-200 font-semibold">
                    Rata-rata Stres: {{ round($trackers->avg('stress_score') ?? 0, 1) }}/10
                </span>
            </div>

            <!-- Form Minta Tantangan ke AI -->
            <form action="{{ route('tracker.challenge') }}" method="POST" class="space-y-2.5">
                @csrf
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setChallengeGoal('Turunkan stres & perbaiki kualitas tidur')" class="px-2.5 py-1 rounded-lg bg-indigo-900/80 hover:bg-indigo-800 text-indigo-200 text-[11px] font-medium border border-indigo-700/60 transition">
                        + Redakan Stres & Tidur Nyenyak
                    </button>
                    <button type="button" onclick="setChallengeGoal('Stabilkan tekanan darah & gula darah')" class="px-2.5 py-1 rounded-lg bg-indigo-900/80 hover:bg-indigo-800 text-indigo-200 text-[11px] font-medium border border-indigo-700/60 transition">
                        + Stabilkan Tensi & Gula
                    </button>
                    <button type="button" onclick="setChallengeGoal('Tingkatkan kebugaran & berat badan ideal')" class="px-2.5 py-1 rounded-lg bg-indigo-900/80 hover:bg-indigo-800 text-indigo-200 text-[11px] font-medium border border-indigo-700/60 transition">
                        + Kebugaran & Massa Tubuh
                    </button>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="focusGoalInput" name="focus_goal" value="{{ $activeChallenge['focus_goal'] ?? '' }}" placeholder="Tulis target kesehatan Anda hari ini (misal: kurangi gula & olahraga ringan)..." required class="flex-1 px-3.5 py-2.5 rounded-xl bg-indigo-900/60 border border-indigo-700/80 text-white placeholder-indigo-300/70 text-xs focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-white font-bold text-xs transition whitespace-nowrap">
                        Minta Tantangan AI
                    </button>
                </div>
            </form>

            <!-- Output Misi Tantangan dari AI -->
            @if(!empty($activeChallenge))
                <div class="p-4 rounded-xl bg-indigo-900/50 border border-indigo-700/60 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $activeChallenge['challenge_title'] ?? 'Tantangan Harian Anda' }}</h3>
                            <p class="text-xs text-indigo-200 mt-0.5 leading-relaxed">{{ $activeChallenge['coach_insight'] ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(($activeChallenge['source'] ?? '') === 'gemini_live')
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold">Gemini Live</span>
                            @elseif(!empty($activeChallenge['error_note']))
                                <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-200 border border-amber-400/30 text-[10px] font-bold">{{ $activeChallenge['error_note'] }}</span>
                            @endif
                            <span id="challengeScoreBadge" class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-mono text-xs font-bold">
                                0 / 100 Poin
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach(($activeChallenge['missions'] ?? []) as $idx => $mission)
                            <label class="flex items-start gap-3 p-3 rounded-xl bg-indigo-950/70 border border-indigo-800/70 cursor-pointer hover:border-indigo-500 transition">
                                <input type="checkbox" onchange="updateChallengePoints()" data-points="{{ $mission['points'] ?? 30 }}" class="mission-checkbox mt-0.5 rounded border-indigo-500 text-indigo-500 focus:ring-indigo-400">
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-indigo-300 uppercase text-[10px] tracking-wider">{{ $mission['time'] ?? 'Harian' }}</span>
                                        <span class="text-[10px] font-semibold text-emerald-300">+{{ $mission['points'] ?? 30 }} Poin</span>
                                    </div>
                                    <p class="text-indigo-50 mt-0.5">{{ $mission['task'] ?? '' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-xs text-indigo-200/90 leading-relaxed">
                    Pilih salah satu fokus di atas atau tuliskan target kesehatan pribadi Anda agar AI Coach menyusun 3 misi tantangan harian sesuai data vital dan profil medis Anda.
                </p>
            @endif
        </div>

        <!-- Tabel Riwayat Catatan Vital -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Riwayat Catatan Vital & MindCare</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Berat</th>
                            <th class="py-2.5 px-3">Tensi</th>
                            <th class="py-2.5 px-3">Gula Darah</th>
                            <th class="py-2.5 px-3">Skor Stres</th>
                            <th class="py-2.5 px-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($trackers->reverse() as $log)
                        <tr>
                            <td class="py-2.5 px-3 font-semibold">{{ $log->log_date }}</td>
                            <td class="py-2.5 px-3">{{ $log->weight ?? '-' }} kg</td>
                            <td class="py-2.5 px-3">{{ $log->blood_pressure ?? '-' }}</td>
                            <td class="py-2.5 px-3">{{ $log->blood_sugar ?? '-' }} mg/dL</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full font-bold {{ $log->stress_score > 5 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $log->stress_score }}/10
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500">{{ $log->mood_note ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setChallengeGoal(goal) {
    const input = document.getElementById('focusGoalInput');
    input.value = goal;
    input.focus();
}

function updateChallengePoints() {
    let total = 0;
    document.querySelectorAll('.mission-checkbox:checked').forEach(cb => {
        total += parseInt(cb.dataset.points || 0, 10);
    });
    const badge = document.getElementById('challengeScoreBadge');
    if (badge) {
        badge.textContent = `${total} / 100 Poin`;
        if (total >= 100) {
            badge.classList.remove('bg-indigo-600');
            badge.classList.add('bg-emerald-600');
        } else {
            badge.classList.remove('bg-emerald-600');
            badge.classList.add('bg-indigo-600');
        }
    }
}
</script>
@endpush