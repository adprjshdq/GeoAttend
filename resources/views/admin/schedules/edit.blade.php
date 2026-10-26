<x-app-layout>
    <x-slot name="title">Edit Jadwal: {{ $schedule->course->course_name }}</x-slot>
    <x-slot name="header">Edit Jadwal & Geofence Kelas</x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="course_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Mata Kuliah</label>
                    <select name="course_id" id="course_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ old('course_id', $schedule->course_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->course_code }} — {{ $c->course_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="lecturer_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Dosen Pengampu</label>
                    <select name="lecturer_id" id="lecturer_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($lecturers as $l)
                            <option value="{{ $l->id }}" {{ old('lecturer_id', $schedule->lecturer_id) == $l->id ? 'selected' : '' }}>
                                {{ $l->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label for="day_of_week" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Hari</label>
                    <select name="day_of_week" id="day_of_week" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $d)
                            <option value="{{ $d }}" {{ old('day_of_week', $schedule->day_of_week) === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Jam Mulai</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Jam Selesai</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="room" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ruangan</label>
                    <input type="text" name="room" id="room" value="{{ old('room', $schedule->room) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Geofencing & Map Picker -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-600"></i>
                            Titik Koordinat & Radius Geofence Kelas
                        </h4>
                        <p class="text-xs text-slate-500">Geser pin pada peta untuk mengubah koordinat kelas.</p>
                    </div>
                    <button type="button" onclick="detectCurrentLocation()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition shrink-0">
                        <i data-lucide="crosshair" class="w-4 h-4"></i>
                        Gunakan Lokasi Saya Saat Ini
                    </button>
                </div>

                <div id="map-picker" class="w-full h-72 rounded-2xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="location_latitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Latitude</label>
                        <input type="number" step="0.00000001" name="location_latitude" id="location_latitude" value="{{ old('location_latitude', $schedule->location_latitude) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="location_longitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Longitude</label>
                        <input type="number" step="0.00000001" name="location_longitude" id="location_longitude" value="{{ old('location_longitude', $schedule->location_longitude) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="geofence_radius" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Radius Geofence (Meter)</label>
                        <input type="number" name="geofence_radius" id="geofence_radius" value="{{ old('geofence_radius', $schedule->geofence_radius) }}" min="10" max="1000" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.schedules.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/30 transition">
                    Perbarui Jadwal & Geofence
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let map, marker, circle;

        document.addEventListener('DOMContentLoaded', () => {
            const defaultLat = parseFloat(document.getElementById('location_latitude').value);
            const defaultLon = parseFloat(document.getElementById('location_longitude').value);
            const defaultRadius = parseInt(document.getElementById('geofence_radius').value) || 100;

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

            marker.on('dragend', function (e) {
                const pos = marker.getLatLng();
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('location_latitude').addEventListener('input', syncFromInputs);
            document.getElementById('location_longitude').addEventListener('input', syncFromInputs);
            document.getElementById('geofence_radius').addEventListener('input', syncFromInputs);
        });

        function updateInputs(lat, lon) {
            document.getElementById('location_latitude').value = lat.toFixed(8);
            document.getElementById('location_longitude').value = lon.toFixed(8);
            circle.setLatLng([lat, lon]);
        }

        function syncFromInputs() {
            const lat = parseFloat(document.getElementById('location_latitude').value);
            const lon = parseFloat(document.getElementById('location_longitude').value);
            const rad = parseInt(document.getElementById('geofence_radius').value) || 100;

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
