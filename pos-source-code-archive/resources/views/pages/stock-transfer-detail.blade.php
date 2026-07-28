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
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Detail Mutasi</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="{{ $transfer->transfer_number }}" module="Inventory" submodule="Mutasi Barang" description="Detail information and items for this stock transfer." />
        </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Transfer Info</h3>
          <div class="space-y-3">
            <div>
              <span class="text-xs text-slate-500">Transfer Number</span>
              <p class="text-sm font-bold text-on-surface">{{ $transfer->transfer_number }}</p>
            </div>
            <div>
              <span class="text-xs text-slate-500">Date</span>
              <p class="text-sm font-medium text-on-surface">{{ $transfer->transfer_date->format('d M Y') }}</p>
            </div>
            <div>
              <span class="text-xs text-slate-500">Status</span>
              <div class="mt-1">
                @php
                  $statusColors = [
                    'draft' => 'bg-slate-100 text-slate-600',
                    'sent' => 'bg-blue-100 text-blue-600',
                    'in_transit' => 'bg-amber-100 text-amber-600',
                    'received' => 'bg-green-100 text-green-600',
                    'rejected' => 'bg-red-100 text-red-600',
                  ];
                  $statusLabels = [
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'in_transit' => 'In Transit',
                    'received' => 'Received',
                    'rejected' => 'Rejected',
                  ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusColors[$transfer->status] }}">
                  {{ $statusLabels[$transfer->status] }}
                </span>
              </div>
            </div>
            @if($transfer->notes)
            <div>
              <span class="text-xs text-slate-500">Notes</span>
              <p class="text-sm text-on-surface">{{ $transfer->notes }}</p>
            </div>
            @endif
          </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Stores</h3>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-600">store</span>
              </div>
              <div>
                <span class="text-xs text-slate-500">From</span>
                <p class="text-sm font-bold text-on-surface">{{ $transfer->sourceStore->name }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-green-600">store</span>
              </div>
              <div>
                <span class="text-xs text-slate-500">To</span>
                <p class="text-sm font-bold text-on-surface">{{ $transfer->destinationStore->name }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
          <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Actions</h3>
          <div class="space-y-2">
            @if($transfer->status === 'draft')
            <form action="/stock-transfer/{{ $transfer->id }}/send" method="POST">
              @csrf
              <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">
                <span class="material-symbols-outlined text-sm align-middle mr-1">send</span>
                Send Transfer
              </button>
            </form>
            <form action="/stock-transfer/{{ $transfer->id }}" method="POST" onsubmit="return confirmDelete(event)">
              @csrf
              @method('DELETE')
              <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">
                <span class="material-symbols-outlined text-sm align-middle mr-1">delete</span>
                Delete Draft
              </button>
            </form>
            @endif

            @if($transfer->status === 'in_transit')
            <form action="/stock-transfer/{{ $transfer->id }}/receive" method="POST">
              @csrf
              <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">
                <span class="material-symbols-outlined text-sm align-middle mr-1">check_circle</span>
                Receive Items
              </button>
            </form>
            <button onclick="showRejectModal()" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">
              <span class="material-symbols-outlined text-sm align-middle mr-1">cancel</span>
              Reject Transfer
            </button>
            @endif
          </div>
        </div>
      </div>

      @if($transfer->status === 'rejected' && $transfer->rejection_reason)
      <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-2 mb-2">
          <span class="material-symbols-outlined text-red-600">error</span>
          <h3 class="text-sm font-bold text-red-800">Rejection Reason</h3>
        </div>
        <p class="text-sm text-red-700">{{ $transfer->rejection_reason }}</p>
      </div>
      @endif

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden mb-6">
        <div class="p-4 lg:p-6 border-b border-slate-100">
          <h3 class="text-base font-bold text-on-surface">Transfer Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Product</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden sm:table-cell">SKU</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Qty</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center">Stock at Source</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @foreach($transfer->items as $item)
              <tr class="hover:bg-blue-50/30 transition-colors">
                <td class="px-3 lg:px-6 py-3 lg:py-5">
                  <span class="text-sm font-medium text-on-surface">{{ $item->product ? $item->product->name : 'Produk dihapus' }}</span>
                </td>
                <td class="px-3 lg:px-6 py-3 lg:py-5 hidden sm:table-cell">
                  <span class="text-xs font-mono text-slate-500">{{ $item->product ? $item->product->sku : '-' }}</span>
                </td>
                <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                  <span class="text-sm font-bold text-blue-600">{{ $item->quantity }}</span>
                </td>
                <td class="px-3 lg:px-6 py-3 lg:py-5 text-center">
                  @php
                    $stock = $sourceStocks[$item->product_id] ?? 0;
                    $stockClass = $stock >= $item->quantity ? 'text-green-600' : 'text-red-600';
                  @endphp
                  <span class="text-sm font-bold {{ $stockClass }}">{{ $stock }}</span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] p-4 lg:p-6">
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Audit Trail</h3>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
              <span class="material-symbols-outlined text-slate-600 text-sm">person</span>
            </div>
            <div>
              <p class="text-xs text-slate-500">Created by</p>
              <p class="text-sm font-medium text-on-surface">{{ $transfer->createdBy->name }} - {{ $transfer->created_at->format('d M Y H:i') }}</p>
            </div>
          </div>
          @if($transfer->approvedBy)
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
              <span class="material-symbols-outlined text-green-600 text-sm">verified</span>
            </div>
            <div>
              <p class="text-xs text-slate-500">Approved by</p>
              <p class="text-sm font-medium text-on-surface">{{ $transfer->approvedBy->name }} - {{ $transfer->approved_at->format('d M Y H:i') }}</p>
            </div>
          </div>
          @endif
          @if($transfer->received_at)
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="material-symbols-outlined text-blue-600 text-sm">check_circle</span>
            </div>
            <div>
              <p class="text-xs text-slate-500">Received at</p>
              <p class="text-sm font-medium text-on-surface">{{ $transfer->received_at->format('d M Y H:i') }}</p>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>

  <!-- Reject Modal -->
  <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-[calc(100%-2rem)] md:w-full max-w-md">
      <h3 class="text-lg font-bold text-on-surface mb-4">Reject Transfer</h3>
      <form action="/stock-transfer/{{ $transfer->id }}/reject" method="POST">
        @csrf
        <div class="space-y-3">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Reason <span class="text-red-500">*</span></label>
          <textarea name="reason" required rows="3" class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-none" placeholder="Reason for rejection..."></textarea>
        </div>
        <div class="flex gap-3 mt-4">
          <button type="button" onclick="hideRejectModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">Cancel</button>
          <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-lg transition-colors text-sm cursor-pointer">Reject</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function showRejectModal() {
      document.getElementById('rejectModal').classList.remove('hidden');
    }

    function hideRejectModal() {
      document.getElementById('rejectModal').classList.add('hidden');
    }

    function confirmDelete(e) {
      e.preventDefault();
      const form = e.target;
      Swal.fire({
        title: 'Delete Draft?',
        text: 'Are you sure you want to delete this draft transfer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
      return false;
    }

    setTimeout(() => {
      const successAlert = document.getElementById('success-alert');
      if (successAlert) successAlert.style.display = 'none';
      const errorAlert = document.getElementById('error-alert');
      if (errorAlert) errorAlert.style.display = 'none';
    }, 3000);
  </script>

</x-layout>
