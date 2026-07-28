<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    @php
        $storeId = $currentStoreId ?? auth()->user()->store_id;
        $today = $referenceDate ?? now();
        $weekStart = $today->copy()->startOfWeek();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $weekStart->copy()->addDays($i);
        }
        $shifts = \App\Models\ShiftSchedule::where('store_id', $storeId)->orderBy('start_time')->get();
        $kasirs = $staff ?? collect();
    @endphp

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Shift Schedule</h1>
      </div>
    </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto overflow-x-hidden no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Shift Schedule" module="Schedule" submodule="Shift" description="Minggu {{ $weekStart->format('d M') }} – {{ $weekStart->copy()->addDays(6)->format('d M, Y') }}">
                <x-slot name="actions">
                    @if(auth()->user()->isSuperAdmin() && $stores->count() > 0)
                    <select onchange="location.href='/shift-schedule?store_id='+this.value"
                        class="bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 outline-none max-w-[180px]">
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                            {{ $store->branch->name ?? 'Unknown' }} - {{ $store->name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                    <div class="flex items-center bg-surface-container-low p-1 rounded-lg shrink-0">
                        <button onclick="location.href='/shift-schedule?{{ auth()->user()->isSuperAdmin() ? 'store_id=' . $storeId . '&' : '' }}date={{ $weekStart->copy()->subWeek()->format('Y-m-d') }}'" class="p-2 hover:bg-surface-bright rounded transition-colors text-primary">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <span class="px-4 font-bold text-sm text-on-surface whitespace-nowrap">Today</span>
                        <button onclick="location.href='/shift-schedule?{{ auth()->user()->isSuperAdmin() ? 'store_id=' . $storeId . '&' : '' }}date={{ $weekStart->copy()->addWeek()->format('Y-m-d') }}'" class="p-2 hover:bg-surface-bright rounded transition-colors text-primary">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                    <button onclick="document.getElementById('addAssignmentModal').classList.remove('hidden')" 
                        class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary-container transition-all shadow-md active:scale-95"
                        {{ $shifts->count() === 0 || $kasirs->count() === 0 ? 'disabled' : '' }}>
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Tambah Penugasan
                    </button>
                </x-slot>
            </x-report-header>
        </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </div>
            @endif

            @if($shifts->count() === 0)
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center gap-3">
                <span class="material-symbols-outlined text-yellow-700">info</span>
                <div>
                    <p class="text-yellow-800 font-semibold">Belum ada shift</p>
                    <form action="/shift-schedule/init-default" method="POST" class="inline">
                        @csrf
                        @if(auth()->user()->isSuperAdmin())
                        <input type="hidden" name="store_id" value="{{ $storeId }}">
                        @endif
                        <button type="submit" class="text-yellow-700 underline font-bold hover:text-yellow-900">Klik untuk buat 3 shift default</button>
                    </form>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-12 gap-8">
                <!-- Main Calendar View -->
                <div class="col-span-12 lg:col-span-9 min-w-0">
                    <div class="bg-surface-container-lowest rounded-xl overflow-x-auto shadow-[0_12px_32px_rgba(0,26,64,0.04)]">
                        <!-- Day Headers -->
                        <div class="grid grid-cols-7 border-b border-surface-container">
                            @foreach($weekDays as $day)
                            <div class="p-4 text-center border-r border-surface-container {{ $day->isToday() ? 'bg-primary-fixed' : 'bg-surface-container-low' }}">
                                <p class="text-[10px] font-bold {{ $day->isToday() ? 'text-on-primary-fixed-variant' : 'text-on-surface-variant' }} uppercase tracking-wider">{{ $day->format('Mon') }}</p>
                                <p class="font-headline text-title-lg font-extrabold {{ $day->isToday() ? 'text-on-primary-fixed-variant' : 'text-on-surface' }}">{{ $day->format('d') }}</p>
                                @if($day->isToday())
                                <div class="mt-1 w-1.5 h-1.5 bg-primary mx-auto rounded-full"></div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <!-- Calendar Body -->
                        <div class="grid grid-cols-7 min-h-[500px]">
                            @foreach($weekDays as $index => $day)
                            <div class="border-r border-surface-container p-3 space-y-3 {{ $day->isToday() ? 'bg-primary-fixed/5' : '' }}">
                                @php
                                    $dayDate = $day->format('Y-m-d');
                                    $dayAssignments = \App\Models\ShiftAssignment::with(['user', 'shift'])
                                        ->where('store_id', $storeId)
                                        ->whereDate('date', $dayDate)
                                        ->orderBy('shift_id')
                                        ->get();
                                @endphp
                                @forelse($dayAssignments as $assignment)
                                @if($assignment->shift)
                                <div class="p-3 rounded-lg space-y-2 group hover:shadow-md transition-all relative"
                                    style="background-color: {{ $assignment->shift->color ?? '#d7e2ff' }}20; border-left: 4px solid {{ $assignment->shift->color ?? '#003f87' }}">
                                    <div class="flex items-center justify-between">
                                        <p class="font-headline text-[13px] font-extrabold" style="color: {{ $assignment->shift->color ?? '#003f87' }}">
                                            {{ $assignment->user?->name ?? 'Tidak Diketahui' }}
                                        </p>
                                        <form action="/shift-assignment/{{ $assignment->id }}" method="POST" onsubmit="return confirm('Hapus penugasan?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 hover:bg-red-100 rounded">
                                                <span class="material-symbols-outlined text-sm text-red-500">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                    <p class="text-[11px] font-bold text-on-surface-variant">{{ $assignment->shift->name }}</p>
                                    <p class="text-[11px] font-medium flex items-center gap-1" style="color: {{ $assignment->shift->color ?? '#003f87' }}">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        {{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('H:i') }}
                                    </p>
                                </div>
                                @endif
                                @empty
                                <div class="text-center py-8 opacity-40">
                                    <span class="material-symbols-outlined text-2xl text-on-surface-variant">event_busy</span>
                                </div>
                                @endforelse
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-span-12 lg:col-span-3 space-y-6">
                    <!-- Staff Availability -->
                    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/10 p-5">
                        <h3 class="font-headline font-extrabold text-lg text-on-surface mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">groups</span>
                            Staff Aktif
                        </h3>
                        <div class="space-y-3 max-h-[400px] overflow-y-auto no-scrollbar pr-1">
                            @forelse($kasirs as $kasir)
                            <div class="bg-surface-container rounded-xl p-3 hover:bg-surface-container-high transition-colors">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($kasir->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-sm text-on-surface truncate leading-tight">{{ $kasir->name }}</p>
                                        <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-tight">{{ $kasir->role_label }}</p>
                                    </div>
                                </div>
                                @php
                                    $weeklyHours = \App\Models\ShiftAssignment::where('user_id', $kasir->id)
                                        ->whereBetween('date', [$weekStart->format('Y-m-d'), $weekStart->copy()->addDays(6)->format('Y-m-d')])
                                        ->count() * 8;
                                    $progress = min(100, $weeklyHours * 2.5);
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-surface-container-low h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-primary h-full rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-on-surface-variant whitespace-nowrap">{{ $weeklyHours }}h</span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <span class="material-symbols-outlined text-3xl text-outline/30 mb-2 block">person_off</span>
                                <p class="text-sm text-on-surface-variant">Belum ada staff</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Shift Legend -->
                    @if($shifts->count() > 0)
                    <div class="bg-surface-container-low rounded-xl p-6">
                        <h3 class="font-headline text-title-lg font-extrabold text-on-surface mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            Daftar Shift
                        </h3>
                        <button onclick="document.getElementById('shiftListModal').classList.remove('hidden')"
                            class="w-full flex items-center justify-between p-4 bg-surface-container-lowest rounded-lg hover:bg-surface-bright transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-8 rounded" style="background-color: {{ $shifts->first()->color }}"></div>
                                <div class="text-left">
                                    <p class="font-bold text-sm text-on-surface">Lihat Semua Shift</p>
                                    <p class="text-xs text-on-surface-variant">{{ $shifts->count() }} shift tersedia</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                        </button>
                    </div>
                    @endif

                    <!-- Quick Insights -->
                    <div class="grid grid-cols-1 gap-4">
                        @php
                            $todayAssignments = \App\Models\ShiftAssignment::where('store_id', $storeId)
                                ->whereDate('date', $today->format('Y-m-d'))
                                ->count();
                            $totalStaff = $kasirs->count();
                            $coverage = $totalStaff > 0 ? round(($todayAssignments / $totalStaff) * 100) : 0;
                        @endphp
                        <div class="bg-primary p-6 rounded-xl text-white shadow-lg overflow-hidden relative">
                            <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-white/10 text-[120px]">event_available</span>
                            <p class="text-xs font-bold uppercase tracking-widest text-primary-container mb-1">Coverage Hari Ini</p>
                            <h4 class="font-headline text-3xl font-extrabold mb-4">{{ $coverage }}%</h4>
                            <p class="text-[11px] opacity-80 leading-relaxed">{{ $todayAssignments }} dari {{ $totalStaff }} staff ditugaskan hari ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Assignment Modal -->
    <div id="addAssignmentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('addAssignmentModal').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-md bg-surface-container-lowest rounded-2xl shadow-2xl p-6">
            <h3 class="font-headline text-title-lg font-extrabold text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">assignment_add</span>
                Tambah Penugasan Shift
            </h3>
            <form action="/shift-assignment" method="POST" class="space-y-4">
                @csrf
                @if(auth()->user()->isSuperAdmin())
                <input type="hidden" name="store_id" value="{{ $storeId }}">
                @endif
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1 block">Tanggal</label>
                    <input type="date" name="date" value="{{ $today->format('Y-m-d') }}" required
                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1 block">Staff</label>
                    <select name="user_id" required
                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="">Pilih Staff</option>
                        @foreach($kasirs as $kasir)
                        <option value="{{ $kasir->id }}">{{ $kasir->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1 block">Shift</label>
                    <select name="shift_id" required
                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="">Pilih Shift</option>
                        @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1 block">Status</label>
                    <select name="status"
                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="scheduled">Dijadwalkan</option>
                        <option value="present">Hadir</option>
                        <option value="absent">Tidak Hadir</option>
                        <option value="late">Terlambat</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addAssignmentModal').classList.add('hidden')"
                        class="px-5 py-2.5 text-on-surface-variant hover:bg-surface-bright rounded-lg font-bold transition-colors">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary-container transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('addAssignmentModal').classList.add('hidden');
                document.getElementById('shiftListModal').classList.add('hidden');
            }
        });
    </script>

    <!-- Shift List Modal -->
    <div id="shiftListModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('shiftListModal').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl p-6 max-h-[80vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline text-title-lg font-extrabold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">schedule</span>
                    Daftar Shift
                </h3>
                <button onclick="document.getElementById('shiftListModal').classList.add('hidden')" class="p-2 hover:bg-surface-bright rounded-full">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="space-y-3 overflow-y-auto pr-2 flex-1">
                @foreach($shifts as $shift)
                <div class="flex items-center gap-3 p-3 bg-surface-container-lowest rounded-lg">
                    <div class="w-3 h-8 rounded" style="background-color: {{ $shift->color }}"></div>
                    <div>
                        <p class="font-bold text-sm text-on-surface">{{ $shift->name }}</p>
                        <p class="text-xs text-on-surface-variant">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</p>
                    </div>
                    <span class="ml-auto text-xs px-2 py-1 rounded bg-surface-container text-on-surface-variant capitalize">{{ $shift->shift_key }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>