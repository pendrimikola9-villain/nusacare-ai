@extends('layouts.app')
@section('title', 'Detail Pemeriksaan Pasien')
@section('header_title', 'Detail Tiket Rujukan & Rekam Progres AI')

@section('content')
<div class="space-y-6">
    <a href="{{ route('medis.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 hover:underline">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Antrean Rujukan
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Ringkasan Triase AI & Update Status -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-slate-100 text-slate-800">{{ $screening->referral_code }}</span>
                    <h2 class="text-lg font-bold text-slate-900 mt-2">{{ $screening->user->name }}</h2>
                    <p class="text-xs text-slate-500">Kategori: {{ $screening->user->health_category }} • Kelurahan {{ $screening->user->kelurahan }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                    {{ $screening->risk_level }}
                </span>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Keluhan / Input Pasien</h3>
                <p class="text-sm text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-100">{{ $screening->input_summary }}</p>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-1">Hasil Analisis Awal AI</h3>
                <p class="text-sm text-slate-800 leading-relaxed">{{ $screening->ai_result['summary'] ?? '-' }}</p>
                <p class="text-xs text-slate-600 mt-2"><strong>Saran Pertolongan Pertama yang Diberikan:</strong> {{ $screening->ai_result['first_aid'] ?? '-' }}</p>
            </div>

            <!-- Form Update Status Penanganan -->
            <form action="{{ route('medis.status', $screening->id) }}" method="POST" class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-3">
                @csrf @method('PATCH')
                <label class="text-xs font-bold text-slate-700">Perbarui Status Penanganan:</label>
                <select name="referral_status" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="menunggu" {{ $screening->referral_status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="diperiksa" {{ $screening->referral_status === 'diperiksa' ? 'selected' : '' }}>Sedang Diperiksa</option>
                    <option value="selesai" {{ $screening->referral_status === 'selesai' ? 'selected' : '' }}>Selesai Ditangani</option>
                </select>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition">
                    Simpan Status
                </button>
            </form>
        </div>

        <!-- Riwayat Health Tracker Pasien -->
        <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Riwayat Pemantauan Vital Pasien</h3>
            <p class="text-xs text-slate-500 mb-4">Data log harian pasien sebelum datang ke faskes.</p>

            <div class="space-y-2.5">
                @forelse($patientTrackers->reverse() as $pt)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <p class="font-bold text-slate-800">{{ $pt->log_date }}</p>
                        <p class="text-slate-500 mt-0.5">Tensi: <strong>{{ $pt->blood_pressure ?? '-' }}</strong> • Gula: <strong>{{ $pt->blood_sugar ?? '-' }}</strong></p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full font-bold {{ $pt->stress_score > 5 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        Stres {{ $pt->stress_score }}/10
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400">Belum ada data log harian.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection