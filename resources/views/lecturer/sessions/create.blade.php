<x-app-layout>
    <x-slot name="title">Buka Sesi Presensi Baru</x-slot>
    <x-slot name="header">Buka Sesi Presensi: {{ $schedule->course->course_name }}</x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md">{{ $schedule->course->course_code }}</span>
                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $schedule->course->course_name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jadwal Reguler: {{ $schedule->day_of_week }}, {{ $schedule->formatted_time }} &bull; {{ $schedule->room }}</p>
            </div>
            <a href="{{ route('lecturer.sessions.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Batal
            </a>
        </div>

        <form action="{{ route('lecturer.sessions.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-1">
                    <label for="session_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pertemuan Ke-</label>
                    <input type="number" name="session_number" id="session_number" value="{{ old('session_number', $nextSessionNumber) }}" min="1" max="32" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-center">
                </div>
                <div class="sm:col-span-3">
                    <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Topik / Judul Pertemuan Perkuliahan</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Contoh: Pertemuan {{ $nextSessionNumber }} — Analisis & Desain Sistem" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Waktu Mulai Presensi</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="ends_at" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Waktu Berakhir Presensi</label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at', now()->addHours(2)->format('Y-m-d\TH:i')) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Geofence Override & Map Picker -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-600"></i>
                            Titik Koordinat & Radius Geofence Kelas
                        </h4>
                        <p class="text-xs text-slate-500">Secara bawaan menggunakan koordinat ruangan jadwal. Anda dapat mengubahnya atau menggunakan lokasi Anda saat ini.</p>
                    </div>
                    <button type="button" onclick="detectCurrentLocation()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition shrink-0">
                        <i data-lucide="crosshair" class="w-4 h-4"></i>
                        Gunakan Lokasi Saya Saat Ini
                    </button>
                </div>

                <div id="map-picker" class="w-full h-64 rounded-2xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="override_latitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Latitude</label>
                        <input type="number" step="0.00000001" name="override_latitude" id="override_latitude" value="{{ old('override_latitude', $schedule->location_latitude) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="override_longitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Longitude</label>
                        <input type="number" step="0.00000001" name="override_longitude" id="override_longitude" value="{{ old('override_longitude', $schedule->location_longitude) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="override_radius" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Radius Toleransi (Meter)</label>
                        <input type="number" name="override_radius" id="override_radius" value="{{ old('override_radius', $schedule->geofence_radius) }}" min="10" max="1000"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Auto Open checkbox -->
            <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                <div>
                    <h5 class="text-xs font-bold text-indigo-900">Buka Sesi Langsung & Tampilkan QR</h5>
                    <p class="text-xs text-indigo-700">Aktifkan sesi ini sekarang dan langsung arahkan ke layar presentasi Dynamic QR Code.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_open" value="1" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('lecturer.sessions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Simpan & Lanjutkan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let map, marker, circle;

        document.addEventListener('DOMContentLoaded', () => {
            const defaultLat = parseFloat(document.getElementById('override_latitude').value) || -6.175392;
            const defaultLon = parseFloat(document.getElementById('override_longitude').value) || 106.827153;
            const defaultRadius = parseInt(document.getElementById('override_radius').value) || 100;

            map = L.map('map-picker').setView([defaultLat, defaultLon], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([defaultLat, defaultLon], { draggable: true }).addTo(map);
            circle = L.circle([defaultLat, defaultLon], {
                color: '#4f46e5',
                fillColor: '#6366f1',
                fillOpacity: 0.25,
                radius: defaultRadius
            }).addTo(map);

            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('override_latitude').addEventListener('input', syncFromInputs);
            document.getElementById('override_longitude').addEventListener('input', syncFromInputs);
            document.getElementById('override_radius').addEventListener('input', syncFromInputs);
        });

        function updateInputs(lat, lon) {
            document.getElementById('override_latitude').value = lat.toFixed(8);
            document.getElementById('override_longitude').value = lon.toFixed(8);
            circle.setLatLng([lat, lon]);
        }

        function syncFromInputs() {
            const lat = parseFloat(document.getElementById('override_latitude').value);
            const lon = parseFloat(document.getElementById('override_longitude').value);
            const rad = parseInt(document.getElementById('override_radius').value) || 100;

            if (!isNaN(lat) && !isNaN(lon)) {
                marker.setLatLng([lat, lon]);
                circle.setLatLng([lat, lon]);
                circle.setRadius(rad);
                map.panTo([lat, lon]);
            }
        }

        function detectCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    marker.setLatLng([lat, lon]);
                    circle.setLatLng([lat, lon]);
                    map.setView([lat, lon], 17);
                    updateInputs(lat, lon);
                },
                (err) => {
                    alert('Gagal mendeteksi lokasi: ' + err.message);
                },
                { enableHighAccuracy: true }
            );
        }
    </script>
    @endpush
</x-app-layout>
