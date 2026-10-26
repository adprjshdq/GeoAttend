<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic QR Code — {{ $session->schedule->course->course_name }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col justify-between p-4 md:p-8 relative overflow-hidden">

    <!-- Atmospheric Glow Background Blobs -->
    <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Bar -->
    <header class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800/80">
        <div class="flex items-center gap-4">
            <a href="{{ route('lecturer.sessions.show', $session) }}" class="p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $session->schedule->course->course_code }}
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Pertemuan {{ $session->session_number }}</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Dynamic QR Live
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white mt-1 tracking-tight">{{ $session->title }}</h1>
                <p class="text-xs text-slate-400 flex items-center gap-3 mt-0.5">
                    <span><i data-lucide="map-pin" class="inline w-3.5 h-3.5 text-slate-500"></i> {{ $session->schedule->room }}</span>
                    <span>&bull;</span>
                    <span>Radius Geofence: {{ $session->effective_radius }}m</span>
                    <span>&bull;</span>
                    <span>Batas Waktu: {{ $session->ends_at->format('H:i') }} WIB</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-slate-900 border border-slate-800 text-right">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hadir / Total Peserta</p>
                <p class="text-lg font-black text-white">
                    <span id="counter-attended" class="text-emerald-400">{{ $attendedCount }}</span>
                    <span class="text-slate-500 font-normal">/ {{ $totalEnrolled }}</span>
                </p>
            </div>
            <form action="{{ route('lecturer.sessions.close', $session) }}" method="POST" onsubmit="return confirm('Tutup sesi presensi sekarang?')">
                @csrf
                <button type="submit" class="px-5 py-3 rounded-2xl bg-rose-600/90 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/25 transition flex items-center gap-2">
                    <i data-lucide="stop-circle" class="w-4 h-4"></i>
                    Tutup Sesi Presensi
                </button>
            </form>
        </div>
    </header>

    <!-- Main QR Projector Center Area -->
    <main class="relative z-10 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center py-6">
        <!-- QR Code & Countdown Display (8 Cols) -->
        <div class="lg:col-span-8 flex flex-col items-center justify-center text-center space-y-6">
            <!-- QR Card Container -->
            <div class="p-6 md:p-8 rounded-3xl bg-white shadow-2xl shadow-indigo-500/10 ring-8 ring-white/10 max-w-sm md:max-w-md w-full relative">
                <!-- Watermark or live pulsing indicator -->
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-indigo-600 text-white text-[11px] font-bold shadow-md tracking-wider flex items-center gap-1.5 uppercase">
                    <i data-lucide="refresh-cw" class="w-3 h-3 animate-spin"></i> Auto-Refresh 30s
                </div>

                <!-- QR Image SVG -->
                <div class="w-full aspect-square flex items-center justify-center p-2 rounded-2xl bg-white">
                    <img id="qr-image" src="{{ $tokenData['qr_data_uri'] }}" alt="Dynamic QR Code" class="w-full h-full object-contain select-none transition-all duration-300">
                </div>

                <!-- Manual Input Fallback Code -->
                <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col items-center justify-center">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kode Manual Fallback</span>
                    <div id="manual-code" class="text-3xl font-extrabold tracking-widest font-mono text-indigo-700 bg-indigo-50 px-6 py-2 rounded-xl mt-1 select-all border border-indigo-100">
                        {{ $tokenData['manual_code'] }}
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1">Gunakan kode ini jika kamera mahasiswa mengalami kendala</span>
                </div>
            </div>

            <!-- Animated Countdown Timer Bar -->
            <div class="max-w-md w-full space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-300">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="timer" class="w-4 h-4 text-amber-400"></i>
                        Token QR diperbarui dalam:
                    </span>
                    <span id="timer-text" class="font-mono text-amber-400 font-bold text-sm">30s</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden ring-1 ring-slate-700">
                    <div id="timer-bar" class="h-full bg-gradient-to-r from-amber-400 via-indigo-500 to-emerald-400 rounded-full transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Live Attendee Ticker (4 Cols) -->
        <div class="lg:col-span-4 bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 h-full max-h-[500px] flex flex-col justify-between shadow-2xl">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
                        Mahasiswa Baru Masuk
                    </h3>
                    <span class="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Live Poll
                    </span>
                </div>

                <div id="ticker-list" class="divide-y divide-slate-800/60 overflow-y-auto max-h-[380px] mt-2 space-y-1">
                    <div class="p-8 text-center text-slate-500 text-xs">
                        <i data-lucide="loader" class="w-6 h-6 animate-spin mx-auto mb-2 text-indigo-400"></i>
                        Memuat data mahasiswa yang hadir...
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 text-center text-xs text-slate-400">
                Pindai QR menggunakan kamera aplikasi GeoAttend di browser ponsel Anda.
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 pt-4 border-t border-slate-800/80 text-center text-xs text-slate-500">
        GeoAttend &bull; Dynamic Token Hash SHA-256 &bull; Geofence GPS Radius Verified &bull; Asia/Jakarta
    </footer>

    <!-- Interactive Auto-Refresh & Polling Script -->
    <script>
        const sessionId = {{ $session->id }};
        const refreshUrl = "{{ route('lecturer.sessions.refresh_token', $session) }}";
        const pollUrl = "{{ route('lecturer.sessions.poll_attendees', $session) }}";

        let countdown = 30;
        const totalDuration = 30;

        function updateTimer() {
            countdown--;
            if (countdown <= 0) {
                countdown = totalDuration;
                fetchNewToken();
            }

            const pct = (countdown / totalDuration) * 100;
            document.getElementById('timer-bar').style.width = pct + '%';
            document.getElementById('timer-text').textContent = countdown + 's';
        }

        async function fetchNewToken() {
            try {
                const res = await fetch(refreshUrl);
                const data = await res.json();
                if (data.active) {
                    const qrImg = document.getElementById('qr-image');
                    qrImg.classList.add('opacity-0');
                    setTimeout(() => {
                        qrImg.src = data.qr_data_uri;
                        document.getElementById('manual-code').textContent = data.manual_code;
                        qrImg.classList.remove('opacity-0');
                    }, 200);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                console.error('Error refreshing token:', err);
            }
        }

        async function pollAttendees() {
            try {
                const res = await fetch(pollUrl);
                const data = await res.json();
                
                document.getElementById('counter-attended').textContent = data.attended_count;

                const listEl = document.getElementById('ticker-list');
                if (data.latest && data.latest.length > 0) {
                    listEl.innerHTML = data.latest.map(item => `
                        <div class="py-2.5 px-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center justify-between text-xs">
                            <div class="space-y-0.5">
                                <div class="font-bold text-white">${item.name}</div>
                                <div class="text-[11px] font-mono text-slate-400">${item.nim} &bull; ${item.time}</div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-${item.status_color}-500/20 text-${item.status_color}-300 border border-${item.status_color}-500/30">
                                    ${item.status}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">${item.distance}</div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    listEl.innerHTML = '<div class="p-8 text-center text-slate-500 text-xs">Belum ada mahasiswa yang mengirim presensi pada sesi ini.</div>';
                }
            } catch (err) {
                console.error('Error polling attendees:', err);
            }
        }

        // Start Intervals
        setInterval(updateTimer, 1000);
        setInterval(pollAttendees, 3500);
        pollAttendees();
    </script>
</body>
</html>
