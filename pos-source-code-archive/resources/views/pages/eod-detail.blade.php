<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>

  <main class="flex-1 flex flex-col min-h-screen relative w-full">

    <header
      class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full px-4 lg:px-8 py-3 lg:py-4 shadow-sm font-manrope antialiased tracking-tight">
      <div class="flex items-center gap-3 lg:gap-8 pl-10 lg:pl-0">
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Detail EOD</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
            <x-report-header title="End of Day Detail" module="Reports" submodule="EOD" description="Detailed breakdown of daily sales, payments, and settlements." />

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6">
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Transactions</span>
          <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ number_format($eodReport->total_transactions) }}</span>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Sales</span>
          <span class="text-lg lg:text-2xl font-extrabold text-green-600 font-headline">Rp {{ number_format($eodReport->total_sales, 0, ',', '.') }}</span>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Net Sales</span>
          <span class="text-lg lg:text-2xl font-extrabold text-blue-600 font-headline">Rp {{ number_format($eodReport->total_net_sales, 0, ',', '.') }}</span>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Expenses</span>
          <span class="text-lg lg:text-2xl font-extrabold text-red-600 font-headline">Rp {{ number_format($eodReport->total_expenses, 0, ',', '.') }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-base font-bold text-on-surface mb-4">Payment Breakdown</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Cash</span>
              <span class="text-sm font-bold text-on-surface">Rp {{ number_format($eodReport->sales_cash, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">QRIS</span>
              <span class="text-sm font-bold text-on-surface">Rp {{ number_format($eodReport->sales_qris, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Debit</span>
              <span class="text-sm font-bold text-on-surface">Rp {{ number_format($eodReport->sales_debit, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Credit</span>
              <span class="text-sm font-bold text-on-surface">Rp {{ number_format($eodReport->sales_credit, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-base font-bold text-on-surface mb-4">Discounts & Deductions</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Promo Discount</span>
              <span class="text-sm font-bold text-red-600">- Rp {{ number_format($eodReport->total_promo_discount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Points Discount</span>
              <span class="text-sm font-bold text-red-600">- Rp {{ number_format($eodReport->total_points_discount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Tier Discount</span>
              <span class="text-sm font-bold text-red-600">- Rp {{ number_format($eodReport->total_tier_discount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Voucher Discount</span>
              <span class="text-sm font-bold text-red-600">- Rp {{ number_format($eodReport->total_voucher_discount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-slate-600">Tax</span>
              <span class="text-sm font-bold text-on-surface">Rp {{ number_format($eodReport->total_tax, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6 mb-6">
        <h3 class="text-base font-bold text-on-surface mb-4">Cash Reconciliation (from Shift Closing)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-blue-50 rounded-lg p-4">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Expected Cash</span>
            <p class="text-lg font-extrabold text-blue-900">Rp {{ number_format($eodReport->total_expected_cash, 0, ',', '.') }}</p>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Actual Cash</span>
            <p class="text-lg font-extrabold text-green-600">Rp {{ number_format($eodReport->total_actual_cash, 0, ',', '.') }}</p>
          </div>
          <div class="{{ $eodReport->cash_difference < 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg p-4">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Difference</span>
            @if($eodReport->cash_difference < 0)
              <p class="text-lg font-extrabold text-red-600">- Rp {{ number_format(abs($eodReport->cash_difference), 0, ',', '.') }}</p>
            @else
              <p class="text-lg font-extrabold text-green-600">Rp {{ number_format($eodReport->cash_difference, 0, ',', '.') }}</p>
            @endif
          </div>
        </div>
        <p class="text-xs text-slate-400 mt-2">Total closings: {{ $eodReport->total_closings }}</p>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden mb-6">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <h3 class="text-base font-bold text-on-surface">Products Sold</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Product</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Qty Sold</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($eodReport->items->sortByDesc('total_qty_sold') as $item)
                <tr class="hover:bg-blue-50/30 transition-colors">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-lg">inventory_2</span>
                      </div>
                      <span class="text-xs lg:text-sm font-bold text-on-surface">{{ $item->product->name }}</span>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    <span class="text-xs lg:text-sm font-bold text-on-surface">{{ number_format($item->total_qty_sold) }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      @if($eodReport->purchaseOrders->count() > 0)
      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden mb-6">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <h3 class="text-base font-bold text-on-surface">Auto-Generated Purchase Orders</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">PO Number</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">Supplier</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Total</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Status</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($eodReport->purchaseOrders as $po)
                <tr class="hover:bg-blue-50/30 transition-colors">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <span class="text-xs lg:text-sm font-bold text-on-surface font-mono">{{ $po->po_number }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $po->supplier->name }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    @if($po->status === 'ordered')
                      <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-[9px] font-black uppercase">Ordered</span>
                    @elseif($po->status === 'received')
                      <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[9px] font-black uppercase">Received</span>
                    @elseif($po->status === 'cancelled')
                      <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-[9px] font-black uppercase">Cancelled</span>
                    @endif
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <a href="/purchase-orders/{{ $po->id }}"
                      class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer">
                      <span class="material-symbols-outlined text-base lg:text-sm">visibility</span>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      <div class="flex gap-3">
        <a href="/eod" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all text-center cursor-pointer">Back to List</a>
        <a href="/eod/{{ $eodReport->id }}/print" target="_blank"
          class="flex items-center justify-center px-6 bg-primary text-white py-3 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">
          <span class="material-symbols-outlined mr-2 text-base">print</span>
          Print EOD
        </a>
      </div>
    </div>
  </main>

</x-layout>
