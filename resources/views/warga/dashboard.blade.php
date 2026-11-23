@extends('layouts.app')
@section('title', 'Dashboard Kesehatan')
@section('header_title', 'Dashboard Kesehatan Terpadu')

@section('content')
<div class="space-y-6">
    <!-- Kartu Statistik Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Tekanan Darah Terakhir</span>
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $latestTracker->blood_pressure ?? '120/80' }} <span class="text-xs font-normal text-slate-400">mmHg</span></p>
            <span class="inline-block mt-2 text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">Terpantau Berkala</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Indeks Stres & Mental</span>
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $latestTracker->stress_score ?? 3 }}<span class="text-sm font-normal text-slate-400">/10</span></p>
            <span class="inline-block mt-2 text-[11px] px-2 py-0.5 rounded-full {{ ($latestTracker->stress_score ?? 3) > 5 ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700' }} font-medium">
                {{ ($latestTracker->stress_score ?? 3) > 5 ? 'Perlu Relaksasi' : 'Kondisi Stabil' }}
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Progres Program AI</span>
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            @php
                $timeline = $activeProgram->ai_timeline ?? [];
                $doneCount = collect($timeline)->where('done', true)->count();
                $totalCount = count($timeline);
                $percent = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
            @endphp
            <p class="text-2xl font-extrabold text-slate-900">{{ $percent }}%</p>
            <span class="inline-block mt-2 text-[11px] text-slate-500">{{ $doneCount }} dari {{ $totalCount }} agenda selesai</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Status Wilayah ({{ $user->kelurahan }})</span>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <p class="text-xl font-extrabold text-amber-600">Waspada ISPA</p>
            <a href="{{ route('radar') }}" class="inline-block mt-2 text-[11px] text-indigo-600 font-semibold hover:underline">Lihat Peta Radar →</a>
        </div>
    </div>

    <!-- Tiket Rujukan Aktif (Jika Ada) -->
    @if($activeReferral)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="p-2.5 rounded-xl bg-amber-100 text-amber-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Tiket Rujukan Puskesmas Aktif</span>
                    <span class="px-2 py-0.5 rounded bg-amber-200 text-amber-900 text-xs font-mono font-bold">{{ $activeReferral->referral_code }}</span>
                </div>
                <p class="text-sm text-slate-800 font-medium mt-1">{{ $activeReferral->ai_result['recommended_poli'] ?? 'Poli Umum Puskesmas' }} — Status: <span class="uppercase font-bold">{{ $activeReferral->referral_status }}</span></p>
            </div>
        </div>
        <a href="{{ route('radar') }}" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 text-center">Detail Tiket</a>
    </div>
    @endif

    <!-- Grafik Tren & Pengingat Hari Ini -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h2 class="text-sm font-bold text-slate-800 mb-4">Grafik Tren Kesehatan & Indeks Stres (7 Hari Terakhir)</h2>
            <canvas id="healthChart" height="120"></canvas>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-800">Pengingat Program AI</h2>
                    <a href="{{ route('wellness') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Kelola</a>
                </div>
                @if($activeProgram)
                    <p class="text-xs font-semibold text-indigo-700 mb-3">{{ $activeProgram->title }}</p>
                    <div class="space-y-2.5">
                        @foreach(array_slice($activeProgram->ai_timeline, 0, 4) as $idx => $step)
                        <div class="p-3 rounded-xl border {{ ($step['done'] ?? false) ? 'bg-slate-50 border-slate-200 opacity-60' : 'bg-indigo-50/40 border-indigo-200' }} flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[11px] font-bold text-indigo-700">Hari {{ $step['day'] }} • Pukul {{ $step['time'] }}</span>
                                <p class="text-xs text-slate-800 font-medium mt-0.5 {{ ($step['done'] ?? false) ? 'line-through' : '' }}">{{ $step['activity'] }}</p>
                            </div>
                            <form action="{{ route('wellness.toggle', [$activeProgram->id, $idx]) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="p-1.5 rounded-lg border {{ ($step['done'] ?? false) ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 text-slate-400 hover:border-indigo-600' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada program aktif. Buat program baru dengan bantuan AI.</p>
                @endif
            </div>
            <a href="{{ route('scanner') }}" class="mt-5 w-full py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold text-center hover:bg-indigo-700 transition">
                Mulai Scan Makanan / Gejala Sekarang
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('healthChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($trackers->pluck('log_date')) !!},
        datasets: [
            {
                label: 'Gula Darah (mg/dL)',
                data: {!! json_encode($trackers->pluck('blood_sugar')) !!},
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.35,
                fill: true
            },
            {
                label: 'Indeks Stres (x10)',
                data: {!! json_encode($trackers->map(fn($t) => $t->stress_score * 10)) !!},
                borderColor: '#F59E0B',
                borderDash: [5, 5],
                tension: 0.35
            }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush