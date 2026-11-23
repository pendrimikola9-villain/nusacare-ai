<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — NusaCare AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-5">
        <div class="text-center space-y-1">
            <a href="/" class="inline-flex items-center gap-2 text-slate-900 font-extrabold text-xl">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span>NusaCare <span class="text-indigo-600">AI</span></span>
            </a>
            <h1 class="text-lg font-bold text-slate-900 pt-2">Buat Akun Kesehatan Warga</h1>
            <p class="text-xs text-slate-500">Personalisasi analisis gizi & triase otomatis sesuai kondisi Anda</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Siti Aminah" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Profil Kesehatan</label>
                    <select name="health_category" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-medium bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="Umum / Dewasa Sehat">Umum / Dewasa Sehat</option>
                        <option value="Ibu Hamil (Trimester 1-3)">Ibu Hamil (Trimester 1-3)</option>
                        <option value="Diabetes / Kontrol Gula">Diabetes / Kontrol Gula</option>
                        <option value="Hipertensi / Rendah Garam">Hipertensi / Rendah Garam</option>
                        <option value="Lansia / Pemantauan Rutin">Lansia / Pemantauan Rutin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelurahan Domisili</label>
                    <input type="text" name="kelurahan" value="{{ old('kelurahan', 'Sungai Miai') }}" required placeholder="Nama Kelurahan" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                    <input type="password" name="password" required placeholder="Min. 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Sandi</label>
                    <input type="password" name="password_confirmation" required placeholder="Konfirmasi sandi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition mt-2">
                Daftar & Masuk Dashboard
            </button>
        </form>

        <p class="text-center text-xs text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-indigo-700 hover:underline">Masuk di sini</a>
        </p>
    </div>
</body>
</html>