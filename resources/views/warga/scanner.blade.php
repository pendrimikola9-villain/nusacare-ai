@extends('layouts.app')
@section('title', 'AI Nutri & Triase Scanner')
@section('header_title', 'Multimodal Health & Nutri Scanner')

@section('content')
<div class="space-y-6">
    <!-- Grafik Tracker Frekuensi Makanan & Item yang Di-Scan -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Grafik Riwayat Makanan & Konsumsi Terpantau</h2>
                <p class="text-xs text-slate-500">Menampilkan frekuensi setiap menu makanan yang Anda scan dan konsumsi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Jenis (Makanan vs Semua) -->
                <select id="typeFilter" onchange="renderFoodChart()" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="nutriscan" selected>Makanan Dimakan (NutriScan)</option>
                    <option value="all">Semua Pemindaian</option>
                    <option value="triase">Keluhan / Triase</option>
                </select>

                <!-- Filter Waktu -->
                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                    <button type="button" onclick="setPeriod('week', this)" class="period-btn px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition">Minggu Ini</button>
                    <button type="button" onclick="setPeriod('month', this)" class="period-btn px-3 py-1 rounded-lg bg-indigo-600 text-white shadow-xs transition">Bulan Ini</button>
                    <button type="button" onclick="setPeriod('year', this)" class="period-btn px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition">Tahun Ini</button>
                </div>
            </div>
        </div>

        <div class="relative">
            <canvas id="scanAnalyticsChart" height="95"></canvas>
            <p id="emptyChartMsg" class="hidden text-center text-xs text-slate-400 py-8">Belum ada data makanan pada periode & filter ini.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Input Multimodal -->
        <div class="lg:col-span-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
            <h2 class="text-base font-bold text-slate-900 mb-1">Analisis Kesehatan & Gizi Berbasis AI</h2>
            <p class="text-xs text-slate-500 mb-5">Disesuaikan otomatis dengan profil Anda: <strong class="text-indigo-700">{{ auth()->user()->health_category }}</strong></p>

            <form action="{{ route('scanner.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Jenis Pemindaian</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="nutriscan" checked class="peer sr-only">
                            <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 transition">
                                <p class="text-xs font-bold text-slate-900">NutriScan Makanan</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Cek label gizi, gula, garam & kalori</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="triase" class="peer sr-only">
                            <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 transition">
                                <p class="text-xs font-bold text-slate-900">Triase Gejala & Maternal</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Skrining keluhan & rujukan faskes</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Foto (Kemasan Makanan / Menu / Kondisi Fisik — Opsional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl p-2">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Makanan atau Keluhan Gejala</label>
                    <textarea name="description" rows="4" required placeholder="Contoh NutriScan: Mie ayam atau minuman kemasan teh manis 350ml
Contoh Triase: Demam sejak 2 hari lalu disertai pusing dan nyeri sendi..." class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Proses Analisis Multimodal AI
                </button>
            </form>
        </div>

        <!-- Hasil Analisis Terbaru + Tombol Arsip/Unduh & Hapus -->
        <div class="lg:col-span-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Arsip & Hasil Pemindaian ({{ $recentScans->count() }})</h3>
                <span class="text-[11px] text-slate-400">Tersimpan otomatis</span>
            </div>

            @forelse($recentScans as $scan)
                @php
                    $badgeColor = match($scan->risk_level) {
                        'aman' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'darurat' => 'bg-rose-100 text-rose-800 border-rose-200',
                        default => 'bg-amber-100 text-amber-800 border-amber-200',
                    };
                @endphp
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">{{ $scan->type }}</span>
                            <span class="text-[11px] text-slate-400">• {{ $scan->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeColor }}">
                            {{ $scan->ai_result['status_label'] ?? strtoupper($scan->risk_level) }}
                        </span>
                    </div>

                    @if($scan->image_path)
                        <img src="{{ asset('storage/' . $scan->image_path) }}" alt="Scan" class="w-full h-36 object-cover rounded-xl border border-slate-100">
                    @endif

                    <p class="text-xs text-slate-500 italic">"{{ Str::limit($scan->input_summary, 100) }}"</p>
                    <p class="text-xs text-slate-800 leading-relaxed">
                        {{ $scan->ai_result['summary'] ?? $scan->ai_result['message'] ?? 'Analisis nutrisi dan kecocokan profil kesehatan telah diproses.' }}
                    </p>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700">
                        <strong class="text-indigo-700">Saran Tindakan:</strong>
                        {{ $scan->ai_result['first_aid'] ?? 'Perhatikan porsi konsumsi, kurangi tambahan saus/garam berlebih, dan imbangi dengan air putih serta sayuran.' }}
                    </div>

                    <!-- Baris Aksi: Unduh Arsip & Hapus -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('scanner.download', $scan->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 text-xs font-semibold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Arsip Laporan
                        </a>

                        <form action="{{ route('scanner.delete', $scan->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat hasil analisis ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                    Belum ada arsip pemindaian. Silakan uji form di samping.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rawChartData = @json($chartData);
let currentPeriod = 'month';

const scanCtx = document.getElementById('scanAnalyticsChart');
const scanChart = new Chart(scanCtx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Jumlah Konsumsi / Scan (Kali)',
            data: [],
            backgroundColor: '#4F46E5',
            hoverBackgroundColor: '#4338CA',
            borderRadius: 8,
            barThickness: 42
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.raw} kali dipindai`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, precision: 0 }
            }
        }
    }
});

function renderFoodChart() {
    const type = document.getElementById('typeFilter').value;
    const datasetObj = (rawChartData[currentPeriod] && rawChartData[currentPeriod][type]) ? rawChartData[currentPeriod][type] : {};
    const labels = Object.keys(datasetObj);
    const values = Object.values(datasetObj);

    const canvas = document.getElementById('scanAnalyticsChart');
    const emptyMsg = document.getElementById('emptyChartMsg');

    if (labels.length === 0) {
        canvas.classList.add('hidden');
        emptyMsg.classList.remove('hidden');
    } else {
        canvas.classList.remove('hidden');
        emptyMsg.classList.add('hidden');
        scanChart.data.labels = labels;
        scanChart.data.datasets[0].data = values;
        scanChart.update();
    }
}

function setPeriod(period, btn) {
    currentPeriod = period;
    document.querySelectorAll('.period-btn').forEach(el => {
        el.classList.remove('bg-indigo-600', 'text-white', 'shadow-xs');
        el.classList.add('text-slate-600');
    });
    btn.classList.add('bg-indigo-600', 'text-white', 'shadow-xs');
    btn.classList.remove('text-slate-600');
    renderFoodChart();
}

renderFoodChart();
</script>
@endpush