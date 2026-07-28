<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full bg-gradient-to-br from-slate-50 to-blue-50/30">

    <!-- Top Bar -->
    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">{{ $title ?? 'Page' }}</h1>
      </div>
    </header>


        <div class="w-full">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Penugasan Shift" module="Schedule" submodule="Assignment" description="Tugaskan kasir ke shift tertentu">
                <x-slot name="actions">
                    @if(auth()->user()->isSuperAdmin() && $stores->count() > 0)
                    <select onchange="location.href='/shift-assignment?store_id='+this.value+'&date={{ $date }}'"
                        class="bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm">
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $currentStoreId == $store->id ? 'selected' : '' }}>
                            {{ $store->branch->name ?? 'Unknown' }} - {{ $store->name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                    <input type="date" id="datePicker" value="{{ $date }}"
                        onchange="location.href='/shift-assignment?store_id={{ $currentStoreId }}&date='+this.value"
                        class="bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm">
                </x-slot>
            </x-report-header>
        </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 font-semibold">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-white/60 p-6 mb-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Tambah Penugasan</h2>
                @if($shifts->count() === 0)
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl mb-4">
                    <p class="text-yellow-700 font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        Belum ada shift. Silakan <a href="/shift-schedule" class="underline hover:text-yellow-900">buat shift terlebih dahulu</a>.
                    </p>
                </div>
                @endif
                @if($kasirs->count() === 0)
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl mb-4">
                    <p class="text-red-700 font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined">person_off</span>
                        Belum ada kasir untuk toko ini.
                    </p>
                </div>
                @endif
                <form action="/shift-assignment" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 bg-slate-50 rounded-xl">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    @if(auth()->user()->isSuperAdmin())
                    <input type="hidden" name="store_id" value="{{ $currentStoreId }}">
                    @endif
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 block">Kasir</label>
                        <select name="user_id" required {{ $kasirs->count() === 0 ? 'disabled' : '' }}
                            class="w-full bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm">
                            <option value="">Pilih Kasir</option>
                            @foreach($kasirs as $kasir)
                            <option value="{{ $kasir->id }}">{{ $kasir->name }} ({{ $kasir->nik }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 block">Shift</label>
                        <select name="shift_id" {{ $shifts->count() === 0 ? 'disabled' : '' }}
                            class="w-full bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm">
                            <option value="">Pilih Shift</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 block">Status</label>
                        <select name="status"
                            class="w-full bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm">
                            <option value="scheduled">Dijadwalkan</option>
                            <option value="present">Hadir</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="late">Terlambat</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2 bg-primary text-white font-bold rounded-lg hover:bg-blue-700">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-white/60 overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Kasir</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Shift</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $assignment)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800">{{ $assignment->user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $assignment->user->nik }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($assignment->shift)
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $assignment->shift->color }}"></div>
                                    <span class="font-semibold">{{ $assignment->shift->name }}</span>
                                </div>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $assignment->shift ? $assignment->shift->time_range : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @switch($assignment->status)
                                    @case('present')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Hadir</span>
                                    @break
                                    @case('absent')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Tidak Hadir</span>
                                    @break
                                    @case('late')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Terlambat</span>
                                    @break
                                    @default
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Dijadwalkan</span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3">
                                <form action="/shift-assignment/{{ $assignment->id }}" method="POST" onsubmit="return confirm('Hapus penugasan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-error hover:bg-error/10 rounded-lg">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada penugasan untuk tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </main>
</x-layout>