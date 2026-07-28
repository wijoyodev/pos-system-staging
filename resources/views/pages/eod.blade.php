<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  @if(session('success'))
    <div id="success-alert" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div id="error-alert" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg">
      {{ session('error') }}
    </div>
  @endif

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">End Of Day</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <x-report-header title="End of Day" module="Reports" submodule="EOD" description="Generate daily closing reports and auto-create purchase orders.">
                <x-slot:actions>
                    <div class="flex gap-2 lg:gap-3">
                        @if(!$todayExists)
                        <form action="/eod/generate" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
                                <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">event_available</span>
                                Generate EOD Today
                            </button>
                        </form>
                        @else
                        <button disabled
                            class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-slate-300 text-slate-500 font-bold rounded-lg shadow-md text-xs lg:text-sm cursor-not-allowed">
                            <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">check_circle</span>
                            EOD Today Done
                        </button>
                        @endif
                    </div>
                </x-slot:actions>
            </x-report-header>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Reports</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ $eodReports->count() }}</span>
          </div>
        </div>
        @if($eodReports->count() > 0)
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Latest Net Sales</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-green-600 font-headline">Rp {{ number_format($eodReports->first()->total_net_sales, 0, ',', '.') }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Latest Transactions</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-blue-600 font-headline">{{ $eodReports->first()->total_transactions }}</span>
          </div>
        </div>
        @endif
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <form method="GET" action="/eod" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date From</label>
              <input name="date_from" value="{{ request('date_from') }}" type="date"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" />
            </div>
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date To</label>
              <input name="date_to" value="{{ request('date_to') }}" type="date"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" />
            </div>
            <div class="flex items-end">
              <button type="submit" class="w-full bg-primary text-white py-2.5 rounded-lg font-bold text-sm hover:bg-primary-container transition-all cursor-pointer">
                Filter
              </button>
            </div>
          </form>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Date</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Store</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Transactions</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right hidden lg:table-cell">Net Sales</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right hidden lg:table-cell">Cash Diff</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Status</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @forelse($eodReports as $eod)
                <tr class="hover:bg-blue-50/30 transition-colors group">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3 lg:gap-4">
                      <div class="w-10 lg:w-12 h-10 lg:h-12 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-purple-600 text-lg lg:text-xl">event_available</span>
                      </div>
                      <div>
                        <div class="text-xs lg:text-sm font-bold text-on-surface">{{ $eod->eod_date->format('d M Y') }}</div>
                        <div class="text-[10px] lg:text-[11px] text-slate-400 font-medium">By: {{ $eod->generatedBy?->name ?? 'Unknown' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $eod->store->name }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    <span class="text-xs lg:text-sm font-bold text-on-surface">{{ number_format($eod->total_transactions) }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right hidden lg:table-cell">
                    <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($eod->total_net_sales, 0, ',', '.') }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right hidden lg:table-cell">
                    @if($eod->cash_difference < 0)
                      <span class="text-xs lg:text-sm font-bold text-red-600">-Rp {{ number_format(abs($eod->cash_difference), 0, ',', '.') }}</span>
                    @else
                      <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($eod->cash_difference, 0, ',', '.') }}</span>
                    @endif
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    @if($eod->status === 'finalized')
                      <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[9px] font-black uppercase tracking-tighter">Finalized</span>
                    @else
                      <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[9px] font-black uppercase tracking-tighter">Draft</span>
                    @endif
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <div class="flex items-center justify-end gap-1">
                      <a href="/eod/{{ $eod->id }}"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                        title="View Details">
                        <span class="material-symbols-outlined text-base lg:text-sm">visibility</span>
                      </a>
                      <a href="/eod/{{ $eod->id }}/print" target="_blank"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                        title="Print">
                        <span class="material-symbols-outlined text-base lg:text-sm">print</span>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-6 py-8 text-center text-slate-500">No EOD reports found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script>
    setTimeout(() => {
      const successAlert = document.getElementById('success-alert');
      const errorAlert = document.getElementById('error-alert');
      if (successAlert) successAlert.style.display = 'none';
      if (errorAlert) errorAlert.style.display = 'none';
    }, 3000);
  </script>

</x-layout>
