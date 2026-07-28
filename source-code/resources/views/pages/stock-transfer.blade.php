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
        <h1 class="text-lg lg:text-xl font-extrabold tracking-tighter text-blue-900 dark:text-blue-100">Mutasi Barang</h1>
      </div>
    </header>

    <div class="p-4 lg:p-8 flex-1 overflow-y-auto no-scrollbar">
        <div class="mb-6 lg:mb-8">
            <x-report-header title="Stock Transfer History" module="Inventory" submodule="Mutasi Antar Toko" description="Track and manage inter-store stock transfers.">
                <x-slot name="actions">
                    <a href="/stock-transfer/create" class="flex items-center px-4 lg:px-5 py-2 lg:py-2.5 bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-container active:scale-95 transition-all text-xs lg:text-sm cursor-pointer">
                        <span class="material-symbols-outlined mr-1 lg:mr-2 text-base lg:text-lg">add_circle</span>
                        New Transfer
                    </a>
                </x-slot>
            </x-report-header>
        </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Transfers</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-blue-900 font-headline">{{ $transfers->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">In Transit</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-amber-600 font-headline">{{ $transfers->where('status', 'in_transit')->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Received</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-green-600 font-headline">{{ $transfers->where('status', 'received')->count() }}</span>
          </div>
        </div>
        <div class="bg-surface-container-lowest p-4 lg:p-6 rounded-xl shadow-[0_12px_32px_rgba(0,26,64,0.04)] flex flex-col">
          <span class="text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Rejected</span>
          <div class="flex items-baseline gap-2">
            <span class="text-lg lg:text-2xl font-extrabold text-red-600 font-headline">{{ $transfers->where('status', 'rejected')->count() }}</span>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden mb-6">
        <div class="p-4 lg:p-6 bg-surface-container-low/30 border-b border-slate-100">
          <form method="GET" action="/stock-transfer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</label>
              <select name="status"
                class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
            </div>
            <div class="flex items-end">
              <button type="submit" class="w-full bg-primary text-white py-2.5 rounded-lg font-bold text-sm hover:bg-primary-container transition-all cursor-pointer">
                Filter
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="bg-surface-container-lowest rounded-xl lg:rounded-2xl shadow-[0_12px_32px_rgba(0,26,64,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50">
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Transfer No</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">From</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden md:table-cell">To</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Date</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Status</th>
                <th class="px-3 lg:px-6 py-3 lg:py-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              @forelse($transfers as $transfer)
                <tr class="hover:bg-blue-50/30 transition-colors group">
                  <td class="px-3 lg:px-6 py-3 lg:py-5">
                    <div class="flex items-center gap-3 lg:gap-4">
                      <div class="w-10 lg:w-12 h-10 lg:h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-600 text-lg lg:text-xl">swap_horiz</span>
                      </div>
                      <div>
                        <div class="text-xs lg:text-sm font-bold text-on-surface">{{ $transfer->transfer_number }}</div>
                        <div class="text-[10px] lg:text-[11px] text-slate-400 font-medium">{{ $transfer->items->sum('quantity') }} items</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $transfer->sourceStore->name }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden md:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $transfer->destinationStore->name }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
                    <span class="text-xs lg:text-sm font-medium text-on-surface">{{ $transfer->transfer_date->format('d M Y') }}</span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 hidden lg:table-cell">
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
                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusColors[$transfer->status] ?? 'bg-slate-100' }}">
                      {{ $statusLabels[$transfer->status] ?? $transfer->status }}
                    </span>
                  </td>
                  <td class="px-3 lg:px-6 py-3 lg:py-5 text-right">
                    <div class="flex items-center justify-end gap-1">
                      <a href="/stock-transfer/{{ $transfer->id }}"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer"
                        title="View Details">
                        <span class="material-symbols-outlined text-base lg:text-sm">visibility</span>
                      </a>
                      @if($transfer->status === 'draft')
                      <button onclick="confirmDelete({{ $transfer->id }})"
                        class="p-1.5 lg:p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                        title="Delete">
                        <span class="material-symbols-outlined text-base lg:text-sm">delete</span>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-6 py-8 text-center text-slate-500">No stock transfers found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <form id="deleteTransferForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
  </form>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function confirmDelete(id) {
      Swal.fire({
        title: 'Delete Transfer?',
        text: 'Are you sure you want to delete this draft transfer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.getElementById('deleteTransferForm');
          form.action = '/stock-transfer/' + id;
          form.submit();
        }
      });
    }

    setTimeout(() => {
      const successAlert = document.getElementById('success-alert');
      if (successAlert) successAlert.style.display = 'none';
      const errorAlert = document.getElementById('error-alert');
      if (errorAlert) errorAlert.style.display = 'none';
    }, 3000);
  </script>

</x-layout>
