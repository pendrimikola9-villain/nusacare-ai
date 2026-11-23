@extends('layouts.app')
@section('title', 'Dashboard Tenaga Medis')
@section('header_title', 'Portal Tenaga Medis — Antrean Rujukan Triase AI')

@section('content')
<div class="space-y-6">
    <!-- Ringkasan Antrean Rujukan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500">Total Tiket Rujukan Masuk</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $referrals->count() }}</p>
            <span class="inline-block mt-2 text-[11px] text-indigo-700 font-medium">Terintegrasi dari Portal Warga</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500">Menunggu Pemeriksaan</span>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $referrals->where('referral_status', 'menunggu')->count() }}</p>
            <span class="inline-block mt-2 text-[11px] text-slate-500">Prioritas tindak lanjut poli</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500">Kasus Prioritas (Waspada / Darurat)</span>
            <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $referrals->whereIn('risk_level', ['waspada', 'darurat'])->count() }}</p>
            <span class="inline-block mt-2 text-[11px] text-rose-600 font-medium">Butuh evaluasi cepat</span>
        </div>
    </div>

    <!-- Tabel Antrean Pasien -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Daftar Rujukan Digital Pasien</h2>
                <p class="text-xs text-slate-500">Diurutkan otomatis berdasarkan tingkat urgensi hasil skrining AI.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Kode Tiket</th>
                        <th class="py-3 px-3">Nama Pasien & Profil</th>
                        <th class="py-3 px-3">Kelurahan</th>
                        <th class="py-3 px-3">Poli Tujuan (AI)</th>
                        <th class="py-3 px-3">Urgensi</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($referrals as $ref)
                        @php
                            $badge = match($ref->risk_level) {
                                'darurat' => 'bg-rose-100 text-rose-800 border-rose-200',
                                'waspada' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $ref->referral_code }}</td>
                            <td class="py-3 px-3">
                                <p class="font-bold text-slate-800">{{ $ref->user->name }}</p>
                                <span class="text-[11px] text-slate-500">{{ $ref->user->health_category }}</span>
                            </td>
                            <td class="py-3 px-3">{{ $ref->user->kelurahan }}</td>
                            <td class="py-3 px-3 font-medium text-indigo-700">{{ $ref->ai_result['recommended_poli'] ?? 'Poli Umum' }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase border {{ $badge }}">
                                    {{ $ref->risk_level }}
                                </span>
                            </td>
                            <td class="py-3 px-3 uppercase font-semibold text-slate-600">{{ $ref->referral_status }}</td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('medis.detail', $ref->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Periksa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada tiket rujukan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection