<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Detail Stok Masuk</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Stock In Detail" module="Inventory" submodule="Stock In" description="View details for stock-in entry #{{ $stockIn->id }}." />
        </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Transaction Info</h3>
          <div class="space-y-3">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Reference No</span>
              <p class="text-sm font-bold text-on-surface">{{ $stockIn->reference_no ?? '#' . $stockIn->id }}</p>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Date</span>
              <p class="text-sm font-bold text-on-surface">{{ $stockIn->date->format('d M Y') }}</p>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Created By</span>
              <p class="text-sm font-bold text-on-surface">{{ $stockIn->user->name }}</p>
            </div>
            @if($stockIn->notes)
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Notes</span>
              <p class="text-sm font-medium text-on-surface">{{ $stockIn->notes }}</p>
            </div>
            @endif
          </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Supplier</h3>
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary text-xl">local_shipping</span>
            </div>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ $stockIn->supplier->name }}</p>
              @if($stockIn->supplier->contact_name)
                <p class="text-[10px] text-slate-400">Contact: {{ $stockIn->supplier->contact_name }}</p>
              @endif
            </div>
          </div>
          @if($stockIn->supplier->phone)
          <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Phone</span>
            <p class="text-sm font-medium text-on-surface">{{ $stockIn->supplier->phone }}</p>
          </div>
          @endif
          @if($stockIn->supplier->email)
          <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Email</span>
            <p class="text-sm font-medium text-on-surface">{{ $stockIn->supplier->email }}</p>
          </div>
          @endif
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Store</h3>
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <span class="material-symbols-outlined text-green-600 text-xl">store</span>
            </div>
            <div>
              <p class="text-sm font-bold text-on-surface">{{ $stockIn->store->name }}</p>
              @if($stockIn->store->code)
                <p class="text-[10px] text-slate-400">Code: {{ $stockIn->store->code }}</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <h3 class="text-base font-bold text-on-surface">Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Product</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden sm:table-cell">SKU</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Quantity</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Expired</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Cost Price</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($stockIn->items as $item)
                @php
                  $expired = $item->expired_date ? \Carbon\Carbon::parse($item->expired_date) : null;
                  $isExpired = $expired && $expired->isPast();
                  $isNear = $expired && $expired->isFuture() && $expired->diffInDays(now()) <= 30;
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-lg">inventory_2</span>
                      </div>
                      <span class="text-xs lg:text-sm font-bold text-on-surface">{{ $item->product->name }}</span>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden sm:table-cell">
                    <span class="text-xs font-mono font-semibold text-slate-500">{{ $item->product->sku ?? '-' }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    <span class="text-xs lg:text-sm font-bold text-on-surface">{{ number_format($item->quantity) }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    @if($expired)
                      <span class="text-xs font-bold px-2 py-0.5 rounded {{ $isExpired ? 'bg-red-100 text-red-700' : ($isNear ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                        {{ $expired->format('d M Y') }}
                        @if($isExpired) (Expired) @elseif($isNear) ({{ $expired->diffInDays(now()) }} hari) @endif
                      </span>
                    @else
                      <span class="text-xs text-slate-400">-</span>
                    @endif
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($item->quantity * $item->cost_price, 0, ',', '.') }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="bg-surface-container-low/50">
                <td colspan="5" class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                  <span class="text-sm font-extrabold text-on-surface uppercase tracking-wider">Total</span>
                </td>
                <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                  <span class="text-lg font-extrabold text-green-600">Rp {{ number_format($stockIn->total_amount, 0, ',', '.') }}</span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="mt-6 flex gap-3">
        <a href="/stock-in" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all text-center cursor-pointer">Back to List</a>
        <a href="/stock-in/{{ $stockIn->id }}/print-faktur" target="_blank"
          class="flex items-center justify-center px-6 bg-primary text-white py-3 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">
          <span class="material-symbols-outlined mr-2 text-base">print</span>
          Cetak Faktur
        </a>
      </div>
    </div>
  </main>

</x-layout>
