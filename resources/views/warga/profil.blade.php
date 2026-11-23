@extends('layouts.app')
@section('title', 'Profil Medis & Kartu Sehat')
@section('header_title', 'Profil Medis & Kartu Sehat Keluarga')

@section('content')
<style>
    @media print {
        body * { visibility: hidden; }
        #printableHealthCard, #printableHealthCard * { visibility: visible; }
        #printableHealthCard {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
        }
        .no-print { display: none !important; }
    }
</style>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Kolom Kiri: Form Pengaturan Profil Medis -->
    <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit space-y-5 no-print">
        <div>
            <span class="inline-block px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-100 mb-1.5">Identitas Kesehatan</span>
            <h2 class="text-base font-bold text-slate-900">Pengaturan Profil Medis</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui kondisi kesehatan atau kelurahan domisili Anda untuk personalisasi otomatis pada seluruh fitur AI.</p>
        </div>

        <form action="{{ route('profil.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Kondisi Kesehatan</label>
                <select name="health_category" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @php
                        $categories = [
                            'Umum / Dewasa Sehat',
                            'Ibu Hamil (Trimester 1-3)',
                            'Diabetes / Kontrol Gula',
                            'Hipertensi / Rendah Garam',
                            'Lansia / Pemantauan Rutin',
                        ];
                    @endphp
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $user->health_category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kelurahan Domisili</label>
                <input type="text" name="kelurahan" value="{{ old('kelurahan', $user->kelurahan) }}" required placeholder="Contoh: Sungai Miai" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition">
                Simpan Perubahan Profil
            </button>
        </form>
    </div>

    <!-- Kolom Kanan: Kartu Sehat & Ringkasan Medis Siap Cetak -->
    <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between no-print">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Kartu Sehat Digital Keluarga</h2>
                <p class="text-xs text-slate-500">Ringkasan medis siap cetak untuk ditunjukkan saat pemeriksaan di Puskesmas.</p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
        </div>

        <!-- Area Kartu Sehat -->
        <div id="printableHealthCard" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-indigo-950 text-white px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-400/40 flex items-center justify-center text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-indigo-300 font-semibold">Kartu Ringkasan Medis Preventif</p>
                        <h3 class="text-base font-extrabold tracking-tight">NusaCare <span class="text-indigo-400">AI</span></h3>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-indigo-300 block">ID Pasien Terdaftar</span>
                    <span class="font-mono text-xs font-bold text-white">NSC-USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <!-- Identitas Utama -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pb-4 border-b border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Pasien</span>
                        <strong class="text-slate-900 text-sm">{{ $user->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Profil Kesehatan</span>
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold">{{ $user->health_category }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Wilayah Domisili</span>
                        <strong class="text-slate-800">Kel. {{ $user->kelurahan }}</strong>
                    </div>
                </div>

                <!-- Data Vital Terakhir -->
                <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5">Catatan Tanda Vital Terakhir</h4>
                    <div class="grid grid-cols-3 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 block">Tekanan Darah</span>
                            <strong class="text-slate-900 text-sm">{{ $latestTracker->blood_pressure ?? '120/80' }} <span class="text-[10px] font-normal">mmHg</span></strong>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 block">Gula Darah</span>
                            <strong class="text-slate-900 text-sm">{{ $latestTracker->blood_sugar ?? '-' }} <span class="text-[10px] font-normal">mg/dL</span></strong>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 block">Berat / Stres</span>
                            <strong class="text-slate-900 text-sm">{{ $latestTracker->weight ?? '-' }} kg • {{ $latestTracker->stress_score ?? 3 }}/10</strong>
                        </div>
                    </div>
                </div>

                <!-- Pantangan Makanan dari NutriScan -->
                <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Catatan Pembatasan Konsumsi (Hasil NutriScan AI)</h4>
                    @if($avoidFoods->count() > 0)
                        <ul class="space-y-1.5 text-xs">
                            @foreach($avoidFoods as $food)
                                <li class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200/70 text-slate-800 flex items-center justify-between">
                                    <span>{{ Str::limit($food->input_summary, 65) }}</span>
                                    <span class="text-[10px] font-bold uppercase text-amber-800 px-2 py-0.5 rounded bg-amber-100">{{ $food->risk_level }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-slate-500 italic">Belum ada catatan makanan berisiko tinggi pada riwayat pemindaian.</p>
                    @endif
                </div>

                <!-- Tiket Rujukan Aktif -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-400 block">Tiket Rujukan Faskes Aktif</span>
                        @if($activeReferral)
                            <strong class="font-mono text-indigo-700 text-sm">{{ $activeReferral->referral_code }}</strong>
                            <span class="text-slate-600 ml-1">({{ $activeReferral->ai_result['recommended_poli'] ?? 'Poli Umum' }})</span>
                        @else
                            <span class="text-slate-500">Tidak ada rujukan aktif saat ini</span>
                        @endif
                    </div>
                    <span class="text-[10px] text-slate-400">Dicetak: {{ now()->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection