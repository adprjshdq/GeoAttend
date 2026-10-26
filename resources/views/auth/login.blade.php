<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — GeoAttend</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600 text-white mb-4">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
            <h1 class="text-xl font-semibold text-white">GeoAttend</h1>
            <p class="text-sm text-slate-400 mt-1">Masuk untuk melanjutkan</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-sm">
            <div class="p-6">
                @if($errors->any())
                    <div class="mb-5 p-3 rounded-lg bg-rose-500/10 border border-rose-500/40 text-rose-300 text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span>Gagal masuk</span>
                        </div>
                        <ul class="mt-1.5 ml-6 list-disc space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 p-3 rounded-lg bg-blue-500/10 border border-blue-500/40 text-blue-300 text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-200 mb-1.5">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@geoattend.ac.id"
                            class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-200 mb-1.5">Kata sandi</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                placeholder="••••••••"
                                class="w-full px-3 py-2 pr-9 rounded-lg bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 px-2.5 flex items-center text-slate-400 hover:text-slate-200">
                                <i id="password-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500">
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit" class="w-full py-2 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk</span>
                    </button>
                </form>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} GeoAttend
        </p>
    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }
            if (window.refreshIcons) {
                window.refreshIcons();
            }
        }
    </script>
</body>
</html>
