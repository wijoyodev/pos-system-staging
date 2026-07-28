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
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Detail PO</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Purchase Order Detail" module="Inventory" submodule="Purchase Orders" description="View details for purchase order." />
        </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Order Info</h3>
          <div class="space-y-3">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">PO Number</span>
              <p class="text-sm font-bold text-on-surface font-mono">{{ $purchaseOrder->po_number }}</p>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Order Date</span>
              <p class="text-sm font-bold text-on-surface">{{ $purchaseOrder->order_date->format('d M Y') }}</p>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Expected Delivery</span>
              <p class="text-sm font-bold text-on-surface">{{ $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('d M Y') : '-' }}</p>
            </div>
            @if($purchaseOrder->delivery_date)
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Delivery Date</span>
              <p class="text-sm font-bold text-green-600">{{ $purchaseOrder->delivery_date->format('d M Y') }}</p>
            </div>
            @endif
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Ordered By</span>
              <p class="text-sm font-bold text-on-surface">{{ $purchaseOrder->orderedBy?->name ?? '-' }}</p>
            </div>
            @if($purchaseOrder->notes)
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Notes</span>
              <p class="text-sm font-medium text-on-surface">{{ $purchaseOrder->notes }}</p>
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
              <p class="text-sm font-bold text-on-surface">{{ $purchaseOrder->supplier?->name ?? '-' }}</p>
              @if($purchaseOrder->supplier->contact_name)
                <p class="text-[10px] text-slate-400">Contact: {{ $purchaseOrder->supplier->contact_name }}</p>
              @endif
            </div>
          </div>
          @if($purchaseOrder->supplier?->phone)
          <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Phone</span>
            <p class="text-sm font-medium text-on-surface">{{ $purchaseOrder->supplier->phone }}</p>
          </div>
          @endif
          @if($purchaseOrder->supplier?->email)
          <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Email</span>
            <p class="text-sm font-medium text-on-surface">{{ $purchaseOrder->supplier->email }}</p>
          </div>
          @endif
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Status</h3>
          <div class="flex items-center gap-3 mb-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center
              @if($purchaseOrder->status === 'ordered') bg-blue-100
              @elseif($purchaseOrder->status === 'received') bg-green-100
              @elseif($purchaseOrder->status === 'draft') bg-yellow-100
              @elseif($purchaseOrder->status === 'cancelled') bg-red-100
              @endif">
              @if($purchaseOrder->status === 'ordered')
                <span class="material-symbols-outlined text-blue-600 text-3xl">send</span>
              @elseif($purchaseOrder->status === 'received')
                <span class="material-symbols-outlined text-green-600 text-3xl">check_circle</span>
              @elseif($purchaseOrder->status === 'draft')
                <span class="material-symbols-outlined text-yellow-600 text-3xl">edit_note</span>
              @elseif($purchaseOrder->status === 'cancelled')
                <span class="material-symbols-outlined text-red-600 text-3xl">cancel</span>
              @endif
            </div>
            <div>
              <p class="text-lg font-extrabold text-on-surface uppercase">{{ $purchaseOrder->status }}</p>
            </div>
          </div>

          @if($purchaseOrder->isReceivable())
          <form action="/purchase-orders/{{ $purchaseOrder->id }}/receive" method="POST">
            @csrf
            <button type="submit"
              class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white font-bold rounded-lg shadow-md hover:bg-green-700 active:scale-95 transition-all text-sm cursor-pointer">
              <span class="material-symbols-outlined mr-2 text-base">inventory</span>
              Receive Items
            </button>
          </form>
          @endif

          @if($purchaseOrder->isCancelable())
          <form action="/purchase-orders/{{ $purchaseOrder->id }}/cancel" method="POST" class="mt-2">
            @csrf
            <button type="submit"
              class="w-full flex items-center justify-center px-4 py-3 bg-red-50 text-red-600 font-bold rounded-lg shadow-md hover:bg-red-100 active:scale-95 transition-all text-sm cursor-pointer">
              <span class="material-symbols-outlined mr-2 text-base">cancel</span>
              Cancel PO
            </button>
          </form>
          @endif
        </div>
      </div>

      @if($purchaseOrder->eodReport)
      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6 mb-6">
        <div class="flex items-center gap-2 mb-2">
          <span class="material-symbols-outlined text-purple-600">event_available</span>
          <h3 class="text-sm font-bold text-on-surface">Generated from EOD Report</h3>
        </div>
        <a href="/eod/{{ $purchaseOrder->eodReport->id }}" class="text-xs text-primary hover:underline">
          EOD {{ $purchaseOrder->eodReport->eod_date->format('d M Y') }} - {{ $purchaseOrder->eodReport->store?->name ?? '-' }}
        </a>
      </div>
      @endif

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <h3 class="text-base font-bold text-on-surface">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Product</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Qty Ordered</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Cost Price</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($purchaseOrder->items as $item)
                <tr class="hover:bg-blue-50/30 transition-colors">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-lg">inventory_2</span>
                      </div>
                      <span class="text-xs lg:text-sm font-bold text-on-surface">{{ $item->product?->name ?? 'Product Deleted' }}</span>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                    <span class="text-xs lg:text-sm font-bold text-on-surface">{{ number_format($item->quantity_ordered) }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <span class="text-xs lg:text-sm font-bold text-green-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr class="bg-surface-container-low/50">
                <td colspan="3" class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                  <span class="text-sm font-extrabold text-on-surface uppercase tracking-wider">Total</span>
                </td>
                <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                  <span class="text-lg font-extrabold text-green-600">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="mt-6 flex gap-3">
        <a href="/purchase-orders" class="flex-1 bg-surface-container-high text-on-surface py-3 rounded-lg font-bold text-sm hover:bg-surface-dim transition-all text-center cursor-pointer">Back to List</a>
        <a href="/purchase-orders/{{ $purchaseOrder->id }}/print-faktur" target="_blank"
          class="flex items-center justify-center px-6 bg-primary text-white py-3 rounded-lg font-bold text-sm shadow-md hover:bg-primary-container transition-all cursor-pointer">
          <span class="material-symbols-outlined mr-2 text-base">print</span>
          Cetak Faktur
        </a>
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
