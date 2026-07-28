<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full">

        <header
            class="bg-white/70 backdrop-blur-xl sticky top-0 z-30 flex items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
            <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
                <span class="material-symbols-outlined text-primary font-black text-xl">dashboard</span>
                <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900">{{ $title }}</h1>
            </div>
        </header>

        <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar space-y-6 lg:space-y-8">
            <x-report-header title="Business Intelligence" module="Dashboard" submodule="Analytics"
                description="Live Performance Overview & Analytics">

            </x-report-header>

            @if(auth()->user()->isSuperAdmin())
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                    <form method="GET" action="/dashboard" class="flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-400">business</span>
                            <select name="branch_id"
                                class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-primary/20 outline-none cursor-pointer"
                                onchange="this.form.submit()">
                                <option value="">Semua Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-400">storefront</span>
                            <select name="store_id"
                                class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-primary/20 outline-none cursor-pointer"
                                onchange="this.form.submit()">
                                <option value="">Semua Toko</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                        {{ $store->code }} - {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Financial Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                <div
                    class="relative group overflow-hidden bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-lg shadow-blue-200/50">
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-2xl">payments</span>
                            </div>
                            <span
                                class="text-[10px] font-bold text-blue-100 bg-white/10 px-2.5 py-1 rounded-full">{{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%</span>
                        </div>
                        <div>
                            <p class="text-blue-100/70 text-xs font-semibold mb-0.5">Today's Revenue</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white">Rp
                                {{ number_format($todaysRevenue, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div
                    class="relative group overflow-hidden bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                                <span class="material-symbols-outlined text-2xl">outbox</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-semibold mb-0.5">Today's Expenses</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-slate-800">Rp
                                {{ number_format($todaysExpenses, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div
                    class="relative group overflow-hidden bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                                <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-semibold mb-0.5">Gross Profit</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-slate-800">Rp
                                {{ number_format($todaysGrossProfit, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div
                    class="relative group overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-lg shadow-emerald-200/50">
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-2xl">finance</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-emerald-100/70 text-xs font-semibold mb-0.5">Net Profit</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white">Rp
                                {{ number_format($todaysNetProfit, 0, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Analytics Row -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
                <!-- Revenue Trend -->
                <div
                    class="lg:col-span-8 bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h4 class="text-lg lg:text-xl font-bold text-slate-900">Revenue Trend</h4>
                            <p class="text-slate-400 text-xs font-medium">Last 7 days performance</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span class="text-[10px] font-semibold text-slate-500">Revenue</span>
                        </div>
                    </div>

                    <div class="relative h-[250px] flex items-end justify-between gap-2 lg:gap-4">
                        @php
                            $maxRevenue = $revenueTrend->max('revenue') ?: 1;
                        @endphp
                        @foreach($revenueTrend as $trend)
                            @php
                                $height = ($trend['revenue'] / $maxRevenue) * 100;
                            @endphp
                            <div class="flex-1 flex flex-col items-center group">
                                <div class="w-full flex items-end justify-center" style="height: 200px;">
                                    <div class="w-full max-w-[36px] bg-blue-600 rounded-lg relative group-hover:bg-blue-700 transition-all duration-300"
                                        style="height: {{ max($height, 5) }}%; min-height: 8px;">
                                        <div
                                            class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-semibold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">
                                            Rp {{ number_format($trend['revenue'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <span class="mt-3 text-[10px] font-semibold text-slate-400">{{ $trend['day'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Best Sellers -->
                <div
                    class="lg:col-span-4 bg-white rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200">
                    <h4 class="text-lg lg:text-xl font-bold text-slate-900 mb-6">Best <span
                            class="text-blue-600">Sellers</span></h4>
                    <div class="space-y-6">
                        @forelse($bestSellers as $seller)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-sm font-semibold text-slate-700 truncate pr-2">
                                        {{ $seller->product->name ?? 'Unknown' }}
                                    </h5>
                                    <span class="text-xs font-bold text-blue-600">{{ round($seller->percentage) }}%</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                            style="width: {{ $seller->percentage }}%"></div>
                                    </div>
                                    <span
                                        class="text-[10px] font-medium text-slate-400 w-14 text-right">{{ $seller->total_sold }}
                                        pcs</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <span class="material-symbols-outlined text-slate-200 text-5xl">inventory_2</span>
                                <p class="text-slate-400 text-xs font-medium mt-3">No Data Available</p>
                            </div>
                        @endforelse
                    </div>
                    <a href="/product"
                        class="mt-6 flex items-center justify-center gap-2 py-3 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold transition-all group">
                        View Inventory
                        <span
                            class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Operational Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <div
                    class="bg-white rounded-xl lg:rounded-2xl p-5 lg:p-6 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div
                        class="w-11 h-11 lg:w-12 lg:h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 shrink-0">
                        <span class="material-symbols-outlined text-xl lg:text-2xl">receipt_long</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-slate-400 truncate">Total Transaksi</p>
                        <h4 class="text-lg lg:text-xl font-bold text-slate-800">{{ number_format($totalTransactions) }}
                        </h4>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl lg:rounded-2xl p-5 lg:p-6 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div
                        class="w-11 h-11 lg:w-12 lg:h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                        <span class="material-symbols-outlined text-xl lg:text-2xl">inventory</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-slate-400 truncate">Produk Terjual</p>
                        <h4 class="text-lg lg:text-xl font-bold text-slate-800">{{ number_format($itemsSold) }}</h4>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl lg:rounded-2xl p-5 lg:p-6 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div
                        class="w-11 h-11 lg:w-12 lg:h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 shrink-0">
                        <span class="material-symbols-outlined text-xl lg:text-2xl">analytics</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-slate-400 truncate">Avg. Ticket</p>
                        <h4 class="text-lg lg:text-xl font-bold text-slate-800">Rp
                            {{ number_format($avgTransactionValue, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl lg:rounded-2xl p-5 lg:p-6 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div
                        class="w-11 h-11 lg:w-12 lg:h-12 {{ $lowStockCount > 0 ? 'bg-orange-50 text-orange-600' : 'bg-slate-50 text-slate-400' }} rounded-xl flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl lg:text-2xl">warning</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-slate-400 truncate">Stok Menipis</p>
                        <h4
                            class="text-lg lg:text-xl font-bold {{ $lowStockCount > 0 ? 'text-orange-600' : 'text-slate-800' }}">
                            {{ $lowStockCount }} Item
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>