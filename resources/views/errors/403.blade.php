<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Ditolak — GeoAttend</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-white flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center shadow-xl">
            <i data-lucide="shield-alert" class="w-10 h-10"></i>
        </div>
        <div>
            <h1 class="text-6xl font-extrabold text-amber-400">403</h1>
            <h2 class="text-2xl font-bold mt-2">Akses Ditolak</h2>
            <p class="text-sm text-slate-400 mt-2">{{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman atau aksi ini.' }}</p>
        </div>
        <div class="pt-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all shadow-lg shadow-indigo-600/30">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
