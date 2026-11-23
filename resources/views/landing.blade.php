<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NusaCare AI - Sistem Triase Preventif Keluarga & Radar Kesehatan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Subtle Light Medical Grid Background */
        .bg-medical-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(79, 70, 229, 0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(79, 70, 229, 0.045) 1px, transparent 1px);
        }

        /* Floating Soft Blobs */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-18px) scale(1.05); }
        }
        .animate-float-slow { animation: floatSlow 8s ease-in-out infinite; }
        .animate-float-delayed { animation: floatSlow 10s ease-in-out 3s infinite; }

        /* ECG Heartbeat Line Animation */
        @keyframes dashECG {
            0% { stroke-dashoffset: 600; }
            100% { stroke-dashoffset: 0; }
        }
        .ecg-path {
            stroke-dasharray: 600;
            stroke-dashoffset: 600;
            animation: dashECG 3s linear infinite;
        }

        /* Stacked Cards Sharp Crisp Rendering (Tanpa Blur Rasterisasi 3D) */
        .holo-stage {
            position: relative;
        }
        .holo-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
            user-select: none;
            text-rendering: optimizeLegibility;
        }

        /* Active Center Card - Crisp Solid White + Indigo Border */
        .holo-card.is-center {
            background: #ffffff;
            border: 2px solid #4f46e5;
            box-shadow:
                0 22px 48px -12px rgba(79, 70, 229, 0.24),
                0 8px 20px -6px rgba(15, 23, 42, 0.08);
            cursor: pointer;
        }

        /* Side Cards in Stack - Crisp Solid Surface */
        .holo-card.is-side {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            box-shadow: 0 12px 28px -10px rgba(15, 23, 42, 0.14);
            cursor: pointer;
        }
        .holo-card.is-side:hover {
            border-color: #6366f1;
            background: #ffffff;
        }

        /* Subtle Holographic Light Sheen */
        .holo-shimmer {
            background: linear-gradient(
                115deg,
                transparent 25%,
                rgba(79, 70, 229, 0.07) 45%,
                rgba(56, 189, 248, 0.09) 55%,
                transparent 75%
            );
            background-size: 220% 220%;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        /* Scroll Reveal */
        .reveal-up {
            opacity: 0;
            transform: translateY(22px);
            transition: all 0.65s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .reveal-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-slate-50 bg-medical-grid text-slate-800 relative selection:bg-indigo-600 selection:text-white">

    <!-- Soft Ambient Indigo Blobs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-28 -left-24 w-[420px] h-[420px] rounded-full bg-indigo-400/15 blur-[110px] animate-float-slow"></div>
        <div class="absolute top-1/3 -right-28 w-[460px] h-[460px] rounded-full bg-blue-400/15 blur-[120px] animate-float-delayed"></div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="#" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-indigo-600/10 border border-indigo-600/30 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="font-extrabold text-xl text-slate-900">NusaCare <span class="text-indigo-600">AI</span></span>
            </a>

            <div class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                <a href="#showcase-3d" class="hover:text-indigo-600 transition">Fitur Utama</a>
                <a href="#alur-sistem" class="hover:text-indigo-600 transition">Cara Kerja</a>
                <a href="#demo-juri" class="hover:text-indigo-600 transition">Demo 1-Klik</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-indigo-600 transition">Masuk</a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition">Daftar Gratis</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-6xl mx-auto px-6 pt-12 pb-6 md:pt-16 md:pb-8 text-center">
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-bold mb-5 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-ping"></span>
            Subtema Kesehatan Masyarakat • IT RUSH 2026
        </span>

        <h1 class="text-4xl md:text-5xl lg:text-[54px] font-extrabold text-slate-900 leading-[1.14] max-w-4xl mx-auto mb-5">
            Cegah Sebelum Sakit dengan Asisten Kesehatan <span class="text-indigo-600">Multimodal AI</span>.
        </h1>

        <p class="text-slate-600 text-base md:text-lg max-w-2xl mx-auto mb-6 leading-relaxed">
            Satu ekosistem terpadu untuk memindai nutrisi makanan lewat foto, skrining gejala & ibu hamil, merancang timeline olahraga dengan pengingat cerdas, hingga memantau peta wabah lokal yang terhubung langsung ke Puskesmas.
        </p>

        <!-- Animasi Detak Jantung -->
        <div class="max-w-sm mx-auto mb-7">
            <svg viewBox="0 0 400 40" class="w-full h-8 overflow-visible">
                <path d="M0 20 L125 20 L140 20 L150 6 L163 35 L177 3 L190 31 L200 20 L275 20 L400 20"
                      fill="none" stroke="rgba(79, 70, 229, 0.15)" stroke-width="2" />
                <path d="M0 20 L125 20 L140 20 L150 6 L163 35 L177 3 L190 31 L200 20 L275 20 L400 20"
                      fill="none" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" class="ecg-path" />
            </svg>
        </div>

        <!-- Tombol Demo Cepat -->
        <div class="flex flex-wrap justify-center gap-3.5">
            <form action="{{ route('demo.login', 'warga') }}" method="POST">
                @csrf
                <button type="submit" class="group inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Coba Demo Warga (1-Klik)
                </button>
            </form>
            <form action="{{ route('demo.login', 'medis') }}" method="POST">
                @csrf
                <button type="submit" class="group inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-indigo-950 shadow-md transition">
                    <svg class="w-4 h-4 text-indigo-300 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Demo Portal Tenaga Medis
                </button>
            </form>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- BIG INTERACTIVE CARD CAROUSEL (CRISP HIGH-DPI TEXT & SHARP CARDS)     -->
    <!-- ===================================================================== -->
    <section id="showcase-3d" class="max-w-6xl mx-auto px-4 sm:px-6 py-8 md:py-12">
        <div class="flex flex-col items-center">
            <!-- Stage Tumpukan Kartu Tajam -->
            <div id="holoStage" class="holo-stage relative w-full h-[290px] sm:h-[310px] md:h-[330px] flex items-center justify-center">
                <!-- Kartu dirender dinamis oleh JavaScript -->
            </div>

            <!-- Kontrol Navigasi & Keterangan Bawah -->
            <div class="mt-3 flex flex-col items-center gap-3">
                <div class="flex items-center gap-4">
                    <button type="button" id="prevHoloBtn" aria-label="Kartu Sebelumnya" class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 shadow-sm flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div id="holoDots" class="flex items-center gap-2"></div>

                    <button type="button" id="nextHoloBtn" aria-label="Kartu Berikutnya" class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 shadow-sm flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <p class="text-xs sm:text-sm text-slate-500 text-center">
                    Klik <span class="text-indigo-600 font-bold">card utama</span> untuk membuka detail modul & simulasi, atau klik kartu samping untuk memutar
                </p>
            </div>
        </div>
    </section>

    <!-- Bar Statistik Interaktif -->
    <section class="max-w-6xl mx-auto px-6 py-6 reveal-up">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-center p-2">
                <p class="text-2xl sm:text-3xl font-extrabold text-indigo-600"><span class="counter" data-target="6">0</span> Modul</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Ekosistem Kesehatan Terpadu</p>
            </div>
            <div class="text-center p-2">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900"><<span class="counter" data-target="3">0</span> Detik</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Analisis Foto & Gejala AI</p>
            </div>
            <div class="text-center p-2">
                <p class="text-2xl sm:text-3xl font-extrabold text-indigo-600"><span class="counter" data-target="3">0</span> Modalitas</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Foto Kamera, Suara & Teks</p>
            </div>
            <div class="text-center p-2">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900"><span class="counter" data-target="100">0</span>%</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Terhubung ke Puskesmas</p>
            </div>
        </div>
    </section>

    <!-- Alur Ekosistem Pencegahan -->
    <section id="alur-sistem" class="max-w-6xl mx-auto px-6 py-14 reveal-up">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Alur Kerja Terintegrasi</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1.5">Dari Deteksi Mandiri Hingga Rujukan Puskesmas</h2>
            <p class="text-sm text-slate-600 mt-2">Membantu keluarga mencegah risiko penyakit kronis dan wabah lokal secara aktif setiap hari.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-600/5 transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">1. Pindai Gizi & Triase Gejala</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Foto label gizi makanan atau keluhan fisik Anda. AI mengevaluasi kadar gula, natrium, dan risiko berdasarkan riwayat diabetes, hipertensi, atau kehamilan.
                </p>
            </div>

            <div class="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-600/5 transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18M9 15l2 2 4-4"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">2. Program Sehat & Smart Reminder</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Dapatkan jadwal olahraga dan menu pangan lokal otomatis lengkap dengan tombol selesai dan ekspor pengingat langsung ke kalender ponsel (`.ics`).
                </p>
            </div>

            <div class="group bg-white p-6 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-600/5 transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.418 0-8-5.373-8-10a8 8 0 1116 0c0 4.627-3.582 10-8 10z"/><circle cx="12" cy="11" r="3"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">3. Radar Wabah & Rujukan Medis</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Pantau peta klaster DBD/ISPA kelurahan secara real-time sekaligus kirim tiket rujukan digital otomatis ke dashboard Tenaga Medis Puskesmas.
                </p>
            </div>
        </div>
    </section>

    <!-- Banner Akses Demo Juri -->
    <section id="demo-juri" class="max-w-6xl mx-auto px-6 pb-16 reveal-up">
        <div class="p-7 sm:p-9 rounded-2xl bg-indigo-950 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <span class="inline-block px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-bold">
                    Akses Cepat Evaluasi IT RUSH 2026
                </span>
                <h3 class="text-2xl font-extrabold">Coba Langsung Tanpa Perlu Daftar Manual</h3>
                <p class="text-sm text-indigo-200 max-w-xl">
                    Masuk dalam 1 klik sebagai <strong>Warga</strong> untuk mencoba seluruh fitur AI, atau sebagai <strong>Tenaga Medis Puskesmas</strong> untuk memantau tiket rujukan warga.
                </p>
            </div>
            <div class="flex flex-wrap justify-center gap-3 shrink-0">
                <form action="{{ route('demo.login', 'warga') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg transition">
                        Demo Warga →
                    </button>
                </form>
                <form action="{{ route('demo.login', 'medis') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-bold text-sm shadow-lg transition">
                        Demo Tenaga Medis →
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        <p>© 2026 <span class="font-bold text-slate-800">NusaCare AI</span> • Sistem Triase Preventif Keluarga & Radar Kesehatan • <span class="text-indigo-600 font-semibold">IT RUSH 2026</span></p>
    </footer>

    <!-- Modal Detail saat Card Utama Diklik -->
    <div id="holoModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="relative w-full max-w-xl rounded-2xl bg-white border-2 border-indigo-600 p-6 sm:p-8 shadow-2xl text-slate-800">
            <button type="button" id="closeHoloModal" class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                ✕
            </button>
            <span id="modalRegion" class="text-xs font-extrabold uppercase tracking-wider text-indigo-600"></span>
            <h3 id="modalTitle" class="text-2xl font-extrabold text-slate-900 mt-1"></h3>
            <p id="modalMeta" class="text-xs font-semibold text-slate-500 mt-1 mb-4"></p>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 leading-relaxed mb-6" id="modalDesc"></div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" id="closeHoloModalBtn" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition">
                    Tutup
                </button>
                <form id="modalDemoForm" action="{{ route('demo.login', 'warga') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                        Coba Fitur Ini Sekarang →
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const holoCardsData = [
            {
                region: 'NUTRISCAN MULTIMODAL',
                title: 'Pemindai Gizi Makanan & Profil Risiko',
                meta: 'Gemini AI Vision • Deteksi Gula, Natrium & Ibu Hamil',
                badge: 'AI VISION',
                role: 'warga',
                icon: `<svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10"/></svg>`,
                description: 'Deteksi kadar gula, natrium, & kecocokan gizi untuk penderita diabetes, hipertensi, maupun ibu hamil hanya dari foto makanan kemasan atau menu harian.'
            },
            {
                region: 'AI PROGRAM & REMINDER',
                title: 'Timeline Olahraga & Menu Sehat Otomatis',
                meta: 'Jadwal Terstruktur • Ekspor Kalender (.ics)',
                badge: 'SMART PLAN',
                role: 'warga',
                icon: `<svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18M9 15l2 2 4-4"/></svg>`,
                description: 'Input target kesehatan Anda — AI menyusun timeline olahraga & pola makan sehat harian lengkap dengan tombol selesai serta ekspor pengingat kalender (.ics).'
            },
            {
                region: 'RADAR WABAH & RUJUKAN',
                title: 'Peta Klaster Kelurahan & Tiket Puskesmas',
                meta: 'Geo-Monitoring DBD/ISPA • Sinkronisasi Medis',
                badge: 'LIVE RADAR',
                role: 'warga',
                icon: `<svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.418 0-8-5.373-8-10a8 8 0 1116 0c0 4.627-3.582 10-8 10z"/><circle cx="12" cy="11" r="3"/></svg>`,
                description: 'Pemetaan klaster kasus DBD/ISPA di sekitar kelurahan dan pengiriman otomatis tiket rujukan digital ke dashboard Tenaga Medis Puskesmas.'
            },
            {
                region: 'HEALTH & MIND COACH AI',
                title: 'Tracker Vital Harian & Tantangan Anti-Stres',
                meta: 'Tensi, Gula Darah & 3 Misi Harian Adaptif',
                badge: 'AI COACH',
                role: 'warga',
                icon: `<svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`,
                description: 'Catat tekanan darah, gula darah, berat badan, dan tingkat stres. AI merancang 3 tantangan kesehatan personal (Pagi, Siang, Malam) lengkap dengan sistem poin.'
            },
            {
                region: 'PORTAL TENAGA MEDIS',
                title: 'Dashboard Verifikasi Triase & Tindak Lanjut',
                meta: 'Khusus Dokter & Petugas Puskesmas',
                badge: 'FASKES LINK',
                role: 'medis',
                icon: `<svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`,
                description: 'Memudahkan petugas kesehatan memantau laporan skrining berisiko tinggi dari warga sekitar serta memverifikasi status rujukan pasien secara cepat.'
            }
        ];

        let activeIndex = 0;
        const stage = document.getElementById('holoStage');
        const dotsContainer = document.getElementById('holoDots');

        function renderHoloCarousel() {
            stage.innerHTML = '';
            dotsContainer.innerHTML = '';
            const total = holoCardsData.length;

            holoCardsData.forEach((item, index) => {
                let offset = (index - activeIndex + total) % total;
                if (offset > Math.floor(total / 2)) offset -= total;

                const isCenter = offset === 0;
                const isVisible = Math.abs(offset) <= 2;

                const card = document.createElement('div');
                card.className = `holo-card rounded-3xl overflow-hidden ${isCenter ? 'is-center' : 'is-side'}`;

                card.style.width = 'min(90vw, 650px)';
                card.style.minHeight = '225px';

                // Posisi 2D presisi tinggi agar font tetap 100% tajam tanpa blur sub-pixel 3D
                const offsetX = Math.round(offset * 165);
                const scale = isCenter ? 1 : Number(Math.max(0.78, 1 - Math.abs(offset) * 0.11).toFixed(2));

                card.style.transform = `translate(calc(-50% + ${offsetX}px), -50%) scale(${scale})`;
                card.style.zIndex = 30 - Math.abs(offset) * 5;
                card.style.opacity = isVisible ? (isCenter ? '1' : Math.max(0.45, 0.82 - Math.abs(offset) * 0.2)) : '0';
                card.style.pointerEvents = isVisible ? 'auto' : 'none';

                card.innerHTML = `
                    <div class="holo-shimmer absolute inset-0 opacity-75"></div>
                    <div class="relative z-10 p-5 sm:p-7 flex items-center gap-5 sm:gap-7 h-full">
                        <!-- Kotak Thumbnail Kiri -->
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col items-center justify-center shrink-0">
                            ${item.icon}
                            <span class="mt-2 px-2.5 py-0.5 rounded-md bg-indigo-600 text-[10px] font-bold text-white tracking-wider">
                                ${item.badge}
                            </span>
                        </div>

                        <!-- Konten Teks Kanan Tajam -->
                        <div class="min-w-0 flex-1 text-left">
                            <span class="block text-xs sm:text-sm font-extrabold uppercase tracking-wider text-indigo-600 mb-1">
                                ${item.region}
                            </span>
                            <h3 class="text-xl sm:text-2xl md:text-[26px] font-extrabold text-slate-900 leading-snug">
                                ${item.title}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-2.5 font-medium">
                                ${item.meta}
                            </p>
                        </div>
                    </div>
                `;

                if (isCenter) {
                    card.addEventListener('mousemove', (e) => {
                        const rect = card.getBoundingClientRect();
                        const x = (e.clientX - rect.left) / rect.width - 0.5;
                        const y = (e.clientY - rect.top) / rect.height - 0.5;
                        const moveX = Math.round(x * 10);
                        const moveY = Math.round(y * 8);
                        card.style.transform = `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(1.015)`;
                        const shimmer = card.querySelector('.holo-shimmer');
                        if (shimmer) shimmer.style.backgroundPosition = `${50 + x * 90}% ${50 + y * 90}%`;
                    });

                    card.addEventListener('mouseleave', () => {
                        card.style.transform = `translate(-50%, -50%) scale(1)`;
                    });

                    card.addEventListener('click', () => openHoloModal(item));
                } else {
                    card.addEventListener('click', () => {
                        activeIndex = index;
                        renderHoloCarousel();
                    });
                }

                stage.appendChild(card);

                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = `h-2 rounded-full transition-all ${isCenter ? 'w-7 bg-indigo-600' : 'w-2 bg-slate-300 hover:bg-indigo-300'}`;
                dot.addEventListener('click', () => {
                    activeIndex = index;
                    renderHoloCarousel();
                });
                dotsContainer.appendChild(dot);
            });
        }

        document.getElementById('prevHoloBtn').addEventListener('click', () => {
            activeIndex = (activeIndex - 1 + holoCardsData.length) % holoCardsData.length;
            renderHoloCarousel();
        });

        document.getElementById('nextHoloBtn').addEventListener('click', () => {
            activeIndex = (activeIndex + 1) % holoCardsData.length;
            renderHoloCarousel();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') document.getElementById('prevHoloBtn').click();
            if (e.key === 'ArrowRight') document.getElementById('nextHoloBtn').click();
            if (e.key === 'Escape') closeHoloModal();
        });

        let touchStartX = 0;
        stage.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
        stage.addEventListener('touchend', (e) => {
            const diff = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(diff) > 45) {
                if (diff > 0) document.getElementById('prevHoloBtn').click();
                else document.getElementById('nextHoloBtn').click();
            }
        }, { passive: true });

        const holoModal = document.getElementById('holoModal');
        function openHoloModal(item) {
            document.getElementById('modalRegion').textContent = item.region;
            document.getElementById('modalTitle').textContent = item.title;
            document.getElementById('modalMeta').textContent = item.meta;
            document.getElementById('modalDesc').textContent = item.description;
            const form = document.getElementById('modalDemoForm');
            form.action = item.role === 'medis'
                ? "{{ route('demo.login', 'medis') }}"
                : "{{ route('demo.login', 'warga') }}";
            holoModal.classList.remove('hidden');
            holoModal.classList.add('flex');
        }
        function closeHoloModal() {
            holoModal.classList.add('hidden');
            holoModal.classList.remove('flex');
        }
        document.getElementById('closeHoloModal').addEventListener('click', closeHoloModal);
        document.getElementById('closeHoloModalBtn').addEventListener('click', closeHoloModal);
        holoModal.addEventListener('click', (e) => { if (e.target === holoModal) closeHoloModal(); });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    entry.target.querySelectorAll('.counter').forEach(counter => {
                        if (counter.dataset.done) return;
                        counter.dataset.done = '1';
                        const target = +counter.dataset.target;
                        let current = 0;
                        const step = Math.max(1, Math.ceil(target / 30));
                        const timer = setInterval(() => {
                            current += step;
                            if (current >= target) {
                                counter.textContent = target;
                                clearInterval(timer);
                            } else {
                                counter.textContent = current;
                            }
                        }, 35);
                    });
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));

        renderHoloCarousel();
    </script>
</body>
</html>