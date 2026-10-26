<x-app-layout>
    <x-slot name="title">Presensi Kehadiran Mahasiswa</x-slot>
    <x-slot name="header">Presensi: {{ $session->schedule->course->course_name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Session Info Banner -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md">
                        {{ $session->schedule->course->course_code }}
                    </span>
                    <span class="text-xs text-slate-500 font-semibold">Pertemuan Ke-{{ $session->session_number }}</span>
                </div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $session->title }}</h2>
                <p class="text-xs text-slate-500 flex items-center gap-3">
                    <span><i data-lucide="map-pin" class="inline w-3.5 h-3.5 text-slate-400"></i> {{ $session->schedule->room }}</span>
                    <span>&bull;</span>
                    <span>Radius Geofence: {{ $session->effective_radius }}m</span>
                    <span>&bull;</span>
                    <span>Batas Waktu: {{ $session->ends_at->format('H:i') }} WIB</span>
                </p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition shrink-0 self-start sm:self-center">
                Batal
            </a>
        </div>

        <!-- Wizard Stepper Navigation -->
        <div class="grid grid-cols-3 gap-2 text-center text-xs font-bold">
            <div id="step-tab-1" class="p-3 rounded-2xl bg-indigo-600 text-white shadow-md transition-all flex items-center justify-center gap-2">
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">1</span>
                <span>Pindai QR</span>
            </div>
            <div id="step-tab-2" class="p-3 rounded-2xl bg-slate-100 text-slate-400 transition-all flex items-center justify-center gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">2</span>
                <span>Validasi Lokasi</span>
            </div>
            <div id="step-tab-3" class="p-3 rounded-2xl bg-slate-100 text-slate-400 transition-all flex items-center justify-center gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">3</span>
                <span>Foto Selfie</span>
            </div>
        </div>

        <!-- Wizard Submission Form -->
        <form id="attendance-form" action="{{ route('student.attendance.store', $session) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Hidden Fields for validated payloads -->
            <input type="hidden" name="qr_token" id="input_qr_token" value="{{ old('qr_token') }}">
            <input type="hidden" name="latitude" id="input_latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="input_longitude" value="{{ old('longitude') }}">
            <input type="hidden" name="accuracy" id="input_accuracy" value="{{ old('accuracy') }}">
            <input type="hidden" name="selfie_image" id="input_selfie_image">

            <!-- ============================================================= -->
            <!-- STEP 1: SCAN QR CODE OR MANUAL INPUT -->
            <!-- ============================================================= -->
            <div id="step-section-1" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6">
                <div class="text-center max-w-md mx-auto space-y-1">
                    <h3 class="text-lg font-extrabold text-slate-900">Langkah 1: Pindai Dynamic QR Code</h3>
                    <p class="text-xs text-slate-500">Pindai kode QR yang sedang ditampilkan dosen di layar proyektor ruang kelas.</p>
                </div>

                <!-- QR Scanner Container -->
                <div class="max-w-md mx-auto space-y-4">
                    <div id="qr-reader" class="rounded-2xl overflow-hidden border-2 border-slate-200 bg-slate-950 aspect-square flex items-center justify-center relative">
                        <div id="qr-placeholder" class="text-center p-6 text-slate-400 space-y-3">
                            <i data-lucide="camera" class="w-12 h-12 mx-auto text-slate-600"></i>
                            <p class="text-xs">Kamera scanner belum aktif</p>
                            <button type="button" onclick="startScanner()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                                Aktifkan Kamera Scanner
                            </button>
                        </div>
                    </div>

                    <div id="scanner-controls" class="hidden text-center">
                        <button type="button" onclick="stopScanner()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                            Matikan Kamera Scanner
                        </button>
                    </div>

                    <!-- Scan Success Indicator -->
                    <div id="qr-success-card" class="hidden p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-center space-y-1 animate-fade-in">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white mx-auto flex items-center justify-center">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-sm font-bold text-emerald-900">QR Code Berhasil Terverifikasi!</h4>
                        <p class="text-xs text-emerald-700">Token QR sesi berhasil dibaca.</p>
                    </div>

                    <!-- Manual Input Fallback Accordion -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="text-center mb-2">
                            <span class="text-xs font-bold text-slate-500 uppercase">Atau Gunakan Kode Manual:</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="manual_token_input" placeholder="Masukkan 6 Digit Kode Layar" maxlength="32"
                                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-center font-mono font-bold tracking-widest text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase">
                            <button type="button" onclick="applyManualCode()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition">
                                Terapkan
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="button" id="btn-next-1" disabled onclick="goToStep(2)" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-200 disabled:text-slate-400 text-white font-bold text-xs transition flex items-center justify-center gap-2">
                            Lanjut ke Validasi Lokasi &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- STEP 2: GEOFENCE GPS VALIDATION -->
            <!-- ============================================================= -->
            <div id="step-section-2" class="hidden bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6">
                <div class="text-center max-w-md mx-auto space-y-1">
                    <h3 class="text-lg font-extrabold text-slate-900">Langkah 2: Validasi Geofence Lokasi GPS</h3>
                    <p class="text-xs text-slate-500">Pastikan Anda berada di dalam radius kelas perkuliahan (Maksimal {{ $session->effective_radius }} meter).</p>
                </div>

                <div class="max-w-xl mx-auto space-y-4">
                    <div class="text-center">
                        <button type="button" id="btn-detect-gps" onclick="detectGPSLocation()" class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 transition inline-flex items-center gap-2">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            Deteksi Titik Lokasi GPS Saya
                        </button>
                    </div>

                    <!-- Geofence Status Card -->
                    <div id="gps-status-card" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800">Status Koordinat:</span>
                            <span id="gps-text-status" class="font-semibold text-slate-500">Belum dideteksi</span>
                        </div>
                        <div class="flex items-center justify-between font-mono">
                            <span>Akurasi GPS:</span>
                            <span id="gps-text-accuracy">-</span>
                        </div>
                        <div class="flex items-center justify-between font-mono">
                            <span>Jarak ke Ruang Kelas:</span>
                            <span id="gps-text-distance" class="font-bold">-</span>
                        </div>
                    </div>

                    <!-- Leaflet Geofence Map -->
                    <div id="student-map" class="w-full h-64 rounded-2xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>

                    <div class="pt-4 flex items-center justify-between gap-4">
                        <button type="button" onclick="goToStep(1)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                            &larr; Kembali
                        </button>
                        <button type="button" id="btn-next-2" disabled onclick="goToStep(3)" class="py-3 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-200 disabled:text-slate-400 text-white font-bold text-xs transition flex items-center gap-2">
                            Lanjut ke Foto Selfie &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- STEP 3: SELFIE CAPTURE & SUBMISSION -->
            <!-- ============================================================= -->
            <div id="step-section-3" class="hidden bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 md:p-8 space-y-6">
                <div class="text-center max-w-md mx-auto space-y-1">
                    <h3 class="text-lg font-extrabold text-slate-900">Langkah 3: Ambil Bukti Foto Selfie</h3>
                    <p class="text-xs text-slate-500">Ambil foto selfie langsung dengan wajah terlihat jelas sebagai bukti visual kehadiran.</p>
                </div>

                <div class="max-w-md mx-auto space-y-4">
                    <!-- Webcam Live Preview Video -->
                    <div id="selfie-camera-box" class="relative rounded-2xl overflow-hidden border-2 border-slate-200 bg-slate-950 aspect-[4/3] flex items-center justify-center">
                        <video id="selfie-video" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
                        <canvas id="selfie-canvas" class="hidden"></canvas>
                        <img id="selfie-preview" src="" alt="Selfie Preview" class="hidden w-full h-full object-cover">
                    </div>

                    <!-- Camera Action Controls -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" id="btn-take-selfie" onclick="captureSelfie()" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition inline-flex items-center gap-2">
                            <i data-lucide="camera" class="w-4 h-4"></i> Ambil Foto
                        </button>
                        <button type="button" id="btn-retake-selfie" onclick="retakeSelfie()" class="hidden px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs transition items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Ambil Ulang
                        </button>
                    </div>

                    <!-- File Upload Fallback -->
                    <div class="pt-4 border-t border-slate-100 text-center">
                        <p class="text-[11px] text-slate-400 mb-2">Kendala webcam browser? Unggah berkas foto:</p>
                        <input type="file" name="selfie_file" id="selfie_file" accept="image/*" onchange="handleFileUpload(this)"
                            class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    </div>

                    <!-- Final Submission Recap Box -->
                    <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-100 text-xs space-y-1.5">
                        <div class="flex justify-between text-slate-600">
                            <span>Mata Kuliah:</span>
                            <span class="font-bold text-slate-900">{{ $session->schedule->course->course_name }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Sesi:</span>
                            <span class="font-bold text-slate-900">Pertemuan {{ $session->session_number }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Verifikasi GPS:</span>
                            <span id="recap-distance" class="font-bold text-emerald-700">Terverifikasi</span>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-between gap-4">
                        <button type="button" onclick="goToStep(2)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                            &larr; Kembali
                        </button>
                        <button type="submit" id="btn-submit-attendance" disabled class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 disabled:from-slate-300 disabled:to-slate-300 disabled:text-slate-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Kirimkan Presensi Saya
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        const targetLat = parseFloat("{{ $session->effective_latitude }}");
        const targetLon = parseFloat("{{ $session->effective_longitude }}");
        const allowedRadius = parseFloat("{{ $session->effective_radius }}");
        const validateLocationUrl = "{{ route('student.attendance.validate_location', $session) }}";

        let html5QrScanner = null;
        let mapInstance = null;
        let studentMarker = null;
        let targetCircle = null;
        let webcamStream = null;

        // Step Navigation
        function goToStep(step) {
            [1, 2, 3].forEach(s => {
                document.getElementById('step-section-' + s).classList.add('hidden');
                const tab = document.getElementById('step-tab-' + s);
                tab.classList.remove('bg-indigo-600', 'text-white', 'shadow-md');
                tab.classList.add('bg-slate-100', 'text-slate-400');
            });

            document.getElementById('step-section-' + step).classList.remove('hidden');
            const activeTab = document.getElementById('step-tab-' + step);
            activeTab.classList.remove('bg-slate-100', 'text-slate-400');
            activeTab.classList.add('bg-indigo-600', 'text-white', 'shadow-md');

            if (step === 2) {
                initStudentMap();
                detectGPSLocation();
            } else if (step === 3) {
                initWebcam();
            }
        }

        // ==========================================
        // STEP 1: QR SCANNER
        // ==========================================
        function startScanner() {
            document.getElementById('qr-placeholder').classList.add('hidden');
            document.getElementById('scanner-controls').classList.remove('hidden');

            html5QrScanner = new Html5Qrcode("qr-reader");
            html5QrScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    handleSuccessfulToken(decodedText);
                    stopScanner();
                },
                (errorMessage) => {
                    // Scanning in progress...
                }
            ).catch(err => {
                alert('Gagal mengakses kamera scanner: ' + err);
                document.getElementById('qr-placeholder').classList.remove('hidden');
                document.getElementById('scanner-controls').classList.add('hidden');
            });
        }

        function stopScanner() {
            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    html5QrScanner.clear();
                }).catch(e => console.log(e));
            }
            document.getElementById('scanner-controls').classList.add('hidden');
        }

        function handleSuccessfulToken(token) {
            document.getElementById('input_qr_token').value = token;
            document.getElementById('qr-success-card').classList.remove('hidden');
            document.getElementById('btn-next-1').disabled = false;
        }

        function applyManualCode() {
            const val = document.getElementById('manual_token_input').value.trim();
            if (val.length >= 4) {
                handleSuccessfulToken(val);
                alert('Kode manual berhasil diterapkan! Silakan klik tombol lanjut.');
            } else {
                alert('Masukkan kode token manual yang valid.');
            }
        }

        // ==========================================
        // STEP 2: GPS & GEOFENCE
        // ==========================================
        function initStudentMap() {
            if (!mapInstance) {
                mapInstance = L.map('student-map').setView([targetLat, targetLon], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapInstance);

                targetCircle = L.circle([targetLat, targetLon], {
                    color: '#4f46e5',
                    fillColor: '#6366f1',
                    fillOpacity: 0.2,
                    radius: allowedRadius
                }).addTo(mapInstance);

                L.marker([targetLat, targetLon]).addTo(mapInstance)
                    .bindPopup('<b>Titik Kelas: {{ $session->schedule->room }}</b><br>Radius: ' + allowedRadius + 'm');
            }
            setTimeout(() => { mapInstance.invalidateSize(); }, 300);
        }

        function detectGPSLocation() {
            const statusEl = document.getElementById('gps-text-status');
            statusEl.textContent = 'Mendeteksi koordinat GPS...';
            statusEl.className = 'font-semibold text-indigo-600 animate-pulse';

            if (!navigator.geolocation) {
                statusEl.textContent = 'Browser tidak mendukung Geolocation.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    const acc = pos.coords.accuracy;

                    document.getElementById('input_latitude').value = lat;
                    document.getElementById('input_longitude').value = lon;
                    document.getElementById('input_accuracy').value = acc;

                    document.getElementById('gps-text-accuracy').textContent = acc.toFixed(1) + ' meter';

                    if (studentMarker) {
                        studentMarker.setLatLng([lat, lon]);
                    } else {
                        studentMarker = L.marker([lat, lon]).addTo(mapInstance).bindPopup('Lokasi Anda Saat Ini');
                    }
                    mapInstance.panTo([lat, lon]);

                    // Server-side Geofence Validation
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch(validateLocationUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ latitude: lat, longitude: lon, accuracy: acc })
                        });
                        const data = await res.json();

                        document.getElementById('gps-text-distance').textContent = data.distance + ' meter';

                        if (data.is_valid) {
                            statusEl.textContent = 'Lokasi Terverifikasi (Dalam Radius)';
                            statusEl.className = 'font-bold text-emerald-700';
                            document.getElementById('btn-next-2').disabled = false;
                            document.getElementById('recap-distance').textContent = data.distance + 'm (Radius ' + allowedRadius + 'm)';
                        } else {
                            statusEl.textContent = data.message;
                            statusEl.className = 'font-bold text-rose-600';
                            document.getElementById('btn-next-2').disabled = true;
                            alert(data.message);
                        }
                    } catch (err) {
                        console.error('Validation error:', err);
                    }
                },
                (err) => {
                    statusEl.textContent = 'Gagal mendeteksi GPS: ' + err.message;
                    statusEl.className = 'font-bold text-rose-600';
                    alert('Izin lokasi ditolak atau sinyal GPS tidak tersedia. Pastikan izin lokasi aktif di browser.');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        // ==========================================
        // STEP 3: SELFIE WEBCAM
        // ==========================================
        function initWebcam() {
            const video = document.getElementById('selfie-video');
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                .then(stream => {
                    webcamStream = stream;
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.warn('Webcam tidak tersedia:', err);
                });
        }

        function captureSelfie() {
            const video = document.getElementById('selfie-video');
            const canvas = document.getElementById('selfie-canvas');
            const preview = document.getElementById('selfie-preview');

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            // Flip horizontal for natural mirror selfie
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            document.getElementById('input_selfie_image').value = dataUrl;
            preview.src = dataUrl;

            video.classList.add('hidden');
            preview.classList.remove('hidden');

            const btnTake = document.getElementById('btn-take-selfie');
            const btnRetake = document.getElementById('btn-retake-selfie');
            btnTake.classList.add('hidden');
            btnTake.classList.remove('inline-flex');
            btnRetake.classList.remove('hidden');
            btnRetake.classList.add('inline-flex');
            document.getElementById('btn-submit-attendance').disabled = false;
        }

        function retakeSelfie() {
            const video = document.getElementById('selfie-video');
            const preview = document.getElementById('selfie-preview');

            video.classList.remove('hidden');
            preview.classList.add('hidden');

            document.getElementById('input_selfie_image').value = '';
            const btnTake = document.getElementById('btn-take-selfie');
            const btnRetake = document.getElementById('btn-retake-selfie');
            btnTake.classList.remove('hidden');
            btnTake.classList.add('inline-flex');
            btnRetake.classList.add('hidden');
            btnRetake.classList.remove('inline-flex');
            document.getElementById('btn-submit-attendance').disabled = true;
        }

        function handleFileUpload(input) {
            if (input.files && input.files[0]) {
                document.getElementById('btn-submit-attendance').disabled = false;
            }
        }

        // Prevent double submit
        document.getElementById('attendance-form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-submit-attendance');
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Mengirim Presensi...';
        });
    </script>
    @endpush
</x-app-layout>
