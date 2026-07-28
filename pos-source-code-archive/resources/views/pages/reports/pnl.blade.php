<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <header class="bg-white/70 backdrop-blur-xl sticky top-0 z-30 flex items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <span class="material-symbols-outlined text-primary font-black text-xl">monitoring</span>
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900">{{ $title }}</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar space-y-6">
            <x-report-header title="Profit & Loss Report" module="Reports" submodule="P&L" description="View profit and loss summary for a specified period." />

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <form method="GET" action="/reports/pnl" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Toko</label>
                        <select name="store_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-semibold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="">Semua Toko</option>
                            @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                            Filter Laporan
                        </button>
                    </div>
                </form>
            </div>

            <!-- P&L Content -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Main Report Card -->
                <div class="lg:col-span-7 bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                    <div class="space-y-8">
                        <div>
                            <h2 class="text-2xl lg:text-3xl font-bold text-slate-900">Profit & Loss <span class="text-blue-600">Summary</span></h2>
                            <p class="text-slate-400 text-xs font-medium mt-1">Periode: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</p>
                        </div>

                        <div class="space-y-5">
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                                        <span class="material-symbols-outlined">payments</span>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">Total Pendapatan</span>
                                </div>
                                <span class="text-xl font-bold text-blue-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-t border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                                        <span class="material-symbols-outlined">inventory_2</span>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">HPP (Modal Produk)</span>
                                </div>
                                <span class="text-xl font-bold text-orange-600">- Rp {{ number_format($totalCogs, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center justify-between bg-slate-50 rounded-xl p-5">
                                <span class="text-sm font-bold text-slate-500">Laba Kotor</span>
                                <span class="text-2xl font-bold text-slate-900">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-t border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-50 text-red-600 rounded-xl flex items-center justify-center">
                                        <span class="material-symbols-outlined">outbox</span>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">Biaya Operasional</span>
                                </div>
                                <span class="text-xl font-bold text-red-600">- Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                            </div>

                            <div class="h-px bg-slate-200"></div>

                            <div class="flex items-center justify-between bg-gradient-to-br {{ $netProfit >= 0 ? 'from-emerald-500 to-teal-700' : 'from-red-500 to-rose-700' }} rounded-2xl p-6">
                                <div>
                                    <p class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">Laba Bersih (Net Profit)</p>
                                    <h3 class="text-2xl lg:text-3xl font-bold text-white mt-0.5">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white">
                                    <span class="material-symbols-outlined text-3xl">{{ $netProfit >= 0 ? 'trending_up' : 'trending_down' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analysis Sidebar -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                        <h4 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-sm">analytics</span>
                            </span>
                            Analisis Margin
                        </h4>
                        
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-500 mb-2">
                                    <span>Gross Margin</span>
                                    <span class="font-semibold text-slate-700">{{ $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0 }}%</span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full transition-all duration-1000" style="width: {{ $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-500 mb-2">
                                    <span>Net Margin</span>
                                    <span class="font-semibold text-slate-700">{{ $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0 }}%</span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000" style="width: {{ $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-slate-50 rounded-xl">
                            <p class="text-xs text-slate-500 leading-relaxed">
                                <span class="font-semibold text-slate-700">Info:</span> Laba bersih dihitung setelah dikurangi seluruh biaya modal (HPP) dan biaya operasional yang tercatat.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="/expenses" class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col items-center gap-3 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-300 group">
                            <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors">
                                <span class="material-symbols-outlined">add_card</span>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 group-hover:text-white transition-colors">Input Biaya</span>
                        </a>
                        <a href="/history" class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col items-center gap-3 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all duration-300 group">
                            <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors">
                                <span class="material-symbols-outlined">history</span>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500 group-hover:text-white transition-colors">Lihat Transaksi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
