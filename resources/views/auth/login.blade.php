<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — NusaCare AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div class="text-center space-y-1">
            <a href="/" class="inline-flex items-center gap-2 text-slate-900 font-extrabold text-xl">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span>NusaCare <span class="text-indigo-600">AI</span></span>
            </a>
            <h1 class="text-lg font-bold text-slate-900 pt-2">Masuk ke Akun Anda</h1>
            <p class="text-xs text-slate-500">Akses pemantauan gizi, triase kesehatan & portal rujukan faskes</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition">
                Masuk Sekarang
            </button>
        </form>

        <!-- Akses Cepat 1-Klik Demo untuk Juri -->
        <div class="pt-4 border-t border-slate-100 space-y-2.5">
            <p class="text-[11px] font-semibold text-center text-slate-400 uppercase tracking-wider">Akses Cepat Demo Pengujian</p>
            <div class="grid grid-cols-2 gap-2.5">
                <form action="{{ route('demo.login', 'warga') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-indigo-50 text-indigo-800 border border-indigo-200 text-xs font-bold hover:bg-indigo-100 transition">
                        Demo Warga
                    </button>
                </form>
                <form action="{{ route('demo.login', 'medis') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-100 text-slate-800 border border-slate-200 text-xs font-bold hover:bg-slate-200 transition">
                        Demo Tenaga Medis
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500">
            Belum memiliki akun? <a href="{{ route('register') }}" class="font-bold text-indigo-700 hover:underline">Daftar Warga Baru</a>
        </p>
    </div>
</body>
</html>