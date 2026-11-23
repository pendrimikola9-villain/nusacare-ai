@extends('layouts.app')
@section('title', 'Program & Pengingat AI')
@section('header_title', 'AI Wellness, Meal Planner & Smart Reminder')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Buat Program Baru -->
    <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-bold text-slate-900">Buat Program Kesehatan</h2>
            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-100">AI Planner</span>
        </div>
        <p class="text-xs text-slate-500 mb-5">Input target Anda, AI akan menyusun timeline olahraga, pola makan sehat, dan pengingat harian.</p>

        <form action="{{ route('wellness.generate') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target / Nama Program</label>
                <input type="text" name="title" required placeholder="Misal: Kontrol Gula Darah & Kebugaran 14 Hari" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Program (Hari)</label>
                <select name="duration_days" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="7">7 Hari (Mingguan)</option>
                    <option value="14">14 Hari (2 Minggu)</option>
                    <option value="30">30 Hari (Bulanan)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Preferensi Waktu & Bahan Makanan</label>
                <textarea name="preference" rows="3" placeholder="Misal: Olahraga pagi jam 06.30 di rumah tanpa alat, menu tinggi protein lokal (ikan, telur, tempe)." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate Timeline & Pengingat AI
            </button>
        </form>
    </div>

    <!-- Daftar Program & Timeline Interaktif -->
    <div class="lg:col-span-8 space-y-5">
        @if(session('wellness_api_status'))
            @php $apiStat = session('wellness_api_status'); @endphp
            @if(($apiStat['source'] ?? '') === 'gemini_live')
                <div class="px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
                    <span>Jadwal disusun secara dinamis oleh <strong>Gemini Live AI</strong>.</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold">Gemini Live</span>
                </div>
            @elseif(!empty($apiStat['error_note']))
                <div class="px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-center justify-between">
                    <span>Menggunakan perencana cadangan karena <strong>{{ $apiStat['error_note'] }}</strong>.</span>
                    <span class="px-2 py-0.5 rounded bg-amber-500 text-white text-[10px] font-bold">Mode Cadangan</span>
                </div>
            @endif
        @endif

        @forelse($programs as $program)
            @php
                $steps = array_values((array) $program->ai_timeline);
                $totalSteps = count($steps);
                $doneSteps = collect($steps)->filter(fn($s) => filter_var($s['done'] ?? false, FILTER_VALIDATE_BOOLEAN))->count();
                $percent = $totalSteps > 0 ? round(($doneSteps / $totalSteps) * 100) : 0;
            @endphp
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900">{{ $program->title }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $percent === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-50 text-indigo-700' }}">
                                {{ $doneSteps }}/{{ $totalSteps }} Selesai ({{ $percent }}%)
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Durasi: {{ $program->duration_days }} Hari • Profil: <span class="font-semibold text-indigo-700">{{ auth()->user()->health_category }}</span></p>
                    </div>
                    <a href="{{ route('wellness.ics', $program->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-indigo-950 transition whitespace-nowrap shrink-0 self-start sm:self-center">
                        <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                        Simpan Pengingat ke Kalender (.ics)
                    </a>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-3">
                    <div class="bg-emerald-500 h-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($steps as $index => $item)
                        @php $isDone = filter_var($item['done'] ?? false, FILTER_VALIDATE_BOOLEAN); @endphp
                        <div class="p-3.5 rounded-xl border {{ $isDone ? 'bg-emerald-50/40 border-emerald-200' : 'bg-white border-slate-200 hover:border-indigo-300' }} flex items-center justify-between gap-4 transition">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-mono text-xs font-bold whitespace-nowrap shrink-0">
                                    H{{ $item['day'] ?? 1 }} • {{ $item['time'] ?? '07:00' }}
                                </span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">{{ $item['category'] ?? 'Aktivitas' }}</span>
                                    <p class="text-sm font-medium {{ $isDone ? 'line-through text-slate-400' : 'text-slate-800' }}">{{ $item['activity'] ?? '' }}</p>
                                </div>
                            </div>
                            <form action="{{ route('wellness.toggle', [$program->id, $index]) }}" method="POST" class="shrink-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition whitespace-nowrap {{ $isDone ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-600 hover:text-indigo-700' }}">
                                    {{ $isDone ? '✓ Selesai' : 'Tandai Selesai' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-sm text-slate-500">
                Belum ada program kesehatan. Silakan isi form di samping untuk membuat timeline otomatis.
            </div>
        @endforelse
    </div>
</div>
@endsection