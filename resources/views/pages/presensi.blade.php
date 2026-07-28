<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    @php
        $user = auth()->user();
        $today = now()->toDateString();
        $now = now();
        $hasPresensi = $assignment && $assignment->check_in;
        $hasCheckOut = $assignment && $assignment->check_out;
        
        if ($hasPresensi && $assignment && $assignment->shift) {
            $startHour = (int) \Carbon\Carbon::parse($assignment->shift->start_time)->format('H');
            $startMinute = (int) \Carbon\Carbon::parse($assignment->shift->start_time)->format('i');
            $startTime = \Carbon\Carbon::today()->setTime($startHour, $startMinute, 0);
            $checkInTime = \Carbon\Carbon::today()->setTime(
                (int) \Carbon\Carbon::parse($assignment->check_in)->format('H'),
                (int) \Carbon\Carbon::parse($assignment->check_in)->format('i'),
                0
            );
            $correctStatus = $checkInTime->gt($startTime) ? 'late' : 'present';
            $statusDisplay = $correctStatus === 'present' ? 'Tepat Waktu' : 'Terlambat';
            $statusClass = $correctStatus === 'present' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800';
        } else {
            $statusDisplay = '';
            $statusClass = '';
        }
    @endphp

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <!-- Top Bar -->
    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">{{ $title ?? 'Page' }}</h1>
      </div>
    </header>


        <div class="w-full max-w-md">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Presensi Harian" module="HR" submodule="Attendance" description="Track employee daily attendance and working hours." />
        </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600">error</span>
                {{ session('error') }}
            </div>
            @endif

            @if($assignment && $assignment->shift)
            <!-- Shift Info Card -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 shadow-sm border border-outline-variant/10 mb-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background-color: {{ $assignment->shift->color }}20">
                        <span class="material-symbols-outlined text-2xl" style="color: {{ $assignment->shift->color }}" style="font-variation-settings: 'FILL' 1;">schedule</span>
                    </div>
                    <div>
                        <p class="font-headline font-extrabold text-lg text-on-surface">{{ $assignment->shift->name }}</p>
                        <p class="text-sm text-on-surface-variant font-mono">
                            {{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('H:i') }}
                        </p>
                    </div>
                </div>
                
                @if($hasPresensi)
                <div class="p-4 rounded-2xl {{ $hasCheckOut ? 'bg-green-50 border border-green-200' : 'bg-blue-50 border border-blue-200' }}">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined {{ $hasCheckOut ? 'text-green-600' : 'text-blue-600' }}" style="font-variation-settings: 'FILL' 1;">
                            {{ $hasCheckOut ? 'check_circle' : 'pending' }}
                        </span>
                        <span class="font-bold {{ $hasCheckOut ? 'text-green-700' : 'text-blue-700' }}">
                            {{ $hasCheckOut ? 'Selesai' : 'Sudah Check-in' }}
                        </span>
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-bold {{ $statusClass }}">
                            {{ $statusDisplay }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/60 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Masuk</p>
                            <p class="font-mono font-bold text-on-surface">{{ $assignment->check_in }}</p>
                        </div>
                        <div class="bg-white/60 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Pulang</p>
                            <p class="font-mono font-bold text-on-surface">{{ $hasCheckOut ? $assignment->check_out : '-' }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-2xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-yellow-600" style="font-variation-settings: 'FILL' 1;">warning</span>
                    <div>
                        <p class="font-bold text-yellow-700">Belum Presensi</p>
                        <p class="text-xs text-yellow-600">Silakan lakukan presensi masuk</p>
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-8 mb-6 text-center">
                <span class="material-symbols-outlined text-4xl text-yellow-600 mb-3 block" style="font-variation-settings: 'FILL' 1;">event_busy</span>
                <p class="font-headline font-extrabold text-lg text-yellow-800 mb-1">Tidak Ada Jadwal</p>
                <p class="text-sm text-yellow-600">Silakan hubungi admin untuk info lebih lanjut</p>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if($assignment && $assignment->shift && !$hasPresensi)
                <form action="/presensi" method="POST" onsubmit="return addLocation(this, event)">
                    @csrf
                    <input type="hidden" name="latitude" class="lat-input">
                    <input type="hidden" name="longitude" class="lng-input">
                    <button type="submit" 
                        class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-2xl hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined">login</span>
                        Presensi Masuk
                    </button>
                </form>
                @endif

                @if($hasPresensi && !$hasCheckOut)
                <form action="/presensi/checkout" method="POST" onsubmit="return addLocation(this, event)">
                    @csrf
                    <input type="hidden" name="latitude" class="lat-input">
                    <input type="hidden" name="longitude" class="lng-input">
                    <button type="submit" 
                        class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-secondary to-indigo-600 text-white font-bold rounded-2xl hover:shadow-lg hover:shadow-secondary/20 transition-all active:scale-[0.98]">
                        <span class="material-symbols-outlined">logout</span>
                        Presensi Pulang
                    </button>
                </form>
                @endif

                @if($hasPresensi && $hasCheckOut)
                <a href="/" 
                    class="block w-full text-center flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-2xl hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    Ke Halaman POS
                </a>
                @elseif(!$assignment || !$assignment->shift)
                <a href="/dashboard" 
                    class="block w-full text-center flex items-center justify-center gap-3 py-4 bg-surface-container text-on-surface font-bold rounded-2xl hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined">home</span>
                    Ke Dashboard
                </a>
                @endif
            </div>

            <!-- Info Footer -->
            <div class="mt-6 bg-surface-container-low rounded-2xl p-4 text-center">
                <p class="text-xs text-on-surface-variant">
                    QR Code berlaku untuk toko ini pada hari ini<br>
                    Tunjukkan kepada staff untuk scanning
                </p>
            </div>
        </div>
    </main>

    <script>
        function addLocation(form, event) {
            event.preventDefault();
            
            if (!navigator.geolocation) {
                alert('Browser tidak mendukung geolokasi');
                return false;
            }
            
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span> Mendapatkan lokasi...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    form.querySelector('.lat-input').value = position.coords.latitude;
                    form.querySelector('.lng-input').value = position.coords.longitude;
                    form.submit();
                },
                function(error) {
                    btn.disabled = false;
                    const isCheckout = form.action.includes('checkout');
                    btn.innerHTML = isCheckout
                        ? '<span class="material-symbols-outlined">logout</span> Presensi Pulang'
                        : '<span class="material-symbols-outlined">login</span> Presensi Masuk';
                    alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
            
            return false;
        }

        function checkPresensi() {
            fetch('/presensi/check')
                .then(res => res.json())
                .then(data => {
                    if (data.has_presensi && !data.assignment.check_out) {
                        // Auto refresh after check-in
                    }
                });
        }
        
        setInterval(checkPresensi, 30000);
    </script>
</x-layout>
