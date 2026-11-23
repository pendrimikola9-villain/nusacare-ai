<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NusaCare AI') - Kesehatan Preventif Berbasis AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Navigasi -->
        <aside class="w-full md:w-64 bg-slate-900 text-slate-200 flex-shrink-0 flex flex-col justify-between">
            <div>
                <div class="p-6 flex items-center gap-3 border-b border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-white text-lg tracking-tight block">NusaCare <span class="text-indigo-400">AI</span></span>
                        <span class="text-xs text-indigo-400">Preventive Health Hub</span>
                    </div>
                </div>

                <nav class="p-4 space-y-1.5 text-sm">
                    @if(auth()->user()->role === 'warga')
                        <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu Utama Warga</p>
                        
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                            </svg>
                            Dashboard Kesehatan
                        </a>

                        <a href="{{ route('scanner') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('scanner') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10"/>
                            </svg>
                            AI Nutri & Triase Scan
                        </a>

                        <a href="{{ route('wellness') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('wellness') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                            Program & Pengingat AI
                        </a>

                        <a href="{{ route('tracker') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('tracker') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            Health & Mind Tracker
                        </a>

                        <a href="{{ route('radar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('radar') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.418 0-8-5.373-8-10a8 8 0 1116 0c0 4.627-3.582 10-8 10z"/><circle cx="12" cy="11" r="3"/>
                            </svg>
                            Peta Radar & Rujukan
                        </a>

                      <a href="{{ route('konsultasi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('konsultasi') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            Konsultasi AI
                        </a>

                        <a href="{{ route('profil') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('profil') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                            Profil & Kartu Sehat
                        </a>
                        
                    @else
                        <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Portal Tenaga Medis</p>
                        <a href="{{ route('medis.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('medis.*') ? 'bg-indigo-600 text-white font-medium shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                            <svg class="w-4 h-4 opacity-85" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Antrean Rujukan AI
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Profil Pengguna & Logout -->
            <div class="p-4 border-t border-slate-800 bg-slate-900/60">
                <div class="mb-3 px-2">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <span class="inline-block mt-1 text-[11px] px-2.5 py-0.5 rounded-full bg-indigo-950/90 text-indigo-300 border border-indigo-700/70">
                        {{ auth()->user()->health_category }} • {{ auth()->user()->kelurahan }}
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-rose-400 hover:bg-slate-800 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar Akun
                    </button>
                </form>
            </div>
        </aside>

        <!-- Konten Utama -->
        <main class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <h1 class="text-lg font-bold text-slate-800">@yield('header_title', 'Dashboard')</h1>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                        <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                        Gemini Multimodal AI Aktif
                    </span>
                </div>
            </header>

            <div class="p-6 flex-1">
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>