<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <a href="/customers" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-slate-600">arrow_back</span>
        </a>
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Customer Details</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <!-- Report Header Section -->
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $title ?? 'Page' }}" />
        </div>

      <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 mb-8">
        <div class="lg:w-1/3">
          <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl p-6 shadow-[0_12px_32px_rgba(0,26,64,0.04)]">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-3xl">person</span>
              </div>
              <div>
                <h2 class="text-xl font-bold text-on-surface">{{ $customer->name }}</h2>
                <p class="text-sm text-slate-500 font-mono">{{ $customer->code }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Phone</span>
                <span class="text-sm font-bold text-on-surface">{{ $customer->phone }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Email</span>
                <span class="text-sm font-bold text-on-surface">{{ $customer->email ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Member Since</span>
                <span class="text-sm font-bold text-on-surface">{{ $customer->created_at->format('d M Y') }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Tier</span>
                <span class="px-2 py-1 rounded text-xs font-bold 
                  @if($customer->tier === 'gold') bg-yellow-100 text-yellow-700
                  @elseif($customer->tier === 'silver') bg-slate-200 text-slate-600
                  @else bg-orange-100 text-orange-700 @endif">
                  {{ strtoupper($customer->tier) }}
                </span>
              </div>
            </div>

            <div class="mt-6 p-4 bg-primary/5 rounded-lg border border-primary/10">
              <div class="text-center">
                <span class="text-xs text-slate-500 uppercase tracking-wider">Available Points</span>
                <p class="text-3xl font-extrabold text-primary font-headline">{{ number_format($customer->available_points) }}</p>
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <button onclick="document.getElementById('editCustomerModal').classList.remove('hidden')" class="flex-1 py-2 bg-surface-container-high text-on-surface font-bold text-sm rounded-lg hover:bg-surface-dim transition-all cursor-pointer">
                Edit
              </button>
              <form action="/customers/{{ $customer->id }}" method="POST" onsubmit="return confirm('Delete this customer?');" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 bg-error-container text-error font-bold text-sm rounded-lg hover:bg-error/10 transition-all cursor-pointer">
                  Delete
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="lg:w-2/3">
          <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
            <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
              <h3 class="text-lg font-bold text-on-surface">Transaction History</h3>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-container-low/50">
                    <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Invoice</th>
                    <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden sm:table-cell">Date</th>
                    <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Items</th>
                    <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  @forelse($histories as $history)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                      <td class="px-3 lg:px-6 py-3 lg:py-5">
                        <span class="text-xs lg:text-sm font-bold text-on-surface font-mono">{{ $history->invoice_id }}</span>
                      </td>
                      <td class="px-3 lg:px-6 py-3 lg:py-5 hidden sm:table-cell">
                        <span class="text-xs lg:text-sm text-slate-500">{{ $history->created_at->format('d M Y, H:i') }}</span>
                      </td>
                      <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                        <span class="text-xs lg:text-sm text-on-surface">{{ $history->items->count() }} items</span>
                      </td>
                      <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                        <span class="text-xs lg:text-sm font-bold text-primary">Rp{{ number_format($history->total_amount, 0, ',', '.') }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="px-6 py-8 text-center text-slate-500">No transactions yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($histories->hasPages())
              <div class="px-6 py-4 bg-surface-container-low/30 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Showing {{ $histories->firstItem() ?? 0 }} - {{ $histories->lastItem() ?? 0 }} of {{ $histories->total() }}</span>
                <div class="flex items-center gap-1">
                  @if($histories->onFirstPage())
                    <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                      <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </span>
                  @else
                    <a href="{{ $histories->previousPageUrl() }}" class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                      <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </a>
                  @endif
                  @foreach($histories->getUrlRange(max($histories->currentPage() - 2, 1), min($histories->currentPage() + 2, $histories->lastPage())) as $page => $url)
                    <a href="{{ $url }}" class="text-xs font-bold px-2.5 py-1 rounded {{ $page == $histories->currentPage() ? 'bg-primary text-white' : 'hover:bg-surface-container cursor-pointer' }} transition-colors">{{ $page }}</a>
                  @endforeach
                  @if($histories->hasMorePages())
                    <a href="{{ $histories->nextPageUrl() }}" class="p-1.5 rounded bg-surface-container hover:bg-surface-container-high transition-colors text-primary">
                      <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </a>
                  @else
                    <span class="p-1.5 rounded bg-surface-container text-slate-300 cursor-not-allowed">
                      <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </span>
                  @endif
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </main>

  <div id="editCustomerModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('editCustomerModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] md:w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/20">
      <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low/30">
        <h3 class="text-lg font-bold text-on-surface">Edit Customer</h3>
        <button onclick="document.getElementById('editCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-on-surface transition-colors cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 overflow-y-auto">
        <form action="/customers/{{ $customer->id }}" method="POST" class="space-y-5">
          @csrf
          @method('PUT')
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Customer Name</label>
            <input name="name" value="{{ $customer->name }}" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Phone Number</label>
            <input name="phone" value="{{ $customer->phone }}" required class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="text" />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email (Optional)</label>
            <input name="email" value="{{ $customer->email ?? '' }}" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" type="email" />
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" onclick="document.getElementById('editCustomerModal').classList.add('hidden')" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all cursor-pointer">Cancel</button>
            <button type="submit" class="flex-2 grow bg-primary text-white py-3 px-6 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</x-layout>