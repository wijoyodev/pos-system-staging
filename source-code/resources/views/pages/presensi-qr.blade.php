<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    @php
        $today = now()->toDateString();
        $qrData = json_encode([
            'store_id' => $storeId,
            'date' => $today,
        ]);
        
        $hadir = $assignments->whereIn('status', ['present', 'late'])->count();
        $belum = $assignments->where('status', 'scheduled')->count();
        $terlambat = $assignments->where('status', 'late')->count();
    @endphp

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <!-- Top Bar -->
    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">{{ $title ?? 'Page' }}</h1>
      </div>
    </header>


        <div class="space-y-6">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Presensi QR" module="HR" submodule="Attendance" description="Track employee attendance via QR code scanning.">
                <x-slot:actions>
                    <button onclick="document.getElementById('qrModal').classList.remove('hidden')" 
                        class="bg-gradient-to-r from-primary to-primary-container text-white px-5 py-3 rounded-xl font-bold transition-all active:scale-95 flex items-center gap-2 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                        <span class="material-symbols-outlined text-lg">qr_code</span>
                        Tampilkan QR
                    </button>
                </x-slot:actions>
            </x-report-header>
        </div>

            <!-- Summary Cards -->
            <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">groups</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Total Staff</p>
                    <h3 class="font-headline font-extrabold text-2xl text-primary">{{ $assignments->count() }}</h3>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Hadir</p>
                    <h3 class="font-headline font-extrabold text-2xl text-green-600">{{ $hadir }}</h3>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600" style="font-variation-settings: 'FILL' 1;">schedule</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Terlambat</p>
                    <h3 class="font-headline font-extrabold text-2xl text-yellow-600">{{ $terlambat }}</h3>
                </div>
                <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-500" style="font-variation-settings: 'FILL' 1;">person_off</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-1">Belum</p>
                    <h3 class="font-headline font-extrabold text-2xl text-gray-500">{{ $belum }}</h3>
                </div>
            </section>

            <!-- Date & Filter -->
            <section class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center">
                            <span class="material-symbols-outlined text-on-surface-variant">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Tanggal</p>
                            <p class="font-headline font-bold text-on-surface">{{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                    <button onclick="location.reload()" class="px-4 py-2.5 bg-surface-container text-on-surface-variant rounded-xl font-bold hover:bg-surface-container-high transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        Refresh
                    </button>
                </div>
            </section>

            <!-- Data Table -->
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/10 overflow-hidden">
                <div class="p-5 border-b border-outline-variant/10">
                    <h3 class="font-headline font-extrabold text-lg text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">table_chart</span>
                        Data Presensi Staff
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[500px]">
                        <thead>
                            <tr class="bg-surface-container">
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Staff</th>
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Shift</th>
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Jadwal</th>
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Masuk</th>
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Pulang</th>
                                <th class="px-5 py-3 font-headline text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse($assignments as $assignment)
                            <tr class="hover:bg-surface-container/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($assignment->user->name, 0, 2)) }}
                                        </div>
                                        <span class="font-semibold text-sm">{{ $assignment->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-sm">{{ $assignment->shift->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-sm font-mono text-on-surface-variant">{{ $assignment->shift ? \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') : '-' }}</td>
                                <td class="px-5 py-3 text-sm font-mono {{ $assignment->status === 'late' ? 'text-red-600 font-bold' : '' }}">
                                    {{ $assignment->check_in ?? '-' }}
                                </td>
                                <td class="px-5 py-3 text-sm font-mono">{{ $assignment->check_out ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @if($assignment->check_in)
                                        @if($assignment->status === 'present')
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-green-100 text-green-700">Hadir</span>
                                        @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-700">Terlambat</span>
                                        @endif
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">Belum</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-outline/30 mb-2 block">event_busy</span>
                                    <p class="text-on-surface-variant text-sm">Belum ada jadwal shift hari ini</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- QR Modal -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" onclick="document.getElementById('qrModal').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-md bg-surface-container-lowest rounded-3xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-headline font-extrabold text-lg text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">qr_code</span>
                    QR Code Presensi
                </h3>
                <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="p-2 hover:bg-surface-container rounded-xl transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant">close</span>
                </button>
            </div>
            
            <div class="text-center">
                <div class="bg-white p-5 rounded-2xl inline-block mb-4 shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrData) }}" alt="QR Code" class="w-48 h-48">
                </div>
                <p class="font-bold text-on-surface mb-1">{{ $store->branch->name ?? '' }} - {{ $store->name }}</p>
                <p class="text-sm text-on-surface-variant">{{ now()->format('d M Y') }}</p>
            </div>
            
            <div class="flex gap-3 mt-5">
                <form action="/presensi" method="POST" class="flex-1" onsubmit="return addLocation(this, event)">
                    @csrf
                    <input type="hidden" name="latitude" class="lat-input">
                    <input type="hidden" name="longitude" class="lng-input">
                    <button type="submit" class="w-full py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-container transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">login</span>
                        Masuk
                    </button>
                </form>
                <form action="/presensi/checkout" method="POST" class="flex-1" onsubmit="return addLocation(this, event)">
                    @csrf
                    <input type="hidden" name="latitude" class="lat-input">
                    <input type="hidden" name="longitude" class="lng-input">
                    <button type="submit" class="w-full py-3 bg-secondary text-white font-bold rounded-xl hover:bg-secondary-container transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">logout</span>
                        Pulang
                    </button>
                </form>
            </div>
        </div>
    </div>

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
                    btn.innerHTML = form.querySelector('.lat-input').closest('form').action.includes('checkout') 
                        ? '<span class="material-symbols-outlined">logout</span> Pulang'
                        : '<span class="material-symbols-outlined">login</span> Masuk';
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
    </script>
</x-layout>
