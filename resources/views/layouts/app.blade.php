<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — GeoAttend Smart Attendance</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans text-slate-800 antialiased flex flex-col md:flex-row bg-slate-50">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs hidden md:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white text-slate-700 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:inset-auto shrink-0 border-r border-slate-200">
        <!-- Brand Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-200">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-slate-900 tracking-tight">
                        GeoAttend
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Smart Attendance</p>
                </div>
            </a>
            <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- User Role Badge Summary -->
        <div class="px-6 py-4 mx-4 my-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-semibold flex items-center justify-center text-sm">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <h4 class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()->name }}</h4>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ Auth::user()->isAdmin() ? 'bg-amber-50 text-amber-700 border border-amber-200' : (Auth::user()->isLecturer() ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                        {{ Auth::user()->role_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto">
            <!-- Universal Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'text-indigo-600' : '' }}"></i>
                Dashboard
            </a>

            @if(Auth::user()->isAdmin())
                <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Manajemen Akademik</div>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-indigo-600' : '' }}"></i>
                    Data Pengguna
                </a>
                <a href="{{ route('admin.students.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="graduation-cap" class="w-4 h-4 {{ request()->routeIs('admin.students.*') ? 'text-indigo-600' : '' }}"></i>
                    Data Mahasiswa
                </a>
                <a href="{{ route('admin.lecturers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.lecturers.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="briefcase" class="w-4 h-4 {{ request()->routeIs('admin.lecturers.*') ? 'text-indigo-600' : '' }}"></i>
                    Data Dosen
                </a>
                <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.courses.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="book-open" class="w-4 h-4 {{ request()->routeIs('admin.courses.*') ? 'text-indigo-600' : '' }}"></i>
                    Mata Kuliah & Kelas
                </a>
                <a href="{{ route('admin.schedules.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.schedules.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="calendar" class="w-4 h-4 {{ request()->routeIs('admin.schedules.*') ? 'text-indigo-600' : '' }}"></i>
                    Jadwal & Geofence
                </a>

                <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sistem & Audit</div>
                <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.audit_logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="shield-check" class="w-4 h-4 {{ request()->routeIs('admin.audit_logs.*') ? 'text-indigo-600' : '' }}"></i>
                    Audit Log Aktivitas
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="sliders" class="w-4 h-4 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-600' : '' }}"></i>
                    Pengaturan Sistem
                </a>
            @endif

            @if(Auth::user()->isLecturer() || Auth::user()->isAdmin())
                <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Perkuliahan Dosen</div>
                <a href="{{ route('lecturer.sessions.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('lecturer.sessions.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="scan-line" class="w-4 h-4 {{ request()->routeIs('lecturer.sessions.*') ? 'text-indigo-600' : '' }}"></i>
                    Sesi & Dynamic QR
                </a>
            @endif

            @if(Auth::user()->isStudent())
                <div class="pt-4 pb-1 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Presensi Mahasiswa</div>
                <a href="{{ route('student.history') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all {{ request()->routeIs('student.history') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i data-lucide="clock" class="w-4 h-4 {{ request()->routeIs('student.history') ? 'text-indigo-600' : '' }}"></i>
                    Riwayat Kehadiran
                </a>
            @endif
        </nav>

        <!-- Sidebar Footer & Logout -->
        <div class="p-4 border-t border-slate-200">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Header Navigation -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-900 tracking-tight">{{ $header ?? $title ?? 'GeoAttend' }}</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">{{ \App\Models\SystemSetting::get('campus_name', 'Universitas Perjuangan Tasikmalaya') }}</p>
                </div>
            </div>

            <!-- Header Right -->
            <div class="flex items-center gap-4">
                <!-- Clock WIB badge -->
                <div class="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span id="live-clock">--:--:-- WIB</span>
                </div>

                <!-- User Profile Dropdown Pill -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-500 font-medium">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto space-y-6">
            @if(session('success'))
                <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="flex items-center justify-between p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-medium">{{ session('info') }}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700 p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-2 font-semibold text-sm">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0"></i>
                        Terdapat kesalahan pada formulir yang Anda kirimkan:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 pl-8 text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Content Slot -->
            {{ $slot }}
        </main>

        <!-- Modern Footer -->
        <footer class="mt-auto py-6 px-8 border-t border-slate-200 bg-white text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} <strong>GeoAttend</strong> — Smart Attendance System with Geolocation, Dynamic QR & Selfie Verification. Zona Waktu: Asia/Jakarta (WIB).</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle mobile sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        // Live Clock (Asia/Jakarta WIB)
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour12: false
            }) + ' WIB';
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                clockEl.textContent = timeString;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>
</html>
