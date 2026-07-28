<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Presensi | POS System</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#003f87",
                        "primary-container": "#0056b3",
                        "primary-fixed": "#d7e2ff",
                        "primary-fixed-dim": "#acc7ff",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#001a40",
                        "on-primary-container": "#bbd0ff",
                        "secondary": "#4c5e84",
                        "surface": "#f8f9fa",
                        "surface-container": "#edeeef",
                        "surface-container-low": "#f3f4f5",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "on-surface": "#191c1d",
                        "on-surface-variant": "#424752",
                        "outline": "#727784",
                        "outline-variant": "#c2c6d4",
                        "error": "#ba1a1a",
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "2xl": "1.5rem",
                        "3xl": "2rem",
                        "full": "9999px"
                    },
                    fontFamily: {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.9);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.3;
            }

            100% {
                transform: scale(0.9);
                opacity: 0.5;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-pulse-ring {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-slide-up {
            animation: slide-up 0.5s ease-out;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .scanner-frame {
            position: relative;
        }

        .scanner-frame::before,
        .scanner-frame::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border-color: #003f87;
            border-style: solid;
        }

        .scanner-frame::before {
            top: 0;
            left: 0;
            border-width: 4px 0 0 4px;
            border-radius: 8px 0 0 0;
        }

        .scanner-frame::after {
            bottom: 0;
            right: 0;
            border-width: 0 4px 4px 0;
            border-radius: 0 0 8px 0;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-surface via-surface-container-low to-surface font-body text-on-surface min-h-screen">

    @php
        $user = auth()->user();
        $isLoggedIn = $user !== null;
    @endphp

    <!-- Top Bar -->
    <header class="fixed top-0 left-0 right-0 z-40 glass-card border-b border-outline-variant/20">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center shadow-md">
                    <span class="material-symbols-outlined text-white text-xl"
                        style="font-variation-settings: 'FILL' 1;">fingerprint</span>
                </div>
                <div>
                    <h1 class="font-headline font-extrabold text-lg text-primary leading-tight">Presensi</h1>
                    <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">
                        {{ now()->format('d M Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($isLoggedIn)
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2.5 bg-surface-container rounded-xl hover:bg-error/10 transition-all group" title="Logout">
                            <span class="material-symbols-outlined text-on-surface-variant group-hover:text-error transition-colors">logout</span>
                        </button>
                    </form>
                    <a href="/dashboard"
                        class="p-2.5 bg-surface-container rounded-xl hover:bg-surface-container-high transition-all group">
                        <span
                            class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">dashboard</span>
                    </a>
                    <div
                        class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @else
                    <a href="/login"
                        class="px-4 py-2 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary-container transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">login</span>
                        Masuk
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20 pb-8 px-4 max-w-7xl mx-auto">

        @if($isLoggedIn)
            @if(!$user->isSuperAdmin())
                <div class="animate-slide-up space-y-6">
        <!-- Report Header Section -->
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $title ?? 'Page' }}" />
        </div>

                    <!-- QR Scanner Section -->
                    <section class="bg-surface-container-lowest rounded-3xl p-6 shadow-sm border border-outline-variant/10">
                        <div class="text-center mb-6">
                            <div
                                class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4 animate-float">
                                <span class="material-symbols-outlined text-primary text-3xl"
                                    style="font-variation-settings: 'FILL' 1;">qr_code_scanner</span>
                            </div>
                            <h2 class="font-headline font-extrabold text-2xl text-on-surface mb-1">Scan QR Presensi</h2>
                            <p class="text-on-surface-variant text-sm">Arahkan kamera ke QR Code yang tersedia</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="openQRScanner('masuk')"
                                class="relative group bg-gradient-to-br from-primary to-primary-container text-white py-5 rounded-2xl font-bold transition-all active:scale-95 flex flex-col items-center gap-2 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                                <div
                                    class="absolute inset-0 rounded-2xl bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                                <span class="material-symbols-outlined text-3xl relative z-10">login</span>
                                <span class="text-base relative z-10">Masuk</span>
                            </button>
                            <button onclick="openQRScanner('pulang')"
                                class="relative group bg-gradient-to-br from-secondary to-indigo-600 text-white py-5 rounded-2xl font-bold transition-all active:scale-95 flex flex-col items-center gap-2 shadow-lg shadow-secondary/20 hover:shadow-xl hover:shadow-secondary/30">
                                <div
                                    class="absolute inset-0 rounded-2xl bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                                <span class="material-symbols-outlined text-3xl relative z-10">logout</span>
                                <span class="text-base relative z-10">Pulang</span>
                            </button>
                        </div>
                    </section>

                    <!-- My Presensi Status -->
                    <section class="bg-surface-container-lowest rounded-3xl p-6 shadow-sm border border-outline-variant/10">
                        <h3 class="font-headline font-extrabold text-lg text-on-surface mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary"
                                style="font-variation-settings: 'FILL' 1;">person</span>
                            Status Presensi Saya
                        </h3>

                        <div class="grid grid-cols-3 gap-3 mb-4" id="myPresensi">
                            <div class="text-center p-3 bg-surface-container rounded-2xl">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-2">Status
                                </p>
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600"
                                    id="myStatus">...</span>
                            </div>
                            <div class="text-center p-3 bg-surface-container rounded-2xl">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-2">Masuk</p>
                                <span class="font-mono text-lg font-bold text-on-surface" id="myCheckIn">-</span>
                            </div>
                            <div class="text-center p-3 bg-surface-container rounded-2xl">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-2">Pulang
                                </p>
                                <span class="font-mono text-lg font-bold text-on-surface" id="myCheckOut">-</span>
                            </div>
                        </div>

                        <!-- Manual Check-in Buttons (fallback) -->
                        <!-- <div class="flex gap-3">
                                <form action="/presensi" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" id="btnMasuk" class="w-full py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-container transition-all flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-primary" disabled>
                                        <span class="material-symbols-outlined text-lg">login</span>
                                        <span class="text-sm">Masuk</span>
                                    </button>
                                </form>
                                <form action="/presensi/checkout" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" id="btnPulang" class="w-full py-3 bg-secondary text-white font-bold rounded-xl hover:bg-secondary-container transition-all flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-secondary" disabled>
                                        <span class="material-symbols-outlined text-lg">logout</span>
                                        <span class="text-sm">Pulang</span>
                                    </button>
                                </form>
                            </div> -->
                    </section>

                    <!-- Attendance History -->
                    @if(isset($history) && $history->count() > 0)
                        <section class="bg-surface-container-lowest rounded-3xl p-6 shadow-sm border border-outline-variant/10">
                            <h3 class="font-headline font-extrabold text-lg text-on-surface mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary"
                                    style="font-variation-settings: 'FILL' 1;">history</span>
                                Riwayat 7 Hari
                            </h3>
                            <div class="space-y-3">
                                @foreach($history as $h)
                                    <div class="flex items-center justify-between p-4 bg-surface-container rounded-2xl">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-11 h-11 rounded-xl flex items-center justify-center {{ $h->status === 'present' ? 'bg-green-100' : ($h->status === 'late' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                                                <span
                                                    class="material-symbols-outlined {{ $h->status === 'present' ? 'text-green-600' : ($h->status === 'late' ? 'text-yellow-600' : 'text-gray-500') }}"
                                                    style="font-variation-settings: 'FILL' 1;">
                                                    {{ $h->check_in ? 'check_circle' : 'schedule' }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm text-on-surface">
                                                    {{ \Carbon\Carbon::parse($h->date)->format('d M Y') }}</p>
                                                <p class="text-xs text-on-surface-variant">{{ $h->shift->name ?? 'Tanpa Shift' }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if($h->check_in)
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-bold {{ $h->status === 'present' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    {{ $h->status === 'present' ? 'Hadir' : 'Terlambat' }}
                                                </span>
                                                <p class="text-xs text-on-surface-variant mt-1 font-mono">{{ $h->check_in }} -
                                                    {{ $h->check_out ?? '-' }}</p>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Tidak
                                                    Hadir</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            @else
                <!-- Super Admin -->
                <div class="animate-slide-up max-w-md mx-auto">
                    <section
                        class="bg-gradient-to-br from-primary to-primary-container rounded-3xl p-8 text-white text-center shadow-xl shadow-primary/20">
                        <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-white text-4xl"
                                style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span>
                        </div>
                        <h2 class="font-headline font-extrabold text-2xl mb-2">Super Admin</h2>
                        <p class="text-white/80 text-sm">Anda tidak perlu melakukan presensi</p>
                        <a href="/dashboard"
                            class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm text-white rounded-xl font-bold hover:bg-white/30 transition-all">
                            <span class="material-symbols-outlined">dashboard</span>
                            Dashboard
                        </a>
                    </section>
                </div>
            @endif

            <!-- Admin Summary Section -->
            @if($user->hasMinRole('admin'))
                <div class="mt-8 animate-slide-up space-y-6">
                    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-2">Pilih Toko
                            </p>
                            <select id="storeSelect" onchange="loadPresensiData()"
                                class="w-full bg-surface-container border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary/20 px-4 py-3">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ $store->id == $user->store_id ? 'selected' : '' }}>
                                        {{ $store->branch->name ?? '' }} - {{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div
                            class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Total
                                    Staff</p>
                                <h3 id="totalStaff" class="font-headline font-extrabold text-2xl text-primary">-</h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary"
                                    style="font-variation-settings: 'FILL' 1;">groups</span>
                            </div>
                        </div>
                        <div
                            class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Hadir
                                    Hari Ini</p>
                                <h3 id="hadirCount" class="font-headline font-extrabold text-2xl text-green-600">-</h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-600"
                                    style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            </div>
                        </div>
                    </section>

                    <!-- Admin Data Table -->
                    <section
                        class="bg-surface-container-lowest rounded-3xl shadow-sm border border-outline-variant/10 overflow-hidden">
                        <div class="p-5 border-b border-outline-variant/10">
                            <h3 class="font-headline font-extrabold text-lg text-on-surface flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary"
                                    style="font-variation-settings: 'FILL' 1;">table_chart</span>
                                Data Presensi Staff
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left min-w-[500px]">
                                <thead>
                                    <tr class="bg-surface-container">
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Staff</th>
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Shift</th>
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Jadwal</th>
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Masuk</th>
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Pulang</th>
                                        <th
                                            class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody id="presensiTable" class="divide-y divide-outline-variant/10">
                                    <tr>
                                        <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">
                                            <span
                                                class="material-symbols-outlined text-3xl text-outline/30 mb-2 block">store</span>
                                            <p class="text-sm">Pilih toko untuk melihat data</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            @endif

        @else
            <!-- Not Logged In -->
            <div class="animate-slide-up max-w-md mx-auto">
                <section
                    class="bg-surface-container-lowest rounded-3xl p-8 text-center shadow-sm border border-outline-variant/10">
                    <div class="w-20 h-20 rounded-2xl bg-surface-container flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-outline text-4xl">login</span>
                    </div>
                    <h2 class="font-headline font-extrabold text-2xl text-on-surface mb-2">Silakan Login</h2>
                    <p class="text-on-surface-variant mb-6">Login untuk melakukan presensi masuk dan pulang</p>
                    <a href="/login"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-primary-container text-white rounded-xl font-bold hover:shadow-lg hover:shadow-primary/20 transition-all">
                        <span class="material-symbols-outlined">login</span>
                        Login Sekarang
                    </a>
                </section>
            </div>
        @endif
    </main>

    <!-- QR Scanner Modal -->
    <div id="qrReaderModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" onclick="closeQRModal()"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-sm bg-surface-container-lowest rounded-3xl shadow-2xl p-6 animate-slide-up">
            <div class="flex items-center justify-between mb-5">
                <h3 id="scanTitle" class="font-headline font-extrabold text-lg text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary"
                        style="font-variation-settings: 'FILL' 1;">qr_code_scanner</span>
                    <span>Scan QR</span>
                </h3>
                <button onclick="closeQRModal()" class="p-2 hover:bg-surface-container rounded-xl transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant">close</span>
                </button>
            </div>

            <!-- Scanner Area -->
            <div class="relative bg-black rounded-2xl overflow-hidden mb-4 scanner-frame aspect-square">
                <div id="reader" class="w-full h-full"></div>
            </div>

            <p class="text-xs text-on-surface-variant text-center mb-4 font-medium">Arahkan kamera ke QR code presensi
            </p>

            <button onclick="closeQRModal()"
                class="w-full py-3 bg-surface-container text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all">
                Tutup
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        let currentMode = 'masuk';
        let isProcessing = false;

        const Swal = window.Swal || { fire: (opts) => alert(opts.title + '\n' + opts.text) };

        function sendPresensiRequest(endpoint, payload) {
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    stopQRScanner();
                    document.getElementById('qrReaderModal').classList.add('hidden');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Presensi ' + currentMode + ' berhasil!', timer: 3000, timerProgressBar: true });
                    @if($isLoggedIn && !$user->isSuperAdmin())
                        loadMyPresensi();
                    @endif
                } else {
                    html5QrcodeScanner.resume();
                    isProcessing = false;
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal presensi' });
                }
            }).catch(() => {
                html5QrcodeScanner.resume();
                isProcessing = false;
                Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi bermasalah' });
            });
        }

        function openQRScanner(mode) {
            currentMode = mode;
            isProcessing = false;
            document.getElementById('qrReaderModal').classList.remove('hidden');
            document.getElementById('scanTitle').innerHTML = `<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">qr_code_scanner</span><span>${mode === 'masuk' ? 'Scan Masuk' : 'Scan Pulang'}</span>`;
            setTimeout(startQRScanner, 300);
        }

        function startQRScanner() {
            if (html5QrcodeScanner) return;

            const readerDiv = document.getElementById('reader');
            readerDiv.innerHTML = '';

            html5QrcodeScanner = new Html5Qrcode("reader");
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                function (decodedText) {
                    if (isProcessing) return;
                    isProcessing = true;
                    html5QrcodeScanner.pause();

                    try {
                        const data = JSON.parse(decodedText);
                        if (data.store_id && data.date) {
                            const endpoint = currentMode === 'masuk' ? '/presensi/scan' : '/presensi/scan-pulang';
                            
                            const payload = { ...data };
                            
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    function(position) {
                                        payload.latitude = position.coords.latitude;
                                        payload.longitude = position.coords.longitude;
                                        sendPresensiRequest(endpoint, payload);
                                    },
                                    function(geoError) {
                                        html5QrcodeScanner.resume();
                                        isProcessing = false;
                                        Swal.fire({ icon: 'error', title: 'GPS Diperlukan', text: 'Aktifkan lokasi untuk presensi' });
                                    },
                                    {
                                        enableHighAccuracy: true,
                                        timeout: 10000,
                                        maximumAge: 0
                                    }
                                );
                            } else {
                                sendPresensiRequest(endpoint, payload);
                            }
                        } else {
                            html5QrcodeScanner.resume();
                            isProcessing = false;
                            Swal.fire({ icon: 'warning', title: 'Invalid', text: 'QR code tidak valid' });
                        }
                    } catch (e) {
                        html5QrcodeScanner.resume();
                        isProcessing = false;
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Format QR tidak dikenal' });
                    }
                },
                function () { }
            ).catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengakses kamera. Pastikan izin kamera diberikan.' });
            });
        }

        function stopQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner = null;
                    isProcessing = false;
                    document.getElementById('reader').innerHTML = '';
                }).catch(() => { });
            }
        }

        function closeQRModal() {
            stopQRScanner();
            document.getElementById('qrReaderModal').classList.add('hidden');
        }

        document.getElementById('qrReaderModal').addEventListener('click', function (e) {
            if (e.target.classList.contains('bg-slate-900/70')) closeQRModal();
        });

        function loadPresensiData() {
            const storeId = document.getElementById('storeSelect').value;
            fetch('/presensi/api/data?store_id=' + storeId)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('totalStaff').textContent = data.total;
                    document.getElementById('hadirCount').textContent = data.hadir;

                    let html = '';
                    if (data.assignments.length === 0) {
                        html = '<tr><td colspan="6" class="px-5 py-8 text-center text-on-surface-variant"><span class="material-symbols-outlined text-3xl text-outline/30 mb-2 block">event_busy</span><p class="text-sm">Belum ada data</p></td></tr>';
                    } else {
                        data.assignments.forEach(a => {
                            let statusBadge = '';
                            if (a.check_in) {
                                statusBadge = a.status === 'present'
                                    ? '<span class="px-3 py-1 rounded-full text-[11px] font-bold bg-green-100 text-green-700">Hadir</span>'
                                    : '<span class="px-3 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-700">Terlambat</span>';
                            } else {
                                statusBadge = '<span class="px-3 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">Belum</span>';
                            }

                            html += `<tr class="hover:bg-surface-container/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                            ${a.user_name.substring(0, 2).toUpperCase()}
                                        </div>
                                        <span class="font-semibold text-sm">${a.user_name}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-sm">${a.shift_name || '-'}</td>
                                <td class="px-5 py-3 text-sm font-mono text-on-surface-variant">${a.shift_start_time || '-'}</td>
                                <td class="px-5 py-3 text-sm font-mono ${a.status === 'late' ? 'text-red-600 font-bold' : ''}">${a.check_in || '-'}</td>
                                <td class="px-5 py-3 text-sm font-mono">${a.check_out || '-'}</td>
                                <td class="px-5 py-3">${statusBadge}</td>
                            </tr>`;
                        });
                    }
                    document.getElementById('presensiTable').innerHTML = html;
                });
        }

        @if($isLoggedIn && $user->hasMinRole('admin'))
            loadPresensiData();
        @endif

        @if($isLoggedIn && !$user->isSuperAdmin())
            document.addEventListener('DOMContentLoaded', loadMyPresensi);

            function loadMyPresensi() {
                fetch('/presensi/check')
                    .then(res => res.json())
                    .then(data => {
                        const statusEl = document.getElementById('myStatus');
                        const checkInEl = document.getElementById('myCheckIn');
                        const checkOutEl = document.getElementById('myCheckOut');
                        const btnMasuk = document.getElementById('btnMasuk');
                        const btnPulang = document.getElementById('btnPulang');

                        if (data.assignment) {
                            const a = data.assignment;
                            if (a.check_in) {
                                statusEl.textContent = a.status === 'present' ? 'Hadir' : 'Terlambat';
                                statusEl.className = a.status === 'present'
                                    ? 'px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700'
                                    : 'px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700';
                                checkInEl.textContent = a.check_in;
                                btnMasuk.disabled = true;

                                if (a.check_out) {
                                    checkOutEl.textContent = a.check_out;
                                    btnPulang.disabled = true;
                                } else {
                                    btnPulang.disabled = false;
                                }
                            } else {
                                statusEl.textContent = 'Belum';
                                statusEl.className = 'px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500';
                                checkInEl.textContent = '-';
                                checkOutEl.textContent = '-';
                                btnMasuk.disabled = false;
                                btnPulang.disabled = true;
                            }
                        } else {
                            statusEl.textContent = 'Tidak Ada';
                            statusEl.className = 'px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-600';
                            checkInEl.textContent = '-';
                            checkOutEl.textContent = '-';
                            btnMasuk.disabled = true;
                            btnPulang.disabled = true;
                        }
                    });
            }
        @endif
    </script>
</body>

</html>