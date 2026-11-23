@extends('layouts.app')
@section('title', 'Peta Radar Wabah & Rujukan')
@section('header_title', 'Radar Kewaspadaan Wabah & Tiket Rujukan Digital')

@section('content')
<div class="space-y-6">
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

    <!-- Tabel Riwayat & Tiket Rujukan -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Riwayat Skrining & Tiket Rujukan Anda</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3">Ringkasan Input</th>
                        <th class="py-3 px-3">Hasil AI</th>
                        <th class="py-3 px-3">Kode Rujukan</th>
                        <th class="py-3 px-3">Status Faskes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($screenings as $item)
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush