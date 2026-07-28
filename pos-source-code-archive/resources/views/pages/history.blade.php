<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="flex-1 flex flex-col min-h-screen relative w-full" x-data="historyPage()">

        <!-- TopAppBar Shared Component -->
        <header
            class="sticky top-0 z-30 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl shadow-sm dark:shadow-none flex flex-col w-full px-4 lg:px-8 py-3 lg:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h1
                        class="text-lg lg:text-2xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100 font-headline">
                        Detailed Reports</h1>
                </div>
                <div class="flex items-center gap-2 lg:gap-3">
                    <form method="GET" action="/history" class="relative group hidden sm:block">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-lg lg:text-xl">search</span>
                        </span>
                        <input name="search" value="{{ request('search') }}"
                            class="pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 text-sm w-48 lg:w-64 transition-all duration-200 outline-none"
                            placeholder="Search transactions..." type="text" />
                    </form>
                </div>
            </div>
            <!-- Mobile Search -->
            <form method="GET" action="/history" class="sm:hidden mt-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <input name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 text-sm outline-none"
                        placeholder="Search transactions..." type="text" />
                </div>
            </form>
        </header>

        <!-- Workspace Content -->
        <div class="p-4 lg:p-8 space-y-4 lg:space-y-6 max-w-full">
        <!-- Report Header Section -->
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $title ?? 'Page' }}" />
        </div>

            <!-- Filter Bar -->
            <form action="/history" method="GET"
                class="bg-surface-container-lowest rounded-xl p-4 lg:p-5 flex flex-col lg:flex-row items-center justify-between gap-4 shadow-[0_4px_20px_rgba(0,26,64,0.03)]">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <div class="flex items-center gap-3 lg:gap-4 w-full lg:w-auto overflow-x-auto">
                    <div class="flex flex-col relative">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 hidden lg:block">Date
                            Range
                            Selection</span>
                        <div
                            class="relative flex items-center bg-surface-container-low rounded-lg hover:bg-surface-container-high transition-colors border border-outline-variant/10 focus-within:ring-2 focus-within:ring-primary/20">
                            <span
                                class="absolute left-2 lg:left-3 material-symbols-outlined text-base lg:text-lg text-primary pointer-events-none">calendar_today</span>
                            <select name="date" onchange="this.form.submit()"
                                class="appearance-none bg-transparent pl-8 lg:pl-10 pr-8 lg:pr-10 py-1.5 lg:py-2 w-32 lg:w-40 text-xs lg:text-sm font-semibold text-on-surface outline-none cursor-pointer">
                                <option value="all" {{ request('date', 'all') == 'all' ? 'selected' : '' }}>All Time
                                </option>
                                <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date') == 'month' ? 'selected' : '' }}>This Month
                                </option>
                            </select>
                            <span
                                class="absolute right-2 lg:right-3 material-symbols-outlined text-base lg:text-lg text-slate-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>
                    <div class="flex flex-col relative">
                        <span
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 hidden lg:block">Payment
                            Method</span>
                        <div
                            class="relative flex items-center bg-surface-container-low rounded-lg hover:bg-surface-container-high transition-colors border border-outline-variant/10 focus-within:ring-2 focus-within:ring-primary/20">
                            <select name="method" onchange="this.form.submit()"
                                class="appearance-none bg-transparent pl-3 lg:pl-4 pr-8 lg:pr-10 py-1.5 lg:py-2 w-32 lg:w-40 text-xs lg:text-sm font-semibold text-on-surface outline-none cursor-pointer">
                                <option value="all" {{ request('method', 'all') == 'all' ? 'selected' : '' }}>All Methods
                                </option>
                                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="qris" {{ request('method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                <option value="debit" {{ request('method') == 'debit' ? 'selected' : '' }}>Debit / Credit
                                </option>
                            </select>
                            <span
                                class="absolute right-2 lg:right-3 material-symbols-outlined text-base lg:text-lg text-slate-400 pointer-events-none">filter_list</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 lg:gap-3 w-full lg:w-auto">
                    <button type="submit" name="export" value="csv"
                        class="flex items-center gap-2 px-4 lg:px-5 py-2 lg:py-2.5 bg-primary-container text-white rounded-lg font-manrope font-bold text-xs lg:text-sm hover:opacity-90 active:scale-95 transition-all shadow-md cursor-pointer">
                        <span class="material-symbols-outlined text-base lg:text-lg">download</span>
                        <span class="hidden lg:inline">Export to CSV</span>
                        <span class="lg:hidden">CSV</span>
                    </button>
                </div>
            </form>

            <!-- Metrics Overview (Small Bento Section) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <div
                    class="bg-surface-container-lowest p-4 lg:p-5 rounded-xl border border-outline-variant/5 shadow-sm">
                    <p class="text-[10px] lg:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total TXs
                    </p>
                    <p class="text-lg lg:text-2xl font-extrabold text-primary font-headline">
                        {{ number_format($totalCount) }}
                    </p>
                </div>
                <div
                    class="bg-surface-container-lowest p-4 lg:p-5 rounded-xl border border-outline-variant/5 shadow-sm">
                    <p class="text-[10px] lg:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Revenue</p>
                    <p class="text-lg lg:text-2xl font-extrabold text-primary font-headline">
                        Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div
                    class="bg-surface-container-lowest p-4 lg:p-5 rounded-xl border border-outline-variant/5 shadow-sm">
                    <p class="text-[10px] lg:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Avg. Ticket
                    </p>
                    <p class="text-lg lg:text-2xl font-extrabold text-primary font-headline">
                        Rp{{ number_format($avgTicket, 0, ',', '.') }}</p>
                </div>
                <div
                    class="bg-surface-container-lowest p-4 lg:p-5 rounded-xl border border-outline-variant/5 shadow-sm">
                    <p class="text-[10px] lg:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Discounts
                    </p>
                    <p class="text-lg lg:text-2xl font-extrabold text-tertiary font-headline">Rp0</p>
                </div>
            </div>

            <!-- Transaction Table Container -->
            <div
                class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_12px_32px_rgba(0,26_64,0.06)] flex flex-col border border-outline-variant/5">
                <div class="overflow-x-auto hide-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low/50">
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10">
                                    Date &amp; Time</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10">
                                    TX ID</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10">
                                    Cashier</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10">
                                    Status</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10 text-right">
                                    Total</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10">
                                    Method</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest border-b border-outline-variant/10 text-center">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse($histories as $history)
                                                     <tr class="hover:bg-surface-bright transition-colors group {{ $history->status == 'voided' ? 'opacity-50' : '' }}">
                                                         <td class="px-6 py-4">
                                                             <div class="text-sm font-medium text-on-surface whitespace-nowrap">
                                                                 {{ $history->created_at->format('M d, H:i') }}
                                                             </div>
                                                         </td>
                                                         <td class="px-6 py-4 text-xs font-mono text-slate-500">{{ $history->invoice_id }}</td>
                                                         <td class="px-6 py-4 text-sm font-semibold text-blue-800">{{ $history->cashier_name }}
                                                         </td>
                                                         <td class="px-6 py-4 whitespace-nowrap">
                                                            @if($history->status == 'voided')
                                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[9px] font-black uppercase tracking-widest">VOIDED</span>
                                                            @else
                                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[9px] font-black uppercase tracking-widest">COMPLETED</span>
                                                            @endif
                                                         </td>
                                                         <td class="px-6 py-4 text-sm text-right font-manrope font-extrabold text-primary {{ $history->status == 'voided' ? 'line-through' : '' }}">
                                                             Rp{{ number_format($history->total_amount, 0, ',', '.') }}</td>
                                                         <td class="px-6 py-4 whitespace-nowrap">
                                                              @php
                                                                  $pmLabel = $history->payment_method;
                                                                  $pmBadgeClass = 'bg-surface-container-high text-on-surface-variant';
                                                                  if ($history->payment_method === 'split') {
                                                                      $pmMethods = $history->payments->pluck('method')->unique()->map(function($m) { return ucfirst($m); })->join(' + ');
                                                                      $pmLabel = $pmMethods;
                                                                      $pmBadgeClass = 'bg-purple-100 text-purple-800';
                                                                  } elseif ($history->payment_method === 'cash') {
                                                                      $pmBadgeClass = 'bg-blue-100 text-blue-800';
                                                                  }
                                                              @endphp
                                                              <span class="px-2 py-1 {{ $pmBadgeClass }} rounded text-[10px] font-bold uppercase tracking-tight">{{ $pmLabel }}</span>
                                                         </td>
                                                         <td class="px-6 py-4 text-center">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <a href="{{ route('recipe', $history->id) }}"
                                                                    class="text-slate-400 hover:text-primary transition-colors inline-block p-1" title="View Receipt">
                                                                    <span class="material-symbols-outlined text-lg">receipt_long</span>
                                                                </a>
                                                                @if($history->status != 'voided')
                                                                <button @click="openVoidModal({{ $history->id }}, '{{ $history->invoice_id }}')"
                                                                    class="text-slate-400 hover:text-red-600 transition-colors inline-block p-1" title="Void Transaction">
                                                                    <span class="material-symbols-outlined text-lg">block</span>
                                                                </button>
                                                                @endif
                                                            </div>
                                                         </td>
                                                     </tr>
                            @empty
                                 <tr>
                                     <td colspan="7" class="p-8 text-center text-on-surface-variant">No transactions yet.
                                     </td>
                                 </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Table Pagination -->
                <div
                    class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Showing {{ $histories->firstItem() ?? 0 }} -
                        {{ $histories->lastItem() ?? 0 }} of {{ $histories->total() }} entries</span>
                    <div class="flex items-center gap-1">
                        @if($histories->onFirstPage())
                            <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                            </span>
                        @else
                            <a href="{{ $histories->previousPageUrl() }}"
                                class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                            </a>
                        @endif
                        @foreach($histories->getUrlRange(max($histories->currentPage() - 2, 1), min($histories->currentPage() + 2, $histories->lastPage())) as $page => $url)
                            <a href="{{ $url }}"
                                class="text-xs font-bold px-2.5 py-1 rounded {{ $page == $histories->currentPage() ? 'bg-primary text-white' : 'hover:bg-surface-container cursor-pointer' }} transition-colors">{{ $page }}</a>
                        @endforeach
                        @if($histories->hasMorePages())
                            <a href="{{ $histories->nextPageUrl() }}"
                                class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </a>
                        @else
                            <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Summary Visualization -->
            @php
                $totalTxs = $totalCount ?: 1;
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-10">
                <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/5 shadow-sm">
                    <h3 class="text-sm font-extrabold text-blue-900 mb-4 tracking-tight">Payment Method Split</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-slate-600">QRIS</span>
                                <span
                                    class="text-xs font-bold font-manrope">{{ round(($qrisCount / $totalTxs) * 100) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-surface-container-low rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full"
                                    style="width: {{ round(($qrisCount / $totalTxs) * 100) }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-slate-600">CASH</span>
                                <span
                                    class="text-xs font-bold font-manrope">{{ round(($cashCount / $totalTxs) * 100) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-surface-container-low rounded-full overflow-hidden">
                                <div class="h-full bg-secondary rounded-full"
                                    style="width: {{ round(($cashCount / $totalTxs) * 100) }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-slate-600">DEBIT / CREDIT</span>
                                <span
                                    class="text-xs font-bold font-manrope">{{ round(($debitCount / $totalTxs) * 100) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-surface-container-low rounded-full overflow-hidden">
                                <div class="h-full bg-slate-400 rounded-full"
                                    style="width: {{ round(($debitCount / $totalTxs) * 100) }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-primary overflow-hidden rounded-2xl relative shadow-xl flex items-center justify-between p-8">
                    <div class="z-10">
                        <h3 class="text-primary-fixed text-sm font-bold uppercase tracking-widest mb-1">Monthly Forecast
                        </h3>
                        <p class="text-white text-3xl font-extrabold font-headline mb-4">
                            Rp{{ number_format($totalRevenue * 1.15, 0, ',', '.') }}</p>
                        <p class="text-primary-fixed text-xs max-w-[240px]">Based on current performance, you are
                            projected to exceed last month's revenue.</p>
                    </div>
                    <div class="z-10">
                        <span class="material-symbols-outlined text-white/20 text-8xl"
                            style="font-size: 8rem;">trending_up</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-container to-primary opacity-50"></div>
                    <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
                </div>
            </div>
        </div>

        <!-- Void Authorization Modal -->
        <div x-show="voidModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div @click.away="voidModalOpen = false" 
                 class="bg-white rounded-[2rem] w-[calc(100%-2rem)] md:w-full max-w-md overflow-hidden shadow-2xl">
                <div class="bg-red-50 p-6 flex items-center gap-4 border-b border-red-100">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl font-black">block</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-red-900">Otorisasi Void</h3>
                        <p class="text-red-600/70 text-xs font-bold uppercase tracking-widest" x-text="'Transaksi: ' + voidInvoiceId"></p>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <p class="text-xs text-blue-700 font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">info</span>
                            Minta kode OTP dari Super Admin terlebih dahulu.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kode OTP</label>
                            <input type="text" x-model="voidOtp" maxlength="6" placeholder="Masukkan 6 digit OTP"
                                class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-red-100 tracking-widest text-center">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Alasan Pembatalan</label>
                            <textarea x-model="voidReason" placeholder="Contoh: Salah input barang, Salah metode bayar..."
                                class="w-full bg-slate-50 border-none rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-red-100 h-24"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button @click="voidModalOpen = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                        <button @click="confirmVoid()" :disabled="submittingVoid"
                            class="flex-1 py-4 bg-red-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-200 flex items-center justify-center gap-2">
                            <span x-show="!submittingVoid">Konfirmasi Void</span>
                            <span x-show="submittingVoid" class="material-symbols-outlined animate-spin text-sm">autorenew</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function historyPage() {
            return {
                voidModalOpen: false,
                voidHistoryId: null,
                voidInvoiceId: '',
                voidOtp: '',
                voidReason: '',
                submittingVoid: false,

                openVoidModal(id, invoiceId) {
                    this.voidHistoryId = id;
                    this.voidInvoiceId = invoiceId;
                    this.voidModalOpen = true;
                    this.voidOtp = '';
                    this.voidReason = '';
                },

                async confirmVoid() {
                    if (!this.voidOtp || !this.voidReason) {
                        Swal.fire({ icon: 'warning', title: 'Data Kurang', text: 'OTP dan alasan pembatalan harus diisi.' });
                        return;
                    }

                    if (this.voidOtp.length !== 6) {
                        Swal.fire({ icon: 'warning', title: 'OTP Tidak Valid', text: 'OTP harus terdiri dari 6 digit.' });
                        return;
                    }

                    this.submittingVoid = true;
                    try {
                        let response = await fetch(`/history/${this.voidHistoryId}/void`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                otp: this.voidOtp,
                                reason: this.voidReason
                            })
                        });

                        let data = await response.json();
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
                    } finally {
                        this.submittingVoid = false;
                    }
                }
            }
        }
    </script>
</x-layout>