<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Customers</h1>
        <form method="GET" action="/customers" class="relative group">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-slate-400">search</span>
          <input name="search" value="{{ request('search') }}"
            class="w-full pl-10 pr-12 py-2.5 bg-slate-100/50 border-none rounded-xl focus:ring-2 focus:ring-primary/10 transition-all text-sm outline-none"
            placeholder="Search by name, phone, or code..." type="text" />
        </form>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <x-report-header title="Customer List" module="CRM" submodule="Customer Management" description="Manage your customers, track their points, and view transaction history.">
                <x-slot:actions>
                    <div class="flex gap-2 lg:gap-3">
                        <button onclick="document.getElementById('addCustomerModal').classList.remove('hidden')"
                            class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
                            <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">person_add</span>
                            Add Customer
                        </button>
                    </div>
                </x-slot:actions>
            </x-report-header>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Customers</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ $customers->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gold Tier</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-yellow-600 font-headline">{{ $customers->filter(fn($c) => $c->tier === 'gold')->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Silver Tier</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-slate-500 font-headline">{{ $customers->filter(fn($c) => $c->tier === 'silver')->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Bronze Tier</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-orange-600 font-headline">{{ $customers->filter(fn($c) => $c->tier === 'bronze')->count() }}</span>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 lg:gap-4">
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 sm:pb-0">
            <a href="/customers"
              class="px-3 lg:px-4 py-1.5 lg:py-2 {{ !request('tier') ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">All</a>
            <a href="/customers?tier=gold"
              class="px-3 lg:px-4 py-1.5 lg:py-2 {{ request('tier') == 'gold' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">Gold</a>
            <a href="/customers?tier=silver"
              class="px-3 lg:px-4 py-1.5 lg:py-2 {{ request('tier') == 'silver' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">Silver</a>
            <a href="/customers?tier=bronze"
              class="px-3 lg:px-4 py-1.5 lg:py-2 {{ request('tier') == 'bronze' ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-slate-100' }} text-xs font-bold rounded-full whitespace-nowrap transition-all">Bronze</a>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Customer</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden sm:table-cell">Code</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Phone</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Points</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Spent</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Act</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @forelse($customers as $customer)
                <tr class="hover:bg-blue-50/30 transition-colors group">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3 lg:gap-4">
                      <div class="w-10 lg:w-12 h-10 lg:h-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-lg lg:text-xl">person</span>
                      </div>
                      <div>
                        <div class="text-xs lg:text-sm font-bold text-on-surface">{{ $customer->name }}</div>
                        <div class="text-[10px] lg:text-[11px] text-slate-400 font-medium">
                          @if($customer->tier === 'gold')
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[9px] font-black uppercase tracking-tighter">Gold</span>
                          @elseif($customer->tier === 'silver')
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[9px] font-black uppercase tracking-tighter">Silver</span>
                          @else
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-[9px] font-black uppercase tracking-tighter">Bronze</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 font-mono text-[10px] lg:text-xs font-semibold text-slate-500 hidden sm:table-cell">
                    {{ $customer->code }}
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $customer->phone }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
                    <div class="flex items-center gap-2">
                      <span class="text-xs lg:text-sm font-bold text-primary">{{ number_format($customer->available_points) }}</span>
                      <span class="text-[10px] text-slate-400">pts</span>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
                    <div class="flex flex-col">
                      <span class="text-xs lg:text-sm font-bold text-slate-700">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</span>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <div class="flex items-center justify-end gap-1">
                      <a href="/customers/{{ $customer->id }}"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                        title="View Details">
                        <span class="material-symbols-outlined text-base lg:text-sm">visibility</span>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-slate-500">No customers found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div id="addCustomerModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('addCustomerModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Add New Customer</h3>
        <button onclick="document.getElementById('addCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form action="/customers" method="POST" class="space-y-5">
          @csrf
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Customer Name</label>
            <input name="name" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter customer name" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Phone Number</label>
            <input name="phone" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter phone number" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email (Optional)</label>
            <input name="email" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Enter email address" type="email" />
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" onclick="document.getElementById('addCustomerModal').classList.add('hidden')" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer">Cancel</button>
            <button type="submit" class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">Save Customer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</x-layout>