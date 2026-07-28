<x-layout>
    <x-slot:title>Stock Opname</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full bg-gradient-to-br from-slate-50 via-white to-blue-50/60">

    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Stock Opname</h1>
      </div>
    </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="mb-6 lg:mb-8">
                <x-report-header title="Stock Opname" module="Inventory" submodule="Stock Opname" description="Manage physical stock counts, track discrepancies, and reconcile inventory." />
            </div>

        @php
            $storeId = auth()->user()->store_id;
            $hasSchedule = \App\Models\StockOpnameSession::hasActiveSchedule($storeId);
            $activeSchedule = \App\Models\StockOpnameSession::getActiveSchedule($storeId);
        @endphp

        @if(!auth()->user()->isSuperAdmin() && !$hasSchedule)
        <div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
            <div class="w-28 h-28 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-14 h-14 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-700 mb-2">Tidak Ada Jadwal SO</h2>
            <p class="text-slate-500 max-w-md mb-6">Saat ini tidak ada jadwal Stock Opname untuk toko Anda. Silakan hubungi Super Admin untuk membuat jadwal SO.</p>
            <div class="px-6 py-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-700 text-sm">
                <span class="font-semibold">Catatan:</span> Perubahan stock produk tidak dapat dilakukan tanpa jadwal SO.
            </div>
        </div>
        @else
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div class="flex items-center gap-4">
                @if($activeSchedule)
                <div class="flex items-center gap-3 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-2xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-emerald-700 text-sm font-semibold">{{ $activeSchedule->name }}</span>
                    <span class="text-emerald-500/60 text-xs">| {{ \Carbon\Carbon::parse($activeSchedule->planned_date)->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-8">
            <div class="overflow-x-auto no-scrollbar pb-1">
            <nav class="flex gap-1 bg-white/80 backdrop-blur-md p-1.5 rounded-2xl border border-slate-200/60 shadow-sm w-max min-w-full sm:w-fit">
                <button onclick="showTab('sessions')" id="tab-sessions" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold shadow-md shadow-blue-500/20 transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Sessions
                    </span>
                </button>
                @if(auth()->user()->isSuperAdmin())
                <button onclick="showTab('create')" id="tab-create" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Session
                    </span>
                </button>
                @endif
                <button onclick="showTab('detail')" id="tab-detail" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Detail
                    </span>
                </button>
                <button onclick="showTab('entry')" id="tab-entry" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Input Data
                    </span>
                </button>
                <button onclick="showTab('discrepancies')" id="tab-discrepancies" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        Selisih
                    </span>
                </button>
                <button onclick="showTab('edit-data')" id="tab-edit-data" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Data
                    </span>
                </button>
                <button onclick="showTab('fixed')" id="tab-fixed" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Fixed
                    </span>
                </button>
                <button onclick="showTab('adjustments')" id="tab-adjustments" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-all duration-200 text-sm whitespace-nowrap">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        Adjustments
                    </span>
                </button>
            </nav>
            </div>
        </div>

        <!-- Tab: Sessions -->
        <div id="panel-sessions" class="block">
            <div class="flex flex-col sm:flex-row gap-3 mb-6 bg-white/60 backdrop-blur-md p-5 rounded-2xl border border-slate-200/50 shadow-sm">
                <div class="relative flex-1">
                    <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="search" placeholder="Cari session..." class="w-full bg-white text-slate-700 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl pl-10 pr-4 py-2.5 transition-all placeholder-slate-400">
                </div>
                <select id="statusFilter" class="bg-white text-slate-700 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-2.5 transition-all cursor-pointer appearance-none">
                    <option value="">Semua Status</option>
                    <option value="PLANNED">Planned</option>
                    <option value="CETAK_KERTAS">Cetak Kertas</option>
                    <option value="ENTRY">Entry</option>
                    <option value="CHECK_DATA">Check Data</option>
                    <option value="PROSES">Proses</option>
                    <option value="CETAK_SELISIH">Cetak Selisih</option>
                    <option value="EDIT_DATA">Edit Data</option>
                    <option value="FIXED">Fixed</option>
                </select>
                @if(auth()->user()->isSuperAdmin())
                <select id="storeFilter" class="bg-white text-slate-700 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-2.5 transition-all cursor-pointer appearance-none">
                    <option value="">Semua Toko</option>
                    @foreach(\App\Models\Store::where('status', 'active')->with('branch')->get() as $store)
                    <option value="{{ $store->id }}">{{ $store->branch->name ?? 'Unknown' }} - {{ $store->name }}</option>
                    @endforeach
                </select>
                @endif
                <button onclick="loadSessions()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-8 py-2.5 rounded-xl font-semibold shadow-md shadow-blue-500/20 transition-all transform hover:scale-[1.02] active:scale-100 flex items-center gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
            </div>

            <div id="sessionsContainer" class="space-y-3">
                <div id="sessionsList"></div>
                <div id="sessionsLoading" class="hidden space-y-3">
                    @for($i = 0; $i < 3; $i++)
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 p-5 animate-pulse">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <div class="h-5 bg-slate-200 rounded-lg w-48"></div>
                                <div class="h-3 bg-slate-100 rounded-lg w-32"></div>
                            </div>
                            <div class="h-8 bg-slate-200 rounded-xl w-24"></div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Tab: Create -->
        <div id="panel-create" class="hidden">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Buat Session Stock Opname</h2>
                            <p class="text-sm text-slate-500">Buat jadwal stock opname baru</p>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Session</label>
                            <input type="text" id="formName" required class="w-full bg-white text-slate-800 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-3 transition-all placeholder-slate-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                            <textarea id="formDesc" rows="3" class="w-full bg-white text-slate-800 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-3 transition-all placeholder-slate-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Rencana</label>
                            <input type="date" id="formDate" required class="w-full bg-white text-slate-800 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-3 transition-all">
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Toko</label>
                            <select id="formStoreId" required class="w-full bg-white text-slate-800 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl px-4 py-3 transition-all appearance-none">
                                <option value="">Pilih Toko</option>
                                @foreach(\App\Models\Store::where('status', 'active')->with('branch')->get() as $store)
                                <option value="{{ $store->id }}">{{ $store->branch->name ?? 'Unknown' }} - {{ $store->name }} ({{ $store->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <button onclick="createSession()" class="w-full mt-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3.5 rounded-xl shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform hover:scale-[1.01] active:scale-100 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Session
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Detail -->
        <div id="panel-detail" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-xl text-slate-800" id="detailTitle">Session Detail</h2>
                            <p class="text-sm text-slate-500" id="detailStatus"></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="goToEntryTab()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Input Data
                        </button>
                        <div class="relative" id="statusDropdownContainer">
                            <button onclick="toggleStatusDropdown()" class="bg-white text-slate-700 px-4 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all border border-slate-300 hover:bg-slate-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                Ubah Status
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="statusDropdown" class="hidden absolute right-0 mt-2 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50 min-w-[180px]">
                                <button onclick="changeStatus('PLANNED')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">PLANNED</button>
                                <button onclick="changeStatus('CETAK_KERTAS')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">CETAK_KERTAS</button>
                                <button onclick="changeStatus('ENTRY')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">ENTRY</button>
                                <button onclick="changeStatus('CHECK_DATA')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">CHECK_DATA</button>
                                <button onclick="changeStatus('PROSES')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">PROSES</button>
                                <button onclick="changeStatus('CETAK_SELISIH')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">CETAK_SELISIH</button>
                                <button onclick="changeStatus('EDIT_DATA')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">EDIT_DATA</button>
                                <button onclick="changeStatus('FIXED')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">FIXED</button>
                                <button onclick="changeStatus('ADJUST')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">ADJUST</button>
                                <button onclick="changeStatus('POSTED')" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 text-sm text-slate-700 transition-colors">POSTED</button>
                            </div>
                        </div>
                        <button onclick="showTab('sessions')" class="text-slate-400 hover:text-slate-600 p-2.5 rounded-xl hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                
                <div id="workflowButtons" class="flex flex-wrap gap-3"></div>
            </div>

            <div id="detailContent" class="space-y-4"></div>
            <div id="detailLoading" class="hidden grid grid-cols-2 md:grid-cols-4 gap-4">
                @for($i = 0; $i < 4; $i++)
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-5 border border-slate-200/60 animate-pulse">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-200"></div>
                        <div class="h-3 bg-slate-200 rounded w-20"></div>
                    </div>
                    <div class="h-8 bg-slate-200 rounded w-16"></div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Tab: Entry -->
        <div id="panel-entry" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-bold text-xl text-slate-800">Input Stock Fisik</h2>
                            <p class="text-sm text-slate-500">Masukkan jumlah fisik untuk setiap produk</p>
                        </div>
                    </div>
                    <div class="relative w-full md:w-72">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="entrySearch" placeholder="Cari produk..." class="w-full bg-white text-slate-700 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl pl-10 pr-4 py-2.5 transition-all placeholder-slate-400" onkeyup="filterEntryProducts()">
                    </div>
                </div>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden mb-6">
                <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
                    <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100/80 text-slate-600 font-medium sticky top-0 backdrop-blur-md z-10 border-b border-slate-200/60">
                            <tr>
                                <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Kode</th>
                                <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Produk</th>
                                <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Fisik</th>
                            </tr>
                        </thead>
                        <tbody id="entryProductsTable" class="divide-y divide-slate-100">
                            <tr id="entryLoading" class="hidden"><td colspan="3" class="px-5 py-4">
                                <div class="space-y-3">
                                    @for($i = 0; $i < 5; $i++)
                                    <div class="flex items-center gap-4 animate-pulse">
                                        <div class="h-4 bg-slate-200 rounded w-16"></div>
                                        <div class="h-4 bg-slate-200 rounded flex-1"></div>
                                        <div class="h-8 bg-slate-200 rounded w-20"></div>
                                    </div>
                                    @endfor
                                </div>
                            </td></tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <button onclick="submitEntry()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 rounded-2xl shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform hover:scale-[1.01] active:scale-100 text-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Simpan Data Fisik
            </button>
        </div>

        <!-- Tab: Discrepancies -->
        <div id="panel-discrepancies" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-slate-800">Laporan Selisih</h2>
                        <p class="text-sm text-slate-500">Produk dengan perbedaan stok</p>
                    </div>
                </div>
                <button onclick="window.open('/stock-opname/' + selectedSessionId + '/print-selisih', '_blank')" class="bg-orange-600 hover:bg-orange-500 text-white px-5 py-2.5 rounded-xl font-semibold flex items-center gap-2 transition-all shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan Selisih
                </button>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100/80 text-slate-600 font-medium border-b border-slate-200/60">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Kode</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Produk</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">System</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Fisik</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Selisih</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="discrepanciesTable" class="divide-y divide-slate-100">
                        <tr id="discrepanciesLoading" class="hidden"><td colspan="6" class="px-5 py-4">
                            <div class="space-y-3">
                                @for($i = 0; $i < 5; $i++)
                                <div class="flex items-center gap-4 animate-pulse">
                                    <div class="h-4 bg-slate-200 rounded w-16"></div>
                                    <div class="h-4 bg-slate-200 rounded flex-1"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-20"></div>
                                </div>
                                @endfor
                            </div>
                        </td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Tab: Edit Data -->
        <div id="panel-edit-data" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-slate-800">Perbaikan Data Fisik</h2>
                        <p class="text-sm text-slate-500">Koreksi perhitungan fisik jika terdapat kesalahan</p>
                    </div>
                </div>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="editSearch" placeholder="Cari produk..." class="w-full bg-white text-slate-700 border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 rounded-xl pl-10 pr-4 py-2.5 transition-all placeholder-slate-400" onkeyup="filterEditProducts()">
                </div>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden mb-6">
                <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100/80 text-slate-600 font-medium border-b border-slate-200/60">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Kode</th>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Produk</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">System</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Fisik (Edit)</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="editProductsTable" class="divide-y divide-slate-100">
                        <tr id="editLoading" class="hidden"><td colspan="5" class="px-5 py-4">
                            <div class="space-y-3">
                                @for($i = 0; $i < 5; $i++)
                                <div class="flex items-center gap-4 animate-pulse">
                                    <div class="h-4 bg-slate-200 rounded w-16"></div>
                                    <div class="h-4 bg-slate-200 rounded flex-1"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-8 bg-slate-200 rounded w-20"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                </div>
                                @endfor
                            </div>
                        </td></tr>
                    </tbody>
                </table>
                </div>
            </div>
            <button onclick="submitEdit()" class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-bold py-3.5 rounded-2xl shadow-md shadow-orange-500/20 hover:shadow-lg transition-all transform hover:scale-[1.01] active:scale-100 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Simpan Perubahan
            </button>
        </div>

        <!-- Tab: Fixed -->
        <div id="panel-fixed" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-slate-800">Konfirmasi Fixed</h2>
                        <p class="text-sm text-slate-500">Kunci data dan lanjutkan ke penyesuaian stok</p>
                    </div>
                </div>
                <button onclick="doAction('fixed')" class="w-full bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white font-bold py-3.5 px-6 rounded-xl shadow-md shadow-red-500/20 hover:shadow-lg transition-all transform hover:scale-[1.01] active:scale-100 text-base flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Konfirmasi & Kunci Data (Fixed)
                </button>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100/80 text-slate-600 font-medium border-b border-slate-200/60">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Produk</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">System</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Fisik</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="fixedTable" class="divide-y divide-slate-100">
                        <tr id="fixedLoading" class="hidden"><td colspan="4" class="px-5 py-4">
                            <div class="space-y-3">
                                @for($i = 0; $i < 4; $i++)
                                <div class="flex items-center gap-4 animate-pulse">
                                    <div class="h-4 bg-slate-200 rounded flex-1"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                </div>
                                @endfor
                            </div>
                        </td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Tab: Adjustments -->
        <div id="panel-adjustments" class="hidden animate-fadeIn">
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-slate-800">Penyesuaian Stok</h2>
                        <p class="text-sm text-slate-500">Sesuaikan seluruh selisih ke stok toko</p>
                    </div>
                </div>
                <button onclick="doAction('adjust')" class="w-full bg-gradient-to-r from-emerald-600 to-green-700 hover:from-emerald-500 hover:to-green-600 text-white font-bold py-3.5 px-6 rounded-xl shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all transform hover:scale-[1.01] active:scale-100 text-base flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    Sesuaikan Semua Stock (Adjust)
                </button>
            </div>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100/80 text-slate-600 font-medium border-b border-slate-200/60">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs uppercase tracking-wider">Produk</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">System</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Fisik</th>
                            <th class="text-right px-5 py-3.5 text-xs uppercase tracking-wider">Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="adjustmentsTable" class="divide-y divide-slate-100">
                        <tr id="adjustmentsLoading" class="hidden"><td colspan="4" class="px-5 py-4">
                            <div class="space-y-3">
                                @for($i = 0; $i < 4; $i++)
                                <div class="flex items-center gap-4 animate-pulse">
                                    <div class="h-4 bg-slate-200 rounded flex-1"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                    <div class="h-4 bg-slate-200 rounded w-12"></div>
                                </div>
                                @endfor
                            </div>
                        </td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-slate-400 text-xs">
            <p>Stock Opname System v2.0</p>
        </div>
        @endif
        </div>
    </main>

    <style>
    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.3);
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.5);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }
    .table-row-hover {
        transition: all 0.15s ease;
    }
    .table-row-hover:hover {
        background: rgba(241, 245, 249, 0.8);
    }
    </style>

    <script>
    let currentTab = 'sessions';
    let selectedSessionId = null;
    let sessions = [];
    let entryProducts = [];
    let counts = [];
    let adjustments = [];
    let entryData = {};

    function showTab(tab) {
        currentTab = tab;
        
        document.querySelectorAll('[id^="panel-"]').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('animate-fadeIn');
        });
        
        document.querySelectorAll('[id^="tab-"]').forEach(el => {
            el.classList.remove('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/20', 'font-semibold');
            el.classList.add('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900', 'font-medium');
        });
        
        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.getElementById('panel-' + tab).classList.add('animate-fadeIn');
        
        const tabEl = document.getElementById('tab-' + tab);
        tabEl.classList.remove('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900', 'font-medium');
        tabEl.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-500/20', 'font-semibold');

        if (tab === 'sessions') loadSessions();
        if (tab === 'detail' && selectedSessionId) loadDetail();
        if (tab === 'entry' && selectedSessionId) loadEntryProducts();
        if (tab === 'edit-data' && selectedSessionId) loadEditProducts();
        if (tab === 'fixed' && selectedSessionId) loadFixedProducts();
        if (tab === 'discrepancies' && selectedSessionId) loadDiscrepancies();
        if (tab === 'adjustments' && selectedSessionId) loadAdjustments();
    }

    function showLoading(show) {
        const loading = document.getElementById('sessionsLoading');
        const list = document.getElementById('sessionsList');
        if (show) {
            loading.classList.remove('hidden');
            list.innerHTML = '';
        } else {
            loading.classList.add('hidden');
        }
    }

    async function loadSessions() {
        const storeFilter = document.getElementById('storeFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter').value;
        const search = document.getElementById('search').value.toLowerCase();

        let url = '/api/opname/sessions?';
        if (storeFilter) url += 'store_id=' + storeFilter + '&';
        if (statusFilter) url += 'status=' + statusFilter;

        showLoading(true);

        try {
            const res = await fetch(url);
            if (!res.ok) {
                console.error('API error:', res.status);
                document.getElementById('sessionsList').innerHTML = '<div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-red-200 p-8 text-center"><p class="text-red-500 flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Error: ' + res.status + '</p></div>';
                showLoading(false);
                return;
            }
            const data = await res.json();
            sessions = data.data || data;
        } catch(e) {
            console.error('Load sessions error:', e);
            sessions = [];
        }

        showLoading(false);

        let html = '';

        const filtered = sessions.filter(s => {
            const matchSearch = !search || s.name.toLowerCase().includes(search);
            return matchSearch;
        });

        const activeSessions = filtered.filter(s => !['ADJUST', 'POSTED'].includes(s.status));

        if (activeSessions.length === 0) {
            html = `
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 p-12 text-center">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-600 mb-1">Tidak ada session aktif</h3>
                    <p class="text-slate-400 text-sm">Buat session baru untuk memulai stock opname</p>
                </div>`;
        } else {
            activeSessions.forEach((s, index) => {
                const storeName = s.store ? (s.store.branch?.name || '') + ' - ' + s.store.name : 'Unknown';
                html += `
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 hover:border-blue-200 p-5 transition-all duration-200 hover:shadow-lg hover:shadow-blue-100/50 cursor-pointer" onclick="selectSession(${s.id})" style="animation: fadeIn 0.2s ease-out ${index * 0.05}s both;">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 flex items-center justify-center shrink-0 mt-1">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-base">${s.name}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">${s.description || ''}</p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            ${storeName}
                                        </span>
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            ${s.planned_date}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 sm:shrink-0">
                                <span class="status-badge ${statusClass(s.status)}">${s.status.replace('_', ' ')}</span>
                                <span class="text-blue-600 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all font-semibold text-sm flex items-center gap-1">
                                    Pilih
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>`;
            });
        }
        document.getElementById('sessionsList').innerHTML = html;
    }

    async function createSession() {
        const storeSelect = document.getElementById('formStoreId');
        const data = {
            name: document.getElementById('formName').value,
            description: document.getElementById('formDesc').value,
            planned_date: document.getElementById('formDate').value,
            store_id: storeSelect ? storeSelect.value : null
        };

        if (!data.name || !data.planned_date || !data.store_id) {
            Swal.fire('Peringatan', 'Mohon isi nama, tanggal, dan pilih toko', 'warning');
            return;
        }

        try {
            const res = await fetch('/api/opname/sessions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            });

            if (!res.ok) {
                const err = await res.json();
                Swal.fire('Error', 'Error: ' + (err.message || err.error), 'error');
                return;
            }

            document.getElementById('formName').value = '';
            document.getElementById('formDesc').value = '';
            document.getElementById('formDate').value = '';
            if (storeSelect) storeSelect.value = '';
            
            await loadSessions();
            showTab('sessions');
            Swal.fire('Berhasil', 'Session berhasil dibuat', 'success');
        } catch(e) {
            console.error('Create session error:', e);
            Swal.fire('Error', 'Error creating session', 'error');
        }
    }

    async function selectSession(id) {
        const session = sessions.find(s => s.id === id);
        if (session && ['ADJUST', 'POSTED'].includes(session.status)) {
            Swal.fire('Info', 'Session ini sudah selesai dan tidak dapat diakses lagi.', 'info');
            return;
        }
        selectedSessionId = id;
        showTab('detail');
    }

    async function goToEntryTab() {
        showTab('entry');
        await loadEntryProducts();
    }

    function toggleStatusDropdown() {
        const dropdown = document.getElementById('statusDropdown');
        dropdown.classList.toggle('hidden');
        document.addEventListener('click', closeStatusDropdown);
    }

    function closeStatusDropdown(e) {
        const dropdown = document.getElementById('statusDropdown');
        if (!e.target.closest('#statusDropdown') && !e.target.closest('button[onclick*="toggleStatusDropdown"]')) {
            dropdown.classList.add('hidden');
            document.removeEventListener('click', closeStatusDropdown);
        }
    }

    async function changeStatus(newStatus) {
        document.getElementById('statusDropdown').classList.add('hidden');
        
        const result = await Swal.fire({
            title: 'Ubah Status?',
            text: `Session akan diubah ke ${newStatus}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b'
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/change-status/' + newStatus, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }

            await loadDetail();
            await loadSessions();
            Swal.fire('Berhasil', 'Status diubah ke ' + newStatus, 'success');
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan: ' + e.message, 'error');
        }
    }

    async function loadDetail() {
        if (!selectedSessionId) return;

        document.getElementById('detailContent').classList.add('hidden');
        document.getElementById('detailLoading').classList.remove('hidden');

        const res = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await res.json();

        document.getElementById('detailContent').classList.remove('hidden');
        document.getElementById('detailLoading').classList.add('hidden');

        counts = session.counts || [];
        adjustments = session.adjustments || [];

        document.getElementById('detailTitle').textContent = session.name;
        document.getElementById('detailStatus').innerHTML = '<span class="status-badge ' + statusClass(session.status) + '">' + session.status.replace('_', ' ') + '</span>';

        const statusDropdownContainer = document.getElementById('statusDropdownContainer');
        if (statusDropdownContainer) {
            statusDropdownContainer.classList.toggle('hidden', ['ADJUST', 'POSTED'].includes(session.status));
        }
        
        const totalItems = counts.length;
        const matchedCount = counts.filter(c => c.variance_qty === 0).length;
        const varianceCount = counts.filter(c => c.variance_qty !== 0).length;
        const varianceValue = counts.reduce((sum, c) => sum + (c.variance_value || 0), 0);

        let html = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-5 border border-slate-200/60">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Items</p>
                    </div>
                    <p class="text-3xl font-black text-slate-800">${totalItems}</p>
                </div>
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-5 border border-slate-200/60">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Sesuai</p>
                    </div>
                    <p class="text-3xl font-black text-emerald-600">${matchedCount}</p>
                </div>
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-5 border border-slate-200/60">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Selisih</p>
                    </div>
                    <p class="text-3xl font-black text-red-600">${varianceCount}</p>
                </div>
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-5 border border-slate-200/60">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Nilai Selisih</p>
                    </div>
                    <p class="text-3xl font-black text-orange-600">${formatCurrency(varianceValue)}</p>
                </div>
            </div>
        `;
        
        document.getElementById('detailContent').innerHTML = html;

        const workflowDiv = document.getElementById('workflowButtons');
        let buttons = '';

        const statusActions = {
            'PLANNED': [{action: 'print-kertas', label: '1. Cetak Kertas', desc: 'Cetak lembar kerja', color: 'yellow'}],
            'CETAK_KERTAS': [{action: 'entry', label: '2. Input Data', desc: 'Input stok fisik', color: 'blue'}],
            'ENTRY': [{action: 'check-data', label: '3. Proses Data', desc: 'Hitung selisih', color: 'blue'}],
            'CHECK_DATA': [{action: 'proses', label: '3. Proses Data', desc: 'Hitung selisih', color: 'blue'}],
            'PROSES': [{action: 'print-selisih', label: '4. Cetak Selisih', desc: 'Cetak laporan', color: 'orange'}],
            'CETAK_SELISIH': [{action: 'goto-edit', label: '5. Edit Data', desc: 'Koreksi manual', color: 'orange'}, {action: 'fixed', label: '6. Fixed', desc: 'Langsung kunci', color: 'red'}],
            'EDIT_DATA': [{action: 'goto-edit', label: '5. Edit Data', desc: 'Koreksi manual', color: 'orange'}, {action: 'fixed', label: '6. Fixed', desc: 'Kunci data', color: 'red'}],
            'FIXED': [{action: 'adjust', label: '7. Adjust Stock', desc: 'Sesuaikan stok', color: 'green'}],
            'ADJUST': [{action: 'adjust', label: '7. Adjust Stock', desc: 'Sesuaikan stok', color: 'green'}],
            'POSTED': []
        };
        
        const bgColors = {
            'yellow': 'from-yellow-500 to-amber-600 hover:from-yellow-400 hover:to-amber-500 shadow-yellow-500/20',
            'blue': 'from-blue-600 to-indigo-700 hover:from-blue-500 hover:to-indigo-600 shadow-blue-500/20',
            'orange': 'from-orange-500 to-amber-600 hover:from-orange-400 hover:to-amber-500 shadow-orange-500/20',
            'red': 'from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 shadow-red-500/20',
            'green': 'from-emerald-600 to-green-700 hover:from-emerald-500 hover:to-green-600 shadow-emerald-500/20'
        };
        const icons = {
            'print-kertas': 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
            'entry': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'check-data': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'proses': 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'print-selisih': 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
            'goto-edit': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'fixed': 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'adjust': 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'
        };
        
        const actions = statusActions[session.status] || [];
        
        actions.forEach(a => {
            buttons += `<button onclick="doWorkflow('${a.action}')" class="bg-gradient-to-r ${bgColors[a.color]} text-white px-5 py-3 rounded-xl font-semibold flex flex-col items-start gap-0.5 shadow-md transition-all transform hover:scale-[1.02] active:scale-100 min-w-[140px]">
                <span class="flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[a.action]}"/></svg>
                    ${a.label}
                </span>
                <span class="text-[10px] opacity-70 font-normal">${a.desc}</span>
            </button>`;
        });

        if (session.status !== 'POSTED' && session.status !== 'CANCELLED') {
            buttons += `<button onclick="cancelSession()" class="bg-white hover:bg-slate-50 text-slate-700 px-5 py-3 rounded-xl font-semibold flex items-center gap-2 transition-all border border-slate-300 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel
            </button>`;
        }

        workflowDiv.innerHTML = buttons;
    }

    async function doWorkflow(action) {
        if (action === 'print-kertas') {
            window.open('/stock-opname/' + selectedSessionId + '/print-kertas', '_blank');
            
            const result = await Swal.fire({
                title: 'Cetak Kertas?',
                text: 'Apakah kertas kerja sudah dicetak? Lanjut ke Input Data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjut',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            
            if (!result.isConfirmed) return;
            
            try {
                const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/entry', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                    return;
                }
                
                await loadDetail();
                await loadSessions();
                showTab('entry');
                await loadEntryProducts();
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
            }
            return;
        } else if (action === 'print-selisih') {
            window.open('/stock-opname/' + selectedSessionId + '/print-selisih', '_blank');
            
            const result = await Swal.fire({
                title: 'Cetak Selisih?',
                text: 'Apakah laporan selisih sudah dicetak? Lanjut ke Edit Data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjut',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            
            if (!result.isConfirmed) return;
            
            try {
                const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/edit-data', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                    return;
                }
                
                await loadDetail();
                await loadSessions();
                showTab('edit-data');
                await loadEditProducts();
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
            }
            return;
        } else if (action === 'entry') {
            try {
                const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/entry', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                    return;
                }
                
                await loadDetail();
                await loadSessions();
                showTab('entry');
                await loadEntryProducts();
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
            }
            return;
        } else if (action === 'check-data') {
            try {
                const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/check-data', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    Swal.fire('Error', 'Gagal mengecek data: ' + (err?.error || err?.message || res.statusText), 'error');
                    return;
                }
                
                const data = await res.json();
                
                if (data.unentered_count > 0) {
                    const confirmResult = await Swal.fire({
                        title: 'Data Belum Lengkap',
                        text: `Terdapat ${data.unentered_count} produk yang BELUM diinput fisiknya (dari total ${data.total_products} produk).`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tetap Lanjutkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#64748b'
                    });
                    if (!confirmResult.isConfirmed) return;
                } else {
                    const confirmResult = await Swal.fire({
                        title: 'Semua Data Lengkap',
                        text: 'Semua produk telah diinput (100%). Lanjut ke proses selisih?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#64748b'
                    });
                    if (!confirmResult.isConfirmed) return;
                }
                
                const prosesRes = await fetch('/api/opname/sessions/' + selectedSessionId + '/proses', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!prosesRes.ok) {
                    const err = await prosesRes.json().catch(() => null);
                    Swal.fire('Error', 'Gagal lanjut ke PROSES: ' + (err?.error || err?.message || prosesRes.statusText), 'error');
                    return;
                }
                
                await loadDetail();
                await loadSessions();
                showTab('detail');
                return;
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
                return;
            }
        } else if (action === 'proses') {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/proses', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            
            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal proses: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }
            
            await loadDetail();
            await loadSessions();
            showTab('detail');
            return;
        } else if (action === 'goto-edit') {
            showTab('edit-data');
            await loadEditProducts();
            return;
        } else if (action === 'fixed') {
            const result = await Swal.fire({
                title: 'Konfirmasi Fixed',
                text: 'Data stock akan dikunci dan tidak bisa diedit lagi. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kunci Data',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            });
            
            if (!result.isConfirmed) return;
            
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/fixed', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            
            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }
            
            await loadDetail();
            await loadSessions();
            showTab('adjustments');
            await loadAdjustments();
            Swal.fire('Berhasil', 'Data berhasil dikunci!', 'success');
            return;
        } else if (action === 'adjust') {
            showTab('adjustments');
            await loadAdjustments();
            return;
        } else {
            const result = await Swal.fire({
                title: 'Lanjutkan?',
                text: 'Lanjut ke step berikutnya?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            if (!result.isConfirmed) return;
        }

        try {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/' + action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }

            await loadDetail();
            await loadSessions();
            
            if (action === 'adjust') {
                Swal.fire('Berhasil', 'Berhasil menyesuaikan semua stock toko!', 'success');
                showTab('detail');
            }
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
        }
    }

    async function doAction(action) {
        if (action === 'print-kertas') {
            window.open('/stock-opname/' + selectedSessionId + '/print-kertas', '_blank');
            
            const result = await Swal.fire({
                title: 'Cetak Kertas?',
                text: 'Apakah kertas sudah dicetak? Lanjut ke status CETAK_KERTAS?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            if (!result.isConfirmed) return;
        } else if (action === 'print-selisih') {
            window.open('/stock-opname/' + selectedSessionId + '/print-selisih', '_blank');
            
            const result = await Swal.fire({
                title: 'Cetak Selisih?',
                text: 'Apakah laporan selisih sudah dicetak?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            if (!result.isConfirmed) return;
            
            showTab('discrepancies');
            if (currentTab === 'discrepancies') await loadDiscrepancies();
            if (currentTab === 'adjustments') await loadAdjustments();
            if (currentTab === 'edit-data') await loadEditProducts();
        } else if (action === 'check-data') {
            try {
                const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/check-data', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    Swal.fire('Error', 'Gagal mengecek data: ' + (err?.error || err?.message || res.statusText), 'error');
                    return;
                }
                
                const data = await res.json();
                
                if (data.unentered_count > 0) {
                    const confirmResult = await Swal.fire({
                        title: 'Data Belum Lengkap',
                        text: `Terdapat ${data.unentered_count} produk yang BELUM diinput fisiknya (dari total ${data.total_products} produk).`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tetap Lanjutkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#64748b'
                    });
                    if (!confirmResult.isConfirmed) return;
                } else {
                    const confirmResult = await Swal.fire({
                        title: 'Semua Data Lengkap',
                        text: 'Semua produk telah diinput (100%). Lanjut ke proses selisih?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#64748b'
                    });
                    if (!confirmResult.isConfirmed) return;
                }
                
                const prosesRes = await fetch('/api/opname/sessions/' + selectedSessionId + '/proses', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                
                if (!prosesRes.ok) {
                    const err = await prosesRes.json().catch(() => null);
                    Swal.fire('Error', 'Gagal lanjut ke PROSES: ' + (err?.error || err?.message || prosesRes.statusText), 'error');
                    return;
                }
                
                await loadDetail();
                await loadSessions();
                return;
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
                return;
            }
        } else {
            const result = await Swal.fire({
                title: 'Lanjutkan?',
                text: 'Lanjut ke step berikutnya?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b'
            });
            if (!result.isConfirmed) return;
        }

        try {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/' + action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }

            await loadDetail();
            await loadSessions();
            if (currentTab === 'adjustments') await loadAdjustments();
            if (currentTab === 'discrepancies') await loadDiscrepancies();
            if (currentTab === 'edit-data') await loadEditProducts();
            
            if (action === 'adjust') {
                Swal.fire('Berhasil', 'Berhasil menyesuaikan semua stock toko!', 'success');
                showTab('detail');
            }
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan jaringan: ' + e.message, 'error');
        }
    }

    async function saveEditCount(countId) {
        const qty = document.getElementById('edit-qty-' + countId).value;
        try {
            const res = await fetch('/api/opname/counts/' + countId, {
                method: 'PUT',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ physical_stock: qty })
            });
            
            if (res.ok) {
                await loadDetail();
            } else {
                const data = await res.json().catch(() => null);
                Swal.fire('Error', 'Error: ' + (data?.error || data?.message || 'Gagal menyimpan'), 'error');
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan jaringan: ' + e.message, confirmButtonColor: '#3085d6' });
        }
    }

    async function cancelSession() {
        const reason = prompt('Alasan cancel:');
        if (!reason) return;

        await fetch('/api/opname/sessions/' + selectedSessionId + '/cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ reason })
        });

        selectedSessionId = null;
        showTab('sessions');
    }

    async function loadEntryProducts() {
        if (!selectedSessionId) {
            document.getElementById('entryProductsTable').innerHTML = '<tr><td colspan="3" class="px-4 py-12 text-center text-slate-500">Pilih session dulu</td></tr>';
            return;
        }

        document.getElementById('entryProductsTable').innerHTML = document.getElementById('entryLoading').outerHTML;
        document.querySelector('#entryProductsTable tr').classList.remove('hidden');

        try {
            const url = '/api/opname/sessions/' + selectedSessionId + '/products';
            
            const res = await fetch(url);
            if (!res.ok) {
                console.error('API error:', res.status);
                document.getElementById('entryProductsTable').innerHTML = '<tr><td colspan="3" class="px-4 py-12 text-center text-red-500">Error: ' + res.status + '</td></tr>';
                return;
            }
            
            const data = await res.json();
            
            entryProducts = data.products || [];
            
            if (entryProducts.length === 0) {
                document.getElementById('entryProductsTable').innerHTML = '<tr><td colspan="3" class="px-4 py-12 text-center text-slate-500">Session ini belum memiliki produk. Ubah status ke CETAK_KERTAS lalu ENTRY.</td></tr>';
            } else {
                renderEntryProducts();
            }
        } catch(e) {
            console.error('Load products error:', e);
            document.getElementById('entryProductsTable').innerHTML = '<tr><td colspan="3" class="px-4 py-12 text-center text-red-500">Error: ' + e.message + '</td></tr>';
        }
    }

    function renderEntryProducts() {
        const search = document.getElementById('entrySearch').value.toLowerCase();
        
        if (entryProducts.length === 0) {
            document.getElementById('entryProductsTable').innerHTML = '<tr><td colspan="3" class="px-4 py-12 text-center text-slate-500">Session belum di Entry. Ubah status dulu.</td></tr>';
            return;
        }
        
        const filtered = entryProducts.filter(p => !search || p.name.toLowerCase().includes(search));

        let html = '';
        filtered.forEach((p, i) => {
            html += '<tr class="table-row-hover border-b border-slate-100">';
            html += '<td class="px-5 py-3.5 text-xs text-slate-500 font-mono">' + (p.code || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-slate-700 font-medium">' + p.name + '</td>';
            html += '<td class="px-5 py-3.5"><input type="number" min="0" data-id="' + p.id + '" class="w-24 bg-white text-slate-800 border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-lg px-3 py-2 text-right font-semibold shadow-inner transition-all" placeholder="0" value="' + (entryData[p.id] || '') + '"></td>';
            html += '</tr>';
        });

        if (filtered.length === 0) {
            html = '<tr><td colspan="3" class="px-5 py-12 text-center text-slate-400 italic">Tidak ada produk ditemukan</td></tr>';
        }

        document.getElementById('entryProductsTable').innerHTML = html;

        document.querySelectorAll('#entryProductsTable input').forEach(input => {
            input.addEventListener('change', function() {
                entryData[this.dataset.id] = parseInt(this.value) || 0;
            });
        });
    }

    function filterEntryProducts() {
        renderEntryProducts();
    }

    async function submitEntry() {
        const sessionRes = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await sessionRes.json();
        
        const entries = Object.entries(entryData).filter(([_, v]) => v !== undefined && v !== null && v !== '').map(([id, qty]) => ({
            product_id: parseInt(id),
            physical_stock: parseInt(qty)
        }));

        if (entries.length === 0) {
            Swal.fire('Peringatan', 'Mohon input minimal 1 produk', 'warning');
            return;
        }

        try {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/counts', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ counts: entries })
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal menyimpan: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }

            Swal.fire({
                title: 'Berhasil',
                text: 'Data tersimpan. Lanjutkan ke proses check data?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses Sekarang',
                cancelButtonText: 'Nanti Saja',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b'
            }).then(async (result) => {
                entryData = {};
                await loadEntryProducts();
                await loadDetail();
                
                if (result.isConfirmed) {
                    const res2 = await fetch('/api/opname/sessions/' + selectedSessionId + '/proses', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    
                    if (!res2.ok) {
                        Swal.fire('Error', 'Gagal proses data', 'error');
                        return;
                    }
                    
                    await loadDetail();
                    await loadSessions();
                    showTab('detail');
                } else {
                    showTab('detail');
                }
            });
        } catch (e) {
            Swal.fire('Error', 'Kesalahan jaringan: ' + e.message, 'error');
        }
    }

    async function loadDiscrepancies() {
        if (!selectedSessionId) return;

        document.getElementById('discrepanciesTable').innerHTML = document.getElementById('discrepanciesLoading').outerHTML;
        document.querySelector('#discrepanciesTable tr').classList.remove('hidden');

        const res = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await res.json();
        const disc = (session.counts || []).filter(c => c.variance_qty !== 0);

        let html = '';
        disc.forEach(c => {
            html += '<tr class="table-row-hover border-b border-slate-100">';
            html += '<td class="px-5 py-3.5 text-xs text-slate-500 font-mono">' + (c.product?.code || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-slate-700 font-medium">' + (c.product?.name || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-600">' + c.system_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-800 font-semibold">' + c.physical_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right font-black ' + (c.variance_qty > 0 ? 'text-emerald-600' : 'text-red-600') + '">' + c.variance_qty + '</td>';
            html += '<td class="px-5 py-3.5 text-right font-black ' + (c.variance_value > 0 ? 'text-emerald-600' : 'text-red-600') + '">' + formatCurrency(c.variance_value) + '</td>';
            html += '</tr>';
        });

        if (disc.length === 0) {
            html = '<tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 italic">Tidak ada selisih, semua data sesuai.</td></tr>';
        }

        document.getElementById('discrepanciesTable').innerHTML = html;
    }

    async function loadAdjustments() {
        if (!selectedSessionId) return;

        document.getElementById('adjustmentsTable').innerHTML = document.getElementById('adjustmentsLoading').outerHTML;
        document.querySelector('#adjustmentsTable tr').classList.remove('hidden');

        const res = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await res.json();
        const adjs = session.counts || [];

        let html = '';
        adjs.filter(c => c.variance_qty !== 0).forEach(c => {
            html += '<tr class="table-row-hover border-b border-slate-100">';
            html += '<td class="px-5 py-3.5 text-slate-700 font-medium">' + (c.product?.name || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-600">' + c.system_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-600">' + c.physical_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right font-black ' + (c.variance_qty > 0 ? 'text-emerald-600' : 'text-red-600') + '">' + c.variance_qty + '</td>';
            html += '</tr>';
        });

        if (adjs.filter(c => c.variance_qty !== 0).length === 0) {
            html = '<tr><td colspan="4" class="px-5 py-12 text-center text-slate-400 italic">Tidak ada selisih</td></tr>';
        }

        document.getElementById('adjustmentsTable').innerHTML = html;
    }

    async function loadEditProducts() {
        if (!selectedSessionId) {
            document.getElementById('editProductsTable').innerHTML = '<tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">Pilih session dulu</td></tr>';
            return;
        }

        document.getElementById('editProductsTable').innerHTML = document.getElementById('editLoading').outerHTML;
        document.querySelector('#editProductsTable tr').classList.remove('hidden');

        const res = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await res.json();
        counts = session.counts || [];
        renderEditProducts();
    }

    function renderEditProducts() {
        const search = document.getElementById('editSearch')?.value?.toLowerCase() || '';
        
        if (counts.length === 0) {
            document.getElementById('editProductsTable').innerHTML = '<tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">Tidak ada data untuk diedit</td></tr>';
            return;
        }
        
        const filtered = counts.filter(c => {
            const name = c.product?.name?.toLowerCase() || '';
            const code = c.product?.code?.toLowerCase() || '';
            return !search || name.includes(search) || code.includes(search);
        });

        let html = '';
        filtered.forEach(c => {
            html += '<tr class="table-row-hover border-b border-slate-100">';
            html += '<td class="px-5 py-3.5 text-xs text-slate-500 font-mono">' + (c.product?.code || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-slate-700 font-medium">' + (c.product?.name || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-600">' + c.system_stock + '</td>';
            html += '<td class="px-5 py-3.5"><input type="number" min="0" data-id="' + c.id + '" class="w-24 bg-white text-slate-800 border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 rounded-lg px-3 py-2 text-right font-semibold shadow-inner transition-all" value="' + c.physical_stock + '"></td>';
            html += '<td class="px-5 py-3.5 text-right font-black ' + (c.variance_qty > 0 ? 'text-emerald-600' : c.variance_qty < 0 ? 'text-red-600' : 'text-slate-500') + '">' + c.variance_qty + '</td>';
            html += '</tr>';
        });

        if (filtered.length === 0) {
            html = '<tr><td colspan="5" class="px-5 py-12 text-center text-slate-400 italic">Tidak ada produk ditemukan</td></tr>';
        }

        document.getElementById('editProductsTable').innerHTML = html;

        document.querySelectorAll('#editProductsTable input').forEach(input => {
            input.addEventListener('change', function() {
                const countId = parseInt(this.dataset.id);
                const newQty = parseInt(this.value) || 0;
                const count = counts.find(c => c.id === countId);
                if (count) {
                    count.newPhysicalStock = newQty;
                }
            });
        });
    }

    function filterEditProducts() {
        renderEditProducts();
    }

    async function loadFixedProducts() {
        if (!selectedSessionId) {
            document.getElementById('fixedTable').innerHTML = '<tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">Pilih session dulu</td></tr>';
            return;
        }

        document.getElementById('fixedTable').innerHTML = document.getElementById('fixedLoading').outerHTML;
        document.querySelector('#fixedTable tr').classList.remove('hidden');

        const res = await fetch('/api/opname/sessions/' + selectedSessionId);
        const session = await res.json();
        const items = (session.counts || []).filter(c => c.variance_qty !== 0);

        let html = '';
        items.forEach(c => {
            html += '<tr class="table-row-hover border-b border-slate-100">';
            html += '<td class="px-5 py-3.5 text-slate-700 font-medium">' + (c.product?.name || '') + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-600">' + c.system_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right text-slate-800 font-semibold">' + c.physical_stock + '</td>';
            html += '<td class="px-5 py-3.5 text-right font-black ' + (c.variance_qty > 0 ? 'text-emerald-600' : 'text-red-600') + '">' + c.variance_qty + '</td>';
            html += '</tr>';
        });

        if (items.length === 0) {
            html = '<tr><td colspan="4" class="px-5 py-12 text-center text-slate-400 italic">Tidak ada selisih</td></tr>';
        }

        document.getElementById('fixedTable').innerHTML = html;
    }

    async function submitEdit() {
        const editEntries = counts.filter(c => c.newPhysicalStock !== undefined && c.newPhysicalStock !== c.physical_stock);

        if (editEntries.length === 0) {
            Swal.fire('Peringatan', 'Tidak ada perubahan untuk disimpan', 'warning');
            return;
        }

        try {
            const res = await fetch('/api/opname/sessions/' + selectedSessionId + '/edit-counts', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ counts: editEntries.map(c => ({ count_id: c.id, physical_stock: c.newPhysicalStock })) })
            });

            if (!res.ok) {
                const err = await res.json().catch(() => null);
                Swal.fire('Error', 'Gagal menyimpan: ' + (err?.error || err?.message || res.statusText), 'error');
                return;
            }

            Swal.fire({
                title: 'Berhasil',
                text: 'Data berhasil diedit. Lanjutkan ke Fixed (Kunci Data)?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kunci Data',
                cancelButtonText: 'Nanti Saja',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            }).then(async (result) => {
                await loadDetail();
                await loadEditProducts();
                
                if (result.isConfirmed) {
                    const res2 = await fetch('/api/opname/sessions/' + selectedSessionId + '/fixed', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    
                    if (!res2.ok) {
                        Swal.fire('Error', 'Gagal mengunci data', 'error');
                        return;
                    }
                    
                    await loadDetail();
                    await loadSessions();
                    showTab('adjustments');
                    await loadAdjustments();
                    Swal.fire('Berhasil', 'Data berhasil dikunci!', 'success');
                } else {
                    showTab('detail');
                }
            });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan jaringan: ' + e.message, confirmButtonColor: '#3085d6' });
        }
    }

    function statusClass(status) {
        const classes = {
            'PLANNED': 'bg-blue-100 text-blue-700',
            'CETAK_KERTAS': 'bg-amber-100 text-amber-700',
            'ENTRY': 'bg-amber-100 text-amber-700',
            'CHECK_DATA': 'bg-amber-100 text-amber-700',
            'PROSES': 'bg-amber-100 text-amber-700',
            'CETAK_SELISIH': 'bg-orange-100 text-orange-700',
            'EDIT_DATA': 'bg-orange-100 text-orange-700',
            'FIXED': 'bg-red-100 text-red-700',
            'ADJUST': 'bg-emerald-100 text-emerald-700',
            'POSTED': 'bg-purple-100 text-purple-700'
        };
        return classes[status] || 'bg-slate-100 text-slate-700';
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
    }

    loadSessions();
    </script>
</x-layout>
